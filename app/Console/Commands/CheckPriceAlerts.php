<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\PriceAlert;
use App\Models\PushSubscription;
use App\Models\StellarMarketToken;
use App\Services\StellarTokenService;
use App\Services\WebPushService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckPriceAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Evaluate active price alerts and dispatch push/on-site notifications';

    /**
     * Execute the console command.
     */
    public function handle(StellarTokenService $stellarService, WebPushService $pushService)
    {
        $activeAlerts = PriceAlert::where('status', 'active')->get();

        if ($activeAlerts->isEmpty()) {
            $this->info('No active alerts to evaluate.');
            return 0;
        }

        $this->info("Evaluating {$activeAlerts->count()} active alerts...");

        $groupedAlerts = $activeAlerts->groupBy(function ($alert) {
            return $alert->asset_code . ':' . $alert->asset_issuer;
        });

        $firedCount = 0;

        foreach ($groupedAlerts as $tokenKey => $alerts) {
            list($code, $issuer) = explode(':', $tokenKey);

            // Fetch current price for this token
            $cacheKey = "token_insight_v3_{$issuer}_{$code}";
            $insight = Cache::get($cacheKey);
            $market = StellarMarketToken::where('asset_issuer', $issuer)->where('asset_code', $code)->first();

            $currentXlmPrice = 0.0;
            $currentUsdPrice = 0.0;
            $current24hPct = 0.0;

            if ($insight && isset($insight['xlm_price'])) {
                $currentXlmPrice = (float)$insight['xlm_price'];
                $currentUsdPrice = (float)($insight['usd_price'] ?? 0.0);
                $current24hPct = (float)($insight['price_change_24h'] ?? 0.0);
            } elseif ($market && $market->current_price_xlm) {
                $currentXlmPrice = (float)$market->current_price_xlm;
                $currentUsdPrice = (float)($market->current_price_usd ?? 0.0);
            } else {
                try {
                    $assets = $stellarService->getAssetsByIssuer($issuer, $code);
                    if (!empty($assets)) {
                        $insight = $stellarService->getTokenInsight($issuer, $code, $assets[0]);
                        $currentXlmPrice = (float)($insight['xlm_price'] ?? 0.0);
                        $currentUsdPrice = (float)($insight['usd_price'] ?? 0.0);
                        $current24hPct = (float)($insight['price_change_24h'] ?? 0.0);
                        Cache::put($cacheKey, $insight, 300);
                    }
                } catch (\Throwable $e) {
                    Log::warning("Could not fetch price for alert check {$code}-{$issuer}: " . $e->getMessage());
                    continue;
                }
            }

            if ($currentXlmPrice <= 0) {
                continue;
            }

            foreach ($alerts as $alert) {
                $conditionMet = false;
                $reasonText = '';
                $currency = strtolower($alert->currency ?? 'xlm');
                $targetVal = (float)$alert->condition_value;
                $initialXlm = (float)$alert->initial_price_xlm;
                $initialUsd = (float)$alert->initial_price_usd;

                switch ($alert->condition_type) {
                    case 'price_above':
                        if ($currency === 'usd') {
                            if ($currentUsdPrice >= $targetVal && $currentUsdPrice > 0) {
                                $conditionMet = true;
                                $reasonText = "Price reached \${$currentUsdPrice} (target: >= \${$targetVal})";
                            }
                        } else {
                            if ($currentXlmPrice >= $targetVal) {
                                $conditionMet = true;
                                $reasonText = "Price reached {$currentXlmPrice} XLM (target: >= {$targetVal} XLM)";
                            }
                        }
                        break;

                    case 'price_below':
                        if ($currency === 'usd') {
                            if ($currentUsdPrice <= $targetVal && $currentUsdPrice > 0) {
                                $conditionMet = true;
                                $reasonText = "Price reached \${$currentUsdPrice} (target: <= \${$targetVal})";
                            }
                        } else {
                            if ($currentXlmPrice <= $targetVal && $currentXlmPrice > 0) {
                                $conditionMet = true;
                                $reasonText = "Price reached {$currentXlmPrice} XLM (target: <= {$targetVal} XLM)";
                            }
                        }
                        break;

                    case 'pct_change_up':
                        $change = 0.0;
                        if ($initialXlm > 0) {
                            $change = (($currentXlmPrice - $initialXlm) / $initialXlm) * 100;
                        } else {
                            $change = $current24hPct;
                        }
                        if ($change >= $targetVal) {
                            $conditionMet = true;
                            $formattedChange = ($change >= 0 ? '+' : '') . number_format($change, 2);
                            $reasonText = "Price gained {$formattedChange}% (target: +{$targetVal}%)";
                        }
                        break;

                    case 'pct_change_down':
                        $drop = 0.0;
                        if ($initialXlm > 0) {
                            $drop = (($initialXlm - $currentXlmPrice) / $initialXlm) * 100;
                        } else {
                            $drop = abs($current24hPct);
                        }
                        if ($drop >= $targetVal) {
                            $conditionMet = true;
                            $reasonText = "Price dropped {$targetVal}% or more";
                        }
                        break;
                }

                if ($conditionMet) {
                    $alert->update([
                        'status' => 'fired',
                        'fired_at' => now(),
                    ]);

                    $firedCount++;
                    $message = "{$alert->asset_code} Alert: {$reasonText}. Current price: {$currentXlmPrice} XLM (\${$currentUsdPrice})";

                    // 1. Insert on-site notification
                    Notification::create([
                        'wallet_address' => $alert->wallet_address,
                        'alert_id'       => $alert->id,
                        'asset_code'     => $alert->asset_code,
                        'asset_issuer'   => $alert->asset_issuer,
                        'title'          => "🔔 {$alert->asset_code} Alert Triggered",
                        'message'        => $message,
                        'read'           => false,
                    ]);

                    // 2. Dispatch Browser Push Notification if channel enabled
                    $channels = $alert->channels ?? ['onsite'];
                    if (in_array('push', $channels)) {
                        $subscriptions = PushSubscription::where('wallet_address', $alert->wallet_address)->get();
                        foreach ($subscriptions as $sub) {
                            $pushService->sendNotification($sub, [
                                'title'        => "🔔 {$alert->asset_code} Price Alert",
                                'body'         => $message,
                                'icon'         => '/src/assets/token-glade-logo.png',
                                'url'          => "https://tokenglade.com/t/{$alert->asset_issuer}",
                                'asset_code'   => $alert->asset_code,
                                'asset_issuer' => $alert->asset_issuer,
                            ]);
                        }
                    }

                    $this->info("Fired alert [{$alert->id}] for {$alert->asset_code} ({$alert->wallet_address})");
                }
            }
        }

        $this->info("Alert evaluation complete. Fired {$firedCount} alerts.");
        return 0;
    }
}

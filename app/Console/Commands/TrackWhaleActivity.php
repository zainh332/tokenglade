<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\StellarMarketToken;
use App\Models\TokenWhaleEvent;
use App\Models\Setting;
use App\Services\StellarTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TrackWhaleActivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:track-whale-activity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Track large DEX trades and liquidity pool operations for active Stellar tokens';

    /**
     * Execute the console command.
     */
    public function handle(StellarTokenService $service)
    {
        $this->info('Starting whale activity tracking...');
        
        $thresholdSetting = Setting::firstOrCreate(
            ['key' => 'whale_activity_threshold_xlm'],
            ['value' => env('WHALE_ACTIVITY_THRESHOLD_XLM', '5000')]
        );
        $thresholdXlm = (float) $thresholdSetting->value;
        $this->info("Threshold set to: {$thresholdXlm} XLM (loaded from database)");

        $xlmUsdPrice = $service->getXlmUsdPrice();
        $marketTokens = StellarMarketToken::all();
        $eventsSaved = 0;

        foreach ($marketTokens as $token) {
            $code = strtoupper($token->asset_code ?? '');
            $issuer = $token->asset_issuer ?? '';
            if (!$code || !$issuer) continue;

            $this->info("Processing token {$code}...");

            // 1. Process DEX Trades
            try {
                $eventsSaved += $this->processTrades($token, $thresholdXlm, $xlmUsdPrice);
            } catch (\Throwable $e) {
                Log::error("Whale activity tracking: failed to process trades for {$code}: " . $e->getMessage());
                $this->error("Failed trades for {$code}: " . $e->getMessage());
            }

            // 2. Process LP Deposits/Withdrawals
            try {
                $eventsSaved += $this->processLpOperations($token, $thresholdXlm, $xlmUsdPrice);
            } catch (\Throwable $e) {
                Log::error("Whale activity tracking: failed to process LP ops for {$code}: " . $e->getMessage());
                $this->error("Failed LP ops for {$code}: " . $e->getMessage());
            }
        }

        // Cleanup events older than 24 hours
        $deleted = TokenWhaleEvent::where('created_at', '<', now()->subHours(24))->delete();
        $this->info("Saved {$eventsSaved} new large events. Purged {$deleted} events older than 24 hours.");

        return Command::SUCCESS;
    }

    /**
     * Process DEX trades for a token.
     */
    private function processTrades(StellarMarketToken $token, float $thresholdXlm, float $xlmUsdPrice): int
    {
        $code = strtoupper($token->asset_code);
        $issuer = $token->asset_issuer;
        $assetType = strlen($code) <= 4 ? 'credit_alphanum4' : 'credit_alphanum12';
        
        $cursorKey = "whale_cursor_trades_{$code}_{$issuer}";
        $cursorSetting = Setting::where('key', $cursorKey)->first();
        $cursor = $cursorSetting ? $cursorSetting->value : null;

        $horizonUrl = 'https://horizon.stellar.org';

        // If no cursor exists, fetch the latest trade to initialize it
        if (!$cursor) {
            $initRes = Http::get("{$horizonUrl}/trades", [
                'base_asset_type'   => $assetType,
                'base_asset_code'   => $code,
                'base_asset_issuer' => $issuer,
                'order'             => 'desc',
                'limit'             => 1,
            ]);

            if ($initRes->ok()) {
                $records = $initRes->json('_embedded.records');
                if (!empty($records)) {
                    $cursor = $records[0]['paging_token'];
                    Setting::updateOrCreate(['key' => $cursorKey], ['value' => $cursor]);
                    $this->info("Initialized trades cursor for {$code} to {$cursor}");
                }
            }
            return 0; // Skip processing on initialization run
        }

        // Fetch trades after the cursor
        $response = Http::get("{$horizonUrl}/trades", [
            'base_asset_type'   => $assetType,
            'base_asset_code'   => $code,
            'base_asset_issuer' => $issuer,
            'order'             => 'asc',
            'cursor'            => $cursor,
            'limit'             => 100,
        ]);

        if (!$response->ok()) {
            throw new \Exception("Horizon trades request failed: " . $response->body());
        }

        $records = $response->json('_embedded.records') ?? [];
        $savedCount = 0;
        $lastPagingToken = null;

        foreach ($records as $trade) {
            $lastPagingToken = $trade['paging_token'];

            // Calculate XLM Value of the trade
            $xlmValue = 0.0;
            if (($trade['counter_asset_type'] ?? null) === 'native') {
                $xlmValue = (float) $trade['counter_amount'];
            } elseif (($trade['base_asset_type'] ?? null) === 'native') {
                $xlmValue = (float) $trade['base_amount'];
            } else {
                // If paired with another asset (e.g. USDC), convert to XLM
                $tokenAmount = (float) $trade['base_amount'];
                $tokenUsdPrice = (float) ($token->current_price_usd ?? 0.0);
                if ($tokenUsdPrice > 0 && $xlmUsdPrice > 0) {
                    $xlmValue = ($tokenAmount * $tokenUsdPrice) / $xlmUsdPrice;
                }
            }

            if ($xlmValue >= $thresholdXlm) {
                // Taker account (executes the trade) is counter_account
                $walletAddress = $trade['counter_account'] ?? ($trade['base_account'] ?? 'Unknown');
                $tokenAmount = (float) $trade['base_amount'];
                $operationId = (int) explode('-', $trade['id'])[0];
                $ledger = $operationId >> 32;

                // Taker bought base if base_is_seller is true, else taker sold base
                $eventType = ($trade['base_is_seller'] ?? false) ? 'BUY' : 'SELL';

                // Fetch transaction hash from Horizon Operation details
                $txHash = null;
                try {
                    $opRes = Http::timeout(4)->get("{$horizonUrl}/operations/{$operationId}");
                    if ($opRes->ok()) {
                        $txHash = $opRes->json('transaction_hash');
                    }
                } catch (\Throwable $e) {
                    Log::warning("Whale activity tracking: failed to fetch op details for {$operationId}: " . $e->getMessage());
                }

                if ($txHash) {
                    // Prevent duplicates using transaction hash
                    $exists = TokenWhaleEvent::where('transaction_hash', $txHash)->exists();
                    if (!$exists) {
                        TokenWhaleEvent::create([
                            'asset_code'       => $code,
                            'asset_issuer'     => $issuer,
                            'transaction_hash' => $txHash,
                            'wallet_address'   => $walletAddress,
                            'event_type'       => $eventType,
                            'token_amount'     => $tokenAmount,
                            'xlm_value'        => $xlmValue,
                            'ledger'           => $ledger,
                        ]);
                        $savedCount++;
                    }
                }
            }
        }

        // Update trades cursor
        if ($lastPagingToken) {
            Setting::updateOrCreate(['key' => $cursorKey], ['value' => $lastPagingToken]);
        }

        return $savedCount;
    }

    /**
     * Process Liquidity Pool Deposits/Withdrawals for a token.
     */
    private function processLpOperations(StellarMarketToken $token, float $thresholdXlm, float $xlmUsdPrice): int
    {
        $code = strtoupper($token->asset_code);
        $issuer = $token->asset_issuer;
        $targetAssetString = "{$code}:{$issuer}";

        $horizonUrl = 'https://horizon.stellar.org';

        // Fetch pools associated with this asset
        $poolsRes = Http::get("{$horizonUrl}/liquidity_pools", [
            'reserves' => $targetAssetString,
            'limit'    => 10,
        ]);

        if (!$poolsRes->ok()) {
            return 0;
        }

        $pools = $poolsRes->json('_embedded.records') ?? [];
        $savedCount = 0;

        foreach ($pools as $pool) {
            $poolId = $pool['id'];
            $cursorKey = "whale_cursor_lp_{$poolId}";
            $cursorSetting = Setting::where('key', $cursorKey)->first();
            $cursor = $cursorSetting ? $cursorSetting->value : null;

            // If no cursor exists, fetch latest LP operation to initialize it
            if (!$cursor) {
                $initRes = Http::get("{$horizonUrl}/liquidity_pools/{$poolId}/operations", [
                    'order' => 'desc',
                    'limit' => 1,
                ]);

                if ($initRes->ok()) {
                    $records = $initRes->json('_embedded.records');
                    if (!empty($records)) {
                        $cursor = $records[0]['paging_token'];
                        Setting::updateOrCreate(['key' => $cursorKey], ['value' => $cursor]);
                    }
                }
                continue;
            }

            // Fetch operations for the pool since cursor
            $response = Http::get("{$horizonUrl}/liquidity_pools/{$poolId}/operations", [
                'order'  => 'asc',
                'cursor' => $cursor,
                'limit'  => 50,
            ]);

            if (!$response->ok()) {
                continue;
            }

            $records = $response->json('_embedded.records') ?? [];
            $lastPagingToken = null;

            foreach ($records as $op) {
                $lastPagingToken = $op['paging_token'];
                $type = $op['type'] ?? '';

                if ($type !== 'liquidity_pool_deposit' && $type !== 'liquidity_pool_withdraw') {
                    continue;
                }

                $eventType = ($type === 'liquidity_pool_deposit') ? 'LP_ADD' : 'LP_REMOVE';
                $reserves = ($eventType === 'LP_ADD') 
                    ? ($op['reserves_deposited'] ?? []) 
                    : ($op['reserves_received'] ?? []);

                if (empty($reserves)) {
                    continue;
                }

                // Extract XLM and token amounts
                $xlmValue = 0.0;
                $tokenAmount = 0.0;

                foreach ($reserves as $res) {
                    $asset = $res['asset'] ?? '';
                    $amount = (float) ($res['amount'] ?? 0.0);

                    if ($asset === 'native') {
                        $xlmValue = $amount;
                    } elseif (str_contains($asset, $targetAssetString)) {
                        $tokenAmount = $amount;
                    }
                }

                // If no XLM reserve exists, calculate XLM value from token amount
                if ($xlmValue === 0.0 && $tokenAmount > 0.0) {
                    $tokenUsdPrice = (float) ($token->current_price_usd ?? 0.0);
                    if ($tokenUsdPrice > 0 && $xlmUsdPrice > 0) {
                        $xlmValue = ($tokenAmount * $tokenUsdPrice) / $xlmUsdPrice;
                    }
                }

                if ($xlmValue >= $thresholdXlm) {
                    $walletAddress = $op['account'] ?? ($op['source_account'] ?? 'Unknown');
                    $txHash = $op['transaction_hash'] ?? null;
                    $ledger = (int) ($op['id'] >> 32);

                    if ($txHash) {
                        $exists = TokenWhaleEvent::where('transaction_hash', $txHash)->exists();
                        if (!$exists) {
                            TokenWhaleEvent::create([
                                'asset_code'       => $code,
                                'asset_issuer'     => $issuer,
                                'transaction_hash' => $txHash,
                                'wallet_address'   => $walletAddress,
                                'event_type'       => $eventType,
                                'token_amount'     => $tokenAmount,
                                'xlm_value'        => $xlmValue,
                                'ledger'           => $ledger,
                            ]);
                            $savedCount++;
                        }
                    }
                }
            }

            // Update LP operations cursor
            if ($lastPagingToken) {
                Setting::updateOrCreate(['key' => $cursorKey], ['value' => $lastPagingToken]);
            }
        }

        return $savedCount;
    }
}

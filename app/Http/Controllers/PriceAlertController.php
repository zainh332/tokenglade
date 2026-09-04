<?php

namespace App\Http\Controllers;

use App\Models\PriceAlert;
use App\Models\PushSubscription;
use App\Models\StellarMarketToken;
use App\Models\StellarToken;
use App\Services\WebPushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PriceAlertController extends Controller
{
    protected WebPushService $pushService;

    public function __construct(WebPushService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Get active and fired alerts for a wallet.
     */
    public function index(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->query('wallet') ?? $request->input('wallet_address') ?? $request->query('wallet_address');

        if (empty($wallet)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Wallet address is required',
            ], 400);
        }

        $alerts = PriceAlert::where('wallet_address', $wallet)
            ->orderBy('created_at', 'desc')
            ->get();

        $enriched = [];
        $issuers = $alerts->pluck('asset_issuer')->unique()->toArray();
        $marketTokens = StellarMarketToken::whereIn('asset_issuer', $issuers)->get()->keyBy(function ($m) {
            return $m->asset_code . ':' . $m->asset_issuer;
        });
        $stellarTokens = StellarToken::whereIn('issuer_public_key', $issuers)->get()->keyBy(function ($s) {
            return $s->asset_code . ':' . $s->issuer_public_key;
        });

        foreach ($alerts as $alert) {
            $key = $alert->asset_code . ':' . $alert->asset_issuer;
            $market = $marketTokens->get($key);
            $stellar = $stellarTokens->get($key);

            $cacheKey = "token_insight_v3_{$alert->asset_issuer}_{$alert->asset_code}";
            $insight = Cache::get($cacheKey);

            $currentXlmPrice = (float)($insight['xlm_price'] ?? ($market?->current_price_xlm ?? 0.0));
            $currentUsdPrice = (float)($insight['usd_price'] ?? ($market?->current_price_usd ?? 0.0));
            $current24hChange = (float)($insight['price_change_24h'] ?? 0.0);
            $image = $insight['image'] ?? ($market?->image ?? ($stellar?->logo ?? null));
            $name = $insight['name'] ?? ($market?->name ?? ($stellar?->name ?? $alert->asset_code));

            $enriched[] = [
                'id' => $alert->id,
                'wallet_address' => $alert->wallet_address,
                'asset_code' => $alert->asset_code,
                'asset_issuer' => $alert->asset_issuer,
                'token_name' => $name,
                'name' => $name,
                'token_image' => $image,
                'image' => $image,
                'logo' => $image,
                'condition_type' => $alert->condition_type,
                'condition_value' => (float)$alert->condition_value,
                'target_value' => (float)$alert->condition_value,
                'currency' => $alert->currency ?? 'xlm',
                'channels' => $alert->channels ?? ['push', 'onsite'],
                'status' => $alert->status,
                'initial_price_xlm' => (float)$alert->initial_price_xlm,
                'initial_price_usd' => (float)$alert->initial_price_usd,
                'current_price_xlm' => $currentXlmPrice,
                'current_price_usd' => $currentUsdPrice,
                'current_price_change_24h' => $current24hChange,
                'fired_at' => $alert->fired_at,
                'created_at' => $alert->created_at,
            ];
        }

        return response()->json([
            'status' => 'success',
            'alerts' => $enriched,
        ]);
    }

    /**
     * Create a new price alert.
     */
    public function store(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->input('wallet_address');
        $issuer = $request->input('asset_issuer');
        $code = $request->input('asset_code');
        $conditionType = $request->input('condition_type');
        $conditionValue = $request->input('condition_value') ?? $request->input('target_value');
        $currency = strtolower($request->input('currency', 'xlm'));
        if (!in_array($currency, ['xlm', 'usd'])) {
            $currency = 'xlm';
        }
        $channels = $request->input('channels', ['push', 'onsite']);
        $initialUsd = $request->input('initial_price_usd') ?? $request->input('reference_price_usd');
        $initialXlm = $request->input('initial_price_xlm') ?? $request->input('reference_price_xlm');

        if (empty($wallet) || empty($issuer) || empty($code) || empty($conditionType) || $conditionValue === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'wallet, asset_issuer, asset_code, condition_type and target_value are required.',
            ], 422);
        }

        $alert = PriceAlert::create([
            'wallet_address' => $wallet,
            'asset_issuer' => $issuer,
            'asset_code' => $code,
            'condition_type' => $conditionType,
            'condition_value' => (float)$conditionValue,
            'currency' => $currency,
            'channels' => is_array($channels) ? $channels : ['push', 'onsite'],
            'status' => 'active',
            'initial_price_xlm' => $initialXlm,
            'initial_price_usd' => $initialUsd,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Price alert created successfully',
            'alert' => $alert,
        ]);
    }

    /**
     * Delete an alert.
     */
    public function destroy($id)
    {
        PriceAlert::where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Alert removed',
        ]);
    }

    /**
     * Save browser push subscription tied to a wallet.
     */
    public function saveSubscription(Request $request)
    {
        $wallet = $request->input('wallet') ?? $request->input('wallet_address');
        $endpoint = $request->input('endpoint');
        $keys = $request->input('keys', []);

        if (empty($wallet) || empty($endpoint)) {
            return response()->json([
                'status' => 'error',
                'message' => 'wallet and endpoint are required.',
            ], 422);
        }

        $sub = PushSubscription::updateOrCreate(
            ['endpoint' => $endpoint],
            [
                'wallet_address' => $wallet,
                'p256dh' => $keys['p256dh'] ?? null,
                'auth' => $keys['auth'] ?? null,
                'user_agent' => $request->userAgent(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'subscription' => $sub,
        ]);
    }

    /**
     * Get VAPID public key.
     */
    public function getVapidPublicKey()
    {
        $publicKey = $this->pushService->getPublicKey();

        return response()->json([
            'status' => 'success',
            'public_key' => $publicKey,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\LiquidityPoolParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Soneso\StellarSDK\StellarSDK;

class LpSyncService
{
    private string $poolIdBase32 = 'LDFRSITIDSOSHAGTIV35HQCW4Q22QQ3FQ3TXNQ4KQBASCIGCIQX3KX4K';
    private string $poolIdHex = 'cb1922681c9d2380d34577d3c056e435a8436586e776c38a80412120c2442fb5';
    private StellarSDK $sdk;
    private bool $isTestnet;
    private string $assetCode;
    private string $tkgIssuer;

    public function __construct()
    {
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT', 'public');
        $this->isTestnet = strtolower($stellarEnv) !== 'public';

        if ($this->isTestnet) {
            $this->sdk = StellarSDK::getTestNetInstance();
            $this->tkgIssuer = env('TKG_ISSUER_TESTNET');
        } else {
            $this->sdk = StellarSDK::getPublicNetInstance();
            $this->tkgIssuer = env('TKG_ISSUER_PUBLIC');
        }
        $this->assetCode = env('ASSET_CODE', 'TKG');
    }

    /**
     * Synchronize liquidity pool participants with Horizon and stellar.expert APIs.
     */
    public function sync(): array
    {
        try {
            // 1. Fetch pool details from Horizon to get reserves and total shares
            $horizonUrl = $this->isTestnet
                ? 'https://horizon-testnet.stellar.org'
                : 'https://horizon.stellar.org';

            $poolResponse = Http::timeout(10)->acceptJson()->get("{$horizonUrl}/liquidity_pools/{$this->poolIdHex}");

            if ($poolResponse->failed()) {
                throw new \RuntimeException("Failed to fetch pool details from Horizon: {$poolResponse->status()}");
            }

            $poolData = $poolResponse->json();
            $totalShares = (float) ($poolData['total_shares'] ?? 0);
            
            if ($totalShares <= 0) {
                throw new \RuntimeException("Pool has zero or invalid total shares.");
            }

            $xlmReserve = 0.0;
            $tkgReserve = 0.0;

            foreach ($poolData['reserves'] as $reserve) {
                if ($reserve['asset'] === 'native') {
                    $xlmReserve = (float) $reserve['amount'];
                } else {
                    // Extract code and issuer from the asset string "CODE:ISSUER"
                    $parts = explode(':', $reserve['asset']);
                    $code = $parts[0] ?? '';
                    $issuer = $parts[1] ?? '';
                    if ($code === $this->assetCode && $issuer === $this->tkgIssuer) {
                        $tkgReserve = (float) $reserve['amount'];
                    }
                }
            }

            // 2. Fetch holders from stellar.expert API
            $expertNetwork = $this->isTestnet ? 'testnet' : 'public';
            $expertUrl = "https://api.stellar.expert/explorer/{$expertNetwork}/liquidity-pool/{$this->poolIdBase32}/holders";
            
            $holdersResponse = Http::timeout(10)->acceptJson()->get($expertUrl);

            if ($holdersResponse->failed()) {
                throw new \RuntimeException("Failed to fetch holders from stellar.expert: {$holdersResponse->status()}");
            }

            $holdersData = $holdersResponse->json();
            $records = $holdersData['_embedded']['records'] ?? [];

            $syncedAddresses = [];

            foreach ($records as $record) {
                $walletAddress = $record['account'] ?? $record['address'] ?? null;
                if (!$walletAddress) {
                    continue;
                }

                // Balance is in stroops (multiply by 10^-7 to get standard unit shares)
                $walletShares = (float) $record['balance'] / 10000000.0;

                // Calculate proportional XLM and TKG amounts
                $shareRatio = $walletShares / $totalShares;
                $tkgAmount = $tkgReserve * $shareRatio;
                $xlmAmount = $xlmReserve * $shareRatio;

                // Check active status in our database (users table)
                $isActive = User::where('public_key', $walletAddress)->where('status', 1)->exists();

                // Check on-chain active status (Horizon request)
                $walletStatus = 'inactive';
                try {
                    $account = $this->sdk->requestAccount($walletAddress);
                    if ($account) {
                        $walletStatus = 'active';
                    }
                } catch (\Throwable $e) {
                    $walletStatus = 'inactive';
                }

                // Update or create participant record in DB
                LiquidityPoolParticipant::updateOrCreate(
                    ['wallet_address' => $walletAddress],
                    [
                        'pool_shares' => $walletShares,
                        'tkg_amount' => $tkgAmount,
                        'xlm_amount' => $xlmAmount,
                        'is_active' => $isActive,
                        'wallet_status' => $walletStatus,
                    ]
                );

                $syncedAddresses[] = $walletAddress;
            }

            // 3. Mark any existing records not in the current holders list as inactive/zeroed
            LiquidityPoolParticipant::whereNotIn('wallet_address', $syncedAddresses)
                ->update([
                    'pool_shares' => 0.0,
                    'tkg_amount' => 0.0,
                    'xlm_amount' => 0.0,
                    'is_active' => false,
                    'wallet_status' => 'inactive'
                ]);

            return [
                'status' => 'success',
                'message' => 'Liquidity pool participants synced successfully.',
                'synced_count' => count($syncedAddresses),
                'total_pool_shares' => $totalShares,
                'total_xlm_reserve' => $xlmReserve,
                'total_tkg_reserve' => $tkgReserve,
            ];

        } catch (\Throwable $e) {
            Log::error('[LpSyncService] Sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Sync failed: ' . $e->getMessage(),
            ];
        }
    }
}

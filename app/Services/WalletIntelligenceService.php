<?php

namespace App\Services;

use App\Models\StellarMarketToken;
use App\Models\StellarToken;
use App\Models\ProjectOfficialWallet;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Throwable;

class WalletIntelligenceService
{
    protected string $horizonUrl;
    protected StellarTokenService $tokenService;
    protected bool $isTestnet;

    public function __construct(StellarTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT', 'public');
        $this->isTestnet = strtolower($stellarEnv) !== 'public';
        $this->horizonUrl = $this->isTestnet
            ? 'https://horizon-testnet.stellar.org'
            : 'https://horizon.stellar.org';
    }

    /**
     * Fetch current account info from Stellar Horizon with 5s short cache to deduplicate simultaneous requests.
     */
    public function fetchCurrentWalletState(string $address): array
    {
        $cacheKey = "horizon_account_state_{$address}";
        return Cache::remember($cacheKey, 5, function () use ($address) {
            try {
                $response = $this->sendHorizonRequest('GET', "accounts/{$address}", [], 5, 1);
                
                if ($response->status() === 404) {
                    // Secondary check against StellarExpert account API before concluding not found
                    try {
                        $seAcc = Http::timeout(4)->get("https://api.stellar.expert/explorer/public/account/{$address}");
                        if ($seAcc->ok()) {
                            $seData = $seAcc->json();
                            $parsedBalances = [];
                            foreach ($seData['balances'] ?? [] as $sb) {
                                $asset = $sb['asset'] ?? '';
                                if ($asset === 'native') {
                                    $parsedBalances[] = [
                                        'asset_type' => 'native',
                                        'balance' => (string)(($sb['amount'] ?? 0) / 10000000),
                                    ];
                                } else {
                                    $parts = explode('-', $asset);
                                    $code = $parts[0] ?? '';
                                    $issuer = $parts[1] ?? '';
                                    $parsedBalances[] = [
                                        'asset_type' => strlen($code) <= 4 ? 'credit_alphanum4' : 'credit_alphanum12',
                                        'asset_code' => $code,
                                        'asset_issuer' => $issuer,
                                        'balance' => (string)(($sb['amount'] ?? 0) / 10000000),
                                    ];
                                }
                            }

                            return [
                                'active' => true,
                                'balances' => $parsedBalances,
                                'claimable_balances' => [],
                                'sequence' => (int) ($seData['sequence'] ?? 0),
                                'subentry_count' => (int) ($seData['subentry_count'] ?? 0),
                                'signers' => $seData['signers'] ?? [],
                                'thresholds' => $seData['thresholds'] ?? [],
                                'flags' => $seData['flags'] ?? [],
                                'home_domain' => $seData['home_domain'] ?? null,
                                'inflation_destination' => $seData['inflation_destination'] ?? null,
                                'num_assets' => count($parsedBalances),
                            ];
                        }
                    } catch (Throwable $e) {}

                    return [
                        'active' => false,
                        'balances' => [],
                        'claimable_balances' => [],
                        'sequence' => 0,
                        'subentry_count' => 0,
                        'signers' => [],
                        'thresholds' => [],
                        'flags' => [],
                        'home_domain' => null,
                        'inflation_destination' => null,
                        'num_assets' => 0,
                    ];
                }

                if (!$response->ok()) {
                    throw new \RuntimeException("Horizon accounts fetch failed: " . $response->body());
                }

                $data = $response->json();
                
                // Fetch claimable balances where wallet is a claimant (fast timeout)
                $claimableBalances = [];
                try {
                    $cbResponse = $this->sendHorizonRequest('GET', "claimable_balances", [
                        'claimant' => $address,
                        'limit' => 200,
                    ], 3, 0);
                    if ($cbResponse->ok()) {
                        $claimableBalances = $cbResponse->json('_embedded.records') ?? [];
                    }
                } catch (Throwable $e) {
                    Log::warning("Failed to fetch claimable balances for {$address}: " . $e->getMessage());
                }

                $balances = $data['balances'] ?? [];
                $rawSigners = $data['signers'] ?? [];
                usort($rawSigners, function ($a, $b) use ($address) {
                    if (($a['key'] ?? '') === $address) return -1;
                    if (($b['key'] ?? '') === $address) return 1;
                    return ($b['weight'] ?? 0) <=> ($a['weight'] ?? 0);
                });
                
                return [
                    'active' => true,
                    'balances' => $balances,
                    'claimable_balances' => $claimableBalances,
                    'sequence' => (int) ($data['sequence'] ?? 0),
                    'subentry_count' => (int) ($data['subentry_count'] ?? 0),
                    'signers' => $rawSigners,
                    'thresholds' => $data['thresholds'] ?? [],
                    'flags' => $data['flags'] ?? [],
                    'home_domain' => $data['home_domain'] ?? null,
                    'inflation_destination' => $data['inflation_destination'] ?? null,
                    'num_assets' => count($balances),
                ];
            } catch (Throwable $e) {
                Log::error("Error fetching current wallet state for {$address}: " . $e->getMessage());
                throw $e;
            }
        });
    }

    /**
     * Valuate a Liquidity Pool Share holding in real-time.
     */
    public function valuateLpShare(string $poolId, float $userShares, float $xlmUsdPrice): array
    {
        if (!$poolId || $userShares <= 0.0) {
            return [
                'price_xlm' => 0.0,
                'price_usd' => 0.0,
                'value_xlm' => 0.0,
                'value_usd' => 0.0,
                'assets_desc' => 'LP',
            ];
        }

        $cacheKey = "lp_pool_val_{$poolId}";
        $poolData = Cache::remember($cacheKey, 300, function () use ($poolId) {
            try {
                $response = $this->sendHorizonRequest('GET', "liquidity_pools/{$poolId}", [], 3, 0);
                return $response->ok() ? $response->json() : null;
            } catch (Throwable $e) {
                return null;
            }
        });

        if (!$poolData || empty($poolData['reserves'])) {
            return [
                'price_xlm' => 0.0,
                'price_usd' => 0.0,
                'value_xlm' => 0.0,
                'value_usd' => 0.0,
                'assets_desc' => 'LP',
            ];
        }

        $totalShares = (float) ($poolData['total_shares'] ?? 0.0);
        if ($totalShares <= 0.0) {
            return [
                'price_xlm' => 0.0,
                'price_usd' => 0.0,
                'value_xlm' => 0.0,
                'value_usd' => 0.0,
                'assets_desc' => 'LP',
            ];
        }

        $totalPoolValueXlm = 0.0;
        $reserveAssets = [];
        foreach ($poolData['reserves'] as $reserve) {
            $amount = (float) $reserve['amount'];
            $assetStr = $reserve['asset'];

            if ($assetStr === 'native') {
                $totalPoolValueXlm += $amount;
                $reserveAssets[] = 'XLM';
            } else {
                $parts = explode(':', $assetStr);
                $resCode = $parts[0] ?? '';
                $resIssuer = $parts[1] ?? '';
                $reserveAssets[] = $resCode;
                
                // Lookup market price
                $marketToken = StellarMarketToken::where('asset_code', strtoupper($resCode))
                    ->where('asset_issuer', $resIssuer)
                    ->first();
                $priceXlm = $marketToken ? (float) ($marketToken->current_price_xlm ?? 0.0) : 0.0;
                $totalPoolValueXlm += ($amount * $priceXlm);
            }
        }

        $sharePriceXlm = $totalPoolValueXlm / $totalShares;
        $sharePriceUsd = $sharePriceXlm * $xlmUsdPrice;

        return [
            'price_xlm' => $sharePriceXlm,
            'price_usd' => $sharePriceUsd,
            'value_xlm' => $userShares * $sharePriceXlm,
            'value_usd' => $userShares * $sharePriceUsd,
            'assets_desc' => implode(' / ', $reserveAssets) ?: 'LP',
        ];
    }

    /**
     * Fetch and valuate all holdings for a wallet in real-time with bulk queries (instant performance).
     */
    public function getWalletHoldings(string $address, ?array $prefetchedState = null): array
    {
        $state = $prefetchedState ?? $this->fetchCurrentWalletState($address);
        if (!$state['active']) {
            return [];
        }

        $xlmUsdPrice = $this->tokenService->getXlmUsdPrice();
        
        // 1. Gather all unique asset codes to do fast BULK DB queries (0 HTTP calls per asset)
        $assetCodes = [];
        foreach ($state['balances'] as $bal) {
            if (!empty($bal['asset_code'])) {
                $assetCodes[] = strtoupper($bal['asset_code']);
            }
        }
        foreach ($state['claimable_balances'] as $cb) {
            $assetStr = $cb['asset'] ?? '';
            if ($assetStr && $assetStr !== 'native') {
                $parts = explode(':', $assetStr);
                if (!empty($parts[0])) {
                    $assetCodes[] = strtoupper($parts[0]);
                }
            }
        }
        $assetCodes = array_unique(array_filter($assetCodes));

        // Bulk load market prices from DB
        $marketTokensMap = [];
        if (!empty($assetCodes)) {
            $marketTokens = StellarMarketToken::whereIn('asset_code', $assetCodes)->get();
            foreach ($marketTokens as $mt) {
                $key = strtoupper($mt->asset_code) . ':' . $mt->asset_issuer;
                $marketTokensMap[$key] = [
                    'price_xlm' => (float) ($mt->current_price_xlm ?? 0.0),
                    'price_usd' => (float) ($mt->current_price_usd ?: (($mt->current_price_xlm ?? 0.0) * $xlmUsdPrice)),
                ];
            }
        }

        // Bulk load verified token logos from DB (exact asset_code:issuer match)
        $dbTokensMap = [];
        if (!empty($assetCodes)) {
            $dbTokens = StellarToken::whereIn('asset_code', $assetCodes)
                ->whereNotNull('logo')
                ->get();
            foreach ($dbTokens as $t) {
                $dbTokensMap[strtoupper($t->asset_code) . ':' . $t->issuer_public_key] = $t->logo;
            }

            foreach ($marketTokens as $mt) {
                if (!empty($mt->image) && !empty($mt->asset_issuer)) {
                    $key = strtoupper($mt->asset_code) . ':' . $mt->asset_issuer;
                    if (!isset($dbTokensMap[$key])) {
                        $dbTokensMap[$key] = $mt->image;
                    }
                }
            }
        }

        $holdingsData = [];
        $totalValUsd = 0.0;
        $totalValXlm = 0.0;

        // 2. Process regular balances
        foreach ($state['balances'] as $bal) {
            $assetType = $bal['asset_type'];
            $balance = (float) $bal['balance'];
            
            // Exclude native XLM if balance is 0 or negative
            if ($assetType === 'native' && $balance <= 0.0) continue;

            if ($assetType === 'native') {
                $valXlm = $balance;
                $valUsd = $balance * $xlmUsdPrice;
                $holdingsData[] = [
                    'id' => 'native_xlm',
                    'asset_type' => 'native',
                    'asset_code' => 'XLM',
                    'asset_issuer' => '',
                    'balance' => $balance,
                    'price_xlm' => 1.0,
                    'price_usd' => $xlmUsdPrice,
                    'value_xlm' => $valXlm,
                    'value_usd' => $valUsd,
                    'pool_id' => '',
                    'limit' => null,
                    'is_authorized' => true,
                    'is_authorized_to_maintain_liabilities' => true,
                    'is_clawback_enabled' => false,
                    'logo_url' => null,
                ];
                $totalValXlm += $valXlm;
                $totalValUsd += $valUsd;
            } elseif ($assetType === 'liquidity_pool_shares') {
                if ($balance <= 0.0) continue;
                $poolId = $bal['liquidity_pool_id'] ?? '';
                $lpVal = $this->valuateLpShare($poolId, $balance, $xlmUsdPrice);
                $holdingsData[] = [
                    'id' => 'lp_' . $poolId,
                    'asset_type' => 'liquidity_pool_shares',
                    'asset_code' => $lpVal['assets_desc'] ?? 'LP',
                    'asset_issuer' => '',
                    'balance' => $balance,
                    'price_xlm' => $lpVal['price_xlm'],
                    'price_usd' => $lpVal['price_usd'],
                    'value_xlm' => $lpVal['value_xlm'],
                    'value_usd' => $lpVal['value_usd'],
                    'pool_id' => $poolId,
                    'limit' => isset($bal['limit']) ? (float) $bal['limit'] : null,
                    'is_authorized' => isset($bal['is_authorized']) ? (bool) $bal['is_authorized'] : true,
                    'is_authorized_to_maintain_liabilities' => isset($bal['is_authorized_to_maintain_liabilities']) ? (bool) $bal['is_authorized_to_maintain_liabilities'] : true,
                    'is_clawback_enabled' => isset($bal['is_clawback_enabled']) ? (bool) $bal['is_clawback_enabled'] : false,
                    'logo_url' => null,
                ];
                $totalValXlm += $lpVal['value_xlm'];
                $totalValUsd += $lpVal['value_usd'];
            } else {
                $code = $bal['asset_code'] ?? '';
                $issuer = $bal['asset_issuer'] ?? '';
                $mapKey = strtoupper($code) . ':' . $issuer;

                $priceXlm = 0.0;
                $priceUsd = 0.0;
                if (isset($marketTokensMap[$mapKey])) {
                    $priceXlm = $marketTokensMap[$mapKey]['price_xlm'];
                    $priceUsd = $marketTokensMap[$mapKey]['price_usd'];
                }

                $valXlm = $balance * $priceXlm;
                $valUsd = $balance * $priceUsd;

                // Resolve logo fast (from map or cache in memory, no remote HTTP)
                $logoUrl = $dbTokensMap[$mapKey] ?? Cache::get("asset_logo_{$code}_{$issuer}");

                $holdingsData[] = [
                    'id' => "asset_{$code}_{$issuer}",
                    'asset_type' => $assetType,
                    'asset_code' => $code,
                    'asset_issuer' => $issuer,
                    'balance' => $balance,
                    'price_xlm' => $priceXlm,
                    'price_usd' => $priceUsd,
                    'value_xlm' => $valXlm,
                    'value_usd' => $valUsd,
                    'pool_id' => '',
                    'limit' => isset($bal['limit']) ? (float) $bal['limit'] : null,
                    'is_authorized' => isset($bal['is_authorized']) ? (bool) $bal['is_authorized'] : true,
                    'is_authorized_to_maintain_liabilities' => isset($bal['is_authorized_to_maintain_liabilities']) ? (bool) $bal['is_authorized_to_maintain_liabilities'] : true,
                    'is_clawback_enabled' => isset($bal['is_clawback_enabled']) ? (bool) $bal['is_clawback_enabled'] : false,
                    'logo_url' => $logoUrl,
                ];
                $totalValXlm += $valXlm;
                $totalValUsd += $valUsd;
            }
        }

        // 3. Process claimable balances
        foreach ($state['claimable_balances'] as $cb) {
            $amount = (float) ($cb['amount'] ?? 0.0);
            if ($amount <= 0.0) continue;

            $assetStr = $cb['asset'] ?? '';
            $priceXlm = 0.0;
            $priceUsd = 0.0;
            $assetCode = 'XLM';
            $assetIssuer = '';
            $assetType = 'native';

            if ($assetStr !== 'native') {
                $parts = explode(':', $assetStr);
                $assetCode = $parts[0] ?? '';
                $assetIssuer = $parts[1] ?? '';
                $assetType = strlen($assetCode) <= 4 ? 'credit_alphanum4' : 'credit_alphanum12';
                $mapKey = strtoupper($assetCode) . ':' . $assetIssuer;
                if (isset($marketTokensMap[$mapKey])) {
                    $priceXlm = $marketTokensMap[$mapKey]['price_xlm'];
                    $priceUsd = $marketTokensMap[$mapKey]['price_usd'];
                }
            } else {
                $priceXlm = 1.0;
                $priceUsd = $xlmUsdPrice;
            }

            $valXlm = $amount * $priceXlm;
            $valUsd = $amount * $priceUsd;

            $mapKey = strtoupper($assetCode) . ':' . $assetIssuer;
            $logoUrl = ($assetCode === 'XLM') ? null : ($dbTokensMap[$mapKey] ?? Cache::get("asset_logo_{$assetCode}_{$assetIssuer}"));

            $holdingsData[] = [
                'id' => 'cb_' . ($cb['id'] ?? uniqid()),
                'asset_type' => 'claimable_balance',
                'asset_code' => $assetCode,
                'asset_issuer' => $assetIssuer,
                'balance' => $amount,
                'price_xlm' => $priceXlm,
                'price_usd' => $priceUsd,
                'value_xlm' => $valXlm,
                'value_usd' => $valUsd,
                'pool_id' => $cb['id'] ?? '',
                'limit' => null,
                'is_authorized' => true,
                'is_authorized_to_maintain_liabilities' => true,
                'is_clawback_enabled' => false,
                'logo_url' => $logoUrl,
            ];
            $totalValXlm += $valXlm;
            $totalValUsd += $valUsd;
        }

        // Calculate allocation percentages
        foreach ($holdingsData as &$hold) {
            $hold['allocation_percentage'] = $totalValUsd > 0 ? ($hold['value_usd'] / $totalValUsd) * 100 : 0.0;
        }

        return $holdingsData;
    }

    /**
     * Get complete live overview for a wallet from Horizon in milliseconds.
     */
    public function getWalletOverview(string $address): array
    {
        $state = $this->fetchCurrentWalletState($address);
        if (!$state['active']) {
            throw new \RuntimeException("Wallet not found on the Stellar network.");
        }

        $holdings = $this->getWalletHoldings($address, $state);

        $regularHoldings = array_filter($holdings, fn($h) => $h['asset_type'] !== 'claimable_balance');
        $claimableHoldings = array_filter($holdings, fn($h) => $h['asset_type'] === 'claimable_balance');

        $portfolioValueUsd = array_sum(array_column($regularHoldings, 'value_usd'));
        $portfolioValueXlm = array_sum(array_column($regularHoldings, 'value_xlm'));

        $liquidHoldings = array_filter($regularHoldings, fn($h) => $h['asset_type'] !== 'liquidity_pool_shares');
        $lpHoldings = array_filter($regularHoldings, fn($h) => $h['asset_type'] === 'liquidity_pool_shares');

        $assetsHeld = count(array_filter($liquidHoldings, fn($h) => $h['balance'] > 0));
        $trustlinesCount = count(array_filter($liquidHoldings, fn($h) => $h['asset_type'] !== 'native'));
        $poolsCount = count(array_filter($lpHoldings, fn($h) => $h['balance'] > 0));

        $xlmBalance = 0.0;
        foreach ($regularHoldings as $h) {
            if ($h['asset_type'] === 'native') {
                $xlmBalance = (float) $h['balance'];
                break;
            }
        }

        $claimableCount = count($claimableHoldings);
        $claimableValueUsd = array_sum(array_column($claimableHoldings, 'value_usd'));

        $isConnected = User::where('public_key', $address)->where('status', 1)->exists();
        $isOfficial = ProjectOfficialWallet::where('wallet_address', $address)->exists();

        return [
            'wallet_address' => $address,
            'portfolio_value_xlm' => (float) $portfolioValueXlm,
            'portfolio_value_usd' => (float) $portfolioValueUsd,
            'xlm_balance' => (float) $xlmBalance,
            'asset_count' => (int) $assetsHeld,
            'assets_held' => (int) $assetsHeld,
            'trustlines_count' => (int) $trustlinesCount,
            'pools_count' => (int) $poolsCount,
            'claimable_count' => (int) $claimableCount,
            'claimable_value_usd' => (float) $claimableValueUsd,
            'is_connected_wallet' => $isConnected,
            'is_official_wallet' => $isOfficial,
            'sequence' => $state['sequence'],
            'subentry_count' => $state['subentry_count'],
            'signers' => $state['signers'],
            'thresholds' => $state['thresholds'],
            'flags' => $state['flags'],
            'home_domain' => $state['home_domain'],
            'inflation_destination' => $state['inflation_destination'],
            'tracking_status' => $isConnected || $isOfficial ? 'ACTIVE' : 'READY',
            'historical_index_complete' => true,
            'indexing_status' => 'ready',
        ];
    }

    /**
     * Get live activity / operations directly from Horizon in real-time.
     */
    public function getWalletActivity(string $address, ?string $cursor = null, int $limit = 10, string $type = 'all'): array
    {
        $limit = min(max($limit, 5), 100);
        $xlmUsdPrice = $this->tokenService->getXlmUsdPrice();

        $params = [
            'order' => 'desc',
            'limit' => $limit,
            'include_failed' => 'false',
        ];
        if ($cursor) {
            $params['cursor'] = $cursor;
        }

        $endpoint = "accounts/{$address}/operations";
        if ($type === 'payments') {
            $endpoint = "accounts/{$address}/payments";
        } elseif ($type === 'trades') {
            $endpoint = "accounts/{$address}/trades";
        }

        $response = $this->sendHorizonRequest('GET', $endpoint, $params, 5, 1);

        if ($response->status() === 404) {
            return [
                'records' => [],
                'next_cursor' => null,
                'prev_cursor' => null,
                'has_more' => false,
            ];
        }

        if (!$response->ok()) {
            throw new \RuntimeException("Failed to fetch operations from Horizon: " . $response->body());
        }

        $rawRecords = $response->json('_embedded.records') ?? [];
        $events = [];

        foreach ($rawRecords as $raw) {
            if ($type === 'trades' || isset($raw['base_asset_code']) || isset($raw['counter_asset_code'])) {
                $normalized = $this->normalizeTrade($address, $raw, $xlmUsdPrice);
            } else {
                $normalized = $this->normalizeOperation($address, $raw, $xlmUsdPrice);
            }

            if ($normalized) {
                $events[] = $normalized;
            }
        }

        $nextCursor = null;
        $prevCursor = null;
        if (!empty($rawRecords)) {
            $lastRecord = end($rawRecords);
            $nextCursor = $lastRecord['paging_token'] ?? null;
            $firstRecord = reset($rawRecords);
            $prevCursor = $firstRecord['paging_token'] ?? null;
        }

        return [
            'records' => $events,
            'next_cursor' => $nextCursor,
            'prev_cursor' => $prevCursor,
            'has_more' => count($rawRecords) === $limit,
        ];
    }

    /**
     * Normalize operations into clean human-readable event objects.
     */
    protected function normalizeOperation(string $address, array $op, float $xlmUsdPrice): ?array
    {
        $type = $op['type'] ?? '';
        $opId = (string) ($op['id'] ?? '');
        $ledger = (int) ($op['ledger'] ?? 0);
        $txHash = $op['transaction_hash'] ?? '';
        $occurredAt = isset($op['created_at']) ? Carbon::parse($op['created_at'])->toIso8601String() : now()->toIso8601String();
        $pagingToken = $op['paging_token'] ?? $opId;

        $event = [
            'id' => $opId ?: uniqid(),
            'paging_token' => $pagingToken,
            'wallet_address' => $address,
            'transaction_hash' => $txHash,
            'operation_id' => $opId,
            'ledger' => $ledger,
            'occurred_at' => $occurredAt,
            'event_type' => 'OTHER',
            'asset_code' => null,
            'asset_issuer' => null,
            'counter_asset_code' => null,
            'counter_asset_issuer' => null,
            'amount' => null,
            'counter_amount' => null,
            'value_xlm' => null,
            'value_usd' => null,
            'counterparty_address' => null,
        ];

        switch ($type) {
            case 'create_account':
                $funder = $op['funder'] ?? '';
                $account = $op['account'] ?? '';
                $amount = (float) ($op['starting_balance'] ?? 0.0);
                
                $event['asset_code'] = 'XLM';
                $event['amount'] = $amount;
                $event['value_xlm'] = $amount;
                $event['value_usd'] = $amount * $xlmUsdPrice;

                if ($account === $address) {
                    $event['event_type'] = 'PAYMENT_IN';
                    $event['counterparty_address'] = $funder;
                } else {
                    $event['event_type'] = 'PAYMENT_OUT';
                    $event['counterparty_address'] = $account;
                }
                break;

            case 'payment':
                $from = $op['from'] ?? '';
                $to = $op['to'] ?? '';
                $amount = (float) ($op['amount'] ?? 0.0);
                $assetCode = $op['asset_code'] ?? 'XLM';
                $assetIssuer = $op['asset_issuer'] ?? '';
                $assetType = $op['asset_type'] ?? 'native';

                $priceXlm = ($assetCode === 'XLM') ? 1.0 : 0.0;
                $priceUsd = ($assetCode === 'XLM') ? $xlmUsdPrice : 0.0;

                $event['asset_code'] = $assetCode;
                $event['asset_issuer'] = $assetIssuer;
                $event['amount'] = $amount;
                $event['value_xlm'] = $amount * $priceXlm;
                $event['value_usd'] = $amount * $priceUsd;

                if ($to === $address) {
                    $event['event_type'] = 'PAYMENT_IN';
                    $event['counterparty_address'] = $from;
                } else {
                    $event['event_type'] = 'PAYMENT_OUT';
                    $event['counterparty_address'] = $to;
                }
                break;

            case 'account_merge':
                $mergedAccount = $op['source_account'] ?? ($op['account'] ?? '');
                $into = $op['into'] ?? '';
                
                $event['asset_code'] = 'XLM';

                if ($mergedAccount === $address) {
                    $event['event_type'] = 'ACCOUNT_MERGE';
                    $event['counterparty_address'] = $into;
                } else {
                    $event['event_type'] = 'PAYMENT_IN';
                    $event['counterparty_address'] = $mergedAccount;
                }
                break;

            case 'path_payment_strict_receive':
            case 'path_payment_strict_send':
                $from = $op['from'] ?? '';
                $to = $op['to'] ?? '';
                
                $destAssetCode = $op['asset_code'] ?? 'XLM';
                $destAssetIssuer = $op['asset_issuer'] ?? '';
                $destAmount = (float) ($op['amount'] ?? 0.0);

                $srcAssetCode = $op['source_asset_code'] ?? 'XLM';
                $srcAssetIssuer = $op['source_asset_issuer'] ?? '';
                $srcAmount = (float) ($op['source_amount'] ?? $op['amount'] ?? 0.0);

                if ($from === $address && $to === $address) {
                    $event['event_type'] = 'BUY';
                    $event['asset_code'] = $destAssetCode;
                    $event['asset_issuer'] = $destAssetIssuer;
                    $event['amount'] = $destAmount;
                    $event['counter_asset_code'] = $srcAssetCode;
                    $event['counter_asset_issuer'] = $srcAssetIssuer;
                    $event['counter_amount'] = $srcAmount;
                } elseif ($to === $address) {
                    $event['event_type'] = 'PAYMENT_IN';
                    $event['asset_code'] = $destAssetCode;
                    $event['asset_issuer'] = $destAssetIssuer;
                    $event['amount'] = $destAmount;
                    $event['counterparty_address'] = $from;
                } else {
                    $event['event_type'] = 'PAYMENT_OUT';
                    $event['asset_code'] = $srcAssetCode;
                    $event['asset_issuer'] = $srcAssetIssuer;
                    $event['amount'] = $srcAmount;
                    $event['counterparty_address'] = $to;
                }
                break;

            case 'change_trust':
                $limit = (float) ($op['limit'] ?? 0.0);
                $assetCode = $op['asset_code'] ?? '';
                $assetIssuer = $op['asset_issuer'] ?? '';

                $event['event_type'] = ($limit === 0.0) ? 'TRUSTLINE_REMOVE' : 'TRUSTLINE_ADD';
                $event['asset_code'] = $assetCode;
                $event['asset_issuer'] = $assetIssuer;
                break;

            case 'claim_claimable_balance':
                $event['event_type'] = 'CLAIMABLE_BALANCE_CLAIM';
                $event['counterparty_address'] = $op['claimant'] ?? null;
                break;

            case 'create_claimable_balance':
                $amount = (float) ($op['amount'] ?? 0.0);
                $assetStr = $op['asset'] ?? 'native';
                $assetCode = 'XLM';
                $assetIssuer = '';

                if ($assetStr !== 'native') {
                    $parts = explode(':', $assetStr);
                    $assetCode = $parts[0] ?? 'XLM';
                    $assetIssuer = $parts[1] ?? '';
                }

                $priceXlm = ($assetCode === 'XLM') ? 1.0 : 0.0;
                $priceUsd = ($assetCode === 'XLM') ? $xlmUsdPrice : 0.0;

                if ($assetCode !== 'XLM' && $assetIssuer) {
                    $mt = StellarMarketToken::where('asset_code', strtoupper($assetCode))
                        ->where('asset_issuer', $assetIssuer)
                        ->first();
                    if ($mt) {
                        $priceXlm = (float) ($mt->current_price_xlm ?? 0.0);
                        $priceUsd = (float) ($mt->current_price_usd ?: ($priceXlm * $xlmUsdPrice));
                    }
                }

                $claimants = $op['claimants'] ?? [];
                $dest = null;
                foreach ($claimants as $c) {
                    $d = $c['destination'] ?? '';
                    if ($d && $d !== $address) {
                        $dest = $d;
                        break;
                    }
                }
                if (!$dest && !empty($claimants[0]['destination'])) {
                    $dest = $claimants[0]['destination'];
                }

                $sourceAcc = $op['source_account'] ?? ($op['sponsor'] ?? '');
                $isCreator = ($sourceAcc === $address) || empty($sourceAcc);

                $event['event_type'] = $isCreator ? 'CLAIMABLE_BALANCE_CREATE' : 'CLAIMABLE_BALANCE_RECEIVED';
                $event['asset_code'] = $assetCode;
                $event['asset_issuer'] = $assetIssuer;
                $event['amount'] = $amount;
                $event['value_xlm'] = $amount * $priceXlm;
                $event['value_usd'] = $amount * $priceUsd;
                $event['counterparty_address'] = $dest;
                break;

            case 'clawback':
            case 'clawback_claimable_balance':
                $amount = (float) ($op['amount'] ?? 0.0);
                $assetCode = $op['asset_code'] ?? 'XLM';
                $assetIssuer = $op['asset_issuer'] ?? '';
                $event['event_type'] = 'CLAWBACK';
                $event['asset_code'] = $assetCode;
                $event['asset_issuer'] = $assetIssuer;
                $event['amount'] = $amount;
                $event['counterparty_address'] = $op['from'] ?? null;
                break;

            case 'set_options':
                $event['event_type'] = 'SET_OPTIONS';
                $event['counterparty_address'] = $op['signer_key'] ?? ($op['inflation_dest'] ?? null);
                break;

            case 'manage_data':
                $event['event_type'] = 'MANAGE_DATA';
                $event['asset_code'] = $op['name'] ?? null;
                break;

            case 'bump_sequence':
                $event['event_type'] = 'BUMP_SEQUENCE';
                $event['amount'] = isset($op['bump_to']) ? (float)$op['bump_to'] : null;
                break;

            case 'invoke_host_function':
                $event['event_type'] = 'INVOKE_HOST_FUNCTION';
                $event['counterparty_address'] = $op['function'] ?? null;
                break;

            case 'set_trust_line_flags':
            case 'allow_trust':
                $event['event_type'] = 'TRUSTLINE_FLAGS';
                $event['asset_code'] = $op['asset_code'] ?? null;
                $event['asset_issuer'] = $op['asset_issuer'] ?? null;
                $event['counterparty_address'] = $op['trustor'] ?? null;
                break;

            case 'liquidity_pool_deposit':
                $event['event_type'] = 'LP_ADD';
                $event['asset_code'] = 'LP';
                $event['amount'] = (float) ($op['shares_received'] ?? 0.0);
                break;

            case 'liquidity_pool_withdraw':
                $event['event_type'] = 'LP_REMOVE';
                $event['asset_code'] = 'LP';
                $event['amount'] = (float) ($op['shares'] ?? 0.0);
                break;

            case 'manage_sell_offer':
            case 'manage_buy_offer':
            case 'create_passive_sell_offer':
                $amount = (float) ($op['amount'] ?? 0.0);
                $offerId = $op['offer_id'] ?? '0';

                $sellingAssetCode = $op['selling_asset_code'] ?? 'XLM';
                $buyingAssetCode = $op['buying_asset_code'] ?? 'XLM';

                $event['asset_code'] = $sellingAssetCode;
                $event['counter_asset_code'] = $buyingAssetCode;
                $event['amount'] = $amount;

                if ($amount === 0.0) {
                    $event['event_type'] = 'OFFER_CANCEL';
                } elseif ($offerId === '0' || $offerId === 0) {
                    $event['event_type'] = 'OFFER_CREATE';
                } else {
                    $event['event_type'] = 'OFFER_UPDATE';
                }
                break;
        }

        return $event;
    }

    /**
     * Normalize trades into clean human-readable event objects.
     */
    protected function normalizeTrade(string $address, array $trade, float $xlmUsdPrice): ?array
    {
        $baseAccount = $trade['base_account'] ?? '';
        $counterAccount = $trade['counter_account'] ?? '';
        $baseIsSeller = (bool) ($trade['base_is_seller'] ?? false);

        $baseCode = $trade['base_asset_code'] ?? 'XLM';
        $baseIssuer = $trade['base_asset_issuer'] ?? '';
        $baseAmount = (float) ($trade['base_amount'] ?? 0.0);

        $counterCode = $trade['counter_asset_code'] ?? 'XLM';
        $counterIssuer = $trade['counter_asset_issuer'] ?? '';
        $counterAmount = (float) ($trade['counter_amount'] ?? 0.0);

        $tradeId = $trade['id'] ?? '';
        $opId = $trade['operation_id'] ?? '';
        $txHash = $trade['transaction_hash'] ?? '';
        $occurredAt = isset($trade['ledger_close_time']) ? Carbon::parse($trade['ledger_close_time'])->toIso8601String() : now()->toIso8601String();
        $ledger = (int) ($opId ? ((int)$opId) >> 32 : 0);

        $isBuy = false;
        if ($baseAccount === $address) {
            $isBuy = !$baseIsSeller;
        } elseif ($counterAccount === $address) {
            $isBuy = $baseIsSeller;
        } else {
            $isBuy = true;
        }

        $eventType = $isBuy ? 'BUY' : 'SELL';

        $valXlm = 0.0;
        $valUsd = 0.0;
        if ($baseCode === 'XLM') {
            $valXlm = $baseAmount;
            $valUsd = $baseAmount * $xlmUsdPrice;
        } elseif ($counterCode === 'XLM') {
            $valXlm = $counterAmount;
            $valUsd = $counterAmount * $xlmUsdPrice;
        }

        return [
            'id' => "trade_" . $tradeId,
            'paging_token' => $trade['paging_token'] ?? $tradeId,
            'wallet_address' => $address,
            'transaction_hash' => $txHash,
            'operation_id' => "trade_" . $tradeId,
            'ledger' => $ledger,
            'event_type' => $eventType,
            'asset_code' => $baseCode,
            'asset_issuer' => $baseIssuer,
            'counter_asset_code' => $counterCode,
            'counter_asset_issuer' => $counterIssuer,
            'amount' => $baseAmount,
            'counter_amount' => $counterAmount,
            'value_xlm' => $valXlm,
            'value_usd' => $valUsd,
            'counterparty_address' => ($baseAccount === $address) ? $counterAccount : $baseAccount,
            'occurred_at' => $occurredAt,
        ];
    }

    /**
     * Send HTTP request to Horizon with node fallback on failure.
     */
    protected function sendHorizonRequest(string $method, string $path, array $params = [], int $timeout = 5, int $retries = 1): \Illuminate\Http\Client\Response
    {
        if ($this->isTestnet) {
            $urls = ['https://horizon-testnet.stellar.org'];
        } else {
            $urls = [
                'https://horizon.stellar.org',
                'https://stellar-horizon.publicnode.com',
                'https://horizon.stellar.lobstr.co',
            ];
        }

        $lastException = null;
        foreach ($urls as $baseUrl) {
            try {
                $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
                $request = Http::timeout($timeout);
                if ($retries > 0) {
                    $request = $request->retry($retries, 100, null, false);
                }
                
                $response = $method === 'POST'
                    ? $request->post($url, $params)
                    : $request->get($url, $params);

                if ($response->ok() || $response->status() === 404) {
                    return $response;
                }

                if ($response->status() === 429) {
                    usleep(500000);
                    continue;
                }
                
                $lastException = new \RuntimeException("Horizon returned status {$response->status()}: " . $response->body());
            } catch (Throwable $e) {
                $lastException = $e;
            }
        }

        throw $lastException ?? new \RuntimeException("Failed to connect to any Horizon node.");
    }
}

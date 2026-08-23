<?php

namespace App\Services;

use App\Models\TrackedWallet;
use App\Models\WalletIndexingState;
use App\Models\WalletHolding;
use App\Models\WalletEvent;
use App\Models\WalletMetric;
use App\Models\WalletPortfolioSnapshot;
use App\Models\WalletAssetSnapshot;
use App\Models\StellarMarketToken;
use App\Models\ProjectOfficialWallet;
use App\Models\User;
use App\Jobs\IndexWalletHistoryJob;
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
     * Track a wallet on-demand.
     */
    public function trackWallet(string $address): TrackedWallet
    {
        $isConnected = User::where('public_key', $address)->where('status', 1)->exists();
        $isOfficial = ProjectOfficialWallet::where('wallet_address', $address)->exists();

        $tracked = TrackedWallet::updateOrCreate(
            ['wallet_address' => $address],
            [
                'is_connected_wallet' => $isConnected,
                'is_official_wallet' => $isOfficial,
            ]
        );

        if (!$tracked->first_viewed_at) {
            $tracked->first_viewed_at = now();
        }
        $tracked->last_viewed_at = now();
        $tracked->view_count += 1;

        // Automatically manage priority lifecycle
        if ($isConnected || $isOfficial || $tracked->is_watchlisted || $tracked->view_count >= 5) {
            $tracked->tracking_status = 'ACTIVE';
        } else {
            $tracked->tracking_status = 'PASSIVE';
        }

        $tracked->save();

        // Ensure indexing state exists
        WalletIndexingState::firstOrCreate(
            ['wallet_address' => $address],
            [
                'indexing_status' => 'pending',
                'historical_index_complete' => false,
            ]
        );

        return $tracked;
    }

    /**
     * Fetch current account info from Stellar Horizon.
     */
    public function fetchCurrentWalletState(string $address): array
    {
        try {
            $response = $this->sendHorizonRequest('GET', "accounts/{$address}", [], 5, 3);
            
            if ($response->status() === 404) {
                return [
                    'active' => false,
                    'balances' => [],
                    'claimable_balances' => [],
                    'sequence' => 0,
                    'subentry_count' => 0,
                    'signers' => [],
                    'home_domain' => null,
                    'num_assets' => 0,
                ];
            }

            if (!$response->ok()) {
                throw new \RuntimeException("Horizon accounts fetch failed: " . $response->body());
            }

            $data = $response->json();
            
            // Fetch claimable balances where wallet is a claimant
            $claimableBalances = [];
            try {
                $cbResponse = $this->sendHorizonRequest('GET', "claimable_balances", [
                    'claimant' => $address,
                    'limit' => 200,
                ], 5, 3);
                if ($cbResponse->ok()) {
                    $claimableBalances = $cbResponse->json('_embedded.records') ?? [];
                }
            } catch (Throwable $e) {
                Log::warning("Failed to fetch claimable balances for {$address}: " . $e->getMessage());
            }

            $balances = $data['balances'] ?? [];
            
            return [
                'active' => true,
                'balances' => $balances,
                'claimable_balances' => $claimableBalances,
                'sequence' => (int) ($data['sequence'] ?? 0),
                'subentry_count' => (int) ($data['subentry_count'] ?? 0),
                'signers' => $data['signers'] ?? [],
                'home_domain' => $data['home_domain'] ?? null,
                'num_assets' => count($balances),
            ];
        } catch (Throwable $e) {
            Log::error("Error fetching current wallet state for {$address}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Resolve XLM & USD price for a specific asset.
     */
    public function resolveAssetPrice(string $assetType, string $code, string $issuer, float $xlmUsdPrice, bool $skipHorizon = false): array
    {
        if ($assetType === 'native' || $code === 'XLM') {
            return [
                'price_xlm' => 1.0,
                'price_usd' => $xlmUsdPrice,
            ];
        }

        // Try resolving via StellarMarketToken table
        $marketToken = StellarMarketToken::where('asset_code', strtoupper($code))
            ->where('asset_issuer', $issuer)
            ->first();

        if ($marketToken && $marketToken->current_price_xlm !== null) {
            $priceXlm = (float) $marketToken->current_price_xlm;
            $priceUsd = (float) $marketToken->current_price_usd ?: ($priceXlm * $xlmUsdPrice);
            return [
                'price_xlm' => $priceXlm,
                'price_usd' => $priceUsd,
            ];
        }

        if ($skipHorizon) {
            return [
                'price_xlm' => 0.0,
                'price_usd' => 0.0,
            ];
        }

        // Query Horizon orderbook best bid as fallback
        try {
            $obResponse = $this->sendHorizonRequest('GET', "order_book", [
                'selling_asset_type' => $assetType,
                'selling_asset_code' => $code,
                'selling_asset_issuer' => $issuer,
                'buying_asset_type' => 'native',
                'limit' => 1,
            ], 3, 0);

            if ($obResponse->ok()) {
                $bestBid = $obResponse->json('bids.0.price');
                if ($bestBid) {
                    $priceXlm = (float) $bestBid;
                    return [
                        'price_xlm' => $priceXlm,
                        'price_usd' => $priceXlm * $xlmUsdPrice,
                    ];
                }
            }
        } catch (Throwable $e) {}

        return [
            'price_xlm' => 0.0,
            'price_usd' => 0.0,
        ];
    }

    /**
     * Valuate a Liquidity Pool Share holding.
     */
    protected function valuateLpShare(string $poolId, float $userShares, float $xlmUsdPrice): array
    {
        $cacheKey = "lp_pool_valuation_{$poolId}";
        $poolData = Cache::remember($cacheKey, 600, function () use ($poolId) {
            try {
                $response = $this->sendHorizonRequest('GET', "liquidity_pools/{$poolId}", [], 4, 0);
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
                $resAssetType = strlen($resCode) <= 4 ? 'credit_alphanum4' : 'credit_alphanum12';
                $price = $this->resolveAssetPrice($resAssetType, $resCode, $resIssuer, $xlmUsdPrice);
                $totalPoolValueXlm += ($amount * $price['price_xlm']);
            }
        }

        $sharePriceXlm = $totalPoolValueXlm / $totalShares;
        $sharePriceUsd = $sharePriceXlm * $xlmUsdPrice;

        $userValXlm = $userShares * $sharePriceXlm;
        $userValUsd = $userShares * $sharePriceUsd;

        return [
            'price_xlm' => $sharePriceXlm,
            'price_usd' => $sharePriceUsd,
            'value_xlm' => $userValXlm,
            'value_usd' => $userValUsd,
            'assets_desc' => implode(' / ', $reserveAssets) ?: 'LP',
        ];
    }

    /**
     * Rebuild and valuate all holdings for a wallet.
     */
    public function refreshHoldings(string $address, bool $skipHorizonPrice = false): array
    {
        $state = $this->fetchCurrentWalletState($address);
        $xlmUsdPrice = $this->tokenService->getXlmUsdPrice();
        
        $holdingsData = [];
        $totalValUsd = 0.0;
        $totalValXlm = 0.0;

        // 1. Process regular balances
        foreach ($state['balances'] as $bal) {
            $assetType = $bal['asset_type'];
            $balance = (float) $bal['balance'];
            
            // Exclude native XLM if balance is 0 or negative
            if ($assetType === 'native' && $balance <= 0.0) continue;

            if ($assetType === 'native') {
                $holdingsData[] = [
                    'asset_type' => 'native',
                    'asset_code' => 'XLM',
                    'asset_issuer' => '',
                    'balance' => $balance,
                    'price_xlm' => 1.0,
                    'price_usd' => $xlmUsdPrice,
                    'value_xlm' => $balance,
                    'value_usd' => $balance * $xlmUsdPrice,
                    'pool_id' => '',
                    'limit' => null,
                    'is_authorized' => true,
                    'is_authorized_to_maintain_liabilities' => true,
                    'is_clawback_enabled' => false,
                ];
                $totalValXlm += $balance;
                $totalValUsd += ($balance * $xlmUsdPrice);
            } elseif ($assetType === 'liquidity_pool_shares') {
                // For LP positions, only show positive balance ones
                if ($balance <= 0.0) continue;
                $poolId = $bal['liquidity_pool_id'] ?? '';
                $lpVal = $this->valuateLpShare($poolId, $balance, $xlmUsdPrice);
                $holdingsData[] = [
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
                ];
                $totalValXlm += $lpVal['value_xlm'];
                $totalValUsd += $lpVal['value_usd'];
            } else {
                $code = $bal['asset_code'] ?? '';
                $issuer = $bal['asset_issuer'] ?? '';
                $price = $this->resolveAssetPrice($assetType, $code, $issuer, $xlmUsdPrice, $skipHorizonPrice);
                $valXlm = $balance * $price['price_xlm'];
                $valUsd = $balance * $price['price_usd'];

                $holdingsData[] = [
                    'asset_type' => $assetType,
                    'asset_code' => $code,
                    'asset_issuer' => $issuer,
                    'balance' => $balance,
                    'price_xlm' => $price['price_xlm'],
                    'price_usd' => $price['price_usd'],
                    'value_xlm' => $valXlm,
                    'value_usd' => $valUsd,
                    'pool_id' => '',
                    'limit' => isset($bal['limit']) ? (float) $bal['limit'] : null,
                    'is_authorized' => isset($bal['is_authorized']) ? (bool) $bal['is_authorized'] : true,
                    'is_authorized_to_maintain_liabilities' => isset($bal['is_authorized_to_maintain_liabilities']) ? (bool) $bal['is_authorized_to_maintain_liabilities'] : true,
                    'is_clawback_enabled' => isset($bal['is_clawback_enabled']) ? (bool) $bal['is_clawback_enabled'] : false,
                ];
                $totalValXlm += $valXlm;
                $totalValUsd += $valUsd;
            }
        }

        // 2. Process claimable balances
        foreach ($state['claimable_balances'] as $cb) {
            $amount = (float) ($cb['amount'] ?? 0.0);
            if ($amount <= 0.0) continue;

            $assetStr = $cb['asset'] ?? '';
            $price = ['price_xlm' => 0.0, 'price_usd' => 0.0];
            $assetCode = 'XLM';
            $assetIssuer = '';
            $assetType = 'native';

            if ($assetStr !== 'native') {
                $parts = explode(':', $assetStr);
                $assetCode = $parts[0] ?? '';
                $assetIssuer = $parts[1] ?? '';
                $assetType = strlen($assetCode) <= 4 ? 'credit_alphanum4' : 'credit_alphanum12';
                $price = $this->resolveAssetPrice($assetType, $assetCode, $assetIssuer, $xlmUsdPrice, $skipHorizonPrice);
            } else {
                $price = [
                    'price_xlm' => 1.0,
                    'price_usd' => $xlmUsdPrice,
                ];
            }

            $valXlm = $amount * $price['price_xlm'];
            $valUsd = $amount * $price['price_usd'];

            $holdingsData[] = [
                'asset_type' => 'claimable_balance',
                'asset_code' => $assetCode,
                'asset_issuer' => $assetIssuer,
                'balance' => $amount,
                'price_xlm' => $price['price_xlm'],
                'price_usd' => $price['price_usd'],
                'value_xlm' => $valXlm,
                'value_usd' => $valUsd,
                'pool_id' => $cb['id'] ?? '',
                'limit' => null,
                'is_authorized' => true,
                'is_authorized_to_maintain_liabilities' => true,
                'is_clawback_enabled' => false,
            ];
            $totalValXlm += $valXlm;
            $totalValUsd += $valUsd;
        }

        // 3. Update or create in Database
        $updatedIds = [];
        foreach ($holdingsData as $hold) {
            $allocation = $totalValUsd > 0 ? ($hold['value_usd'] / $totalValUsd) * 100 : 0.0;
            
            $holdingRecord = WalletHolding::updateOrCreate(
                [
                    'wallet_address' => $address,
                    'asset_type' => $hold['asset_type'],
                    'asset_code' => $hold['asset_code'],
                    'asset_issuer' => $hold['asset_issuer'],
                    'pool_id' => $hold['pool_id'],
                ],
                [
                    'balance' => $hold['balance'],
                    'price_xlm' => $hold['price_xlm'],
                    'price_usd' => $hold['price_usd'],
                    'value_xlm' => $hold['value_xlm'],
                    'value_usd' => $hold['value_usd'],
                    'allocation_percentage' => $allocation,
                    'limit' => $hold['limit'],
                    'is_authorized' => $hold['is_authorized'],
                    'is_authorized_to_maintain_liabilities' => $hold['is_authorized_to_maintain_liabilities'],
                    'is_clawback_enabled' => $hold['is_clawback_enabled'],
                ]
            );
            $updatedIds[] = $holdingRecord->id;
        }

        // Delete any holdings that are no longer present
        WalletHolding::where('wallet_address', $address)
            ->whereNotIn('id', $updatedIds)
            ->delete();

        // Update last refreshed at
        TrackedWallet::where('wallet_address', $address)->update([
            'last_refreshed_at' => now(),
        ]);

        return WalletHolding::where('wallet_address', $address)->get()->toArray();
    }

    /**
     * Index next chunk of wallet history from Stellar Horizon (operations & trades).
     * Returns true if there are more chunks, false otherwise.
     */
    public function indexNextChunk(string $address): bool
    {
        $state = WalletIndexingState::where('wallet_address', $address)->firstOrFail();
        
        if ($state->indexing_status === 'pending') {
            $state->update([
                'indexing_status' => 'indexing',
                'first_indexed_at' => now(),
            ]);
        }

        // Fast-path: If the wallet is completely unfunded/inactive on the ledger, it has no history
        $walletState = $this->fetchCurrentWalletState($address);
        if (!$walletState['active']) {
            $state->update([
                'indexing_status' => 'ready',
                'historical_index_complete' => true,
                'last_indexed_at' => now(),
            ]);
            return false;
        }

        $xlmUsdPrice = $this->tokenService->getXlmUsdPrice();
        $hasMoreOps = false;
        $hasMoreTrades = false;

        try {
            // 1. Process Operations page
            $opParams = [
                'order' => 'asc',
                'limit' => 200,
            ];
            if ($state->last_processed_cursor) {
                $opParams['cursor'] = $state->last_processed_cursor;
            }

            $opResponse = $this->sendHorizonRequest('GET', "accounts/{$address}/operations", $opParams, 10, 3);

            if ($opResponse->status() === 404) {
                $opRecords = [];
            } elseif (!$opResponse->ok()) {
                throw new \RuntimeException("Failed to fetch operations: " . $opResponse->body());
            } else {
                $opRecords = $opResponse->json('_embedded.records') ?? [];
            }
            $newOpCursor = $state->last_processed_cursor;
            $lastLedger = $state->last_processed_ledger;

            foreach ($opRecords as $op) {
                $newOpCursor = $op['paging_token'];
                $lastLedger = (int) ($op['ledger'] ?? $lastLedger);
                
                $opId = (string) $op['id'];

                // Check duplicate
                $exists = WalletEvent::where('wallet_address', $address)
                    ->where('operation_id', $opId)
                    ->exists();

                if ($exists) continue;

                $event = $this->normalizeOperation($address, $op, $xlmUsdPrice);
                if ($event) {
                    try {
                        WalletEvent::create($event);
                    } catch (\Illuminate\Database\QueryException $e) {
                        if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                            continue;
                        }
                        throw $e;
                    }
                }
            }

            if (count($opRecords) === 200) {
                $hasMoreOps = true;
            }

            // 2. Process Trades page
            $tradeParams = [
                'order' => 'asc',
                'limit' => 200,
            ];
            if ($state->last_processed_trade_cursor) {
                $tradeParams['cursor'] = $state->last_processed_trade_cursor;
            }

            $tradeResponse = $this->sendHorizonRequest('GET', "accounts/{$address}/trades", $tradeParams, 10, 3);

            if ($tradeResponse->status() === 404) {
                $tradeRecords = [];
            } elseif (!$tradeResponse->ok()) {
                throw new \RuntimeException("Failed to fetch trades: " . $tradeResponse->body());
            } else {
                $tradeRecords = $tradeResponse->json('_embedded.records') ?? [];
            }
            $newTradeCursor = $state->last_processed_trade_cursor;

            foreach ($tradeRecords as $trade) {
                $newTradeCursor = $trade['paging_token'];
                $tradeUid = "trade_" . $trade['id'];

                // Check duplicate
                $exists = WalletEvent::where('wallet_address', $address)
                    ->where('operation_id', $tradeUid)
                    ->exists();

                if ($exists) continue;

                $event = $this->normalizeTrade($address, $trade, $xlmUsdPrice);
                if ($event) {
                    try {
                        WalletEvent::create($event);
                    } catch (\Illuminate\Database\QueryException $e) {
                        if ($e->getCode() === '23000' || str_contains($e->getMessage(), '1062')) {
                            continue;
                        }
                        throw $e;
                    }
                }
            }

            if (count($tradeRecords) === 200) {
                $hasMoreTrades = true;
            }

            // Save cursors
            $state->update([
                'last_processed_cursor' => $newOpCursor,
                'last_processed_trade_cursor' => $newTradeCursor,
                'last_processed_ledger' => $lastLedger,
                'last_indexed_at' => now(),
            ]);

            $hasMore = $hasMoreOps || $hasMoreTrades;

            if (!$hasMore) {
                // Done indexing history
                $state->update([
                    'indexing_status' => 'ready',
                    'historical_index_complete' => true,
                    'error_message' => null,
                ]);

                // Update metrics
                $this->updateMetrics($address);
            }

            return $hasMore;

        } catch (Throwable $e) {
            Log::error("Historical indexing failed for {$address}: " . $e->getMessage());
            $state->update([
                'indexing_status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Normalize operations into event records.
     */
    protected function normalizeOperation(string $address, array $op, float $xlmUsdPrice): ?array
    {
        $type = $op['type'] ?? '';
        $opId = (string) $op['id'];
        $ledger = (int) ($op['ledger'] ?? 0);
        $txHash = $op['transaction_hash'] ?? '';
        $occurredAt = Carbon::parse($op['created_at']);

        $event = [
            'wallet_address' => $address,
            'transaction_hash' => $txHash,
            'operation_id' => $opId,
            'ledger' => $ledger,
            'occurred_at' => $occurredAt,
            'metadata_json' => $op,
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

                $price = $this->resolveAssetPrice($assetType, $assetCode, $assetIssuer, $xlmUsdPrice);
                
                $event['asset_code'] = $assetCode;
                $event['asset_issuer'] = $assetIssuer;
                $event['amount'] = $amount;
                $event['value_xlm'] = $amount * $price['price_xlm'];
                $event['value_usd'] = $amount * $price['price_usd'];

                if ($to === $address) {
                    $event['event_type'] = 'PAYMENT_IN';
                    $event['counterparty_address'] = $from;
                } else {
                    $event['event_type'] = 'PAYMENT_OUT';
                    $event['counterparty_address'] = $to;
                }
                break;

            case 'account_merge':
                // Merges account, sending all XLM to destination
                $mergedAccount = $op['source_account'] ?? '';
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
                $destAssetType = $op['asset_type'] ?? 'native';
                $destAmount = (float) ($op['amount'] ?? 0.0);

                $srcAssetCode = $op['source_asset_code'] ?? 'XLM';
                $srcAssetIssuer = $op['source_asset_issuer'] ?? '';
                $srcAssetType = $op['source_asset_type'] ?? 'native';
                $srcAmount = (float) ($op['source_amount'] ?? $op['amount'] ?? 0.0);

                // If from and to are both the wallet: it's a swap! Handled entirely by `/trades`
                if ($from === $address && $to === $address) {
                    return null; // Skip operation event, let trade handle it
                }

                if ($to === $address) {
                    $price = $this->resolveAssetPrice($destAssetType, $destAssetCode, $destAssetIssuer, $xlmUsdPrice);
                    $event['event_type'] = 'PAYMENT_IN';
                    $event['asset_code'] = $destAssetCode;
                    $event['asset_issuer'] = $destAssetIssuer;
                    $event['amount'] = $destAmount;
                    $event['value_xlm'] = $destAmount * $price['price_xlm'];
                    $event['value_usd'] = $destAmount * $price['price_usd'];
                    $event['counterparty_address'] = $from;
                } else {
                    $price = $this->resolveAssetPrice($srcAssetType, $srcAssetCode, $srcAssetIssuer, $xlmUsdPrice);
                    $event['event_type'] = 'PAYMENT_OUT';
                    $event['asset_code'] = $srcAssetCode;
                    $event['asset_issuer'] = $srcAssetIssuer;
                    $event['amount'] = $srcAmount;
                    $event['value_xlm'] = $srcAmount * $price['price_xlm'];
                    $event['value_usd'] = $srcAmount * $price['price_usd'];
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
                // Claimable balance amount/asset isn't always direct in operations list, but we save metadata
                break;

            case 'liquidity_pool_deposit':
                $event['event_type'] = 'LP_ADD';
                break;

            case 'liquidity_pool_withdraw':
                $event['event_type'] = 'LP_REMOVE';
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
     * Normalize trades into event records.
     */
    protected function normalizeTrade(string $address, array $trade, float $xlmUsdPrice): ?array
    {
        $baseAccount = $trade['base_account'] ?? '';
        $counterAccount = $trade['counter_account'] ?? '';
        $baseIsSeller = (bool) ($trade['base_is_seller'] ?? false);

        $baseCode = $trade['base_asset_code'] ?? 'XLM';
        $baseIssuer = $trade['base_asset_issuer'] ?? '';
        $baseType = $trade['base_asset_type'] ?? 'native';
        $baseAmount = (float) ($trade['base_amount'] ?? 0.0);

        $counterCode = $trade['counter_asset_code'] ?? 'XLM';
        $counterIssuer = $trade['counter_asset_issuer'] ?? '';
        $counterType = $trade['counter_asset_type'] ?? 'native';
        $counterAmount = (float) ($trade['counter_amount'] ?? 0.0);

        $tradeId = $trade['id'] ?? '';
        $opId = $trade['operation_id'] ?? '';
        $txHash = $trade['transaction_hash'] ?? '';
        $occurredAt = Carbon::parse($trade['ledger_close_time']);
        $ledger = (int) ($opId ? ((int)$opId) >> 32 : 0);

        // Determine if BUY or SELL of the base asset
        $isBuy = false;
        if ($baseAccount === $address) {
            $isBuy = !$baseIsSeller;
        } elseif ($counterAccount === $address) {
            $isBuy = $baseIsSeller;
        } else {
            return null; // Trade doesn't belong to this wallet
        }

        $eventType = $isBuy ? 'BUY' : 'SELL';

        // Resolve value of base asset
        $price = $this->resolveAssetPrice($baseType, $baseCode, $baseIssuer, $xlmUsdPrice);
        $valXlm = $baseAmount * $price['price_xlm'];
        $valUsd = $baseAmount * $price['price_usd'];

        // Fallback to counter asset if base has no value
        if ($valXlm <= 0.0) {
            $cPrice = $this->resolveAssetPrice($counterType, $counterCode, $counterIssuer, $xlmUsdPrice);
            $valXlm = $counterAmount * $cPrice['price_xlm'];
            $valUsd = $counterAmount * $cPrice['price_usd'];
        }

        return [
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
            'metadata_json' => $trade,
        ];
    }

    /**
     * Recalculate metrics for a given wallet address.
     */
    public function updateMetrics(string $address): void
    {
        $holdings = WalletHolding::where('wallet_address', $address)
            ->where('asset_type', '!=', 'claimable_balance')
            ->get();
        $totalValXlm = $holdings->sum('value_xlm');
        $totalValUsd = $holdings->sum('value_usd');
        $assetCount = $holdings->where('balance', '>', 0)->count();
        
        $trustlineCount = $holdings->whereIn('asset_type', ['credit_alphanum4', 'credit_alphanum12'])->count();
        $lpPositionCount = $holdings->where('asset_type', 'liquidity_pool_shares')->count();

        // Time limits
        $now = now();
        $time24h = $now->copy()->subHours(24);
        $time7d = $now->copy()->subDays(7);
        $time30d = $now->copy()->subDays(30);

        // Transaction/Event counts
        $txCount24h = WalletEvent::where('wallet_address', $address)->where('occurred_at', '>=', $time24h)->count();
        $txCount7d = WalletEvent::where('wallet_address', $address)->where('occurred_at', '>=', $time7d)->count();
        $txCount30d = WalletEvent::where('wallet_address', $address)->where('occurred_at', '>=', $time30d)->count();

        // Buy/Sell volume 24h & 7d (of BUY & SELL event values)
        $buyVol24h = WalletEvent::where('wallet_address', $address)
            ->where('event_type', 'BUY')
            ->where('occurred_at', '>=', $time24h)
            ->sum('value_xlm');

        $sellVol24h = WalletEvent::where('wallet_address', $address)
            ->where('event_type', 'SELL')
            ->where('occurred_at', '>=', $time24h)
            ->sum('value_xlm');

        $buyVol7d = WalletEvent::where('wallet_address', $address)
            ->where('event_type', 'BUY')
            ->where('occurred_at', '>=', $time7d)
            ->sum('value_xlm');

        $sellVol7d = WalletEvent::where('wallet_address', $address)
            ->where('event_type', 'SELL')
            ->where('occurred_at', '>=', $time7d)
            ->sum('value_xlm');

        // Average trade size and largest trade size (XLM)
        $tradesQuery = WalletEvent::where('wallet_address', $address)
            ->whereIn('event_type', ['BUY', 'SELL']);

        $avgTradeSize = $tradesQuery->avg('value_xlm');
        $largestTrade = $tradesQuery->max('value_xlm');

        WalletMetric::updateOrCreate(
            ['wallet_address' => $address],
            [
                'portfolio_value_xlm' => $totalValXlm,
                'portfolio_value_usd' => $totalValUsd,
                'asset_count' => $assetCount,
                'trustline_count' => $trustlineCount,
                'lp_position_count' => $lpPositionCount,
                'transaction_count_24h' => $txCount24h,
                'transaction_count_7d' => $txCount7d,
                'transaction_count_30d' => $txCount30d,
                'buy_volume_xlm_24h' => $buyVol24h,
                'sell_volume_xlm_24h' => $sellVol24h,
                'buy_volume_xlm_7d' => $buyVol7d,
                'sell_volume_xlm_7d' => $sellVol7d,
                'average_trade_size_xlm' => $avgTradeSize,
                'largest_trade_xlm' => $largestTrade,
                'last_calculated_at' => now(),
            ]
        );
    }

    /**
     * Capture portfolio snapshots for a wallet.
     */
    public function takeSnapshot(string $address): void
    {
        $holdings = WalletHolding::where('wallet_address', $address)
            ->where('asset_type', '!=', 'claimable_balance')
            ->get();
        $totalValXlm = $holdings->sum('value_xlm');
        $totalValUsd = $holdings->sum('value_usd');
        $assetCount = $holdings->where('balance', '>', 0)->count();

        $snapshotAt = now();

        WalletPortfolioSnapshot::create([
            'wallet_address' => $address,
            'total_value_xlm' => $totalValXlm,
            'total_value_usd' => $totalValUsd,
            'asset_count' => $assetCount,
            'snapshot_at' => $snapshotAt,
        ]);

        foreach ($holdings as $hold) {
            WalletAssetSnapshot::create([
                'wallet_address' => $address,
                'asset_code' => $hold->asset_code ?: 'XLM',
                'asset_issuer' => $hold->asset_issuer ?: null,
                'balance' => $hold->balance,
                'price_xlm' => $hold->price_xlm,
                'price_usd' => $hold->price_usd,
                'value_xlm' => $hold->value_xlm,
                'value_usd' => $hold->value_usd,
                'snapshot_at' => $snapshotAt,
            ]);
        }
    }

    /**
     * Send HTTP request to Horizon with node fallback on failure.
     */
    protected function sendHorizonRequest(string $method, string $path, array $params = [], int $timeout = 10, int $retries = 3): \Illuminate\Http\Client\Response
    {
        if ($this->isTestnet) {
            $urls = ['https://horizon-testnet.stellar.org'];
        } else {
            $urls = [
                'https://horizon.stellar.org',
                'https://stellar-horizon.publicnode.is',
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
                    sleep(1);
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

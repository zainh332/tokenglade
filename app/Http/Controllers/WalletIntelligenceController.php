<?php

namespace App\Http\Controllers;

use App\Models\TrackedWallet;
use App\Models\WalletHolding;
use App\Models\WalletEvent;
use App\Models\WalletPortfolioSnapshot;
use App\Models\WalletMetric;
use App\Services\WalletIntelligenceService;
use App\Jobs\IndexWalletHistoryJob;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Throwable;

class WalletIntelligenceController extends Controller
{
    protected WalletIntelligenceService $service;

    public function __construct(WalletIntelligenceService $service)
    {
        $this->service = $service;
    }

    /**
     * GET /api/wallet/{address}/overview
     */
    public function overview(Request $request, string $address)
    {
        try {
            $tracked = TrackedWallet::where('wallet_address', $address)->first();
            $firstTime = !$tracked;

            // 1. Track the wallet (creates or updates views)
            $tracked = $this->service->trackWallet($address);

            $indexState = \App\Models\WalletIndexingState::where('wallet_address', $address)->first();

            // Manual retry reset triggered by the user
            if ($request->input('retry') && $indexState) {
                $indexState->update(['indexing_status' => 'pending']);
                $indexState = $indexState->fresh();
            }

            // Self-healing: Reset stuck indexing state if no progress for > 1 minute
            if ($indexState && $indexState->indexing_status === 'indexing') {
                if ($indexState->updated_at < now()->subMinute()) {
                    $indexState->update(['indexing_status' => 'pending']);
                    $indexState = $indexState->fresh(); // Reload state
                }
            }

            // 2. Fetch current state immediately if first time, failed, or pending
            if ($firstTime || !$indexState || $indexState->indexing_status === 'failed' || $indexState->indexing_status === 'pending') {
                try {
                    $this->service->refreshHoldings($address, true);
                    IndexWalletHistoryJob::dispatch($address)->afterResponse();
                } catch (Throwable $e) {
                    if ($firstTime || !$indexState) {
                        throw $e;
                    }
                    \Illuminate\Support\Facades\Log::warning("Stellar Horizon refresh failed for tracked wallet {$address}, using cached data: " . $e->getMessage());
                }
            } else {
                // If data is stale (older than 5 minutes), dispatch incremental updates in background
                $staleTime = now()->subMinutes(5);
                if (!$tracked->last_refreshed_at || $tracked->last_refreshed_at < $staleTime) {
                    try {
                        IndexWalletHistoryJob::dispatch($address)->afterResponse();
                    } catch (Throwable $e) {}
                }
            }

            // 3. Gather stats from holdings (excluding claimables)
            $holdings = WalletHolding::where('wallet_address', $address)->get();
            $regularHoldings = $holdings->where('asset_type', '!=', 'claimable_balance');
            $claimableHoldings = $holdings->where('asset_type', 'claimable_balance');

            $portfolioValueUsd = $regularHoldings->sum('value_usd');
            $portfolioValueXlm = $regularHoldings->sum('value_xlm');
            
            // Separate liquid holdings and pool holdings
            $liquidHoldings = $regularHoldings->where('asset_type', '!=', 'liquidity_pool_shares');
            $lpHoldings = $regularHoldings->where('asset_type', 'liquidity_pool_shares');

            // Assets Held = current liquid assets with positive balances, including native XLM.
            $assetsHeld = $liquidHoldings->where('balance', '>', 0)->count();
            
            // Trustlines = number of issued standard Stellar asset trustlines established by the account.
            $trustlinesCount = $liquidHoldings->where('asset_type', '!=', 'native')->count();

            // Pools Count = count of positive balance liquidity pools
            $poolsCount = $lpHoldings->where('balance', '>', 0)->count();
            
            $xlmHolding = $regularHoldings->where('asset_type', 'native')->first();
            $xlmBalance = $xlmHolding ? $xlmHolding->balance : 0.0;

            $claimableCount = $claimableHoldings->count();
            $claimableValueUsd = $claimableHoldings->sum('value_usd');

            // 4. Gather activity metrics
            $firstEvent = WalletEvent::where('wallet_address', $address)->orderBy('occurred_at', 'asc')->first();
            $lastEvent = WalletEvent::where('wallet_address', $address)->orderBy('occurred_at', 'desc')->first();

            $firstActivity = $firstEvent ? $firstEvent->occurred_at->toIso8601String() : null;
            $lastActivity = $lastEvent ? $lastEvent->occurred_at->toIso8601String() : null;
            
            $walletAgeDays = null;
            if ($firstEvent) {
                $walletAgeDays = (int) ceil(now()->diffInDays($firstEvent->occurred_at));
            }

            $indexComplete = $indexState ? (bool) $indexState->historical_index_complete : false;

            return response()->json([
                'status' => 'success',
                'data' => [
                    'wallet_address' => $address,
                    'portfolio_value_xlm' => (float) $portfolioValueXlm,
                    'portfolio_value_usd' => (float) $portfolioValueUsd,
                    'xlm_balance' => (float) $xlmBalance,
                    'asset_count' => (int) $assetsHeld, // Backward compatible
                    'assets_held' => (int) $assetsHeld,
                    'trustlines_count' => (int) $trustlinesCount,
                    'pools_count' => (int) $poolsCount,
                    'wallet_age_days' => $walletAgeDays,
                    'first_activity' => $firstActivity,
                    'last_activity' => $lastActivity,
                    'tracking_status' => $tracked->tracking_status,
                    'historical_index_complete' => $indexComplete,
                    'indexing_status' => $indexState ? $indexState->indexing_status : 'pending',
                    'claimable_count' => (int) $claimableCount,
                    'claimable_value_usd' => (float) $claimableValueUsd,
                ]
            ]);

        } catch (Throwable $e) {
            $msg = $e->getMessage();
            $isConnectionError = str_contains(strtolower($msg), 'connection') 
                || str_contains(strtolower($msg), 'resolve host') 
                || str_contains(strtolower($msg), 'refused')
                || str_contains(strtolower($msg), 'curl error');

            return response()->json([
                'status' => 'error',
                'error_type' => $isConnectionError ? 'connection_error' : 'not_found',
                'message' => 'Failed to fetch overview: ' . $msg
            ], 500);
        }
    }

    /**
     * GET /api/wallet/{address}/holdings
     */
    public function holdings(string $address)
    {
        try {
            // Automatically track if not already tracked
            $tracked = TrackedWallet::where('wallet_address', $address)->first();
            $holdingsCollection = WalletHolding::where('wallet_address', $address)->get();

            if ($holdingsCollection->isEmpty() || !$tracked) {
                if (!$tracked) {
                    $this->service->trackWallet($address);
                }
                $this->service->refreshHoldings($address, true);
                // Dispatch history indexing if not already tracked
                if (!$tracked) {
                    IndexWalletHistoryJob::dispatch($address);
                }
                $holdingsCollection = WalletHolding::where('wallet_address', $address)->get();
            }

            $hasUnresolvedLp = $holdingsCollection->contains(function ($hold) {
                return $hold->asset_type === 'liquidity_pool_shares' && $hold->asset_code === 'LP';
            });

            if ($hasUnresolvedLp) {
                $this->service->refreshHoldings($address, true);
                $holdingsCollection = WalletHolding::where('wallet_address', $address)->get();
            }

            $holdings = $holdingsCollection->map(function ($hold) {
                $holdArray = $hold->toArray();
                if ($hold->asset_code === 'XLM') {
                    $holdArray['logo_url'] = null;
                } elseif ($hold->asset_type === 'liquidity_pool_shares') {
                    $holdArray['logo_url'] = null;
                } else {
                    $token = \App\Models\StellarToken::where('asset_code', $hold->asset_code)
                        ->where('issuer_public_key', $hold->asset_issuer)
                        ->first();
                    if ($token && $token->logo) {
                        $holdArray['logo_url'] = $token->logo;
                    } else {
                        // Get the cached logo URL. If not cached yet, returns null (shows fallback text)
                        // but doesn't block the request synchronously. The background indexer job will resolve it shortly.
                        $holdArray['logo_url'] = \Illuminate\Support\Facades\Cache::get(
                            "asset_logo_" . $hold->asset_code . "_" . $hold->asset_issuer
                        );
                    }
                }
                return $holdArray;
            });

            return response()->json([
                'status' => 'success',
                'data' => $holdings
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch holdings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/wallet/{address}/activity
     */
    public function activity(string $address)
    {
        try {
            $events = WalletEvent::where('wallet_address', $address)
                ->orderBy('occurred_at', 'desc')
                ->paginate(10);

            return response()->json($events);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/wallet/{address}/portfolio-history
     */
    public function portfolioHistory(string $address)
    {
        try {
            $portfolio = WalletPortfolioSnapshot::where('wallet_address', $address)
                ->orderBy('snapshot_at', 'asc')
                ->get();

            $assets = \App\Models\WalletAssetSnapshot::where('wallet_address', $address)
                ->orderBy('snapshot_at', 'asc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'portfolio' => $portfolio,
                    'assets' => $assets
                ]
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch portfolio history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/wallet/{address}/metrics
     */
    public function metrics(string $address)
    {
        try {
            // Recalculate metrics on the fly if needed, or return cached
            $metrics = WalletMetric::where('wallet_address', $address)->first();

            return response()->json([
                'status' => 'success',
                'data' => $metrics
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch metrics: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Services\WalletIntelligenceService;
use Illuminate\Http\Request;
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
     * Fetches live wallet overview from Horizon in real-time.
     */
    public function overview(Request $request, string $address)
    {
        try {
            $overview = $this->service->getWalletOverview($address);

            return response()->json([
                'status' => 'success',
                'data' => $overview
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
     * Fetches live token balances, trustlines, pools, and claimables from Horizon.
     */
    public function holdings(string $address)
    {
        try {
            $holdings = $this->service->getWalletHoldings($address);

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
     * Fetches live operations/transactions from Horizon with cursor pagination.
     */
    public function activity(Request $request, string $address)
    {
        try {
            $cursor = $request->input('cursor');
            $limit = (int) $request->input('limit', 10);
            $type = $request->input('type', 'all');

            $activity = $this->service->getWalletActivity($address, $cursor, $limit, $type);

            return response()->json([
                'status' => 'success',
                'data' => $activity['records'],
                'next_cursor' => $activity['next_cursor'],
                'prev_cursor' => $activity['prev_cursor'],
                'has_more' => $activity['has_more'],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch activity: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/wallet/{address}/portfolio-history
     * Deprecated (PnL historical database snapshots removed).
     */
    public function portfolioHistory(string $address)
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'portfolio' => [],
                'assets' => []
            ]
        ]);
    }

    /**
     * GET /api/wallet/{address}/metrics
     * Live metrics / stats.
     */
    public function metrics(string $address)
    {
        try {
            $overview = $this->service->getWalletOverview($address);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'portfolio_value_xlm' => $overview['portfolio_value_xlm'],
                    'portfolio_value_usd' => $overview['portfolio_value_usd'],
                    'asset_count' => $overview['assets_held'],
                    'trustline_count' => $overview['trustlines_count'],
                    'lp_position_count' => $overview['pools_count'],
                    'signers_count' => count($overview['signers'] ?? []),
                    'subentry_count' => $overview['subentry_count'] ?? 0,
                    'sequence' => $overview['sequence'] ?? 0,
                ]
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch metrics: ' . $e->getMessage()
            ], 500);
        }
    }
}

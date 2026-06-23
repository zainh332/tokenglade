<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StellarToken;
use App\Models\Staking;
use Illuminate\Http\Request;
use Soneso\StellarSDK\StellarSDK;

class AdminController extends Controller
{
    /**
     * Get connected wallets.
     */
    public function wallets(Request $request)
    {
        $wallets = User::orderBy('created_at', 'desc')->paginate(15);

        // Map and check balances from Stellar network (Horizon API)
        $sdk = StellarSDK::getPublicNetInstance();
        $items = collect($wallets->items())->map(function ($user) use ($sdk) {
            $balance = 0.0;
            try {
                $account = $sdk->requestAccount($user->public_key);
                if ($account) {
                    foreach ($account->getBalances() as $bal) {
                        if ($bal->getAssetType() === 'native') {
                            $balance = (float) $bal->getBalance();
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Not active or error
            }

            return [
                'id' => $user->id,
                'address' => $user->public_key,
                'created_at' => $user->created_at ? $user->created_at->toIso8601String() : null,
                'last_active' => $user->updated_at ? $user->updated_at->toIso8601String() : null,
                'balance' => $balance
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $wallets->currentPage(),
                'last_page' => $wallets->lastPage(),
                'total' => $wallets->total(),
                'per_page' => $wallets->perPage(),
            ]
        ]);
    }

    /**
     * Get minted tokens inventory.
     */
    public function tokens(Request $request)
    {
        $tokens = StellarToken::orderBy('created_at', 'desc')->paginate(15);

        $items = collect($tokens->items())->map(function ($token) {
            return [
                'id' => $token->id,
                'code' => $token->asset_code,
                'issuer' => $token->issuer_public_key,
                'supply' => (float) $token->total_supply,
                'creator' => $token->user_wallet_address,
                'created_at' => $token->created_at ? $token->created_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $tokens->currentPage(),
                'last_page' => $tokens->lastPage(),
                'total' => $tokens->total(),
                'per_page' => $tokens->perPage(),
            ]
        ]);
    }

    /**
     * Get staking snapshotted stats.
     */
    public function staking(Request $request)
    {
        $stakings = Staking::with('user', 'rewards')->orderBy('created_at', 'desc')->paginate(15);

        $items = collect($stakings->items())->map(function ($stake) {
            return [
                'id' => $stake->id,
                'address' => $stake->user ? $stake->user->public_key : '—',
                'status' => $stake->is_withdrawn ? 'Withdrawn' : 'Active',
                'locked_amount' => (float) $stake->amount,
                'total_rewards' => (float) $stake->rewards->sum('reward_amount'),
                'unlock_date' => $stake->unlock_at ? $stake->unlock_at->toIso8601String() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'meta' => [
                'current_page' => $stakings->currentPage(),
                'last_page' => $stakings->lastPage(),
                'total' => $stakings->total(),
                'per_page' => $stakings->perPage(),
            ]
        ]);
    }

    /**
     * Get liquidity pool participants list.
     */
    public function lpParticipants(Request $request)
    {
        $participants = \App\Models\LiquidityPoolParticipant::orderBy('pool_shares', 'desc')->paginate(15);

        $items = collect($participants->items())->map(function ($part) {
            return [
                'id' => $part->id,
                'wallet_address' => $part->wallet_address,
                'pool_shares' => (float) $part->pool_shares,
                'tkg_amount' => (float) $part->tkg_amount,
                'xlm_amount' => (float) $part->xlm_amount,
                'is_active' => $part->is_active,
                'wallet_status' => $part->wallet_status,
                'updated_at' => $part->updated_at ? $part->updated_at->toIso8601String() : null,
            ];
        });

        // Compute summary stats for the headers
        $totalTkg = \App\Models\LiquidityPoolParticipant::sum('tkg_amount');
        $totalXlm = \App\Models\LiquidityPoolParticipant::sum('xlm_amount');
        $totalShares = \App\Models\LiquidityPoolParticipant::sum('pool_shares');

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'stats' => [
                'total_tkg' => (float) $totalTkg,
                'total_xlm' => (float) $totalXlm,
                'total_shares' => (float) $totalShares,
                'total_participants' => $participants->total(),
            ],
            'meta' => [
                'current_page' => $participants->currentPage(),
                'last_page' => $participants->lastPage(),
                'total' => $participants->total(),
                'per_page' => $participants->perPage(),
            ]
        ]);
    }

    /**
     * Force sync liquidity pool participants.
     */
    public function syncLpParticipants(Request $request, \App\Services\LpSyncService $syncService)
    {
        $result = $syncService->sync();

        if ($result['status'] === 'success') {
            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'data' => $result
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message']
        ], 500);
    }

    /**
     * Get settings.
     */
    public function getSettings(Request $request)
    {
        $lpWeeklyReward = \App\Models\Setting::where('key', 'lp_weekly_reward_amount')->first();

        return response()->json([
            'status' => 'success',
            'settings' => [
                'lp_weekly_reward_amount' => $lpWeeklyReward ? (float) $lpWeeklyReward->value : 16000.0,
            ]
        ]);
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'lp_weekly_reward_amount' => 'required|numeric|min:0',
        ]);

        \App\Models\Setting::updateOrCreate(
            ['key' => 'lp_weekly_reward_amount'],
            ['value' => $request->lp_weekly_reward_amount]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Settings updated successfully.',
        ]);
    }

    /**
     * Get LP rewards history.
     */
    public function lpHistory(Request $request)
    {
        $query = \App\Models\LpRewardDistribution::with('cycle')->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where('wallet_address', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('cycle_id')) {
            $query->where('lp_reward_cycle_id', $request->cycle_id);
        }

        $distributions = $query->paginate(15);

        $items = collect($distributions->items())->map(function ($dist) {
            return [
                'id' => $dist->id,
                'wallet_address' => $dist->wallet_address,
                'pool_share_percentage' => (float) $dist->pool_share_percentage,
                'reward_amount' => (float) $dist->reward_amount,
                'tx_hash' => $dist->tx_hash,
                'status' => $dist->status,
                'created_at' => $dist->created_at ? $dist->created_at->toIso8601String() : null,
                'cycle' => $dist->cycle ? [
                    'id' => $dist->cycle->id,
                    'week_number' => $dist->cycle->week_number,
                    'snapshot_date' => $dist->cycle->snapshot_date ? $dist->cycle->snapshot_date : null,
                    'total_reward_pool' => (float) $dist->cycle->total_reward_pool,
                    'status' => $dist->cycle->status,
                ] : null,
            ];
        });

        // Load all cycles for select filter
        $cycles = \App\Models\LpRewardCycle::orderBy('week_number', 'desc')->get(['id', 'week_number', 'snapshot_date']);

        return response()->json([
            'status' => 'success',
            'data' => $items,
            'cycles' => $cycles,
            'meta' => [
                'current_page' => $distributions->currentPage(),
                'last_page' => $distributions->lastPage(),
                'total' => $distributions->total(),
                'per_page' => $distributions->perPage(),
            ]
        ]);
    }
}

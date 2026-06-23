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
}

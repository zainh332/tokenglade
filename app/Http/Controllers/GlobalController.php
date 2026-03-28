<?php

namespace App\Http\Controllers;

use App\Models\Blockchain;
use App\Models\Staking;
use App\Models\StakingReward;
use App\Models\StellarToken;
use App\Models\Token;
use App\Models\WalletType;
use App\Services\WalletService;
use Illuminate\Http\Request;

use Exception;
use Illuminate\Support\Facades\Log;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Network;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;

class GlobalController extends Controller
{
    private $sdk, $assetIssuer, $stakingRewardWalletKey, $stakingPublicWalletKey, $network;
    private WalletService $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT');

        if ($stellarEnv === 'public') {
            $this->sdk = StellarSDK::getPublicNetInstance();
            $this->stakingRewardWalletKey = env('STAKING_REWARD_WALLET_KEY');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY');
            $this->assetIssuer =  env('TKG_ISSUER_PUBLIC');
            $this->network = Network::public();
        } else {
            $this->sdk = StellarSDK::getTestNetInstance();
            $this->stakingRewardWalletKey = env('STAKING_REWARD_WALLET_KEY_TESTNET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY_TESTNET');
            $this->assetIssuer =  env('TKG_ISSUER_TESTNET');
            $this->network = Network::testnet();
        }
    }

    public function check_xlm_balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_wallet' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $balance = $this->wallet->getXlmBalance($request->public_wallet);

        return response()->json([
            'status'    => 1,
            'total_xlm' => (float) $balance,
        ]);
    }

    public function check_tkg_balance(Request $request)
    {
        try {
            $data = $request->validate([
                'public_wallet' => ['required', 'string'],
                'min' => ['nullable', 'numeric'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        }

        try {
            $min = $data['min'] ?? null;
            $result = $this->wallet->getTkgBalance($data['public_wallet'], $min);

            // If "min" provided, service returns bool. Otherwise it returns float balance.
            if ($min !== null) {
                return response()->json([
                    'status' => 1,
                    'hasMin' => (bool) $result,
                    'min'    => (float) $min,
                ]);
            }

            return response()->json([
                'status'    => 1,
                'balance' => (float) $result,
            ]);
        } catch (HorizonRequestException $e) {
            if ($e->getStatusCode() === 404) {
                // account not funded / not found
                return response()->json([
                    'status'      => 1,
                    'balance' => 0.0,
                ]);
            }
            return response()->json([
                'status'  => 0,
                'message' => 'Horizon error',
                'code'    => $e->getStatusCode(),
            ], 502);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unexpected error',
            ], 500);
        }
    }


    public function wallet_types()
    {
        try {
            $wallets = WalletType::where('status', 1)
                ->select('id', 'name', 'key', 'blockchain_id')
                ->get();

            return response()->json([
                'status' => 'success',
                'wallets' => $wallets,
            ]);
        } catch (\Throwable $e) {
            Log::error('wallet_types error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch wallet types.',
            ], 500);
        }
    }

    public function blockchains()
    {
        try {
            $blockchains = Blockchain::query()
                ->orderBy('name')
                ->get();

            return response()->json([
                'status'      => 'success',
                'blockchains' => $blockchains,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Failed to fetch blockchains', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            $payload = [
                'status'  => 'error',
                'message' => 'Failed to load blockchains. Please try again later.',
            ];

            if (config('app.debug')) {
                $payload['debug'] = $e->getMessage();
            }

            return response()->json($payload, 500);
        }
    }


    public function fetch_holding_tokens_claim_claimable_balance(Request $request)
    {
        $wallet_address = $request->json('wallet_key');

        // Continue only if wallet_address is not null
        if ($wallet_address !== null) {
            try {
                // Fetch details of the wallet from the public address
                $WalletAccount = $this->sdk->requestAccount($wallet_address);

                $tokens = []; // Initialize an array to hold non-native assets
                $totalXLM = 0;

                // Loop through the balances and fetch non-native assets
                foreach ($WalletAccount->getBalances() as $balance) {
                    if ($balance->getAssetType() === 'native') {
                        // Store the XLM balance if the asset type is 'native'
                        $totalXLM = $balance->getBalance();
                    } else {
                        // Only store non-native assets with a balance greater than 0
                        $asset_balance = $balance->getBalance();
                        if ($asset_balance > 0) {
                            $tokens[] = [
                                'code' => $balance->getAssetCode(), // Asset code
                                'issuer' => $balance->getAssetIssuer(), // Asset issuer
                                'balance' => $asset_balance, // Asset balance
                            ];
                        }
                    }
                }

                if (count($tokens) > 0) {
                    return response()->json([
                        'status' => 'success',
                        'tokens' => $tokens,
                        'total_xlm' => $totalXLM, // Include the total XLM balance in the response
                    ]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'No tokens found in your wallet']);
                }
            } catch (\InvalidArgumentException $e) {
                return response()->json(['status' => 'error', 'message' => 'Invalid Wallet Address']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'Wallet is not active']);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => 'Wallet address is required']);
        }
    }

    public function generated_tokens()
    {
        try {
            $get_wallets_tokens = Token::with([
                'stellarToken.mintTransaction',
                'blockchain'
            ])
                ->whereHas('stellarToken', function ($q) {
                    $q->whereNotNull('issuer_public_key')
                        ->where('issuer_public_key', '!=', '');
                })
                ->latest()
                ->take(6)
                ->get()
                ->map(function ($token) {

                    $stellar = $token->stellarToken;
                    $mintTx = $stellar->mintTransaction;

                    return [
                        'id' => $token->id,
                        'name' => $stellar->name ?? null,
                        'token_verify' => (int) ($token->token_verify ?? 0),
                        'asset_code' => $stellar->asset_code ?? null,
                        'total_supply' => $stellar->total_supply ?? null,
                        'logo_url' => $stellar->logo ?? null,
                        'issuer_locked' => $stellar->issuer_locked ?? false,
                        'tx_hash' => $mintTx->tx_hash ?? null,
                        'blockchain' => [
                            'name' => $token->blockchain->name ?? null,
                        ],
                    ];
                });


            return response()->json([
                'status' => 'success',
                'tokens' => $get_wallets_tokens,
            ]);
        } catch (\Throwable $e) {
            Log::error('generated_tokens error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch generated tokens.',
            ], 500);
        }
    }

    public function count_data()
    {
        try {
            $total_tokens = StellarToken::count();
            return response()->json([
                'status' => 'success',
                'total_tokens' => $total_tokens,
            ]);
        } catch (\Throwable $e) {
            Log::error('count_data error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch total token count.',
            ], 500);
        }
    }

    public function staking_reward(Request $request)
    {
        try {
            $limit = min(max((int)$request->input('limit', 20), 1), 100);

            $rows = StakingReward::query()
                ->with([
                    'staking' => fn($q) => $q->select('id', 'user_id')
                        ->with(['user:id,public_key']),
                ])
                ->whereHas('staking.user')
                ->latest('created_at')
                ->limit($limit)
                ->get(['id', 'staking_id', 'amount', 'transaction_id', 'created_at']);

            $out = $rows->map(function (StakingReward $r) {
                return [
                    'wallet_address' => $r->staking?->user?->public_key,
                    'reward'         => (float) $r->amount,
                    'transaction'    => $r->transaction_id,
                    'at'             => optional($r->created_at)->toIso8601String(),
                ];
            });

            return response()->json([
                'status'        => 'success',
                'stakingreward' => $out,
            ]);
        } catch (\Throwable $e) {
            Log::error('staking_reward error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch staking rewards.',
            ], 500);
        }
    }

    public function live_staking_stats()
    {
        try {
            $total_staked = Staking::where('is_withdrawn', 0)
                ->whereNotNull('transaction_id')
                ->where('staking_status_id', '!=', 4)
                ->sum('amount');

            $active_stakers = Staking::where('is_withdrawn', 0)
                ->whereNotNull('transaction_id')
                ->where('staking_status_id', '!=', 4)
                ->distinct('user_id')
                ->count('user_id');

            $rewards_paid = StakingReward::where('created_at', '>=', now()->subDay())
                ->sum('amount');

            $total_payouts = StakingReward::sum('amount');

            $stats = [
                'total_staked'   => (float) $total_staked,
                'active_stakers' => $active_stakers,
                'rewards_paid'   => (float) $rewards_paid,
                'total_payouts'  => (float) $total_payouts,
            ];

            return response()->json([
                'status' => 'success',
                'stats'  => $stats,
            ]);
        } catch (\Throwable $e) {
            Log::error('live_staking_stats error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch live staking stats.',
            ], 500);
        }
    }
}

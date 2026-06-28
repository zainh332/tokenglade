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

    public function verified_projects()
    {
        try {
            $projects = \App\Models\VerifiedProject::where('status', 1)
                ->get()
                ->map(function ($p) {
                    $supply = 0;
                    $price = 0.0;
                    $liq = 0;
                    $desc = "Verified asset \"{$p->asset_code}\" on the Stellar network.";
                    $logoUrl = null;

                    try {
                        // Fetch details from StellarExpert on backend (immune to CORS!)
                        $response = \Illuminate\Support\Facades\Http::timeout(5)
                            ->get("https://api.stellar.expert/explorer/public/asset/{$p->asset_code}-{$p->identifier}");

                        if ($response->successful()) {
                            $data = $response->json();
                            $supply = isset($data['supply']) ? (float)$data['supply'] / 10000000 : 0;
                            $price = isset($data['price']) ? (float)$data['price'] : 0.0;
                            $liq = isset($data['liquidity']) ? (float)$data['liquidity'] / 10000000 : 0;
                            $desc = $data['toml_info']['conditions'] ?? ($data['toml_info']['desc'] ?? $desc);
                            $logoUrl = $data['toml_info']['image'] ?? null;
                        }
                    } catch (\Throwable $seEx) {
                        \Illuminate\Support\Facades\Log::warning("StellarExpert fetch failed for {$p->asset_code}", ['msg' => $seEx->getMessage()]);
                    }

                    return [
                        'id' => $p->id,
                        'name' => $p->name ?? $p->asset_code,
                        'symbol' => $p->asset_code,
                        'desc' => $desc,
                        'mcap' => round($supply * $price),
                        'supply' => round($supply),
                        'liq' => round($liq),
                        'logo_url' => $logoUrl,
                        'website' => $p->website,
                        'twitter' => $p->twitter,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'projects' => $projects,
            ]);
        } catch (\Throwable $e) {
            Log::error('verified_projects error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch verified projects.',
            ], 500);
        }
    }

    public function wallet_analytics(Request $request)
    {
        try {
            $request->validate([
                'public_wallet' => 'required|string|size:56|starts_with:G',
            ]);

            $publicKey = $request->public_wallet;

            // Fetch balances from Stellar Network via Horizon SDK
            $sdk = \Soneso\StellarSDK\StellarSDK::getPublicNetInstance();
            $xlm = 0.0;
            $tkg = 0.0;
            try {
                $account = $sdk->requestAccount($publicKey);
                if ($account) {
                    foreach ($account->getBalances() as $bal) {
                        if ($bal->getAssetType() === 'native') {
                            $xlm = (float) $bal->getBalance();
                        } else if ($bal->getAssetCode() === 'TKG') {
                            $tkg = (float) $bal->getBalance();
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Account not created or request failed
            }

            // Active LP Positions
            $lpActivePoolsCount = \App\Models\LiquidityPoolParticipant::where('wallet_address', $publicKey)
                ->where('is_active', 1)
                ->count();

            // Total Earned Staking Rewards
            $stakingRewardsSum = \App\Models\StakingReward::whereHas('staking', function ($q) use ($publicKey) {
                $q->whereHas('user', function ($u) use ($publicKey) {
                    $u->where('public_key', $publicKey);
                });
            })->sum('amount');

            // Total Earned LP Rewards
            $lpRewardsSum = \App\Models\LpRewardDistribution::where('wallet_address', $publicKey)->sum('reward_amount');

            $totalRewards = $stakingRewardsSum + $lpRewardsSum;

            // Estimated Portfolio Net Worth (XLM price: $0.1254, TKG price: $0.0450 as fallbacks)
            $xlmPrice = 0.1254;
            $tkgPrice = 0.0450;
            $netWorth = ($xlm * $xlmPrice) + ($tkg * $tkgPrice);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'xlm_balance' => $xlm,
                    'tkg_balance' => $tkg,
                    'lp_pools_count' => $lpActivePoolsCount,
                    'total_rewards' => $totalRewards,
                    'net_worth' => $netWorth,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('wallet_analytics error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch wallet analytics.',
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

    public function lp_rewards_data()
    {
        try {
            // 1. Total LP TKG Distributed
            $total_distributed = \App\Models\LpRewardDistribution::where('status', 'sent')->sum('reward_amount');

            // 2. Total active LP providers in our database
            $active_providers = \App\Models\LiquidityPoolParticipant::where('wallet_status', 'active')->count();

            // 3. Weekly Reward Pool from settings
            $weekly_reward_setting = \App\Models\Setting::where('key', 'lp_weekly_reward_amount')->first();
            $weekly_reward_pool = $weekly_reward_setting ? (float)$weekly_reward_setting->value : 16000.0;

            // 4. Completed cycles count
            $completed_cycles = \App\Models\LpRewardCycle::where('status', 'completed')->count();

            // 4b. Current week number
            $current_week = \Carbon\Carbon::now()->weekOfYear;

            // 5. Recent completed reward cycles (e.g. latest 4)
            $cycles = \App\Models\LpRewardCycle::withCount([
                'distributions as rewarded_wallets_count' => function ($query) {
                    $query->where('status', 'sent');
                }
            ])
            ->withSum([
                'distributions as total_reward_distributed' => function ($query) {
                    $query->where('status', 'sent');
                }
            ], 'reward_amount')
            ->where('status', 'completed')
            ->orderBy('week_number', 'desc')
            ->take(4)
            ->get();

            $cycles_list = $cycles->map(function ($cycle) {
                return [
                    'week' => $cycle->week_number,
                    'date' => $cycle->snapshot_date ? \Carbon\Carbon::parse($cycle->snapshot_date)->toDateString() : '',
                    'pool' => (float) $cycle->total_reward_pool,
                    'wallets' => (int) $cycle->rewarded_wallets_count,
                    'distributed' => (float) ($cycle->total_reward_distributed ?? 0),
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_distributed' => (float) $total_distributed,
                    'active_providers' => $active_providers,
                    'weekly_reward_pool' => $weekly_reward_pool,
                    'completed_cycles' => $completed_cycles,
                    'current_week' => $current_week,
                    'cycles_list' => $cycles_list,
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('lp_rewards_data error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch LP rewards data.',
            ], 500);
        }
    }

    public function lp_reserves(Request $request)
    {
        $poolIdHex = 'cb1922681c9d2380d34577d3c056e435a8436586e776c38a80412120c2442fb5';
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT', 'public');
        $isTestnet = strtolower($stellarEnv) !== 'public';
        $horizonUrl = $isTestnet ? 'https://horizon-testnet.stellar.org' : 'https://horizon.stellar.org';

        try {
            $poolResponse = \Illuminate\Support\Facades\Http::timeout(10)->acceptJson()->get("{$horizonUrl}/liquidity_pools/{$poolIdHex}");

            $xlmReserve = 0.0;
            $tkgReserve = 0.0;

            if ($poolResponse->successful()) {
                $poolData = $poolResponse->json();
                foreach ($poolData['reserves'] as $reserve) {
                    if ($reserve['asset'] === 'native') {
                        $xlmReserve = (float) $reserve['amount'];
                    } else {
                        $parts = explode(':', $reserve['asset']);
                        $code = $parts[0] ?? '';
                        if ($code === 'TKG') {
                            $tkgReserve = (float) $reserve['amount'];
                        }
                    }
                }
            }

            // Fallbacks if reserves not found (e.g. pool not funded yet on Horizon)
            if ($xlmReserve <= 0 || $tkgReserve <= 0) {
                $xlmReserve = 1000.0; 
                $tkgReserve = 16000.0;
            }

            $user_xlm = 0.0;
            $user_tkg = 0.0;
            $wallet_address = $request->input('wallet_address');

            if ($wallet_address) {
                try {
                    $user_xlm = (float) $this->wallet->getXlmBalance($wallet_address);
                    $user_tkg = (float) $this->wallet->getTkgBalance($wallet_address);
                } catch (\Throwable $e) {
                    // Ignore errors fetching wallet balance
                }
            }

            return response()->json([
                'status' => 'success',
                'pool_id' => $poolIdHex,
                'xlm_reserve' => $xlmReserve,
                'tkg_reserve' => $tkgReserve,
                'ratio' => $tkgReserve / $xlmReserve, // TKG per 1 XLM
                'user_xlm' => $user_xlm,
                'user_tkg' => $user_tkg,
            ]);
        } catch (\Throwable $e) {
            Log::error('lp_reserves error', ['message' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch pool reserves: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function lp_deposit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wallet_address' => ['required', 'string'],
            'xlm_amount' => ['required', 'numeric', 'min:0.0000001'],
            'tkg_amount' => ['required', 'numeric', 'min:0.0000001'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $wallet_address = $request->wallet_address;
            $xlm_amount = $request->xlm_amount;
            $tkg_amount = $request->tkg_amount;

            // Load user account from Horizon
            $userAccount = $this->sdk->requestAccount($wallet_address);

            // TKG Asset
            $tkgAsset = \Soneso\StellarSDK\Asset::createNonNativeAsset('TKG', $this->assetIssuer);
            $nativeAsset = \Soneso\StellarSDK\Asset::native();

            // Sort assets to determine correct A/B price bounds and pool shares
            $assetA = $nativeAsset;
            $assetB = $tkgAsset;
            
            $swap = false;
            $rank = function ($as) {
                return $as->getType() === \Soneso\StellarSDK\Asset::TYPE_NATIVE ? 0
                    : ($as->getType() === \Soneso\StellarSDK\Asset::TYPE_CREDIT_ALPHANUM_4 ? 1
                        : ($as->getType() === \Soneso\StellarSDK\Asset::TYPE_CREDIT_ALPHANUM_12 ? 2 : 3));
            };
            if ($rank($assetA) > $rank($assetB)) $swap = true;
            elseif (
                $rank($assetA) === $rank($assetB)
                && $assetA instanceof \Soneso\StellarSDK\AssetTypeCreditAlphanum
                && $assetB instanceof \Soneso\StellarSDK\AssetTypeCreditAlphanum
            ) {
                $codeCmp = strcmp($assetA->getCode(), $assetB->getCode());
                if ($codeCmp > 0 || ($codeCmp === 0 && strcmp($assetA->getIssuer(), $assetB->getIssuer()) > 0)) $swap = true;
            }
            if ($swap) {
                [$assetA, $assetB] = [$assetB, $assetA];
            }

            // Price bounds are determined by A/B price, so amountA / amountB
            $amountA = $swap ? $tkg_amount : $xlm_amount;
            $amountB = $swap ? $xlm_amount : $tkg_amount;
            $rawPrice = $amountA / $amountB;

            // Slippage tolerance of 2%
            $minPriceVal = $rawPrice * 0.98;
            $maxPriceVal = $rawPrice * 1.02;

            $minN = (int) round($minPriceVal * 10000000);
            $minD = 10000000;
            $maxN = (int) round($maxPriceVal * 10000000);
            $maxD = 10000000;

            $minPrice = new \Soneso\StellarSDK\Price($minN, $minD);
            $maxPrice = new \Soneso\StellarSDK\Price($maxN, $maxD);

            $txb = (new \Soneso\StellarSDK\TransactionBuilder($userAccount, $this->network));

            // 1. Ensure user has trustline to TKG
            $hasTkgTrustline = false;
            foreach ($userAccount->getBalances() as $bal) {
                if ($bal->getAssetType() !== 'native') {
                    if ($bal->getAssetCode() === 'TKG' && $bal->getAssetIssuer() === $this->assetIssuer) {
                        $hasTkgTrustline = true;
                        break;
                    }
                }
            }
            if (!$hasTkgTrustline) {
                $txb->addOperation((new \Soneso\StellarSDK\ChangeTrustOperationBuilder($tkgAsset))->build());
            }

            // 2. Ensure user has trustline to Liquidity Pool shares
            $poolId = 'cb1922681c9d2380d34577d3c056e435a8436586e776c38a80412120c2442fb5';
            $poolShareAsset = new \Soneso\StellarSDK\AssetTypePoolShare($assetA, $assetB);

            $hasPoolShareTrustline = false;
            foreach ($userAccount->getBalances() as $bal) {
                if ($bal->getAssetType() === 'liquidity_pool_shares') {
                    if ($bal->getLiquidityPoolId() === $poolId) {
                        $hasPoolShareTrustline = true;
                        break;
                    }
                }
            }
            if (!$hasPoolShareTrustline) {
                $txb->addOperation(new \Soneso\StellarSDK\ChangeTrustOperation($poolShareAsset, '922337203685.4775807'));
            }

            // 3. Add LiquidityPoolDeposit
            $xlmAmountFormatted = number_format((float)$xlm_amount, 7, '.', '');
            $tkgAmountFormatted = number_format((float)$tkg_amount, 7, '.', '');

            $txb->addOperation(
                (new \Soneso\StellarSDK\LiquidityPoolDepositOperationBuilder(
                    $poolId,
                    $xlmAmountFormatted,
                    $tkgAmountFormatted,
                    $minPrice,
                    $maxPrice
                ))->build()
            );

            $tx = $txb->build();
            $unsignedXdr = $tx->toEnvelopeXdrBase64();

            return response()->json([
                'status' => 'success',
                'unsigned_xdr' => $unsignedXdr,
            ]);
        } catch (\Throwable $e) {
            Log::error('lp_deposit prepare error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to prepare liquidity deposit transaction: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function lp_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'signedXdr' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $signedXdr = trim($request->signedXdr);

            if (base64_decode($signedXdr, true) === false) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'signedXdr is not valid base64.',
                ], 422);
            }

            $tx = \Soneso\StellarSDK\Transaction::fromEnvelopeBase64XdrString($signedXdr);
            $result = $this->sdk->submitTransaction($tx);

            if ($result->isSuccessful()) {
                // Sync the liquidity pool participants instantly
                try {
                    app(\App\Services\LpSyncService::class)->sync();
                } catch (\Throwable $syncEx) {
                    Log::warning('LP sync failed after successful deposit', ['message' => $syncEx->getMessage()]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Liquidity added successfully!',
                    'tx_hash' => $result->getId(),
                ]);
            } else {
                $resultCodes = $result->getExtras()?->getResultCodes();
                $errString = $this->parseHorizonError('Transaction failed on Horizon.', $resultCodes);
                return response()->json([
                    'status' => 'error',
                    'message' => $errString,
                    'result_codes' => $resultCodes,
                ], 400);
            }
        } catch (\Soneso\StellarSDK\Exceptions\HorizonRequestException $e) {
            $prev = $e->getPrevious();
            $body = null;
            $resultCodes = null;
            if ($prev instanceof \GuzzleHttp\Exception\ClientException && $prev->getResponse()) {
                $body = (string)$prev->getResponse()->getBody();
                $decodedBody = json_decode($body, true);
                $resultCodes = $decodedBody['extras']['result_codes'] ?? null;
            }
            
            Log::error('lp_submit HorizonRequestException', [
                'message' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'body' => $body,
                'result_codes' => $resultCodes,
            ]);

            $errString = $this->parseHorizonError($e->getMessage(), $resultCodes);

            return response()->json([
                'status' => 'error',
                'message' => $errString,
                'result_codes' => $resultCodes,
            ], 400);
        } catch (\Throwable $e) {
            Log::error('lp_submit error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit transaction: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function parseHorizonError(string $message, ?array $resultCodes = null): string
    {
        $txCode = $resultCodes['transaction'] ?? '';
        $opCodes = $resultCodes['operations'] ?? [];

        // Convert the message to lowercase for case-insensitive matching
        $lowerMsg = strtolower($message);

        // 1. Fee / Insufficient Balance errors
        if ($txCode === 'tx_insufficient_balance' || strpos($lowerMsg, 'tx_insufficient_balance') !== false) {
            return 'Your wallet does not have enough XLM to pay for network transaction fees. Please add a small amount of XLM to your wallet.';
        }

        // 2. Underfunded error (lack of XLM or TKG balance)
        if (in_array('op_underfunded', $opCodes) || strpos($lowerMsg, 'op_underfunded') !== false) {
            return 'Insufficient spendable balance. Please check that you have enough XLM and TKG in your wallet. Note: Stellar reserves a minimum of 2.5 XLM that cannot be spent or deposited.';
        }

        // 3. Low reserve error (dropping below minimum reserve)
        if (in_array('op_low_reserve', $opCodes) || strpos($lowerMsg, 'op_low_reserve') !== false) {
            return 'Your wallet balance is too close to the Stellar minimum reserve limit. You need to keep at least 2.5 XLM in your wallet to cover the active trustlines.';
        }

        // 4. Bad authorization or rejected signatures
        if ($txCode === 'tx_bad_auth' || $txCode === 'tx_bad_auth_extra' || 
            strpos($lowerMsg, 'tx_bad_auth') !== false || strpos($lowerMsg, 'tx_bad_auth_extra') !== false) {
            return 'Wallet signature was invalid or rejected. Please verify you are using the correct connected wallet and approved the prompt.';
        }

        // 5. Sequence error
        if ($txCode === 'tx_bad_seq' || strpos($lowerMsg, 'tx_bad_seq') !== false) {
            return 'Transaction out of sync. Please refresh the page and try again.';
        }

        // 6. Timebounds / expired
        if ($txCode === 'tx_too_late' || strpos($lowerMsg, 'tx_too_late') !== false) {
            return 'Transaction took too long to sign and has expired. Please try again.';
        }

        // 7. No Trustline
        if (in_array('op_no_trust', $opCodes) || strpos($lowerMsg, 'op_no_trust') !== false) {
            return 'Trustline missing. Please verify your wallet has trustlines configured for TKG and the liquidity pool shares.';
        }

        // 8. Slippage / Price limits exceeded
        if (in_array('op_invalid_price', $opCodes) || in_array('op_bad_price', $opCodes) || 
            strpos($lowerMsg, 'op_invalid_price') !== false || strpos($lowerMsg, 'op_bad_price') !== false) {
            return 'The pool exchange rate changed slightly. This exceeded the 2% slippage tolerance. Please try again.';
        }

        // 9. Line full
        if (in_array('op_line_full', $opCodes) || strpos($lowerMsg, 'op_line_full') !== false) {
            return 'Your wallet has reached the limit for holding liquidity pool shares.';
        }

        // Default: If there are operation codes we didn't map, format them nicely
        if (!empty($opCodes)) {
            $cleaned = array_map(function($c) { return str_replace('op_', '', $c); }, $opCodes);
            return 'Stellar network rejected the transaction. Reason: ' . implode(', ', $cleaned);
        }

        // Otherwise return a generic rejected message
        return 'Stellar network rejected the transaction. Please make sure your wallet is funded and has enough XLM for reserves.';
    }
}


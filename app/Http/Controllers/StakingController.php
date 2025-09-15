<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staking;
use App\Models\StakingAsset;
use App\Models\StakingReward;
use App\Models\StakingTransaction;
use App\Models\User;
use App\Services\WalletService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Soneso\StellarSDK\AssetTypeCreditAlphanum4;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Transaction;
use Soneso\StellarSDK\TransactionBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;

class StakingController extends Controller
{
    private $sdk, $minAmount, $maxFee, $assetCode, $assetIssuer;
    private $stakingRewardWalletKey, $stakingPublicWalletKey, $network, $stakingPublicWallet, $stakingRewardWallet;
    private WalletService $wallet;

    public function __construct(WalletService $wallet)
    {
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT');

        if ($stellarEnv === 'public') {
            $this->sdk = StellarSDK::getPublicNetInstance();

            $this->stakingRewardWallet = env('STAKING_REWARD_WALLET');
            $this->stakingRewardWalletKey = env('STAKING_REWARD_WALLET_KEY');

            $this->stakingPublicWallet = env('STAKING_PUBLIC_WALLET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY');

            $this->network = Network::public();
            $this->assetIssuer =  env('TKG_ISSUER_PUBLIC');
        } else {
            $this->sdk = StellarSDK::getTestNetInstance();

            $this->stakingRewardWallet = env('STAKING_REWARD_WALLET_TESTNET');
            $this->stakingRewardWalletKey = env('STAKING_REWARD_WALLET_KEY_TESTNET');

            $this->stakingPublicWallet = env('STAKING_PUBLIC_WALLET_TESTNET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY_TESTNET');

            $this->assetIssuer =  env('TKG_ISSUER_TESTNET');
            $this->network = Network::testnet();
        }

        $this->minAmount = 1500;
        $this->maxFee = 3000;
        $this->assetCode = 'TKG';
        $this->wallet = $wallet;
    }


    public function start_staking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staking_asset_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1500'],
            'public_key' => ['required', 'string'],
        ], [
            'amount.required' => 'The amount field is required.',
            'amount.integer'  => 'The amount must be a valid number.',
            'amount.min'      => 'The amount must be at least 1500.',
            'public_key.required' => 'The public key is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'error'    => 'Validation error!',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!isset($_COOKIE['public_key'])) {
            return response()->json(['status' => 'error', 'message' => 'Wallet not connected!']);
        }

        $public = $_COOKIE['public_key'] ?? null;
        $userId = $public ? User::where('public_key', $public)->value('id') : null;

        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'Wallet not found!']);
        }

        $staking_asset = StakingAsset::find($request->staking_asset_id);
        if (!$staking_asset) {
            return response()->json(['status' => 'error', 'message' => 'Staking asset not found!']);
        }

        // Make sure the account exists on Stellar (and we can read its balances)
        try {
            $account = $this->sdk->requestAccount($public);
        } catch (HorizonRequestException $e) {
            if ($e->getStatusCode() == 404) {
                return response()->json(['status' => 'error', 'message' => 'Stellar account does not exist or is not funded!']);
            }
            return response()->json(['status' => 'error', 'message' => 'Horizon error, please try again later.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Unexpected error. Please try again.']);
        }

        // Detect TKG trustline (issuer-specific)
        $hasTrustline = false;
        foreach ($account->getBalances() as $b) {
            if (
                $b->getAssetType() === 'credit_alphanum4' &&
                $b->getAssetCode() === 'TKG' &&
                $b->getAssetIssuer() === $this->assetIssuer
            ) {
                $hasTrustline = true;
                break;
            }
        }

        if (!$hasTrustline) {
            return response()->json(['status' => 'error', 'message' => 'Wallet does not have TKG trustline!']);
        }

        // Use your helper to get TKG balance (or compare directly if you implemented boolean return)
        $required = (float) $request->amount;

        // --- If your helper returns the BALANCE (original implementation) ---
        $tkgBalance = $this->wallet->getTkgBalance($public);

        if ($tkgBalance === false) {
            return response()->json(['status' => 'error', 'message' => 'Stellar account not found on network!']);
        }
        if ($tkgBalance === null) {
            return response()->json(['status' => 'error', 'message' => 'Could not read TKG balance. Try again later.']);
        }
        if ($tkgBalance < $required) {
            return response()->json(['status' => 'error', 'message' => 'Not enough TKG Tokens!']);
        }

        DB::beginTransaction();

        try {

            $source = $this->sdk->requestAccount($public);
            $asset = new AssetTypeCreditAlphanum4($this->assetCode, $this->assetIssuer);

            $paymentOp = (new PaymentOperationBuilder($this->stakingPublicWallet, $asset, $request->amount))
                ->setSourceAccount($public)
                ->build();

            $transaction = (new TransactionBuilder($source, $this->network))
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'TKG staking'))
                ->addOperation($paymentOp)
                ->build();

            $unsigned_xdr = $transaction->toEnvelopeXdrBase64();

            // Operation failed
            if (!$unsigned_xdr) {
                throw new \Exception('Something went wrong during staking operation.');
                Log::info('Something went wrong during staking operation.');
            }

            $existing_staking = Staking::where('user_id', $userId)
                ->where('is_withdrawn', false)
                ->whereNotNull('transaction_id')
                ->latest()
                ->first();

            if ($existing_staking) {
                $newTotal = (float)$existing_staking->amount + (float)$request->amount;

                // compute tier + apy for TKG totals
                [$tier, $apy] = $this->tkgTierAndApy($newTotal);

                $existing_staking->amount = $newTotal;
                $existing_staking->tier   = $tier;
                $existing_staking->apy    = $apy;
                $existing_staking->staking_status_id    = 2; //topped up
                $existing_staking->save();

                $this->addStakingTransactionRecord($existing_staking->id, null, $unsigned_xdr, null, null, 2);
            } else {
                // first stake in this asset
                $startTotal = (float)$request->amount;
                [$tier, $apy] = $this->tkgTierAndApy($startTotal);

                $new_stake = new Staking();
                $new_stake->user_id = $userId;
                $new_stake->staking_asset_id  = $staking_asset->id;
                $new_stake->amount            = $startTotal;
                $new_stake->tier              = $tier;
                $new_stake->apy               = $apy;
                $new_stake->staking_status_id    = 1;
                $new_stake->save();

                $this->addStakingTransactionRecord($new_stake->id, null, $unsigned_xdr, null, null, 1);
            }

            DB::commit();
            return response()->json(['xdr' => $unsigned_xdr, 'status' => 'success', 'staking_id' => $existing_staking ? $existing_staking->id : $new_stake->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function submit_xdr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'signedXdr'  => [
                'required',
                function ($attr, $value, $fail) {
                    if (!is_string($value) && !is_array($value)) {
                        $fail('signedXdr must be a base64 string or a wallet response object.');
                    }
                },
            ],
            'staking_id' => ['required', 'integer', 'exists:stakings,id'],
        ], [
            'signedXdr.required'  => 'signedXdr is required.',
            'staking_id.required' => 'staking_id is required.',
            'staking_id.integer'  => 'staking_id must be an integer.',
            'staking_id.exists'   => 'The specified staking record does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $raw = $request->signedXdr;

        if (is_array($raw)) {
            $signedXdr = $raw['signedTxXdr']         // Freighter
                ?? $raw['xdr']                       // Rabet / xbull
                ?? $raw['signed_envelope_xdr']       // Albedo
                ?? $raw['envelope_xdr']              // fallback
                ?? null;
        } else {
            $signedXdr = $raw;
        }

        if (!is_string($signedXdr) || trim($signedXdr) === '') {
            return response()->json([
                'success' => false,
                'error'   => 'Invalid signedXdr: expected base64 envelope XDR string.',
            ], 422);
        }

        $signedXdr = trim($signedXdr);

        if (base64_decode($signedXdr, true) === false) {
            return response()->json([
                'success' => false,
                'error'   => 'signedXdr is not valid base64.',
            ], 422);
        }

        $staking = Staking::where('id', $request->staking_id)->first();
        if (!$staking) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong!']);
        }

        try {

            $tx = Transaction::fromEnvelopeBase64XdrString($signedXdr);
            $result = $this->sdk->submitTransaction($tx);

            $txID = $result->getId();
            if ($txID) {
                $staking->transaction_id = $txID;
                $staking->save();
            }

            $this->addStakingTransactionRecord($staking->id, null, null, $signedXdr, $txID, $staking->staking_status_id);

            return response()->json(['status' => 'success', 'message' => 'Staking Succeffull'], 200);
        } catch (HorizonRequestException $e) {
            Log::info('Error while submitting Xdr.' . $e);
            return response()->json(['status' => 'error', 'message' => 'Failed!']);
        }
    }

    public function unstake(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'staking_id' => ['required', 'integer', 'exists:stakings,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error'   => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $staking = Staking::with('user:id,public_key')->find($request->staking_id);
        if (!$staking) {
            return response()->json(['status' => 'error', 'message' => 'Staking not found!'], 404);
        }

        $active_staking_wallet = Staking::whereKey($request->staking_id)
            ->with('user:id,public_key')
            ->where('staking_status_id', '<>', 4)
            ->where('is_withdrawn', false)
            ->whereNotNull('transaction_id')
            ->first();

        if (!$active_staking_wallet) {
            return response()->json(['status' => 'error', 'message' => 'Already stopped staking!'], 422);
        }

        try {
            $mainPair = KeyPair::fromSeed($this->stakingPublicWalletKey);

            $mainAccount = $this->sdk->requestAccount($mainPair->getAccountId());
            $account = $this->sdk->requestAccount($active_staking_wallet->user->public_key);

            $asset = new AssetTypeCreditAlphanum4($this->assetCode, $this->assetIssuer);

            // Payment Operation Returning TKG Tokens from Staking Public Wallet to User Wallet
            $paymentOperation = (new PaymentOperationBuilder($account->getAccountId(), $asset, $active_staking_wallet->amount))->build();
            $txbuilder = new TransactionBuilder($mainAccount);
            $txbuilder->setMaxOperationFee($this->maxFee);
            $transaction = $txbuilder->addOperation($paymentOperation)->addMemo(Memo::text('TKG Staking Stopped'))->build();
            $transaction->sign($mainPair, $this->network);
            $res = $this->sdk->submitTransaction($transaction);

            $active_staking_wallet->is_withdrawn = true;
            $active_staking_wallet->transaction_id = $res->getId();
            $active_staking_wallet->staking_status_id = 4; //unstaked
            $active_staking_wallet->save();

            $this->addStakingTransactionRecord($active_staking_wallet->id, null, null, null, $res->getId(), $active_staking_wallet->staking_status_id);

            return response()->json(['status' => 'success', 'message' => 'Sucessfully Stoped Staking and ' . $active_staking_wallet->amount . ' TKG tokens are sent back to your wallet', 'tx' => $res->getId()]);
        } catch (\Throwable $th) {
            Log::error('Stop Staking Error: ' . $th->getMessage());
            Log::info('Error while stop staking.');
            return response()->json(['status' => 'error', 'message' => 'An error occurred while processing the transaction.']);
        }
    }

    // Job distributing staking reward
    public function reward_distribution()
    {
        Log::info('staking.reward_distribution.start', [
            'now'      => now()->toIso8601String(),
            'min'      => $this->minAmount,
            'env'      => env('VITE_STELLAR_ENVIRONMENT'),
        ]);

        try {
            $invests = Staking::query()
                ->with(['user:id,public_key'])
                ->whereNotNull('transaction_id')
                ->where('amount', '>=', $this->minAmount)
                ->where('is_withdrawn', false)
                ->where('staking_status_id', '<>', 4)
                ->where('updated_at', '<=', now()->subHours(24)) // pay at most once a day
                ->get();

            if ($invests->isEmpty()) {
                Log::info('staking.reward_distribution.no_eligible', [
                    'reason' => 'No rows matched eligibility filters'
                ]);
                return response()->json(['processed' => 0]);
            }

            Log::info('staking.reward_distribution.eligible_found', [
                'count' => $invests->count()
            ]);

            $processed = 0;

            foreach ($invests as $invest) {
                try {
                    $since = $invest->updated_at ?? $invest->created_at;
                    $days  = max(1, $since->diffInDays(now()));

                    Log::info('staking.reward_distribution.evaluate', [
                        'staking_id'  => $invest->id,
                        'user_id'     => $invest->user_id,
                        'public_key'  => $invest->user?->public_key,
                        'amount'      => (float) $invest->amount,
                        'apy'         => (float) $invest->apy,
                        'last_mark'   => optional($since)->toIso8601String(),
                        'days'        => $days,
                    ]);

                    $result = $this->reward($invest, $days);

                    if ($result) {
                        $reward = StakingReward::create([
                            'staking_id'     => $invest->id,
                            'amount'         => $result->amount,
                            'transaction_id' => $result->tx,
                        ]);

                        Log::info('staking.reward_distribution.reward_recorded', [
                            'staking_id'       => $invest->id,
                            'reward_id'        => $reward->id,
                            'amount'           => $result->amount,
                            'horizon_tx'       => $result->tx,
                        ]);

                        $this->addStakingTransactionRecord(
                            $invest->id,
                            $reward->id,
                            $result->unsignedXdr ?? null,  
                            $result->signedXdr   ?? null,  
                            $result->tx,
                            3, //Reward Distributed
                            ['days' => $days, 'apy' => (float)$invest->apy]
                        );

                        $invest->updated_at = now();
                        $invest->save();

                        Log::info('staking.reward_distribution.marked_paid', [
                            'staking_id' => $invest->id,
                            'updated_at' => $invest->updated_at->toIso8601String(),
                        ]);

                        $processed++;
                    } else {
                        Log::warning('staking.reward_distribution.skipped_or_failed', [
                            'staking_id' => $invest->id,
                            'user_id'    => $invest->user_id,
                            'reason'     => 'reward() returned null (see reward() logs for details)',
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('staking.reward_distribution.item_exception', [
                        'staking_id' => $invest->id,
                        'user_id'    => $invest->user_id,
                        'message'    => $e->getMessage(),
                        'file'       => $e->getFile(),
                        'line'       => $e->getLine(),
                    ]);
                }
            }

            Log::info('staking.reward_distribution.done', [
                'processed' => $processed
            ]);

            return response()->json(['processed' => $processed]);
        } catch (\Throwable $e) {
            Log::critical('staking.reward_distribution.fatal', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Reward distribution failed. Check logs.'
            ], 500);
        }
    }

    // Sending staking reward tokens to the wallet from staking reward wallet
    private function reward(Staking $invest, int $days = 1)
    {
        $apy = (float) $invest->apy;

        if ($apy <= 0 || $days <= 0) {
            Log::info('staking.reward.skip.non_positive_apy_or_days', [
                'staking_id' => $invest->id,
                'apy'        => $apy,
                'days'       => $days
            ]);
            return null;
        }

        $rewardAmount = round(
            (float)$invest->amount * ($apy / 100.0) * ($days / 365.0),
            7
        );

        if ($rewardAmount <= 0) {
            Log::info('staking.reward.skip.zero_amount', [
                'staking_id' => $invest->id,
                'apy'        => $apy,
                'amount'     => (float)$invest->amount,
                'days'       => $days,
                'reward'     => $rewardAmount,
            ]);
            return null;
        }

        try {
            $toPk = $invest->user?->public_key;
            if (!$toPk) {
                Log::warning('staking.reward.skip.missing_public_key', [
                    'staking_id' => $invest->id,
                    'user_id'    => $invest->user_id
                ]);
                return null;
            }

            // Build and submit transaction
            $rewardPair = KeyPair::fromSeed($this->stakingRewardWalletKey);
            $fromAcct   = $this->sdk->requestAccount($rewardPair->getAccountId());
            $toAcct     = $this->sdk->requestAccount($toPk);
            $asset      = new AssetTypeCreditAlphanum4($this->assetCode, $this->assetIssuer);

            $op = (new PaymentOperationBuilder($toAcct->getAccountId(), $asset, (string)$rewardAmount))->build();

            $tx = (new TransactionBuilder($fromAcct))
                ->setMaxOperationFee($this->maxFee)
                ->addOperation($op)
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'TKG stake reward'))
                ->build();

            // Don’t log XDR or secret
            $tx->sign($rewardPair, $this->network);
            $res = $this->sdk->submitTransaction($tx);

            Log::info('staking.reward.horizon_ok', [
                'staking_id' => $invest->id,
                'tx'         => $res->getId(),
                'reward'     => $rewardAmount
            ]);

            // If you want to capture XDRs for your audit record,
            // you can add these safely (they don't include seeds):
            $unsignedXdr = null;
            $signedXdr   = null;

            return (object) [
                'tx'         => $res->getId(),
                'amount'     => $rewardAmount,
                'unsignedXdr' => $unsignedXdr,
                'signedXdr'  => $signedXdr,
                'horizon'    => [
                    'result_xdr'  => $res->getExtras()?->getResultXdr(),
                    'result_codes' => $res->getExtras()?->getResultCodes(),
                ],
            ];
        } catch (HorizonRequestException $e) {
            $err = method_exists($e, 'getHorizonErrorResponse') ? $e->getHorizonErrorResponse() : null;
            Log::error('staking.reward.horizon_error', [
                'staking_id'   => $invest->id,
                'user_pk'      => $invest->user?->public_key,
                'amount'       => (string)$invest->amount,
                'reward'       => $rewardAmount,
                'status_code'  => $e->getStatusCode(),
                'horizon'      => $err,
                'message'      => $e->getMessage(),
            ]);
            return null;
        } catch (\Throwable $th) {
            Log::error('staking.reward.exception', [
                'staking_id' => $invest->id,
                'user_pk'    => $invest->user?->public_key,
                'days'       => $days,
                'apy'        => $apy,
                'amount'     => (string)$invest->amount,
                'reward'     => $rewardAmount,
                'message'    => $th->getMessage(),
                'file'       => $th->getFile(),
                'line'       => $th->getLine(),
            ]);
            return null;
        }
    }

    private function tkgTierAndApy(float $total): array
    {
        // Tier 3: 100,000+  => 18%
        if ($total >= 100000) return [3, 18.00];

        // Tier 2: 10,000–99,999 => 15%
        if ($total >= 10000)   return [2, 15.00];

        // Tier 1: 1,400–9,999  => 12%
        if ($total >= 1400)    return [1, 12.00];

        // below tier threshold
        return [0, 0.00];
    }

    public function user_staking(Request $request)
    {
        try {
            $data = $request->validate([
                'public_key'        => ['required', 'string', 'size:56', 'regex:/^G[A-Z0-9]{55}$/'],
                'staking_asset_id'  => ['nullable', 'integer', 'exists:staking_assets,id'],
                'include_withdrawn' => ['nullable', 'boolean'],
            ], [
                'public_key.regex'  => 'Invalid Stellar public key.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'error'  => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        }
        $assetId          = $data['staking_asset_id'] ?? null;

        $rows = Staking::query()
            ->select([
                'id',
                'staking_asset_id',
                'amount',
                'apy',
                'lock_days',
                'unlock_at',
                'created_at',
                'transaction_id',
                'staking_status_id',
            ])
            // what we need for display
            ->with([
                'asset:id,code',
                'status:id,name',
            ])
            // reward aggregates
            ->withSum('rewards as total_reward', 'amount')
            ->withCount('rewards as rewards_count')
            ->withMax('rewards as last_reward_at', 'created_at')

            ->ForPublicKey($data['public_key'])
            ->whereNotNull('transaction_id')
            ->when($assetId, fn($q) => $q->where('staking_asset_id', $assetId))
            ->minAmount($this->minAmount)
            ->latest()
            ->get();

        $positions = $rows->map(function (Staking $s) {
            return [
                'id'            => (int) $s->id,
                'asset_code'    => $s->asset?->code,
                'amount'        => (float) $s->amount,
                'apy'           => (float) $s->apy,

                // status
                'status'        => $s->status?->name,
                'status_id'     => (int) ($s->staking_status_id ?? 0),
                'transaction'     => $s->transaction_id,

                // rewards
                'total_reward'  => (float) ($s->total_reward ?? 0),   // sum of rewards.amount
                'rewards_count' => (int) ($s->rewards_count ?? 0),
                'last_reward_at' => $s->last_reward_at ? \Illuminate\Support\Carbon::parse($s->last_reward_at)->toIso8601String() : null,

                // lock / dates
                'lock_days'     => (int) $s->lock_days,
                'start_at'      => optional($s->created_at)->toIso8601String(),
                'unlock_at'     => optional($s->unlock_at)->toIso8601String(),
            ];
        });

        return response()->json([
            'status'    => 'success',
            'positions' => $positions,
        ]);
    }

    private function addStakingTransactionRecord($staking_id, $staking_reward_id, $unsigned_xdr, $signed_xdr, $transaction_id, $staking_status_id)
    {
        $transaction = new StakingTransaction();
        $transaction->staking_id = $staking_id;
        $transaction->staking_reward_id = $staking_reward_id;
        $transaction->unsigned_xdr = $unsigned_xdr;
        $transaction->signed_xdr = $signed_xdr;
        $transaction->transaction_id = $transaction_id;
        $transaction->staking_status_id = $staking_status_id;
        $transaction->save();

        return $transaction;
    }
}

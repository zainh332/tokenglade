<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Staking;
use App\Models\StakingResult;
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
use Soneso\StellarSDK\Signer;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Transaction;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Xdr\XdrDecoratedSignature;
use Soneso\StellarSDK\Xdr\XdrSigner;
use Illuminate\Support\Facades\Log;
use Soneso\StellarSDK\AbstractTransaction;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;

class StakingController extends Controller
{
    private $sdk, $minAmount, $maxFee, $daily_rate, $assetCode, $assetIssuer;
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
        $this->daily_rate = 0.1 / 100;
        $this->assetCode = 'TKG';
        $this->wallet = $wallet;
    }


    public function start_staking(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        $public = $request->input('public_key');

        $wallet = User::where('public_key', $public)->first();
        if (!$wallet) {
            return response()->json(['status' => 'error', 'message' => 'Wallet not found!']);
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

            $xdr = $transaction->toEnvelopeXdrBase64();

            // Operation failed
            if (!$xdr) {
                throw new \Exception('Something went wrong during staking operation.');
                Log::info('Something went wrong during staking operation.');
            }

            $existing_staking = Staking::where('public', $_COOKIE['public_key'])->where('is_withdrawn', false)->where('amount', '>=', $this->minAmount)->latest()->first();
            if ($existing_staking) {
                $existing_staking->amount += $request->amount;
                $existing_staking->save();
            } else {
                $new_stake = new Staking();
                $new_stake->public = $_COOKIE['public_key'];
                $new_stake->is_withdrawn = false;
                $new_stake->amount = $request->amount;
                $new_stake->save();
            }

            DB::commit(); // Commit the transaction
            return response()->json(['xdr' => $xdr, 'status' => 'success', 'staking_id' => $existing_staking ? $existing_staking->id : $new_stake->id]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction
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
            return response()->json(['status' => 'success', 'message' => 'Staking Succeffull'], 200);
        } catch (HorizonRequestException $e) {
            Log::info('Error while submitting Xdr.'. $e);
            return response()->json(['status' => 'error', 'message' => 'Failed!']);
        }
    }

    public function stop_staking($wallet_address = null)
    {
        if (!$wallet_address) {
            return response()->json(['status' => 'error', 'message' => 'Wallet address not provided!']);
        }

        $staking_wallet = Staking::where('public', $wallet_address)->first();
        if ($staking_wallet->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Wallet not found!']);
        }

        $active_staking_wallet = $staking_wallet->where('amount', '>=', $this->minAmount)
            ->where('is_withdrawn', false)
            ->whereNotNull('transaction_id');

        if ($active_staking_wallet->isEmpty()) {
            return response()->json(['status' => 'error', 'message' => 'Already stopped staking!']);
        }

        try {
            $mainSecret = env('STAKING_PUBLIC_WALLET_KEY');
            $mainPair = KeyPair::fromSeed($mainSecret);

            $mainAccount = $this->sdk->requestAccount($mainPair->getAccountId());
            $account = $this->sdk->requestAccount($wallet_address);

            $asset = new AssetTypeCreditAlphanum4($this->assetCode, $this->assetIssuer);

            // Payment Operation Returning TKG Tokens from Staking Public Wallet to User Wallet
            $paymentOperation = (new PaymentOperationBuilder($account->getAccountId(), $asset, $active_staking_wallet->amount))->build();
            $txbuilder = new TransactionBuilder($mainAccount);
            $txbuilder->setMaxOperationFee($this->maxFee);
            $transaction = $txbuilder->addOperation($paymentOperation)->addMemo(Memo::text('TKG Staking Stopped'))->build();
            $transaction->sign($mainPair, $this->network);
            $res = $this->sdk->submitTransaction($transaction);

            $active_staking_wallet->is_withdrawn = true;
            $active_staking_wallet->save();

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
        // remove incomplete staking created
        Staking::whereNull('transaction_id')->delete();

        $invests = Staking::whereNotNull('transaction_id')
            ->where('amount', '>=', 1000)
            ->where('is_withdrawn', 0)
            ->where('updated_at', '<=', now()->subHours(24))
            ->get();

        // Looping through invest
        foreach ($invests as $key => $invest) {
            $result = $this->reward($invest);
            if ($result) {
                StakingResult::create(['staking_id' => $invest->id, 'amount' => $result->amount, 'transaction_id' => $result->tx]);
            }
            $invest->updated_at = now();
            $invest->save();
        }
        return response()->json([$invests]);
    }

    // Sending staking reward tokens to the wallet from staking reward wallet
    private function reward($invest)
    {
        $amount = $this->daily_rate * $invest->amount;
        try {
            // Destination Account
            $mainPair = KeyPair::fromSeed($this->stakingRewardWalletKey);

            $mainAccount = $this->sdk->requestAccount($mainPair->getAccountId());
            $account = $this->sdk->requestAccount($invest->public);

            $asset = new AssetTypeCreditAlphanum4($this->assetCode, $this->assetIssuer);

            // Payment Operation
            $paymentOperation = (new PaymentOperationBuilder($account->getAccountId(), $asset, $amount))->build();
            $txbuilder = new TransactionBuilder($mainAccount);
            $txbuilder->setMaxOperationFee($this->maxFee);
            $transaction = $txbuilder->addOperation($paymentOperation)->addMemo(new Memo(1, 'TKG Stake Reward'))->build();
            $transaction->sign($mainPair, $this->network);
            $res = $this->sdk->submitTransaction($transaction);
            return (object)['tx' => $res->getId(), 'amount' => $amount];
        } catch (\Throwable $th) {
            Log::info('Error in reward method', [
                'invest_id' => $invest->id ?? null,
                'public' => $invest->public ?? null,
                'amount' => $amount,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);
        }
    }
}

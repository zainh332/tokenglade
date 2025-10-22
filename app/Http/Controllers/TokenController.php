<?php

namespace App\Http\Controllers;

use App\Models\StellarToken;
use App\Models\StellarTransactions;
use App\Models\Token;
use App\Services\WalletService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\AssetTypeCreditAlphanum4;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\ChangeTrustOperationBuilder;
use Soneso\StellarSDK\Transaction;
use Soneso\StellarSDK\AssetTypeCreditAlphanum12;
use Soneso\StellarSDK\AssetTypePoolShare;
use Soneso\StellarSDK\ChangeTrustOperation;
use Soneso\StellarSDK\CreateAccountOperationBuilder;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;
use Soneso\StellarSDK\LiquidityPoolDepositOperationBuilder;
use Soneso\StellarSDK\PathPaymentStrictReceiveOperationBuilder;
use Soneso\StellarSDK\Price;
use Soneso\StellarSDK\SetOptionsOperationBuilder;

class TokenController extends Controller
{
    private $sdk, $network, $token_creation_fee;
    private $feePercentageForLP, $xlm_funding_wallet, $xlm_funding_wallet_key, $issuer_wallet_amount, $stakingPublicWallet, $stakingPublicWalletKey, $tkgIssuer, $assetCode;
    private WalletService $wallet;
    private bool $isTestnet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT');
        $this->isTestnet = strtolower($stellarEnv) !== 'public';

        if ($stellarEnv === 'public') {
            $this->sdk = StellarSDK::getPublicNetInstance();
            $this->xlm_funding_wallet = env('XLM_FUNDING_WALLET');
            $this->xlm_funding_wallet_key = env('XLM_FUNDING_WALLET_KEY');
            $this->stakingPublicWallet = env('STAKING_PUBLIC_WALLET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY');
            $this->tkgIssuer = env('TKG_ISSUER_PUBLIC');
            $this->network = Network::public();
        } else {
            $this->sdk = StellarSDK::getTestNetInstance();
            $this->network = Network::testnet();
            $this->xlm_funding_wallet = env('XLM_FUNDING_WALLET_TESTNET');
            $this->xlm_funding_wallet_key = env('XLM_FUNDING_WALLET_KEY_TESTNET');
            $this->stakingPublicWallet = env('STAKING_PUBLIC_WALLET_TESTNET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY_TESTNET');
            $this->tkgIssuer = env('TKG_ISSUER_TESTNET');
        }

        $this->assetCode = env('ASSET_CODE');
        $this->token_creation_fee = 40; //XLM
        $this->issuer_wallet_amount = 4; //XLM
        $this->feePercentageForLP = 0.7;
    }

    public function generate_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributor_wallet_key' => 'required|string',
            'asset_code' => 'required|string|max:12',
            'total_supply' => 'required|integer|min:1',
            'name' => 'required|string|max:255',
            'desc' => 'required|string',
            'website_url'            => 'nullable|url|max:255',
            'logo' => 'required|file|mimes:png,jpg,jpeg|max:5120',
            'lock_status'            => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $distributor_wallet_key = $request->input('distributor_wallet_key');
        $name = $request->input('name');
        $desc = $request->input('desc');
        $website_url = $request->input('website_url');
        $asset_code = $request->input('asset_code');
        $total_supply = $request->input('total_supply');
        $lock_status = $request->input('lock_status');
        $distributor_wallet_xlm_balance = $this->wallet->getXlmBalance($distributor_wallet_key);

        // if ($distributor_wallet_xlm_balance < ($this->token_creation_fee + 5)) {
        if ($distributor_wallet_xlm_balance < $this->token_creation_fee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance. You need at least ' . $this->token_creation_fee . ' XLM available in your wallet to proceed.',
            ]);
        }


        //charge token creation fee
        $token_creation_charges = $this->tokenCreationXLMFeeTransaction($distributor_wallet_key, $asset_code, $total_supply, $lock_status);
        if (!$token_creation_charges) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction failed. Something went wrong',
            ], 500);
        }

        $logoUrl = null;
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $logoUrl = asset('storage/' . $path);
        }

        $token_creation = new StellarToken();
        $token_creation->name = $name;
        $token_creation->desc = $desc;
        $token_creation->website_url = $website_url;
        $token_creation->logo = $logoUrl;
        $token_creation->asset_code = $asset_code;
        $token_creation->total_supply = $total_supply;
        $token_creation->user_wallet_address = $distributor_wallet_key;
        $token_creation->lock_status = $lock_status;
        $token_creation->save();

        $token = new Token();
        $token->stellar_token_id = $token_creation->id;
        $token->blockchain_id = 1; //stellar
        $token->save();

        $addStellarTransactionRecord = $this->addStellarTransactionRecord($token_creation->id, $distributor_wallet_key, 1, $token_creation_charges['unsigned_token_creation_fee_transaction'], '', '', false);
        $token_creation->current_stellar_transaction_id = $addStellarTransactionRecord->id;
        $token_creation->save();

        return response()->json([
            'status' => 'success',
            'unsigned_token_creation_fee_transaction' => $token_creation_charges['unsigned_token_creation_fee_transaction'],
        ], 200);
    }

    private function tokenCreationXLMFeeTransaction($distributor_wallet_key)
    {
        try {
            // Load distributor account from Stellar
            $distributorAccount = $this->sdk->requestAccount($distributor_wallet_key);

            // Define the payment operation (from distributor to issuer)
            $paymentOp = (new PaymentOperationBuilder(
                $this->xlm_funding_wallet,
                Asset::native(),                  // XLM
                strval($this->token_creation_fee) // amount: fee (200)
            ))
                ->setSourceAccount($distributor_wallet_key)
                ->build();

            // Build the transaction
            $transaction = (new TransactionBuilder($distributorAccount, 'public'))
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Create token fee'))
                ->addOperation($paymentOp)
                ->build();

            // Return unsigned transaction (XDR) to frontend
            return [
                'status' => 'success',
                'unsigned_token_creation_fee_transaction' => $transaction->toEnvelopeXdrBase64()
            ];
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Could not create token creation fee transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function submit_transaction(Request $request)
    {
        $raw = $request->input('signedXdr');
        $payload = $request->payload;

        $distributor_wallet_key = $payload['distributor_wallet_key'];
        $type = $request->type;
        $assetCode = $payload['asset_code'];

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

        $transactionEnvelope = Transaction::fromEnvelopeBase64XdrString($signedXdr);
        // Submit the transaction to the Stellar network using the SDK
        $response = $this->sdk->submitTransaction($transactionEnvelope);

        // Check if the transaction was successful
        if ($response && $response->isSuccessful()) {
            try {
                if ($type == 1) //tokenCreationFeeTransaction
                {
                    $token_created = StellarToken::where('user_wallet_address', $distributor_wallet_key)
                        ->where('asset_code', $assetCode)
                        ->where('issuer_wallet_status', 0)
                        ->where('created_token_transfer_status', 0)
                        ->whereNotNull('current_stellar_transaction_id')
                        ->latest()->first();

                    if (!$token_created) {
                        return response()->json([
                            'success' => false,
                            'error' => 'Token creation record not found for this wallet and asset code.',
                        ], 404);
                    }

                    $generate_issuer_wallet_transaction = $this->addStellarTransactionRecord($token_created->id, $distributor_wallet_key, 1, '', $signedXdr, $response->getHash(), true);
                    if (!$generate_issuer_wallet_transaction) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Creating stellar transaction failed while token creation fee.'
                        ], 500);
                    }

                    // Update the token creation record with the new transaction ID
                    $token_created->current_stellar_transaction_id = $generate_issuer_wallet_transaction->id;
                    $token_created->save();

                    $current_transaction_id = $this->generateIssuerWallet($distributor_wallet_key, $token_created->current_stellar_transaction_id);

                    if (!$current_transaction_id) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Issuer wallet generation failed.'
                        ], 500);
                    }

                    $issuer_wallet_distributor_wallet_trustline_transaction = $this->issuer_wallet_distributor_wallet_trustline_transaction($distributor_wallet_key, $current_transaction_id);
                    if (!$issuer_wallet_distributor_wallet_trustline_transaction) {
                        return response()->json([
                            'status' => 'error',
                            'message' => $trustlineResult['message'] ?? 'Trustline Transaction failed',
                        ], 500);
                    }
                    return response()->json([
                        'status' => 'success',
                        'unsigned_trustline_transaction' => $issuer_wallet_distributor_wallet_trustline_transaction['unsigned_trustline_transaction'],
                    ], 200);
                } else if ($type == 3) //Issuer Wallet Distributor Wallet Trustline transaction
                {
                    $token_created = StellarToken::where('user_wallet_address', $distributor_wallet_key)
                        ->where('asset_code', $assetCode)
                        ->whereNotNull('current_stellar_transaction_id')
                        ->whereNotNull('issuer_public_key')
                        ->whereNotNull('issuer_secret_key')
                        ->where('issuer_wallet_status', 1)
                        ->where('created_token_transfer_status', 0)
                        ->latest()
                        ->first();

                    if (!$token_created) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Token creation record not found for this wallet and asset code.',
                        ], 404);
                    }

                    $Issuer_wallet_distributor_wallet_trustline_transaction = $this->addStellarTransactionRecord($token_created->id, $distributor_wallet_key, 3, '', $signedXdr, $response->getHash(), true);

                    if (!$Issuer_wallet_distributor_wallet_trustline_transaction) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Creating stellar transaction failed.'
                        ], 500);
                    }

                    // Update the token creation record with the new transaction ID
                    $token_created->current_stellar_transaction_id = $Issuer_wallet_distributor_wallet_trustline_transaction->id;
                    $token_created->save();

                    $transfer_generate_token = $this->transfer_created_tokens($distributor_wallet_key, $assetCode, $token_created->issuer_public_key, $token_created->issuer_secret_key, $token_created->total_supply);
                    if (!$transfer_generate_token) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Sending generated token to user Failed.'
                        ], 500);
                    }

                    $created_tokens_transfer_transaction = $this->addStellarTransactionRecord($token_created->id, $distributor_wallet_key, 4, '', $transfer_generate_token['signed_xdr'], $transfer_generate_token['tx_hash'], true);

                    if (!$created_tokens_transfer_transaction) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Creating stellar transaction failed while tokens transfer.'
                        ], 500);
                    }

                    // Update the token creation record with the new transaction ID
                    $token_created->current_stellar_transaction_id = $created_tokens_transfer_transaction->id;
                    $token_created->created_token_transfer_status = 1;
                    $token_created->save();

                    $homeDomainTx = $this->setIssuerHomeDomain(
                        $token_created->issuer_public_key,
                        $token_created->issuer_secret_key,
                        'tokenglade.com'
                    );

                    if (!$homeDomainTx['ok']) {
                        Log::warning('Failed to set home_domain for issuer', [
                            'issuer' => $token_created->issuer_public_key,
                            'error'  => $homeDomainTx['error'] ?? 'unknown'
                        ]);
                    } else {
                        Log::info('home_domain set for issuer', [
                            'issuer'   => $token_created->issuer_public_key,
                            'tx_hash'  => $homeDomainTx['tx_hash'],
                        ]);

                        // (Optional) persist this tx in your own transactions table
                        // try {
                        //     $this->addStellarTransactionRecord(
                        //         $token_created->id,
                        //         $token_created->issuer_public_key,
                        //         7, // <-- pick an internal "type" code for "Set home_domain"
                        //         '',
                        //         $homeDomainTx['signed_xdr'],
                        //         $homeDomainTx['tx_hash'],
                        //         true
                        //     );
                        // } catch (\Throwable $t) {
                        //     Log::warning('Could not save home_domain tx record', ['e' => $t->getMessage()]);
                        // }
                    }

                    $directory = public_path('.well-known');
                    if (!is_dir($directory)) {
                        mkdir($directory, 0755, true);
                    }

                    $tomlPath = $directory . '/stellar.toml';

                    $tomlContent = <<<EOT
                    [[CURRENCIES]]
                    code="{$token_created->asset_code}"
                    issuer="{$token_created->issuer_public_key}"
                    display_decimals={$token_created->display_decimals}
                    name="{$token_created->name}"
                    desc="{$token_created->desc}"
                    image="{$token_created->logo}"
                    fixed_number="{$token_created->total_supply}"
                    status="live"

                    EOT;

                    if (file_exists($tomlPath)) {
                        file_put_contents($tomlPath, "\n" . $tomlContent, FILE_APPEND);
                    } else {
                        file_put_contents($tomlPath, $tomlContent);
                    }

                    try {
                        $liquidityDepositTransaction = $this->tokenCreationLiquidityDepositTransaction();
                        if ($liquidityDepositTransaction['status'] !== 'success') {
                            Log::warning('Liquidity deposit failed', [
                                'message' => $liquidityDepositTransaction['message'],
                                'error'   => $liquidityDepositTransaction['error'] ?? 'Unknown error',
                            ]);
                        }
                    } catch (\Throwable $t) {
                        Log::error('Liquidity deposit exception', [
                            'exception' => $t->getMessage(),
                            'trace'     => $t->getTraceAsString(),
                        ]);
                    }

                    if ((int)$token_created->lock_status === 1) {
                        $lockOk = $this->lockIssuerWallet(
                            $token_created->issuer_public_key,
                            $token_created->issuer_secret_key
                        );

                        if (!$lockOk) {
                            return response()->json([
                                'status'  => 'error',
                                'message' => 'Issuer wallet lock failed after transfer.',
                            ], 500);
                        }
                    }

                    return response()->json([
                        'status' => 'success',
                        'assetCode' => $assetCode,
                        'issuerPublicKey' => $token_created->issuer_public_key,
                        'issuerSecretKey' => $token_created->issuer_secret_key
                    ], 200);
                } else {
                    return response()->json([
                        'success' => 'error',
                        'message' => 'Transaction type not found',
                    ], 404);
                }
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed',
                'result_codes' => $response?->getExtras()?->getResultCodes() ?? 'Unknown error',
                'details' => $response?->getExtras()?->getResultXdr() ?? 'No details available'
            ], 400);
        }
    }

    private function generateIssuerWallet($distributor_wallet_key, $current_stellar_transaction_id)
    {
        try {
            $xlm_funding_wallet_key = KeyPair::fromSeed($this->xlm_funding_wallet_key);

            $issuerKeyPair = KeyPair::random();
            $issuerPublicKey = $issuerKeyPair->getAccountId();
            $issuerSecretkey = $issuerKeyPair->getSecretSeed();

            $fundingAccount = $this->sdk->requestAccount($this->xlm_funding_wallet);

            // Create & Fund the Issuer Wallet from Funding wallet
            $createAccountOp = (new CreateAccountOperationBuilder($issuerPublicKey, strval($this->issuer_wallet_amount)))->build();

            // Build & Sign the Transaction
            $transaction = (new TransactionBuilder($fundingAccount, $this->network))
                ->addOperation($createAccountOp)
                ->build();

            $transaction->sign($xlm_funding_wallet_key, $this->network);

            // Submit the transaction to the Stellar network
            $response = $this->sdk->submitTransaction($transaction);

            if ($response && $response->isSuccessful()) {
                $token_created = StellarToken::where('user_wallet_address', $distributor_wallet_key)
                    ->where('current_stellar_transaction_id', $current_stellar_transaction_id)
                    ->whereNull('issuer_public_key')
                    ->whereNull('issuer_secret_key')
                    ->where('issuer_wallet_status', 0)
                    ->where('created_token_transfer_status', 0)
                    ->latest()
                    ->first();

                if (!$token_created) {
                    return false;
                }

                $generate_issuer_wallet_transaction = $this->addStellarTransactionRecord($token_created->id, $distributor_wallet_key, 3, '', $transaction->toEnvelopeXdrBase64(), $response->getHash(), true);

                if (!$generate_issuer_wallet_transaction) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Creating stellar transaction failed while generating issuer wallet.'
                    ], 500);
                }

                $token_created->current_stellar_transaction_id = $generate_issuer_wallet_transaction->id;
                $token_created->issuer_public_key = $issuerPublicKey;
                $token_created->issuer_secret_key = $issuerSecretkey;
                $token_created->issuer_wallet_status = 1;
                $token_created->save();

                return $token_created->current_stellar_transaction_id;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Issuer wallet generation failed: ' . $e->getMessage());
            return false;
        }
    }

    private function issuer_wallet_distributor_wallet_trustline_transaction($distributor_wallet_key, $current_stellar_transaction_id)
    {
        try {
            // Load the distributor account
            $distributorAccount = $this->sdk->requestAccount($distributor_wallet_key);

            $token_created = StellarToken::where('user_wallet_address', $distributor_wallet_key)
                ->where('current_stellar_transaction_id', $current_stellar_transaction_id)
                ->where('issuer_wallet_status', 1)
                ->where('created_token_transfer_status', 0)
                ->latest()
                ->first();

            if (!$token_created) {
                return [
                    'status' => 'error',
                    'message' => 'Token not found or issuer wallet not active',
                ];
            }

            $asset_code = $token_created->asset_code;
            $issuerPublicKey = $token_created->issuer_public_key;


            $asset = (strlen($asset_code) <= 4)
                ? new AssetTypeCreditAlphaNum4($asset_code, $issuerPublicKey)
                : new AssetTypeCreditAlphanum12($asset_code, $issuerPublicKey);

            $trustlineOperation = (new ChangeTrustOperationBuilder($asset))->build();

            $trustlineTransaction = (new TransactionBuilder($distributorAccount, $this->network))
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Trustline to new issuer'))
                ->addOperation($trustlineOperation)
                ->build();

            return [
                'status' => 'success',
                'unsigned_trustline_transaction' => $trustlineTransaction->toEnvelopeXdrBase64(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'An error occurred while processing the transaction: ' . $e->getMessage(),
            ];
        }
    }

    private function transfer_created_tokens($distributor_wallet_key, $asset_code, $issuerPublicKey, $issuerSecretKey, $total_supply)
    {
        $issuerAccount = $this->sdk->requestAccount($issuerPublicKey);

        if (strlen($asset_code) <= 4) {
            $asset = new AssetTypeCreditAlphaNum4($asset_code, $issuerPublicKey);
        } else {
            $asset = new AssetTypeCreditAlphanum12($asset_code, $issuerPublicKey);
        }

        // Send the total supply from issuer to distributor
        $paymentOperation = (new PaymentOperationBuilder($distributor_wallet_key, $asset, $total_supply))->build();

        // Build the payment transaction
        $paymentTransaction = (new TransactionBuilder($issuerAccount))
            ->addOperation($paymentOperation)
            ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Tokens minted on TokenGlade'))
            ->build();

        $issuerKeypair = KeyPair::fromSeed($issuerSecretKey);

        // Sign the payment transaction
        $paymentTransaction->sign($issuerKeypair, $this->network);

        // Submit the payment transaction
        $response = $this->sdk->submitTransaction($paymentTransaction);

        if ($response && $response->isSuccessful()) {
            return [
                'signed_xdr' => $paymentTransaction->toEnvelopeXdrBase64(),
                'tx_hash' => $response->getHash(),
            ];
        }
        return false;
    }

    private function addStellarTransactionRecord($stellar_token_id, $wallet, $type_id, $unsigned_xdr, $signed_xdr, $tx_hash, $status)
    {
        $transaction = new StellarTransactions();
        $transaction->stellar_token_id = $stellar_token_id;
        $transaction->user_wallet_address = $wallet;
        $transaction->transaction_type_id = $type_id;
        $transaction->unsigned_xdr = $unsigned_xdr;
        $transaction->signed_xdr = $signed_xdr;
        $transaction->tx_hash = $tx_hash;
        $transaction->status = $status;
        $transaction->save();

        return $transaction;
    }

    private function tokenCreationLiquidityDepositTransaction()
    {
        try {
            $xlmFundingWalletPublicKey = $this->xlm_funding_wallet;
            $xlmFundingAccount = $this->sdk->requestAccount($xlmFundingWalletPublicKey);

            $nativeBal = '0';
            $tkgBal    = '0';
            foreach ($xlmFundingAccount->getBalances() as $bal) {
                $t = $bal->getAssetType();
                if ($t === 'native') $nativeBal = $bal->getBalance();
                if ($t === 'credit_alphanum4' || $t === 'credit_alphanum12') {
                    if ($bal->getAssetCode() === $this->assetCode && $bal->getAssetIssuer() === $this->tkgIssuer) {
                        $tkgBal = $bal->getBalance();
                    }
                }
            }

            // $poolId = $this->getPoolIdFromHorizon($this->assetCode, $this->tkgIssuer, $this->isTestnet);
            $poolId = 'cb1922681c9d2380d34577d3c056e435a8436586e776c38a80412120c2442fb5';
            if (!$poolId) throw new \RuntimeException('Liquidity pool not found yet on Horizon.');

            $reserves = $this->getPoolReserves($poolId);
            Log::info('[LP] Pool reserves read', ['reserves' => $reserves]);

            if (!$reserves || !isset($reserves['xlm'], $reserves['tkg'])) {
                throw new \RuntimeException('Could not read pool reserves.');
            }

            // pool holdings (strings like "123.4567890")
            $poolXlm = $reserves['xlm']; // XLM amount in pool
            $poolTkg = $reserves['tkg']; // TKG amount in pool

            // Decide how much XLM you want to add (70% of fee, as before)
            // $xlmLiquidityAmount = $this->scale7($this->token_creation_fee * $this->feePercentageForLP);
            $xlmBudget = $this->scale7($this->token_creation_fee * $this->feePercentageForLP);

            $tkgPerXlm = $this->bcdiv($poolTkg, $poolXlm, 12);

            // --- decide swap split (start with 50/50)
            $xlmForSwap = $this->scale7($this->bcdiv($xlmBudget, '2', 7));

            Log::info('XLM Swap', [
                'xlmForSwap'         => $xlmForSwap,
                'xlmBudget' => $xlmBudget,
            ]);


            $xlmForDeposit = $this->scale7($this->bcsub($xlmBudget, $xlmForSwap, 7)); 

            // Target TKG to buy so deposit matches pool ratio (approx)
            $tkgTarget = $this->scale7($this->bcmul($xlmForDeposit, $tkgPerXlm, 12));

            Log::info('XLM Swap', [
                'xlmForDeposit'         => $xlmForDeposit,
                'tkgTarget' => $tkgTarget,
                'nativeBal' => $nativeBal,
            ]);

            // Ensure funding wallet has enough XLM for the whole budget (not budget + swap)
            if ((float)$nativeBal < (float)$xlmBudget) {
                throw new \RuntimeException('Underfunded: not enough XLM to cover budgeted LP add.');
            }

            $tkgAsset = Asset::createNonNativeAsset($this->assetCode, $this->tkgIssuer);

            $txb = (new TransactionBuilder($xlmFundingAccount, $this->network))
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'LP trustlines + deposit'));

            // Trust TKG (ok to always include)
            $txb->addOperation((new ChangeTrustOperationBuilder($tkgAsset, '922337203685.4775807'))->build());

            // Trust LP shares (use AssetTypePoolShare; ALWAYS include)
            $txb->addOperation($this->buildLpShareChangeTrustOpForSdk());

            $needTkg = max(0.0, (float)$tkgTarget - (float)$tkgBal);

            if ($needTkg > 0) {
                $xlmForSwap = $this->xlmNeededForTkg($poolXlm, $poolTkg, number_format($needTkg, 7, '.', ''), 30);
                if ($xlmForSwap === null) {
                    throw new \RuntimeException('Target TKG exceeds pool capacity.');
                }

                // ensure you have enough XLM: swap + deposit + some fee headroom
                $xlmNeededTotal = (float)$xlmForSwap + (float)$xlmBudget;
                if ((float)$nativeBal < $xlmNeededTotal) {
                    throw new \RuntimeException('Underfunded XLM for swap + deposit.');
                }
                
                $xlmForSwapStr         = number_format((float)$xlmForSwap, 7, '.', '');
                $tkgLiquidityAmountStr = number_format((float)$tkgTarget, 7, '.', '');
                Log::info('XLM Swap', [
                    'xlmNeededTotal'     => $xlmNeededTotal,
                    'xlmForSwap'         => $xlmForSwap,
                    'xlmBudget' => $xlmBudget,
                    'xlmForSwapStr' => $xlmForSwapStr,
                    'tkgLiquidityAmountStr' => $tkgLiquidityAmountStr,
                ]);

                // Path payment strict receive: send XLM, receive exact TKG to self
                $pathOp = (new PathPaymentStrictReceiveOperationBuilder(
                    Asset::native(),
                    $xlmForSwapStr,
                    $xlmFundingWalletPublicKey,
                    $tkgAsset,
                    $tkgLiquidityAmountStr
                ))->build();

                $txb->addOperation($pathOp);
            }

            $minPrice = new Price(1, 100000000);
            $maxPrice = new Price(100000000, 1);

            // Deposit
            $txb->addOperation(
                (new LiquidityPoolDepositOperationBuilder(
                    $poolId,
                    $xlmBudget,
                    $tkgLiquidityAmountStr,
                    $minPrice,
                    $maxPrice
                ))->build()
            );

            $tx = $txb->build();
            $kp = KeyPair::fromSeed($this->xlm_funding_wallet_key);
            $tx->sign($kp, $this->network);
            $response = $this->sdk->submitTransaction($tx);

            if ($response->isSuccessful()) {
                return [
                    'status' => 'success',
                    'message' => 'Liquidity Pool Deposit transaction successfully submitted.',
                    'transaction_hash' => $response->getHash()
                ];
            } else {
                $extras = $response->getExtras();
                $codes  = $extras ? $extras->getResultCodes() : null;
                $envXdr = $extras ? $extras->getEnvelopeXdr() : null;
                $resXdr = $extras ? $extras->getResultXdr() : null;

                Log::error('Liquidity Pool Deposit failed', [
                    'result_codes' => $codes,
                    'envelope_xdr' => $envXdr,
                    'result_xdr'   => $resXdr,
                ]);
                return [
                    'status' => 'error',
                    'message' => 'Liquidity Pool Deposit submission failed.',
                    'error' => $codes,
                    'debug'   => [
                        'envelope_xdr' => $envXdr,
                        'result_xdr'   => $resXdr,
                    ],
                ];
            }
        } catch (HorizonRequestException $hex) {
            $prev = $hex->getPrevious();
            $body = null;
            if ($prev instanceof ClientException && $prev->getResponse()) {
                $body = (string)$prev->getResponse()->getBody();
            }
            Log::error('HorizonRequestException on submitTransaction', [
                'horizon_message' => $hex->getMessage(),
                'horizon_body'    => $body,
            ]);
            return [
                'status'  => 'error',
                'message' => 'Horizon rejected transaction',
                'error'   => $body ?: $hex->getMessage(),
            ];
        } catch (\Throwable $e) {
            Log::critical('Unexpected exception submitting tx', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return [
                'status'  => 'error',
                'message' => 'Unexpected exception',
                'error'   => $e->getMessage(),
            ];
        }
    }

    private function buildLpShareChangeTrustOpForSdk(): ChangeTrustOperation
    {
        $xlm = Asset::native();
        $tkg = Asset::createNonNativeAsset($this->assetCode, $this->tkgIssuer);

        $a = $xlm;
        $b = $tkg;
        $rank = function (Asset $as): int {
            return $as->getType() === Asset::TYPE_NATIVE ? 0
                : ($as->getType() === Asset::TYPE_CREDIT_ALPHANUM_4 ? 1
                    : ($as->getType() === Asset::TYPE_CREDIT_ALPHANUM_12 ? 2 : 3));
        };
        $swap = false;
        if ($rank($a) > $rank($b)) $swap = true;
        elseif (
            $rank($a) === $rank($b)
            && $a instanceof \Soneso\StellarSDK\AssetTypeCreditAlphanum
            && $b instanceof \Soneso\StellarSDK\AssetTypeCreditAlphanum
        ) {
            $codeCmp = strcmp($a->getCode(), $b->getCode());
            if ($codeCmp > 0 || ($codeCmp === 0 && strcmp($a->getIssuer(), $b->getIssuer()) > 0)) $swap = true;
        }
        if ($swap) {
            [$a, $b] = [$b, $a];
        }

        $poolShareAsset = new AssetTypePoolShare($a, $b);

        return new ChangeTrustOperation($poolShareAsset, '922337203685.4775807');
    }

    private function getPoolIdFromHorizon(string $codeB, string $issuerB, bool $testnet = false): ?string
    {
        $base = $testnet
            ? 'https://horizon-testnet.stellar.org'
            : 'https://horizon.stellar.org';

        // Try repeated-reserves style first
        $url1 = $base
            . '/liquidity_pools'
            . '?reserves=native'
            . '&reserves=' . rawurlencode($codeB . ':' . $issuerB)
            . '&limit=1&order=asc';

        // Also prepare a fallback comma-separated style
        $url2 = $base
            . '/liquidity_pools'
            . '?reserves=' . rawurlencode('native,' . $codeB . ':' . $issuerB)
            . '&limit=1&order=asc';

        $client = new Client(['timeout' => 10]);

        foreach ([$url1, $url2] as $url) {
            try {
                $res = $client->get($url);
                $status = $res->getStatusCode();
                if ($status !== 200) {
                    // not successful, skip this attempt
                    continue;
                }
                $body = (string)$res->getBody();
                $json = json_decode($body, true);
                if (!is_array($json) || !isset($json['_embedded']['records'])) {
                    continue;
                }
                $records = $json['_embedded']['records'];
                if (count($records) === 0) {
                    continue;
                }
                $rec = $records[0];
                if (isset($rec['id'])) {
                    return $rec['id'];
                }
            } catch (RequestException $ex) {
                // optionally log $ex->getMessage()
                continue;
            } catch (\Exception $ex) {
                // other errors
                continue;
            }
        }

        // If none worked or pool not found
        return null;
    }

    private function lockIssuerWallet(string $issuerPub, string $issuerSec): bool
    {
        try {
            $issuerAccount = $this->sdk->requestAccount($issuerPub);

            $setOptions = (new SetOptionsOperationBuilder())
                ->setMasterKeyWeight(0)
                ->setSourceAccount($issuerPub)
                ->build();

            $tx = (new TransactionBuilder($issuerAccount, $this->network))
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Lock issuer'))
                ->addOperation($setOptions)
                ->build();

            $issuerSecretKey = KeyPair::fromSeed($issuerSec);

            $tx->sign($issuerSecretKey, $this->network);

            $resp = $this->sdk->submitTransaction($tx);
            return $resp->isSuccessful();
        } catch (\Throwable $e) {
            Log::error('lockIssuerWallet failed', ['msg' => $e->getMessage()]);
            return false;
        }
    }

    private function getPoolReserves(string $poolId): ?array
    {
        $base = $this->isTestnet
            ? 'https://horizon-testnet.stellar.org'
            : 'https://horizon.stellar.org';

        $url = $base . '/liquidity_pools/' . $poolId;

        Log::info('[LP:getPoolReserves] Fetching pool', [
            'env'    => $this->isTestnet ? 'testnet' : 'public',
            'poolId' => $poolId,
            'url'    => $url,
            'asset'  => $this->assetCode,
            'issuer' => $this->tkgIssuer,
        ]);

        try {
            $res = Http::timeout(10)->acceptJson()->get($url);

            if ($res->failed()) {
                Log::warning('[LP:getPoolReserves] Horizon request failed', [
                    'status' => $res->status(),
                    'body'   => mb_substr($res->body(), 0, 800),
                ]);
                return null;
            }

            $data = $res->json();

            $rawReserves = $data['reserves'] ?? null;
            Log::info('[LP:getPoolReserves] Reserves field', [
                'hasReservesArray' => is_array($rawReserves),
                'count'            => is_array($rawReserves) ? count($rawReserves) : 0,
                'sample'           => is_array($rawReserves) ? array_slice($rawReserves, 0, 2) : $rawReserves,
            ]);

            if (!is_array($rawReserves)) {
                Log::warning('[LP:getPoolReserves] reserves missing or not an array');
                return null;
            }

            $xlm = null;
            $tkg = null;

            foreach ($rawReserves as $r) {
                $asset  = $r['asset']  ?? null;
                $amount = $r['amount'] ?? null;

                if ($asset === 'native') {
                    $xlm = $amount;
                    continue;
                }

                if (!is_string($asset)) {
                    continue;
                }

                $parts = explode(':', $asset);

                if (count($parts) === 2) {
                    [$code, $issuer] = $parts;
                } elseif (count($parts) === 3) {
                    [, $code, $issuer] = $parts;
                } else {
                    continue;
                }

                if ($code === $this->assetCode && $issuer === $this->tkgIssuer) {
                    $tkg = $amount;
                }
            }

            if ($xlm === null || $tkg === null) {
                Log::warning('[LP:getPoolReserves] Could not match both XLM and TKG in reserves', [
                    'asset'  => $this->assetCode,
                    'issuer' => $this->tkgIssuer,
                    'raw'    => $rawReserves,
                ]);
                return null;
            }

            return ['xlm' => $xlm, 'tkg' => $tkg];
        } catch (\Throwable $e) {
            Log::error('[LP:getPoolReserves] Exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /** bcmath helpers (safe if extension present; fallback to native). */
    private function bcdiv($left, $right, $scale = 12)
    {
        if (extension_loaded('bcmath')) return bcdiv((string)$left, (string)$right, $scale);
        return (string) ((float)$left / (float)$right);
    }

    private function bcmul($left, $right, $scale = 12)
    {
        if (extension_loaded('bcmath')) return bcmul((string)$left, (string)$right, $scale);
        return (string) ((float)$left * (float)$right);
    }

    /** format to 7dp (round down) */
    private function scale7($val): string
    {
        $f = floor(((float)$val) * 1e7) / 1e7;
        return number_format($f, 7, '.', '');
    }

    private function bcsub(string $left, string $right, int $scale = 7): string
    {
        return bcsub($left, $right, $scale);
    }

    private function xlmNeededForTkg(string $poolXlm, string $poolTkg, string $targetTkg, int $feeBp = 30): string
    {
        $X = (float)$poolXlm;
        $Y = (float)$poolTkg;
        $dy = (float)$targetTkg;

        // guard: cannot withdraw more than reserve
        if ($dy >= $Y) return null;

        $fee = 1.0 - ($feeBp / 10000.0); // e.g. 0.997
        // strict-receive inverse: dx_eff = (dy * X) / (Y - dy)
        $dxEff = ($dy * $X) / ($Y - $dy);
        $dx = $dxEff / $fee; // undo fee on input
        // 7dp, round up a hair to be safe
        $dx += 1e-7;
        return number_format($dx, 7, '.', '.');
    }

    private function setIssuerHomeDomain(string $issuerPublic, string $issuerSecret, string $domain = 'tokenglade.com'): array
    {
        try {
            // Load current account state
            $account = $this->sdk->accounts()->account($issuerPublic);

            // Build SetOptions(home_domain) op
            $setHomeDomainOp = (new SetOptionsOperationBuilder())
                ->setHomeDomain($domain)
                ->build();

            // Build & sign tx
            $tx = (new TransactionBuilder($account))
                ->addOperation($setHomeDomainOp)
                ->build();

            $kp = KeyPair::fromSeed($issuerSecret);
            // Many versions require network on sign; you already keep it in $this->network
            $tx->sign($kp, $this->network);

            // Submit
            $res = $this->sdk->submitTransaction($tx);

            if ($res->isSuccessful()) {
                return [
                    'ok'        => true,
                    'tx_hash'   => $res->getHash(),
                    'signed_xdr' => $tx->toEnvelopeXdrBase64(),
                ];
            }

            return [
                'ok'    => false,
                'error' => [
                    'result_codes' => $res?->getExtras()?->getResultCodes(),
                    'result_xdr'   => $res?->getExtras()?->getResultXdr(),
                ],
            ];
        } catch (\Throwable $t) {
            return [
                'ok'    => false,
                'error' => $t->getMessage(),
            ];
        }
    }
}

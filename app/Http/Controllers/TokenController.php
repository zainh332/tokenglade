<?php

namespace App\Http\Controllers;

use App\Models\ClaimClaimableBalance;
use App\Models\ClaimClaimableBalanceId;
use App\Models\ClaimClaimableClaimant;
use App\Models\StellarToken;
use App\Models\StellarTransactions;
use App\Models\Token;
use App\Services\WalletService;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use phpseclib3\Math\BigInteger;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\AssetTypeCreditAlphanum4;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\CreateClaimableBalanceOperation;
use Soneso\StellarSDK\Claimant;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\ChangeTrustOperationBuilder;
use Soneso\StellarSDK\Transaction;
use Soneso\StellarSDK\AssetTypeCreditAlphanum12;
use Soneso\StellarSDK\AssetTypePoolShare;
use Soneso\StellarSDK\ChangeTrustOperation;
use Soneso\StellarSDK\ClaimClaimableBalanceOperation;
use Soneso\StellarSDK\CreateAccountOperationBuilder;
use Soneso\StellarSDK\Crypto\StrKey;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;
use Soneso\StellarSDK\LiquidityPoolDepositOperationBuilder;
use Soneso\StellarSDK\Price;

// XDR pieces for LP-share trustline:
use Soneso\StellarSDK\Xdr\XdrChangeTrustAsset;
use Soneso\StellarSDK\Xdr\XdrOperation;
use Soneso\StellarSDK\Xdr\XdrOperationBody;
use Soneso\StellarSDK\Xdr\XdrOperationType;
use Soneso\StellarSDK\Xdr\XdrLiquidityPoolConstantProductParameters;
use Soneso\StellarSDK\Xdr\XdrAssetType;
use Soneso\StellarSDK\Xdr\XdrChangeTrustOperation;
use Soneso\StellarSDK\Xdr\XdrLiquidityPoolParameters;
use Soneso\StellarSDK\Xdr\XdrLiquidityPoolType;

class TokenController extends Controller
{
    private $sdk, $maxFee, $network, $token_creation_fee;
    private $xlm_funding_wallet, $xlm_funding_wallet_key, $issuer_wallet_amount, $stakingPublicWallet, $stakingPublicWalletKey, $tkgIssuer, $assetCode, $tkgDepositRatio;
    private WalletService $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT');

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
        $this->maxFee = 30;
        $this->token_creation_fee = 50; //XLM
        $this->issuer_wallet_amount = 4; //XLM
        $this->tkgDepositRatio = 10;
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
            'logo' => 'required|file|mimes:png,jpg,jpeg,webp,svg|max:5120',
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
        $memo = $request->input('memo');
        $lock_status = $request->input('lock_status');
        $distributor_wallet_xlm_balance = $this->wallet->getXlmBalance($distributor_wallet_key);

        if ($distributor_wallet_xlm_balance < ($this->token_creation_fee + 5)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient balance. You need at least ' . $this->token_creation_fee . ' XLM available in your wallet to proceed.',
            ]);
        }


        //charge token creation fee
        $token_creation_charges = $this->tokenCreationXLMFeeTransaction($distributor_wallet_key, $asset_code, $total_supply, $memo, $lock_status);
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
        $token_creation->memo = $memo;
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
                $this->stakingPublicWallet,
                Asset::native(),                  // XLM
                strval($this->token_creation_fee) // amount: fee (200)
            ))
                ->setSourceAccount($distributor_wallet_key)
                ->build();

            // Build the transaction
            $transaction = (new TransactionBuilder($distributorAccount, $this->network))
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
            // try {
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
                        'error' => 'Token creation record not found for this wallet and asset code.',
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

                $generate_token = $this->transfer_created_tokens($distributor_wallet_key, $assetCode, $token_created->issuer_public_key, $token_created->issuer_secret_key, $token_created->total_supply);
                if (!$generate_token) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Token generation failed.'
                    ], 500);
                }

                $created_tokens_transfer_transaction = $this->addStellarTransactionRecord($token_created->id, $distributor_wallet_key, 4, '', $generate_token['signed_xdr'], $generate_token['tx_hash'], true);

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
            // } catch (\Exception $e) {
            //     return false;
            // }
        } else {
            return response()->json([
                'success' => false,
                'error' => 'Transaction failed',
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
            ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Token created by TokenGlade'))
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
        // 70% of the token creation fee goes into the LP as XLM (Asset A)
        $feePercentageForLP = 0.7;

        try {
            $stakingAccountPublicKey = $this->stakingPublicWallet;
            $stakingAccount = $this->sdk->requestAccount($stakingAccountPublicKey);

            $poolId = $this->getPoolIdFromHorizon(
                $this->assetCode,
                $this->tkgIssuer,
                $this->isTestnet ?? true
            );

            if (!$poolId) {
                Log::error('Liquidity pool not found on Horizon', [
                    'assetCode' => $this->assetCode,
                    'issuer' => $this->tkgIssuer,
                ]);
                throw new \RuntimeException('Liquidity pool not found yet on Horizon.');
            }

            $xlmLiquidityAmount = number_format($this->token_creation_fee * $feePercentageForLP, 7, '.', '');
            $tkgLiquidityAmount = number_format(
                (float)$xlmLiquidityAmount * (float)$this->tkgDepositRatio,
                7,
                '.',
                ''
            );

            $minPrice = new Price(1, 100000000);
            $maxPrice = new Price(100000000, 1);

            $tkgAsset = Asset::createNonNativeAsset($this->assetCode, $this->tkgIssuer);

            $txb = new TransactionBuilder($stakingAccount, $this->network);
            $txb->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'LP trustlines + deposit'));

            // 1) Trust TKG (ok to always include)
            $txb->addOperation(
                (new ChangeTrustOperationBuilder($tkgAsset, '922337203685.4775807'))->build()
            );

            // 2) Trust LP shares (use AssetTypePoolShare; ALWAYS include)
            $txb->addOperation($this->buildLpShareChangeTrustOpForSdk());

            // 3) Deposit
            $txb->addOperation(
                (new LiquidityPoolDepositOperationBuilder(
                    $poolId,
                    $xlmLiquidityAmount,
                    $tkgLiquidityAmount,
                    $minPrice,
                    $maxPrice
                ))->build()
            );

            $tx = $txb->build();
            $kp = KeyPair::fromSeed($this->stakingPublicWalletKey);
            $tx->sign($kp, $this->network);
            $response = $this->sdk->submitTransaction($tx);

            if ($response->isSuccessful()) {
                return [
                    'status' => 'success',
                    'message' => 'Liquidity Pool Deposit transaction successfully submitted.',
                    'transaction_hash' => $response->getHash()
                ];
            } else {
                $codes = $response->getExtras()->getResultCodes();
                Log::error('Liquidity Pool Deposit failed', [
                    'result_codes' => $codes,
                ]);
                return [
                    'status' => 'error',
                    'message' => 'Liquidity Pool Deposit submission failed.',
                    'error' => $codes
                ];
            }
        } catch (HorizonRequestException $hex) {
            // Soneso wraps the underlying Guzzle exception; pull the body
            $prev = $hex->getPrevious();
            $body = null;
            if ($prev instanceof ClientException && $prev->getResponse()) {
                $body = (string)$prev->getResponse()->getBody();
            }
            Log::error('HorizonRequestException on submitTransaction', [
                'horizon_message' => $hex->getMessage(),
                'horizon_body'    => $body, // contains extras.result_codes.{transaction,operations}
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
}

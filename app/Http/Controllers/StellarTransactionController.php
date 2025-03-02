<?php

namespace App\Http\Controllers;

use App\Models\ClaimClaimableBalanceId;
use App\Models\StellarToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Soneso\StellarSDK\AbstractTransaction;
use Soneso\StellarSDK\AssetTypeCreditAlphanum12;
use Soneso\StellarSDK\AssetTypeCreditAlphanum4;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\SetOptionsOperationBuilder;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\TransactionBuilder;

class StellarTransactionController extends Controller
{
    private $sdk, $network;

    public function __construct()
    {
        // $this->sdk = StellarSDK::getTestNetInstance();
        // $this->network = Network::testnet();
        $this->sdk = StellarSDK::getPublicNetInstance();
        $this->network = Network::public();
    }

    public function funding_issuer_wallet_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'funding_issuer_wallet_signedXdr' => 'required',
            'issuer_wallet_public_key' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $signedXdr = $request->input('funding_issuer_wallet_signedXdr');
            $issuer_public_key = $request->input('issuer_wallet_public_key');

            // Convert the XDR string into a Transaction object using fromEnvelopeBase64XdrString
            $transactionEnvelope = AbstractTransaction::fromEnvelopeBase64XdrString($signedXdr);
            // Submit the transaction to the Stellar network using the SDK
            $response = $this->sdk->submitTransaction($transactionEnvelope);
            Log::info("Transaction Response:", (array) $response);

            // Check if the transaction was successful
            if ($response && $response->isSuccessful()) {

                // // Update status in claimable_balance_receivers table for all receivers
                DB::table('user_issuer_wallets')
                    ->where('issuer_public_key', $issuer_public_key)
                    ->update([
                        'signed_transaction' => $signedXdr,
                        'status' => 1,
                    ]);

                return response()->json([
                    'success' => true,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'error' => 'Transaction failed',
                    'result_codes' => $response?->getExtras()?->getResultCodes() ?? 'Unknown error',
                    'details' => $response?->getExtras()?->getResultXdr() ?? 'No details available'
                ], 400);
            }
        } catch (HorizonRequestException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            // Catch and return any errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function token_generating_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transactionToSubmit' => 'required',
            'issuer_wallet_public_key' => 'required',
            'issuer_wallet_secrect_key' => 'required',
            'total_supply' => 'required',
            'distributorPublicKey' => 'required',
            'asset_code' => 'required',
            'lock_status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the signed XDR string from the request
            $signedXdr = $request->input('transactionToSubmit');
            $issuerPublicKey = $request->input('issuer_wallet_public_key');
            $issuerSecretkey = $request->input('issuer_wallet_secrect_key');
            $total_supply = $request->input('total_supply');
            $distributorPublicKey = $request->input('distributorPublicKey');
            $asset_code = $request->input('asset_code');
            $lock_status = $request->input('lock_status');


            // Validate input lengths and types
            if (strlen($issuerPublicKey) !== 56) {
                throw new \Exception('Invalid issuer public key length.');
            }
            if (strlen($issuerSecretkey) !== 56) {
                throw new \Exception('Invalid issuer secret key length.');
            }
            if (strlen($distributorPublicKey) !== 56) {
                throw new \Exception('Invalid distributor public key length.');
            }
            if (strlen($asset_code) > 12) {
                throw new \Exception('Asset code cannot exceed 12 characters.');
            }

            // Convert the XDR string into a Transaction object using fromEnvelopeBase64XdrString
            $transactionEnvelope = AbstractTransaction::fromEnvelopeBase64XdrString($signedXdr);

            // Submit the transaction to the Stellar network using the SDK
            $response = $this->sdk->submitTransaction($transactionEnvelope);

            // Check if the transaction was successful
            if ($response) {

                // Load the issuer account
                $issuerKeyPair = KeyPair::fromSeed($issuerSecretkey);
                $issuerAccountId = $issuerKeyPair->getAccountId();
                $issuerAccount = $this->sdk->requestAccount($issuerAccountId);
                // Define the asset
                if (strlen($asset_code) <= 4) {
                    $asset = new AssetTypeCreditAlphanum4($asset_code, $issuerPublicKey);
                } else {
                    $asset = new AssetTypeCreditAlphanum12($asset_code, $issuerPublicKey);
                }

                // Send the total supply from issuer to distributor
                $paymentOperation = (new PaymentOperationBuilder($distributorPublicKey, $asset, $total_supply))->build();

                // Build the payment transaction
                $paymentTransaction = (new TransactionBuilder($issuerAccount))
                    ->addOperation($paymentOperation)
                    ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Token created by TokenGlade'))
                    ->build();


                // Sign the payment transaction
                $paymentTransaction->sign($issuerKeyPair, $this->network);

                // Submit the payment transaction
                $response = $this->sdk->submitTransaction($paymentTransaction);
                if ($response) {

                    if ($lock_status != true) {
                        $token_generated = StellarToken::where('asset_code', $asset_code)
                            ->where('total_supply', $total_supply)
                            ->where('user_wallet_address', $distributorPublicKey)
                            ->where('issuerPublicKey', $issuerPublicKey)
                            ->first();

                        $token_generated->signed_transaction = $signedXdr;
                        $token_generated->memo = 'Token created by TokenGlade';
                        $token_generated->lock_status = $lock_status;
                        $token_generated->status = 1;
                        $token_generated->save();

                        return response()->json([
                            'message' => 'Token created, issued to distributor, & issuer account remains unlocked',
                            'issuer_public_key' => $issuerPublicKey,
                        ]);
                    } else {
                        // Lock the issuer account by setting master weight to 0
                        $lockOperation = (new SetOptionsOperationBuilder())
                            ->setMasterKeyWeight(0) // Set master weight to 0 to lock the account
                            ->build();

                        // Build the lock transaction
                        $lockTransaction = (new TransactionBuilder($issuerAccount))
                            ->addOperation($lockOperation)
                            ->build();

                        // Sign the lock transaction
                        $lockTransaction->sign($issuerKeyPair, $this->network);

                        // Submit the lock transaction to lock the issuer account
                        $this->sdk->submitTransaction($lockTransaction);

                        $token_generated = StellarToken::where('asset_code', $asset_code)
                            ->where('total_supply', $total_supply)
                            ->where('user_wallet_address', $distributorPublicKey)
                            ->where('issuerPublicKey', $issuerPublicKey)
                            ->first();

                        $token_generated->signed_transaction = $signedXdr;
                        $token_generated->memo = 'Token created by TokenGlade';
                        $token_generated->lock_status = $lock_status;
                        $token_generated->status = 1;
                        $token_generated->save();

                        // Return the success message and issuer account details
                        return response()->json([
                            'message' => 'Token created, issued to distributor, and issuer account locked',
                            'issuer_public_key' => $issuerPublicKey,
                        ]);
                    }
                } else {
                    // Log and return the failure response including extras.result_codes
                    $resultCodes = $response->getExtras()->getResultCodes();
                    return response()->json([
                        'error' => 'Transaction failed',
                        'result_codes' => $resultCodes,
                        'details' => $response->getExtras()->getResultXdr() // Include detailed XDR for further debugging
                    ], 400);
                }
            }

            // If transaction fails, return error
            return response()->json(['error' => 'Trustline transaction failed'], 400);
        } catch (HorizonRequestException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            // Catch and return any errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function claimable_balance_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transactionToSubmit' => 'required',
            'claimable_balance_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the signed XDR string from the request
            $signedXdr = $request->input('transactionToSubmit');

            // Convert the XDR string into a Transaction object using fromEnvelopeBase64XdrString
            $transactionEnvelope = AbstractTransaction::fromEnvelopeBase64XdrString($signedXdr);

            // Submit the transaction to the Stellar network using the SDK
            $response = $this->sdk->submitTransaction($transactionEnvelope);

            // Check if the transaction was successful
            if ($response && $response->isSuccessful()) {
                DB::transaction(function () use ($request) {
                    // Assuming you get the claimable_balance_id from the request
                    $claimableBalanceId = $request->input('claimable_balance_id');

                    // Update status in claimable_balances table
                    DB::table('claimable_balances')
                        ->where('id', $claimableBalanceId)
                        ->update(['status' => 1, 'updated_at' => now()]);

                    // Update status in claimable_balance_receivers table for all receivers
                    DB::table('claimable_balance_receivers')
                        ->where('claimable_balance_id', $claimableBalanceId)
                        ->update(['status' => 1, 'updated_at' => now()]);
                });

                return response()->json([
                    'status' => 'success',
                ]);
            } else {
                // Log and return the failure response including extras.result_codes
                $resultCodes = $response->getExtras()->getResultCodes();
                return response()->json([
                    'error' => 'Transaction failed',
                    'result_codes' => $resultCodes,
                    'details' => $response->getExtras()->getResultXdr() // Include detailed XDR for further debugging
                ], 400);
            }

            // If transaction fails, return error
            return response()->json(['error' => 'Transaction failed'], 400);
        } catch (HorizonRequestException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            // Catch and return any errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reclaim_claimable_balance_transaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transactionToSubmit' => 'required',
            'claim_claimable_balance_id' => 'required',
            'wallet_ids' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get the signed XDR string from the request
            $signedXdr = $request->input('transactionToSubmit');

            // Convert the XDR string into a Transaction object using fromEnvelopeBase64XdrString
            $transactionEnvelope = AbstractTransaction::fromEnvelopeBase64XdrString($signedXdr);

            // // Submit the transaction to the Stellar network using the SDK
            $response = $this->sdk->submitTransaction($transactionEnvelope);

            // // Check if the transaction was successful
            if ($response && $response->isSuccessful()) {
                DB::beginTransaction();

                try {
                    // Assuming you get the claimable_balance_id from the request
                    $claimableBalanceIds = $request->input('claim_claimable_balance_id');
                    $walletIds = $request->input('wallet_ids');

                    if ($walletIds) {
                        // Update status in claimable_balances table for each wallet_id
                        foreach ($walletIds as $walletId) {
                            DB::table('claim_claimable_claimants')
                                ->where('claimants_wallet_address', $walletId)
                                ->update(['status' => 1]);
                        }
                    }

                    if ($claimableBalanceIds) {
                        // Update status in claimable_balance_receivers table for each claimable balance ID
                        foreach ($claimableBalanceIds as $claimableBalanceId) {
                            DB::table('claim_claimable_balance_ids')
                                ->where('balance_id', $claimableBalanceId)
                                ->update(['status' => 1]);
                        }
                    }

                    // Update status in claim_claimable_balances table
                    $abc = ClaimClaimableBalanceId::where('balance_id', $claimableBalanceIds)->first();
                    if (!$abc) {
                        // DB::rollBack();
                        return response()->json(['status' => 'error', 'message' => 'No matching balance ID found'], 404);
                    }
                    DB::table('claim_claimable_balances')
                        ->where('id', $abc->claim_claimable_balance_id) // Cast to integer
                        ->update(['status' => 1]);
                    DB::commit();
                } catch (\Exception $e) {
                    // Roll back the transaction in case of error
                    DB::rollBack();
                    throw $e; // Re-throw the exception to be caught by the outer catch
                }

                return response()->json([
                    'status' => 'success',
                ]);
            } else {
                // Log and return the failure response including extras.result_codes
                $resultCodes = $response->getExtras()->getResultCodes();
                return response()->json([
                    'error' => 'Transaction failed',
                    'result_codes' => $resultCodes,
                    'details' => $response->getExtras()->getResultXdr() // Include detailed XDR for further debugging
                ], 400);
            }

            // If transaction fails, return error
            return response()->json(['error' => 'Transaction failed'], 400);
        } catch (HorizonRequestException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            // Catch and return any errors
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

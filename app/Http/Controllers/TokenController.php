<?php

namespace App\Http\Controllers;

use DateTime;
use Exception;
use Soneso\StellarSDK\StellarSdk;
use Soneso\StellarSDK\AssetTypeCreditAlphanum4;
use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Memo;
use Soneso\StellarSDK\CreateClaimableBalanceOperation;
use Soneso\StellarSDK\Claimant;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\PaymentOperationBuilder;
use Soneso\StellarSDK\Signer;
use Soneso\StellarSDK\Transaction;
use Soneso\StellarSDK\TransactionBuilder;
use Soneso\StellarSDK\Xdr\XdrDecoratedSignature;
use Soneso\StellarSDK\Xdr\XdrSigner;
use Soneso\StellarSDK\TimeBounds;
use GuzzleHttp\Client;
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\ChangeTrustOperationBuilder;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Soneso\StellarSDK\AssetTypeCreditAlphanum12;

class TokenController extends Controller
{
    private $sdk, $maxFee;

    public function __construct()
    {
        // $this->sdk = StellarSdk::getPublicNetInstance();
        $this->sdk = StellarSdk::getTestNetInstance();
        $this->maxFee = 3000;
    }

    public function check_wallet(Request $request)
    {
        $private_key = $request->input('private_key');
        try 
            {
                $issuerKeyPair = KeyPair::fromSeed($private_key);
            } 
            catch (InvalidArgumentException $e) {
                return response()->json(['status' => 'error', 'msg' => 'The private key is not valid']);
            }

        // $account = $this->sdk->requestAccount($walletAddress);
        // foreach ($account->getBalances() as $balance) {
        //     if ($balance->getAssetType() === 'native') {
        //         if ($balance->getBalance() <= 5) {
        //             return response()->json(['status' => 0, 'msg' => 'Account does not have enough XLM. Please deposit at least 5 XLM!']);
        //         } else {
        //             return response()->json(['status' => 1]);
        //         }
        //     }
        // }
    }

    public function generate_token(Request $request)
    {
        try {
            $ticker = $request->input('ticker');
            $total_supply = $request->input('total_supply');
            $issuer_wallet_address_private_key = $request->input('issuer_wallet_private_key');
            $distributor_wallet_address_private_key = $request->input('distributor_wallet_private_key');

            // check if issuer wallet private key is valid or not 
            // try 
            // {
            //     $issuerKeyPair = KeyPair::fromSeed($issuer_wallet_address_private_key);
            // } 
            // catch (InvalidArgumentException $e) {
            //     return response()->json(['status' => 'error', 'msg' => 'The issuer wallet private key is not valid"']);
            // }
            
            $issuerKeyPair = KeyPair::fromSeed($issuer_wallet_address_private_key);
            $issuerAccountId = $issuerKeyPair->getAccountId();
            
            $distributorKeyPair = KeyPair::fromSeed($distributor_wallet_address_private_key);
            $distributorAccountId = $distributorKeyPair->getAccountId();
            $distributorAccount = $this->sdk->requestAccount($distributorAccountId);

            // if ticker is less or equal to 4 characters 
            if (strlen($ticker) <= 4) {
                // Define the custom asset/token issued by the issuer account
                $asset = new AssetTypeCreditAlphaNum4($ticker, $issuerAccountId);
            }
            else{
                // Define the custom asset/token issued by the issuer account
                $asset = new AssetTypeCreditAlphanum12($ticker, $issuerAccountId);
            }

            // Prepare a change trust operation for the distributor account and trust limit
            $changeTrustOperation = (new ChangeTrustOperationBuilder($asset))->build();

            // Build the transaction
            $transactionBuilder = new TransactionBuilder($distributorAccount);
            $transactionBuilder
                ->setMaxOperationFee($this->maxFee)
                ->addOperation($changeTrustOperation)
                ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT,'Token created by TokenGlade'));

            $transaction = $transactionBuilder->build();
            $transaction->sign($distributorKeyPair, Network::testnet());
            // $transaction->sign($distributorKeyPair, Network::public());
            $result = $this->sdk->submitTransaction($transaction);


            // Load the issuer account data from the Stellar network
            $issuerAccount = $this->sdk->requestAccount($issuerAccountId);

            // Build the transaction for the payment operation from the issuer to the distributor
            $paymentOperation = (new PaymentOperationBuilder($distributorAccountId, $asset, $total_supply))->build();
            $transaction = (new TransactionBuilder($issuerAccount))->addOperation($paymentOperation)->build();

            // The issuer signs the transaction.
            $transaction->sign($issuerKeyPair, Network::testnet());
            // $transaction->sign($issuerKeyPair, Network::public());

            // Submit the transaction.
            $response = $this->sdk->submitTransaction($transaction);
            if ($response) {
                // Sleep for a few seconds to allow the transaction to be confirmed (not recommended in production)
                sleep(5);

                // Load the distributor account data from the Stellar network again to get updated balances
                $distributorAccount = $this->sdk->requestAccount($distributorAccountId);
                $paymentReceived = false;
                // Check that the payment was successfully received by the distributor
                foreach ($distributorAccount->getBalances() as $balance) {
                    $balanceFloat = floatval($balance->getBalance());
                    $balanceInteger = number_format($balanceFloat, 0, '', '');
                    if ($balance->getAssetType() != 'native' && $balance->getAssetCode() == $ticker && $balanceInteger == $total_supply) {
                        $paymentReceived = true;
                        break;
                    }
                }
                if ($paymentReceived) {
                    return response()->json(['status' => 'success', 'msg' => 'Token has Created Successfully. Please Check Your Wallet']);
                } else {
                    return response()->json(['status' => 'error', 'msg' => 'Something Went Wrong']);
                }
            }
        } catch (\Throwable $th) {
            return  "Something Went Wrong";
        }
    }

    public function claimable_balance(Request $request)
    {
        try {
            $user_wallet_address_private_key = $request->wallet_address_private_key;
            $assetCode = $request->input('token');
            $amount = $request->input('amount');
            $target_addresses = $request->input('target_wallet_address');
            $memo = $request->input('memo');


            $receiver_claim_time = $request->input('receiver_claim_time');
            if (!empty($receiver_claim_time)) {
                $dateTime = new DateTime($receiver_claim_time);
                $timestamp = $dateTime->getTimestamp();

                //when the funds can be claimed by receivers
                $ReceiverCanClaim = Claimant::predicateBeforeRelativeTime($timestamp);
            } else {
                // Set $ReceiverCanClaim to null, indicating that the claimants can claim the funds immediately
                $ReceiverCanClaim = null;
            }

            $soon = time() + 60;
            //The funds can only be reclaimed within a specific timeframe
            $UserCanReclaim = Claimant::predicateNot(
                Claimant::predicateBeforeAbsoluteTime(strval($soon))
            );

            // Split the $target_addresses string into an array of individual addresses
            $target_addresses_array = explode("\n", $target_addresses);

            $UserKeypair = KeyPair::fromSeed($user_wallet_address_private_key);
            // if (!KeyPair::fromSeed($user_wallet_address_private_key)) {
            //     throw new Exception("The distributor wallet address private key is not a valid seed.");
            // }
            $user_public_key = $UserKeypair->getAccountId();
            $userAccount = $this->sdk->requestAccount($user_public_key);

            $claimantsByReceiver = [];

            // Add claimants for each receiver to the $claimants array
            foreach ($target_addresses_array as $receiver) {
                $claimants = [];

                if ($ReceiverCanClaim !== null) {
                    $claimants[] = new Claimant($receiver, $ReceiverCanClaim);
                }
                $claimants[] = new Claimant($user_public_key, $UserCanReclaim);
                $claimantsByReceiver[$receiver] = $claimants;
            }


            $issuer_id = null; // Initialize the issuer_id variable
            $hasEnoughBalance = false; // Initialize a flag variable

            foreach ($userAccount->getBalances() as $balance) {
                $balanceFloat = floatval($balance->getBalance());
                $balanceInteger = number_format($balanceFloat, 0, '', '');

                if ($balance->getAssetType() != 'native' && $balance->getAssetCode() == $assetCode && $balanceInteger >= $amount) {
                    $issuer_id = $balance->getAssetIssuer();
                    $hasEnoughBalance = true; // Set the flag to true
                    break; // Exit the loop since we found a matching balance
                }
            }

            // Check the flag variable to determine if enough balance was found
            if ($hasEnoughBalance) {
                // Create the asset
                $asset = new AssetTypeCreditAlphanum4($assetCode, $issuer_id);

                // Build the transaction for each receiver and sign it separately
                $transactionIds = [];
                foreach ($claimantsByReceiver as $receiver => $claimants) {
                    // Create the claimable balance operation
                    $claimableBalanceOperation = new CreateClaimableBalanceOperation($claimants, $asset, $amount);
                    $cleanedReceiver = trim($receiver);


                    // Build the transaction
                    $transactionBuilder = new TransactionBuilder($userAccount);
                    $transactionBuilder
                        ->setMaxOperationFee($this->maxFee)
                        ->addOperation($claimableBalanceOperation);

                    if (!empty($memo)) {
                        $transactionBuilder->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, $memo, 'by TokenGlade'));
                    } else {
                        $transactionBuilder->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'by TokenGlade'));
                    }

                    $transaction = $transactionBuilder->build();
                    $transaction->sign($UserKeypair, Network::testnet());
                    // $transaction->sign($UserKeypair, Network::public());
                    $result = $this->sdk->submitTransaction($transaction);
                    $transactionIds[$cleanedReceiver] = $result->getId();
                }

                //return $transactionIds;
                // Return success response with Wallet Address and Transaction IDs
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Claimable balance created successfully',
                        'data' => [
                            'wallet_address' => $user_public_key,
                            'transaction_ids' => $transactionIds,
                        ],
                    ],
                    //It represents the HTTP status code 200 OK, which indicates that the request was successful, and the server has returned the requested data
                    Response::HTTP_OK
                );
            } else {
                return response()->json(['status' => 'error', 'msg' => 'Insufficient balance for the specified asset and amount'], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Throwable $th) {
            // Return error response for any unexpected errors
            return response()->json(['status' => 'error', 'message' => 'Something went wrong. Please try again later.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function token_transfer(Request $request)
    {
        return null;
    }
}

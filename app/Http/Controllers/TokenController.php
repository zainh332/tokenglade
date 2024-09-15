<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DateTime;
use Exception;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Server;
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
use Soneso\StellarSDK\Asset;
use Soneso\StellarSDK\ChangeTrustOperationBuilder;



use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Soneso\StellarSDK\AbstractTransaction;
use Soneso\StellarSDK\AssetTypeCreditAlphanum12;
use Soneso\StellarSDK\ClaimClaimableBalanceOperation;
use Soneso\StellarSDK\CreateAccountOperationBuilder;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;
use Soneso\StellarSDK\SetOptionsOperationBuilder;
use Soneso\StellarSDK\Util\FriendBot;
use Soneso\StellarSDK\Xdr\XdrTransactionEnvelope;
use Soneso\StellarSDK\Xdr\TransactionEnvelope;

class TokenController extends Controller
{
    private $sdk, $maxFee;

    public function __construct()
    {
        // $this->sdk = StellarSDK::getPublicNetInstance();
        $this->sdk = StellarSDK::getTestNetInstance();
        $this->maxFee = 3000;
    }

    public function check_wallet(Request $request)
    {
        $private_key = $request->input('private_key');

        // Continue only if private_key is not null
        if ($private_key !== null) {
            try {
                $private_key_pair = KeyPair::fromSeed($private_key);
                // Fetch the public address of the wallet from the private key
                $privatekeyId = $private_key_pair->getAccountId();
                // Fetch details of the wallet from the public address
                $privatekeyAccount = $this->sdk->requestAccount($privatekeyId);
                // Fetch balance of the wallet
                foreach ($privatekeyAccount->getBalances() as $balance) {
                    // Fetch XLM (native) balance of the wallet
                    if ($balance->getAssetType() === 'native') {
                        // Checking if the XLM balance is less than 5 
                        if ($balance->getBalance() < 5) {
                            return response()->json(['status' => 'error', 'msg' => 'Account does not have enough XLM. Please deposit at least 5 XLM']);
                        }
                    }
                }
            } catch (\InvalidArgumentException $e) {
                return response()->json(['status' => 'error', 'msg' => 'Invalid Private Key']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'msg' => 'Wallet is not active']);
            }
        }
    }


    public function check_holding_tokens(Request $request)
    {
        $private_key = $request->input('private_key');
        $token = $request->input('token');

        // Continue only if private_key is not null
        if ($private_key !== null) {
            try {
                $private_key_pair = KeyPair::fromSeed($private_key);
                // Fetch the public address of the wallet from the private key
                $privatekeyId = $private_key_pair->getAccountId();
                // Fetch details of the wallet from the public address
                $privatekeyAccount = $this->sdk->requestAccount($privatekeyId);
                // Fetch balance of the wallet

                $holding_tokens = false; // Initialize a flag variable

                foreach ($privatekeyAccount->getBalances() as $balance) {
                    if ($balance->getAssetType() != 'native' && $balance->getAssetCode() === $token) {
                        $holding_tokens = true;
                        break;
                    } else {
                        $holding_tokens = false;
                    }
                }
                if ($holding_tokens === false) {
                    return response()->json(['status' => 'error', 'msg' => 'You dont hold ' . $token . ' tokens']);
                }
            } catch (\InvalidArgumentException $e) {
                return response()->json(['status' => 'error', 'msg' => 'Invalid Private Key']);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'msg' => 'Wallet is not active']);
            }
        }
    }

    public function generate_token(Request $request)
    {
        // try {
        // Get input data from the request
        $asset_code = $request->input('asset_code');
        $total_supply = $request->input('total_supply');
        $distributor_wallet_key = $request->input('distributor_wallet_key'); // Use distributor's secret key (private key)

        // Generate a new keypair for the issuer
        $issuerKeyPair = KeyPair::random();
        $issuerPublicKey = $issuerKeyPair->getAccountId();
        $issuerSecretkey = $issuerKeyPair->getSecretSeed();

        // Fund the issuer account on the testnet using Friendbot (for testnet only)
        file_get_contents("https://friendbot.stellar.org/?addr=" . $issuerPublicKey);

        // Load the distributor account using its private key
        $distributorAccount = $this->sdk->requestAccount($distributor_wallet_key);

        if (strlen($asset_code) <= 4) {
            $asset = new AssetTypeCreditAlphaNum4($asset_code, $issuerPublicKey);
        } else {
            $asset = new AssetTypeCreditAlphanum12($asset_code, $issuerPublicKey);
        }

        // Create a trustline between distributor and the asset
        $trustlineOperation = (new ChangeTrustOperationBuilder($asset))->build();

        // Build the trustline transaction
        $trustlineTransaction = (new TransactionBuilder($distributorAccount, Network::testnet()))
            ->addOperation($trustlineOperation)
            ->build();

        // Return the XDR string to the frontend
        return response()->json([
            'unsigned_trustline_transaction' => $trustlineTransaction->toEnvelopeXdrBase64(),
            'issuerPublicKey' => $issuerPublicKey,
            'issuerSecretkey' => $issuerSecretkey,
            'total_supply' => $total_supply,
            'asset_code' => $asset_code
        ]);
    }

    public function submit_transaction(Request $request)
    {
        try {
            // Get the signed XDR string from the request
            $signedXdr = $request->input('transactionToSubmit');
            $issuerPublicKey = $request->input('issuerPublicKey');
            $issuerSecretkey = $request->input('issuerSecretkey');
            $total_supply = $request->input('total_supply');
            $distributorPublicKey = $request->input('distributorPublicKey');
            $asset_code = $request->input('asset_code');

            
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
            $transactionEnvelope = Transaction::fromEnvelopeBase64XdrString($signedXdr);
            
            // Submit the transaction to the Stellar network using the SDK
            $response = $this->sdk->submitTransaction($transactionEnvelope);
            return response()->json([
                'message' => 'Token created, issued to distributor, and issuer account locked',
                'issuer_public_key' => $response,
            ]);

            // Check if the transaction was successful
            if ($response) {

                // Load the issuer account
                $issuerKeyPair = KeyPair::fromSeed($issuerSecretkey);
                $issuerAccountId = $issuerKeyPair->getAccountId();
                $issuerAccount = $this->sdk->requestAccount($issuerAccountId);
                // Define the asset
                if (strlen($asset_code) <= 4) {
                    $asset = new AssetTypeCreditAlphaNum4($asset_code, $issuerPublicKey);
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


                // Sign the payment transaction with the issuer's private key
                $paymentTransaction->sign($issuerKeyPair, Network::testnet());

                // Submit the payment transaction
                $response = $this->sdk->submitTransaction($paymentTransaction);
                if ($response) {
                    // Lock the issuer account by setting master weight to 0
                    $lockOperation = (new SetOptionsOperationBuilder())
                        ->setMasterKeyWeight(0) // Set master weight to 0 to lock the account
                        ->build();

                    // Build the lock transaction
                    $lockTransaction = (new TransactionBuilder($issuerAccount))
                        ->addOperation($lockOperation)
                        ->build();

                    // Sign the lock transaction with the issuer's private key
                    $lockTransaction->sign($issuerKeyPair, Network::testnet());

                    // Submit the lock transaction to lock the issuer account
                    $this->sdk->submitTransaction($lockTransaction);

                    // Return the success message and issuer account details
                    return response()->json([
                        'message' => 'Token created, issued to distributor, and issuer account locked',
                        'issuer_public_key' => $issuerPublicKey,
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

    public function claimable_balance(Request $request)
    {
        try {
            $user_wallet_address_private_key = $request->wallet_address_private_key;
            $assetCode = $request->input('token');
            $amount = $request->input('amount');
            $target_addresses = $request->input('target_wallet_address');
            $memo = $request->input('memo');


            // $receiver_claim_time = $request->input('receiver_claim_time');
            $receiver_claim_time = Carbon::now();
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

                // if ($balance->getAssetType() != 'native' && $balance->getAssetCode() == $assetCode && $balanceInteger >= $amount) {
                if ($balanceInteger >= $amount) {
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
                        $transactionBuilder->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, $memo . ' (TokenGlade)'));
                    } else {
                        $transactionBuilder->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'By TokenGlade'));
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

    public function calim_claimable_balance(Request $request)
    {
        // $asset_code;
        // $issuer_wallet_address;
        // $distributor_wallet_address;
        // fetch('https://horizon.stellar.org/claimable_balances/?asset='.$asset_code.'%3A'.$issuer_wallet_address.'&claimant='.$distributor_wallet_address.'&limit=200&order=desc')

        // $distributor_wallet_address_private_key = $request->input('distributor_wallet_private_key');
        // $distributorKeyPair = KeyPair::fromSeed($distributor_wallet_address_private_key);
        // $distributorAccountId = $distributorKeyPair->getAccountId();
        // $distributorAccount = $this->sdk->requestAccount($distributorAccountId);$transactionBuilder = new TransactionBuilder($distributorAccount);

        // $claim_claimable_Balance_Operation = new ClaimClaimableBalanceOperation($balance_id);

        // $transactionBuilder
        //     ->setMaxOperationFee($this->maxFee)
        //     ->addOperation($changeTrustOperation)
        //     ->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'Token created by TokenGlade'));

        // $transaction = $transactionBuilder->build();
        // // $transaction->sign($distributorKeyPair, Network::testnet());
        // $transaction->sign($distributorKeyPair, Network::public());
        // $result = $this->sdk->submitTransaction($transaction);
    }
}

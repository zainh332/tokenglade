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
        $this->maxFee = 30000;
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


    public function fetch_holding_tokens_total_xlm(Request $request)
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
                        // Store non-native assets
                        $tokens[] = [
                            'code' => $balance->getAssetCode(), // Asset code
                            'issuer' => $balance->getAssetIssuer(), // Asset issuer
                            'balance' => $balance->getBalance(), // Asset balance
                        ];
                    }
                }

                if (count($tokens) > 0) {
                    return response()->json([
                        'status' => 'success',
                        'tokens' => $tokens,
                        'total_xlm' => $totalXLM, // Include the total XLM balance in the response
                    ]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'No Tokens Found in Connected Wallet']);
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


    public function generate_token(Request $request)
    {
        try {
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
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'msg' => 'Error while generating token']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'msg' => 'Something went wrong']);
        }
    }

    public function token_generating_transaction(Request $request)
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
        $validatedData = $request->validate([
            'distributor_wallet_address' => 'required|string', // Distributor wallet private key
            'token' => 'required|string',                      // Asset token
            'amount' => 'required|numeric|min:1',              // Amount must be a number and greater than 0
            'target_wallet_address' => 'required|string',      // Target wallet addresses in string format
            'claimable_after' => 'required|integer|min:1',     // Time (in minutes) after which the balance can be claimed
        ]);

        $user_distributor_wallet_address = $request->distributor_wallet_address;
        $assetCode = $request->input('token');
        $amount = $request->input('amount');
        $target_addresses = $request->input('target_wallet_address');
        $memo = $request->input('memo');
        $claimable_after = $request->input('claimable_after'); // Time in minutes after which balance is claimable
        $usercanclaimUnit = $request->input('user_can_claim_unit');


        // Convert reclaim time to seconds based on the unit provided by the user
        switch ($usercanclaimUnit) {
            case 'minutes':
                $canclaimTimeInSeconds = $claimable_after * 60;
                break;
            case 'hours':
                $canclaimTimeInSeconds = $claimable_after * 3600;
                break;
            case 'days':
                $canclaimTimeInSeconds = $claimable_after * 86400;
                break;
            default:
                $canclaimTimeInSeconds = 86400; // Default to 1 day
                break;
        }

        // Calculate the time after which the recipient can claim (relative time)
        // $timestampForClaim = time() + ($claimableAfterMinutes * 60);

        // // The balance can only be claimed after this time
        // $ReceiverCanClaim = Claimant::predicateNot(
        //     Claimant::predicateBeforeAbsoluteTime(strval($timestampForClaim))
        // );

        $ReceiverCanClaim = Claimant::predicateNot(
            Claimant::predicateBeforeAbsoluteTime(strval(time() + $canclaimTimeInSeconds))
        );
        
        $soon = time() + 60; // Example reclaim time, set to 1 minute after creation

        // The funds can only be reclaimed within a specific timeframe
        $DistributorCanReclaim = Claimant::predicateNot(
            Claimant::predicateBeforeAbsoluteTime(strval($soon))
        );

        // Split the $target_addresses string into an array of individual addresses
        $target_addresses_array = explode("\n", $target_addresses);

        $number_of_addresses = count($target_addresses_array);
        $distributorAccount = $this->sdk->requestAccount($user_distributor_wallet_address);
        
        // Initialize variables for balance checks
        $issuer_id = null;
        $hasEnoughTokens = false;
        $hasEnoughXLM = false;
        $total_tokens = $amount * $number_of_addresses;

        // Check for sufficient XLM balance and token balance in the distributor's wallet
        foreach ($distributorAccount->getBalances() as $balance) {
            if ($balance->getAssetType() === 'native') {
                $balanceFloat = floatval($balance->getBalance());
                if ($balanceFloat >= $number_of_addresses) {
                    $hasEnoughXLM = true; // Sufficient XLM for reserves
                }
            }
            // Check for non-native token balance
            if ($balance->getAssetCode() === $assetCode) {
                $holding_tokens = floatval($balance->getBalance());
                if ($holding_tokens >= $total_tokens) {
                    $issuer_id = $balance->getAssetIssuer();
                    $hasEnoughTokens = true;
                }
            }
        }

        // If there is not enough XLM, return an error
        if (!$hasEnoughXLM) {
            return response()->json(['status' => 'error', 'msg' => 'Insufficient XLM balance to create claimable balances'], Response::HTTP_BAD_REQUEST);
        }

        // If there is not enough token balance, return an error
        if (!$hasEnoughTokens) {
            return response()->json(['status' => 'error', 'msg' => 'Insufficient balance for the specified asset and amount'], Response::HTTP_BAD_REQUEST);
        }

        // Create the asset
        if (strlen($assetCode) <= 4) {
            $asset = new AssetTypeCreditAlphaNum4($assetCode, $issuer_id);
        } else {
            $asset = new AssetTypeCreditAlphanum12($assetCode, $issuer_id);
        }

        // Build the transaction for each receiver and sign it separately
        $claimants = [];
        foreach ($target_addresses_array as $receiver) {
            $claimants[] = new Claimant($receiver, $ReceiverCanClaim); // Each receiver
        }
        $claimants[] = new Claimant($user_distributor_wallet_address, $DistributorCanReclaim);

        $claimableBalanceOperation = new CreateClaimableBalanceOperation($claimants, $asset, $amount);

        // Build the transaction with a single operation that has single or multiple claimants
        $transactionBuilder = (new TransactionBuilder($distributorAccount, Network::testnet()))
            ->setMaxOperationFee($this->maxFee)
            ->addOperation($claimableBalanceOperation);

        if (!empty($memo)) {
            $transactionBuilder->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, $memo));
        } else {
            $transactionBuilder->addMemo(new Memo(Memo::MEMO_TYPE_TEXT, 'By TokenGlade'));
        }
        $transaction = $transactionBuilder->build();

        // Return success response with Wallet Address and Transaction IDs
        return response()->json(
            [
                'status' => 'success',
                'message' => 'Claimable balance created successfully',
                'data' => [
                    'unsigned_transactions' => $transaction->toEnvelopeXdrBase64(),
                    'wallet_address' => $user_distributor_wallet_address,
                ],
            ],
            Response::HTTP_OK
        );
    }

    public function submit_transaction(Request $request)
    {
        try {
            // Get the signed XDR string from the request
            $signedXdr = $request->input('transactionToSubmit');

            // Convert the XDR string into a Transaction object using fromEnvelopeBase64XdrString
            $transactionEnvelope = AbstractTransaction::fromEnvelopeBase64XdrString($signedXdr);

            // Submit the transaction to the Stellar network using the SDK
            $response = $this->sdk->submitTransaction($transactionEnvelope);

            // Check if the transaction was successful
            if ($response) {

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

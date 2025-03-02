<?php

namespace App\Http\Controllers;

use App\Models\ClaimClaimableBalance;
use App\Models\ClaimClaimableBalanceId;
use App\Models\ClaimClaimableClaimant;
use App\Models\StellarToken;
use App\Models\UserIssuerWallet;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
    private $sdk, $maxFee, $network;

    public function __construct()
    {
        // $this->sdk = StellarSDK::getTestNetInstance();
        // $this->network = Network::testnet();
        $this->sdk = StellarSDK::getPublicNetInstance();
        $this->network = Network::public();
        $this->maxFee = 30000;
    }

    public function generate_issuer_wallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributor_wallet_key' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $distributor_wallet_key = $request->input('distributor_wallet_key');

            $check_xlm_balance = checkXlmBalance($distributor_wallet_key);
            if($check_xlm_balance < 6){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient balance. You need at least 6 XLM to proceed.',
                ]);
            }
            $xlm_amount = 3;

            // Generate a new keypair for the issuer
            $issuerKeyPair = KeyPair::random();
            $issuerPublicKey = $issuerKeyPair->getAccountId();
            $issuerSecretkey = $issuerKeyPair->getSecretSeed();

            $distributorAccount = $this->sdk->requestAccount($distributor_wallet_key);

            // Step 1: Create & Fund the Issuer Wallet
            $createAccountOp = (new CreateAccountOperationBuilder($issuerPublicKey, strval($xlm_amount)))->build();

            // Build & Sign the Transaction
            $transaction = (new TransactionBuilder($distributorAccount, $this->network))
            ->addOperation($createAccountOp)
            ->build();

            // Convert transaction to XDR format
            $unsignedXDR = $transaction->toEnvelopeXdrBase64();

            $token_generated = new UserIssuerWallet();
            $token_generated->user_wallet_address = $distributor_wallet_key;
            $token_generated->issuer_public_key = $issuerPublicKey;
            $token_generated->issuer_secret_key = $issuerSecretkey;
            $token_generated->xlm_amount = $xlm_amount;
            $token_generated->unsigned_transaction = $unsignedXDR;
            $token_generated->save();

            // Return the XDR string to the frontend
            return response()->json([
                'success' => true,
                'unsignedXDR' => $unsignedXDR,
                'issuerPublicKey' => $issuerPublicKey,
                'issuerSecretkey' => $issuerSecretkey,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error while creating issuer wallet']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function generate_token(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_code' => 'required',
            'total_supply' => 'required',
            'distributor_wallet_key' => 'required',
            'issuer_wallet_public_key' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get input data from the request
            $asset_code = $request->input('asset_code');
            $total_supply = $request->input('total_supply');
            $distributor_wallet_key = $request->input('distributor_wallet_key');
            $issuerPublicKey = $request->input('issuer_wallet_public_key');

            // Load the distributor account
            $distributorAccount = $this->sdk->requestAccount($distributor_wallet_key);

            if (strlen($asset_code) <= 4) {
                $asset = new AssetTypeCreditAlphaNum4($asset_code, $issuerPublicKey);
            } else {
                $asset = new AssetTypeCreditAlphanum12($asset_code, $issuerPublicKey);
            }

            // Create a trustline between distributor and the asset
            $trustlineOperation = (new ChangeTrustOperationBuilder($asset))->build();

            // Build the trustline transaction
            $trustlineTransaction = (new TransactionBuilder($distributorAccount, $this->network))
                ->addOperation($trustlineOperation)
                ->build();

            $token_generated = new StellarToken();
            $token_generated->asset_code = $asset_code;
            $token_generated->total_supply = $total_supply;
            $token_generated->user_wallet_address = $distributor_wallet_key;
            $token_generated->issuerPublicKey = $issuerPublicKey;
            $token_generated->issuerSecretkey = $issuerPublicKey;
            $token_generated->unsigned_transaction = $trustlineTransaction->toEnvelopeXdrBase64();
            $token_generated->save();

            // Return the XDR string to the frontend
            return response()->json([
                'unsigned_trustline_transaction' => $trustlineTransaction->toEnvelopeXdrBase64(),
                'issuerPublicKey' => $issuerPublicKey,
                'issuerSecretkey' => $issuerPublicKey,
                'total_supply' => $total_supply,
                'asset_code' => $asset_code
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['status' => 'error', 'message' => 'Error while generating token']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong']);
        }
    }

    public function claimable_balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'distributor_wallet_address' => 'required|string',
            'token' => 'required',
            'amount' => 'required|numeric|min:1', 
            'reclaim_time' => 'required|numeric|min:1',
            'sender_can_claim_unit' => 'required',
            'target_wallet_address' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user_distributor_wallet_address = $request->distributor_wallet_address;
        $assetCode = $request->input('token');
        $amount = $request->input('amount');
        $receiver_addresses = $request->input('target_wallet_address');
        $memo = $request->input('memo');
        $claimable_after = $request->input('claimable_after'); // Time in minutes after which balance is claimable
        $reclaim_time = $request->input('reclaim_time');
        $sendercanclaimUnit = $request->input('sender_can_claim_unit');

        if ($claimable_after) {
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

            $ReceiverCanClaim = Claimant::predicateNot(
                Claimant::predicateBeforeAbsoluteTime(strval(time() + $canclaimTimeInSeconds))
            );
        } else {
            $ReceiverCanClaim = Claimant::predicateNot(
                Claimant::predicateBeforeAbsoluteTime(strval(time()))
            );
        }

        // Convert reclaim time to seconds based on the unit provided by the user
        switch ($sendercanclaimUnit) {
            case 'minutes':
                $sendercanclaimTimeInSeconds = $reclaim_time * 60;
                break;
            case 'hours':
                $sendercanclaimTimeInSeconds = $reclaim_time * 3600;
                break;
            case 'days':
                $sendercanclaimTimeInSeconds = $reclaim_time * 86400;
                break;
            default:
                $sendercanclaimTimeInSeconds = 86400; // Default to 1 day
                break;
        }

        // $soon = time() + 60; // Example reclaim time, set to 1 minute after creation

        // The funds can only be reclaimed within a specific timeframe
        $DistributorCanReclaim = Claimant::predicateNot(
            Claimant::predicateBeforeAbsoluteTime(strval(time() + $sendercanclaimTimeInSeconds))
        );

        // Split the $receiver_addresses string into an array of individual addresses
        $receiver_addresses_array = explode("\n", $receiver_addresses);

        $number_of_addresses = count($receiver_addresses_array);
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
            return response()->json(['status' => 'error', 'message' => 'Insufficient XLM balance to create claimable balances'], Response::HTTP_BAD_REQUEST);
        }

        // If there is not enough token balance, return an error
        if (!$hasEnoughTokens) {
            return response()->json(['status' => 'error', 'message' => 'Insufficient balance for the specified asset and amount'], Response::HTTP_BAD_REQUEST);
        }

        // Create the asset
        if (strlen($assetCode) <= 4) {
            $asset = new AssetTypeCreditAlphaNum4($assetCode, $issuer_id);
        } else {
            $asset = new AssetTypeCreditAlphanum12($assetCode, $issuer_id);
        }

        // Insert data into `claimable_balances` table
        $claimableBalanceId = DB::table('claimable_balances')->insertGetId([
            'distributor_wallet_key' => $user_distributor_wallet_address,
            'asset_code' => $assetCode,
            'amount' => $amount,
            'memo' => $memo ?? 'By TokenGlade',
            'status' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Build the transaction for each receiver and sign it separately
        $claimants = [];
        foreach ($receiver_addresses_array as $receiver) {
            $claimants[] = new Claimant($receiver, $ReceiverCanClaim); // Each receiver
            DB::table('claimable_balance_receivers')->insert([
                'claimable_balance_id' => $claimableBalanceId,
                'receiver_wallet_address' => $receiver,
                'status' => 1, // Assuming 1 means active
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $claimants[] = new Claimant($user_distributor_wallet_address, $DistributorCanReclaim);

        $claimableBalanceOperation = new CreateClaimableBalanceOperation($claimants, $asset, $amount);

        // Build the transaction with a single operation that has single or multiple claimants
        $transactionBuilder = (new TransactionBuilder($distributorAccount, $this->network))
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
                    'claimable_balance_id' => $claimableBalanceId,
                ],
            ],
            Response::HTTP_OK
        );
    }

    
    public function reclaim_claimable_balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'holdingTokenIssuerAddress' => 'required',
            'distributor_wallet_address' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $asset_code = $request->token;
        $issuer_wallet_address = $request->holdingTokenIssuerAddress;
        $distributor_wallet_address = $request->distributor_wallet_address;
        // $url = 'https://horizon-testnet.stellar.org/claimable_balances/?asset='
        //     . $asset_code . '%3A' . $issuer_wallet_address
        //     . '&claimant=' . $distributor_wallet_address
        //     . '&limit=200&order=desc';
        $url = 'https://horizon.stellar.org/claimable_balances/?asset='
            . $asset_code . '%3A' . $issuer_wallet_address
            . '&claimant=' . $distributor_wallet_address
            . '&limit=200&order=desc';

        // Fetch data from Horizon using file_get_contents or another method
        $response = file_get_contents($url);

        // Check if the response is valid
        if ($response === FALSE) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch data from Horizon.',
            ]);
        } else {
            $distributorAccount = $this->sdk->requestAccount($distributor_wallet_address);
            $response_data = json_decode($response, true); // Decode as associative array

            // Check for records and parse balance IDs
            if (isset($response_data['_embedded']['records']) && !empty($response_data['_embedded']['records'])) {
                $balance_ids = [];
                $wallets_claiming_times = [];
                $wallets_not_eligible = [];
                $eligible_wallet_ids = [];
                $eligible_balance_ids = [];
                $eligible_balance_id_amounts = [];
                $eligible_wallets_by_balance_id = []; 
            
                foreach ($response_data['_embedded']['records'] as $record) {
                    if (isset($record['id'])) {
                        $current_balance_id = $record['id'];
                        $balance_ids[] = $current_balance_id;
                    }
                    if (isset($record['amount'])) {
                        $current_amount = $record['amount'];
                    }
            
                    if (isset($record['claimants'])) {
                        $claimants = $record['claimants'];
                        $last_claimant_key = count($claimants) - 1;
                        $distributor_claiming_time = null;
                        $current_balance_wallet_ids = [];
            
                        foreach ($claimants as $key => $claimant) {
                            if ($key === $last_claimant_key) {
                                // Set distributor_claiming_time for this balance
                                if (isset($claimant['predicate']['not']['abs_before'])) {
                                    $distributor_claiming_time = $claimant['predicate']['not']['abs_before'];
                                }
                                continue; // Skip adding the last claimant's wallet
                            }
            
                            // Add the wallet ID if it's not the last one (i.e., non-distributor)
                            if (isset($claimant['destination'])) {
                                $current_balance_wallet_ids[] = $claimant['destination'];
                            }
            
                            // Access 'abs_before' and add to claiming times if needed
                            if (isset($claimant['predicate']['not']['abs_before'])) {
                                $wallets_claiming_times[] = $claimant['predicate']['not']['abs_before'];
                            }
                        }
            
                        // Check if the distributor can claim the tokens based on current time
                        $can_claim = true;
                        $claim_time  = true;
                        if ($distributor_claiming_time) {
                            $distributor_datetime = Carbon::parse($distributor_claiming_time, 'UTC');
                            $current_time = Carbon::now('UTC');
                            $claim_time = $distributor_datetime->format('Y-m-d H:i:s');
                            if ($current_time->lt($distributor_datetime)) {
                                $can_claim = false;
                            }
                        }
            
                        if ($can_claim) {
                            $eligible_wallet_ids = array_merge($eligible_wallet_ids, $current_balance_wallet_ids);
                            // $eligible_balance_ids = array_merge($eligible_balance_ids, [$current_balance_id]); // Wrap $current_balance_id in an array
                            $eligible_balance_ids[] = $current_balance_id; //$current_balance_id can be single or array thats why we aren't using array_merge
                            $eligible_balance_id_amounts[] = $current_amount; //$current_amount can be single or array
                            $eligible_wallets_by_balance_id[$current_balance_id] = $current_balance_wallet_ids;
                        } else {
                            // Otherwise, mark these wallets as not eligible
                            $wallets_not_eligible = array_merge($wallets_not_eligible, $current_balance_wallet_ids);
                        }
                    }
                }
                if (!empty($eligible_balance_ids) && !empty($eligible_wallets_by_balance_id)) {

                    // Initiate the transaction for eligible claimable balances
                    $claimClaimableBalanceTransaction = (new TransactionBuilder($distributorAccount, $this->network))
                        ->setMaxOperationFee($this->maxFee);
                
                    // Create a main entry for the claimable balance
                    $claim_claimable_balance = new ClaimClaimableBalance();
                    $claim_claimable_balance->distributor_wallet_key = $distributor_wallet_address;
                    $claim_claimable_balance->issuer_address = $issuer_wallet_address;
                    $claim_claimable_balance->asset_code = $asset_code;
                    $claim_claimable_balance->save(); // Save to get the ID
                
                    // Loop through each eligible balance ID and its wallet addresses
                    foreach ($eligible_wallets_by_balance_id as $balance_id => $wallet_addresses) {
                        
                        // Add an operation for each balance ID
                        $claim_claimable_balance_operation = new ClaimClaimableBalanceOperation($balance_id);
                        $claimClaimableBalanceTransaction->addOperation($claim_claimable_balance_operation);
                
                        // Save the balance ID and amount
                        $amount = $eligible_balance_id_amounts[array_search($balance_id, $eligible_balance_ids)]; // Ensure correct amount
                        $claim_claimable_balance_id = new ClaimClaimableBalanceId();
                        $claim_claimable_balance_id->claim_claimable_balance_id = $claim_claimable_balance->id;
                        $claim_claimable_balance_id->token_amount = $amount;
                        $claim_claimable_balance_id->balance_id = $balance_id;
                        $claim_claimable_balance_id->save();
                
                        // Loop through each wallet address for the current balance ID and save it
                        foreach ($wallet_addresses as $wallet_address) {
                            $claim_claimable_balance_claimant = new ClaimClaimableClaimant();
                            $claim_claimable_balance_claimant->claim_claimable_balance_id = $claim_claimable_balance_id->id;
                            $claim_claimable_balance_claimant->claimants_wallet_address = $wallet_address;
                            $claim_claimable_balance_claimant->save();
                        }
                    }

                    $builtTransaction = $claimClaimableBalanceTransaction->build();

                    return response()->json([
                        'status' => 'success',
                        'unsigned_transactions' => $builtTransaction->toEnvelopeXdrBase64(),
                        'asset_code' => $asset_code,
                        'claim_claimable_balance_id' => $eligible_balance_ids,
                        'wallet_ids' => $eligible_wallet_ids,
                        'wallets_not_eligible' => $wallets_not_eligible
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Reclaim not available for the asset: ".$asset_code.". The specified claim time has not yet passed. You can reclaim after ".$claim_time." UTC.",
                        'data' => $response_data
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No reclaimable balance found for ' .$asset_code,
                    'data' => $response_data
                ]);
            }
        }
    }

}

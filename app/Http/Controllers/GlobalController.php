<?php

namespace App\Http\Controllers;

use App\Models\Blockchain;
use App\Models\ClaimableBalance;
use App\Models\ClaimableBalanceReceiver;
use App\Models\StellarToken;
use App\Models\Token;
use App\Models\WalletType;
use Illuminate\Http\Request;

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

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
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

class GlobalController extends Controller
{
    private $sdk, $maxFee;

    public function __construct()
    {
        $this->sdk = StellarSDK::getPublicNetInstance();
        // $this->sdk = StellarSDK::getTestNetInstance();
        $this->maxFee = 30000;
    }
    //
    public function fetch_wallet_types()
    {
        $wallets = WalletType::where('status', 1) 
            ->select('id','name', 'key', 'blockchain_id')
            ->get();

        // Return the response
        return response()->json([
            'status' => 'success',
            'wallets' => $wallets
        ]);
    }

    public function fetch_blockchains()
    {
        $blockchains = Blockchain::orderBy('name')->get();

        // Return the response
        return response()->json([
            'status' => 'success',
            'blockchains' => $blockchains
        ]);
    }

    
    public function fetch_holding_tokens_total_xlm(Request $request)
    {
        $wallet_address = $request->json('wallet_key');

        // Continue only if wallet_address is not null
        if ($wallet_address !== null) {
            try {
                // Fetch details of the wallet from the public address
                $WalletAccount = $this->sdk->requestAccount($wallet_address);
                if(!$WalletAccount){
                    return response()->json(['status' => 'error', 'message' => 'Wallet is not active']);
                }
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

    public function fetch_wallet_tokens (){
        $get_wallets_tokens = Token::with(['stellarToken', 'blockchain'])
        ->limit(4)
        ->orderBy('id', 'desc')
        ->get();
        return response()->json([
            'status' => 'success',
            'tokens' => $get_wallets_tokens,
        ]);
    }

    public function count_data(){
        $total_tokens = StellarToken::count();
        $total_claimablebalance_users = ClaimableBalance::count();
        $total_claimablebalance = ClaimableBalanceReceiver::count();
        return response()->json([
            'status' => 'success',
            'total_tokens' => $total_tokens,
            'total_claimablebalance_users' => $total_claimablebalance_users,
            'total_claimablebalance' => $total_claimablebalance,
        ]);
    }
    
    // public function fetch_claimable_balance(Request $request)
    // {
    //     $wallet_address = $request->json('wallet_key');

    //     // Continue only if wallet_address is not null
    //     if ($wallet_address !== null) {
    //         try {
    //             $client = new \GuzzleHttp\Client();
    //             $response = $client->request('GET', 'https://horizon-testnet.stellar.org/accounts/'.$wallet_address.'/claimable_balances');
    //             dd($response);
    //             // Check status code
    //             $status_code = $response->getStatusCode();
    //             $data = json_decode($response->getBody(), true);

    //             if ($status_code == 200) {
    //                 dd($data); // Successfully fetched claimable balances
    //             } else {
    //                 dd('Error:', $status_code, $data); // Error in fetching data
    //             }
    //             // Fetch details of the wallet from the public address
    //             $WalletAccount = $this->sdk->requestAccount($wallet_address);
    //             $claimableBalance = $this->sdk->claimableBalances($wallet_address);
    //             dd($claimableBalance);
    //             // If there are claimable balances, process them
    //             if (count($claimableBalance) > 0) {
    //                 $claimable = []; // Initialize an array to hold claimable balances
                    
    //                 // Loop through claimable balances and extract necessary information
    //                 foreach ($claimableBalances as $balance) {
    //                     $claimable[] = [
    //                         'id' => $balance->getId(), // Claimable balance ID
    //                         'amount' => $balance->getAmount(), // Amount of the claimable balance
    //                         'asset_code' => $balance->getAssetCode(), // Asset code (this is the code you want)
    //                         'asset_issuer' => $balance->getAssetIssuer(), // Asset issuer
    //                         'claimants' => $balance->getClaimants() // List of claimants
    //                     ];
    //                 }
    //             }

    //             $tokens = []; // Initialize an array to hold non-native assets
    //             $totalXLM = 0;

    //             // Loop through the balances and fetch non-native assets
    //             foreach ($WalletAccount->getBalances() as $balance) {
    //                 if ($balance->getAssetType() === 'native') {
    //                     // Store the XLM balance if the asset type is 'native'
    //                     $totalXLM = $balance->getBalance();
    //                 } else {
    //                     // Store non-native assets
    //                     $tokens[] = [
    //                         'code' => $balance->getAssetCode(), // Asset code
    //                         'issuer' => $balance->getAssetIssuer(), // Asset issuer
    //                         'balance' => $balance->getBalance(), // Asset balance
    //                     ];
    //                 }
    //             }

    //             if (count($tokens) > 0) {
    //                 return response()->json([
    //                     'status' => 'success',
    //                     'tokens' => $tokens,
    //                     'total_xlm' => $totalXLM, // Include the total XLM balance in the response
    //                 ]);
    //             } else {
    //                 return response()->json(['status' => 'error', 'message' => 'No Claimable Balance in Connected Wallet']);
    //             }
    //         } catch (\InvalidArgumentException $e) {
    //             return response()->json(['status' => 'error', 'message' => 'Invalid Wallet Address']);
    //         } catch (\Exception $e) {
    //             return response()->json(['status' => 'error', 'message' => 'Wallet is not active']);
    //         }
    //     } else {
    //         return response()->json(['status' => 'error', 'message' => 'Wallet address is required']);
    //     }
    // }
}

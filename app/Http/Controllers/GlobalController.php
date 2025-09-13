<?php

namespace App\Http\Controllers;

use App\Models\Blockchain;
use App\Models\ClaimableBalance;
use App\Models\ClaimableBalanceReceiver;
use App\Models\StellarToken;
use App\Models\Token;
use App\Models\WalletType;
use App\Services\WalletService;
use Illuminate\Http\Request;

use Exception;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Network;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;

class GlobalController extends Controller
{
    private $sdk, $assetIssuer, $stakingRewardWalletKey, $stakingPublicWalletKey, $network;
    private WalletService $wallet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet; 
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT');

        if ($stellarEnv === 'public') {
            $this->sdk = StellarSDK::getPublicNetInstance();
            $this->stakingRewardWalletKey = env('STAKING_REWARD_WALLET_KEY');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY');
            $this->assetIssuer =  env('TKG_ISSUER_PUBLIC');
            $this->network = Network::public();
        } else {
            $this->sdk = StellarSDK::getTestNetInstance();
            $this->stakingRewardWalletKey = env('STAKING_REWARD_WALLET_KEY_TESTNET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY_TESTNET');
            $this->assetIssuer =  env('TKG_ISSUER_TESTNET');
            $this->network = Network::testnet();
        }
    }

    public function check_xlm_balance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_wallet' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $balance = $this->wallet->getXlmBalance($request->public_wallet);

        return response()->json([
            'status'    => 1,
            'total_xlm' => (float) $balance,
        ]);
    }

    public function check_tkg_balance(Request $request)
    {
        try {
            $data = $request->validate([
                'public_wallet' => ['required','string'],
                'min' => ['nullable','numeric'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Validation error',
                'errors'  => $e->errors(),
            ], 422);
        }
        
        try {
            $min = $data['min'] ?? null;
            $result = $this->wallet->getTkgBalance($data['public_wallet'], $min);

            // If "min" provided, service returns bool. Otherwise it returns float balance.
            if ($min !== null) {
                return response()->json([
                    'status' => 1,
                    'hasMin' => (bool) $result,
                    'min'    => (float) $min,
                ]);
            }

            return response()->json([
                'status'    => 1,
                'balance' => (float) $result,
            ]);

        } catch (HorizonRequestException $e) {
            if ($e->getStatusCode() === 404) {
                // account not funded / not found
                return response()->json([
                    'status'      => 1,
                    'balance' => 0.0,
                ]);
            }
            return response()->json([
                'status'  => 0,
                'message' => 'Horizon error',
                'code'    => $e->getStatusCode(),
            ], 502);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 0,
                'message' => 'Unexpected error',
            ], 500);
        }
    }


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

    public function fetch_generated_tokens (){
        $get_wallets_tokens = Token::with(['stellarToken', 'blockchain'])
            ->whereHas('stellarToken', function ($q) {
                $q->whereNotNull('issuer_public_key')
                    ->where('issuer_public_key', '!=', '');
            })
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

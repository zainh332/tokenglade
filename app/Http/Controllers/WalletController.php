<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Exception;

use Soneso\StellarSDK\StellarSDK;

class WalletController extends Controller
{
    private $publicSDK, $testSDK, $maxFee;

    public function __construct()
    {
        $this->publicSDK = StellarSDK::getPublicNetInstance();
        $this->maxFee = 3000;
    }

    //Storing wallets connecting from extensions like rabet etc
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'public_key' => 'required|string',
            'wallet_type_id' => 'required|integer'
        ]);

        if (empty($request->public_key) || empty($request->wallet_type_id)) {
            return response()->json(['status' => 'error', 'message' => 'Public Key or Wallet Type is missing']);
        }

        $wallet = User::updateOrCreate(
            ['public_key' => $request->public_key],  // Condition for finding an existing record
            ['wallet_type_id' => $request->wallet_type_id]  // Fields to update or insert
        );

        // Create a new token for the user
        $token = $wallet->createToken('tokenglade')->plainTextToken;

        
        setcookie('public_key', $request->public_key, time() + (86400 * 30), "/");
        setcookie('wallet_type_id', $request->wallet_type_id, time() + (86400 * 30), "/");
        setcookie('accessToken', $token, time() + (86400 * 30), "/");
        
        return response()->json([
            'status' => 'success',
            'public' => $request->public_key,
            'token' => $token
        ]);
    }





















    
    // Check Stellar Account
    // try {
    //     $account = $this->publicSDK->requestAccount($request->public);
    // } catch (Exception $th) {
    //     return response()->json(['status' => 0, 'msg' => 'Deposit 5 XLM lumens into your wallet!']);
    // }
    
    
    // foreach ($account->getBalances() as $balance) {
    //     // Fetch XLM (native) balance of the wallet
    //     if ($balance->getAssetType() === 'native') {
    //         // Checking if the XLM balance is less than 5 
    //         if ($balance->getBalance() < 5) {
    //             return response()->json(['status' => 'error', 'msg' => 'Account does not have enough XLM. Please deposit at least 5 XLM']);
    //         }
    //     }
    // }
    
    // $data = [
    //     'public' => $request->public,
    //     'wallet' => $request->wallet
    // ];
    
    // Store Stellar Account if not exist
    // $wallet = Wallet::where('public', $request->public)->first();
    // if (!$wallet) {
    //     Wallet::create($data);
    // } else {
    //     $wallet->update($data);
    // }
















}

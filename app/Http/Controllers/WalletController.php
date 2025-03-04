<?php

namespace App\Http\Controllers;

use App\Models\StellarToken;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use Soneso\StellarSDK\StellarSDK;

class WalletController extends Controller
{

    public function __construct()
    {
        //
    }

    //Storing wallets connecting from extensions like rabet etc
    public function store_wallet(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'public_key' => 'required|string',
            'wallet_type_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        if (empty($request->public_key) || empty($request->wallet_type_id)) {
            return response()->json(['status' => 'error', 'message' => 'Public Key or Wallet Type is missing']);
        }

        // Update or create the wallet with the status set to 1
        $wallet = User::updateOrCreate(
            ['public_key' => $request->public_key],  // Condition for finding an existing record
            [
                'wallet_type_id' => $request->wallet_type_id, // Fields to update or insert
                'status' => 1
            ]
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

    //Updating wallets connecting from frieghter
    public function update_wallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'previous_public_key' => 'required|string',
            'current_public_key' => 'required|string',
            'wallet_type_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $previous_wallet = User::where('public_key', $request->previous_public_key)->where('status', 1)->first();
        if($previous_wallet){
            $previous_wallet->status = 0;
            $previous_wallet->save();
        }
        else{
            return response()->json(['status' => 'error', 'message' => 'Previous Public Key is missing']);
        }

        $wallet = User::updateOrCreate(
            ['public_key' => $request->current_public_key],  // Condition for finding an existing record
            [
                'wallet_type_id' => $request->wallet_type_id, // Fields to update or insert
                'status' => 1
            ]
        );

        // Create a new token for the user
        $token = $wallet->createToken('tokenglade')->plainTextToken;

        setcookie('public_key', $request->current_public_key, time() + (86400 * 30), "/");
        setcookie('wallet_type_id', $request->wallet_type_id, time() + (86400 * 30), "/");
        setcookie('accessToken', $token, time() + (86400 * 30), "/");

        return response()->json([
            'status' => 'success',
            'public' => $request->current_public_key,
            'token' => $token
        ]);
    }


    public function disconnect_wallet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'public_key' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $public_wallet = $request->public_key;
        $wallet = User::where('public_key', $public_wallet)->where('status', 1)->first();
        if ($wallet) {
            $wallet->status = 0; //wallet disconnected
            $wallet->save();
            return response()->json([
                'status' => 'success',
            ]);
        }
        else{
            return response()->json(['status' => 'error', 'message' => 'Wallet not found']);
        }
    }











}

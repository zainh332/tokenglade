<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Soneso\StellarSDK\Crypto\KeyPair;
use Soneso\StellarSDK\Soroban\Responses\GetHealthResponse;
use Soneso\StellarSDK\Soroban\SorobanServer;
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Util\FuturenetFriendBot;

class SorobanController extends Controller
{
    public function createSmartContract(Request $request)
    {
        // Provide the url to the endpoint of the Soroban-RPC server to connect to:
        $server = new SorobanServer("https://rpc-futurenet.stellar.org:443");


        // Set the experimental flag to true. Otherwise it will not work
        $server->acknowledgeExperimental = true;

        // General node health check
        $healthResponse = $server->getHealth();

        if (GetHealthResponse::HEALTHY == $healthResponse->status) {
        //...
        }

        // You first need an account on Futurenet.
        // $accountKeyPair = KeyPair::random();
        $accountKeyPair = KeyPair::fromSeed($request->private_key);
        $accountId = $accountKeyPair->getAccountId();

        //funding
        FuturenetFriendBot::fundTestAccount($accountId);

        // Next you can fetch current information about your Stellar account using the SDK:
        $sdk = StellarSDK::getFutureNetInstance();
        $accountResponse = $sdk->requestAccount($accountId);
        // print("Sequence: ".$getAccountResponse->getSequenceNumber());
    }
}

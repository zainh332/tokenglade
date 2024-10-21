<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletAuthController extends Controller
{
    public function Wallet_Register(Request $request) {
        
    }

    public function Wallet_Connected(Request $request) {
        
        $validator = Validator::make($request->all(),[
            
        ]);
    }
}

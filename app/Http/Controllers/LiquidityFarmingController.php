<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\Request;
use Soneso\StellarSDK\Network;
use Soneso\StellarSDK\StellarSDK;

class LiquidityFarmingController extends Controller
{
    private $sdk, $network, $token_creation_fee;
    private $feePercentageForLP, $xlm_funding_wallet, $xlm_funding_wallet_key, $issuer_wallet_amount, $stakingPublicWallet, $stakingPublicWalletKey, $tkgIssuer, $assetCode;
    private WalletService $wallet;
    private bool $isTestnet;

    public function __construct(WalletService $wallet)
    {
        $this->wallet = $wallet;
        $stellarEnv = env('VITE_STELLAR_ENVIRONMENT');
        $this->isTestnet = strtolower($stellarEnv) !== 'public';

        if ($stellarEnv === 'public') {
            $this->sdk = StellarSDK::getPublicNetInstance();
            $this->xlm_funding_wallet = env('XLM_FUNDING_WALLET');
            $this->xlm_funding_wallet_key = env('XLM_FUNDING_WALLET_KEY');
            $this->stakingPublicWallet = env('STAKING_PUBLIC_WALLET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY');
            $this->tkgIssuer = env('TKG_ISSUER_PUBLIC');
            $this->network = Network::public();
        } else {
            $this->sdk = StellarSDK::getTestNetInstance();
            $this->network = Network::testnet();
            $this->xlm_funding_wallet = env('XLM_FUNDING_WALLET_TESTNET');
            $this->xlm_funding_wallet_key = env('XLM_FUNDING_WALLET_KEY_TESTNET');
            $this->stakingPublicWallet = env('STAKING_PUBLIC_WALLET_TESTNET');
            $this->stakingPublicWalletKey = env('STAKING_PUBLIC_WALLET_KEY_TESTNET');
            $this->tkgIssuer = env('TKG_ISSUER_TESTNET');
        }

        $this->assetCode = env('ASSET_CODE');
        $this->token_creation_fee = 15; //XLM
        $this->issuer_wallet_amount = 4; //XLM
        $this->feePercentageForLP = 0.7;
    }
}

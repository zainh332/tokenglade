<?php

namespace App\Services;

use Soneso\StellarSDK\Exceptions\HorizonRequestException;
use Soneso\StellarSDK\StellarSDK;

class WalletService
{
    protected StellarSDK $sdk;
    protected string $env;
    protected ?string $tkgIssuer;

    public function __construct()
    {
        $this->env = env('VITE_STELLAR_ENVIRONMENT', 'public');

        $this->sdk = $this->env === 'public'
            ? StellarSDK::getPublicNetInstance()
            : StellarSDK::getTestNetInstance();

        $this->tkgIssuer = $this->env === 'public'
            ? env('TKG_ISSUER_PUBLIC')
            : env('TKG_ISSUER_TESTNET');
    }

    /** Get native XLM balance */
    public function getXlmBalance(string $publicKey): float
    {
        try {
            $account = $this->sdk->requestAccount($publicKey);

            foreach ($account->getBalances() as $bal) {
                if ($bal->getAssetType() === 'native') {
                    return (float) $bal->getBalance();
                }
            }
            return 0.0;
        } catch (HorizonRequestException $e) {
            return $e->getStatusCode() == 404 ? 0.0 : throw $e;
        }
    }

    /** Get TKG balance or check if >= amount */
    public function getTkgBalance(string $publicKey, ?float $minAmount = null): float|bool
    {
        if (!$this->tkgIssuer) {
            return $minAmount === null ? 0.0 : false;
        }

        try {
            $account = $this->sdk->requestAccount($publicKey);

            foreach ($account->getBalances() as $bal) {
                if (
                    $bal->getAssetType() === 'credit_alphanum4' &&
                    $bal->getAssetCode() === 'TKG' &&
                    $bal->getAssetIssuer() === $this->tkgIssuer
                ) {
                    $balance = (float) $bal->getBalance();
                    return $minAmount === null ? $balance : $balance >= $minAmount;
                }
            }
            return $minAmount === null ? 0.0 : false;
        } catch (HorizonRequestException $e) {
            return $e->getStatusCode() == 404 ? ($minAmount === null ? 0.0 : false) : throw $e;
        }
    }
}

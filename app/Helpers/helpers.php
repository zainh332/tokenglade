<?php
use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;


/**
 * Check the XLM balance of a Stellar wallet
 */
function checkXlmBalance(string $publicKey): ?float
{
    try {
        $sdk = StellarSDK::getPublicNetInstance();
        $account = $sdk->requestAccount($publicKey);

        foreach ($account->getBalances() as $balance) {
            if ($balance->getAssetType() === 'native') {
                return (float) $balance->getBalance();
            }
        }

        return 0.0; // If no native balance found, return 0

    } catch (HorizonRequestException $e) {
        if ($e->getStatusCode() == 404) {
            return false;
        }
        throw $e;
    }
    catch (\Exception $e) {
        return null; 
    }
}
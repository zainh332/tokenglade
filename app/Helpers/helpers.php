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

function checkTkgBalance(string $publicKey): ?float
{
    $tkgIssuer = 'GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ';

    try {
        $sdk = StellarSDK::getPublicNetInstance();
        $account = $sdk->requestAccount($publicKey);

        foreach ($account->getBalances() as $balance) {
            if ($balance->getAssetType() === 'credit_alphanum4' && 
                $balance->getAssetCode() === 'TKG' && 
                $balance->getAssetIssuer() === $tkgIssuer) 
            {
                return (float) $balance->getBalance();
            }
        }

        return 0.0; // If no matching TKG balance found, return 0

    } catch (HorizonRequestException $e) {
        if ($e->getStatusCode() == 404) {
            return false; // Account does not exist
        }
        throw $e;
    } catch (\Exception $e) {
        return null; // Handle other exceptions gracefully
    }
}

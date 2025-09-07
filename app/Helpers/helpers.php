<?php

use Soneso\StellarSDK\StellarSDK;
use Soneso\StellarSDK\Exceptions\HorizonRequestException;


/**
 * Check the XLM balance of a Stellar wallet
 */
function checkXlmBalance(string $publicKey): ?float
{
    try {
        $stellarEnv = env('STELLAR_ENVIRONMENT');

        if ($stellarEnv === 'public') {
            $sdk = StellarSDK::getPublicNetInstance();
        } else {
            $sdk = StellarSDK::getTestNetInstance();
        }
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
    } catch (\Exception $e) {
        return null;
    }
}

function checkTkgBalance(string $publicKey, ?float $amount = null): float|bool|null
{
    // --- Resolve environment + issuer (fail early if not configured) ---
    $stellarEnv = env('STELLAR_ENVIRONMENT'); // 'public' or 'testnet'
    $tkgIssuer  = $stellarEnv === 'public'
        ? env('TKG_ISSUER_PUBLIC')
        : env('TKG_ISSUER_TESTNET');

    if (!$tkgIssuer) {
        return null;
    }

    // --- Pick SDK by env ---
    $sdk = ($stellarEnv === 'public')
        ? StellarSDK::getPublicNetInstance()
        : StellarSDK::getTestNetInstance();

    try {
        // --- Fetch account (throws on 404) ---
        $account = $sdk->requestAccount($publicKey);

        foreach ($account->getBalances() as $balance) {
            if (
                $balance->getAssetType() === 'credit_alphanum4' &&
                $balance->getAssetCode() === 'TKG' &&
                $balance->getAssetIssuer() === $tkgIssuer
            ) {
                $bal = (float) $balance->getBalance();

                // If amount is given, return comparison instead of balance
                if ($amount !== null) {
                    return $bal >= $amount;
                }

                return $bal;
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

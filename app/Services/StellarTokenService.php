<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class StellarTokenService
{
    protected string $horizon = 'https://horizon.stellar.org';

    public function getTokenInsight(string $issuer): array
    {
        // Validate format
        if (!$this->isValidStellarAddress($issuer)) {
            throw new \Exception('Invalid Stellar address format.');
        }

        // Asset Info
        $accountResponse = Http::get($this->horizon . "/accounts/{$issuer}");

        if (!$accountResponse->ok()) {
            throw new \Exception('Account not found on Stellar network.');
        }

        // Fetch assets issued by this account
        $assetsResponse = Http::get($this->horizon . '/assets', [
            'asset_issuer' => $issuer,
            'limit' => 200
        ]);

        if (!$assetsResponse->ok()) {
            throw new \Exception('Failed to fetch assets.');
        }

        $asset = $assetsResponse['_embedded']['records'][0];

        $totalSupply  = $asset['amount'];
        $holderCount  = $asset['accounts'];

        // Issuer Info
        $issuerResponse = Http::get($this->horizon . "/accounts/{$issuer}");

        if (!$issuerResponse->ok()) {
            throw new \Exception('Issuer not found.');
        }

        $issuerData = $issuerResponse->json();

        $isImmutable = $issuerData['flags']['auth_immutable'] ?? false;
        $mintPossible = !$isImmutable;

        // Trustlines (Holder Distribution)
        $trustlines = $this->fetchAllTrustlines($code, $issuer);

        $holders = collect($trustlines)
            ->filter(fn($line) => $line['balance'] > 0)
            ->map(fn($line) => [
                'account' => $line['account_id'],
                'balance' => (float) $line['balance'],
            ])
            ->sortByDesc('balance')
            ->values();

        $largestHolder = $holders->first();

        $top10 = $holders->take(10);

        $top10Percentage = $top10->sum('balance') / (float) $totalSupply * 100;

        // Recent Activity
        $paymentsResponse = Http::get($this->horizon . '/payments', [
            'asset_code'   => $code,
            'asset_issuer' => $issuer,
            'limit'        => 10,
            'order'        => 'desc',
        ]);

        $recentTransfers = $paymentsResponse->ok()
            ? $paymentsResponse['_embedded']['records']
            : [];

        return [
            'asset_code'        => $code,
            'issuer'            => $issuer,
            'total_supply'      => $totalSupply,
            'holder_count'      => $holderCount,
            'issuer_locked'     => $isImmutable,
            'minting_possible'  => $mintPossible,
            'largest_holder'    => $largestHolder,
            'top10_percentage'  => round($top10Percentage, 2),
            'recent_transfers'  => $recentTransfers,
            'holders'           => $top10,
        ];
    }

    protected function fetchAllTrustlines(string $code, string $issuer): array
    {
        $records = [];
        $url = $this->horizon . "/accounts?asset={$code}:{$issuer}&limit=200";

        while ($url) {
            $response = Http::get($url);

            if (!$response->ok()) {
                break;
            }

            $data = $response->json();

            $records = array_merge($records, $data['_embedded']['records']);

            $url = $data['_links']['next']['href'] ?? null;

            // Optional: stop after certain limit for MVP
            if (count($records) > 2000) {
                break;
            }
        }

        return $records;
    }

    // public function getIssuerTokens(string $issuer): array
    // {
    //     // 1️⃣ Validate format
    //     if (!$this->isValidStellarAddress($issuer)) {
    //         throw new \Exception('Invalid Stellar address format.');
    //     }

    //     // 2️⃣ Check account exists
    //     $accountResponse = Http::get($this->horizon . "/accounts/{$issuer}");

    //     if (!$accountResponse->ok()) {
    //         throw new \Exception('Account not found on Stellar network.');
    //     }

    //     // 3️⃣ Fetch assets issued by this account
    //     $assetsResponse = Http::get($this->horizon . '/assets', [
    //         'asset_issuer' => $issuer,
    //         'limit' => 200
    //     ]);

    //     if (!$assetsResponse->ok()) {
    //         throw new \Exception('Failed to fetch assets.');
    //     }

    //     $assets = $assetsResponse['_embedded']['records'] ?? [];

    //     if (empty($assets)) {
    //         return [
    //             'is_issuer' => false,
    //             'message' => 'This address has not issued any tokens.',
    //             'tokens' => []
    //         ];
    //     }

    //     // 4️⃣ Process each issued token
    //     $tokens = collect($assets)->map(function ($asset) {

    //         return [
    //             'asset_code'   => $asset['asset_code'],
    //             'total_supply' => $asset['amount'],
    //             'holders'      => $asset['accounts'],
    //         ];
    //     });

    //     return [
    //         'is_issuer' => true,
    //         'issuer'    => $issuer,
    //         'tokens'    => $tokens
    //     ];
    // }

    protected function isValidStellarAddress(string $address): bool
    {
        return preg_match('/^G[A-Z2-7]{55}$/', $address) === 1;
    }
}

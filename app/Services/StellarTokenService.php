<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

class StellarTokenService
{
    protected string $horizon = 'https://horizon.stellar.org';

    public function getTokenInsight(string $issuer, string $code): array
    {
        if (!$this->isValidStellarAddress($issuer)) {
            throw new \Exception('Invalid Stellar address format.');
        }

        // Fetch assets issued by this account
        $assetsResponse = Http::get($this->horizon . '/assets', [
            'asset_issuer' => $issuer,
            'asset_code' => $code,
            'limit' => 1
        ]);

        if (!$assetsResponse->ok()) {
            throw new \Exception('Failed to fetch asset.');
        }

        $asset = $assetsResponse->json('_embedded.records.0');

        if (!$asset) {
            throw new \Exception('Asset not found.');
        }

        $totalSupply = (float) ($asset['balances']['authorized'] ?? 0);
        $holderCount = (int) ($asset['accounts']['authorized'] ?? 0);

        // Issuer Info
        $issuerResponse = Http::get($this->horizon . "/accounts/{$issuer}");

        if (!$issuerResponse->ok()) {
            throw new \Exception('Issuer not found.');
        }

        $issuerData = $issuerResponse->json();

        $isImmutable = $issuerData['flags']['auth_immutable'] ?? false;
        $mintPossible = !$isImmutable;

        // Trustlines (Holder Distribution)
        $topHolders = $this->fetchTopHolders($code, $issuer);

        $holders = collect($topHolders)
            ->filter(
                fn($line) =>
                isset($line['balance']) &&
                    (float)$line['balance'] > 0
            )
            ->sortByDesc('balance')
            ->values();

        $largestHolder = $holders->first();

        $top10 = $holders->take(10);

        $totalSupply = (float) $totalSupply;

        $top10Percentage = $totalSupply > 0
            ? ($top10->sum('balance') / $totalSupply) * 100
            : 0;

        // Recent Activity
        $paymentsResponse = Http::get($this->horizon . '/payments', [
            'asset_code'   => $code,
            'asset_issuer' => $issuer,
            'limit'        => 10,
            'order'        => 'desc',
        ]);

        $recentTransfers = $paymentsResponse->ok()
            ? ($paymentsResponse->json('_embedded.records') ?? [])
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

    protected function fetchTopHolders(string $code, string $issuer): array
    {
        $url = $this->horizon . "/accounts?asset={$code}:{$issuer}&limit=200";

        $response = Http::get($url);

        if (!$response->ok()) {
            return [];
        }

        $records = $response->json('_embedded.records') ?? [];

        return collect($records)
            ->map(function ($account) use ($code, $issuer) {

                $balance = collect($account['balances'])
                    ->first(function ($b) use ($code, $issuer) {
                        return ($b['asset_code'] ?? null) === $code
                            && ($b['asset_issuer'] ?? null) === $issuer;
                    });

                return [
                    'account' => $account['account_id'],
                    'balance' => (float) ($balance['balance'] ?? 0),
                ];
            })
            ->filter(fn($h) => $h['balance'] > 0)
            ->sortByDesc('balance')
            ->values()
            ->take(10)
            ->toArray();
    }

    protected function isValidStellarAddress(string $address): bool
    {
        return preg_match('/^G[A-Z2-7]{55}$/', $address) === 1;
    }

    public function getAssetsByIssuer(string $issuer): array
    {
        if (!$this->isValidStellarAddress($issuer)) {
            throw new \Exception('Invalid Stellar address format.');
        }

        $response = Http::get($this->horizon . '/assets', [
            'asset_issuer' => $issuer,
            'limit' => 200
        ]);

        if (!$response->ok()) {
            throw new \Exception('Failed to fetch assets.');
        }

        $records = $response->json('_embedded.records') ?? [];

        if (empty($records)) {
            return [];
        }

        // SMART SELECTION
        usort($records, function ($a, $b) {
            return ($b['accounts']['authorized'] ?? 0)
                <=> ($a['accounts']['authorized'] ?? 0);
        });

        return $records;
    }
}

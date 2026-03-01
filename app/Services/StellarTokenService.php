<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;
use Yosymfony\Toml\Toml;

class StellarTokenService
{
    protected string $horizon = 'https://horizon.stellar.org';

    public function getTokenInsight(string $issuer, string $code): array
    {
        if (!$this->isValidStellarAddress($issuer)) {
            throw new \Exception('Invalid Stellar address format.');
        }

        $assetResponse = Http::get($this->horizon . '/assets', [
            'asset_issuer' => $issuer,
            'asset_code' => $code,
            'limit' => 1
        ]);

        if (!$assetResponse->ok()) {
            throw new \Exception('Asset not found.');
        }

        $asset = $assetResponse->json('_embedded.records.0');
        $meta = $this->fetchTomlMetadata($asset);

        if (!$asset) {
            throw new \Exception('Asset not found.');
        }

        $issuerResponse = Http::get($this->horizon . "/accounts/{$issuer}");

        if (!$issuerResponse->ok()) {
            throw new \Exception('Issuer not found.');
        }

        $issuerData = $issuerResponse->json();

        return [
            'asset_code'       => $code,
            'issuer'           => $issuer,

            'name'             => $meta['token']['name'] ?? $code,
            'image'            => $meta['token']['image'] ?? null,
            'description'      => $meta['token']['description'] ?? null,
            'decimals'         => $meta['token']['decimals'] ?? 7,

            'project'          => $meta['project'] ?? [],

            'total_supply'     => (float) ($asset['balances']['authorized'] ?? 0),
            'holder_count'     => (int) ($asset['accounts']['authorized'] ?? 0),

            'issuer_locked'    => $issuerData['flags']['auth_immutable'] ?? false,
            'minting_possible' => !($issuerData['flags']['auth_immutable'] ?? false),
        ];
    }

    public function getHolderAnalytics(string $issuer, string $code): array
    {
        $topHolders = $this->fetchTopHolders($code, $issuer);

        $holders = collect($topHolders)
            ->filter(fn($h) => $h['balance'] > 0)
            ->sortByDesc('balance')
            ->values();

        $largestHolder = $holders->first();

        $top10 = $holders->take(10);

        $assetResponse = Http::get($this->horizon . '/assets', [
            'asset_issuer' => $issuer,
            'asset_code' => $code,
            'limit' => 1
        ]);

        $asset = $assetResponse->json('_embedded.records.0');

        $totalSupply = (float) ($asset['balances']['authorized'] ?? 0);

        $top10Percentage = $totalSupply > 0
            ? ($top10->sum('balance') / $totalSupply) * 100
            : 0;

        return [
            'largest_holder'   => $largestHolder,
            'top10_percentage' => round($top10Percentage, 2),
            'holders'          => $top10,
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

    protected function fetchTomlMetadata(array $asset): array
    {
        $tomlUrl = $asset['_links']['toml']['href'] ?? null;

        if (!$tomlUrl) {
            return [];
        }

        $response = Http::timeout(6)->get($tomlUrl);

        if (!$response->ok()) {
            return [];
        }

        try {
            $parsed = Toml::parse($response->body());
        } catch (\Exception $e) {
            return [];
        }

        $documentation = $parsed['DOCUMENTATION'] ?? [];

        $project = [
            'org_name'        => $documentation['ORG_NAME'] ?? null,
            'org_dba'         => $documentation['ORG_DBA'] ?? null,
            'org_url'         => $documentation['ORG_URL'] ?? null,
            'org_logo'        => $documentation['ORG_LOGO'] ?? null,
            'org_description' => $documentation['ORG_DESCRIPTION'] ?? null,
            'org_twitter'     => $documentation['ORG_TWITTER'] ?? null,
            'org_email'       => $documentation['ORG_OFFICIAL_EMAIL'] ?? null,
            'org_support'     => $documentation['ORG_SUPPORT_EMAIL'] ?? null,
        ];

        $token = [];

        foreach (($parsed['CURRENCIES'] ?? []) as $currency) {

            if (
                ($currency['code'] ?? null) === $asset['asset_code'] &&
                ($currency['issuer'] ?? null) === $asset['asset_issuer']
            ) {
                $token = [
                    'name'        => $currency['name'] ?? $asset['asset_code'],
                    'image'       => $currency['image'] ?? null,
                    'description' => $currency['desc'] ?? null,
                    'decimals'    => $currency['display_decimals'] ?? 7,
                    'conditions'  => $currency['conditions'] ?? null,
                ];

                break;
            }
        }

        return [
            'project' => $project,
            'token'   => $token,
        ];
    }
}

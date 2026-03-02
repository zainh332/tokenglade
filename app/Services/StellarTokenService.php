<?php

namespace App\Services;

use Carbon\Carbon;
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

        $url = "https://api.stellar.expert/explorer/public/asset/{$code}-{$issuer}";

        $response = Http::timeout(8)->get($url);

        if (!$response->ok()) {
            throw new \Exception('response not found.');
        }

        $asset = $assetResponse->json('_embedded.records.0');
        $mintDateRaw = $response->json('created');
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

            'name'             => $meta['project']['org_name'] ?? $code,
            'image'            => $meta['token']['image'] ?? null,
            'description'      => $meta['token']['description'] ?? null,
            'decimals'         => $meta['token']['decimals'] ?? 7,

            'project'          => $meta['project'] ?? [],

            'total_supply'     => (float) ($asset['balances']['authorized'] ?? 0),
            'holders'     => (int) ($asset['accounts']['authorized'] ?? 0),

            'issuer_locked'    => $issuerData['flags']['auth_immutable'] ?? false,
            'minting_possible' => !($issuerData['flags']['auth_immutable'] ?? false),
            'mint_date_human' => Carbon::createFromTimestampUTC($mintDateRaw)->format('Y-m-d'),
            'liquidity_pools'     => (float) ($asset['num_liquidity_pools'] ?? 0),
            'updated_at'     => '1 min ago',
            'website'             => $meta['project']['org_url'] ?? $code,
            'twitter'             => $meta['project']['org_twitter'] ?? $code,
            'email'             => $meta['project']['org_email'] ?? $code,
            'support_email'             => $meta['project']['org_support'] ?? $code,
        ];
    }

    // public function getHolderAnalytics(string $issuer, string $code): array
    // {
    //     $topHolders = collect(
    //         $this->fetchTopHolders($code, $issuer)
    //     );

    //     if ($topHolders->isEmpty()) {
    //         return [
    //             'largest_holder'   => null,
    //             'top10_percentage' => 0,
    //             'top10_holders'    => [],
    //         ];
    //     }

    //     $largestHolder = $topHolders->first();

    //     $top10 = $topHolders->take(10)->values();
    //     $assetResponse = Http::get($this->horizon . '/assets', [
    //         'asset_issuer' => $issuer,
    //         'asset_code' => $code,
    //         'limit' => 1
    //     ]);

    //     $asset = $assetResponse->json('_embedded.records.0');

    //     $totalSupply = (float) ($asset['balances']['authorized'] ?? 0);

    //     $top10Percentage = $totalSupply > 0
    //         ? ($top10->sum('balance') / $totalSupply) * 100
    //         : 0;

    //     return [
    //         'largest_holder'   => $largestHolder,
    //         'top10_percentage' => round($top10Percentage, 2),
    //         'top10_holders'    => $top10,
    //     ];
    // }

    // protected function fetchTopHolders(string $code, string $issuer): array
    // {
    //     $asset = "{$code}-{$issuer}";

    //     $url = "https://api.stellar.expert/explorer/public/asset/{$asset}/holders";

    //     $response = Http::timeout(10)->get($url, [
    //         'limit' => 10,
    //         'order' => 'desc'
    //     ]);

    //     if (!$response->ok()) {
    //         return [];
    //     }

    //     $records = $response->json('_embedded.records') ?? [];

    //     return collect($records)
    //         ->map(fn($r) => [
    //             'account' => $r['account'],
    //             'balance' => (float) $r['balance'],
    //         ])
    //         ->values()
    //         ->toArray();
    // }

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

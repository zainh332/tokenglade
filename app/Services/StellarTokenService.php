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

        $horizonResponse = Http::get($this->horizon . '/assets', [
            'asset_issuer' => $issuer,
            'asset_code' => $code,
            'limit' => 1
        ]);

        if (!$horizonResponse->ok()) {
            throw new \Exception('Asset not found.');
        }

        $url = "https://api.stellar.expert/explorer/public/asset/{$code}-{$issuer}";

        $response = Http::timeout(8)->get($url);

        if (!$response->ok()) {
            throw new \Exception('response not found.');
        }

        $horizon = $horizonResponse->json('_embedded.records.0');
        $mintDateRaw = $response->json('created');
        $toml = $this->fetchTomlMetadata($horizon);

        if (!$horizon) {
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

            'name'             => $toml['project']['org_name'] ?? $code,
            'image'            => $toml['token']['image'] ?? null,
            'description'      => $toml['token']['description'] ?? null,
            'decimals'         => $toml['token']['decimals'] ?? 7,

            'project'          => $toml['project'] ?? [],

            'total_supply'     => (float) ($horizon['balances']['authorized'] ?? 0),
            'holders'     => (int) ($horizon['accounts']['authorized'] ?? 0),

            'issuer_locked'    => $issuerData['flags']['auth_immutable'] ?? false,
            'minting_possible' => !($issuerData['flags']['auth_immutable'] ?? false),
            'mint_date_human' => Carbon::createFromTimestampUTC($mintDateRaw)->format('Y-m-d'),
            'liquidity_pools'     => (float) ($horizon['num_liquidity_pools'] ?? 0),
            'updated_at'     => '1 min ago',
            'website'             => $toml['project']['org_url'] ?? $code,
            'twitter'             => $this->normalizeUrl($toml['project']['org_twitter'] ?? null),
            'email'             => $toml['project']['org_email'] ?? $code,
            'support_email'             => $toml['project']['org_support'] ?? $code,

            'auth_required'     => ($horizon['flags']['auth_required'] ?? false),
            'auth_revocable'     => ($horizon['flags']['auth_revocable'] ?? false),
            'auth_immutable'     => ($horizon['flags']['auth_immutable'] ?? false),
            'auth_clawback_enabled'     => ($horizon['flags']['auth_clawback_enabled'] ?? false),

            'num_claimable_balances' => $horizon['num_claimable_balances'] ?? 0,
            'num_contracts' => $horizon['num_contracts'] ?? 0,

            'claimable_balances_amount' => $horizon['claimable_balances_amount'] ?? 0,
            'liquidity_pools_amount' => $horizon['liquidity_pools_amount'] ?? 0,
            'contracts_amount' => $horizon['contracts_amount'] ?? 0,
            'transactions' => $this->getRecentTransactions($issuer, $code),
        ];
    }

    private function getRecentTransactions(string $issuer, string $code): array
    {
        $response = Http::get($this->horizon . '/payments', [
            'asset_code'   => $code,
            'asset_issuer' => $issuer,
            'limit'        => 40,
            'order'        => 'desc',
        ]);

        if (!$response->ok()) {
            return [];
        }

        $records = $response->json('_embedded.records') ?? [];

        return collect($records)
            ->flatMap(function ($op) use ($issuer, $code) {

                // NORMAL payments
                if (($op['type'] ?? '') === 'payment') {
                    return [[
                        'type'   => 'payment',
                        'from'   => $op['from'] ?? '-',
                        'to'     => $op['to'] ?? '-',
                        'amount' => (float) ($op['amount'] ?? 0),
                        'time'   => Carbon::parse($op['created_at'])->diffForHumans(),
                    ]];
                }

                // SOROBAN / CONTRACT OPS
                if (!empty($op['asset_balance_changes'])) {

                    return collect($op['asset_balance_changes'])
                        ->filter(function ($c) use ($issuer, $code) {
                            return (
                                ($c['asset_code'] ?? null) === $code &&
                                ($c['asset_issuer'] ?? null) === $issuer
                            );
                        })
                        ->map(function ($c) use ($op) {
                            return [
                                'type'   => $c['type'] ?? 'contract',
                                'from'   => $c['from'] ?? '-',
                                'to'     => $c['to'] ?? '-',
                                'amount' => (float) ($c['amount'] ?? 0),
                                'time'   => Carbon::parse($op['created_at'])->diffForHumans(),
                            ];
                        });
                }

                return [];
            })
            ->take(10)
            ->values()
            ->toArray();
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

    private function normalizeUrl(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        if (!str_starts_with($url, 'http')) {
            return 'https://' . $url;
        }

        return $url;
    }
}

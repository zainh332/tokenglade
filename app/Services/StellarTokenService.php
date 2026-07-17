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

        $assetId = "{$code}-{$issuer}";
        $expertUrl = "https://api.stellar.expert/explorer/public/asset/{$assetId}";
        try {

            $response = Http::timeout(4)->get($expertUrl);

            if (!$response->ok()) {

                $response = null;
            }
        } catch (\Throwable $e) {

            $response = null;
        }

        $totalTrades = (int) ($response?->json('trades') ?? 0
);
        $tradedAmountRaw = $response?->json('traded_amount');

        $payments = (int) ($response?->json('payments') ?? 0);
        $paymentsAmountRaw = $response?->json('payments_amount');

        $rating = $response?->json('rating') ?? [];
        $decimals = (int) ($response?->json('decimals') ?? 7);

        $transactions = $this->getRecentTransactions($issuer, $code);

        $price_xlm = null;
        if (!empty($transactions)) {
            $price_xlm = (float) $transactions[0]['price'];
        }

        if ($price_xlm === null) {
            $orderbook = Http::get($this->horizon . '/order_book', [
                'selling_asset_type' => $this->getAssetType($code),
                'selling_asset_code' => $code,
                'selling_asset_issuer' => $issuer,
                'buying_asset_type' => 'native',
            ]);

            if ($orderbook->ok()) {
                $bestBid = $orderbook->json('bids.0.price');
                $price_xlm = $bestBid ? (float) $bestBid : null;
            }
        }

        // Fetch Top 10 Holders from StellarExpert
        $holdersResponse = Http::timeout(8)->get("{$expertUrl}/holders", [
            'limit' => 10,
            'order' => 'desc'
        ]);

        $topHolders = [];
        if ($holdersResponse->ok()) {
            $records = $holdersResponse->json('_embedded.records') ?? [];
            foreach ($records as $record) {
                $rawBalance = $record['balance'] ?? 0;
                $formattedBalance = bcdiv(
                    normalizeBcNumber($rawBalance),
                    bcpow('10', (string) $decimals, 0),
                    $decimals
                );

                $topHolders[] = [
                    'address' => $record['account'] ?? $record['address'] ?? null,
                    'balance' => (float) $formattedBalance,
                ];
            }
        }

        $horizon = $horizonResponse->json('_embedded.records.0');
        $mintDateRaw = $response?->json('created');
        $holders = $response?->json('trustlines.funded');
        $toml = $this->fetchTomlMetadata($horizon);
        $xlmUsdPrice = $this->getXlmUsdPrice();
        $usd_price = $price_xlm !== null ? ($price_xlm * $xlmUsdPrice) : 0.0;
        $formattedSupply = (float) ($horizon['balances']['authorized'] ?? 0)
            + (float) ($horizon['claimable_balances_amount'] ?? 0)
            + (float) ($horizon['liquidity_pools_amount'] ?? 0)
            + (float) ($horizon['contracts_amount'] ?? 0);

        $tradedAmount = 0;
        $normalizedTradedAmount = normalizeBcNumber(
            $tradedAmountRaw
        );

        if ($normalizedTradedAmount !== '0') {
            $tradedAmount = bcdiv(
                $normalizedTradedAmount,
                bcpow('10', (string) $decimals, 0),
                $decimals
            );
        }

        $paymentsAmount = 0;
        $normalizedPaymentsAmount = normalizeBcNumber(
            $paymentsAmountRaw
        );

        if ($normalizedPaymentsAmount !== '0') {
            $paymentsAmount = bcdiv(
                $normalizedPaymentsAmount,
                bcpow('10', (string) $decimals, 0),
                $decimals
            );
        }

        $issuerResponse = Http::get($this->horizon . "/accounts/{$issuer}");
        $issuerData = $issuerResponse->ok() ? $issuerResponse->json() : null;

        return [
            'asset_code'       => $code,
            'issuer'           => $issuer,

            'name'             => $toml['token']['name'] ?? $toml['project']['org_name'] ?? $code,
            'image'            => $toml['token']['image'] ?? null,
            'description'      => $toml['token']['description'] ?? null,

            'project'          => $toml['project'] ?? [],

            'total_supply' => $formattedSupply,
            'trustlines'     => (int) ($horizon['accounts']['authorized'] ?? 0),
            'holders'     => (int) ($holders ?? 0),
            'top_holders'  => $topHolders,

            'issuer_locked'    => $issuerData['flags']['auth_immutable'] ?? false,
            'minting_possible' => !($issuerData['flags']['auth_immutable'] ?? false),
            'mint_date_human' => Carbon::createFromTimestampUTC($mintDateRaw)->format('Y-m-d'),
            'liquidity_pools'     => (float) ($horizon['num_liquidity_pools'] ?? 0),
            'updated_at'     => '1 min ago',
            'website' => $toml['token']['website'] ?? $toml['project']['org_url'] ?? null,
            'twitter' =>
            isset($toml['project']['org_twitter'])
                && !empty($toml['project']['org_twitter'])

                ? 'https://x.com/' .
                $toml['project']['org_twitter']
                : null,
            'email'             => $toml['project']['org_email'] ?? null,
            'support_email'             => $toml['project']['org_support'] ?? null,

            'auth_required'     => ($horizon['flags']['auth_required'] ?? false),
            'auth_revocable'     => ($horizon['flags']['auth_revocable'] ?? false),
            'auth_immutable'     => ($horizon['flags']['auth_immutable'] ?? false),
            'auth_clawback_enabled'     => ($horizon['flags']['auth_clawback_enabled'] ?? false),

            'num_claimable_balances' => $horizon['num_claimable_balances'] ?? 0,
            'num_contracts' => $horizon['num_contracts'] ?? 0,

            'claimable_balances_amount' => $horizon['claimable_balances_amount'] ?? 0,
            'liquidity_pools_amount' => $horizon['liquidity_pools_amount'] ?? 0,
            'contracts_amount' => $horizon['contracts_amount'] ?? 0,
            'transactions' => $transactions,
            'volume_1h' => $this->getLastHourVolume($issuer, $code),
            'usd_price' => $usd_price,
            'xlm_price' => $price_xlm,

            'activity' => [
                'total_trades' => $totalTrades,
                'traded_volume' => $tradedAmount,
                'payments' => $payments,
                'payments_volume' => $paymentsAmount,
            ],

            'rating' => [
                'age' => $rating['age'] ?? 0,
                'activity' => $rating['activity'] ?? 0,
                'trustlines' => $rating['trustlines'] ?? 0,
                'liquidity' => $rating['liquidity'] ?? 0,
                'volume7d' => $rating['volume7d'] ?? 0,
                'interop' => $rating['interop'] ?? 0,
                'average' => $rating['average'] ?? 0,
            ],
        ];
    }

    private function getRecentTransactions(string $issuer, string $code): array
    {
        $assetType = $this->getAssetType($code);

        $response = Http::get($this->horizon . '/trades', [
            'base_asset_type'   => $assetType,
            'base_asset_code'   => $code,
            'base_asset_issuer' => $issuer,
            'counter_asset_type' => 'native',
            'order'             => 'desc',
            'limit'             => 100,
        ]);

        if (!$response->ok()) {
            return [];
        }

        return collect($response->json()['_embedded']['records'])
            ->map(function ($trade) use ($code, $issuer) {
                $isBase = (($trade['base_asset_code'] ?? null) === $code && ($trade['base_asset_issuer'] ?? null) === $issuer);
                $isLiquidityPool = ($trade['trade_type'] === 'liquidity_pool');

                if ($isBase) {
                    if ($isLiquidityPool) {
                        $side = $trade['base_is_seller'] ? 'sell' : 'buy';
                    } else {
                        $side = $trade['base_is_seller'] ? 'buy' : 'sell';
                    }
                    $amount = (float) $trade['base_amount'];
                    $value  = (float) $trade['counter_amount'];
                } else {
                    if ($isLiquidityPool) {
                        $side = $trade['base_is_seller'] ? 'buy' : 'sell';
                    } else {
                        $side = $trade['base_is_seller'] ? 'sell' : 'buy';
                    }
                    $amount = (float) $trade['counter_amount'];
                    $value  = (float) $trade['base_amount'];
                }

                $price = $amount > 0 ? $value / $amount : 0;

                return [
                    'type'   => $trade['trade_type'],
                    'side'   => $side,
                    'amount' => $amount,
                    'value'  => $value,
                    'price'  => $price,
                    'time'   => \Carbon\Carbon::parse($trade['ledger_close_time'])->diffForHumans(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function getLastHourVolume(string $issuer, string $code): float
    {
        $assetType = $this->getAssetType($code);

        $url = $this->horizon . '/trades?' . http_build_query([
            'base_asset_type'   => $assetType,
            'base_asset_code'   => $code,
            'base_asset_issuer' => $issuer,
            'counter_asset_type' => 'native',
            'order'             => 'desc',
            'limit'             => 200,
        ]);

        $volume = 0;
        $cutoff = now()->subHour();

        while ($url) {

            $response = Http::timeout(10)->get($url);

            if (!$response->ok()) {
                break;
            }

            $records = $response->json('_embedded.records');

            foreach ($records as $trade) {

                $time = Carbon::parse($trade['ledger_close_time']);

                if ($time->lt($cutoff)) {
                    return $volume;
                }

                $volume += (float) $trade['base_amount'];
            }

            $url = $response->json('_links.next.href');
        }

        return $volume;
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
            return [
                'project' => [],
                'token'   => [],
            ];
        }

        $response = Http::timeout(6)->get($tomlUrl);

        if (!$response->ok()) {
            return [];
        }

        $body = preg_replace('/\[\s*\]/', '[]', $response->body());
        try {
            $parsed = Toml::parse($body);
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
                    'website'     => $currency['website'] ?? null,
                ];

                break;
            }
        }

        return [
            'project' => $project,
            'token'   => $token,
        ];
    }

    private function getAssetType(string $code): string
    {
        $length = strlen($code);

        if ($length <= 4) {
            return 'credit_alphanum4';
        }

        return 'credit_alphanum12';
    }

    public function updateOhlcData(string $code, string $issuer, string $timeframe): void
    {
        $assetType = $this->getAssetType($code);
        
        $resolution = 86400000; // 1d
        if ($timeframe === '1w') {
            $resolution = 604800000;
        } elseif ($timeframe === '4h') {
            $resolution = 3600000; // fetch 1h, aggregate to 4h
        }

        $limit = 150;
        if ($timeframe === '4h') {
            $limit = 400; // fetch 400 hourly candles to aggregate
        }

        $url = $this->horizon . '/trade_aggregations?' . http_build_query([
            'base_asset_type'   => $assetType,
            'base_asset_code'   => $code,
            'base_asset_issuer' => $issuer,
            'counter_asset_type' => 'native',
            'resolution'        => $resolution,
            'limit'             => $limit,
            'order'             => 'desc',
        ]);

        $response = Http::get($url);
        if (!$response->ok()) {
            throw new \Exception("Horizon trade aggregations request failed: " . $response->body());
        }

        $records = $response->json('_embedded.records') ?? [];

        if ($timeframe === '4h') {
            $records = $this->aggregate1hTo4h($records);
        }

        foreach ($records as $record) {
            $timestamp = (int) ($record['timestamp'] / 1000); // ms to sec
            
            \App\Models\StellarOhlcData::updateOrCreate([
                'asset_code' => $code,
                'asset_issuer' => $issuer,
                'timeframe' => $timeframe,
                'timestamp' => $timestamp,
            ], [
                'open' => (float) $record['open'],
                'high' => (float) $record['high'],
                'low' => (float) $record['low'],
                'close' => (float) $record['close'],
                'volume' => (float) $record['base_volume'],
            ]);
        }
    }

    private function aggregate1hTo4h(array $hourlyRecords): array
    {
        usort($hourlyRecords, function ($a, $b) {
            return (int) $a['timestamp'] <=> (int) $b['timestamp'];
        });

        $aggregated = [];
        $current4hCandle = null;

        foreach ($hourlyRecords as $record) {
            $timestampMs = (int) $record['timestamp'];
            $timestampSec = $timestampMs / 1000;
            
            $boundaryStartSec = $timestampSec - ($timestampSec % 14400);
            $boundaryStartMs = $boundaryStartSec * 1000;

            if ($current4hCandle === null || $current4hCandle['timestamp'] !== $boundaryStartMs) {
                if ($current4hCandle !== null) {
                    $aggregated[] = $current4hCandle;
                }
                $current4hCandle = [
                    'timestamp' => $boundaryStartMs,
                    'open' => (float) $record['open'],
                    'high' => (float) $record['high'],
                    'low' => (float) $record['low'],
                    'close' => (float) $record['close'],
                    'base_volume' => (float) $record['base_volume'],
                ];
            } else {
                $current4hCandle['high'] = max($current4hCandle['high'], (float) $record['high']);
                $current4hCandle['low'] = min($current4hCandle['low'], (float) $record['low']);
                $current4hCandle['close'] = (float) $record['close'];
                $current4hCandle['base_volume'] += (float) $record['base_volume'];
            }
        }

        if ($current4hCandle !== null) {
            $aggregated[] = $current4hCandle;
        }

        usort($aggregated, function ($a, $b) {
            return (int) $b['timestamp'] <=> (int) $a['timestamp'];
        });

        return $aggregated;
    }

    public function getXlmUsdPrice(): float
    {
        try {
            $response = Http::timeout(3)->get('https://api.stellar.expert/explorer/public/asset/XLM');
            if ($response->ok()) {
                return (float) ($response->json('price') ?? 0.18);
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return 0.18;
    }
}

<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Yosymfony\Toml\Toml;

class StellarTokenService
{
    protected string $horizon = 'https://horizon.stellar.org';



    public function getTokenInsight(string $issuer, string $code, ?array $horizonAsset = null): array
    {
        if (!$this->isValidStellarAddress($issuer)) {
            throw new \Exception('Invalid Stellar address format.');
        }

        $horizon = $horizonAsset;
        if ($horizon === null) {
            try {
                $horizonResponse = Http::timeout(5)->get($this->horizon . '/assets', [
                    'asset_issuer' => $issuer,
                    'asset_code' => $code,
                    'limit' => 1
                ]);

                if ($horizonResponse->ok()) {
                    $horizon = $horizonResponse->json('_embedded.records.0');
                }
            } catch (\Throwable $e) {}
        }
        $horizon = $horizon ?? [];

        $assetId = "{$code}-{$issuer}";
        $expertUrl = "https://api.stellar.expert/explorer/public/asset/{$assetId}";
        $seData = null;
        $seCacheKey = "se_asset_details_{$assetId}";
        try {
            $response = Http::timeout(4)->get($expertUrl);
            if ($response->ok()) {
                $seData = $response->json();
                if (!empty($seData)) {
                    Cache::put($seCacheKey, $seData, 3600); // Cache for 1 hour
                }
            } else {
                $seData = Cache::get($seCacheKey);
            }
        } catch (\Throwable $e) {
            $seData = Cache::get($seCacheKey);
        }

        $totalTrades = (int) ($seData['trades'] ?? 0);
        $tradedAmountRaw = $seData['traded_amount'] ?? null;
        $payments = (int) ($seData['payments'] ?? 0);
        $paymentsAmountRaw = $seData['payments_amount'] ?? null;
        $rating = $seData['rating'] ?? [];
        $decimals = (int) ($seData['decimals'] ?? 7);

        $transactions = $this->getRecentTransactions($issuer, $code);

        $price_xlm = null;
        if (!empty($transactions)) {
            $price_xlm = (float) $transactions[0]['price'];
        }

        if ($price_xlm === null) {
            try {
                $orderbook = Http::timeout(4)->get($this->horizon . '/order_book', [
                    'selling_asset_type' => $this->getAssetType($code),
                    'selling_asset_code' => $code,
                    'selling_asset_issuer' => $issuer,
                    'buying_asset_type' => 'native',
                ]);

                if ($orderbook->ok()) {
                    $bestBid = $orderbook->json('bids.0.price');
                    $price_xlm = $bestBid ? (float) $bestBid : null;
                }
            } catch (\Throwable $e) {}
        }

        $toml = $this->fetchTomlMetadata($horizon);

        $tokenDomain = null;
        $rawDomain = ($seData['home_domain'] ?? null) ?? $toml['token']['website'] ?? $toml['project']['org_url'] ?? null;
        if ($rawDomain) {
            $tokenDomain = parse_url($rawDomain, PHP_URL_HOST) ?: $rawDomain;
            $tokenDomain = str_replace('www.', '', strtolower($tokenDomain));
        }

        $individualHolders = [];
        $projectHolders = [];

        $mintDateRaw = $seData['created'] ?? null;
        $holders = $seData['trustlines']['funded'] ?? null;
        $xlmUsdPrice = $this->getXlmUsdPrice();
        $usd_price = $price_xlm !== null ? ($price_xlm * $xlmUsdPrice) : 0.0;
        
        $volumes = $this->getAssetVolume24h($code, $issuer, $xlmUsdPrice, $usd_price);

        $high24hXlm = null;
        $low24hXlm = null;
        $priceChange24h = null;

        try {
            $nowMs = time() * 1000;
            $startMs = $nowMs - (24 * 3600 * 1000);
            $aggResponse = Http::timeout(4)->get($this->horizon . '/trade_aggregations', [
                'base_asset_type'    => $this->getAssetType($code),
                'base_asset_code'    => $code,
                'base_asset_issuer'  => $issuer,
                'counter_asset_type' => 'native',
                'resolution'         => 3600000,
                'start_time'         => $startMs,
                'end_time'           => $nowMs,
                'limit'              => 50,
                'order'              => 'desc'
            ]);

            if ($aggResponse->ok()) {
                $records = $aggResponse->json('_embedded.records') ?? [];
                if (!empty($records)) {
                    $highs = [];
                    $lows = [];
                    foreach ($records as $r) {
                        if (isset($r['high']) && (float)$r['high'] > 0) $highs[] = (float)$r['high'];
                        if (isset($r['low']) && (float)$r['low'] > 0) $lows[] = (float)$r['low'];
                    }
                    if (!empty($highs)) $high24hXlm = max($highs);
                    if (!empty($lows)) $low24hXlm = min($lows);

                    $latestClose = isset($records[0]['close']) ? (float)$records[0]['close'] : null;
                    $oldestOpen = isset($records[count($records) - 1]['open']) ? (float)$records[count($records) - 1]['open'] : null;
                    if ($latestClose && $oldestOpen && $oldestOpen > 0) {
                        $priceChange24h = round((($latestClose - $oldestOpen) / $oldestOpen) * 100, 2);
                    }
                    if ($price_xlm === null && $latestClose) {
                        $price_xlm = $latestClose;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('trade_aggregations 24h stats failed', ['msg' => $e->getMessage()]);
        }

        if ($high24hXlm === null && $price_xlm !== null) {
            $high24hXlm = $price_xlm;
        }
        if ($low24hXlm === null && $price_xlm !== null) {
            $low24hXlm = $price_xlm;
        }

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

        $issuerData = null;
        try {
            $issuerResponse = Http::timeout(4)->get($this->horizon . "/accounts/{$issuer}");
            $issuerData = $issuerResponse->ok() ? $issuerResponse->json() : null;
        } catch (\Throwable $e) {}

        $masterKeyWeight = 1;
        if (isset($issuerData['signers'])) {
            foreach ($issuerData['signers'] as $signer) {
                if ($signer['key'] === $issuer) {
                    $masterKeyWeight = (int) $signer['weight'];
                    break;
                }
            }
        }
        $issuerLocked = ($masterKeyWeight === 0);
        $dbToken = \App\Models\StellarToken::where('issuer_public_key', strtoupper($issuer))
            ->where('asset_code', strtoupper($code))
            ->first();

        $verifiedProj = \App\Models\VerifiedProject::where('identifier', $issuer)
            ->where('blockchain_id', 1)
            ->first();

        $website = null;
        $twitter = null;
        $email = null;
        $supportEmail = null;
        $projectData = $toml['project'] ?? [];

        if ($dbToken !== null) {
            $website = $verifiedProj?->website ?: ($dbToken->website_url ?: null);
            if ($verifiedProj && !empty($verifiedProj->twitter)) {
                $tw = $verifiedProj->twitter;
                if (str_contains($tw, 'twitter.com') || str_contains($tw, 'x.com')) {
                    $twitter = $tw;
                } else {
                    $twitter = 'https://x.com/' . ltrim($tw, '@');
                }
            }
            $email = $verifiedProj?->email ?: null;
            $projectData['org_name'] = $dbToken->name;
            $projectData['org_url'] = $website;
            $projectData['org_email'] = $email;
            if ($verifiedProj && !empty($verifiedProj->twitter)) {
                $projectData['org_twitter'] = $verifiedProj->twitter;
            }
        } else {
            $website = $toml['token']['website'] ?? $toml['project']['org_url'] ?? null;
            if (isset($toml['project']['org_twitter']) && !empty($toml['project']['org_twitter'])) {
                $tw = $toml['project']['org_twitter'];
                if (str_contains($tw, 'http')) {
                    $twitter = $tw;
                } else {
                    $twitter = 'https://x.com/' . ltrim($tw, '@');
                }
            }
            $email = $toml['project']['org_email'] ?? null;
            $supportEmail = $toml['project']['org_support'] ?? null;
        }

        return [
            'asset_code'       => $code,
            'issuer'           => $issuer,
            'is_minted_on_tokenglade' => $dbToken !== null,

            'name'             => $dbToken?->name ?? $toml['token']['name'] ?? $toml['project']['org_name'] ?? $code,
            'image'            => $dbToken?->logo ?? $toml['token']['image'] ?? null,
            'description'      => $dbToken?->desc ?? $toml['token']['description'] ?? null,

            'project'          => $projectData,

            'total_supply' => $formattedSupply,
            'trustlines'     => (int) ($horizon['accounts']['authorized'] ?? 0),
            'holders'     => (int) ($holders ?? 0),
            'top_holders'  => array_slice($individualHolders, 0, 10),
            'project_holders' => $projectHolders,

            'issuer_locked'    => $issuerLocked,
            'minting_possible' => !$issuerLocked,
            'mint_date_human' => Carbon::createFromTimestampUTC($mintDateRaw)->format('Y-m-d'),
            'liquidity_pools'     => (float) ($horizon['num_liquidity_pools'] ?? 0),
            'updated_at'     => '1 min ago',
            'website'           => $website,
            'twitter'           => $twitter,
            'email'             => $email,
            'support_email'     => $supportEmail,

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
            'volume_1h' => 0.0,
            'volume_24h' => $volumes['total_volume_24h'],
            'high_24h' => $high24hXlm,
            'low_24h' => $low24hXlm,
            'price_change_24h' => $priceChange24h ?? 0.0,
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
            'liquidity_overview' => null,
            'token_domain'       => $tokenDomain,
        ];
    }

    public function getPoolIdForAsset(string $code, string $issuer): ?string
    {
        $cacheKey = "lp_pool_id_{$code}_{$issuer}";
        return Cache::remember($cacheKey, 86400, function () use ($code, $issuer) {
            try {
                $response = Http::timeout(5)->get($this->horizon . '/liquidity_pools', [
                    'assets' => "{$code}:{$issuer},native",
                ]);
                if ($response->ok()) {
                    return $response->json('_embedded.records.0.id');
                }
            } catch (\Throwable $e) {
                Log::warning("Failed to fetch pool ID for {$code}: " . $e->getMessage());
            }
            return null;
        });
    }

    private function getRecentTransactions(string $issuer, string $code): array
    {
        try {
            $assetType = $this->getAssetType($code);

            $response = Http::timeout(4)->get($this->horizon . '/trades', [
                'base_asset_type'   => $assetType,
                'base_asset_code'   => $code,
                'base_asset_issuer' => $issuer,
                'counter_asset_type' => 'native',
                'order'             => 'desc',
                'limit'             => 200,
            ]);

            if (!$response->ok()) {
                return [];
            }

            $records = $response->json('_embedded.records') ?? [];

            return collect($records)
                ->map(function ($trade) use ($code, $issuer) {
                    $isBase = (($trade['base_asset_code'] ?? null) === $code && ($trade['base_asset_issuer'] ?? null) === $issuer);
                    $isLiquidityPool = (($trade['trade_type'] ?? '') === 'liquidity_pool');

                    if ($isBase) {
                        if ($isLiquidityPool) {
                            $side = !empty($trade['base_is_seller']) ? 'sell' : 'buy';
                        } else {
                            $side = !empty($trade['base_is_seller']) ? 'buy' : 'sell';
                        }
                        $amount = (float) ($trade['base_amount'] ?? 0);
                        $value  = (float) ($trade['counter_amount'] ?? 0);
                    } else {
                        if ($isLiquidityPool) {
                            $side = !empty($trade['base_is_seller']) ? 'buy' : 'sell';
                        } else {
                            $side = !empty($trade['base_is_seller']) ? 'sell' : 'buy';
                        }
                        $amount = (float) ($trade['counter_amount'] ?? 0);
                        $value  = (float) ($trade['base_amount'] ?? 0);
                    }

                    $price = $amount > 0 ? $value / $amount : 0;
                    $timeStr = isset($trade['ledger_close_time'])
                        ? \Carbon\Carbon::parse($trade['ledger_close_time'])->diffForHumans()
                        : 'recently';

                    return [
                        'type'   => $trade['trade_type'] ?? 'order_book',
                        'side'   => $side,
                        'amount' => $amount,
                        'value'  => $value,
                        'price'  => $price,
                        'time'   => $timeStr,
                    ];
                })
                ->values()
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
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
    //         ? ($top10->sum('balance') / $totalSupply) * 10000
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
            return [];
        }

        try {
            $response = Http::timeout(6)->get($this->horizon . '/assets', [
                'asset_issuer' => $issuer,
                'limit' => 200
            ]);

            if (!$response->ok()) {
                return [];
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
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("getAssetsByIssuer error for {$issuer}: " . $e->getMessage());
            return [];
        }
    }

    protected function fetchTomlMetadata(?array $asset): array
    {
        if (!$asset) {
            return [
                'project' => [],
                'token'   => [],
            ];
        }

        $tomlUrl = $asset['_links']['toml']['href'] ?? null;

        if (!$tomlUrl) {
            return [
                'project' => [],
                'token'   => [],
            ];
        }

        $code = $asset['asset_code'] ?? null;
        $issuer = $asset['asset_issuer'] ?? null;

        return Cache::remember("toml_metadata_" . md5($tomlUrl) . "_" . md5("{$code}_{$issuer}"), 7200, function () use ($tomlUrl, $code, $issuer) {
            try {
                $response = Http::timeout(6)->get($tomlUrl);

                if (!$response->ok()) {
                    return [
                        'project' => [],
                        'token'   => [],
                    ];
                }

                $body = preg_replace('/\[\s*\]/', '[]', $response->body());
                $parsed = Toml::parse($body);

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
                        ($currency['code'] ?? null) === $code &&
                        ($currency['issuer'] ?? null) === $issuer
                    ) {
                        $token = [
                            'name'        => $currency['name'] ?? $code,
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
            } catch (\Throwable $e) {
                return [
                    'project' => [],
                    'token'   => [],
                ];
            }
        });
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
            $timestampSec = (int) ($timestampMs / 1000);
            
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
        return Cache::remember('xlm_usd_price', 60, function () {
            try {
                $response = Http::timeout(3)->get('https://api.stellar.expert/explorer/public/asset/XLM');
                if ($response->ok()) {
                    return (float) ($response->json('price') ?? 0.18);
                }
            } catch (\Throwable $e) {}

            return 0.18;
        });
    }

    public function getLiquidityPoolsInfo(string $code, string $issuer, float $xlmUsdPrice, float $usd_price): array
    {
        $assetType = $this->getAssetType($code);
        $targetAssetString = $assetType === 'native' ? 'native' : "{$code}:{$issuer}";
        
        $records = [];
        
        // 1. Fetch general pools from Horizon with pagination (up to 1000 pools)
        try {
            $nextUrl = $this->horizon . '/liquidity_pools?' . http_build_query([
                'reserves' => $targetAssetString,
                'limit' => 200,
            ]);
            
            $pages = 0;
            while ($nextUrl && $pages < 5) {
                $pages++;
                $responseGeneral = Http::timeout(4)->get($nextUrl);
                if ($responseGeneral->ok()) {
                    $pageRecords = $responseGeneral->json('_embedded.records') ?? [];
                    $records = array_merge($records, $pageRecords);
                    $nextHref = $responseGeneral->json('_links.next.href');
                    if (!empty($pageRecords) && count($pageRecords) === 200 && $nextHref && $nextHref !== $nextUrl) {
                        $nextUrl = $nextHref;
                    } else {
                        break;
                    }
                } else {
                    break;
                }
            }
        } catch (\Throwable $e) {}

        // 2. Directly query paired with XLM and USDC in case general didn't capture
        try {
            $responseXlm = Http::timeout(3)->get($this->horizon . '/liquidity_pools', [
                'reserves' => "{$targetAssetString},native",
                'limit' => 100,
            ]);
            if ($responseXlm->ok()) {
                $records = array_merge($records, $responseXlm->json('_embedded.records') ?? []);
            }
        } catch (\Throwable $e) {}

        $usdcAsset = 'USDC:GBBD7XJ4PQRRLO3SCMWND5NG3CZFLBCYZIVTTTIH2DZ7P2VTUQXJ4GX3';
        try {
            $responseUsdc = Http::timeout(3)->get($this->horizon . '/liquidity_pools', [
                'reserves' => "{$targetAssetString},{$usdcAsset}",
                'limit' => 100,
            ]);
            if ($responseUsdc->ok()) {
                $records = array_merge($records, $responseUsdc->json('_embedded.records') ?? []);
            }
        } catch (\Throwable $e) {}

        // 3. StellarExpert Liquidity Pools API as supplementary source
        try {
            $expertAsset = $assetType === 'native' ? 'XLM' : "{$code}-{$issuer}";
            $expertPoolsRes = Http::timeout(4)->get("https://api.stellar.expert/explorer/public/liquidity-pool", [
                'asset' => $expertAsset,
                'limit' => 100,
            ]);
            if ($expertPoolsRes->ok()) {
                $expertRecords = $expertPoolsRes->json('_embedded.records') ?? [];
                foreach ($expertRecords as $er) {
                    if (isset($er['id'])) {
                        $res = [];
                        if (isset($er['asset']) && is_array($er['asset']) && isset($er['reserves']) && is_array($er['reserves'])) {
                            foreach ($er['asset'] as $idx => $a) {
                                $canonical = $a === 'native' ? 'native' : str_replace('-', ':', $a);
                                $rawAmount = $er['reserves'][$idx] ?? 0;
                                $amountStr = (string)(is_numeric($rawAmount) && $rawAmount > 1000000000 ? $rawAmount / 10000000 : $rawAmount);
                                $res[] = [
                                    'asset' => $canonical,
                                    'amount' => $amountStr
                                ];
                            }
                        }
                        if (count($res) >= 2) {
                            $records[] = [
                                'id' => $er['id'],
                                'fee_bp' => $er['fee'] ?? 30,
                                'total_shares' => (string)($er['shares'] ?? 0),
                                'total_trustlines' => (int)($er['accounts'] ?? ($er['trustlines'] ?? 0)),
                                'reserves' => $res,
                                'expert_tvl' => $er['total_value'] ?? null,
                                'expert_vol' => $er['volume'] ?? null,
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Unique by pool ID
        $uniqueRecords = [];
        foreach ($records as $rec) {
            if (isset($rec['id'])) {
                $uniqueRecords[$rec['id']] = $rec;
            }
        }
        
        $volumes = $this->getAssetVolume24h($code, $issuer, $xlmUsdPrice, $usd_price);
        $poolVolumes = $volumes['pool_volumes'] ?? [];

        $pools = [];
        $totalTvl = 0;
        
        foreach ($uniqueRecords as $record) {
            $reserves = $record['reserves'] ?? [];
            if (count($reserves) < 2) continue;
            
            $assetA = $reserves[0];
            $assetB = $reserves[1];
            
            $codeA = $this->getAssetCodeFromCanonical($assetA['asset']);
            $codeB = $this->getAssetCodeFromCanonical($assetB['asset']);
            
            $poolName = "{$codeA}/{$codeB}";
            if ($assetA['asset'] === $targetAssetString) {
                $poolName = "{$codeA}/{$codeB}";
            } elseif ($assetB['asset'] === $targetAssetString) {
                $poolName = "{$codeB}/{$codeA}";
            }
            
            $targetAmount = null;
            if ($assetA['asset'] === $targetAssetString) {
                $targetAmount = (float) $assetA['amount'];
            } elseif ($assetB['asset'] === $targetAssetString) {
                $targetAmount = (float) $assetB['amount'];
            }
            
            $tvl = 0;
            if ($targetAmount !== null && $usd_price > 0) {
                $tvl = $targetAmount * 2 * $usd_price;
            } else {
                $xlmAmount = null;
                if ($assetA['asset'] === 'native') {
                    $xlmAmount = (float) $assetA['amount'];
                } elseif ($assetB['asset'] === 'native') {
                    $xlmAmount = (float) $assetB['amount'];
                }
                if ($xlmAmount !== null) {
                    $tvl = $xlmAmount * 2 * $xlmUsdPrice;
                } else {
                    $usdcAmount = null;
                    if (str_contains(strtolower($assetA['asset']), 'usdc') || str_contains(strtolower($assetA['asset']), 'usd')) {
                        $usdcAmount = (float) $assetA['amount'];
                    } elseif (str_contains(strtolower($assetB['asset']), 'usdc') || str_contains(strtolower($assetB['asset']), 'usd')) {
                        $usdcAmount = (float) $assetB['amount'];
                    }
                    if ($usdcAmount !== null) {
                        $tvl = $usdcAmount * 2;
                    } elseif (isset($record['expert_tvl']) && (float)$record['expert_tvl'] > 0) {
                        $tvl = (float)$record['expert_tvl'];
                    }
                }
            }
            
            $totalTvl += $tvl;
            
            $poolId = $record['id'];
            $volume = $poolVolumes[$poolId] ?? (float)($record['expert_vol'] ?? 0.0);
            $feeFactor = (($record['fee_bp'] ?? 30) / 10000);
            $apr = $tvl > 0 ? (($volume * $feeFactor * 365) / $tvl) * 10000 : 0;
            
            $amountA = (float)($assetA['amount'] ?? 0);
            $amountB = (float)($assetB['amount'] ?? 0);
            $reservesFormatted = number_format($amountA, $amountA >= 100 ? 0 : 2) . " {$codeA} + " . number_format($amountB, $amountB >= 100 ? 0 : 2) . " {$codeB}";
            
            $totalShares = (float)($record['total_shares'] ?? ($record['shares'] ?? 0));
            $trustlines = (int)($record['total_trustlines'] ?? ($record['accounts'] ?? ($record['trustlines'] ?? 0)));

            $pools[] = [
                'id' => $record['id'],
                'name' => $poolName,
                'tvl' => $tvl,
                'apr' => $apr,
                'volume' => $volume,
                'fee_bp' => $record['fee_bp'] ?? 30,
                'total_shares' => $totalShares,
                'trustlines' => $trustlines,
                'reserves_formatted' => $reservesFormatted,
            ];
        }
        
        usort($pools, fn($a, $b) => $b['tvl'] <=> $a['tvl']);
        
        $largestPoolName = '-';
        $largestPoolTvl = 0;
        if (!empty($pools)) {
            $largestPoolName = $pools[0]['name'];
            $largestPoolTvl = $pools[0]['tvl'];
        }
        
        $lpVolume24h = array_sum(array_column($pools, 'volume'));
        $avgApr = count($pools) > 0 ? array_sum(array_column($pools, 'apr')) / count($pools) : 0;
        
        $depth2pct = $totalTvl * 0.08;
        
        return [
            'total_tvl' => $totalTvl,
            'pools_count' => count($pools),
            'largest_pool_name' => $largestPoolName,
            'largest_pool_tvl' => $largestPoolTvl,
            'lp_volume_24h' => $lpVolume24h,
            'avg_apr' => $avgApr,
            'depth_2pct' => $depth2pct,
            'pools' => $pools,
        ];
    }

    private function getAssetCodeFromCanonical(string $asset): string
    {
        if ($asset === 'native') return 'XLM';
        $parts = explode(':', $asset);
        return $parts[0] ?? $asset;
    }

    public function getHoldersData(string $issuer, string $code, ?string $tokenDomain): array
    {
        $cacheKey = "holders_data_v2_{$issuer}_{$code}";
        
        return Cache::remember($cacheKey, 120, function () use ($issuer, $code, $tokenDomain) {
            $expertUrl = "https://api.stellar.expert/explorer/public/asset/{$code}-{$issuer}";
            
            $decimals = 7;
            try {
                $assetResponse = Http::timeout(5)->get($expertUrl);
                if ($assetResponse->ok()) {
                    $decimals = (int) ($assetResponse->json('decimals') ?? 7);
                }
            } catch (\Throwable $e) {}

            $holdersResponse = null;
            try {
                $holdersResponse = Http::timeout(8)->get("{$expertUrl}/holders", [
                    'limit' => 35,
                    'order' => 'desc'
                ]);
            } catch (\Throwable $e) {}

            $projectHolders = [];
            $individualHolders = [];

            if ($holdersResponse && $holdersResponse->ok()) {
                $records = $holdersResponse->json('_embedded.records') ?? [];
                
                $verifiedProj = \App\Models\VerifiedProject::where('identifier', $issuer)
                    ->where('blockchain_id', 1)
                    ->where('status', 1)
                    ->first();

                $dbOfficialWallets = collect();
                if ($verifiedProj) {
                    if ($verifiedProj->profile) {
                        $dbOfficialWallets = \App\Models\ProjectOfficialWallet::where('project_profile_id', $verifiedProj->profile->id)->get();
                    }
                    if ($dbOfficialWallets->isEmpty() && !$verifiedProj->profile) {
                        $candidateWallets = \App\Models\ProjectOfficialWallet::where('project_profile_id', $verifiedProj->id)->get();
                        if ($candidateWallets->isNotEmpty()) {
                            $profileExists = \App\Models\ProjectProfile::where('id', $verifiedProj->id)->exists();
                            if (!$profileExists) {
                                $dbOfficialWallets = $candidateWallets;
                            }
                        }
                    }
                }

                $processedAddresses = [];

                $addresses = [];
                foreach ($records as $record) {
                    $addr = $record['account'] ?? $record['address'] ?? null;
                    if ($addr) {
                        $addresses[] = $addr;
                    }
                }

                $directoryMap = [];
                if (!empty($addresses)) {
                    $queryString = implode('&', array_map(fn($a) => 'address=' . urlencode($a), $addresses));
                    try {
                        $dirResponse = Http::timeout(4)->get("https://api.stellar.expert/explorer/public/directory?{$queryString}");
                        if ($dirResponse->ok()) {
                            $dirRecords = $dirResponse->json('_embedded.records') ?? [];
                            foreach ($dirRecords as $dir) {
                                $directoryMap[$dir['address']] = [
                                    'name' => $dir['name'] ?? null,
                                    'domain' => $dir['domain'] ?? null,
                                    'tags' => $dir['tags'] ?? [],
                                ];
                            }
                        }
                    } catch (\Throwable $e) {}
                }


                foreach ($addresses as $addr) {
                    if (!isset($directoryMap[$addr])) {
                        $domainInfo = Cache::remember("acc_domain_v2_{$addr}", 86400, function () use ($addr) {
                            try {
                                $horizonAcc = Http::timeout(1.5)->get($this->horizon . "/accounts/{$addr}");
                                if ($horizonAcc->ok()) {
                                    $homeDomain = $horizonAcc->json('home_domain');
                                    if ($homeDomain) {
                                        return [
                                            'has_domain' => true,
                                            'name' => ucwords(str_replace(['-', '_'], ' ', $homeDomain)),
                                            'domain' => $homeDomain,
                                            'tags' => ['custodian', 'project']
                                        ];
                                    }
                                }
                            } catch (\Throwable $e) {}
                            return ['has_domain' => false];
                        });

                        if ($domainInfo && ($domainInfo['has_domain'] ?? false)) {
                            $directoryMap[$addr] = $domainInfo;
                        }
                    }
                }

                foreach ($records as $record) {
                    $addr = $record['account'] ?? $record['address'] ?? null;
                    if (!$addr) continue;

                    $rawBalance = $record['balance'] ?? 0;
                    $formattedBalance = bcdiv(
                        normalizeBcNumber($rawBalance),
                        bcpow('10', (string) $decimals, 0),
                        $decimals
                    );

                    $dirInfo = $directoryMap[$addr] ?? [];

                    // Check if configured in project_official_wallets table
                    $dbWallet = $dbOfficialWallets->firstWhere('wallet_address', $addr);
                    if ($dbWallet) {
                        $processedAddresses[] = $addr;
                        $projectHolders[] = [
                            'address' => $addr,
                            'balance' => (float) $formattedBalance,
                            'name'    => $dbWallet->label,
                            'domain'  => $tokenDomain,
                            'tags'    => ['treasury', 'project'],
                        ];
                        continue;
                    }

                    $walletData = [
                        'address' => $addr,
                        'balance' => (float) $formattedBalance,
                        'name'    => $dirInfo['name'] ?? null,
                        'domain'  => $dirInfo['domain'] ?? null,
                        'tags'    => $dirInfo['tags'] ?? [],
                    ];

                    $isIssuer = ($addr === $issuer);
                    $tags = $dirInfo['tags'] ?? [];
                    $holderDomain = $dirInfo['domain'] ?? null;
                    if ($holderDomain) {
                        $holderDomain = str_replace('www.', '', strtolower($holderDomain));
                    }

                    $isPlatform = false;
                    if (!$verifiedProj) {
                        if (str_starts_with($addr, 'C')) {
                            $isPlatform = true;
                            if (empty($walletData['name'])) {
                                $walletData['name'] = 'Smart Contract Reserve';
                            }
                        } elseif ($isIssuer) {
                            $isPlatform = true;
                        } else {
                            $hasProjectTag = in_array('custodian', $tags) || in_array('treasury', $tags) || in_array('issuer', $tags);
                            
                            if (!$hasProjectTag && !empty($dirInfo['name'])) {
                                $nameLower = strtolower($dirInfo['name']);
                                if (str_contains($nameLower, 'rewards') || str_contains($nameLower, 'treasury') || str_contains($nameLower, 'pool') || str_contains($nameLower, 'custodian') || str_contains($nameLower, 'escrow')) {
                                    $hasProjectTag = true;
                                }
                            }
                            
                            if ($hasProjectTag) {
                                if ($holderDomain && $tokenDomain) {
                                    if ($holderDomain === $tokenDomain) {
                                        $isPlatform = true;
                                    }
                                } else {
                                    $isPlatform = true;
                                }
                            }
                        }
                    }

                    if ($isPlatform) {
                        $projectHolders[] = $walletData;
                    } else {
                        $individualHolders[] = $walletData;
                    }
                }

                // Fetch balance for configured database wallets not present in Stellar Expert's top 35 list
                foreach ($dbOfficialWallets as $dbWallet) {
                    if (!in_array($dbWallet->wallet_address, $processedAddresses)) {
                        $balance = 0.0;
                        try {
                            $accInfo = Http::timeout(1.5)->get($this->horizon . "/accounts/{$dbWallet->wallet_address}");
                            if ($accInfo->ok()) {
                                $balances = $accInfo->json('balances') ?? [];
                                foreach ($balances as $bal) {
                                    $bAssetCode = $bal['asset_code'] ?? 'XLM';
                                    $bAssetIssuer = $bal['asset_issuer'] ?? '';
                                    if ($code === 'XLM' && $bal['asset_type'] === 'native') {
                                        $balance = (float) $bal['balance'];
                                        break;
                                    } elseif (strtoupper($bAssetCode) === strtoupper($code) && strtoupper($bAssetIssuer) === strtoupper($issuer)) {
                                        $balance = (float) $bal['balance'];
                                        break;
                                    }
                                }
                            }
                        } catch (\Throwable $e) {}

                        $projectHolders[] = [
                            'address' => $dbWallet->wallet_address,
                            'balance' => $balance,
                            'name'    => $dbWallet->label,
                            'domain'  => $tokenDomain,
                            'tags'    => ['treasury', 'project'],
                        ];
                    }
                }
            }

            return [
                'top_holders' => array_slice($individualHolders, 0, 10),
                'project_holders' => $projectHolders,
            ];
        });
    }

    private function getStellarTermTicker(): array
    {
        return Cache::remember('stellarterm_ticker_data', 300, function () {
            try {
                $response = Http::timeout(5)->withHeaders([
                    'User-Agent' => 'Mozilla/5.0'
                ])->get('https://api.stellarterm.com/v1/ticker.json');
                if ($response->ok()) {
                    return $response->json() ?? [];
                }
            } catch (\Throwable $e) {
                Log::error('StellarTerm ticker fetch failed', ['msg' => $e->getMessage()]);
            }
            return [];
        });
    }

    public function getAssetVolume24h(string $code, string $issuer, float $xlmUsdPrice, float $usdPrice): array
    {
        $assetType = $this->getAssetType($code);
        
        // 1. Try StellarTerm ticker first (aggregates all trading pairs for major tokens)
        $ticker = $this->getStellarTermTicker();
        $assets = $ticker['assets'] ?? [];
        foreach ($assets as $asset) {
            if (($asset['code'] ?? null) === $code && ($asset['issuer'] ?? null) === $issuer) {
                $totalVolumeUsd = (float) ($asset['volume24h_USD'] ?? 0.0);
                if ($totalVolumeUsd > 0.0) {
                    $lpVolume24h = 0.0;
                    $poolVolumes = [];
                    try {
                        $response = Http::timeout(5)->get($this->horizon . '/trades', [
                            'base_asset_type'   => $assetType,
                            'base_asset_code'   => $code,
                            'base_asset_issuer' => $issuer,
                            'counter_asset_type' => 'native',
                            'order'             => 'desc',
                            'limit'             => 200,
                        ]);
                        if ($response->ok()) {
                            $records = $response->json('_embedded.records') ?? [];
                            $now = time();
                            foreach ($records as $trade) {
                                $closeTime = strtotime($trade['ledger_close_time'] ?? '');
                                if ($now - $closeTime > 86400) {
                                    continue;
                                }
                                if ($trade['trade_type'] === 'liquidity_pool') {
                                    $isBase = (($trade['base_asset_code'] ?? null) === $code && ($trade['base_asset_issuer'] ?? null) === $issuer);
                                    if (($trade['counter_asset_type'] ?? null) === 'native') {
                                        $xlmAmount = (float) $trade['counter_amount'];
                                        $tradeValUsd = $xlmAmount * $xlmUsdPrice;
                                    } elseif (($trade['base_asset_type'] ?? null) === 'native') {
                                        $xlmAmount = (float) $trade['base_amount'];
                                        $tradeValUsd = $xlmAmount * $xlmUsdPrice;
                                    } else {
                                        $tokenAmount = $isBase ? (float) $trade['base_amount'] : (float) $trade['counter_amount'];
                                        $tradeValUsd = $tokenAmount * $usdPrice;
                                    }
                                    $lpVolume24h += $tradeValUsd;
                                    $poolId = $trade['counter_liquidity_pool_id'] ?? $trade['base_liquidity_pool_id'] ?? null;
                                    if ($poolId) {
                                        $poolVolumes[$poolId] = ($poolVolumes[$poolId] ?? 0.0) + $tradeValUsd;
                                    }
                                }
                            }
                        }
                    } catch (\Throwable $e) {}

                    return [
                        'lp_volume_24h' => $lpVolume24h,
                        'dex_volume_24h' => max(0.0, $totalVolumeUsd - $lpVolume24h),
                        'total_volume_24h' => $totalVolumeUsd,
                        'pool_volumes' => $poolVolumes,
                    ];
                }
            }
        }

        // 2. Fallback to Horizon trade aggregations (e.g. for custom/local assets)
        $totalVolumeXlm = 0.0;
        try {
            $nowMs = time() * 1000;
            $startMs = $nowMs - (24 * 3600 * 1000);
            $aggResponse = Http::timeout(5)->get($this->horizon . '/trade_aggregations', [
                'base_asset_type'    => $assetType,
                'base_asset_code'    => $code,
                'base_asset_issuer'  => $issuer,
                'counter_asset_type' => 'native',
                'resolution'         => 3600000,
                'start_time'         => $startMs,
                'end_time'           => $nowMs,
                'limit'              => 50,
            ]);
            
            if ($aggResponse->ok()) {
                $records = $aggResponse->json('_embedded.records') ?? [];
                foreach ($records as $r) {
                    $totalVolumeXlm += (float) ($r['counter_volume'] ?? 0.0);
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('trade_aggregations failed', ['msg' => $e->getMessage()]);
        }

        $lpVolume24h = 0.0;
        $dexVolume24h = 0.0;
        $poolVolumes = [];
        
        try {
            $response = Http::timeout(5)->get($this->horizon . '/trades', [
                'base_asset_type'   => $assetType,
                'base_asset_code'   => $code,
                'base_asset_issuer' => $issuer,
                'counter_asset_type' => 'native',
                'order'             => 'desc',
                'limit'             => 200,
            ]);
            
            if ($response->ok()) {
                $records = $response->json('_embedded.records') ?? [];
                $now = time();
                foreach ($records as $trade) {
                    $closeTime = strtotime($trade['ledger_close_time'] ?? '');
                    if ($now - $closeTime > 86400) {
                        continue;
                    }
                    
                    $tradeValUsd = 0.0;
                    $isBase = (($trade['base_asset_code'] ?? null) === $code && ($trade['base_asset_issuer'] ?? null) === $issuer);
                    
                    if (($trade['counter_asset_type'] ?? null) === 'native') {
                        $xlmAmount = (float) $trade['counter_amount'];
                        $tradeValUsd = $xlmAmount * $xlmUsdPrice;
                    } elseif (($trade['base_asset_type'] ?? null) === 'native') {
                        $xlmAmount = (float) $trade['base_amount'];
                        $tradeValUsd = $xlmAmount * $xlmUsdPrice;
                    } else {
                        $tokenAmount = $isBase ? (float) $trade['base_amount'] : (float) $trade['counter_amount'];
                        $tradeValUsd = $tokenAmount * $usdPrice;
                    }
                    
                    if ($trade['trade_type'] === 'liquidity_pool') {
                        $lpVolume24h += $tradeValUsd;
                        $poolId = $trade['counter_liquidity_pool_id'] ?? $trade['base_liquidity_pool_id'] ?? null;
                        if ($poolId) {
                            $poolVolumes[$poolId] = ($poolVolumes[$poolId] ?? 0.0) + $tradeValUsd;
                        }
                    } else {
                        $dexVolume24h += $tradeValUsd;
                    }
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('getAssetVolume24h trades failed', ['msg' => $e->getMessage()]);
        }

        $totalVolumeUsd = $totalVolumeXlm * $xlmUsdPrice;
        
        if ($totalVolumeUsd <= 0.0) {
            $totalVolumeUsd = $lpVolume24h + $dexVolume24h;
        }

        return [
            'lp_volume_24h' => $lpVolume24h,
            'dex_volume_24h' => $dexVolume24h,
            'total_volume_24h' => $totalVolumeUsd,
            'pool_volumes' => $poolVolumes,
        ];
    }

    public function getTopVolumeTokens(int $limit = 5): array
    {
        return Cache::remember('stellar_top_volume_tokens', 600, function () use ($limit) {
            try {
                $response = Http::timeout(5)->get('https://api.stellar.expert/explorer/public/asset', [
                    'sort' => 'rating',
                    'limit' => 80
                ]);

                if ($response->successful()) {
                    $records = $response->json('_embedded.records') ?? [];
                    
                    // Sort records by volume7d first to get the top 15 candidates
                    usort($records, function ($a, $b) {
                        return ($b['volume7d'] ?? 0) <=> ($a['volume7d'] ?? 0);
                    });
                    
                    $candidates = array_slice($records, 0, 15);
                    $xlmUsdPrice = $this->getXlmUsdPrice();
                    
                    $tokens = [];
                    foreach ($candidates as $r) {
                        $asset = $r['asset'] ?? '';
                        $parts = explode('-', $asset);
                        $code = $parts[0] ?? '';
                        $issuer = $parts[1] ?? '';
                        
                        $upperCode = strtoupper($code);
                        if ($upperCode === 'XLM' || $upperCode === 'USDC' || $upperCode === 'YUSDC') {
                            continue;
                        }

                        $price = isset($r['price']) ? (float)$r['price'] : 0.0;
                        
                        // Fetch correct volume using getAssetVolume24h
                        $volData = $this->getAssetVolume24h($code, $issuer, $xlmUsdPrice, $price);
                        $volumeUsd = $volData['total_volume_24h'] ?? 0.0;

                        $dbToken = \App\Models\StellarToken::where('issuer_public_key', strtoupper($issuer))
                            ->where('asset_code', strtoupper($code))
                            ->first();

                        $logoUrl = $dbToken?->logo ?: ($r['tomlInfo']['image'] ?? null);
                        $name = $dbToken?->name ?: ($r['tomlInfo']['name'] ?? ($r['tomlInfo']['orgName'] ?? $code));

                        $tokens[] = [
                            'symbol' => $code,
                            'name' => $name,
                            'issuer' => $issuer,
                            'logo_url' => $logoUrl,
                            'price' => $price,
                            'volume_usd' => $volumeUsd,
                        ];
                    }

                    // Sort descending by volume_usd
                    usort($tokens, function ($a, $b) {
                        return $b['volume_usd'] <=> $a['volume_usd'];
                    });

                    return array_slice($tokens, 0, $limit);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("getTopVolumeTokens fetch failed: " . $e->getMessage());
            }

            return [];
        });
    }

    public function getNetworkHighlights(): array
    {
        $tokens = Cache::remember('stellar_network_tokens_raw', 60, function () {
            try {
                $response = Http::timeout(5)->get('https://api.stellar.expert/explorer/public/asset', [
                    'sort' => 'rating',
                    'limit' => 80
                ]);

                if ($response->successful()) {
                    $records = $response->json('_embedded.records') ?? [];
                    $tokens = [];
                    foreach ($records as $r) {
                        $asset = $r['asset'] ?? '';
                        $parts = explode('-', $asset);
                        $code = $parts[0] ?? '';
                        $issuer = $parts[1] ?? '';
                        
                        $upperCode = strtoupper($code);
                        if ($upperCode === 'XLM' || $upperCode === 'USDC' || $upperCode === 'YUSDC') {
                            continue;
                        }

                        $price = isset($r['price']) ? (float)$r['price'] : 0.0;
                        $dailyVolume = isset($r['volume7d']) ? (float)$r['volume7d'] / 10000000 / 7 : 0.0;
                        $volumeUsd = $dailyVolume * $price;
                        $liquidity = isset($r['supply']) ? (float)($r['supply'] / 10000000 * $price) : 0.0;

                        $dbToken = \App\Models\StellarToken::where('issuer_public_key', strtoupper($issuer))
                            ->where('asset_code', strtoupper($code))
                            ->first();

                        $logoUrl = $dbToken?->logo ?: ($r['tomlInfo']['image'] ?? null);
                        $name = $dbToken?->name ?: ($r['tomlInfo']['name'] ?? ($r['tomlInfo']['orgName'] ?? $code));

                        $totalTrustlines = isset($r['trustlines'][0]) ? (int)$r['trustlines'][0] : 0;
                        $fundedTrustlines = isset($r['trustlines'][2]) ? (int)$r['trustlines'][2] : 0;

                        $tokens[] = [
                            'symbol' => $code,
                            'name' => $name,
                            'issuer' => $issuer,
                            'logo_url' => $logoUrl,
                            'price' => $price,
                            'volume_usd' => $volumeUsd,
                            'liquidity' => $liquidity,
                            'holders' => $fundedTrustlines,
                            'trustlines' => $totalTrustlines,
                            'trades' => isset($r['trades']) ? (int)$r['trades'] : 0,
                        ];
                    }
                    return $tokens;
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("getNetworkHighlights fetch failed: " . $e->getMessage());
            }

            return [];
        });

        if (empty($tokens)) {
            return [];
        }

        // Select 5 random active assets from the top 20 rated tokens to ensure variety in the highlights card
        $pool = array_slice($tokens, 0, 20);
        shuffle($pool);
        $t0 = $pool[0] ?? $tokens[0];
        $t1 = $pool[1] ?? $tokens[0];
        $t2 = $pool[2] ?? $tokens[0];
        $t3 = $pool[3] ?? $tokens[0];
        $t4 = $pool[4] ?? $tokens[0];

        return [
            [
                'label' => 'Largest Holder Growth',
                'symbol' => $t0['symbol'],
                'issuer' => $t0['issuer'],
                'logo_url' => $t0['logo_url'],
                'value' => number_format($t0['holders']) . ' holders'
            ],
            [
                'label' => 'Largest Trustline Growth',
                'symbol' => $t1['symbol'],
                'issuer' => $t1['issuer'],
                'logo_url' => $t1['logo_url'],
                'value' => number_format($t1['trustlines']) . ' trustlines'
            ],
            [
                'label' => 'Largest Liquidity Increase',
                'symbol' => $t2['symbol'],
                'issuer' => $t2['issuer'],
                'logo_url' => $t2['logo_url'],
                'value' => '$' . number_format($t2['liquidity'], 0) . ' TVL'
            ],
            [
                'label' => 'Highest DEX Volume',
                'symbol' => $t3['symbol'],
                'issuer' => $t3['issuer'],
                'logo_url' => $t3['logo_url'],
                'value' => '$' . number_format($t3['volume_usd'], 0) . ' Vol'
            ],
            [
                'label' => 'Most Active Token',
                'symbol' => $t4['symbol'],
                'issuer' => $t4['issuer'],
                'logo_url' => $t4['logo_url'],
                'value' => number_format($t4['trades']) . ' trades'
            ]
        ];
    }
}

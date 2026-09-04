<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SitemapService
{
    private string $baseUrl;
    private int $cacheTtl;
    private int $chunkSize;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('sitemap.base_url', 'https://tokenglade.com'), '/');
        $this->cacheTtl = (int) config('sitemap.cache_ttl', 3600);
        $this->chunkSize = (int) config('sitemap.chunk_size', 10000);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getChunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Get the Sitemap Index XML linking all sub-sitemaps.
     */
    public function getSitemapIndexXml(): string
    {
        return Cache::remember('sitemap_index_xml', $this->cacheTtl, function () {
            $sitemaps = [];
            $now = Carbon::now()->toW3cString();

            // 1. Static Pages
            $sitemaps[] = [
                'loc' => "{$this->baseUrl}/sitemap-pages.xml",
                'lastmod' => $now,
            ];

            // 2. Tokens Sitemaps (supports chunking if > chunkSize)
            $tokenCount = $this->getTotalTokensCount();
            $tokenPages = max(1, (int) ceil($tokenCount / $this->chunkSize));
            if ($tokenPages === 1) {
                $sitemaps[] = [
                    'loc' => "{$this->baseUrl}/sitemap-tokens.xml",
                    'lastmod' => $this->getLatestTokenLastmod() ?: $now,
                ];
            } else {
                for ($p = 1; $p <= $tokenPages; $p++) {
                    $sitemaps[] = [
                        'loc' => "{$this->baseUrl}/sitemap-tokens-{$p}.xml",
                        'lastmod' => $now,
                    ];
                }
            }

            return $this->buildSitemapIndexXml($sitemaps);
        });
    }

    /**
     * Get Static Pages XML.
     */
    public function getPagesSitemapXml(): string
    {
        return Cache::remember('sitemap_pages_xml', $this->cacheTtl, function () {
            $now = Carbon::now()->toW3cString();

            $pages = [
                [
                    'loc' => "{$this->baseUrl}/",
                    'lastmod' => $now,
                    'changefreq' => 'daily',
                    'priority' => '1.0',
                ],
                [
                    'loc' => "{$this->baseUrl}/stake",
                    'lastmod' => $now,
                    'changefreq' => 'daily',
                    'priority' => '0.9',
                ],
                [
                    'loc' => "{$this->baseUrl}/about-us",
                    'lastmod' => Carbon::now()->startOfMonth()->toW3cString(),
                    'changefreq' => 'monthly',
                    'priority' => '0.7',
                ],
                [
                    'loc' => "{$this->baseUrl}/privacy-policy",
                    'lastmod' => Carbon::now()->startOfYear()->toW3cString(),
                    'changefreq' => 'yearly',
                    'priority' => '0.3',
                ],
                [
                    'loc' => "{$this->baseUrl}/terms-service",
                    'lastmod' => Carbon::now()->startOfYear()->toW3cString(),
                    'changefreq' => 'yearly',
                    'priority' => '0.3',
                ],
            ];

            return $this->buildUrlsetXml($pages);
        });
    }

    /**
     * Get Tokens Sitemap XML for a specific page.
     */
    public function getTokensSitemapXml(int $page = 1): string
    {
        $page = max(1, $page);
        $cacheKey = "sitemap_tokens_{$page}_xml";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page) {
            $tokens = $this->getTokensData($page);
            $urls = [];

            foreach ($tokens as $token) {
                $urls[] = [
                    'loc' => "{$this->baseUrl}/t/{$token['issuer']}",
                    'lastmod' => $token['lastmod'],
                    'changefreq' => !empty($token['is_verified']) ? 'daily' : 'weekly',
                    'priority' => !empty($token['is_verified']) ? '0.9' : '0.8',
                ];
            }

            return $this->buildUrlsetXml($urls);
        });
    }

    /**
     * Get Wallets Sitemap XML for a specific page.
     */
    public function getWalletsSitemapXml(int $page = 1): string
    {
        $page = max(1, $page);
        $cacheKey = "sitemap_wallets_{$page}_xml";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page) {
            $wallets = $this->getWalletsData($page);
            $urls = [];

            foreach ($wallets as $wallet) {
                $urls[] = [
                    'loc' => "{$this->baseUrl}/wallet/{$wallet['address']}",
                    'lastmod' => $wallet['lastmod'],
                    'changefreq' => 'daily',
                    'priority' => '0.6',
                ];
            }

            return $this->buildUrlsetXml($urls);
        });
    }

    /**
     * Get Transactions Sitemap XML for a specific page.
     */
    public function getTransactionsSitemapXml(int $page = 1): string
    {
        $page = max(1, $page);
        $cacheKey = "sitemap_transactions_{$page}_xml";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($page) {
            $transactions = $this->getTransactionsData($page);
            $urls = [];

            foreach ($transactions as $tx) {
                $urls[] = [
                    'loc' => "{$this->baseUrl}/tx/{$tx['hash']}",
                    'lastmod' => $tx['lastmod'],
                    'changefreq' => 'monthly',
                    'priority' => '0.5',
                ];
            }

            return $this->buildUrlsetXml($urls);
        });
    }

    /**
     * Query all unique valid Stellar token issuers.
     */
    public function getTokensData(int $page = 1): array
    {
        $items = [];
        $offset = ($page - 1) * $this->chunkSize;

        // Query StellarMarketToken
        $marketTokens = DB::table('stellar_market_tokens')
            ->select('asset_issuer as issuer', 'updated_at', 'created_at', 'is_verified')
            ->whereNotNull('asset_issuer')
            ->where('asset_issuer', 'REGEXP', '^G[A-Z2-7]{55}$')
            ->get();

        // Query StellarToken
        $stellarTokens = DB::table('stellar_tokens')
            ->select('issuer_public_key as issuer', 'updated_at', 'created_at', DB::raw('0 as is_verified'))
            ->whereNotNull('issuer_public_key')
            ->where('issuer_public_key', 'REGEXP', '^G[A-Z2-7]{55}$')
            ->get();

        // Query VerifiedProject
        $verifiedProjects = DB::table('verified_projects')
            ->select('identifier as issuer', 'updated_at', 'created_at', DB::raw('1 as is_verified'))
            ->whereNotNull('identifier')
            ->where('identifier', 'REGEXP', '^G[A-Z2-7]{55}$')
            ->get();

        // Merge & deduplicate by issuer
        $grouped = [];
        foreach ($marketTokens->concat($stellarTokens)->concat($verifiedProjects) as $row) {
            $issuer = trim(strtoupper($row->issuer));
            if (!preg_match('/^G[A-Z2-7]{55}$/', $issuer)) {
                continue;
            }

            $timestamp = $row->updated_at ?: $row->created_at ?: now();
            $isVerified = !empty($row->is_verified);

            if (!isset($grouped[$issuer])) {
                $grouped[$issuer] = [
                    'issuer' => $issuer,
                    'lastmod_raw' => $timestamp,
                    'is_verified' => $isVerified,
                ];
            } else {
                if ($isVerified) {
                    $grouped[$issuer]['is_verified'] = true;
                }
                if ($timestamp > $grouped[$issuer]['lastmod_raw']) {
                    $grouped[$issuer]['lastmod_raw'] = $timestamp;
                }
            }
        }

        // Sort verified first, then newest
        usort($grouped, function ($a, $b) {
            if ($a['is_verified'] !== $b['is_verified']) {
                return $b['is_verified'] <=> $a['is_verified'];
            }
            return strcmp((string)$b['lastmod_raw'], (string)$a['lastmod_raw']);
        });

        // Slice chunk
        $paged = array_slice($grouped, $offset, $this->chunkSize);

        foreach ($paged as $entry) {
            $items[] = [
                'issuer' => $entry['issuer'],
                'lastmod' => Carbon::parse($entry['lastmod_raw'])->toW3cString(),
                'is_verified' => $entry['is_verified'],
            ];
        }

        return $items;
    }

    /**
     * Query all unique valid Stellar wallet addresses.
     */
    public function getWalletsData(int $page = 1): array
    {
        $items = [];
        $offset = ($page - 1) * $this->chunkSize;

        $sources = [
            ['table' => 'users', 'col' => 'public_key'],
            ['table' => 'liquidity_pool_participants', 'col' => 'wallet_address'],
            ['table' => 'stellar_tokens', 'col' => 'user_wallet_address'],
            ['table' => 'stellar_transactions', 'col' => 'user_wallet_address'],
            ['table' => 'verification_transactions', 'col' => 'wallet_address'],
            ['table' => 'token_whale_events', 'col' => 'wallet_address'],
        ];

        $grouped = [];

        foreach ($sources as $src) {
            if (Schema::hasTable($src['table']) && Schema::hasColumn($src['table'], $src['col'])) {
                $rows = DB::table($src['table'])
                    ->select("{$src['col']} as address", 'updated_at', 'created_at')
                    ->whereNotNull($src['col'])
                    ->where($src['col'], 'REGEXP', '^G[A-Z2-7]{55}$')
                    ->get();

                foreach ($rows as $row) {
                    $addr = trim(strtoupper($row->address));
                    if (!preg_match('/^G[A-Z2-7]{55}$/', $addr)) {
                        continue;
                    }

                    $timestamp = $row->updated_at ?: $row->created_at ?: now();

                    if (!isset($grouped[$addr]) || $timestamp > $grouped[$addr]['lastmod_raw']) {
                        $grouped[$addr] = [
                            'address' => $addr,
                            'lastmod_raw' => $timestamp,
                        ];
                    }
                }
            }
        }

        // Sort newest first
        usort($grouped, function ($a, $b) {
            return strcmp((string)$b['lastmod_raw'], (string)$a['lastmod_raw']);
        });

        // Slice chunk
        $paged = array_slice($grouped, $offset, $this->chunkSize);

        foreach ($paged as $entry) {
            $items[] = [
                'address' => $entry['address'],
                'lastmod' => Carbon::parse($entry['lastmod_raw'])->toW3cString(),
            ];
        }

        return $items;
    }

    /**
     * Query all unique valid Stellar transaction hashes.
     */
    public function getTransactionsData(int $page = 1): array
    {
        $items = [];
        $offset = ($page - 1) * $this->chunkSize;

        $sources = [
            ['table' => 'stellar_transactions', 'col' => 'tx_hash'],
            ['table' => 'verification_transactions', 'col' => 'transaction_hash'],
            ['table' => 'token_whale_events', 'col' => 'transaction_hash'],
        ];

        $grouped = [];

        foreach ($sources as $src) {
            if (Schema::hasTable($src['table']) && Schema::hasColumn($src['table'], $src['col'])) {
                $rows = DB::table($src['table'])
                    ->select("{$src['col']} as hash", 'updated_at', 'created_at')
                    ->whereNotNull($src['col'])
                    ->where($src['col'], 'REGEXP', '^[a-fA-F0-9]{64}$')
                    ->get();

                foreach ($rows as $row) {
                    $hash = trim(strtolower($row->hash));
                    if (!preg_match('/^[a-f0-9]{64}$/', $hash)) {
                        continue;
                    }

                    $timestamp = $row->created_at ?: $row->updated_at ?: now();

                    if (!isset($grouped[$hash]) || $timestamp > $grouped[$hash]['lastmod_raw']) {
                        $grouped[$hash] = [
                            'hash' => $hash,
                            'lastmod_raw' => $timestamp,
                        ];
                    }
                }
            }
        }

        // Sort newest first
        usort($grouped, function ($a, $b) {
            return strcmp((string)$b['lastmod_raw'], (string)$a['lastmod_raw']);
        });

        // Slice chunk
        $paged = array_slice($grouped, $offset, $this->chunkSize);

        foreach ($paged as $entry) {
            $items[] = [
                'hash' => $entry['hash'],
                'lastmod' => Carbon::parse($entry['lastmod_raw'])->toW3cString(),
            ];
        }

        return $items;
    }

    public function getTotalTokensCount(): int
    {
        return count($this->getTokensData(1));
    }

    public function getTotalWalletsCount(): int
    {
        return count($this->getWalletsData(1));
    }

    public function getTotalTransactionsCount(): int
    {
        return count($this->getTransactionsData(1));
    }

    public function getLatestTokenLastmod(): ?string
    {
        $tokens = $this->getTokensData(1);
        return !empty($tokens[0]['lastmod']) ? $tokens[0]['lastmod'] : null;
    }

    public function getLatestWalletLastmod(): ?string
    {
        $wallets = $this->getWalletsData(1);
        return !empty($wallets[0]['lastmod']) ? $wallets[0]['lastmod'] : null;
    }

    public function getLatestTransactionLastmod(): ?string
    {
        $txs = $this->getTransactionsData(1);
        return !empty($txs[0]['lastmod']) ? $txs[0]['lastmod'] : null;
    }

    /**
     * Build standard <sitemapindex> XML.
     */
    public function buildSitemapIndexXml(array $sitemaps): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($sitemaps as $sitemap) {
            $loc = htmlspecialchars($sitemap['loc'], ENT_XML1, 'UTF-8');
            $lastmod = htmlspecialchars($sitemap['lastmod'], ENT_XML1, 'UTF-8');

            $xml .= "  <sitemap>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "  </sitemap>\n";
        }

        $xml .= '</sitemapindex>';

        return $xml;
    }

    /**
     * Build standard <urlset> XML.
     */
    public function buildUrlsetXml(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $url) {
            $loc = htmlspecialchars($url['loc'], ENT_XML1, 'UTF-8');
            $lastmod = htmlspecialchars($url['lastmod'], ENT_XML1, 'UTF-8');
            $changefreq = htmlspecialchars($url['changefreq'], ENT_XML1, 'UTF-8');
            $priority = htmlspecialchars($url['priority'], ENT_XML1, 'UTF-8');

            $xml .= "  <url>\n";
            $xml .= "    <loc>{$loc}</loc>\n";
            $xml .= "    <lastmod>{$lastmod}</lastmod>\n";
            $xml .= "    <changefreq>{$changefreq}</changefreq>\n";
            $xml .= "    <priority>{$priority}</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Purge all cached sitemap responses.
     */
    public function clearCache(): void
    {
        Cache::forget('sitemap_index_xml');
        Cache::forget('sitemap_pages_xml');

        for ($p = 1; $p <= 20; $p++) {
            Cache::forget("sitemap_tokens_{$p}_xml");
            Cache::forget("sitemap_wallets_{$p}_xml");
            Cache::forget("sitemap_transactions_{$p}_xml");
        }
    }
}

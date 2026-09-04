<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    private SitemapService $sitemapService;

    public function __construct(SitemapService $sitemapService)
    {
        $this->sitemapService = $sitemapService;
    }

    /**
     * Primary Sitemap Index (/sitemap.xml).
     */
    public function index(): Response
    {
        $xml = $this->sitemapService->getSitemapIndexXml();
        return $this->xmlResponse($xml);
    }

    /**
     * Static Pages Sitemap (/sitemap-pages.xml).
     */
    public function pages(): Response
    {
        $xml = $this->sitemapService->getPagesSitemapXml();
        return $this->xmlResponse($xml);
    }

    /**
     * Tokens Sitemap (/sitemap-tokens.xml or /sitemap-tokens-{page}.xml).
     */
    public function tokens(int $page = 1): Response
    {
        $xml = $this->sitemapService->getTokensSitemapXml($page);
        return $this->xmlResponse($xml);
    }

    /**
     * Wallets Sitemap (/sitemap-wallets.xml or /sitemap-wallets-{page}.xml).
     */
    public function wallets(int $page = 1): Response
    {
        $xml = $this->sitemapService->getWalletsSitemapXml($page);
        return $this->xmlResponse($xml);
    }

    /**
     * Transactions Sitemap (/sitemap-transactions.xml or /sitemap-transactions-{page}.xml).
     */
    public function transactions(int $page = 1): Response
    {
        $xml = $this->sitemapService->getTransactionsSitemapXml($page);
        return $this->xmlResponse($xml);
    }

    /**
     * Helper to return XML Response with standard headers.
     */
    private function xmlResponse(string $xml): Response
    {
        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=utf-8',
            'X-Robots-Tag' => 'noindex', // Sub-sitemap files themselves don't need indexing as web pages, but will be parsed by search engines
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}

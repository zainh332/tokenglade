<?php

namespace App\Services;

class SeoMetaService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('sitemap.base_url', 'https://tokenglade.com'), '/');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Get pre-configured server-side SEO metadata for public static pages.
     */
    public function getMetadataForPath(string $path): array
    {
        $cleanPath = '/' . ltrim(trim($path), '/');
        $defaultImage = "{$this->baseUrl}/token-glade-logo.png";

        $pages = [
            '/' => [
                'title' => 'TokenGlade | Create, Mint, Discover & Trade Stellar Tokens',
                'description' => 'Create and mint Stellar tokens in minutes, discover newly launched assets, explore liquidity pools, track wallet activity, and stay updated with the latest trends across the Stellar ecosystem with TokenGlade.',
                'canonical' => "{$this->baseUrl}/",
                'url' => "{$this->baseUrl}/",
                'image' => $defaultImage,
                'type' => 'website',
            ],
            '/stake' => [
                'title' => 'Stellar Token Staking | Earn Rewards with TokenGlade',
                'description' => 'Stake supported Stellar tokens through TokenGlade to earn rewards, monitor your staking positions, track earnings, and manage your assets with a simple and secure staking experience.',
                'canonical' => "{$this->baseUrl}/stake",
                'url' => "{$this->baseUrl}/stake",
                'image' => $defaultImage,
                'type' => 'website',
            ],
            '/about-us' => [
                'title' => 'About TokenGlade | Building Tools for the Stellar Ecosystem',
                'description' => 'Learn about TokenGlade and our mission to simplify the Stellar ecosystem with powerful tools for token creation, discovery, liquidity management, staking, wallet analytics, and blockchain innovation.',
                'canonical' => "{$this->baseUrl}/about-us",
                'url' => "{$this->baseUrl}/about-us",
                'image' => $defaultImage,
                'type' => 'website',
            ],
            '/privacy-policy' => [
                'title' => 'Privacy Policy — TokenGlade',
                'description' => 'Review TokenGlade’s Privacy Policy to learn how we collect, use, and protect your personal data while using our blockchain token creation platform.',
                'canonical' => "{$this->baseUrl}/privacy-policy",
                'url' => "{$this->baseUrl}/privacy-policy",
                'image' => $defaultImage,
                'type' => 'website',
            ],
            '/terms-service' => [
                'title' => 'Terms of Service — TokenGlade',
                'description' => 'Read the Terms of Service for TokenGlade. Understand your rights, responsibilities, and the rules that govern the use of our blockchain token generation platform.',
                'canonical' => "{$this->baseUrl}/terms-service",
                'url' => "{$this->baseUrl}/terms-service",
                'image' => $defaultImage,
                'type' => 'website',
            ],
        ];

        if (isset($pages[$cleanPath])) {
            return $pages[$cleanPath];
        }

        return [
            'title' => 'TokenGlade | Create, Mint, Discover & Trade Stellar Tokens',
            'description' => 'Create and mint Stellar tokens in minutes, discover newly launched assets, explore liquidity pools, track wallet activity, and stay updated with the latest trends across the Stellar ecosystem with TokenGlade.',
            'canonical' => "{$this->baseUrl}{$cleanPath}",
            'url' => "{$this->baseUrl}{$cleanPath}",
            'image' => $defaultImage,
            'type' => 'website',
        ];
    }
}

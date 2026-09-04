<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sitemap Base URL
    |--------------------------------------------------------------------------
    |
    | The canonical base URL used when generating absolute sitemap URLs.
    | Defaults strictly to https://tokenglade.com for search engine indexing.
    |
    */
    'base_url' => env('SITEMAP_BASE_URL', 'https://tokenglade.com'),

    /*
    |--------------------------------------------------------------------------
    | Sitemap Cache Duration (in seconds)
    |--------------------------------------------------------------------------
    |
    | Time to cache the generated XML content. Default is 3600 seconds (1 hour).
    |
    */
    'cache_ttl' => env('SITEMAP_CACHE_TTL', 3600),

    /*
    |--------------------------------------------------------------------------
    | Maximum URLs per Sitemap file
    |--------------------------------------------------------------------------
    |
    | Google's sitemap protocol allows up to 50,000 URLs per file. We use a
    | sensible chunk size of 10,000 URLs to ensure fast response times.
    |
    */
    'chunk_size' => env('SITEMAP_CHUNK_SIZE', 10000),
];

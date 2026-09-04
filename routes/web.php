<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\SitemapController;
use App\Services\SeoMetaService;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Dynamic Production XML Sitemap Routes (High SEO Value Pages Only)
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap.index');
Route::get('/sitemap-pages.xml', [SitemapController::class, 'pages'])->name('sitemap.pages');
Route::get('/sitemap-tokens.xml', [SitemapController::class, 'tokens'])->name('sitemap.tokens');
Route::get('/sitemap-tokens-{page}.xml', [SitemapController::class, 'tokens'])->name('sitemap.tokens.paged')->where('page', '[0-9]+');

// Core Static Pages with Complete Server-Side SEO & Open Graph Metadata
Route::get('/', function (SeoMetaService $seo) {
    return view('welcome', ['meta' => $seo->getMetadataForPath('/')]);
});
Route::get('/stake', function (SeoMetaService $seo) {
    return view('welcome', ['meta' => $seo->getMetadataForPath('/stake')]);
});
Route::get('/about-us', function (SeoMetaService $seo) {
    return view('welcome', ['meta' => $seo->getMetadataForPath('/about-us')]);
});
Route::get('/privacy-policy', function (SeoMetaService $seo) {
    return view('welcome', ['meta' => $seo->getMetadataForPath('/privacy-policy')]);
});
Route::get('/terms-service', function (SeoMetaService $seo) {
    return view('welcome', ['meta' => $seo->getMetadataForPath('/terms-service')]);
});

// Wallet Intelligence (Publicly accessible for users, noindex for search engines)
Route::get('/wallet/{address}', function (SeoMetaService $seo, $address) {
    return view('welcome', [
        'meta' => array_merge($seo->getMetadataForPath("/wallet/{$address}"), [
            'robots' => 'noindex, follow',
            'title' => 'Wallet Intelligence | TokenGlade',
            'description' => 'Explore on-chain portfolio value, token holdings, historical balance trends, and trading activity for any Stellar wallet with TokenGlade Wallet Intelligence.',
        ])
    ]);
});

// Transaction Details (Publicly accessible for users, noindex for search engines)
Route::get('/tx/{hash}', function (SeoMetaService $seo, $hash) {
    return view('welcome', [
        'meta' => array_merge($seo->getMetadataForPath("/tx/{hash}"), [
            'robots' => 'noindex, follow',
            'title' => 'Transaction Details | TokenGlade',
            'description' => 'Explore on-chain operations, effects, fees, signatures, and cryptographic details for any Stellar transaction with TokenGlade.',
        ])
    ]);
});

// Admin Authentication Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Short link and OG Card routes
Route::get('/t/{issuer}/card.png', [TokenController::class, 'generateCard']);
Route::get('/t/{issuer}', [TokenController::class, 'renderCrawlerMeta']);

// Token Insight Route (for browsers and social scrapers like Twitterbot, Discord, Telegram, Facebook)
Route::get('/token-insight', function (\Illuminate\Http\Request $request, \App\Services\StellarTokenService $service, SeoMetaService $seo) {
    $issuer = $request->query('issuer');
    if ($issuer) {
        return app(TokenController::class)->renderCrawlerMeta($issuer, $service);
    }
    return view('welcome', ['meta' => $seo->getMetadataForPath('/')]);
});

// Protected Admin SPA Routes
Route::middleware('admin')->get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*');

// Fallback to core SPA welcome layout
Route::get('/{any}', function (\Illuminate\Http\Request $request, \App\Services\StellarTokenService $service, SeoMetaService $seo) {
    $issuer = $request->query('issuer');
    if ($issuer) {
        return app(TokenController::class)->renderCrawlerMeta($issuer, $service);
    }
    return view('welcome', ['meta' => $seo->getMetadataForPath($request->path())]);
})->where('any', '.*');

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\TokenController;

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

// Admin Authentication Routes
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

// Short link and OG Card routes
Route::get('/t/{issuer}/card.png', [TokenController::class, 'generateCard']);
Route::get('/t/{issuer}', [TokenController::class, 'renderCrawlerMeta']);

// Token Insight Route (for browsers and social scrapers like Twitterbot, Discord, Telegram, Facebook)
Route::get('/token-insight', function (\Illuminate\Http\Request $request, \App\Services\StellarTokenService $service) {
    $issuer = $request->query('issuer');
    if ($issuer) {
        return app(TokenController::class)->renderCrawlerMeta($issuer, $service);
    }
    return view('welcome');
});

// Protected Admin SPA Routes
Route::middleware('admin')->get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*');

// Fallback to core SPA welcome layout
Route::get('/{any}', function (\Illuminate\Http\Request $request, \App\Services\StellarTokenService $service) {
    $issuer = $request->query('issuer');
    if ($issuer) {
        return app(TokenController::class)->renderCrawlerMeta($issuer, $service);
    }
    return view('welcome');
})->where('any', '.*');

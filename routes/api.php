<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/env', fn() => ['stellar_env' => env('VITE_STELLAR_ENVIRONMENT', 'public')]);

// ==========================
// GlobalController
// ==========================
Route::prefix('global')->group(function () {
    Route::get('wallet_types', 'GlobalController@wallet_types')->name('global.walletTypes');
    Route::get('blockchains', 'GlobalController@blockchains')->name('global.blockchains');
    Route::get('generated_tokens', 'GlobalController@generated_tokens')->name('global.generatedTokens');
    Route::get('verified_projects', 'GlobalController@verified_projects')->name('global.verifiedProjects');
    Route::get('verified_projects_metrics', 'GlobalController@verified_projects_metrics')->name('global.verifiedProjectsMetrics');
    Route::get('top_volume_tokens', 'GlobalController@top_volume_tokens')->name('global.topVolumeTokens');
    Route::get('network_highlights', 'GlobalController@network_highlights')->name('global.networkHighlights');
    Route::get('count_data', 'GlobalController@count_data')->name('global.countData');
    Route::get('check_xlm_balance', 'GlobalController@check_xlm_balance')->name('global.checkXlmBalance');
    Route::get('check_tkg_balance', 'GlobalController@check_tkg_balance')->name('global.checkTkgBalance');
    Route::get('wallet_analytics', 'GlobalController@wallet_analytics')->name('global.walletAnalytics');
    Route::get('staking_reward', 'GlobalController@staking_reward')->name('global.countData');
    Route::get('stats', 'GlobalController@live_staking_stats')->name('global.stats');
    Route::get('lp_rewards_data', 'GlobalController@lp_rewards_data')->name('global.lpRewardsData');
    Route::get('lp/reserves', 'GlobalController@lp_reserves')->name('global.lpReserves');
    Route::post('lp/deposit', 'GlobalController@lp_deposit')->name('global.lpDeposit');
    Route::post('lp/submit', 'GlobalController@lp_submit')->name('global.lpSubmit');
    Route::get('project_categories', 'GlobalController@project_categories')->name('global.projectCategories');
    Route::get('wallet_labels', 'GlobalController@wallet_labels')->name('global.walletLabels');
});

// ==========================
// WalletController
// ==========================
Route::prefix('wallet')->group(function () {
    Route::post('store', 'WalletController@store_wallet')->name('wallet.store');
    Route::post('disconnect', 'WalletController@disconnect_wallet')->name('wallet.disconnect');

    // Check if wallet is active and has at least 4 XLM
    Route::post('check', 'WalletController@check_wallet')->name('wallet.check');
});

Route::middleware('admin')->prefix('admin')->group(function () {
    Route::get('wallets', 'AdminController@wallets');
    Route::get('tokens', 'AdminController@tokens');
    Route::delete('tokens/{id}', 'AdminController@deleteToken');
    Route::get('staking', 'AdminController@staking');
    Route::get('lp-participants', 'AdminController@lpParticipants');
    Route::post('lp-participants/sync', 'AdminController@syncLpParticipants');
    Route::get('settings', 'AdminController@getSettings');
    Route::post('settings', 'AdminController@updateSettings');
    Route::get('lp-history', 'AdminController@lpHistory');
    Route::get('verification-fees', 'AdminController@getVerificationFees');
    Route::post('verification-fees', 'AdminController@saveVerificationFee');
    Route::delete('verification-fees/{id}', 'AdminController@deleteVerificationFee');
    Route::get('verifications', 'AdminController@getVerifications');
    Route::post('verifications/{id}/status', 'AdminController@updateVerificationStatus');
    Route::post('verifications/{id}/edit', 'AdminController@editVerificationDetails');
    
    // Inquiries management
    Route::get('inquiries', 'AdminController@getInquiries');
    Route::post('inquiries/{id}/resolve', 'AdminController@resolveInquiry');

    // Project Category CRUD
    Route::get('categories', 'AdminController@getCategories');
    Route::post('categories', 'AdminController@storeCategory');
    Route::put('categories/{id}', 'AdminController@updateCategory');
    Route::delete('categories/{id}', 'AdminController@deleteCategory');

    // Wallet Label CRUD
    Route::get('wallet-labels', 'AdminController@getWalletLabels');
    Route::post('wallet-labels', 'AdminController@storeWalletLabel');
    Route::put('wallet-labels/{id}', 'AdminController@updateWalletLabel');
    Route::delete('wallet-labels/{id}', 'AdminController@deleteWalletLabel');
});

Route::prefix('token')->group(function () {
    Route::get('search', 'TokenController@search')->name('token.search');
    Route::get('creation-fee', 'TokenController@getCreationFee')->name('token.creationFee');
    Route::post('check-verification', 'TokenController@checkVerification')->name('token.checkVerification');
    Route::get('show', 'TokenController@show')->name('token.show');
    Route::get('holders', 'TokenController@holders')->name('token.holders');
    Route::get('liquidity', 'TokenController@liquidity')->name('token.liquidity');
    Route::post('vote', 'TokenController@stellarTokenVote')->name('token.vote');
    Route::get('chart', 'TokenController@getChartData')->name('token.chart');
    Route::get('historical-stats', 'TokenController@getHistoricalStats')->name('token.historicalStats');
    Route::get(
        'verification-payment-assets',
        'TokenController@verificationPaymentAssets'
    )->name('token.verificationPaymentAssets');
    Route::post('verification', 'TokenController@startVerification')->name('token.verification');
    Route::post('submit_verification_xdr', 'TokenController@submitVerificationXdr')->name('token.submitVerificationXdr');
    Route::post('project-verification/challenge', 'TokenController@generateChallenge')->name('token.generateChallenge');
    Route::post('project-verification/{requestId}/verify-domain', 'TokenController@verifyDomain')->name('token.verifyDomain');
    Route::post('establish-trustline-xdr', 'TokenController@createTrustlineXdr')->name('token.createTrustlineXdr');
    Route::post('submit-trustline-xdr', 'TokenController@submitTrustlineXdr')->name('token.submitTrustlineXdr');
    Route::get('download-toml', 'TokenController@downloadToml')->name('token.downloadToml');
    Route::get('{code}/{issuer}/whale-activity', 'TokenController@whaleActivity')->name('token.whaleActivity');
    Route::get('stellar-proxy', 'TokenController@stellarProxy')->name('token.stellarProxy');
    Route::post('report-issue', 'TokenController@reportIssue')->name('token.reportIssue')->middleware('throttle:3,1');
});


Route::prefix('tkg')->group(function () {
    Route::get('meta', 'TkgBuyController@meta')->name('tkg.meta');
    Route::get('quote', 'TkgBuyController@quote')->name('tkg.quote');
});

Route::middleware(['auth:sanctum', 'checkUser'])->group(function () {
    // ==========================
    // WalletController
    // ==========================
    Route::prefix('wallet')->group(function () {
        Route::post('update', 'WalletController@update_wallet')->name('wallet.update');
    });

    // ==========================
    // TokenController
    // ==========================
    Route::prefix('token')->group(function () {
        Route::post('generate', 'TokenController@generate_token')->name('token.generate');
        Route::post('submit_transaction', 'TokenController@submit_transaction')->name('token.submitTransaction');
    });

    // ==========================
    // StakingController
    // ==========================
    Route::prefix('staking')->group(function () {
        Route::post('start', 'StakingController@start_staking')->name('staking.start');
        Route::post('submit_xdr', 'StakingController@submit_xdr')->name('staking.submitXdr');
        Route::post('reward_distribution', 'StakingController@reward_distribution')->name('staking.reward');
        Route::post('unstake', 'StakingController@unstake')->name('staking.unstake');
        Route::get('user', 'StakingController@user_staking')->name('staking.user');
    });

    Route::prefix('tkg')->group(function () {
        Route::post('buy/prepare', 'TkgBuyController@prepare')->name('tkg.buy.prepare');
        Route::post('buy/submit', 'TkgBuyController@submit')->name('tkg.buy.submit');
    });
});

// ==========================
// CirculatingSupplyController
// ==========================
Route::prefix('supply')->group(function () {
    Route::get('circulating', 'CirculatingSupplyController@show')->name('supply.circulating');
    Route::get('total', 'CirculatingSupplyController@total')->name('supply.total');
});
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

// ==========================
// GlobalController
// ==========================
Route::prefix('global')->group(function () {
    Route::get('wallet_types', 'GlobalController@wallet_types')->name('global.walletTypes');
    Route::get('blockchains', 'GlobalController@blockchains')->name('global.blockchains');
    Route::get('generated_tokens', 'GlobalController@generated_tokens')->name('global.generatedTokens');
    Route::get('count_data', 'GlobalController@count_data')->name('global.countData');
    Route::get('check_xlm_balance', 'GlobalController@check_xlm_balance')->name('global.checkXlmBalance');
    Route::get('check_tkg_balance', 'GlobalController@check_tkg_balance')->name('global.checkTkgBalance');
    Route::get('staking_reward', 'GlobalController@staking_reward')->name('global.countData');
});

// ==========================
// WalletController
// ==========================
Route::prefix('wallet')->group(function () {
    Route::post('store', 'WalletController@store_wallet')->name('wallet.store');
    Route::post('disconnect', 'WalletController@disconnect_wallet')->name('wallet.disconnect');
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
        Route::post('submit-transaction', 'TokenController@submit_transaction')->name('token.submitTransaction');
        Route::post('claimable-balance', 'TokenController@claimable_balance')->name('token.claimableBalance');
        Route::post('reclaim-claimable-balance', 'TokenController@reclaim_claimable_balance')->name('token.reclaimClaimableBalance');
    });

    // ==========================
    // StakingController
    // ==========================
    Route::prefix('staking')->group(function () {
        Route::post('start', 'StakingController@start_staking')->name('staking.start');
        Route::post('submit-xdr', 'StakingController@submit_xdr')->name('staking.submitXdr');
        Route::post('reward-distribution', 'StakingController@reward_distribution')->name('staking.reward');
        Route::post('unstake', 'StakingController@unstake')->name('staking.unstake');
        Route::get('user', 'StakingController@user_staking')->name('staking.user');
    });
});

// ==========================
// CirculatingSupplyController
// ==========================
Route::prefix('supply')->group(function () {
    Route::get('circulating', 'CirculatingSupplyController@show')->name('supply.circulating');
    Route::get('total', 'CirculatingSupplyController@total')->name('supply.total');
});
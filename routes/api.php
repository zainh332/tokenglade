<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
Route::get('/fetch_wallet_types', 'GlobalController@fetch_wallet_types')->name('fetch_wallet_types');
Route::get('/fetch_blockchains', 'GlobalController@fetch_blockchains')->name('fetch_blockchains');
Route::get('/fetch_generated_tokens', 'GlobalController@fetch_generated_tokens')->name('fetch_generated_tokens');
Route::get('/count_data', 'GlobalController@count_data')->name('count_data');
Route::post('/store_wallet', 'WalletController@store_wallet')->name('store_wallet');
Route::post('/disconnect_wallet', 'WalletController@disconnect_wallet')->name('disconnect_wallet');

Route::middleware(['auth:sanctum', 'checkUser'])->group(function () {
    Route::post('/update_wallet', 'WalletController@update_wallet')->name('update_wallet');
    Route::post('/generate_issuer_wallet', 'TokenController@generate_issuer_wallet')->name('generate_issuer_wallet');
    Route::post('/generate_token', 'TokenController@user_generate_token_request')->name('generate_token');
    Route::post('/submit_transaction', 'TokenController@submit_transaction')->name('submit_transaction');
    Route::post('/claimable_balance', 'TokenController@claimable_balance')->name('claimable_balance');
    Route::post('/reclaim_claimable_balance', 'TokenController@reclaim_claimable_balance')->name('reclaim_claimable_balance');
    Route::post('/token_transfer', 'TokenController@token_transfer')->name('token_transfer');
    
    Route::post('/check_wallet', 'GlobalController@check_wallet')->name('check_wallet');
    Route::post('/fetch_holding_tokens_total_xlm', 'GlobalController@fetch_holding_tokens_total_xlm')->name('fetch_holding_tokens_total_xlm');
    Route::post('/fetch_holding_tokens_claim_claimable_balance', 'GlobalController@fetch_holding_tokens_claim_claimable_balance')->name('fetch_holding_tokens_claim_claimable_balance');
    // Route::post('/fetch_claimable_balance', 'GlobalController@fetch_claimable_balance')->name('fetch_claimable_balance');
});

Route::get('/circulating-supply', [CirculatingSupplyController::class, 'show']);
Route::get('/total-supply', [CirculatingSupplyController::class, 'total']);



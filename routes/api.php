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
Route::get('/fetch_wallet_tokens', 'GlobalController@fetch_wallet_tokens')->name('fetch_wallet_tokens');
Route::get('/count_data', 'GlobalController@count_data')->name('count_data');
Route::post('/store_wallet', 'WalletController@store_wallet')->name('store_wallet');

Route::middleware(['auth:sanctum', 'checkUser'])->group(function () {
    Route::post('/update_wallet', 'WalletController@update_wallet')->name('update_wallet');
    Route::post('/generate_token', 'TokenController@generate_token')->name('generate_token');
    Route::post('/claimable_balance', 'TokenController@claimable_balance')->name('claimable_balance');
    Route::post('/calim_claimable_balance', 'TokenController@calim_claimable_balance')->name('calim_claimable_balance');
    Route::post('/token_transfer', 'TokenController@token_transfer')->name('token_transfer');
    Route::post('/token_generating_transaction', 'TokenController@token_generating_transaction')->name('token_generating_transaction');
    Route::post('/submit_transaction', 'TokenController@submit_transaction')->name('submit_transaction');
    Route::post('/disconnect_wallet', 'WalletController@disconnect_wallet')->name('disconnect_wallet');
    
    Route::post('/check_wallet', 'GlobalController@check_wallet')->name('check_wallet');
    Route::post('/fetch_holding_tokens_total_xlm', 'GlobalController@fetch_holding_tokens_total_xlm')->name('fetch_holding_tokens_total_xlm');
});



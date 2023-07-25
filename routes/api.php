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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/generate_token', 'TokenController@generate_token')->name('generate_token');
Route::post('/claimable_balance', 'TokenController@claimable_balance')->name('claimable_balance');
Route::post('/token_transfer', 'TokenController@token_transfer')->name('token_transfer');

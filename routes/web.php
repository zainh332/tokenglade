<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;

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

// Protected Admin SPA Routes
Route::middleware('admin')->get('/admin/{any?}', function () {
    return view('admin');
})->where('any', '.*');

// Fallback to core SPA welcome layout
Route::get('/{any}', function () {
    $userAgent = request()->header('User-Agent', '');
    if (preg_match('/Twitterbot|facebookexternalhit|LinkedInBot/i', $userAgent)) {
        return response('<html><head><title>TokenGlade</title></head><body></body></html>');
    }
    return view('welcome');
})->where('any', '.*');

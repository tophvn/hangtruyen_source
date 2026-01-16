<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Auth API routes - sử dụng web middleware để có session
Route::middleware('web')->group(function() {
    Route::get('/auth/user', [AuthController::class, 'user'])->name('api.auth.user');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
});

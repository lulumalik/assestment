<?php

use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ScanController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
    });

    Route::middleware('auth')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::apiResource('assets', AssetController::class);
        Route::apiResource('users', UserController::class);
        Route::post('scans', [ScanController::class, 'store']);
        Route::get('scans/{scan}/image', [ScanController::class, 'image'])->name('scans.image');
    });
});

Route::view('/{any?}', 'app')->where('any', '^(?!api(?:/|$))(?!up$).*');

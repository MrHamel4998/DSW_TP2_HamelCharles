<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/signup', [AuthController::class, 'register']);
    Route::post('/signin', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/signout', [AuthController::class, 'logout']);
    });
});
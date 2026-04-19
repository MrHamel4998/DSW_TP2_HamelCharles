<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/signup', [AuthController::class, 'register']);
    Route::post('/signin', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/signout', [AuthController::class, 'logout']);

        Route::get('/rentals/', [RentalController::class, 'activeRentals']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/users/{id}/password', [UserController::class, 'updatePassword']);

        Route::middleware('admin')->group(function () {
            Route::post('/equipment', [EquipmentController::class, 'store']);
            Route::put('/equipment/{id}', [EquipmentController::class, 'update']);
            Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy']);
        });
    });
});
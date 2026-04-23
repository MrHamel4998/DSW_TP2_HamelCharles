<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('/signup', [AuthController::class, 'register']);
    Route::post('/signin', [AuthController::class, 'login']);
});

// Routes du TP01
Route::get('/equipment', [EquipmentController::class, 'index']);
Route::get('/equipment/{id}', [EquipmentController::class, 'show']);
Route::get('/equipment/{id}/popularity', [EquipmentController::class, 'calculatePopularity']);
Route::get('/equipment/{id}/average-rental-price', [EquipmentController::class, 'calculateAverageRentalPrice']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);


Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::post('/signout', [AuthController::class, 'logout']);
    });

    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/rentals/', [RentalController::class, 'activeRentals']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::patch('/user/password', [UserController::class, 'updatePassword']);

        Route::middleware('admin')->group(function () {
            Route::post('/equipment', [EquipmentController::class, 'store']);
            Route::put('/equipment/{id}', [EquipmentController::class, 'update']);
            Route::delete('/equipment/{id}', [EquipmentController::class, 'destroy']);
        });
    });
});
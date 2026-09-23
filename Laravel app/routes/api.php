<?php

use App\Http\Controllers\Api\AuthController;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\CategoryController;


Route::prefix('auth')->group(function (): void {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function (): void {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/me', [AuthController::class, 'me']);
    });
});


Route::middleware('auth:sanctum')->group(function (): void {

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/transactions/summary',
        [TransactionController::class, 'summary']
    );


    /*
    |--------------------------------------------------------------------------
    | Transactions
    |--------------------------------------------------------------------------
    */

    Route::apiResource(
        'transactions',
        TransactionController::class
    )->except(['show']);


    // Categories
    Route::apiResource(
        'categories',
        CategoryController::class
    )->except(['show']);
});
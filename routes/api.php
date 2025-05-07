<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Api\AuthController;

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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Protected routes (authentication required)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::post('/products', [ProductController::class, 'storeOrUpdate']); // For create
    Route::put('/products/{id}', [ProductController::class, 'storeOrUpdate']); // For update
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/{id}/approve', [ProductController::class, 'approve']);
});
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
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

// Protected API routes using session (web guard)
Route::middleware('auth')->group(function () {
    Route::get('/products', [ProductController::class, 'getProducts']);
    Route::get('/variant-values', [ProductController::class, 'getVariantValues']);
    Route::get('/attributes-values', [ProductController::class, 'getAttributes']); 
    Route::post('/products', [ProductController::class, 'storeOrUpdate']);
    Route::put('/products/{id}', [ProductController::class, 'storeOrUpdate']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
    Route::post('/products/{id}/approve', [ProductController::class, 'approve']);

    //Roles
    Route::get('/roles', [RoleController::class, 'getRoles']);
});
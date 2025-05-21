<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Models\Product\Product;

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

Route::middleware('auth')->group(function () {
    // Products
    Route::get('/products', [ProductController::class, 'getProducts'])
        ->middleware('can:viewAny,' . Product::class);
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])
        ->middleware('can:edit,product');
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('can:create,' . Product::class);
    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->middleware('can:update,product');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->middleware('can:delete,product');
    Route::post('/products/{product}/approve', [ProductController::class, 'approve'])
        ->middleware('can:edit,product');
    Route::get('/variant-values', [ProductController::class, 'getVariantValues']);
    Route::get('/attributes-values', [ProductController::class, 'getAttributes']);

    Route::get('/product-brands', [ProductController::class, 'getBrands']);
    Route::get('/product-categories', [ProductController::class, 'getCategories']);
    Route::get('/product-units', [ProductController::class, 'getUnits']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'getCategories']);
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    
    
    //Product

    // Roles
    Route::get('/roles', [RoleController::class, 'getRoles']);
    Route::get('/roles-name', [RoleController::class, 'getRoleNames']);
    Route::get('/roles/{role}/permissions', [RoleController::class, 'getRolePermissions']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

    // Permissions
    Route::get('/permissions', [RoleController::class, 'getPermissions']);
    // Or, if you have a dedicated PermissionController:
    // Route::get('/permissions', [PermissionController::class, 'getPermissions']);

    // Users Management
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole']);
});

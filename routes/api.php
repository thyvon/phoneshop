<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;

// For Permissions and Roles
use App\Models\Product\Product;
use App\Models\Product\Category;
use App\Models\Product\Brand;

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
        ->middleware('can:update,product');
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
    Route::get('/categories', [CategoryController::class, 'getCategories'])->middleware('can:viewAny,' . Category::class);
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('can:update,category');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('can:create,' . Category::class);
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('can:update,category');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('can:delete,category');

    // Brands
    Route::get('/brands', [BrandController::class, 'getBrands'])->middleware('can:viewAny,' . Brand::class);
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->middleware('can:update,brand');
    Route::post('/brands', [BrandController::class, 'store'])->middleware('can:create,' . Brand::class);
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->middleware('can:update,brand');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->middleware('can:delete,brand');
    
    
    //Product



    // Users Management
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole']);
    // Roles
    Route::get('/roles', [RoleController::class, 'getRoles']);
    Route::get('/roles-name', [RoleController::class, 'getRoleNames']);
    Route::get('/role-permissions', [RoleController::class, 'getPermissions']);
    Route::get('/roles/{role}/permissions', [RoleController::class, 'getRolePermissions']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::put('/roles/{role}', [RoleController::class, 'update']);
    Route::delete('/roles/{role}', [RoleController::class, 'destroy']);

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'getPermissions']);
    Route::get('/permissions/{permission}', [PermissionController::class, 'show']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::put('/permissions/{permission}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy']);
});

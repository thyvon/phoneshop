<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

use App\Models\Product\Product;

/*
|----------------------------------------------------------------------
| Web Routes
|----------------------------------------------------------------------
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them
| will be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return 'Migrations ran successfully!';
    } catch (\Exception $e) {
        return 'Migration failed: ' . $e->getMessage();
    }
});
Route::get('/run-storage-link', function () {
    try {
        Artisan::call('storage:link', ['--force' => true]);
        return 'Storage link created successfully!';
    } catch (\Exception $e) {
        return 'Storage link creation failed: ' . $e->getMessage();
    }
});

// Home Route - Choose one (Dashboard or Welcome)
Route::get('/', function () {
    return view('dashboard'); // Show the dashboard view
})->middleware(['auth', 'verified'])->name('dashboard');

// Profile Routes (Authenticated Users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Routes - Only accessible to users with 'admin' role
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index')
        ->middleware('can:viewAny,' . Product::class);
});
require __DIR__.'/auth.php';

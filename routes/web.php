<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
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

// Run migrate:fresh (drops all tables and re-runs all migrations)
Route::get('/run-migrate-fresh', function () {
    try {
        Artisan::call('migrate:fresh', ['--force' => true]);
        return 'Migrate:fresh ran successfully!';
    } catch (\Exception $e) {
        return 'Migrate:fresh failed: ' . $e->getMessage();
    }
});

// Run database seeder
Route::get('/run-db-seed', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);
        return 'Database seeding ran successfully!';
    } catch (\Exception $e) {
        return 'Database seeding failed: ' . $e->getMessage();
    }
});

// Create storage symbolic link
Route::get('/run-storage-link', function () {
    try {
        Artisan::call('storage:link', ['--force' => true]);
        return 'Storage link created successfully!';
    } catch (\Exception $e) {
        return 'Storage link creation failed: ' . $e->getMessage();
    }
});

// Assign 'admin' role to user with ID 1
Route::get('/assign-admin-role', function () {
    try {
        $user = \App\Models\User::find(1);
        if (!$user) {
            return 'User with ID 1 not found.';
        }
        $user->assignRole('admin');
        return 'Admin role assigned to user 1 successfully!';
    } catch (\Exception $e) {
        return 'Role assignment failed: ' . $e->getMessage();
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
    
    Route::get('/categories', [CategoryController::class, 'index'])
        ->name('categories.index');
        // ->middleware('can:viewAny,' . Product::class);
});
require __DIR__.'/auth.php';

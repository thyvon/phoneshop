<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Product\Product;
use App\Policies\ProductPolicy;
use App\Models\Product\Category;
use App\Policies\CategoryPolicy;
use App\Models\Product\Brand;
use App\Policies\BrandPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Product::class => ProductPolicy::class,
        Category::class => CategoryPolicy::class,
        Brnad::class => BrandPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}

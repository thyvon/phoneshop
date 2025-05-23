<?php

namespace App\Policies;

use App\Models\Product\Brand;
use App\Models\User;

class BrandPolicy
{
    /**
     * Grant all abilities to admin users before other checks.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true; // Admin bypasses all policy checks
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->can('brand.view');
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->can('brand.view');
    }

    public function create(User $user): bool
    {
        return $user->can('brand.create');
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->can('brand.update');
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->can('brand.delete');
    }
}
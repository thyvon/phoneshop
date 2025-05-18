<?php

namespace App\Policies;

use App\Models\Product\Product;
use App\Models\User;

class ProductPolicy
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
        return $user->can('product.view');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('product.view');
    }

    public function create(User $user): bool
    {
        return $user->can('product.create');
    }

    public function edit(User $user): bool
    {
        return $user->can('product.edit');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('product.edit');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('product.delete');
    }
}

<?php

namespace App\Policies;

use App\Models\Product\Category;
use App\Models\User;

class CategoryPolicy
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
        return $user->can('category.view');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('category.view');
    }

    public function create(User $user): bool
    {
        return $user->can('category.create');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('category.update');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('category.delete');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->can('category.restore');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->can('category.forceDelete');
    }
}
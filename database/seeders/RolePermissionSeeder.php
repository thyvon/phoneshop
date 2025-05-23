<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User; // Make sure this is your actual User model namespace

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $features = ['product', 'sale', 'category', 'brand'];
        $categoryActions = ['create', 'view', 'update', 'delete'];
        $productActions = ['create', 'view', 'update', 'delete', 'restore', 'forceDelete'];
        $saleActions = ['create', 'view', 'update', 'delete', 'restore', 'forceDelete'];
        $brandActions = ['create', 'view', 'update', 'delete'];

        $permissions = [];

        foreach ($features as $feature) {
            if ($feature == 'category') {
                foreach ($categoryActions as $action) {
                    $permissions[] = "$feature.$action";
                }
            } elseif ($feature == 'product') {
                foreach ($productActions as $action) {
                    $permissions[] = "$feature.$action";
                }
            } elseif ($feature == 'sale') {
                foreach ($saleActions as $action) {
                    $permissions[] = "$feature.$action";
                }
            } elseif ($feature == 'brand') {
                foreach ($brandActions as $action) {
                    $permissions[] = "$feature.$action";
                }
            }
        }

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // Create roles
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $stock = Role::firstOrCreate(['name' => 'stock']);
        $sales = Role::firstOrCreate(['name' => 'sale']);

        $admin->syncPermissions(Permission::all());

        // Assign admin role to user ID 1
        $user = User::find(1);
        if ($user) {
            $user->assignRole('admin');
        }
    }
}

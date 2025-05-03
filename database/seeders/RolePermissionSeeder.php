<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $features = ['product', 'sale'];
        $actions = ['create', 'view', 'update', 'delete'];

        $permissions = [];

        foreach ($features as $feature) {
            foreach ($actions as $action) {
                $permissions[] = "$feature.$action";
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

        $stock->syncPermissions($permissions); // Full product & sale access

        $sales->syncPermissions([
            'sale.create', 'sale.view', 'sale.update', 'sale.delete',
            'product.view'
        ]);
    }
}

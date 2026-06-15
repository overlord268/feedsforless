<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'sanctum';

        foreach (['admin', 'customer'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
        }
    }
}

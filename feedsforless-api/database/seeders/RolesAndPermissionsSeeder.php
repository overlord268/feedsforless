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
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => $guard]);
        Role::firstOrCreate(['name' => 'Customer', 'guard_name' => $guard]);
    }
}
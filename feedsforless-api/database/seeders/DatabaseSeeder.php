<?php

namespace Database\Seeders;

use App\Domains\B2B\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Check if the user already exists to avoid 500 errors
        if (!User::where('email', 'admin@feedsforless.com')->exists()) {
            User::create([
                'first_name' => 'Admin',
                'last_name'  => 'User',
                'email'      => 'admin@feedsforless.com',
                'password'   => Hash::make('password123'), // Change this later!
            ]);
        }
        
        $this->call([
            RolesAndPermissionsSeeder::class,
            CatalogSeeder::class,
            InitialProductsSeeder::class,
        ]);

        // Assign Super Admin role to the admin user (existing or just created)
        $admin = User::where('email', 'admin@feedsforless.com')->first();
        if ($admin && !$admin->hasRole('Super Admin')) {
            $admin->assignRole('Super Admin');
        }
    }
}

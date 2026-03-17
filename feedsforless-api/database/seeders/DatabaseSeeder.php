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
        
        // Add any other seeders you need to call here
        $this->call([
            RolesAndPermissionsSeeder::class,
            CatalogSeeder::class,
            InitialProductsSeeder::class,
        ]);
    }
}

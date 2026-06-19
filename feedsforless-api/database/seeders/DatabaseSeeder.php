<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Fresh install / demo reset:
     *   php artisan migrate:fresh --seed
     *
     * Default logins (override via SEED_* in .env):
     *   Admin:    admin@feedsforless.com / password
     *   Customer: cliente@empresa.com / password
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UsersSeeder::class,
            CatalogMastersSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}

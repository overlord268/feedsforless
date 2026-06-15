<?php

namespace Database\Seeders;

use App\Domains\B2B\Models\Company;
use App\Domains\B2B\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Default B2B users for local/staging fresh installs.
     * Override credentials via .env (SEED_*); never commit real passwords.
     */
    public function run(): void
    {
        $defaultPassword = env('SEED_DEFAULT_PASSWORD', 'password');

        $this->seedUser([
            'email' => env('SEED_ADMIN_EMAIL', 'admin@feedsforless.com'),
            'password' => env('SEED_ADMIN_PASSWORD', $defaultPassword),
            'first_name' => 'admin',
            'last_name' => 'ffl',
            'company_name' => 'feedsforless',
            'role' => 'admin',
        ]);

        $this->seedUser([
            'email' => env('SEED_CLIENT_EMAIL', 'cliente@empresa.com'),
            'password' => env('SEED_CLIENT_PASSWORD', $defaultPassword),
            'first_name' => 'cliente',
            'last_name' => 'ffl',
            'phone' => '252525255',
            'company_name' => 'feedsforless',
            'role' => 'customer',
        ]);
    }

    /**
     * @param  array<string, mixed>  $profile
     */
    private function seedUser(array $profile): void
    {
        $company = Company::firstOrCreate(
            ['name' => $profile['company_name']],
            ['name' => $profile['company_name']]
        );

        $user = User::updateOrCreate(
            ['email' => $profile['email']],
            [
                'company_id' => $company->id,
                'first_name' => $profile['first_name'],
                'last_name' => $profile['last_name'],
                'phone' => $profile['phone'] ?? null,
                'password' => $profile['password'],
            ]
        );

        $user->syncRoles([$profile['role']]);
    }
}

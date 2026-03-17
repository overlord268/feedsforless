<?php

use App\Domains\B2B\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:make-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();
    if (!$user) {
        $this->error("User with email [{$email}] not found.");
        return 1;
    }
    // App expects role 'admin' (sanctum) for admin panel access
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
    if ($user->hasRole('admin') || $user->hasRole('Admin') || $user->hasRole('Super Admin')) {
        $this->info("User [{$email}] already has an admin role.");
        return 0;
    }
    $user->assignRole('admin');
    $this->info("Admin role assigned to [{$email}].");
    return 0;
})->purpose('Assign admin role to a user by email (for admin panel access)');

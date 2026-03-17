<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Asegurar que las rutas API estén bajo /api
Route::prefix('api')->group(function () {
    require base_path('routes/api.php');
});

Route::get('/run-migrations', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return 'Migrations ran successfully!';
});

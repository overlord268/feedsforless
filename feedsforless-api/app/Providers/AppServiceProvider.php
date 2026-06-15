<?php

namespace App\Providers;

use App\OpenApi\ConfigureScrambleDocumentation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        ConfigureScrambleDocumentation::register();
    }
}

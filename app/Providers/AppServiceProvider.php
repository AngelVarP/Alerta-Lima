<?php

namespace App\Providers;

use App\Models\Denuncia;
use App\Policies\DenunciaPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar policies
        Gate::policy(Denuncia::class, DenunciaPolicy::class);
    }
}

<?php

namespace App\Providers;

use App\Models\Denuncia;
use App\Models\Usuario;
use App\Observers\DenunciaObserver;
use App\Observers\UsuarioObserver;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Aquí puedes registrar listeners tradicionales si los necesitas
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // CORRECCIÓN: Registrar Observers para mover lógica de triggers a Laravel
        Denuncia::observe(DenunciaObserver::class);
        Usuario::observe(UsuarioObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

<?php

namespace App\Providers;

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
        // Permisos por rol (tabla roles, catálogo en config/permissions.php).
        // Habilita can:modules.x en rutas, @can en Blade y $this->authorize() en controladores.
        // Devuelve null (no false) cuando no aplica, para no bloquear otras políticas.
        Gate::before(function ($user, string $ability) {
            if (!str_starts_with($ability, 'modules.') && !str_starts_with($ability, 'actions.')) {
                return null;
            }

            return method_exists($user, 'hasPermission') && $user->hasPermission($ability) ? true : null;
        });
    }
}

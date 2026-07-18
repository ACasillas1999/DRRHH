<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $rol = $request->user()?->Rol;

        abort_if(!$rol || !in_array($rol, $roles, true), 403, 'No tienes permiso para acceder a esta sección.');

        return $next($request);
    }
}

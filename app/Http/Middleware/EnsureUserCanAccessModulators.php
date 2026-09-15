<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanAccessModulators
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $isMaster = (int) $user->id === 1;
        $isDth = $user->area === 'DTH';
        $isConmutacionesManager = (bool) ($user->is_conmutaciones_manager ?? false);

        if (! ($isMaster || $isDth || $isConmutacionesManager)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}

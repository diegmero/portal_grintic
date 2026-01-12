<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientAccess
{
    /**
     * Handle an incoming request.
     * Ensures the user has 'client' role and belongs to a company.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('client')) {
            abort(403, 'Acceso denegado. Esta sección es exclusiva para clientes.');
        }

        if (!$user->company_id) {
            abort(403, 'Tu cuenta no está asociada a ninguna empresa.');
        }

        return $next($request);
    }
}

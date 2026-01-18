<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->two_factor_confirmed_at) {
            if (! $request->session()->get('auth.two_factor_verified')) {
                // If the user is accessing the challenge or logout, let them pass.
                // Also ignore internal routes if needed, but standard protection is:
                if (! $request->is('two-factor-challenge') && ! $request->is('logout') && ! $request->is('sanctum/*')) {
                     return redirect()->route('two-factor.login');
                }
            }
        }

        return $next($request);
    }
}

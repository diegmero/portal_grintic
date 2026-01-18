<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            // Update if last login was more than 1 minute ago to avoid constantly hitting DB
            if (!$user->last_login_at || $user->last_login_at->lt(now()->subMinute())) {
                $user->last_login_at = now();
                $user->saveQuietly();
            }
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceChangePasswordMiddleware
{
    private const WARNING_MESSAGE = 'Untuk keamanan akun, Anda wajib mengganti password sementara sebelum menggunakan sistem.';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('guru') || ! $user->must_change_password) {
            return $next($request);
        }

        $allowedRoutes = [
            'guru.profile.show',
            'guru.profile.update',
            'guru.profile.updateAvatar',
            'password.update',
            'logout',
        ];

        $currentRoute = $request->route()?->getName();

        if ($currentRoute && in_array($currentRoute, $allowedRoutes, true)) {
            return $next($request);
        }

        return redirect()
            ->route('guru.profile.show')
            ->with('warning', self::WARNING_MESSAGE);
    }
}
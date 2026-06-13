<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuruMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasRole') || ! $user->hasRole('guru')) {
            abort(403, 'Forbidden - guru only');
        }

        // Share teacher data with Inertia for sidebar
        $teacher = $user->teacher;
        if ($teacher) {
            $avatarUrl = $teacher->avatar ? \Illuminate\Support\Facades\Storage::url($teacher->avatar) : null;
            
            Inertia::share([
                'teacher' => [
                    'id' => $teacher->id,
                    'name' => $teacher->name,
                    'nip' => $teacher->nip,
                    'avatar' => $avatarUrl,
                ],
            ]);
        }

        return $next($request);
    }
}

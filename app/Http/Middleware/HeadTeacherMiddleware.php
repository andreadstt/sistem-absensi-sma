<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeadTeacherMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is authenticated and has teacher record
        if (!$user || !$user->teacher) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Get the class room from route parameter
        $classRoom = $request->route('classRoom');

        if ($classRoom) {
            // Check if the teacher is the head teacher of this class
            if ($classRoom->head_teacher_id !== $user->teacher->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses ke kelas ini. Hanya wali kelas yang dapat mengakses.'
                ], 403);
            }
        }

        return $next($request);
    }
}

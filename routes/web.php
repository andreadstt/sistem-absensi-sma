<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\TeacherRegistrationController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\GuruMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// Root route - redirect to appropriate dashboard based on auth status
Route::get('/', function () {
    // If user is authenticated, redirect to their dashboard
    if (Auth::check()) {
        $user = Auth::user();

        // Admin -> Filament panel
        if ($user->hasRole('admin')) {
            return redirect('/admin');
        }

        // Guru -> Guru dashboard
        if ($user->hasRole('guru')) {
            return redirect()->route('guru.dashboard');
        }

        // Default fallback
        return redirect()->route('dashboard');
    }

    // If not authenticated, show welcome/login page
    return redirect()->route('login');
});

// Main dashboard route - redirects based on role
Route::get('/dashboard', function () {
    $user = Auth::user();

    // Redirect admin users to Filament admin panel
    if ($user->hasRole('admin')) {
        return redirect('/admin');
    }

    // Redirect guru users to guru dashboard
    if ($user->hasRole('guru')) {
        return redirect()->route('guru.dashboard');
    }

    // Default dashboard for other users (if any)
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Teacher Registration Routes (public - for guest users)
Route::middleware('guest')->group(function () {
    Route::get('/teacher/register', [TeacherRegistrationController::class, 'create'])->name('teacher.register.create');
    Route::post('/teacher/register', [TeacherRegistrationController::class, 'store'])->name('teacher.register.store');
});

// Note: Filament Admin Panel is at /admin (configured via AdminPanelProvider)
// It's protected by Authenticate + AdminMiddleware

// Guru Portal Routes
Route::middleware(['auth', GuruMiddleware::class])->prefix('guru')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/kelas/{classRoom}', [\App\Http\Controllers\Guru\KelasController::class, 'show'])->name('guru.kelas.show');
    Route::get('/absensi/{classRoom}/{subject}/{date}', [AbsensiController::class, 'show'])->name('guru.absensi.show');
    Route::post('/absensi', [AbsensiController::class, 'store'])->name('guru.absensi.store');
});

require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Guru\DashboardController as GuruDashboardController;
use App\Http\Controllers\Guru\AbsensiController;
use App\Http\Controllers\Guru\WaliKelasController;
use App\Http\Controllers\Guru\KehadiranController;
use App\Http\Controllers\Admin\TeacherAttendanceController;
use App\Http\Controllers\TeacherRegistrationController;
use App\Http\Middleware\HeadTeacherMiddleware;
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

Route::middleware(['auth', 'force-change-password'])->group(function () {
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
Route::middleware(['auth', GuruMiddleware::class, 'force-change-password'])->prefix('guru')->group(function () {
    Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('guru.dashboard');
    Route::get('/rekap-absen', [GuruDashboardController::class, 'rekapAbsen'])->name('guru.rekap.absen');
    Route::get('/kehadiran', [KehadiranController::class, 'index'])->name('guru.kehadiran.index');
    Route::get('/profile', [\App\Http\Controllers\Guru\ProfileController::class, 'show'])->name('guru.profile.show');
    Route::put('/profile', [\App\Http\Controllers\Guru\ProfileController::class, 'update'])->name('guru.profile.update');
    Route::post('/profile/avatar', [\App\Http\Controllers\Guru\ProfileController::class, 'updateAvatar'])->name('guru.profile.updateAvatar');
    Route::get('/kelas/{classRoom}', [\App\Http\Controllers\Guru\KelasController::class, 'show'])->name('guru.kelas.show');
    Route::get('/kelas/{classRoom}/export', [\App\Http\Controllers\Guru\KelasController::class, 'export'])->name('guru.kelas.export');
    Route::get('/absensi/{classRoom}/{subject}/{date}', [AbsensiController::class, 'show'])->name('guru.absensi.show');
    Route::post('/absensi', [AbsensiController::class, 'store'])->name('guru.absensi.store');
    Route::post('/attendance/update', [\App\Http\Controllers\Guru\KelasController::class, 'updateAttendance'])->name('guru.attendance.update');
    
    // Wali Kelas (Head Teacher) Routes
    Route::get('/wali-kelas', [WaliKelasController::class, 'index'])->name('guru.wali-kelas.index');
    Route::get('/wali-kelas/{classRoom}', [WaliKelasController::class, 'show'])->middleware(HeadTeacherMiddleware::class)->name('guru.wali-kelas.show');
    Route::get('/wali-kelas/{classRoom}/export', [WaliKelasController::class, 'export'])->middleware(HeadTeacherMiddleware::class)->name('guru.wali-kelas.export');
});

// Admin Portal Routes (Non-Filament Dashboard Pages)
Route::middleware(['auth', AdminMiddleware::class])->prefix('admin-portal')->group(function () {
    Route::get('/teacher-attendance', [TeacherAttendanceController::class, 'index'])->name('admin.teacher-attendance.index');
    Route::get('/teacher/{teacher}/attendance', [TeacherAttendanceController::class, 'getTeacherAttendance'])->name('admin.teacher-attendance.show');
    Route::post('/teacher-attendance/update', [TeacherAttendanceController::class, 'updateAttendance'])->name('admin.teacher-attendance.update');
});

require __DIR__ . '/auth.php';

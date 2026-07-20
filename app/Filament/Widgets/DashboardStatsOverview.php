<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\TeacherRegistration;
use App\Services\TeacherAttendanceService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class DashboardStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $todayStats = TeacherAttendanceService::getTodayStats();
        $hadir = $todayStats['total_hadir'];
        $jadwal = $todayStats['total_jadwal'];
        
        $pendingRegistrations = TeacherRegistration::where('status', 'pending')->count();

        $attendanceRatio = $jadwal > 0 ? ($hadir / $jadwal) * 100 : 0;
        $attendanceColor = $attendanceRatio >= 80 ? 'success' : ($attendanceRatio >= 50 ? 'warning' : 'danger');

        $sparklineData = Cache::remember('teacher_attendance_sparkline_7_days', 300, function () {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $stats = TeacherAttendanceService::getAttendanceStatsForDate($date);
                $data[] = $stats['percentage'];
            }
            return $data;
        });

        return [
            Stat::make('Total Siswa', Student::count())
                ->icon('heroicon-o-users')
                ->color('primary'),
            Stat::make('Total Guru', Teacher::count())
                ->icon('heroicon-o-academic-cap')
                ->color('info'),
            Stat::make('Total Kelas', ClassRoom::count())
                ->icon('heroicon-o-building-office-2')
                ->color('success'),
            Stat::make('Total Mapel', Subject::count())
                ->icon('heroicon-o-book-open')
                ->color('warning'),
            // Stat::make('Kehadiran Guru (Hari Ini)', "{$hadir}/{$jadwal} Hadir")
            //     ->description('Berdasarkan sesi jadwal aktif')
            //     ->color($attendanceColor)
            //     ->chart($sparklineData),
            Stat::make('Pendaftaran Pending', $pendingRegistrations)
                ->description('Registrasi guru baru')
                ->icon('heroicon-o-user-plus')
                ->color($pendingRegistrations > 0 ? 'danger' : 'gray')
                ->extraAttributes(['class' => 'lg:col-span-2']),
        ];
    }
}

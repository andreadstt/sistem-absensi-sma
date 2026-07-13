<?php

namespace App\Services;

use App\Models\AcademicEvent;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use Carbon\Carbon;

class TeacherAttendanceService
{
    /**
     * Determine the status of a schedule for a given date.
     * 
     * @param Schedule $schedule
     * @param string $date (Y-m-d format)
     * @return array ['status' => 'OPEN'|'NOT_STARTED'|'CLOSED'|'HOLIDAY', 'message' => string]
     */
    public static function getScheduleStatus(Schedule $schedule, string $date): array
    {
        // 1. Check for holidays
        $holiday = AcademicEvent::where('type', 'holiday')
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();

        if ($holiday) {
            return [
                'status' => 'HOLIDAY',
                'message' => "Hari ini adalah {$holiday->title} — absensi tidak dibuka.",
            ];
        }

        // 2. Check Time Window
        $bufferMinutes = config('academic.teacher_attendance_buffer_minutes', 20);
        $timeParts = explode('-', $schedule->time_slot);
        
        if (count($timeParts) !== 2) {
            // Malformed time slot, fallback to open
            return [
                'status' => 'OPEN',
                'message' => 'Bisa diakses.',
            ];
        }

        $startTime = trim($timeParts[0]);
        $endTime = trim($timeParts[1]);

        $windowStart = Carbon::parse("{$date} {$startTime}");
        $windowEnd = Carbon::parse("{$date} {$endTime}")->addMinutes($bufferMinutes);
        $now = now(); // Automatically utilizes config('app.timezone')

        if ($now->lt($windowStart)) {
            return [
                'status' => 'NOT_STARTED',
                'message' => 'Jadwal belum dimulai.',
            ];
        }

        if ($now->gt($windowEnd)) {
            return [
                'status' => 'CLOSED',
                'message' => 'Waktu input absensi untuk jadwal ini sudah berakhir. Hubungi admin jika perlu koreksi.',
            ];
        }

        // 2.5 Check if already submitted
        $alreadySubmitted = TeacherAttendance::where('schedule_id', $schedule->id)
            ->where('date', $date)
            ->where('teacher_id', $schedule->teacher_id)
            ->exists();

        if ($alreadySubmitted) {
            return [
                'status' => 'ALREADY_SUBMITTED',
                'message' => 'Absensi untuk jadwal ini sudah Anda rekam.',
            ];
        }

        // 3. Status Open
        return [
            'status' => 'OPEN',
            'message' => 'Bisa diakses.',
        ];
    }

    /**
     * Get aggregated attendance stats for a specific date.
     * 
     * @param Carbon $date
     * @return array
     */
    public static function getAttendanceStatsForDate(Carbon $date): array
    {
        $dateString = $date->format('Y-m-d');
        $weekday = $date->dayOfWeekIso;

        // Total schedules for the date (all teachers' schedules on this weekday)
        $totalSchedules = Schedule::where('weekday', $weekday)->count();
        
        $records = TeacherAttendance::whereDate('date', $dateString)->get();
        $totalHadir = $records->where('status', 'HADIR')->count();
        $totalTidakHadir = $records->where('status', 'TIDAK_HADIR')->count();

        return [
            'total_jadwal' => $totalSchedules,
            'total_hadir' => $totalHadir,
            'total_tidak_hadir' => $totalTidakHadir,
            'total_tercatat' => $records->count(),
            'percentage' => $totalSchedules > 0 ? round(($totalHadir / $totalSchedules) * 100, 1) : 0,
        ];
    }

    /**
     * Get aggregated attendance stats for today.
     * 
     * @return array
     */
    public static function getTodayStats(): array
    {
        return self::getAttendanceStatsForDate(now());
    }
}

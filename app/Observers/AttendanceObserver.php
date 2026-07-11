<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\TeacherAttendance;
use Carbon\Carbon;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     * Auto-track teacher attendance as HADIR
     */
    public function created(Attendance $attendance): void
    {
        $this->markTeacherAsPresent($attendance);
    }

    /**
     * Handle the Attendance "updated" event.
     */
    public function updated(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "deleted" event.
     */
    public function deleted(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "restored" event.
     */
    public function restored(Attendance $attendance): void
    {
        //
    }

    /**
     * Handle the Attendance "force deleted" event.
     */
    public function forceDeleted(Attendance $attendance): void
    {
        //
    }

    /**
     * Mark teacher as HADIR for the specific schedule when they record student attendance.
     * Handles "double period" case by disambiguating based on current time vs time_slot.
     */
    private function markTeacherAsPresent(Attendance $attendance): void
    {
        $teacher = $attendance->teacher;
        $date = $attendance->date;

        if (!$teacher) {
            return;
        }

        // Find ALL matching schedules (could be >1 for double periods)
        $schedules = Schedule::where('teacher_id', $attendance->teacher_id)
            ->where('class_room_id', $attendance->class_room_id)
            ->where('subject_id', $attendance->subject_id)
            ->where('weekday', Carbon::parse($date)->dayOfWeekIso)
            ->get();

        if ($schedules->isEmpty()) {
            return;
        }

        // If only one match, use it directly
        if ($schedules->count() === 1) {
            $schedule = $schedules->first();
        } else {
            // Multiple matches (double period) — pick the one whose time window
            // best matches the current time
            $schedule = $this->findBestMatchingSchedule($schedules, $date);
        }

        if ($schedule) {
            TeacherAttendance::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'schedule_id' => $schedule->id,
                    'date' => $date,
                ],
                [
                    'status' => 'HADIR',
                    'notes' => 'Otomatis dari aktivitas mengajar',
                ]
            );
        }
    }

    /**
     * From multiple schedules with the same teacher+class+subject+weekday,
     * find the one whose time_slot best matches the current time.
     *
     * Priority:
     * 1. Schedule currently in progress (now is between start and end+buffer)
     * 2. If none active, pick the one that ended most recently before now
     */
    private function findBestMatchingSchedule($schedules, $date): ?Schedule
    {
        $now = now();
        $bufferMinutes = config('academic.teacher_attendance_buffer_minutes', 20);
        $activeSchedule = null;
        $closestPastSchedule = null;
        $closestPastDiff = PHP_INT_MAX;

        foreach ($schedules as $schedule) {
            $timeParts = explode('-', $schedule->time_slot);
            if (count($timeParts) !== 2) {
                continue;
            }

            $startTime = Carbon::parse("{$date} " . trim($timeParts[0]));
            $endTime = Carbon::parse("{$date} " . trim($timeParts[1]))->addMinutes($bufferMinutes);

            // Check if now falls within this schedule's window
            if ($now->gte($startTime) && $now->lte($endTime)) {
                $activeSchedule = $schedule;
                break; // Exact match found, use it
            }

            // Track the closest past schedule (ended most recently before now)
            if ($now->gt($endTime)) {
                $diff = $now->diffInSeconds($endTime);
                if ($diff < $closestPastDiff) {
                    $closestPastDiff = $diff;
                    $closestPastSchedule = $schedule;
                }
            }
        }

        return $activeSchedule ?? $closestPastSchedule ?? $schedules->first();
    }
}


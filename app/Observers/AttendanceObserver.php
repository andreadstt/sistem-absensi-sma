<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\TeacherAttendance;

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
     * Mark teacher as HADIR when they create/update attendance
     */
    private function markTeacherAsPresent(Attendance $attendance): void
    {
        $teacher = $attendance->teacher;
        $date = $attendance->date;

        if ($teacher) {
            TeacherAttendance::updateOrCreate(
                [
                    'teacher_id' => $teacher->id,
                    'date' => $date,
                ],
                [
                    'status' => 'HADIR',
                    'notes' => 'Otomatis dari aktivitas mengajar',
                ]
            );
        }
    }
}

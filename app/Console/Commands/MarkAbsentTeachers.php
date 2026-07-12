<?php

namespace App\Console\Commands;

use App\Models\Schedule;
use App\Models\TeacherAttendance;
use App\Models\AcademicEvent;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAbsentTeachers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teachers:mark-absent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-mark teachers as TIDAK_HADIR for schedules whose time window has passed without attendance record';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->format('Y-m-d');
        $todayWeekday = now()->dayOfWeekIso;
        $bufferMinutes = config('academic.teacher_attendance_buffer_minutes', 20);
        $now = now();
        $marked = 0;

        // Check if today is a holiday
        $isHoliday = AcademicEvent::where('type', 'holiday')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->exists();

        if ($isHoliday) {
            $this->info("Hari ini libur. Cronjob di-skip.");
            return self::SUCCESS;
        }

        // Get all schedules for today's weekday
        $schedules = Schedule::where('weekday', $todayWeekday)->get();

        foreach ($schedules as $schedule) {
            // Parse time_slot to get end time (format: "07:00-08:00")
            $timeParts = explode('-', $schedule->time_slot);
            if (count($timeParts) !== 2) {
                continue;
            }

            $endTime = trim($timeParts[1]);
            $deadline = Carbon::parse("{$today} {$endTime}")->addMinutes($bufferMinutes);

            // Only process if the time window has already passed
            if ($now->lte($deadline)) {
                continue;
            }

            // Check if a TeacherAttendance record already exists for this combination
            $exists = TeacherAttendance::where('teacher_id', $schedule->teacher_id)
                ->where('schedule_id', $schedule->id)
                ->where('date', $today)
                ->exists();

            if (!$exists) {
                TeacherAttendance::create([
                    'teacher_id' => $schedule->teacher_id,
                    'schedule_id' => $schedule->id,
                    'date' => $today,
                    'status' => 'TIDAK_HADIR',
                    'notes' => 'Otomatis: tidak ada absensi tercatat',
                ]);
                $marked++;
            }
        }

        $this->info("Marked {$marked} teacher schedule(s) as TIDAK_HADIR.");

        return self::SUCCESS;
    }
}

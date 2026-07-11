<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbsensiController extends Controller
{
    public function show(Request $request, $classRoomId, $subjectId, $date)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'No teacher profile found.');
        }

        // Load class room and subject
        $classRoom = ClassRoom::with('students')->findOrFail($classRoomId);
        $subject = Subject::findOrFail($subjectId);

        // Find ALL matching schedules for this combination
        $parsedDate = Carbon::parse($date);
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('class_room_id', $classRoomId)
            ->where('subject_id', $subjectId)
            ->where('weekday', $parsedDate->dayOfWeekIso)
            ->get();

        if ($schedules->isEmpty()) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Jadwal tidak ditemukan untuk kombinasi kelas, mata pelajaran, dan hari ini.');
        }

        // Disambiguate for double periods
        $schedule = $schedules->count() === 1 
            ? $schedules->first() 
            : $this->findBestMatchingSchedule($schedules, $date);

        // Check time restriction
        $timeCheck = $this->checkTimeWindow($schedule, $date);
        if ($timeCheck !== true) {
            return redirect()->route('guru.dashboard')
                ->with('error', $timeCheck);
        }

        // Check if attendance already exists for this date
        $existingAttendance = Attendance::where('date', $date)
            ->where('class_room_id', $classRoomId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', $teacher->id)
            ->pluck('status', 'student_id');

        $students = $classRoom->students->map(function ($student) use ($existingAttendance) {
            return [
                'id' => $student->id,
                'nis' => $student->nis,
                'name' => $student->name,
                'gender' => $student->gender,
                'status' => $existingAttendance[$student->id] ?? 'HADIR',
            ];
        });

        return Inertia::render('Guru/Absensi', [
            'classRoom' => [
                'id' => $classRoom->id,
                'name' => $classRoom->name,
            ],
            'subject' => [
                'id' => $subject->id,
                'name' => $subject->name,
            ],
            'date' => $date,
            'students' => $students,
            'isReadOnly' => $existingAttendance->isNotEmpty(),
            'scheduleInfo' => [
                'id' => $schedule->id,
                'time_slot' => $schedule->time_slot,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_room_id' => 'required|exists:class_rooms,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:HADIR,SAKIT,IZIN,ALFA',
        ]);

        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->with('error', 'No teacher profile found.');
        }

        // Find ALL matching schedules for this combination
        $parsedDate = Carbon::parse($validated['date']);
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('class_room_id', $validated['class_room_id'])
            ->where('subject_id', $validated['subject_id'])
            ->where('weekday', $parsedDate->dayOfWeekIso)
            ->get();

        if ($schedules->isEmpty()) {
            return back()->with('error', 'Jadwal tidak ditemukan.');
        }

        // Disambiguate for double periods
        $schedule = $schedules->count() === 1 
            ? $schedules->first() 
            : $this->findBestMatchingSchedule($schedules, $validated['date']);

        // Check time restriction
        $timeCheck = $this->checkTimeWindow($schedule, $validated['date']);
        if ($timeCheck !== true) {
            return back()->with('error', $timeCheck);
        }

        // Check if attendance already exists
        $exists = Attendance::where('date', $validated['date'])
            ->where('class_room_id', $validated['class_room_id'])
            ->where('subject_id', $validated['subject_id'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Attendance already recorded for this class and date.');
        }

        // Insert attendance records
        foreach ($validated['attendances'] as $attendance) {
            Attendance::create([
                'date' => $validated['date'],
                'class_room_id' => $validated['class_room_id'],
                'subject_id' => $validated['subject_id'],
                'teacher_id' => $teacher->id,
                'student_id' => $attendance['student_id'],
                'status' => $attendance['status'],
                'recorded_by' => $user->id,
            ]);
        }

        return redirect()->route('guru.dashboard')
            ->with('success', 'Attendance successfully recorded!');
    }

    /**
     * Check if the current time falls within the allowed time window for attendance input.
     *
     * @param Schedule $schedule
     * @param string $date
     * @return true|string True if within window, error message string otherwise.
     */
    private function checkTimeWindow(Schedule $schedule, string $date): true|string
    {
        $bufferMinutes = config('academic.teacher_attendance_buffer_minutes', 20);

        // Parse time_slot (format: "07:00-08:00")
        $timeParts = explode('-', $schedule->time_slot);
        if (count($timeParts) !== 2) {
            // If time_slot format is unexpected, allow access
            return true;
        }

        $startTime = trim($timeParts[0]);
        $endTime = trim($timeParts[1]);

        $windowStart = Carbon::parse("{$date} {$startTime}");
        $windowEnd = Carbon::parse("{$date} {$endTime}")->addMinutes($bufferMinutes);
        $now = now();

        if ($now->lt($windowStart)) {
            return 'Jadwal belum dimulai.';
        }

        if ($now->gt($windowEnd)) {
            return 'Waktu input absensi untuk jadwal ini sudah berakhir. Hubungi admin jika perlu koreksi.';
        }

        return true;
    }

    /**
     * From multiple schedules with the same teacher+class+subject+weekday,
     * find the one whose time_slot best matches the current time.
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

            if ($now->gte($startTime) && $now->lte($endTime)) {
                $activeSchedule = $schedule;
                break;
            }

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


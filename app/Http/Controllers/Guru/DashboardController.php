<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get teacher record for the authenticated user
        $teacher = $user->teacher;

        if (!$teacher) {
            return Inertia::render('Guru/Dashboard', [
                'schedules' => [],
                'message' => 'No teacher profile found for your account.',
            ]);
        }

        // Determine current weekday (1 = Monday, 7 = Sunday)
        $currentWeekday = now()->dayOfWeekIso; // ISO-8601 (1=Monday, 7=Sunday)

        // Fetch schedules for today
        $schedules = Schedule::where('teacher_id', $teacher->id)
            ->where('weekday', $currentWeekday)
            ->with(['classRoom.academicYear', 'classRoom.program', 'subject'])
            ->orderBy('time_slot')
            ->get()
            ->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'class_name' => $schedule->classRoom->full_name ?? $schedule->classRoom->name,
                    'subject_name' => $schedule->subject->name,
                    'time_slot' => $schedule->time_slot,
                    'class_room_id' => $schedule->class_room_id,
                    'subject_id' => $schedule->subject_id,
                ];
            });

        // Get all teaching assignments with class info (1 assignment = 1 Class + 1 Subject)
        $myClasses = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->with(['classRoom.academicYear', 'classRoom.program', 'subject', 'classRoom.students'])
            ->get()
            ->map(function ($assignment) use ($teacher) {
                $classRoom = $assignment->classRoom;
                $subject = $assignment->subject;
                
                // Get schedules for this specific class AND subject
                $classSchedules = Schedule::where('teacher_id', $teacher->id)
                    ->where('class_room_id', $classRoom->id)
                    ->where('subject_id', $subject->id)
                    ->orderBy('weekday')
                    ->orderBy('time_slot')
                    ->get()
                    ->map(function ($schedule) {
                        $dayName = match($schedule->weekday) {
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu',
                            default => '-',
                        };
                        
                        $todayDate = now()->format('Y-m-d');
                        $statusObj = TeacherAttendanceService::getScheduleStatus($schedule, $todayDate);

                        return [
                            'day' => $dayName,
                            'time_slot' => $schedule->time_slot,
                            'subject_name' => $schedule->subject->name ?? '-',
                            'subject_id' => $schedule->subject_id,
                            'status' => $statusObj,
                        ];
                    });
                
                return [
                    'class_room_id' => $classRoom->id,
                    'subject_id' => $subject->id,
                    'class_name' => $classRoom->full_name ?? $classRoom->name,
                    'subject_name' => $subject->name,
                    'card_title' => ($classRoom->full_name ?? $classRoom->name) . ' - ' . $subject->name,
                    'student_count' => $classRoom->students->count(),
                    'academic_year' => $classRoom->academicYear->name ?? '-',
                    'program' => $classRoom->program->short_name ?? '-',
                    'schedules' => $classSchedules,
                ];
            })->sortBy('class_name')->values();

        // Statistics
        $totalSchedulesToday = $schedules->count();
        $totalClasses = $myClasses->count();
        $totalStudents = $myClasses->sum('student_count');
        $totalSubjects = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->distinct('subject_id')
            ->count('subject_id');

        return Inertia::render('Guru/Dashboard', [
            'schedules' => $schedules,
            'myClasses' => $myClasses,
            'stats' => [
                'totalSchedulesToday' => $totalSchedulesToday,
                'totalClasses' => $totalClasses,
                'totalStudents' => $totalStudents,
                'totalSubjects' => $totalSubjects,
            ],
            'today' => now()->format('l, F j, Y'),
            'teacherName' => $teacher->name,
            'bufferMinutes' => config('academic.teacher_attendance_buffer_minutes', 20),
        ]);
    }

    public function rekapAbsen(Request $request)
    {
        $user = $request->user();

        // Get teacher record for the authenticated user
        $teacher = $user->teacher;

        if (!$teacher) {
            return Inertia::render('Guru/RekapAbsen', [
                'myClasses' => [],
            ]);
        }

        // Get all teaching assignments with class info (1 assignment = 1 Class + 1 Subject)
        $myClasses = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->with(['classRoom.academicYear', 'classRoom.program', 'subject', 'classRoom.students'])
            ->get()
            ->map(function ($assignment) use ($teacher) {
                $classRoom = $assignment->classRoom;
                $subject = $assignment->subject;
                
                // Get schedules for this specific class AND subject
                $classSchedules = Schedule::where('teacher_id', $teacher->id)
                    ->where('class_room_id', $classRoom->id)
                    ->where('subject_id', $subject->id)
                    ->orderBy('weekday')
                    ->orderBy('time_slot')
                    ->get()
                    ->map(function ($schedule) {
                        $dayName = match($schedule->weekday) {
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu',
                            default => '-',
                        };
                        return [
                            'day' => $dayName,
                            'time_slot' => $schedule->time_slot,
                            'subject_name' => $schedule->subject->name ?? '-',
                            'subject_id' => $schedule->subject_id,
                        ];
                    });
                
                return [
                    'class_room_id' => $classRoom->id,
                    'subject_id' => $subject->id,
                    'class_name' => $classRoom->full_name ?? $classRoom->name,
                    'subject_name' => $subject->name,
                    'card_title' => ($classRoom->full_name ?? $classRoom->name) . ' - ' . $subject->name,
                    'student_count' => $classRoom->students->count(),
                    'academic_year' => $classRoom->academicYear->name ?? '-',
                    'program' => $classRoom->program->short_name ?? '-',
                    'schedules' => $classSchedules,
                ];
            })->sortBy('class_name')->values();

        return Inertia::render('Guru/RekapAbsen', [
            'myClasses' => $myClasses,
        ]);
    }
}

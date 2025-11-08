<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
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

        // Get all teaching assignments with class info
        $myClasses = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->with(['classRoom.academicYear', 'classRoom.program', 'subject', 'classRoom.students'])
            ->get()
            ->groupBy('class_room_id')
            ->map(function ($assignments, $classRoomId) {
                $classRoom = $assignments->first()->classRoom;
                return [
                    'class_room_id' => $classRoomId,
                    'class_name' => $classRoom->full_name ?? $classRoom->name,
                    'student_count' => $classRoom->students->count(),
                    'academic_year' => $classRoom->academicYear->name ?? '-',
                    'program' => $classRoom->program->short_name ?? '-',
                    'subjects' => $assignments->map(function ($assignment) {
                        return [
                            'id' => $assignment->subject_id,
                            'name' => $assignment->subject->name,
                        ];
                    })->values(),
                ];
            })->values();

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
        ]);
    }
}

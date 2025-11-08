<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KelasController extends Controller
{
    public function show(Request $request, $classRoomId)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Verify teacher mengajar kelas ini
        $teachingAssignment = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('class_room_id', $classRoomId)
            ->first();

        if (!$teachingAssignment) {
            abort(403, 'You are not assigned to teach this class');
        }

        // Get class info
        $classRoom = ClassRoom::with(['academicYear', 'program', 'students'])
            ->findOrFail($classRoomId);

        // Get all students in this class
        $students = $classRoom->students()
            ->orderBy('name')
            ->get();

        // Get unique attendance dates for this class (where this teacher recorded)
        $attendanceDates = Attendance::where('class_room_id', $classRoomId)
            ->where('teacher_id', $teacher->id)
            ->distinct()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/y');
            })
            ->values();

        // Get attendance records for display
        $attendanceData = Attendance::where('class_room_id', $classRoomId)
            ->where('teacher_id', $teacher->id)
            ->with('student')
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('d/m/y');
            });

        // Build student rows with attendance
        $studentRows = $students->map(function ($student) use ($attendanceData, $attendanceDates) {
            $attendances = [];
            foreach ($attendanceDates as $date) {
                $record = $attendanceData->get($date)?->firstWhere('student_id', $student->id);
                $attendances[$date] = $record ? $record->status : null;
            }

            return [
                'id' => $student->id,
                'name' => $student->name,
                'attendances' => $attendances,
            ];
        });

        return Inertia::render('Guru/KelasDetail', [
            'classRoom' => [
                'id' => $classRoom->id,
                'name' => $classRoom->full_name ?? $classRoom->name,
                'academic_year' => $classRoom->academicYear->name ?? '-',
                'program' => $classRoom->program->short_name ?? '-',
                'student_count' => $students->count(),
            ],
            'students' => $studentRows,
            'attendanceDates' => $attendanceDates,
        ]);
    }
}

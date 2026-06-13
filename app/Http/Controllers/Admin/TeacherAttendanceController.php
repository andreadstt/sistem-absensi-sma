<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherAttendanceController extends Controller
{
    /**
     * Display teacher attendance calendar
     */
    public function index(Request $request)
    {
        $currentMonth = $request->get('month', now()->format('Y-m'));

        // Get all teachers
        $teachers = Teacher::with(['user'])->orderBy('name')->get();

        // Get attendance data for all teachers for the current month
        $teachersAttendance = $teachers->map(function ($teacher) use ($currentMonth) {
            $attendances = TeacherAttendance::where('teacher_id', $teacher->id)
                ->where('date', 'like', $currentMonth . '%')
                ->get();

            return [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'nip' => $teacher->nip,
                'hadir_count' => $attendances->where('status', 'HADIR')->count(),
                'tidak_hadir_count' => $attendances->where('status', 'TIDAK_HADIR')->count(),
                'attendances' => $attendances,
            ];
        });

        return Inertia::render('Admin/TeacherAttendance', [
            'teachersAttendance' => $teachersAttendance,
            'currentMonth' => $currentMonth,
        ]);
    }

    /**
     * Get attendance data for a specific teacher
     */
    public function getTeacherAttendance(Teacher $teacher, Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $attendances = TeacherAttendance::where('teacher_id', $teacher->id)
            ->where('date', 'like', $month . '%')
            ->orderBy('date')
            ->get();

        return response()->json([
            'teacher' => $teacher,
            'attendances' => $attendances,
            'month' => $month,
        ]);
    }

    /**
     * Update teacher attendance record
     */
    public function updateAttendance(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'date' => 'required|date',
            'status' => 'required|in:HADIR,TIDAK_HADIR',
            'notes' => 'nullable|string|max:255',
        ]);

        $attendance = TeacherAttendance::updateOrCreate(
            [
                'teacher_id' => $validated['teacher_id'],
                'date' => $validated['date'],
            ],
            [
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return response()->json(['success' => true, 'data' => $attendance]);
    }
}

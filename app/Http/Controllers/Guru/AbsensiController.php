<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Student;
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
}

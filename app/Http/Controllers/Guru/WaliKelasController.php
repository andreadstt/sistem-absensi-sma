<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Exports\AttendanceSummaryExport;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class WaliKelasController extends Controller
{
    /**
     * Display a listing of all classes where the teacher is head teacher.
     */
    public function index()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan');
        }

        // Get all classes where this teacher is the head teacher
        $myClasses = $teacher->classRoomsAsHeadTeacher()
            ->with([
                'academicYear',
                'program',
                'students',
            ])
            ->get()
            ->map(function ($classRoom) {
                return [
                    'id' => $classRoom->id,
                    'name' => $classRoom->name,
                    'grade_level' => $classRoom->grade_level,
                    'section' => $classRoom->section,
                    'academic_year' => $classRoom->academicYear?->name,
                    'program' => $classRoom->program?->name,
                    'student_count' => $classRoom->students->count(),
                    'created_at' => $classRoom->created_at,
                ];
            });

        return Inertia::render('Guru/WaliKelas/Index', [
            'myClasses' => $myClasses,
            'teacherName' => $teacher->name,
            'totalClasses' => $myClasses->count(),
        ]);
    }

    /**
     * Display the specified class room details for head teacher.
     */
    public function show(ClassRoom $classRoom)
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan');
        }

        // Check if teacher is the head teacher of this class
        if ($classRoom->head_teacher_id !== $teacher->id) {
            return redirect()->route('guru.wali-kelas.index')
                ->with('error', 'Anda tidak memiliki akses ke kelas ini');
        }

        // Get detailed class information
        $classData = [
            'id' => $classRoom->id,
            'name' => $classRoom->name,
            'grade_level' => $classRoom->grade_level,
            'section' => $classRoom->section,
            'academic_year' => $classRoom->academicYear?->name,
            'program' => $classRoom->program?->name,
            'head_teacher' => [
                'id' => $classRoom->headTeacher?->id,
                'name' => $classRoom->headTeacher?->name,
                'phone' => $classRoom->headTeacher?->phone,
            ],
        ];

        // Get all students in this class with their attendance stats
        $students = $classRoom->students()
            ->with('classRoom')
            ->get()
            ->map(function ($student) use ($classRoom) {
                // Get attendance records for this student in this class with subject details
                $attendanceRecords = \App\Models\Attendance::where('class_room_id', $classRoom->id)
                    ->where('student_id', $student->id)
                    ->with('subject')
                    ->orderBy('date', 'desc')
                    ->get();

                // Calculate stats
                $stats = [
                    'hadir' => $attendanceRecords->where('status', 'HADIR')->count(),
                    'sakit' => $attendanceRecords->where('status', 'SAKIT')->count(),
                    'izin' => $attendanceRecords->where('status', 'IZIN')->count(),
                    'alfa' => $attendanceRecords->where('status', 'ALFA')->count(),
                    'total' => $attendanceRecords->count(),
                ];

                // Build attendance details with subject names and dates
                $attendanceDetails = [
                    'sakit' => $attendanceRecords->where('status', 'SAKIT')->map(function ($record) {
                        return [
                            'subject_name' => $record->subject?->name ?? 'Tidak Diketahui',
                            'date' => $record->date,
                        ];
                    })->values(),
                    'izin' => $attendanceRecords->where('status', 'IZIN')->map(function ($record) {
                        return [
                            'subject_name' => $record->subject?->name ?? 'Tidak Diketahui',
                            'date' => $record->date,
                        ];
                    })->values(),
                    'alfa' => $attendanceRecords->where('status', 'ALFA')->map(function ($record) {
                        return [
                            'subject_name' => $record->subject?->name ?? 'Tidak Diketahui',
                            'date' => $record->date,
                        ];
                    })->values(),
                ];

                // Calculate attendance rate
                $attendanceRate = $stats['total'] > 0 
                    ? round(($stats['hadir'] / $stats['total']) * 100, 1) 
                    : 0;

                return [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'gender' => $student->gender,
                    'created_at' => $student->created_at,
                    'attendance_stats' => $stats,
                    'attendance_rate' => $attendanceRate,
                    'attendance_details' => $attendanceDetails,
                ];
            });

        // Calculate class-wide attendance summary
        $allAttendances = \App\Models\Attendance::where('class_room_id', $classRoom->id)->get();
        
        $classAttendanceSummary = [
            'total_hadir' => $allAttendances->where('status', 'HADIR')->count(),
            'total_sakit' => $allAttendances->where('status', 'SAKIT')->count(),
            'total_izin' => $allAttendances->where('status', 'IZIN')->count(),
            'total_alfa' => $allAttendances->where('status', 'ALFA')->count(),
            'total_records' => $allAttendances->count(),
        ];

        // Get statistics
        $stats = [
            'total_students' => $classRoom->students->count(),
            'male_count' => $classRoom->students->where('gender', 'M')->count(),
            'female_count' => $classRoom->students->where('gender', 'F')->count(),
        ];

        return Inertia::render('Guru/WaliKelas/Show', [
            'classRoom' => $classData,
            'students' => $students,
            'stats' => $stats,
            'classAttendanceSummary' => $classAttendanceSummary,
            'teacherName' => $teacher->name,
        ]);
    }

    /**
     * Export attendance summary to Excel
     */
    public function export(ClassRoom $classRoom)
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return redirect()->route('guru.dashboard')->with('error', 'Data guru tidak ditemukan');
        }

        // Check if teacher is the head teacher of this class
        if ($classRoom->head_teacher_id !== $teacher->id) {
            return redirect()->route('guru.wali-kelas.index')
                ->with('error', 'Anda tidak memiliki akses ke kelas ini');
        }

        // Get class data
        $classData = [
            'name' => $classRoom->name,
            'academic_year' => $classRoom->academicYear?->name,
            'program' => $classRoom->program?->name,
        ];

        // Get all students with attendance stats
        $students = $classRoom->students()
            ->with('classRoom')
            ->get()
            ->map(function ($student) use ($classRoom) {
                $attendanceRecords = \App\Models\Attendance::where('class_room_id', $classRoom->id)
                    ->where('student_id', $student->id)
                    ->get();

                $stats = [
                    'hadir' => $attendanceRecords->where('status', 'HADIR')->count(),
                    'sakit' => $attendanceRecords->where('status', 'SAKIT')->count(),
                    'izin' => $attendanceRecords->where('status', 'IZIN')->count(),
                    'alfa' => $attendanceRecords->where('status', 'ALFA')->count(),
                    'total' => $attendanceRecords->count(),
                ];

                $attendanceRate = $stats['total'] > 0 
                    ? round(($stats['hadir'] / $stats['total']) * 100, 1) 
                    : 0;

                return [
                    'nis' => $student->nis,
                    'name' => $student->name,
                    'gender' => $student->gender,
                    'attendance_stats' => $stats,
                    'attendance_rate' => $attendanceRate,
                ];
            });

        // Class-wide attendance summary
        $allAttendances = \App\Models\Attendance::where('class_room_id', $classRoom->id)->get();
        
        $classAttendanceSummary = [
            'total_hadir' => $allAttendances->where('status', 'HADIR')->count(),
            'total_sakit' => $allAttendances->where('status', 'SAKIT')->count(),
            'total_izin' => $allAttendances->where('status', 'IZIN')->count(),
            'total_alfa' => $allAttendances->where('status', 'ALFA')->count(),
        ];

        // Statistics
        $stats = [
            'total_students' => $classRoom->students->count(),
            'male_count' => $classRoom->students->where('gender', 'M')->count(),
            'female_count' => $classRoom->students->where('gender', 'F')->count(),
        ];

        $fileName = 'Kehadiran_' . $classRoom->name . '_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(
            new AttendanceSummaryExport($classData, $students, $classAttendanceSummary, $stats),
            $fileName
        );
    }
}

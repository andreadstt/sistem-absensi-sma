<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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

    public function export(Request $request, $classRoomId)
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

        // Get unique attendance dates for this class
        $attendanceDates = Attendance::where('class_room_id', $classRoomId)
            ->where('teacher_id', $teacher->id)
            ->distinct()
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            })
            ->values();

        // Get attendance records
        $attendanceData = Attendance::where('class_room_id', $classRoomId)
            ->where('teacher_id', $teacher->id)
            ->with('student')
            ->orderBy('date', 'asc')
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m-d');
            });

        // Generate CSV content
        $csvData = [];
        
        // Header row 1: Class information
        $csvData[] = ['Rekap Kehadiran Siswa'];
        $csvData[] = ['Kelas', $classRoom->full_name ?? $classRoom->name];
        $csvData[] = ['Program', $classRoom->program->short_name ?? '-'];
        $csvData[] = ['Tahun Akademik', $classRoom->academicYear->name ?? '-'];
        $csvData[] = ['Guru', $teacher->name];
        $csvData[] = ['Tanggal Export', \Carbon\Carbon::now()->format('d/m/Y H:i')];
        $csvData[] = []; // Empty row

        // Header row 2: Column headers
        $headerRow = ['No', 'Nama Siswa'];
        foreach ($attendanceDates as $date) {
            $headerRow[] = \Carbon\Carbon::parse($date)->format('d/m/Y');
        }
        $headerRow[] = 'Total Hadir';
        $headerRow[] = 'Total Sakit';
        $headerRow[] = 'Total Izin';
        $headerRow[] = 'Total Alfa';
        $csvData[] = $headerRow;

        // Data rows
        $no = 1;
        foreach ($students as $student) {
            $row = [$no++, $student->name];
            
            $totalHadir = 0;
            $totalSakit = 0;
            $totalIzin = 0;
            $totalAlfa = 0;

            foreach ($attendanceDates as $date) {
                $record = $attendanceData->get($date)?->firstWhere('student_id', $student->id);
                $status = $record ? $record->status : '-';
                $row[] = $status;

                // Count totals
                if ($status === 'HADIR') $totalHadir++;
                else if ($status === 'SAKIT') $totalSakit++;
                else if ($status === 'IZIN') $totalIzin++;
                else if ($status === 'ALFA') $totalAlfa++;
            }

            $row[] = $totalHadir;
            $row[] = $totalSakit;
            $row[] = $totalIzin;
            $row[] = $totalAlfa;
            
            $csvData[] = $row;
        }

        // Generate CSV file
        $filename = 'Absensi_' . str_replace(' ', '_', $classRoom->full_name ?? $classRoom->name) . '_' . date('Y-m-d_His') . '.csv';
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function updateAttendance(Request $request)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            return back()->withErrors(['error' => 'Teacher profile not found']);
        }

        // Validate request
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'date' => 'required|string',
            'class_room_id' => 'required|exists:class_rooms,id',
            'status' => 'required|in:HADIR,SAKIT,IZIN,ALFA',
        ]);

        // Convert date format from "dd/mm/yy" to "Y-m-d"
        $dateStr = $validated['date'];
        $dateParts = explode('/', $dateStr);
        if (count($dateParts) === 3) {
            // Format: dd/mm/yy
            $day = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
            $month = str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
            $year = '20' . $dateParts[2]; // Assuming 20xx
            $formattedDate = "{$year}-{$month}-{$day}";
        } else {
            return back()->withErrors(['error' => 'Invalid date format']);
        }

        // Verify teacher has access to this class
        $teachingAssignment = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('class_room_id', $validated['class_room_id'])
            ->first();

        if (!$teachingAssignment) {
            return back()->withErrors(['error' => 'You are not authorized to update attendance for this class']);
        }

        // Find the attendance record
        $attendance = Attendance::where('student_id', $validated['student_id'])
            ->where('class_room_id', $validated['class_room_id'])
            ->where('teacher_id', $teacher->id)
            ->where('date', $formattedDate)
            ->first();

        if (!$attendance) {
            return back()->withErrors(['error' => 'Attendance record not found']);
        }

        // Update the status
        $attendance->status = $validated['status'];
        $attendance->save();

        return back()->with('success', 'Status kehadiran berhasil diubah');
    }
}

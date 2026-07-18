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
    public function show(Request $request, $classRoomId, $subjectId)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Verify teacher mengajar kelas ini dan mata pelajaran ini
        $teachingAssignment = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('class_room_id', $classRoomId)
            ->where('subject_id', $subjectId)
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

        $attendanceQuery = Attendance::where('class_room_id', $classRoomId)
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->orderBy('date', 'desc');

        $attendanceRecords = (clone $attendanceQuery)->get();

        $availableMonthValues = $attendanceRecords->pluck('date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('Y-m');
            })
            ->unique()
            ->values();

        $requestedMonth = $request->query('month');
        $selectedMonth = $availableMonthValues->contains($requestedMonth)
            ? $requestedMonth
            : $availableMonthValues->first();

        $availableMonths = $availableMonthValues->map(function ($month) {
            $monthDate = \Carbon\Carbon::createFromFormat('Y-m-d', $month . '-01')->locale('id');

            return [
                'value' => $month,
                'label' => $monthDate->translatedFormat('F Y'),
            ];
        })->values();

        $selectedAttendanceRecords = $selectedMonth
            ? $attendanceRecords->filter(function ($record) use ($selectedMonth) {
                return \Carbon\Carbon::parse($record->date)->format('Y-m') === $selectedMonth;
            })->values()
            : collect();

        // Get unique attendance dates for the selected month only
        $attendanceDates = $selectedAttendanceRecords
            ->pluck('date')
            ->map(function ($date) {
                return \Carbon\Carbon::parse($date)->format('d/m/y');
            })
            ->unique()
            ->values();

        // Get attendance records for the selected month only
        $attendanceData = $selectedAttendanceRecords
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
                'subject_id' => $teachingAssignment->subject->id,
                'subject_name' => $teachingAssignment->subject->name,
            ],
            'students' => $studentRows,
            'attendanceDates' => $attendanceDates,
            'attendanceData' => $attendanceData,
            'availableMonths' => $availableMonths,
            'selectedMonth' => $selectedMonth,
        ]);
    }

    public function export(Request $request, $classRoomId, $subjectId)
    {
        $user = $request->user();
        $teacher = $user->teacher;

        if (!$teacher) {
            abort(403, 'Teacher profile not found');
        }

        // Verify teacher mengajar kelas ini dan mata pelajaran ini
        $teachingAssignment = \App\Models\TeachingAssignment::where('teacher_id', $teacher->id)
            ->where('class_room_id', $classRoomId)
            ->where('subject_id', $subjectId)
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

        // Get all attendance records for summary calculation
        $attendanceRecords = Attendance::where('class_room_id', $classRoomId)
            ->where('teacher_id', $teacher->id)
            ->where('subject_id', $subjectId)
            ->orderBy('date', 'asc')
            ->get()
            ->groupBy('student_id');

        // Generate CSV content
        $csvData = [];

        // Header rows: report metadata
        $csvData[] = ['Rekap Kehadiran Siswa'];
        $csvData[] = ['Kelas', $classRoom->full_name ?? $classRoom->name];
        $csvData[] = ['Mata Pelajaran', $teachingAssignment->subject->name ?? '-'];
        $csvData[] = ['Guru Pengajar', $teacher->name];
        $csvData[] = ['Tahun Akademik', $classRoom->academicYear->name ?? '-'];
        $csvData[] = ['Tanggal Export', \Carbon\Carbon::now()->format('d/m/Y H:i')];
        $csvData[] = [];

        // Summary column headers
        $csvData[] = ['No', 'NIS', 'Nama Siswa', 'Hadir', 'Sakit', 'Izin', 'Alfa', 'Persentase Kehadiran'];

        // Data rows
        $no = 1;
        foreach ($students as $student) {
            $studentAttendances = $attendanceRecords->get($student->id, collect());

            $totalHadir = 0;
            $totalSakit = 0;
            $totalIzin = 0;
            $totalAlfa = 0;

            foreach ($studentAttendances as $attendance) {
                if ($attendance->status === 'HADIR') {
                    $totalHadir++;
                } elseif ($attendance->status === 'SAKIT') {
                    $totalSakit++;
                } elseif ($attendance->status === 'IZIN') {
                    $totalIzin++;
                } elseif ($attendance->status === 'ALFA') {
                    $totalAlfa++;
                }
            }

            $totalRecord = $totalHadir + $totalSakit + $totalIzin + $totalAlfa;
            $persentaseKehadiran = $totalRecord > 0
                ? number_format(($totalHadir / $totalRecord) * 100, 1, '.', '') . '%'
                : '0.0%';

            $csvData[] = [
                $no++,
                $student->nis,
                $student->name,
                $totalHadir,
                $totalSakit,
                $totalIzin,
                $totalAlfa,
                $persentaseKehadiran,
            ];
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

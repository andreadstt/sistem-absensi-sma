<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\TeacherAttendance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KehadiranController extends Controller
{
    /**
     * Display the teacher's attendance records
     */
    public function index(Request $request)
    {
        $teacher = auth()->user()->teacher;
        
        if (!$teacher) {
            abort(403, 'Anda bukan seorang guru');
        }

        // Get all attendance records with schedule relations (new schema: per-schedule)
        $attendances = TeacherAttendance::where('teacher_id', $teacher->id)
            ->with(['schedule.classRoom', 'schedule.subject'])
            ->orderBy('date', 'desc')
            ->get();

        // Group attendances by date for the frontend
        $groupedByDate = $attendances->groupBy(function ($att) {
            return $att->date->format('Y-m-d');
        })->map(function ($dayAttendances, $dateStr) {
            $records = $dayAttendances->map(function ($att) {
                return [
                    'id' => $att->id,
                    'status' => $att->status,
                    'notes' => $att->notes,
                    'time_slot' => $att->schedule->time_slot ?? '-',
                    'class_name' => $att->schedule->classRoom->full_name ?? $att->schedule->classRoom->name ?? '-',
                    'subject_name' => $att->schedule->subject->name ?? '-',
                ];
            })->values();

            $totalJadwal = $records->count();
            $totalHadir = $records->where('status', 'HADIR')->count();
            $totalTidakHadir = $records->where('status', '!=', 'HADIR')->count();

            // Determine aggregate status for calendar coloring
            if ($totalHadir === $totalJadwal) {
                $aggregateStatus = 'ALL_HADIR';
            } elseif ($totalTidakHadir === $totalJadwal) {
                $aggregateStatus = 'ALL_TIDAK_HADIR';
            } else {
                $aggregateStatus = 'CAMPURAN';
            }

            return [
                'date' => $dateStr,
                'records' => $records,
                'total_jadwal' => $totalJadwal,
                'total_hadir' => $totalHadir,
                'total_tidak_hadir' => $totalTidakHadir,
                'aggregate_status' => $aggregateStatus,
            ];
        })->values();

        // Calculate overall statistics
        $totalRecords = $attendances->count();
        $totalHadir = $attendances->where('status', 'HADIR')->count();
        $totalTidakHadir = $attendances->where('status', '!=', 'HADIR')->count();
        $percentageHadir = $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 0;

        return Inertia::render('Guru/Kehadiran', [
            'attendancesByDate' => $groupedByDate,
            'stats' => [
                'total_records' => $totalRecords,
                'total_hadir' => $totalHadir,
                'total_tidak_hadir' => $totalTidakHadir,
                'percentage_hadir' => $percentageHadir,
            ],
            'teacherName' => $teacher->name,
        ]);
    }
}

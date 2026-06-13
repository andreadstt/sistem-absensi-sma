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

        // Get all attendance records for this teacher
        $attendances = TeacherAttendance::where('teacher_id', $teacher->id)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate statistics
        $totalRecords = $attendances->count();
        $totalHadir = $attendances->where('status', 'HADIR')->count();
        $totalTidakHadir = $attendances->where('status', 'TIDAK_HADIR')->count();
        $percentageHadir = $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 0;

        return Inertia::render('Guru/Kehadiran', [
            'attendances' => $attendances,
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

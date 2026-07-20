<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class StudentAttendanceTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Kehadiran Siswa (7 Hari Terakhir)';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $chartData = Cache::remember('student_attendance_chart_7_days_v2', 300, function () {
            $startDate = now()->subDays(6)->startOfDay();
            $endDate = now()->endOfDay();
            
            // Best Practice: 1 Query untuk mendapatkan seluruh data 7 hari (Group By)
            $attendanceData = Attendance::whereBetween('date', [$startDate, $endDate])
                ->where('status', 'HADIR')
                ->selectRaw('DATE(date) as date, COUNT(DISTINCT student_id) as total')
                ->groupByRaw('DATE(date)')
                ->pluck('total', 'date');

            $labels = [];
            $data = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateString = $date->format('Y-m-d');
                
                $labels[] = $date->translatedFormat('d M');
                $data[] = $attendanceData->get($dateString, 0); // Ambil dari collection, 0 jika kosong
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Siswa Hadir',
                    'data' => $chartData['data'],
                    'fill' => 'start',
                    'borderColor' => '#3b82f6', // Tailwind primary blue
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
            ],
            'labels' => $chartData['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}

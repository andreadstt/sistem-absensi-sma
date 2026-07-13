<?php

namespace App\Filament\Widgets;

use App\Services\TeacherAttendanceService;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class AttendanceTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Kehadiran Guru (7 Hari Terakhir)';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $chartData = Cache::remember('teacher_attendance_chart_7_days', 300, function () {
            $labels = [];
            $data = [];
            
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $stats = TeacherAttendanceService::getAttendanceStatsForDate($date);
                
                $labels[] = $date->translatedFormat('d M');
                $data[] = $stats['percentage'];
            }
            
            return [
                'labels' => $labels,
                'data' => $data,
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Persentase Kehadiran (%)',
                    'data' => $chartData['data'],
                    'fill' => 'start',
                    'borderColor' => '#10b981', // Tailwind success (emerald-500)
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
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

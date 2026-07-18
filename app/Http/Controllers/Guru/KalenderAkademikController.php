<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicEvent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KalenderAkademikController extends Controller
{
    public function index(Request $request)
    {
        $now = now();
        $month = $request->input('month', $now->month);
        $year = $request->input('year', $now->year);

        $firstDay = Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startDay = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
        $endDay = $lastDay->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $startDay->copy();
        
        // Use Query Builder to filter events that overlap with the visible calendar grid
        $events = AcademicEvent::where(function($q) use ($startDay, $endDay) {
            $q->whereBetween('start_date', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
              ->orWhereBetween('end_date', [$startDay->format('Y-m-d'), $endDay->format('Y-m-d')])
              ->orWhere(function($sq) use ($startDay, $endDay) {
                  $sq->where('start_date', '<', $startDay->format('Y-m-d'))
                     ->where('end_date', '>', $endDay->format('Y-m-d'));
              });
        })->get();

        while ($current->lte($endDay)) {
            $dateStr = $current->format('Y-m-d');
            
            // Find events for this day using explicit format to avoid Carbon vs string comparison issues
            $dayEvents = $events->filter(function($event) use ($dateStr) {
                return $dateStr >= $event->start_date->format('Y-m-d')
                    && $dateStr <= $event->end_date->format('Y-m-d');
            })->values()->toArray();
            
            $days[] = [
                'date' => $dateStr,
                'day' => $current->day,
                'isCurrentMonth' => $current->month == $month,
                'isToday' => $current->isToday(),
                'isWeekend' => $current->isWeekend(),
                'events' => $dayEvents,
            ];
            $current->addDay();
        }

        return Inertia::render('Guru/KalenderAkademik', [
            'days' => $days,
            'currentMonth' => (int) $month,
            'currentYear' => (int) $year,
            'monthName' => $firstDay->translatedFormat('F'),
        ]);
    }
}

<?php

namespace App\Livewire;

use App\Models\Teacher;
use App\Models\TeacherAttendance;
use Carbon\Carbon;
use Livewire\Component;

class AdminTeacherAttendanceCalendar extends Component
{
    public $currentMonth;
    public $currentYear;
    public $selectedTeacherId = null;
    public $selectedDate = null;
    public $selectedTeacherDetail = null;
    public $showDetailModal = false;

    protected $listeners = [];

    public function mount()
    {
        $now = now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    public function previousMonth()
    {
        if ($this->currentMonth == 1) {
            $this->currentMonth = 12;
            $this->currentYear--;
        } else {
            $this->currentMonth--;
        }
    }

    public function nextMonth()
    {
        if ($this->currentMonth == 12) {
            $this->currentMonth = 1;
            $this->currentYear++;
        } else {
            $this->currentMonth++;
        }
    }

    public function goToToday()
    {
        $now = now();
        $this->currentMonth = $now->month;
        $this->currentYear = $now->year;
    }

    public function selectDate($date)
    {
        $this->selectedDate = $date;
        $this->showDetailModal = true;
    }

    public function closeModal()
    {
        $this->showDetailModal = false;
        $this->selectedDate = null;
        $this->selectedTeacherDetail = null;
    }

    public function getTeachersProperty()
    {
        return Teacher::orderBy('name')->get();
    }

    public function getAttendanceDataProperty()
    {
        $query = TeacherAttendance::query()
            ->whereYear('date', $this->currentYear)
            ->whereMonth('date', $this->currentMonth);

        if ($this->selectedTeacherId) {
            $query->where('teacher_id', $this->selectedTeacherId);
        }

        return $query->with('teacher')->get()->groupBy('teacher_id')->map(function ($records) {
            return $records->keyBy(function ($record) {
                return Carbon::parse($record->date)->format('Y-m-d');
            });
        });
    }

    public function getCalendarDaysProperty()
    {
        $firstDay = Carbon::create($this->currentYear, $this->currentMonth, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startDay = $firstDay->copy()->startOfWeek(Carbon::MONDAY);
        $endDay = $lastDay->copy()->endOfWeek(Carbon::SUNDAY);

        $days = [];
        $current = $startDay->copy();

        while ($current->lte($endDay)) {
            $days[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->day,
                'isCurrentMonth' => $current->month == $this->currentMonth,
                'isToday' => $current->isToday(),
                'isWeekend' => $current->isWeekend(),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function getDayStatusesForDate($date, $attendanceData)
    {
        $statuses = [];

        foreach ($attendanceData as $teacherId => $records) {
            if (isset($records[$date])) {
                $statuses[] = [
                    'teacher_name' => $records[$date]->teacher?->name ?? 'Unknown',
                    'status' => $records[$date]->status,
                    'notes' => $records[$date]->notes,
                ];
            }
        }

        return $statuses;
    }

    public function getSummaryStatsProperty()
    {
        $attendanceData = $this->attendanceData;
        $totalHadir = 0;
        $totalTidakHadir = 0;
        $totalRecords = 0;

        foreach ($attendanceData as $records) {
            foreach ($records as $record) {
                $totalRecords++;
                if ($record->status === 'HADIR') {
                    $totalHadir++;
                } else {
                    $totalTidakHadir++;
                }
            }
        }

        return [
            'total_records' => $totalRecords,
            'total_hadir' => $totalHadir,
            'total_tidak_hadir' => $totalTidakHadir,
            'percentage' => $totalRecords > 0 ? round(($totalHadir / $totalRecords) * 100, 1) : 0,
        ];
    }

    public function getSelectedDateDetailsProperty()
    {
        if (!$this->selectedDate) {
            return [];
        }

        $query = TeacherAttendance::with('teacher')
            ->where('date', $this->selectedDate);

        if ($this->selectedTeacherId) {
            $query->where('teacher_id', $this->selectedTeacherId);
        }

        return $query->orderBy('date')->get();
    }

    public function render()
    {
        $teachers = Teacher::orderBy('name')->get();
        $attendanceData = $this->getAttendanceDataProperty();
        $calendarDays = $this->getCalendarDaysProperty();
        $summaryStats = $this->getSummaryStatsProperty();
        $selectedDateDetails = $this->getSelectedDateDetailsProperty();

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $monthName = $monthNames[$this->currentMonth] . ' ' . $this->currentYear;

        // Pre-compute all day statuses to avoid N+1 queries
        $allDayStatuses = [];
        foreach ($calendarDays as $day) {
            $allDayStatuses[$day['date']] = $this->getDayStatusesForDate($day['date'], $attendanceData);
        }

        return view('livewire.admin-teacher-attendance-calendar', [
            'teachers' => $teachers,
            'attendanceData' => $attendanceData,
            'calendarDays' => $calendarDays,
            'summaryStats' => $summaryStats,
            'monthName' => $monthName,
            'selectedDateDetails' => $selectedDateDetails,
            'allDayStatuses' => $allDayStatuses,
        ]);
    }
}

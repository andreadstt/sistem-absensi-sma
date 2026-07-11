<?php

namespace App\Livewire;

use App\Models\Schedule;
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
        
        $this->selectedDate = $now->format('Y-m-d');
        $this->showDetailModal = true;
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
        
        $this->dispatch('scrollToDetail');
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

        // Group by date, collecting all records per date
        return $query->with(['teacher', 'schedule.subject', 'schedule.classRoom'])
            ->get()
            ->groupBy(function ($record) {
                return Carbon::parse($record->date)->format('Y-m-d');
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

        if (!isset($attendanceData[$date])) {
            return $statuses;
        }

        foreach ($attendanceData[$date] as $record) {
            $scheduleName = '';
            if ($record->schedule) {
                $subject = $record->schedule->subject->name ?? '-';
                $class = $record->schedule->classRoom->name ?? '-';
                $scheduleName = "{$subject} ({$class}) {$record->schedule->time_slot}";
            }

            $statuses[] = [
                'teacher_name' => $record->teacher?->name ?? 'Unknown',
                'status' => $record->status,
                'notes' => $record->notes,
                'schedule_name' => $scheduleName,
            ];
        }

        return $statuses;
    }

    public function getTodayStatsProperty()
    {
        $today = now()->format('Y-m-d');
        $todayWeekday = now()->dayOfWeekIso;

        // Total schedules for today (all teachers' schedules on this weekday)
        $totalSchedulesToday = Schedule::where('weekday', $todayWeekday)->count();
        
        $todayRecords = TeacherAttendance::whereDate('date', $today)->get();
        $totalHadir = $todayRecords->where('status', 'HADIR')->count();
        $totalTidakHadir = $todayRecords->where('status', 'TIDAK_HADIR')->count();

        return [
            'total_jadwal' => $totalSchedulesToday,
            'total_hadir' => $totalHadir,
            'total_tidak_hadir' => $totalTidakHadir,
            'total_tercatat' => $todayRecords->count(),
            'percentage' => $totalSchedulesToday > 0 ? round(($totalHadir / $totalSchedulesToday) * 100, 1) : 0,
        ];
    }

    public function getMonthlyStatsProperty()
    {
        $attendanceData = $this->attendanceData;
        $totalHadir = 0;
        $totalTidakHadir = 0;
        $totalRecords = 0;

        foreach ($attendanceData as $dateRecords) {
            foreach ($dateRecords as $record) {
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

        $query = TeacherAttendance::with(['teacher', 'schedule.subject', 'schedule.classRoom'])
            ->where('date', $this->selectedDate);

        if ($this->selectedTeacherId) {
            $query->where('teacher_id', $this->selectedTeacherId);
        }

        // Group by class_room_id (safe unique key), then map to structured array per class
        return $query->get()
            ->groupBy(fn ($record) => $record->schedule?->class_room_id ?? 0)
            ->sortBy(fn ($records, $classRoomId) => $records->first()?->schedule?->classRoom?->full_name ?? 'zzz')
            ->map(function ($records, $classRoomId) {
                $firstRecord = $records->first();
                $classRoom = $firstRecord?->schedule?->classRoom;
                return [
                    'class_room_id'   => $classRoomId,
                    'class_name'      => $classRoom?->full_name ?? $classRoom?->name ?? '(Kelas Tidak Diketahui)',
                    'total_hadir'     => $records->where('status', 'HADIR')->count(),
                    'total_not_hadir' => $records->where('status', '!=', 'HADIR')->count(),
                    'records'         => $records->map(fn ($r) => [
                        'teacher_name' => $r->teacher?->name ?? 'Unknown',
                        'subject_name' => $r->schedule?->subject?->name ?? '-',
                        'time_slot'    => $r->schedule?->time_slot ?? '-',
                        'status'       => $r->status,
                        'notes'        => $r->notes,
                        'recorded_at'  => $r->created_at?->format('H:i') ?? '-',
                    ])->values()->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function render()
    {
        $teachers = Teacher::orderBy('name')->get();
        $attendanceData = $this->getAttendanceDataProperty();
        $calendarDays = $this->getCalendarDaysProperty();
        $todayStats = $this->getTodayStatsProperty();
        $monthlyStats = $this->getMonthlyStatsProperty();
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
            'todayStats' => $todayStats,
            'monthlyStats' => $monthlyStats,
            'monthName' => $monthName,
            'selectedDateDetails' => $selectedDateDetails,
            'allDayStatuses' => $allDayStatuses,
        ]);
    }
}


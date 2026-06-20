<div class="ta-calendar">
    <style>
        .ta-calendar { display: flex; flex-direction: column; gap: 1.5rem; }
                .ta-calendar .ta-stat-value { font-size: 1.5rem; font-weight: 700; line-height: 1.2; }
                .ta-calendar .ta-stat-label { font-size: 0.75rem; font-weight: 500; color: rgb(107 114 128); }
                .dark .ta-calendar .ta-stat-label { color: rgb(156 163 175); }
                .ta-calendar .ta-stat-value--success { color: rgb(5 150 105); }
                .ta-calendar .ta-stat-value--danger { color: rgb(220 38 38); }
                .ta-calendar .ta-stat-value--warning { color: rgb(217 119 6); }
                .ta-calendar .ta-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
                .ta-calendar .ta-month-nav { display: flex; align-items: center; gap: 0.5rem; }
                .ta-calendar .ta-month-title { min-width: 10rem; text-align: center; font-size: 1rem; font-weight: 600; }
                .ta-calendar .ta-legend { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; font-size: 0.75rem; }
                .ta-calendar .ta-legend-item { display: inline-flex; align-items: center; gap: 0.375rem; }
                .ta-calendar .ta-dot { width: 0.75rem; height: 0.75rem; border-radius: 9999px; flex-shrink: 0; }
                .ta-calendar .ta-dot--success { background: rgb(16 185 129); }
                .ta-calendar .ta-dot--danger { background: rgb(239 68 68); }
                .ta-calendar .ta-dot--muted { background: rgb(209 213 219); }
                .ta-calendar .ta-dot--today { background: rgb(255 255 255); border: 2px solid rgb(251 191 36); }
                .ta-calendar .ta-cal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
                .ta-calendar .ta-cal-head {
                    padding: 0.75rem 0.25rem;
                    text-align: center;
                    font-size: 0.75rem;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    color: rgb(75 85 99);
                    border-bottom: 1px solid rgb(229 231 235);
                    background: rgb(249 250 251);
                }
                .dark .ta-calendar .ta-cal-head {
                    color: rgb(156 163 175);
                    border-bottom-color: rgba(255, 255, 255, 0.1);
                    background: rgba(255, 255, 255, 0.03);
                }
                .ta-calendar .ta-cal-day {
                    min-height: 5.5rem;
                    padding: 0.375rem;
                    border-right: 1px solid rgb(243 244 246);
                    border-bottom: 1px solid rgb(243 244 246);
                }
                .dark .ta-calendar .ta-cal-day {
                    border-right-color: rgba(255, 255, 255, 0.06);
                    border-bottom-color: rgba(255, 255, 255, 0.06);
                }
                .ta-calendar .ta-cal-day--outside { background: rgba(249, 250, 251, 0.6); }
                .dark .ta-calendar .ta-cal-day--outside { background: rgba(255, 255, 255, 0.02); }
                .ta-calendar .ta-cal-day--weekend { background: rgba(255, 251, 235, 0.35); }
                .dark .ta-calendar .ta-cal-day--weekend { background: rgba(251, 191, 36, 0.05); }
                .ta-calendar .ta-cal-day--clickable { cursor: pointer; }
                .ta-calendar .ta-cal-day--clickable:hover { background: rgba(239, 246, 255, 0.7); }
                .dark .ta-calendar .ta-cal-day--clickable:hover { background: rgba(59, 130, 246, 0.08); }
                .ta-calendar .ta-day-top { display: flex; align-items: center; justify-content: space-between; gap: 0.25rem; margin-bottom: 0.25rem; }
                .ta-calendar .ta-day-number { font-size: 0.875rem; font-weight: 500; color: rgb(55 65 81); }
                .dark .ta-calendar .ta-day-number { color: rgb(229 231 235); }
                .ta-calendar .ta-day-number--outside { color: rgb(156 163 175); }
                .ta-calendar .ta-day-number--today {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 1.75rem;
                    height: 1.75rem;
                    border-radius: 9999px;
                    background: rgb(245 158 11);
                    color: white;
                    font-weight: 700;
                }
                .ta-calendar .ta-day-badge {
                    font-size: 0.625rem;
                    font-weight: 600;
                    padding: 0.125rem 0.375rem;
                    border-radius: 9999px;
                    white-space: nowrap;
                }
                .ta-calendar .ta-day-badge--success { background: rgb(209 250 229); color: rgb(4 120 87); }
                .ta-calendar .ta-day-badge--danger { background: rgb(254 226 226); color: rgb(185 28 28); }
                .ta-calendar .ta-day-badge--warning { background: rgb(254 243 199); color: rgb(180 83 9); }
                .ta-calendar .ta-teacher-row { display: flex; align-items: center; gap: 0.25rem; margin-top: 0.125rem; }
                .ta-calendar .ta-teacher-dot { width: 0.375rem; height: 0.375rem; border-radius: 9999px; flex-shrink: 0; }
                .ta-calendar .ta-teacher-name { font-size: 0.625rem; color: rgb(75 85 99); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .dark .ta-calendar .ta-teacher-name { color: rgb(156 163 175); }
                .ta-calendar .ta-more { font-size: 0.625rem; color: rgb(156 163 175); font-weight: 500; }
                .ta-calendar .ta-libur { display: flex; align-items: center; justify-content: center; height: 2rem; font-size: 0.625rem; color: rgb(156 163 175); font-style: italic; }
                .ta-calendar .ta-modal-backdrop {
                    position: fixed; inset: 0; z-index: 50;
                    display: flex; align-items: center; justify-content: center;
                    padding: 1rem; background: rgba(17, 24, 39, 0.75);
                }
                .ta-calendar .ta-modal {
                    width: 100%; max-width: 32rem; max-height: 90vh; overflow: auto;
                    border-radius: 0.75rem;
                    background: rgb(255 255 255);
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                }
                .dark .ta-calendar .ta-modal { background: rgb(17 24 39); }
                .ta-calendar .ta-modal-header {
                    display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;
                    padding: 1rem 1.5rem; border-bottom: 1px solid rgb(229 231 235);
                }
                .dark .ta-calendar .ta-modal-header { border-bottom-color: rgba(255, 255, 255, 0.1); }
                .ta-calendar .ta-modal-title { font-size: 1.125rem; font-weight: 600; }
                .ta-calendar .ta-modal-subtitle { font-size: 0.875rem; color: rgb(107 114 128); margin-top: 0.125rem; }
                .ta-calendar .ta-modal-body { padding: 1rem 1.5rem; }
                .ta-calendar .ta-modal-footer {
                    display: flex; justify-content: flex-end; gap: 0.5rem;
                    padding: 0.75rem 1.5rem; border-top: 1px solid rgb(229 231 235);
                }
                .dark .ta-calendar .ta-modal-footer { border-top-color: rgba(255, 255, 255, 0.1); }
                .ta-calendar .ta-detail-item {
                    display: flex; align-items: center; gap: 0.75rem;
                    padding: 0.75rem; border-radius: 0.5rem; border: 1px solid;
                    margin-bottom: 0.75rem;
                }
                .ta-calendar .ta-detail-item--success { border-color: rgb(167 243 208); background: rgba(236, 253, 245, 0.5); }
                .ta-calendar .ta-detail-item--danger { border-color: rgb(254 202 202); background: rgba(254, 242, 242, 0.5); }
                .dark .ta-calendar .ta-detail-item--success { border-color: rgba(16, 185, 129, 0.3); background: rgba(16, 185, 129, 0.08); }
                .dark .ta-calendar .ta-detail-item--danger { border-color: rgba(239, 68, 68, 0.3); background: rgba(239, 68, 68, 0.08); }
                .ta-calendar .ta-detail-icon {
                    width: 2.5rem; height: 2.5rem; border-radius: 9999px;
                    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
                }
                .ta-calendar .ta-detail-icon--success { background: rgb(209 250 229); color: rgb(5 150 105); }
                .ta-calendar .ta-detail-icon--danger { background: rgb(254 226 226); color: rgb(220 38 38); }
                .ta-calendar .ta-detail-main { flex: 1; min-width: 0; }
                .ta-calendar .ta-detail-name { font-size: 0.875rem; font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
                .ta-calendar .ta-detail-status { font-size: 0.75rem; margin-top: 0.125rem; }
                .ta-calendar .ta-detail-status--success { color: rgb(5 150 105); }
                .ta-calendar .ta-detail-status--danger { color: rgb(220 38 38); }
                .ta-calendar .ta-detail-notes { font-size: 0.75rem; color: rgb(107 114 128); margin-top: 0.25rem; font-style: italic; }
                .ta-calendar .ta-summary {
                    padding: 0.75rem; border-radius: 0.5rem;
                    border: 1px solid rgb(229 231 235); background: rgb(249 250 251);
                }
                .dark .ta-calendar .ta-summary { border-color: rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.03); }
                .ta-calendar .ta-summary-row { display: flex; justify-content: space-between; font-size: 0.875rem; margin-top: 0.25rem; }
                .ta-calendar .ta-empty { text-align: center; padding: 2rem 1rem; color: rgb(107 114 128); font-size: 0.875rem; }
    </style>

    {{-- Stats Hari Ini --}}
    <h3 class="text-lg font-bold">Ringkasan Hari Ini</h3>
    <x-filament::grid :default="2" :md="4" class="gap-4">
        <x-filament::section compact>
            <div class="ta-stat-label">Total Guru</div>
            <div class="ta-stat-value">{{ $todayStats['total_guru'] }}</div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="ta-stat-label">Hadir Hari Ini</div>
            <div class="ta-stat-value ta-stat-value--success">{{ $todayStats['total_hadir'] }}</div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="ta-stat-label">Tidak Hadir Hari Ini</div>
            <div class="ta-stat-value ta-stat-value--danger">{{ $todayStats['total_tidak_hadir'] }}</div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="ta-stat-label">% Kehadiran Hari Ini</div>
            <div class="ta-stat-value ta-stat-value--warning">{{ $todayStats['percentage'] }}%</div>
        </x-filament::section>
    </x-filament::grid>

    {{-- Stats Bulan Ini --}}
    <h3 class="text-lg font-bold mt-2">Ringkasan Bulan Ini</h3>
    <x-filament::grid :default="2" :md="4" class="gap-4">
        <x-filament::section compact>
            <div class="ta-stat-label">Total Guru</div>
            <div class="ta-stat-value">{{ $monthlyStats['total_guru'] }}</div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="ta-stat-label">Hadir Bulan Ini</div>
            <div class="ta-stat-value ta-stat-value--success">{{ $monthlyStats['total_hadir'] }}</div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="ta-stat-label">Tidak Hadir Bulan Ini</div>
            <div class="ta-stat-value ta-stat-value--danger">{{ $monthlyStats['total_tidak_hadir'] }}</div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="ta-stat-label">% Kehadiran Bulan Ini</div>
            <div class="ta-stat-value ta-stat-value--warning">{{ $monthlyStats['percentage'] }}%</div>
        </x-filament::section>
    </x-filament::grid>

    {{-- Filter & navigation --}}
    <x-filament::section compact>
        <div class="ta-toolbar">
            <div class="flex items-center gap-3">
                <label for="ta-teacher-filter" class="text-sm font-medium text-gray-700 dark:text-gray-200 whitespace-nowrap">Filter Guru:</label>
                <div class="relative min-w-[14rem]">
                    <select id="ta-teacher-filter" wire:model.live="selectedTeacherId" 
                            class="block w-full rounded-lg border-0 py-1.5 pl-3 pr-10 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:focus:ring-primary-500">
                        <option value="">Semua Guru</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="ta-month-nav">
                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-m-chevron-left"
                    label="Bulan sebelumnya"
                    wire:click="previousMonth"
                />

                <x-filament::button color="gray" wire:click="goToToday">
                    Hari Ini
                </x-filament::button>

                <span class="ta-month-title">{{ $monthName }}</span>

                <x-filament::icon-button
                    color="gray"
                    icon="heroicon-m-chevron-right"
                    label="Bulan berikutnya"
                    wire:click="nextMonth"
                />
            </div>
        </div>
    </x-filament::section>

    {{-- Legend --}}
    <div class="ta-legend">
        <span class="ta-stat-label">Keterangan:</span>
        <span class="ta-legend-item"><span class="ta-dot ta-dot--success"></span> Hadir</span>
        <span class="ta-legend-item"><span class="ta-dot ta-dot--danger"></span> Tidak Hadir</span>
        <span class="ta-legend-item"><span class="ta-dot ta-dot--muted"></span> Tidak Ada Data</span>
        <span class="ta-legend-item"><span class="ta-dot ta-dot--today"></span> Hari Ini</span>
    </div>

    {{-- Calendar --}}
    <x-filament::section compact>
        <div class="ta-cal-grid">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <div class="ta-cal-head">{{ $dayName }}</div>
            @endforeach
        </div>

        <div class="ta-cal-grid">
            @foreach ($calendarDays as $day)
                @php
                    $dayStatuses = $allDayStatuses[$day['date']] ?? [];
                    $hadirCount = collect($dayStatuses)->where('status', 'HADIR')->count();
                    $tidakHadirCount = collect($dayStatuses)->where('status', 'TIDAK_HADIR')->count();
                    $hasData = count($dayStatuses) > 0;
                    $allHadir = $hasData && $tidakHadirCount === 0;
                    $allTidakHadir = $hasData && $hadirCount === 0;
                    $mixed = $hasData && $hadirCount > 0 && $tidakHadirCount > 0;

                    $dayClasses = ['ta-cal-day'];
                    if (! $day['isCurrentMonth']) {
                        $dayClasses[] = 'ta-cal-day--outside';
                    }
                    if ($day['isWeekend'] && $day['isCurrentMonth']) {
                        $dayClasses[] = 'ta-cal-day--weekend';
                    }
                    if ($day['isCurrentMonth']) {
                        $dayClasses[] = 'ta-cal-day--clickable';
                    }
                @endphp

                <div
                    @class($dayClasses)
                    @if ($day['isCurrentMonth'])
                        wire:click="selectDate('{{ $day['date'] }}')"
                    @endif
                >
                    <div class="ta-day-top">
                        <span @class([
                            'ta-day-number',
                            'ta-day-number--today' => $day['isToday'],
                            'ta-day-number--outside' => ! $day['isCurrentMonth'],
                        ])>
                            {{ $day['day'] }}
                        </span>

                        @if ($hasData && $day['isCurrentMonth'])
                            <span @class([
                                'ta-day-badge',
                                'ta-day-badge--success' => $allHadir,
                                'ta-day-badge--danger' => $allTidakHadir,
                                'ta-day-badge--warning' => $mixed,
                            ])>
                                {{ $hadirCount }}/{{ count($dayStatuses) }}
                            </span>
                        @endif
                    </div>

                    @if ($day['isCurrentMonth'] && $hasData)
                        @foreach (array_slice($dayStatuses, 0, 2) as $statusItem)
                            <div class="ta-teacher-row">
                                <span @class([
                                    'ta-teacher-dot',
                                    'ta-dot--success' => $statusItem['status'] === 'HADIR',
                                    'ta-dot--danger' => $statusItem['status'] !== 'HADIR',
                                ])></span>
                                <span class="ta-teacher-name">
                                    {{ \Illuminate\Support\Str::limit($statusItem['teacher_name'], 14) }}
                                </span>
                            </div>
                        @endforeach

                        @if (count($dayStatuses) > 2)
                            <div class="ta-more">+{{ count($dayStatuses) - 2 }} lainnya</div>
                        @endif
                    @endif

                    @if ($day['isCurrentMonth'] && ! $hasData && $day['isWeekend'])
                        <div class="ta-libur">Libur</div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-filament::section>

    {{-- Detail Section (Shown below calendar instead of modal) --}}
    @if ($showDetailModal && $selectedDate)
        <div id="attendance-detail-section" class="mt-4">
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div>
                            Detail Kehadiran - 
                            @php
                                $parsedDate = \Carbon\Carbon::parse($selectedDate);
                                $dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                                $monthNamesId = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
                            @endphp
                            {{ $dayNames[$parsedDate->dayOfWeek] }}, {{ $parsedDate->day }} {{ $monthNamesId[$parsedDate->month] }} {{ $parsedDate->year }}
                        </div>
                        <x-filament::icon-button
                            color="gray"
                            icon="heroicon-m-x-mark"
                            label="Tutup"
                            wire:click="closeModal"
                        />
                    </div>
                </x-slot>

                <div class="ta-modal-body px-0 py-2">
                    @if (count($selectedDateDetails) > 0)
                        @foreach ($selectedDateDetails as $detail)
                            <div @class([
                                'ta-detail-item',
                                'ta-detail-item--success' => $detail->status === 'HADIR',
                                'ta-detail-item--danger' => $detail->status !== 'HADIR',
                            ])>
                                <div @class([
                                    'ta-detail-icon',
                                    'ta-detail-icon--success' => $detail->status === 'HADIR',
                                    'ta-detail-icon--danger' => $detail->status !== 'HADIR',
                                ])>
                                    @if ($detail->status === 'HADIR')
                                        <x-filament::icon icon="heroicon-m-check" class="h-5 w-5" />
                                    @else
                                        <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                                    @endif
                                </div>

                                <div class="ta-detail-main">
                                    <div class="flex items-center justify-between">
                                        <div class="ta-detail-name">{{ $detail->teacher?->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-gray-500">
                                            Dicatat: {{ $detail->created_at ? $detail->created_at->format('H:i') : '-' }}
                                        </div>
                                    </div>
                                    
                                    <div @class([
                                        'ta-detail-status',
                                        'ta-detail-status--success' => $detail->status === 'HADIR',
                                        'ta-detail-status--danger' => $detail->status !== 'HADIR',
                                    ])>
                                        {{ $detail->status === 'HADIR' ? 'Hadir' : 'Tidak Hadir' }}
                                    </div>

                                    @if ($detail->teacher && $detail->teacher->schedules->count() > 0)
                                        <div class="mt-2 space-y-1">
                                            <div class="text-xs font-semibold text-gray-600 dark:text-gray-400">Jadwal Mengajar:</div>
                                            @foreach ($detail->teacher->schedules as $schedule)
                                                <div class="text-xs flex items-start gap-1">
                                                    <x-filament::icon icon="heroicon-m-clock" class="h-3 w-3 mt-0.5 text-gray-400" />
                                                    <span>
                                                        <span class="font-medium">{{ $schedule->subject->name ?? 'Mata Pelajaran' }}</span> di 
                                                        <span class="font-medium">{{ $schedule->classRoom->name ?? 'Kelas' }}</span> 
                                                        ({{ $schedule->time_slot }})
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif ($detail->teacher && $detail->teacher->schedules->count() === 0)
                                        <div class="mt-2 text-xs text-gray-500 italic">
                                            Tidak ada jadwal mengajar pada hari ini.
                                        </div>
                                    @endif

                                    @if ($detail->notes)
                                        <div class="ta-detail-notes mt-2 border-t border-gray-200 dark:border-gray-700 pt-1">
                                            <span class="font-medium">Catatan:</span> {{ $detail->notes }}
                                        </div>
                                    @endif
                                </div>

                                <x-filament::badge :color="$detail->status === 'HADIR' ? 'success' : 'danger'">
                                    {{ $detail->status === 'HADIR' ? 'H' : 'TH' }}
                                </x-filament::badge>
                            </div>
                        @endforeach

                        <div class="ta-summary">
                            <div class="ta-summary-row">
                                <span>Total guru tercatat:</span>
                                <strong>{{ count($selectedDateDetails) }}</strong>
                            </div>
                            <div class="ta-summary-row">
                                <span class="ta-detail-status--success">Hadir:</span>
                                <strong class="ta-detail-status--success">
                                    {{ collect($selectedDateDetails)->where('status', 'HADIR')->count() }}
                                </strong>
                            </div>
                            <div class="ta-summary-row">
                                <span class="ta-detail-status--danger">Tidak Hadir:</span>
                                <strong class="ta-detail-status--danger">
                                    {{ collect($selectedDateDetails)->where('status', 'TIDAK_HADIR')->count() }}
                                </strong>
                            </div>
                        </div>
                    @else
                        <div class="ta-empty">Belum ada data kehadiran untuk tanggal ini.</div>
                    @endif
                </div>
            </x-filament::section>
        </div>
        
        <script>
            document.addEventListener('livewire:initialized', () => {
                Livewire.on('scrollToDetail', () => {
                    setTimeout(() => {
                        const el = document.getElementById('attendance-detail-section');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 100);
                });
            });
        </script>
    @endif
</div>

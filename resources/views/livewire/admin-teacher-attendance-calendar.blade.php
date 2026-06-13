<div>
    {{-- Summary Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Total Rekaman</p>
                    <p class="text-xl font-bold text-gray-900">{{ $summaryStats['total_records'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Hadir</p>
                    <p class="text-xl font-bold text-emerald-600">{{ $summaryStats['total_hadir'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">Tidak Hadir</p>
                    <p class="text-xl font-bold text-red-600">{{ $summaryStats['total_tidak_hadir'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium">% Kehadiran</p>
                    <p class="text-xl font-bold text-amber-600">{{ $summaryStats['percentage'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter & Navigation Bar --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- Teacher Filter --}}
            <div class="flex items-center gap-3">
                <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter Guru:</label>
                <select wire:model.live="selectedTeacherId" class="block w-full md:w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">Semua Guru</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Month Navigation --}}
            <div class="flex items-center gap-2">
                <button wire:click="previousMonth" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <button wire:click="goToToday" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 transition-colors">
                    Hari Ini
                </button>

                <span class="text-base font-semibold text-gray-900 min-w-[160px] text-center capitalize">
                    {{ $monthName }}
                </span>

                <button wire:click="nextMonth" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-300 bg-white hover:bg-gray-50 text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-4 mb-4 px-1">
        <span class="text-xs font-medium text-gray-500">Keterangan:</span>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
            <span class="text-xs text-gray-600">Hadir</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-red-500"></span>
            <span class="text-xs text-gray-600">Tidak Hadir</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-gray-300"></span>
            <span class="text-xs text-gray-600">Tidak Ada Data</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded border-2 border-amber-400 bg-white"></span>
            <span class="text-xs text-gray-600">Hari Ini</span>
        </div>
    </div>

    {{-- Calendar Grid --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- Day Headers --}}
        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
            @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                <div class="py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                    {{ $dayName }}
                </div>
            @endforeach
        </div>

        {{-- Calendar Days --}}
        <div class="grid grid-cols-7">
            @foreach($calendarDays as $index => $day)
                @php
                    $dayStatuses = $allDayStatuses[$day['date']] ?? [];
                    $hadirCount = collect($dayStatuses)->where('status', 'HADIR')->count();
                    $tidakHadirCount = collect($dayStatuses)->where('status', 'TIDAK_HADIR')->count();
                    $hasData = count($dayStatuses) > 0;
                    $allHadir = $hasData && $tidakHadirCount === 0;
                    $allTidakHadir = $hasData && $hadirCount === 0;
                    $mixed = $hasData && $hadirCount > 0 && $tidakHadirCount > 0;
                @endphp
                <div
                    class="relative min-h-[90px] border-b border-r border-gray-100 p-1.5 transition-colors
                        {{ !$day['isCurrentMonth'] ? 'bg-gray-50/60' : '' }}
                        {{ $day['isWeekend'] && $day['isCurrentMonth'] ? 'bg-amber-50/30' : '' }}
                        {{ $day['isCurrentMonth'] ? 'hover:bg-blue-50/50 cursor-pointer' : '' }}
                    "
                    @if($day['isCurrentMonth'])
                        wire:click="selectDate('{{ $day['date'] }}')"
                    @endif
                >
                    {{-- Date Number --}}
                    <div class="flex items-center justify-between mb-1">
                        <span class="inline-flex items-center justify-center
                            {{ $day['isToday'] ? 'w-7 h-7 rounded-full bg-amber-500 text-white font-bold text-sm' : '' }}
                            {{ !$day['isToday'] && $day['isCurrentMonth'] ? 'text-sm font-medium text-gray-700' : '' }}
                            {{ !$day['isCurrentMonth'] ? 'text-sm text-gray-400' : '' }}
                        ">
                            {{ $day['day'] }}
                        </span>

                        @if($hasData && $day['isCurrentMonth'])
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full
                                {{ $allHadir ? 'bg-emerald-100 text-emerald-700' : '' }}
                                {{ $allTidakHadir ? 'bg-red-100 text-red-700' : '' }}
                                {{ $mixed ? 'bg-amber-100 text-amber-700' : '' }}
                            ">
                                {{ $hadirCount }}/{{ count($dayStatuses) }}
                            </span>
                        @endif
                    </div>

                    {{-- Status Indicators --}}
                    @if($day['isCurrentMonth'] && $hasData)
                        <div class="space-y-0.5">
                            {{-- Show first 2 teacher statuses --}}
                            @foreach(array_slice($dayStatuses, 0, 2) as $statusItem)
                                <div class="flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
                                        {{ $statusItem['status'] === 'HADIR' ? 'bg-emerald-500' : 'bg-red-500' }}
                                    "></span>
                                    <span class="text-[10px] text-gray-600 truncate">
                                        {{ \Illuminate\Support\Str::limit($statusItem['teacher_name'], 12) }}
                                    </span>
                                </div>
                            @endforeach
                            @if(count($dayStatuses) > 2)
                                <span class="text-[10px] text-gray-400 font-medium">
                                    +{{ count($dayStatuses) - 2 }} lainnya
                                </span>
                            @endif
                        </div>
                    @endif

                    {{-- Empty state for weekends with no data --}}
                    @if($day['isCurrentMonth'] && !$hasData && $day['isWeekend'])
                        <div class="flex items-center justify-center h-8">
                            <span class="text-[10px] text-gray-400 italic">Libur</span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Detail Modal --}}
    @if($showDetailModal && $selectedDate)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeModal"></div>

                {{-- Modal Content --}}
                <div class="relative inline-block w-full max-w-lg overflow-hidden text-left align-middle transition-all sm:my-8 bg-white rounded-xl shadow-xl">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                                Detail Kehadiran
                            </h3>
                            <p class="text-sm text-gray-500 mt-0.5">
                                @php
                                    $parsedDate = \Carbon\Carbon::parse($selectedDate);
                                    $dayNames = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                    $monthNamesId = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
                                    $dayName = $dayNames[$parsedDate->dayOfWeek];
                                    $monthNameId = $monthNamesId[$parsedDate->month];
                                @endphp
                                {{ $dayName }}, {{ $parsedDate->day }} {{ $monthNameId }} {{ $parsedDate->year }}
                            </p>
                        </div>
                        <button wire:click="closeModal" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="px-6 py-4">
                        @if(count($selectedDateDetails) > 0)
                            <div class="space-y-3">
                                @foreach($selectedDateDetails as $detail)
                                    <div class="flex items-center gap-3 p-3 rounded-lg border
                                        {{ $detail->status === 'HADIR' ? 'border-emerald-200 bg-emerald-50/50' : 'border-red-200 bg-red-50/50' }}
                                    ">
                                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                            {{ $detail->status === 'HADIR' ? 'bg-emerald-100' : 'bg-red-100' }}
                                        ">
                                            @if($detail->status === 'HADIR')
                                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $detail->teacher?->name ?? 'Unknown' }}</p>
                                            <p class="text-xs mt-0.5
                                                {{ $detail->status === 'HADIR' ? 'text-emerald-600' : 'text-red-600' }}
                                            ">
                                                {{ $detail->status === 'HADIR' ? 'Hadir' : 'Tidak Hadir' }}
                                            </p>
                                            @if($detail->notes)
                                                <p class="text-xs text-gray-500 mt-1 italic">{{ $detail->notes }}</p>
                                            @endif
                                        </div>
                                        <span class="flex-shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                            {{ $detail->status === 'HADIR' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white' }}
                                        ">
                                            {{ $detail->status === 'HADIR' ? 'H' : 'TH' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Day Summary --}}
                            <div class="mt-4 p-3 rounded-lg bg-gray-50 border border-gray-200">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Total guru tercatat:</span>
                                    <span class="font-semibold text-gray-900">{{ count($selectedDateDetails) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-1">
                                    <span class="text-emerald-600">Hadir:</span>
                                    <span class="font-semibold text-emerald-600">
                                        {{ collect($selectedDateDetails)->where('status', 'HADIR')->count() }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-1">
                                    <span class="text-red-600">Tidak Hadir:</span>
                                    <span class="font-semibold text-red-600">
                                        {{ collect($selectedDateDetails)->where('status', 'TIDAK_HADIR')->count() }}
                                    </span>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="text-sm text-gray-500">Belum ada data kehadiran untuk tanggal ini.</p>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="px-6 py-3 border-t border-gray-100 bg-gray-50/50 flex justify-end">
                        <button wire:click="closeModal" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

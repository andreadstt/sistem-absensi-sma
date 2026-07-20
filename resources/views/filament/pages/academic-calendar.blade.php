<x-filament-panels::page>
    <div class="ta-calendar">
        <style>
            .ta-calendar { display: flex; flex-direction: column; gap: 1.5rem; }
            .ta-calendar .ta-toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; }
            .ta-calendar .ta-month-nav { display: flex; align-items: center; justify-content: center; gap: 0.5rem; width: 100%; }
            .ta-calendar .ta-month-title { min-width: 10rem; text-align: center; font-size: 1rem; font-weight: 600; }
            .ta-calendar .ta-legend { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 1rem; font-size: 0.75rem; margin-top: 1rem; }
            .ta-calendar .ta-legend-item { display: inline-flex; align-items: center; gap: 0.375rem; }
            .ta-calendar .ta-dot { width: 0.75rem; height: 0.75rem; border-radius: 9999px; flex-shrink: 0; }
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
                min-height: 6.5rem;
                padding: 0.375rem;
                border-right: 1px solid rgb(243 244 246);
                border-bottom: 1px solid rgb(243 244 246);
            }
            .dark .ta-calendar .ta-cal-day {
                border-right-color: rgba(255, 255, 255, 0.06);
                border-bottom-color: rgba(255, 255, 255, 0.06);
            }
            .ta-calendar .ta-cal-day--outside { background: rgba(249, 250, 251, 0.6); opacity: 0.5; }
            .dark .ta-calendar .ta-cal-day--outside { background: rgba(255, 255, 255, 0.02); opacity: 0.5; }
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
            
            /* Event colors */
            .event-holiday { background-color: rgb(254 226 226); color: rgb(153 27 27); border-left: 3px solid rgb(220 38 38); }
            .dark .event-holiday { background-color: rgba(220, 38, 38, 0.2); color: rgb(252 165 165); }
            
            .event-exam { background-color: rgb(255 237 213); color: rgb(154 52 18); border-left: 3px solid rgb(234 88 12); }
            .dark .event-exam { background-color: rgba(234, 88, 12, 0.2); color: rgb(253 186 116); }
            
            .event-meeting { background-color: rgb(219 234 254); color: rgb(30 58 138); border-left: 3px solid rgb(37 99 235); }
            .dark .event-meeting { background-color: rgba(37, 99, 235, 0.2); color: rgb(147 197 253); }
            
            .event-activity { background-color: rgb(220 252 231); color: rgb(22 101 52); border-left: 3px solid rgb(22 163 74); }
            .dark .event-activity { background-color: rgba(22, 163, 74, 0.2); color: rgb(134 239 172); }
            
            .event-other { background-color: rgb(243 244 246); color: rgb(31 41 55); border-left: 3px solid rgb(75 85 99); }
            .dark .event-other { background-color: rgba(75, 85, 99, 0.2); color: rgb(209 213 219); }
            
            .event-badge {
                font-size: 0.65rem;
                padding: 0.25rem 0.375rem;
                border-radius: 0.25rem;
                margin-top: 0.25rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                cursor: pointer;
                transition: opacity 0.2s;
            }
            .event-badge:hover { opacity: 0.8; }
            
            .event-bg-holiday { background: rgb(220 38 38); }
            .event-bg-exam { background: rgb(234 88 12); }
            .event-bg-meeting { background: rgb(37 99 235); }
            .event-bg-activity { background: rgb(22 163 74); }
            .event-bg-other { background: rgb(75 85 99); }
        </style>

        <x-filament::section compact>
            @php
                $monthNames = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                ];
                $monthName = $monthNames[$currentMonth] . ' ' . $currentYear;
                $calendarDays = $this->getCalendarDaysProperty();
            @endphp

            <div class="ta-toolbar">
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

            <div class="ta-legend">
                <span class="ta-legend-item"><span class="ta-dot event-bg-holiday"></span> Libur</span>
                <span class="ta-legend-item"><span class="ta-dot event-bg-exam"></span> Ujian</span>
                <span class="ta-legend-item"><span class="ta-dot event-bg-meeting"></span> Rapat</span>
                <span class="ta-legend-item"><span class="ta-dot event-bg-activity"></span> Kegiatan</span>
                <span class="ta-legend-item"><span class="ta-dot event-bg-other"></span> Lainnya</span>
            </div>
        </x-filament::section>

        <x-filament::section compact>
            <div class="overflow-x-auto w-full">
                <div class="min-w-[700px]">
                    <div class="ta-cal-grid">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $dayName)
                    <div class="ta-cal-head">{{ $dayName }}</div>
                @endforeach
            </div>

            <div class="ta-cal-grid">
                @foreach ($calendarDays as $day)
                    @php
                        $dayClasses = ['ta-cal-day'];
                        if (! $day['isCurrentMonth']) {
                            $dayClasses[] = 'ta-cal-day--outside';
                        }
                    @endphp

                    <div @class($dayClasses)>
                        <div class="ta-day-top">
                            <span @class([
                                'ta-day-number',
                                'ta-day-number--today' => $day['isToday'],
                                'ta-day-number--outside' => ! $day['isCurrentMonth'],
                            ])>
                                {{ $day['day'] }}
                            </span>
                        </div>

                        @if (count($day['events']) > 0)
                            @foreach ($day['events'] as $event)
                                @php
                                    $typeClass = 'event-other';
                                    if ($event['type'] == 'holiday') $typeClass = 'event-holiday';
                                    elseif ($event['type'] == 'exam') $typeClass = 'event-exam';
                                    elseif ($event['type'] == 'meeting') $typeClass = 'event-meeting';
                                    elseif ($event['type'] == 'activity') $typeClass = 'event-activity';
                                @endphp
                                <div class="event-badge {{ $typeClass }}"
                                     wire:click="$dispatch('open-modal', { id: 'event-detail-{{ $event['id'] }}' })">
                                    {{ $event['title'] }}
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Render all event modals ONCE outside the date loop to avoid duplicate IDs --}}
        @php
            $allEvents = collect($calendarDays)->flatMap(fn($d) => $d['events'])->unique('id')->values();
        @endphp

        @foreach ($allEvents as $event)
            <x-filament::modal id="event-detail-{{ $event['id'] }}" width="md">
                <x-slot name="heading">Detail Event</x-slot>

                <div class="space-y-4">
                    <div>
                        <div class="font-bold text-gray-500 dark:text-gray-400 text-sm">Nama Kegiatan</div>
                        <div class="text-lg font-semibold">{{ $event['title'] }}</div>
                    </div>
                    <div>
                        <div class="font-bold text-gray-500 dark:text-gray-400 text-sm">Jenis</div>
                        <div>
                            @if ($event['type'] == 'holiday') Hari Libur
                            @elseif ($event['type'] == 'exam') Ujian
                            @elseif ($event['type'] == 'meeting') Rapat
                            @elseif ($event['type'] == 'activity') Kegiatan
                            @else Lainnya @endif
                        </div>
                    </div>
                    <div>
                        <div class="font-bold text-gray-500 dark:text-gray-400 text-sm">Tanggal</div>
                        <div>
                            @if ($event['start_date'] == $event['end_date'])
                                {{ \Carbon\Carbon::parse($event['start_date'])->isoFormat('D MMMM Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($event['start_date'])->isoFormat('D MMM Y') }} &mdash; {{ \Carbon\Carbon::parse($event['end_date'])->isoFormat('D MMM Y') }}
                            @endif
                        </div>
                    </div>
                    @if (!empty($event['description']))
                    <div>
                        <div class="font-bold text-gray-500 dark:text-gray-400 text-sm">Keterangan</div>
                        <div class="whitespace-pre-line">{{ $event['description'] }}</div>
                    </div>
                    @endif

                    <div class="flex justify-end pt-4 gap-2 border-t dark:border-gray-700">
                        <x-filament::button color="gray" x-on:click="close()">
                            Tutup
                        </x-filament::button>
                        <x-filament::button
                            color="warning"
                            icon="heroicon-m-pencil-square"
                            x-on:click="close(); $nextTick(() => $wire.mountAction('editEvent', { event: {{ $event['id'] }} }))"
                        >
                            Edit Event
                        </x-filament::button>
                        <x-filament::button
                            color="danger"
                            wire:click="deleteEvent({{ $event['id'] }})"
                            wire:confirm="Apakah Anda yakin ingin menghapus event ini?"
                            x-on:click="close()">
                            Hapus Event
                        </x-filament::button>
                    </div>
                </div>
            </x-filament::modal>
        @endforeach
    </div>
</x-filament-panels::page>

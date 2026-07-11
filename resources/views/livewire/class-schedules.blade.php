<x-filament-widgets::widget>
    <x-filament::section heading="Jadwal Kelas" description="Kelola jadwal pelajaran untuk kelas ini.">
        <div class="space-y-2">
            @foreach ($weekdays as $dayNumber => $dayName)
                <div class="rounded-lg shadow-sm" style="border: 1px solid rgba(128,128,128,0.2);">
                    <div 
                        class="p-4 cursor-pointer rounded-t-lg transition"
                        style="background-color: rgba(128,128,128,0.05);"
                        wire:click="toggleDay({{ $dayNumber }})"
                    >
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium" style="color: inherit;">{{ $dayName }}</h3>
                            <svg 
                                class="w-5 h-5 transition-transform @if($openDay === $dayNumber) rotate-180 @endif"
                                style="color: inherit; opacity: 0.6;"
                                xmlns="http://www.w3.org/2000/svg" 
                                viewBox="0 0 20 20" 
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>

                    @if ($openDay === $dayNumber)
                        <div class="p-4" style="border-top: 1px solid rgba(128,128,128,0.2); background: transparent;">
                            @if (!empty($schedulesByDay[$dayNumber]))
                                <ul class="space-y-3">
                                    @foreach ($schedulesByDay[$dayNumber] as $schedule)
                                        <li class="p-3 rounded-md flex justify-between items-center" style="background-color: rgba(128,128,128,0.05); border: 1px solid rgba(128,128,128,0.1);">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-semibold">{{ $schedule['time_slot'] }}</span>
                                                <span style="opacity: 0.5;">-</span>
                                                <span>{{ $schedule['subject']['name'] }}</span>
                                                <span class="italic ml-2" style="opacity: 0.6;">({{ $schedule['teacher']['name'] }})</span>
                                            </div>
                                            <div class="space-x-2">
                                                {{ ($this->editAction)(['record' => $schedule['id']]) }}
                                                {{ ($this->deleteAction)(['record' => $schedule['id']]) }}
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p style="opacity: 0.6;">Belum ada jadwal.</p>
                            @endif

                            <div class="mt-4">
                                <button 
                                    type="button" 
                                    wire:click="openCreateModal({{ $dayNumber }})"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 transition"
                                >
                                    Tambah Jadwal
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <x-filament-actions::modals />
    </x-filament::section>
</x-filament-widgets::widget>

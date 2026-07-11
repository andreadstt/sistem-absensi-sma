<div>
    <div class="space-y-2">
        @foreach ($weekdays as $dayNumber => $dayName)
            <div class="border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm">
                <div 
                    class="p-4 cursor-pointer bg-gray-50 hover:bg-gray-100 dark:bg-gray-800/50 dark:hover:bg-gray-800 rounded-t-lg"
                    wire:click="toggleDay({{ $dayNumber }})"
                >
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">{{ $dayName }}</h3>
                        <svg 
                            class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform @if($openDay === $dayNumber) rotate-180 @endif"
                            xmlns="http://www.w3.org/2000/svg" 
                            viewBox="0 0 20 20" 
                            fill="currentColor"
                        >
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                @if ($openDay === $dayNumber)
                    <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                        @if (!empty($schedulesByDay[$dayNumber]))
                            <ul class="space-y-3">
                                @foreach ($schedulesByDay[$dayNumber] as $schedule)
                                    <li class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-md ring-1 ring-gray-900/5 dark:ring-white/10 flex justify-between items-center">
                                        <div class="flex items-center space-x-2">
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ $schedule['time_slot'] }}</span>
                                            <span class="text-gray-500 dark:text-gray-400">-</span>
                                            <span class="text-gray-800 dark:text-gray-200">{{ $schedule['subject']['name'] }}</span>
                                            <span class="text-gray-400 dark:text-gray-500 italic ml-2">({{ $schedule['teacher']['name'] }})</span>
                                        </div>
                                        <div class="space-x-2">
                                            {{ ($this->editAction)(['record' => $schedule['id']]) }}
                                            {{ ($this->deleteAction)(['record' => $schedule['id']]) }}
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500 dark:text-gray-400">Belum ada jadwal.</p>
                        @endif

                        <div class="mt-4">
                            <button 
                                type="button" 
                                wire:click="openCreateModal({{ $dayNumber }})"
                                class="inline-flex items-center justify-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-500 active:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-300 disabled:opacity-25 transition"
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
</div>

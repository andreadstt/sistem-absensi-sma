<x-filament-panels::page>
    <div x-data="{ activeView: 'calendar' }" class="space-y-6">
        {{-- Tab Switcher --}}
        <div class="flex items-center gap-1 p-1 bg-gray-100 rounded-lg w-fit">
            <button
                @click="activeView = 'calendar'"
                :class="activeView === 'calendar' ? 'bg-white shadow-sm text-amber-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm rounded-md transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Kalender
            </button>
            <button
                @click="activeView = 'table'"
                :class="activeView === 'table' ? 'bg-white shadow-sm text-amber-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                class="px-4 py-2 text-sm rounded-md transition-all flex items-center gap-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Tabel
            </button>
        </div>

        {{-- Calendar View --}}
        <div x-show="activeView === 'calendar'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            @livewire('admin-teacher-attendance-calendar')
        </div>

        {{-- Table View --}}
        <div x-show="activeView === 'table'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>

<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::tabs>
            <x-filament::tabs.item
                :active="$activeView === 'calendar'"
                icon="heroicon-o-calendar-days"
                wire:click="$set('activeView', 'calendar')"
            >
                Kalender
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :active="$activeView === 'table'"
                icon="heroicon-o-table-cells"
                wire:click="$set('activeView', 'table')"
            >
                Tabel
            </x-filament::tabs.item>
        </x-filament::tabs>

        @if ($activeView === 'calendar')
            <div>
                @livewire('admin-teacher-attendance-calendar')
            </div>
        @endif

        @if ($activeView === 'table')
            <div>
                {{ $this->table }}
            </div>
        @endif
    </div>
</x-filament-panels::page>

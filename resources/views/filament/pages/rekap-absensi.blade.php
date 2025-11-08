<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Filter Form -->
        <x-filament::section>
            <x-slot name="heading">
                Select Class and Date
            </x-slot>

            <x-slot name="description">
                Choose a class room and date to view attendance records.
            </x-slot>

            <form wire:submit.prevent="$refresh">
                {{ $this->form }}

                <div class="mt-4">
                    <x-filament::button type="submit" wire:click="$refresh">
                        View Attendance
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

        <!-- Results Table -->
        @if (!empty($this->data['class_room_id']) && !empty($this->data['date']))
            <x-filament::section>
                <x-slot name="heading">
                    Attendance Records
                </x-slot>

                <x-slot name="description">
                    Showing attendance for
                    <strong>{{ \App\Models\ClassRoom::find($this->data['class_room_id'])?->name }}</strong>
                    on
                    <strong>{{ \Carbon\Carbon::parse($this->data['date'])->format('F j, Y') }}</strong>
                </x-slot>

                {{ $this->table }}
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <div class="text-gray-400 text-lg">
                        Please select a class and date to view attendance records.
                    </div>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>

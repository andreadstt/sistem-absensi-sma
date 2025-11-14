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
        @php
            $exportType = $this->data['export_type'] ?? 'daily';
            $hasRequiredData =
                !empty($this->data['class_room_id']) &&
                (($exportType === 'daily' && !empty($this->data['date'])) ||
                    ($exportType === 'semester' && !empty($this->data['semester']) && !empty($this->data['year'])));
        @endphp

        @if ($hasRequiredData)
            <x-filament::section>
                <x-slot name="heading">
                    Attendance Records
                </x-slot>

                <x-slot name="description">
                    @if ($exportType === 'daily')
                        Showing attendance for
                        <strong>{{ \App\Models\ClassRoom::find($this->data['class_room_id'])?->name }}</strong>
                        on
                        <strong>{{ \Carbon\Carbon::parse($this->data['date'])->format('F j, Y') }}</strong>
                    @else
                        Showing semester report for
                        <strong>{{ \App\Models\ClassRoom::find($this->data['class_room_id'])?->name }}</strong>
                        -
                        <strong>Semester {{ $this->data['semester'] }} ({{ $this->data['year'] }})</strong>
                    @endif
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

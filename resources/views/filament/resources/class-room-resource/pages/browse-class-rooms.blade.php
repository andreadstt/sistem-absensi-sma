<x-filament-panels::page>
    <div x-data="{}" class="space-y-4">
        {{-- Breadcrumbs / Back Navigation --}}
        <div class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
            <span
                @class([
                    'text-gray-900 dark:text-white' => is_null($selectedGrade), // Level saat ini, tanpa styling klik
                    'cursor-pointer text-primary-600 hover:text-primary-500 hover:underline' => !is_null($selectedGrade), // Bisa diklik
                ])
                @if (!is_null($selectedGrade)) wire:click="resetSelection('all')" @endif
            >
                Semua Tingkat
            </span>

            @if (!is_null($selectedGrade))
                <x-filament::icon icon="heroicon-s-chevron-right" class="h-4 w-4 mx-1 rtl:mx-2 text-gray-400 dark:text-gray-500" />
                <span
                    @class([
                        'text-gray-900 dark:text-white' => is_null($selectedProgramId), // Level saat ini, tanpa styling klik
                        'cursor-pointer text-primary-600 hover:text-primary-500 hover:underline' => !is_null($selectedProgramId), // Bisa diklik
                    ])
                    @if (!is_null($selectedProgramId)) wire:click="resetSelection('grade')" @endif
                >
                    Tingkat {{ $selectedGrade }}
                </span>
            @endif

            @if (!is_null($selectedProgramId) && !is_null($selectedGrade))
                @php
                    $program = $this->programs()->firstWhere('id', $selectedProgramId);
                @endphp
                <x-filament::icon icon="heroicon-s-chevron-right" class="h-4 w-4 mx-1 rtl:mx-2 text-gray-400 dark:text-gray-500" />
                <span class="text-gray-900 dark:text-white">
                    {{ $program?->name }}
                </span>
            @endif
        </div>

        {{-- Level 1: Grade Selection --}}
        <div x-show="!$wire.selectedGrade" x-transition.opacity.duration.300ms>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Pilih Tingkat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse ($this->grades() as $grade)
                    <div
                        wire:click="selectGrade({{ $grade }})"
                        class="p-6 bg-white rounded-lg shadow-md hover:shadow-xl dark:bg-gray-800 dark:hover:bg-gray-700 cursor-pointer transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <h4 class="text-xl font-bold text-primary-600 dark:text-primary-400">Kelas {{ $grade }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lihat semua jurusan</p>
                    </div>
                @empty
                    <div class="col-span-full p-6 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada data kelas ditemukan. Silakan buat kelas baru terlebih dahulu.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Level 2: Program Selection --}}
        <div x-show="$wire.selectedGrade && !$wire.selectedProgramId" x-transition.opacity.duration.300ms style="display: none;">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Pilih Jurusan untuk Tingkat {{ $selectedGrade }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse ($this->programs() as $program)
                    <div
                        wire:click="selectProgram({{ $program->id }})"
                        class="p-6 bg-white rounded-lg shadow-md hover:shadow-xl dark:bg-gray-800 dark:hover:bg-gray-700 cursor-pointer transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <h4 class="text-xl font-bold text-success-600 dark:text-success-400">{{ $program->name }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lihat semua kelas</p>
                    </div>
                @empty
                    <div class="col-span-full p-6 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada program/jurusan ditemukan untuk tingkat ini.
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Level 3: Class Room Listing --}}
        <div x-show="$wire.selectedGrade && $wire.selectedProgramId" x-transition.opacity.duration.300ms style="display: none;">
            @php
                 $program = $this->programs()->firstWhere('id', $selectedProgramId);
            @endphp
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Daftar Kelas untuk {{ $program?->name }} - Tingkat {{ $selectedGrade }}</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @forelse ($this->classRooms() as $classRoom)
                    <a
                        href="{{ \App\Filament\Resources\ClassRoomResource::getUrl('edit', ['record' => $classRoom]) }}"
                        class="block p-6 bg-white rounded-lg shadow-md hover:shadow-xl dark:bg-gray-800 dark:hover:bg-gray-700 cursor-pointer transition-all duration-300 transform hover:-translate-y-1"
                    >
                        <h4 class="text-xl font-bold text-info-600 dark:text-info-400">{{ $classRoom->full_name }}</h4>
                        <div class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                            <p>Wali Kelas: <span class="font-semibold">{{ $classRoom->headTeacher?->name ?? 'Belum diatur' }}</span></p>
                            <p>Jumlah Siswa: <span class="font-semibold">{{ $classRoom->students_count }}</span></p>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full p-6 text-center text-gray-500 dark:text-gray-400">
                        Tidak ada kelas ditemukan untuk program/jurusan ini.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</x-filament-panels::page>

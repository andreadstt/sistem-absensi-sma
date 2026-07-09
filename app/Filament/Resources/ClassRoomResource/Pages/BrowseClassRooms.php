<?php

namespace App\Filament\Resources\ClassRoomResource\Pages;

use App\Filament\Resources\ClassRoomResource;
use App\Models\ClassRoom;
use App\Models\Program;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;

class BrowseClassRooms extends Page
{
    protected static string $resource = ClassRoomResource::class;

    protected static string $view = 'filament.resources.class-room-resource.pages.browse-class-rooms';

    protected static ?string $title = 'Telusuri Kelas';

    public ?int $selectedGrade = null;
    public ?int $selectedProgramId = null;

    public function mount(): void
    {
        // Starts with a clean slate.
    }

    #[Computed]
    public function grades()
    {
        return ClassRoom::query()
            ->select('grade_level')
            ->distinct()
            ->orderBy('grade_level')
            ->pluck('grade_level');
    }

    #[Computed]
    public function programs()
    {
        if (!$this->selectedGrade) {
            return collect();
        }

        return Program::query()
            ->where('min_grade_level', '<=', $this->selectedGrade)
            ->whereHas('classRooms', fn($query) => $query->where('grade_level', $this->selectedGrade))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function classRooms()
    {
        if (!$this->selectedProgramId) {
            return collect();
        }

        return ClassRoom::query()
            ->where('grade_level', $this->selectedGrade)
            ->where('program_id', $this->selectedProgramId)
            ->withCount('students')
            ->orderBy('section')
            ->get();
    }

    public function selectGrade(int $grade): void
    {
        $this->selectedGrade = $grade;
        $this->selectedProgramId = null;
    }

    public function selectProgram(int $programId): void
    {
        $this->selectedProgramId = $programId;
    }

    public function resetSelection(string $level = 'all'): void
    {
        if ($level === 'all' || $level === 'grade') {
            $this->selectedGrade = null;
        }
        $this->selectedProgramId = null;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Kelas Baru')
                ->url($this->getResource()::getUrl('create')),
        ];
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->selectedProgramId && $this->selectedGrade) {
            $programName = $this->programs()->firstWhere('id', $this->selectedProgramId)?->name;
            return 'Menampilkan Kelas untuk Tingkat ' . $this->selectedGrade . ' - ' . $programName;
        }

        if ($this->selectedGrade) {
            return 'Menampilkan Jurusan untuk Tingkat ' . $this->selectedGrade;
        }

        return 'Pilih Tingkat untuk memulai';
    }
}

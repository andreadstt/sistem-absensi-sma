<?php

namespace App\Livewire;

use App\Filament\Resources\ScheduleResource;
use App\Models\ClassRoom;
use App\Models\Schedule;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class ClassSchedules extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    public ClassRoom $classRoom;
    public ?int $openDay = null;

    public function mount(ClassRoom $classRoom): void
    {
        $this->classRoom = $classRoom;
    }

    public function getSchedulesGroupedByDay(): array
    {
        return $this->classRoom->schedules()
            ->with(['teacher', 'subject'])
            ->orderBy('time_slot')
            ->get()
            ->groupBy('weekday')
            ->toArray();
    }

    public function toggleDay(int $day): void
    {
        $this->openDay = $this->openDay === $day ? null : $day;
    }
    
    public function createAction(): Action
    {
        return Action::make('create')
            ->label('Tambah Jadwal')
            ->form(ScheduleResource::getFormSchema())
            ->model(Schedule::class)
            ->action(function (array $data) {
                $this->classRoom->schedules()->create($data);
            });
    }
    
    public function editAction(): Action
    {
        return Action::make('edit')
            ->label('Edit')
            ->form(ScheduleResource::getFormSchema())
            ->model(Schedule::class)
            ->action(function (Schedule $record, array $data) {
                $record->update($data);
            });
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label('Hapus')
            ->requiresConfirmation()
            ->action(function (Schedule $record) {
                $record->delete();
            });
    }

    public function openCreateModal(int $weekday): void
    {
        $this->mountAction('create', [
            'class_room_id' => $this->classRoom->id,
            'weekday' => $weekday,
        ]);
    }

    public function render()
    {
        return view('livewire.class-schedules', [
            'schedulesByDay' => $this->getSchedulesGroupedByDay(),
            'weekdays' => [
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
            ]
        ]);
    }
}

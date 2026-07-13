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
use Filament\Widgets\Widget;

class ClassSchedules extends Widget implements HasForms, HasActions
{
    protected int | string | array $columnSpan = 'full';
    use InteractsWithForms;
    use InteractsWithActions;

    public ?ClassRoom $record = null;
    public ?int $openDay = null;

    public function mount(?ClassRoom $classRoom = null, ?ClassRoom $record = null): void
    {
        $this->record = $record ?? $classRoom ?? $this->record;
    }

    public function getSchedulesGroupedByDay(): array
    {
        if (! $this->record) return [];

        return $this->record->schedules()
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
            ->mountUsing(function (\Filament\Forms\Form $form, array $arguments) {
                $form->fill([
                    'class_room_id' => $this->record?->id,
                    'weekday' => $arguments['weekday'] ?? null,
                ]);
            })
            ->action(function (array $data) {
                $this->record->schedules()->create($data);
            });
    }
    
    public function editAction(): \Filament\Actions\EditAction
    {
        return \Filament\Actions\EditAction::make('edit')
            ->label('Edit')
            ->record(fn (array $arguments) => Schedule::find($arguments['record']))
            ->form(ScheduleResource::getFormSchema());
    }

    public function deleteAction(): \Filament\Actions\DeleteAction
    {
        return \Filament\Actions\DeleteAction::make('delete')
            ->label('Hapus')
            ->record(fn (array $arguments) => Schedule::find($arguments['record']));
    }

    public function openCreateModal(int $weekday): void
    {
        $this->mountAction('create', [
            'class_room_id' => $this->record?->id,
            'weekday' => $weekday,
        ]);
    }

    public function render(): \Illuminate\Contracts\View\View
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

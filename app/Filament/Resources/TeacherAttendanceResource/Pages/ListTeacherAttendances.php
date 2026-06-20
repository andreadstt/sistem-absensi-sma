<?php

namespace App\Filament\Resources\TeacherAttendanceResource\Pages;

use App\Filament\Resources\TeacherAttendanceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherAttendances extends ListRecords
{
    public $activeView = 'calendar';

    protected static string $resource = TeacherAttendanceResource::class;

    protected static string $view = 'filament.resources.teacher-attendance-resource.pages.list-teacher-attendances';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => $this->activeView === 'table'),
        ];
    }
}

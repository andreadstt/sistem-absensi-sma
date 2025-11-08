<?php

namespace App\Filament\Resources\TeacherRegistrationResource\Pages;

use App\Filament\Resources\TeacherRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacherRegistration extends ViewRecord
{
    protected static string $resource = TeacherRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url(static::getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }
}

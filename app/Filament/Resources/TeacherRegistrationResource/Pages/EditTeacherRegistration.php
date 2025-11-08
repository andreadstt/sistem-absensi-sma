<?php

namespace App\Filament\Resources\TeacherRegistrationResource\Pages;

use App\Filament\Resources\TeacherRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacherRegistration extends EditRecord
{
    protected static string $resource = TeacherRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}

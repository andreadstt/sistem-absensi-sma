<?php

namespace App\Filament\Resources\TeacherRegistrationResource\Pages;

use App\Filament\Resources\TeacherRegistrationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeacherRegistrations extends ListRecords
{
    protected static string $resource = TeacherRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

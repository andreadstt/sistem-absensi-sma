<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    /**
     * After creating the user, assign the 'admin' role
     * and set must_change_password to false.
     */
    protected function afterCreate(): void
    {
        $this->record->assignRole('admin');
        $this->record->update(['must_change_password' => false]);
    }
}

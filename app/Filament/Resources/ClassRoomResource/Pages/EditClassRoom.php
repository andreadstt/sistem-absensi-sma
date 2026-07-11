<?php

namespace App\Filament\Resources\ClassRoomResource\Pages;

use App\Filament\Resources\ClassRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassRoom extends EditRecord
{
    protected static string $resource = ClassRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getBackAction(): Actions\Action
    {
        return Actions\Action::make('back')
            ->label(null)
            ->url(function () {
                $referer = request()->headers->get('referer');
                $indexUrl = static::getResource()::getUrl('index');
                $browseUrl = static::getResource()::getUrl('browse'); // The drill-down page

                // Prefer going back to the browse page if the referer is not helpful
                $fallbackUrl = $browseUrl ?? $indexUrl;

                if (is_null($referer) || str_contains($referer, '/edit') || str_contains($referer, '/create')) {
                    // if #[Url] is implemented, the index URL will be a problem.
                    // The best UX is to go back to the browse page.
                    return $browseUrl;
                }

                return $referer;
            })
            ->icon('heroicon-o-arrow-left');
    }
}

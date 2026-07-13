<?php

namespace App\Filament\Widgets;

use App\Models\TeacherRegistration;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\TeacherRegistrationResource;

class LatestTeacherRegistrations extends BaseWidget
{
    protected static ?int $sort = 3;
    
    // Setting column span to 1 so it takes half width on large screens
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Pendaftaran Guru Terbaru (Pending)';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TeacherRegistration::where('status', 'pending')->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->emptyStateHeading('Tidak ada pendaftaran pending')
            ->emptyStateDescription('Semua pendaftaran telah ditinjau.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false)
            ->recordUrl(
                fn (TeacherRegistration $record): string => TeacherRegistrationResource::getUrl('view', ['record' => $record])
            );
    }
}

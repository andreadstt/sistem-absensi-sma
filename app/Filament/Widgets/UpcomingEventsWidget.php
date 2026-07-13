<?php

namespace App\Filament\Widgets;

use App\Models\AcademicEvent;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int $sort = 4;
    
    // Setting column span to 1 so it takes half width on large screens
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Acara Mendatang';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AcademicEvent::where('start_date', '>=', now()->format('Y-m-d'))
                    ->orderBy('start_date', 'asc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Acara')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'holiday' => 'danger',
                        'exam' => 'warning',
                        'meeting' => 'info',
                        'activity' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
            ])
            ->emptyStateHeading('Tidak ada acara mendatang')
            ->emptyStateDescription('Belum ada acara yang dijadwalkan.')
            ->emptyStateIcon('heroicon-o-calendar')
            ->paginated(false);
    }
}

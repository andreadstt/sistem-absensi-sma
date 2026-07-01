<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AcademicEventResource\Pages;
use App\Models\AcademicEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AcademicEventResource extends Resource
{
    protected static ?string $model = AcademicEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Kalender Akademik';

    protected static ?string $modelLabel = 'Event Akademik';

    protected static ?string $pluralModelLabel = 'Kalender Akademik';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('title')
                    ->label('Nama Kegiatan')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Jenis')
                    ->options([
                        'holiday' => 'Hari Libur',
                        'exam' => 'Ujian',
                        'meeting' => 'Rapat',
                        'activity' => 'Kegiatan',
                        'other' => 'Lainnya',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai')
                    ->required(),

                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai')
                    ->required(),

                Forms\Components\Textarea::make('description')
                    ->label('Keterangan')
                    ->rows(4),

                Forms\Components\Hidden::make
                ('created_by')
                ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('title')
                    ->label('Kegiatan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date(),

            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAcademicEvents::route('/'),
            'create' => Pages\CreateAcademicEvent::route('/create'),
            'edit' => Pages\EditAcademicEvent::route('/{record}/edit'),
        ];
    }
}
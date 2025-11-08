<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Schedule;
use App\Models\TeachingAssignment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Jadwal Pelajaran';

    protected static ?string $modelLabel = 'Jadwal';

    protected static ?string $pluralModelLabel = 'Jadwal Pelajaran';

    protected static ?string $navigationGroup = 'Manajemen Pengajar';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set) {
                        $set('subject_id', null);
                        $set('class_room_id', null);
                    }),
                Forms\Components\Select::make('subject_id')
                    ->label('Mata Pelajaran')
                    ->options(function (Get $get) {
                        $teacherId = $get('teacher_id');
                        if (!$teacherId) {
                            return [];
                        }
                        return TeachingAssignment::where('teacher_id', $teacherId)
                            ->with('subject')
                            ->get()
                            ->pluck('subject.name', 'subject_id')
                            ->unique();
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Forms\Set $set) {
                        $set('class_room_id', null);
                    })
                    ->helperText('Hanya mapel yang diampu oleh guru terpilih'),
                Forms\Components\Select::make('class_room_id')
                    ->label('Kelas')
                    ->options(function (Get $get) {
                        $teacherId = $get('teacher_id');
                        $subjectId = $get('subject_id');
                        if (!$teacherId || !$subjectId) {
                            return [];
                        }
                        return TeachingAssignment::where('teacher_id', $teacherId)
                            ->where('subject_id', $subjectId)
                            ->with('classRoom')
                            ->get()
                            ->pluck('classRoom.name', 'class_room_id');
                    })
                    ->searchable()
                    ->required()
                    ->helperText('Hanya kelas yang diampu guru untuk mapel terpilih'),
                Forms\Components\Select::make('weekday')
                    ->label('Hari')
                    ->options([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('time_slot')
                    ->label('Jam Pelajaran')
                    ->placeholder('07:00-08:00')
                    ->helperText('Format: HH:MM-HH:MM (contoh: 07:00-08:00)')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^\d{2}:\d{2}-\d{2}:\d{2}$/')
                    ->validationMessages([
                        'regex' => 'Format jam harus HH:MM-HH:MM (contoh: 07:00-08:00)',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('classRoom.full_name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('weekday')
                    ->label('Hari')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                        default => '-',
                    }),
                Tables\Columns\TextColumn::make('time_slot')
                    ->label('Jam Pelajaran')
                    ->sortable()
                    ->badge()
                    ->color('warning'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('class_room_id')
                    ->label('Kelas')
                    ->relationship('classRoom', 'name')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('weekday')
                    ->label('Hari')
                    ->options([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('weekday');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }
}

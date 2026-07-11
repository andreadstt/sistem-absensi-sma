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

    protected static ?string $navigationLabel = 'Jadwal Kelas';

    protected static ?string $modelLabel = 'Jadwal';

    protected static ?string $pluralModelLabel = 'Jadwal Kelas';

    protected static ?string $navigationGroup = 'Jadwal Kelas';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema(self::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        return [
            Forms\Components\Section::make('Penugasan Mengajar')
                ->description('Pilih Kelas, guru, dan mata pelajaran. Penugasan akan dibuat otomatis jika belum ada.')
                ->schema([
                    Forms\Components\Select::make('class_room_id')
                        ->label('Kelas')
                        ->relationship(
                            'classRoom',
                            'name',
                            fn(Builder $query) => $query
                                ->whereHas('academicYear', fn($q) => $q->where('is_active', true))
                                ->orderBy('grade_level')
                                ->orderBy('name')
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Hanya kelas dari tahun ajaran aktif'),
                    Forms\Components\Select::make('teacher_id')
                        ->label('Guru')
                        ->relationship('teacher', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live(),
                    Forms\Components\Select::make('subject_id')
                        ->label('Mata Pelajaran')
                        ->relationship('subject', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                ])
                ->columns(3),
            Forms\Components\Section::make('Jadwal Pelajaran')
                ->description('Tentukan hari dan jam untuk mata pelajaran ini.')
                ->schema([
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
                        ])
                        ->rules([
                            fn(Get $get, $component): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $component) {
                                $recordId = $component->getRecord()->id ?? null;
                                $weekday = $get('weekday');
                                $teacherId = $get('teacher_id');
                                $classRoomId = $get('class_room_id');
                                $timeSlot = $value;

                                if (!$weekday || !$teacherId || !$classRoomId || !$timeSlot) {
                                    return;
                                }

                                $timeParts = explode('-', $timeSlot);
                                if (count($timeParts) !== 2) {
                                    return;
                                }

                                $newStartTime = strtotime($timeParts[0]);
                                $newEndTime = strtotime($timeParts[1]);

                                if ($newStartTime === false || $newEndTime === false || $newStartTime >= $newEndTime) {
                                    $fail('Format jam pelajaran tidak valid atau jam mulai lebih besar dari/sama dengan jam selesai.');
                                    return;
                                }

                                $conflictingSchedules = Schedule::where('weekday', $weekday)
                                    ->where(function ($query) use ($teacherId, $classRoomId) {
                                        $query->where('teacher_id', $teacherId)
                                            ->orWhere('class_room_id', $classRoomId);
                                    })
                                    ->when($recordId, fn($query) => $query->where('id', '!=', $recordId))
                                    ->get();

                                foreach ($conflictingSchedules as $existingSchedule) {
                                    $existingTimeParts = explode('-', $existingSchedule->time_slot);
                                    if (count($existingTimeParts) !== 2) {
                                        continue;
                                    }

                                    $existingStartTime = strtotime($existingTimeParts[0]);
                                    $existingEndTime = strtotime($existingTimeParts[1]);

                                    if ($existingStartTime === false || $existingEndTime === false) {
                                        continue;
                                    }

                                    if ($newStartTime < $existingEndTime && $newEndTime > $existingStartTime) {
                                        if ($existingSchedule->teacher_id == $teacherId) {
                                            $fail("Jadwal bentrok: Guru ini sudah mengajar di kelas {$existingSchedule->classRoom->name} pada jam {$existingSchedule->time_slot}.");
                                            return;
                                        }
                                        if ($existingSchedule->class_room_id == $classRoomId) {
                                            $fail("Jadwal bentrok: Ruang kelas ini sudah dipakai oleh guru {$existingSchedule->teacher->name} untuk mapel {$existingSchedule->subject->name} pada jam {$existingSchedule->time_slot}.");
                                            return;
                                        }
                                    }
                                }
                            }
                        ]),
                ])
                ->columns(2),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('classRoom.name')
                    ->label('Kelas')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(function ($record) {
                        return $record->classRoom->full_name ?? $record->classRoom->name;
                    }),
                
                Tables\Columns\TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->sortable()
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('teacher.name')
                    ->label('Guru')
                    ->sortable()
                    ->searchable(),
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

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassRoomResource\Pages;
use App\Filament\Resources\ClassRoomResource\RelationManagers;
use App\Models\ClassRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassRoomResource extends Resource
{
    protected static ?string $model = ClassRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationLabel = 'Kelas';

    protected static ?string $modelLabel = 'Kelas';

    protected static ?string $pluralModelLabel = 'Kelas';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema(fn (?ClassRoom $record) => [
                Forms\Components\Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'name')
                    ->required()
                    ->reactive()
                    ->default(fn() => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
                Forms\Components\Select::make('grade_level')
                    ->label('Tingkat')
                    ->options([
                        10 => 'Kelas 10',
                        11 => 'Kelas 11',
                        12 => 'Kelas 12',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn($state, callable $set) => $set('name', null)),
                Forms\Components\Select::make('program_id')
                    ->label('Program/Jurusan')
                    ->relationship(
                        'program',
                        'short_name',
                        fn(Builder $query, \Filament\Forms\Get $get) =>
                        $query->where('is_active', true)
                            ->where('min_grade_level', '<=', $get('grade_level') ?? 10)
                    )
                    ->required()
                    ->reactive(),
                Forms\Components\Select::make('section')
                    ->label('Kelas Paralel')
                    ->options(array_combine(range(1, 10), range(1, 10)))
                    ->required()
                    ->reactive()
                    ->validationMessages([
                        'unique' => 'Kombinasi Tahun Ajaran, Tingkat, Program, dan Kelas Paralel sudah ada.',
                    ])
                    ->unique(
                        'class_rooms',
                        'section',
                        ignoreRecord: true,
                        modifyRuleUsing: function (\Illuminate\Validation\Rules\Unique $rule, \Filament\Forms\Get $get) {
                            return $rule->where('academic_year_id', $get('academic_year_id'))
                                         ->where('grade_level', $get('grade_level'))
                                         ->where('program_id', $get('program_id'));
                        }
                    ),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kelas (otomatis)')
                    ->disabled()
                    ->maxLength(255),
                Forms\Components\Select::make('head_teacher_id')
                    ->label('Wali Kelas')
                    ->relationship('headTeacher', 'name', fn(Builder $query) => $query->orderBy('name'))
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->unique(
                        table: 'class_rooms',
                        column: 'head_teacher_id',
                        ignoreRecord: true
                    )
                    ->validationMessages([
                        'unique' => 'Guru ini sudah menjadi wali kelas di kelas lain.',
                    ])
                    ->helperText('Pilih guru yang akan menjadi wali kelas (opsional)'),

                Forms\Components\Section::make('Jadwal Kelas')
                    ->description('Kelola jadwal pelajaran untuk kelas ini.')
                    ->collapsible()
                    ->collapsed()
                    ->visible($record !== null)
                    ->schema([
                        Forms\Components\Livewire::make(\App\Livewire\ClassSchedules::class, ['classRoom' => $record])
                            ->key('class-schedules'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama Kelas')
                    ->searchable(['name', 'grade_level', 'section', 'headTeacher.name'])
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('academicYear.name')
                    ->label('Tahun Ajaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('program.short_name')
                    ->label('Program')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('headTeacher.name')
                    ->label('Wali Kelas')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('students_count')
                    ->counts('students')
                    ->label('Jumlah Siswa')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'name')
                    ->default(fn() => \App\Models\AcademicYear::where('is_active', true)->first()?->id),
                Tables\Filters\SelectFilter::make('grade_level')
                    ->label('Tingkat')
                    ->options([
                        10 => 'Kelas 10',
                        11 => 'Kelas 11',
                        12 => 'Kelas 12',
                    ]),
                Tables\Filters\SelectFilter::make('program_id')
                    ->label('Program')
                    ->relationship('program', 'short_name'),
                Tables\Filters\SelectFilter::make('head_teacher_id')
                    ->label('Wali Kelas')
                    ->relationship('headTeacher', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Kelas')
                    ->modalDescription(fn(ClassRoom $record) => 
                        "Anda akan menghapus kelas **{$record->full_name}**.\n\n" .
                        "⚠️ **Data berikut akan ikut terhapus:**\n" .
                        "- {$record->students()->count()} Siswa\n" .
                        "- {$record->schedules()->count()} Jadwal\n" .
                        "- {$record->teachingAssignments()->count()} Penugasan Mengajar\n" .
                        "- {$record->students()->withCount('attendances')->get()->sum('attendances_count')} Record Absensi\n\n" .
                        "Penghapusan ini **tidak dapat dibatalkan**."
                    )
                    ->modalSubmitActionLabel('Ya, Hapus Kelas')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Kelas Terpilih')
                        ->modalDescription(fn($records) => 
                            "Anda akan menghapus **{$records->count()} kelas**.\n\n" .
                            "⚠️ **Semua data terkait akan ikut terhapus** (siswa, jadwal, penugasan, absensi).\n\n" .
                            "Penghapusan ini **tidak dapat dibatalkan**."
                        )
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('grade_level')
            ->groups([
                Group::make('grade_level_with_label')->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\BrowseClassRooms::route('/'),
            'create' => Pages\CreateClassRoom::route('/create'),
            'edit' => Pages\EditClassRoom::route('/{record}/edit'),
        ];
    }
}

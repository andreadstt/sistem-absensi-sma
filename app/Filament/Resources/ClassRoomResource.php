<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassRoomResource\Pages;
use App\Filament\Resources\ClassRoomResource\RelationManagers;
use App\Models\ClassRoom;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
            ->schema([
                Forms\Components\Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'name')
                    ->required()
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
                        fn(Builder $query, Forms\Get $get) =>
                        $query->where('is_active', true)
                            ->where('min_grade_level', '<=', $get('grade_level') ?? 10)
                    )
                    ->required()
                    ->reactive(),
                Forms\Components\TextInput::make('section')
                    ->label('Rombel (A, B, C, ...)')
                    ->placeholder('A')
                    ->maxLength(10)
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Kelas (opsional)')
                    ->helperText('Kosongkan untuk generate otomatis dari tingkat + program + rombel')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nama Kelas')
                    ->searchable(['name', 'grade_level', 'section'])
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
            ->defaultSort('grade_level');
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
            'index' => Pages\ListClassRooms::route('/'),
            'create' => Pages\CreateClassRoom::route('/create'),
            'edit' => Pages\EditClassRoom::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\RelationManagers;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Program/Jurusan';

    protected static ?string $modelLabel = 'Program';

    protected static ?string $pluralModelLabel = 'Program/Jurusan';

    protected static ?string $navigationGroup = 'Akademik';

    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->label('Kode Program')
                    ->placeholder('MIPA')
                    ->required()
                    ->maxLength(20)
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('name')
                    ->label('Nama Program')
                    ->placeholder('Matematika dan Ilmu Pengetahuan Alam')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('short_name')
                    ->label('Nama Pendek')
                    ->placeholder('MIPA')
                    ->required()
                    ->maxLength(50),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000),
                Forms\Components\Select::make('min_grade_level')
                    ->label('Tingkat Minimal')
                    ->options([
                        10 => 'Kelas 10',
                        11 => 'Kelas 11',
                        12 => 'Kelas 12',
                    ])
                    ->default(10)
                    ->required()
                    ->helperText('Tingkat kelas minimal untuk program ini'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('short_name')
                    ->label('Nama Pendek')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('min_grade_level')
                    ->label('Tingkat Minimal')
                    ->formatStateUsing(fn($state) => "Kelas {$state}")
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                Tables\Columns\TextColumn::make('class_rooms_count')
                    ->label('Jumlah Kelas')
                    ->counts('classRooms')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                Tables\Filters\SelectFilter::make('min_grade_level')
                    ->label('Tingkat Minimal')
                    ->options([
                        10 => 'Kelas 10',
                        11 => 'Kelas 11',
                        12 => 'Kelas 12',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->modalHeading('⚠️ PERINGATAN KERAS: Hapus Program/Jurusan')
                    ->modalDescription(function ($record) {
                        $classCount = $record->classRooms()->count();
                        $studentCount = \App\Models\Student::whereHas('classRoom', function ($query) use ($record) {
                            $query->where('program_id', $record->id);
                        })->count();
                        $assignmentCount = \App\Models\TeachingAssignment::whereHas('classRoom', function ($query) use ($record) {
                            $query->where('program_id', $record->id);
                        })->count();
                        $scheduleCount = \App\Models\Schedule::whereHas('classRoom', function ($query) use ($record) {
                            $query->where('program_id', $record->id);
                        })->count();
                        $attendanceCount = \App\Models\Attendance::whereHas('classRoom', function ($query) use ($record) {
                            $query->where('program_id', $record->id);
                        })->count();

                        return new \Illuminate\Support\HtmlString("
                            <div class='text-left space-y-4'>
                                <div class='bg-red-50 border-2 border-red-500 p-4 rounded-lg'>
                                    <p class='font-bold text-red-900 text-lg mb-2'>🚨 TINDAKAN INI TIDAK DAPAT DIBATALKAN!</p>
                                    <p class='text-red-800'>Menghapus program <strong>{$record->short_name} ({$record->name})</strong> akan secara otomatis menghapus SEMUA data terkait:</p>
                                </div>
                                
                                <div class='bg-yellow-50 border border-yellow-400 p-4 rounded-lg space-y-2'>
                                    <p class='font-semibold text-yellow-900 mb-2'>📊 Data yang akan TERHAPUS PERMANEN:</p>
                                    <ul class='list-disc list-inside space-y-1 text-yellow-900'>
                                        <li><strong>{$classCount} Kelas</strong> yang menggunakan program ini</li>
                                        <li><strong>{$studentCount} Siswa</strong> yang terdaftar di kelas program ini</li>
                                        <li><strong>{$assignmentCount} Penugasan Mengajar</strong> untuk kelas program ini</li>
                                        <li><strong>{$scheduleCount} Jadwal Pelajaran</strong> untuk kelas program ini</li>
                                        <li><strong>{$attendanceCount} Data Absensi</strong> dari kelas program ini</li>
                                    </ul>
                                </div>

                                <div class='bg-gray-50 border border-gray-400 p-4 rounded-lg'>
                                    <p class='font-semibold text-gray-900 mb-2'>⚠️ DAMPAK:</p>
                                    <ul class='list-disc list-inside space-y-1 text-gray-700'>
                                        <li>Semua siswa akan kehilangan data kelas dan riwayat absensi</li>
                                        <li>Guru akan kehilangan akses ke kelas dan data mengajar</li>
                                        <li>Laporan absensi historis akan hilang</li>
                                        <li>Data tidak dapat dipulihkan setelah dihapus</li>
                                    </ul>
                                </div>

                                <div class='bg-red-100 border-2 border-red-600 p-3 rounded-lg'>
                                    <p class='font-bold text-red-900'>⛔ Ketik CONFIRM di bawah untuk melanjutkan penghapusan</p>
                                </div>
                            </div>
                        ");
                    })
                    ->modalSubmitActionLabel('Ya, Hapus Permanen')
                    ->modalCancelActionLabel('Batal')
                    ->requiresConfirmation()
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->modalHeading('⚠️ PERINGATAN KERAS: Hapus Multiple Program')
                        ->modalDescription(fn($records) => new \Illuminate\Support\HtmlString("
                            <div class='text-left space-y-4'>
                                <div class='bg-red-50 border-2 border-red-500 p-4 rounded-lg'>
                                    <p class='font-bold text-red-900 text-lg mb-2'>🚨 TINDAKAN INI SANGAT BERBAHAYA!</p>
                                    <p class='text-red-800'>Anda akan menghapus <strong>{$records->count()} program</strong> sekaligus!</p>
                                </div>
                                <div class='bg-yellow-50 border border-yellow-400 p-3 rounded-lg'>
                                    <p class='text-yellow-900'>Semua kelas, siswa, penugasan, jadwal, dan data absensi dari program-program ini akan TERHAPUS PERMANEN!</p>
                                </div>
                            </div>
                        "))
                        ->modalSubmitActionLabel('Ya, Hapus Semua')
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ])
            ->defaultSort('code');
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
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\AcademicYear;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class RekapAbsensi extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Rekap Absensi';

    protected static ?string $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.rekap-absensi';

    public ?array $data = [];
    public $classRoomId = null;
    public $date = null;
    public $exportType = 'daily'; // daily or semester

    public function mount(): void
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        
        $this->form->fill([
            'class_room_id' => null,
            'date' => now()->format('Y-m-d'),
            'export_type' => 'daily',
            'semester' => null,
            'year' => $activeYear ? $activeYear->name : now()->year,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('class_room_id')
                    ->label('Kelas')
                    ->options(ClassRoom::all()->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live(),
                Select::make('export_type')
                    ->label('Tipe Rekap')
                    ->options([
                        'daily' => 'Harian',
                        'semester' => 'Per Semester',
                    ])
                    ->default('daily')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        // Reset fields when changing type
                        if ($state === 'semester') {
                            $this->data['date'] = null;
                        } else {
                            $this->data['semester'] = null;
                        }
                    }),
                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now())
                    ->live()
                    ->visible(fn($get) => $get('export_type') === 'daily'),
                Select::make('year')
                    ->label('Tahun Ajaran')
                    ->options(function () {
                        return AcademicYear::orderBy('start_year', 'desc')
                            ->get()
                            ->pluck('name', 'name');
                    })
                    ->default(function () {
                        $activeYear = AcademicYear::where('is_active', true)->first();
                        return $activeYear ? $activeYear->name : null;
                    })
                    ->required()
                    ->live()
                    ->visible(fn($get) => $get('export_type') === 'semester'),
                Select::make('semester')
                    ->label('Semester')
                    ->options(function (callable $get) {
                        $yearName = $get('year');
                        if (!$yearName) {
                            return ['1' => 'Semester 1', '2' => 'Semester 2'];
                        }
                        
                        $academicYear = AcademicYear::where('name', $yearName)->with('semesters')->first();
                        if (!$academicYear || $academicYear->semesters->isEmpty()) {
                            return ['1' => 'Semester 1', '2' => 'Semester 2'];
                        }
                        
                        $options = [];
                        foreach ($academicYear->semesters as $semester) {
                            $typeLabel = $semester->type == '1' ? 'Semester 1' : 'Semester 2';
                            
                            \Carbon\Carbon::setLocale('id');
                            $start = \Carbon\Carbon::parse($semester->start_date)->translatedFormat('M Y');
                            $end = \Carbon\Carbon::parse($semester->end_date)->translatedFormat('M Y');
                            
                            $options[$semester->type] = "{$typeLabel} ({$start} - {$end})";
                        }
                        
                        return $options;
                    })
                    ->required()
                    ->live()
                    ->visible(fn($get) => $get('export_type') === 'semester'),
            ])
            ->statePath('data');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gender')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'M' => 'info',
                        'F' => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'M' => 'Laki-laki',
                        'F' => 'Perempuan',
                    }),
                // Columns for daily view
                TextColumn::make('total_sessions')
                    ->label('Total Sesi')
                    ->default(0)
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'daily'),
                TextColumn::make('hadir_sessions')
                    ->label('Sesi Hadir')
                    ->default(0)
                    ->badge()
                    ->color('success')
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'daily'),
                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if ($record->total_sessions == 0) return 'TIDAK ADA DATA';
                        return ($record->hadir_sessions >= ($record->total_sessions / 2)) ? 'HADIR' : 'TIDAK HADIR';
                    })
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'HADIR' => 'success',
                        'TIDAK HADIR' => 'danger',
                        'TIDAK ADA DATA' => 'gray',
                        default => 'gray',
                    })
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'daily'),
                // Columns for semester view
                TextColumn::make('total_days')
                    ->label('Total Sesi')
                    ->default(0)
                    ->sortable()
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'semester'),
                TextColumn::make('hadir')
                    ->label('Hadir (H)')
                    ->default(0)
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'semester'),
                TextColumn::make('sakit')
                    ->label('Sakit (S)')
                    ->default(0)
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'semester'),
                TextColumn::make('izin')
                    ->label('Izin (I)')
                    ->default(0)
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'semester'),
                TextColumn::make('alfa')
                    ->label('Alfa (A)')
                    ->default(0)
                    ->badge()
                    ->color('danger')
                    ->sortable()
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'semester'),
                TextColumn::make('percentage')
                    ->label('% Kehadiran')
                    ->default(0)
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 2) . '%')
                    ->color(function (?string $state): string {
                        $pct = (float) $state;
                        if ($pct >= 90) return 'success';
                        if ($pct >= 75) return 'warning';
                        return 'danger';
                    })
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'semester'),
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn() => $this->exportToCSV())
                    ->disabled(function () {
                        if (empty($this->data['class_room_id'])) {
                            return true;
                        }
                        $exportType = $this->data['export_type'] ?? 'daily';
                        if ($exportType === 'daily') {
                            return empty($this->data['date']);
                        }
                        return empty($this->data['semester']) || empty($this->data['year']);
                    }),
            ])
            ->paginated(false);
    }

    protected function getTableQuery(): Builder
    {
        if (empty($this->data['class_room_id'])) {
            return Student::query()->whereRaw('1 = 0');
        }

        $exportType = $this->data['export_type'] ?? 'daily';

        // For daily view
        if ($exportType === 'daily' && !empty($this->data['date'])) {
            return $this->getDailyTableQuery();
        }

        // For semester view - show statistics in table
        if ($exportType === 'semester' && !empty($this->data['semester']) && !empty($this->data['year'])) {
            return $this->getSemesterTableQuery();
        }

        return Student::query()->whereRaw('1 = 0');
    }

    protected function getDailyTableQuery(): Builder
    {
        $classRoomId = $this->data['class_room_id'];
        $date = $this->data['date'];

        return Student::query()
            ->where('students.class_room_id', $classRoomId)
            ->leftJoin('attendances', function($join) use ($classRoomId, $date) {
                $join->on('students.id', '=', 'attendances.student_id')
                     ->where('attendances.class_room_id', $classRoomId)
                     ->where('attendances.date', $date);
            })
            ->select('students.*')
            ->selectRaw('COUNT(attendances.id) as total_sessions')
            ->selectRaw('COALESCE(SUM(CASE WHEN attendances.status = \'HADIR\' THEN 1 ELSE 0 END), 0) as hadir_sessions')
            ->groupBy('students.id');
    }

    protected function getSemesterTableQuery(): Builder
    {
        $classRoomId = $this->data['class_room_id'];
        $semester = $this->data['semester'];
        $yearName = $this->data['year'] ?? null; // e.g., "2025/2026"
        
        if (!$yearName) {
            return Student::query()->whereRaw('1 = 0');
        }

        // Get academic year from database
        $academicYear = AcademicYear::where('name', $yearName)->first();
        
        if (!$academicYear) {
            return Student::query()->whereRaw('1 = 0');
        }

        // Get semester from database
        $semesterData = \App\Models\Semester::where('academic_year_id', $academicYear->id)
            ->where('type', $semester)
            ->first();

        if (!$semesterData) {
            return Student::query()->whereRaw('1 = 0');
        }

        $startDate = $semesterData->start_date->format('Y-m-d');
        $endDate = $semesterData->end_date->format('Y-m-d');

        // Return query with highly optimized join
        return Student::query()
            ->where('students.class_room_id', $classRoomId)
            ->leftJoin('attendances', function($join) use ($classRoomId, $startDate, $endDate) {
                $join->on('students.id', '=', 'attendances.student_id')
                     ->where('attendances.class_room_id', $classRoomId)
                     ->whereBetween('attendances.date', [$startDate, $endDate]);
            })
            ->select('students.*')
            ->selectRaw('COUNT(attendances.id) as total_days')
            ->selectRaw('COALESCE(SUM(CASE WHEN attendances.status = \'HADIR\' THEN 1 ELSE 0 END), 0) as hadir')
            ->selectRaw('COALESCE(SUM(CASE WHEN attendances.status = \'SAKIT\' THEN 1 ELSE 0 END), 0) as sakit')
            ->selectRaw('COALESCE(SUM(CASE WHEN attendances.status = \'IZIN\' THEN 1 ELSE 0 END), 0) as izin')
            ->selectRaw('COALESCE(SUM(CASE WHEN attendances.status = \'ALFA\' THEN 1 ELSE 0 END), 0) as alfa')
            ->selectRaw('
                ROUND(
                    CASE 
                        WHEN COUNT(attendances.id) > 0 
                        THEN (SUM(CASE WHEN attendances.status = \'HADIR\' THEN 1 ELSE 0 END) * 100.0 / COUNT(attendances.id))
                        ELSE 0 
                    END, 
                2) as percentage
            ')
            ->groupBy('students.id');
    }

    public function exportToCSV()
    {
        if (empty($this->data['class_room_id'])) {
            return;
        }

        $exportType = $this->data['export_type'] ?? 'daily';

        if ($exportType === 'semester') {
            return $this->exportSemesterToCSV();
        }

        return $this->exportDailyToCSV();
    }

    protected function exportDailyToCSV()
    {
        if (empty($this->data['class_room_id']) || empty($this->data['date'])) {
            return;
        }

        $classRoom = ClassRoom::find($this->data['class_room_id']);
        $date = $this->data['date'];

        $filename = "absensi_harian_{$classRoom->name}_{$date}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceDailyExport($classRoom, $date),
            $filename
        );
    }

    protected function exportSemesterToCSV()
    {
        if (empty($this->data['class_room_id']) || empty($this->data['semester'])) {
            return;
        }

        $classRoom = ClassRoom::find($this->data['class_room_id']);
        $semester = $this->data['semester'];
        $yearName = $this->data['year'] ?? null;

        $filename = "absensi_semester_{$semester}_{$classRoom->name}.xlsx";

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AttendanceSemesterExport($classRoom, $semester, $yearName),
            $filename
        );
    }
}

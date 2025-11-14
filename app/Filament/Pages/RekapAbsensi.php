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
                    ->options([
                        '1' => 'Semester 1 (Juli - Desember)',
                        '2' => 'Semester 2 (Januari - Juni)',
                    ])
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'HADIR' => 'success',
                        'SAKIT' => 'warning',
                        'IZIN' => 'info',
                        'ALFA' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'HADIR' => 'H',
                        'SAKIT' => 'S',
                        'IZIN' => 'I',
                        'ALFA' => 'A',
                        default => '-',
                    })
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'daily'),
                TextColumn::make('subject_name')
                    ->label('Mata Pelajaran')
                    ->default('-')
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'daily'),
                TextColumn::make('teacher_name')
                    ->label('Guru')
                    ->default('-')
                    ->visible(fn() => ($this->data['export_type'] ?? 'daily') === 'daily'),
                // Columns for semester view
                TextColumn::make('total_days')
                    ->label('Total Hari')
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
                    ->default('0%')
                    ->badge()
                    ->sortable()
                    ->color(function (?string $state): string {
                        $pct = (float) str_replace('%', '', $state ?? '0');
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

        // Get all students in the class
        $students = Student::where('class_room_id', $classRoomId)->get();

        // Get attendance records for this class and date
        $attendances = Attendance::where('class_room_id', $classRoomId)
            ->where('date', $date)
            ->with(['subject', 'teacher'])
            ->get()
            ->keyBy('student_id');

        // Map students with their attendance status
        $studentIds = $students->map(function ($student) use ($attendances) {
            $attendance = $attendances->get($student->id);
            $student->status = $attendance?->status ?? null;
            $student->subject_name = $attendance?->subject?->name ?? null;
            $student->teacher_name = $attendance?->teacher?->name ?? null;
            return $student->id;
        });

        return Student::query()
            ->whereIn('id', $studentIds)
            ->where('class_room_id', $classRoomId);
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

        // Determine semester date range based on academic year
        if ($semester == '1') {
            // Semester 1: Juli - Desember
            $startDate = "{$academicYear->start_year}-07-01";
            $endDate = "{$academicYear->start_year}-12-31";
        } else {
            // Semester 2: Januari - Juni
            $startDate = "{$academicYear->end_year}-01-01";
            $endDate = "{$academicYear->end_year}-06-30";
        }

        // Return query with calculated attributes
        return Student::query()
            ->where('class_room_id', $classRoomId)
            ->select('students.*')
            ->selectRaw('
                (SELECT COUNT(*) FROM attendances 
                 WHERE attendances.student_id = students.id 
                 AND attendances.class_room_id = ? 
                 AND attendances.date BETWEEN ? AND ?) as total_days',
                [$classRoomId, $startDate, $endDate]
            )
            ->selectRaw('
                (SELECT COUNT(*) FROM attendances 
                 WHERE attendances.student_id = students.id 
                 AND attendances.class_room_id = ? 
                 AND attendances.date BETWEEN ? AND ?
                 AND attendances.status = "HADIR") as hadir',
                [$classRoomId, $startDate, $endDate]
            )
            ->selectRaw('
                (SELECT COUNT(*) FROM attendances 
                 WHERE attendances.student_id = students.id 
                 AND attendances.class_room_id = ? 
                 AND attendances.date BETWEEN ? AND ?
                 AND attendances.status = "SAKIT") as sakit',
                [$classRoomId, $startDate, $endDate]
            )
            ->selectRaw('
                (SELECT COUNT(*) FROM attendances 
                 WHERE attendances.student_id = students.id 
                 AND attendances.class_room_id = ? 
                 AND attendances.date BETWEEN ? AND ?
                 AND attendances.status = "IZIN") as izin',
                [$classRoomId, $startDate, $endDate]
            )
            ->selectRaw('
                (SELECT COUNT(*) FROM attendances 
                 WHERE attendances.student_id = students.id 
                 AND attendances.class_room_id = ? 
                 AND attendances.date BETWEEN ? AND ?
                 AND attendances.status = "ALFA") as alfa',
                [$classRoomId, $startDate, $endDate]
            )
            ->selectRaw('
                CONCAT(
                    ROUND(
                        CASE 
                            WHEN (SELECT COUNT(*) FROM attendances 
                                  WHERE attendances.student_id = students.id 
                                  AND attendances.class_room_id = ? 
                                  AND attendances.date BETWEEN ? AND ?) > 0
                            THEN (
                                (SELECT COUNT(*) FROM attendances 
                                 WHERE attendances.student_id = students.id 
                                 AND attendances.class_room_id = ? 
                                 AND attendances.date BETWEEN ? AND ?
                                 AND attendances.status = "HADIR") * 100.0 / 
                                (SELECT COUNT(*) FROM attendances 
                                 WHERE attendances.student_id = students.id 
                                 AND attendances.class_room_id = ? 
                                 AND attendances.date BETWEEN ? AND ?)
                            )
                            ELSE 0
                        END, 
                        2
                    ), 
                    "%"
                ) as percentage',
                [
                    $classRoomId, $startDate, $endDate,
                    $classRoomId, $startDate, $endDate,
                    $classRoomId, $startDate, $endDate
                ]
            );
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

        $students = Student::where('class_room_id', $this->data['class_room_id'])->get();
        $attendances = Attendance::where('class_room_id', $this->data['class_room_id'])
            ->where('date', $date)
            ->with(['subject', 'teacher'])
            ->get()
            ->keyBy('student_id');

        // Generate CSV content
        $csv = "NIS,Nama Siswa,Jenis Kelamin,Status,Mata Pelajaran,Guru\n";

        foreach ($students as $student) {
            $attendance = $attendances->get($student->id);
            $status = $attendance?->status ?? '-';
            $subject = $attendance?->subject?->name ?? '-';
            $teacher = $attendance?->teacher?->name ?? '-';
            $gender = $student->gender === 'M' ? 'Laki-laki' : 'Perempuan';

            $csv .= implode(',', [
                $student->nis,
                "\"{$student->name}\"",
                $gender,
                $status,
                "\"{$subject}\"",
                "\"{$teacher}\"",
            ]) . "\n";
        }

        $filename = "absensi_harian_{$classRoom->name}_{$date}.csv";

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function exportSemesterToCSV()
    {
        if (empty($this->data['class_room_id']) || empty($this->data['semester'])) {
            return;
        }

        $classRoom = ClassRoom::find($this->data['class_room_id']);
        $semester = $this->data['semester'];
        $yearName = $this->data['year'] ?? null;

        // Get academic year from database
        $academicYear = AcademicYear::where('name', $yearName)->first();
        
        if (!$academicYear) {
            return;
        }

        // Determine semester date range
        if ($semester == '1') {
            // Semester 1: Juli - Desember
            $startDate = "{$academicYear->start_year}-07-01";
            $endDate = "{$academicYear->start_year}-12-31";
            $semesterLabel = "Semester 1 ({$academicYear->name})";
        } else {
            // Semester 2: Januari - Juni
            $startDate = "{$academicYear->end_year}-01-01";
            $endDate = "{$academicYear->end_year}-06-30";
            $semesterLabel = "Semester 2 ({$academicYear->name})";
        }

        $students = Student::where('class_room_id', $this->data['class_room_id'])->get();

        // Get all attendance records for the semester
        $attendances = Attendance::where('class_room_id', $this->data['class_room_id'])
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['subject'])
            ->get();

        // Group by student and calculate statistics
        $studentStats = [];
        foreach ($students as $student) {
            $studentAttendances = $attendances->where('student_id', $student->id);
            $totalDays = $studentAttendances->count();

            $studentStats[$student->id] = [
                'nis' => $student->nis,
                'name' => $student->name,
                'gender' => $student->gender === 'M' ? 'Laki-laki' : 'Perempuan',
                'total_days' => $totalDays,
                'hadir' => $studentAttendances->where('status', 'HADIR')->count(),
                'sakit' => $studentAttendances->where('status', 'SAKIT')->count(),
                'izin' => $studentAttendances->where('status', 'IZIN')->count(),
                'alfa' => $studentAttendances->where('status', 'ALFA')->count(),
                'percentage' => $totalDays > 0 ? round(($studentAttendances->where('status', 'HADIR')->count() / $totalDays) * 100, 2) : 0,
            ];
        }

        // Generate CSV content
        $csv = "REKAP ABSENSI {$semesterLabel}\n";
        $csv .= "Kelas: {$classRoom->name}\n";
        $csv .= "Periode: {$startDate} s/d {$endDate}\n";
        
        // Add summary statistics
        $totalRecords = collect($studentStats)->sum('total_days');
        $totalHadir = collect($studentStats)->sum('hadir');
        $avgPercentage = collect($studentStats)->avg('percentage');
        
        $csv .= "Total Rekaman Absensi: {$totalRecords}\n";
        $csv .= "Total Kehadiran: {$totalHadir}\n";
        $csv .= "Rata-rata Kehadiran: " . round($avgPercentage, 2) . "%\n\n";
        
        $csv .= "NIS,Nama Siswa,Jenis Kelamin,Total Hari,Hadir (H),Sakit (S),Izin (I),Alfa (A),Persentase Kehadiran\n";

        foreach ($studentStats as $stats) {
            $csv .= implode(',', [
                $stats['nis'],
                "\"{$stats['name']}\"",
                $stats['gender'],
                $stats['total_days'],
                $stats['hadir'],
                $stats['sakit'],
                $stats['izin'],
                $stats['alfa'],
                $stats['percentage'] . '%',
            ]) . "\n";
        }

        $filename = "absensi_semester_{$classRoom->name}_semester{$semester}_{$academicYear->name}.csv";
        $filename = str_replace('/', '-', $filename); // Replace / with - for filename safety

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

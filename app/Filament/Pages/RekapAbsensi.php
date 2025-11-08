<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\Student;
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
        $this->form->fill([
            'class_room_id' => null,
            'date' => now()->format('Y-m-d'),
            'export_type' => 'daily',
            'semester' => null,
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
                    }),
                TextColumn::make('subject_name')
                    ->label('Mata Pelajaran')
                    ->default('-'),
                TextColumn::make('teacher_name')
                    ->label('Guru')
                    ->default('-'),
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
                        return empty($this->data['semester']);
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

        // For semester view - show empty by default, data will be in export
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
        $year = now()->year;

        // Determine semester date range
        if ($semester == '1') {
            // Semester 1: Juli - Desember
            $startDate = "{$year}-07-01";
            $endDate = "{$year}-12-31";
            $semesterLabel = "Semester 1 ({$year})";
        } else {
            // Semester 2: Januari - Juni
            $startDate = "{$year}-01-01";
            $endDate = "{$year}-06-30";
            $semesterLabel = "Semester 2 ({$year})";
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
        $csv .= "Periode: {$startDate} s/d {$endDate}\n\n";
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

        $filename = "absensi_semester_{$classRoom->name}_semester{$semester}_{$year}.csv";

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}

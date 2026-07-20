<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Schedule;
use Carbon\Carbon;

class AttendanceDailyExport implements FromCollection, WithStyles, WithColumnWidths
{
    protected $classRoom;
    protected $date;
    protected $rows = [];
    protected $dynamicSubjects = [];
    protected $stats = [
        'total_siswa' => 0,
        'hadir' => 0,
        'tidak_hadir' => 0,
    ];

    public function __construct($classRoom, $date)
    {
        $this->classRoom = $classRoom;
        $this->date = $date;
    }

    public function collection()
    {
        $data = collect();
        $currentRow = 0;

        $push = function (array $rowData) use ($data, &$currentRow) {
            $data->push($rowData);
            $currentRow++;
            return $currentRow;
        };
        $spacer = function () use ($push) {
            $push([null]);
        };

        $carbonDate = Carbon::parse($this->date);
        $dayOfWeekIso = $carbonDate->dayOfWeekIso;

        // Ambil jadwal mapel untuk kelas ini pada hari tsb, unique by subject
        $schedules = Schedule::where('class_room_id', $this->classRoom->id)
            ->where('weekday', $dayOfWeekIso)
            ->with('subject')
            ->orderBy('time_slot')
            ->get();

        $this->dynamicSubjects = $schedules->pluck('subject')
            ->filter()
            ->unique('id')
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->name])
            ->values()
            ->toArray();

        // Title
        $this->rows['title'] = $push(["Rekap Absensi Harian - {$this->classRoom->name} - " . $carbonDate->locale('id')->translatedFormat('d F Y')]);
        $spacer();
        
        if (empty($this->dynamicSubjects)) {
            $this->rows['no_schedule'] = $push(['Tidak ada jadwal pelajaran pada tanggal ini']);
            $spacer();
        }

        // Base headers
        $headers = ['NIS', 'Nama Siswa', 'Jenis Kelamin'];
        foreach ($this->dynamicSubjects as $subject) {
            $headers[] = $subject['name'];
        }
        $headers[] = 'Total Sesi';
        $headers[] = 'Sesi Hadir';
        $headers[] = 'Status';
        
        $this->rows['table_header'] = $push($headers);
        $this->rows['student_start'] = $currentRow + 1;

        $students = Student::where('class_room_id', $this->classRoom->id)->get();
        $this->stats['total_siswa'] = $students->count();
        
        $attendances = Attendance::where('class_room_id', $this->classRoom->id)
            ->where('date', $this->date)
            ->get()
            ->groupBy('student_id');

        $this->rows['danger_rows'] = [];
        $this->rows['success_rows'] = [];

        foreach ($students as $student) {
            $studentAttendances = $attendances->get($student->id, collect());
            $totalSessions = $studentAttendances->count();
            $hadirSessions = $studentAttendances->where('status', 'HADIR')->count();
            
            if ($totalSessions == 0) {
                $status = 'TIDAK ADA DATA';
                $this->rows['danger_rows'][] = $currentRow + 1;
            } else {
                if ($hadirSessions >= ($totalSessions / 2)) {
                    $status = 'HADIR';
                    $this->stats['hadir']++;
                    $this->rows['success_rows'][] = $currentRow + 1;
                } else {
                    $status = 'TIDAK HADIR';
                    $this->stats['tidak_hadir']++;
                    $this->rows['danger_rows'][] = $currentRow + 1;
                }
            }

            $gender = $student->gender === 'M' ? 'Laki-laki' : 'Perempuan';

            $rowData = [
                $student->nis,
                $student->name,
                $gender,
            ];

            // Isi nilai H/I/S/A untuk masing-masing kolom mapel Pivot
            foreach ($this->dynamicSubjects as $subject) {
                $subjectAtts = $studentAttendances->where('subject_id', $subject['id']);
                if ($subjectAtts->isNotEmpty()) {
                    $statuses = $subjectAtts->pluck('status')->map(fn($s) => match ($s) {
                        'HADIR' => 'H', 'SAKIT' => 'S', 'IZIN' => 'I', 'ALFA' => 'A',
                        default => $s,
                    })->toArray();
                    $rowData[] = implode(', ', $statuses);
                } else {
                    $rowData[] = '-';
                }
            }

            $rowData[] = $totalSessions;
            $rowData[] = $hadirSessions;
            $rowData[] = $status;

            $push($rowData);
        }
        $this->rows['student_end'] = $currentRow;

        // Summary
        $spacer();
        $this->rows['summary_start'] = $push(['RINGKASAN KEHADIRAN']);
        $push(['Total Siswa', null, $this->stats['total_siswa']]);
        $push(['Total Hadir', null, $this->stats['hadir']]);
        $push(['Total Tidak Hadir', null, $this->stats['tidak_hadir']]);
        
        $percentage = $this->stats['total_siswa'] > 0 
            ? round(($this->stats['hadir'] / $this->stats['total_siswa']) * 100, 2) 
            : 0;
            
        $this->rows['summary_end'] = $push(['Persentase Kehadiran', null, $percentage . '%']);

        return $data;
    }

    protected function getLastCol()
    {
        $colCount = 3 + count($this->dynamicSubjects) + 3; // NIS, Nama, JK + subjects + Total, Hadir, Status
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount);
    }

    public function styles(Worksheet $sheet)
    {
        $thinBorder = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFBDBDBD'],
                ],
            ],
        ];

        $r = $this->rows;
        $lastCol = $this->getLastCol();

        // ===== Title =====
        $sheet->mergeCells('A' . $r['title'] . ':' . $lastCol . $r['title']);
        $sheet->getStyle('A' . $r['title'])->getFont()->setBold(true)->setSize(14)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A' . $r['title'])->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00796B');
        $sheet->getStyle('A' . $r['title'])->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension((string) $r['title'])->setRowHeight(28);
        
        if (isset($r['no_schedule'])) {
            $sheet->mergeCells('A' . $r['no_schedule'] . ':' . $lastCol . $r['no_schedule']);
            $sheet->getStyle('A' . $r['no_schedule'])->getFont()->setItalic(true)->getColor()->setARGB('FF757575');
        }

        // ===== Table Header =====
        $headerRange = 'A' . $r['table_header'] . ':' . $lastCol . $r['table_header'];
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF37474F');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->applyFromArray($thinBorder);

        $sheet->freezePane('A' . $r['student_start']);

        // ===== Data Rows =====
        if ($r['student_start'] <= $r['student_end']) {
            $dataRange = 'A' . $r['student_start'] . ':' . $lastCol . $r['student_end'];
            $sheet->getStyle($dataRange)->applyFromArray($thinBorder);
            $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            // Center align everything except name
            $sheet->getStyle('A' . $r['student_start'] . ':A' . $r['student_end'])->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $r['student_start'] . ':' . $lastCol . $r['student_end'])->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Row Highlights
            foreach ($r['danger_rows'] as $row) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEBEE');
            }
            foreach ($r['success_rows'] as $row) {
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F8E9');
            }
        }
        
        // ===== Summary =====
        $sheet->mergeCells('A' . $r['summary_start'] . ':C' . $r['summary_start']);
        $sheet->getStyle('A' . $r['summary_start'])->getFont()->setBold(true)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A' . $r['summary_start'])->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF00796B');
        
        for ($row = $r['summary_start'] + 1; $row <= $r['summary_end']; $row++) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:C{$row}")->applyFromArray($thinBorder);
        }

        $sheet->setShowGridlines(false);

        return [];
    }

    public function columnWidths(): array
    {
        $widths = [ 'A' => 15, 'B' => 30, 'C' => 15 ];
        $currentIdx = 4; // Start at column 4 (D) for subjects
        
        foreach ($this->dynamicSubjects as $subject) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIdx++);
            $widths[$colLetter] = 12;
        }
        
        $widths[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIdx++)] = 12;
        $widths[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIdx++)] = 12;
        $widths[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($currentIdx++)] = 15;

        return $widths;
    }
}

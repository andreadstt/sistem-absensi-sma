<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class AttendanceSummaryExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $classRoom;
    protected $students;
    protected $classAttendanceSummary;
    protected $stats;

    public function __construct($classRoom, $students, $classAttendanceSummary, $stats)
    {
        $this->classRoom = $classRoom;
        $this->students = $students;
        $this->classAttendanceSummary = $classAttendanceSummary;
        $this->stats = $stats;
    }

    public function collection()
    {
        $data = collect();

        // Row 1-2: Title
        $data->push(['LAPORAN KEHADIRAN SISWA']);
        $data->push([]);

        // Row 3-7: School Info
        $data->push(['Sekolah', 'SMAN 10 Kota Bogor']);
        $data->push(['Kelas', $this->classRoom['name']]);
        $data->push(['Tahun Akademik', $this->classRoom['academic_year']]);
        $data->push(['Program Studi', $this->classRoom['program']]);
        $data->push(['Tanggal Laporan', now()->format('d F Y')]);
        $data->push([]);

        // Row 9-12: Class Statistics
        $data->push(['STATISTIK KELAS']);
        $data->push(['Total Siswa', $this->stats['total_students'], 'orang']);
        $data->push(['Laki-laki', $this->stats['male_count'], 'orang']);
        $data->push(['Perempuan', $this->stats['female_count'], 'orang']);
        $data->push([]);

        // Row 14-18: Attendance Summary
        $data->push(['RINGKASAN KEHADIRAN SELURUH SISWA']);
        $data->push(['Hadir', $this->classAttendanceSummary['total_hadir'], 'kali']);
        $data->push(['Sakit', $this->classAttendanceSummary['total_sakit'], 'kali']);
        $data->push(['Izin', $this->classAttendanceSummary['total_izin'], 'kali']);
        $data->push(['Alfa (Tanpa Keterangan)', $this->classAttendanceSummary['total_alfa'], 'kali']);
        $data->push([]);
        $data->push([]);

        // Row 21: Student Details Header
        $data->push(['DETAIL KEHADIRAN PER SISWA']);
        $data->push([]);

        // Row 23: Student Table Header (will be set in headings())
        $studentStartRow = count($data) + 1;

        // Add student data rows
        foreach ($this->students as $index => $student) {
            $attendanceRate = $student['attendance_rate'];
            $data->push([
                $index + 1,
                $student['nis'],
                $student['name'],
                $student['gender'] === 'M' ? 'Laki-laki' : 'Perempuan',
                $student['attendance_stats']['hadir'],
                $student['attendance_stats']['sakit'],
                $student['attendance_stats']['izin'],
                $student['attendance_stats']['alfa'],
                $student['attendance_stats']['total'],
                $attendanceRate . '%'
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Jenis Kelamin',
            'Hadir',
            'Sakit',
            'Izin',
            'Alfa',
            'Total',
            'Persentase'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];

        // Title styling (Row 1)
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E7D32');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension('1')->setRowHeight(30);

        // School Info styling (Row 3-7)
        $sheet->getStyle('A3:B7')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('C3:J7')->getFont()->setSize(11);
        $sheet->getStyle('A3:B7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F8E9');

        // Class Statistics Header (Row 9)
        $sheet->mergeCells('A9:C9');
        $sheet->getStyle('A9')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A9')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1565C0');
        $sheet->getStyle('A9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Class Stats Data (Row 10-12)
        $sheet->getStyle('A10:C12')->getFont()->setSize(11);
        $sheet->getStyle('A10:C12')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE3F2FD');
        $sheet->getStyle('A10:C12')->applyFromArray($styleArray);
        $sheet->getStyle('A10:A12')->getFont()->setBold(true);

        // Attendance Summary Header (Row 14)
        $sheet->mergeCells('A14:C14');
        $sheet->getStyle('A14')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A14')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF57C00');
        $sheet->getStyle('A14')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Attendance Summary Data (Row 15-18)
        $sheet->getStyle('A15:C18')->getFont()->setSize(11);
        $sheet->getStyle('A15:C18')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFE0B2');
        $sheet->getStyle('A15:C18')->applyFromArray($styleArray);
        $sheet->getStyle('A15:A18')->getFont()->setBold(true);

        // Student Details Header (Row 21)
        $sheet->mergeCells('A21:J21');
        $sheet->getStyle('A21')->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A21')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF6A1B9A');
        $sheet->getStyle('A21')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Student Table Header (Row 23)
        $sheet->getStyle('A23:J23')->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A23:J23')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF455A64');
        $sheet->getStyle('A23:J23')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A23:J23')->applyFromArray($styleArray);

        // Student Data Rows (Row 24+)
        $studentStartRow = 24;
        $studentEndRow = $studentStartRow + count($this->students) - 1;

        // Alternate row colors
        for ($row = $studentStartRow; $row <= $studentEndRow; $row++) {
            if (($row - $studentStartRow) % 2 === 0) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');
            }
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($styleArray);
            $sheet->getStyle("A{$row}:A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Row heights
        $sheet->getRowDimension('23')->setRowHeight(20);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 12,
            'C' => 25,
            'D' => 15,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 10,
            'J' => 12,
        ];
    }
}

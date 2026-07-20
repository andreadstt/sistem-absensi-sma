<?php

namespace App\Exports\Sheets;

use App\Models\ClassRoom;
use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceSemesterSummarySheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $classRoom;
    protected $startDate;
    protected $endDate;
    protected $semesterLabel;

    public function __construct(ClassRoom $classRoom, $startDate, $endDate, $semesterLabel)
    {
        $this->classRoom = $classRoom;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->semesterLabel = $semesterLabel;
    }

    public function collection()
    {
        // 1. Get the optimized query data
        $students = Student::query()
            ->where('students.class_room_id', $this->classRoom->id)
            ->leftJoin('attendances', function($join) {
                $join->on('students.id', '=', 'attendances.student_id')
                     ->where('attendances.class_room_id', $this->classRoom->id)
                     ->whereBetween('attendances.date', [$this->startDate, $this->endDate]);
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
            ->groupBy('students.id')
            ->get();

        // 2. Map data into rows
        $rows = [];
        $totalRecords = 0;
        $totalHadir = 0;
        $sumPercentage = 0;
        $riskCount = 0;

        foreach ($students as $student) {
            $rows[] = [
                $student->nis,
                $student->name,
                $student->gender === 'M' ? 'Laki-laki' : 'Perempuan',
                $student->total_days,
                $student->hadir,
                $student->sakit,
                $student->izin,
                $student->alfa,
                number_format((float) $student->percentage, 2) . '%',
                (float) $student->percentage // hidden raw percentage for styling later
            ];

            $totalRecords += $student->total_days;
            $totalHadir += $student->hadir;
            $sumPercentage += (float) $student->percentage;
            
            if ($student->total_days > 0 && (float) $student->percentage < 75) {
                $riskCount++;
            }
        }

        $avgPercentage = count($students) > 0 ? ($sumPercentage / count($students)) : 0;

        // 3. Add summary rows at the bottom
        $rows[] = ['', '', '', '', '', '', '', '', '', ''];
        $rows[] = ['Rata-rata Kehadiran Kelas:', number_format($avgPercentage, 2) . '%', '', '', '', '', '', '', '', ''];
        $rows[] = ['Jumlah Siswa Berisiko (<75%):', $riskCount . ' Siswa', '', '', '', '', '', '', '', ''];
        $rows[] = ['Total Rekaman Absensi:', $totalRecords, '', '', '', '', '', '', '', ''];

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            ["REKAP ABSENSI {$this->semesterLabel}"],
            ["Kelas: {$this->classRoom->name}"],
            ["Periode: {$this->startDate} s/d {$this->endDate}"],
            [''],
            [
                'NIS', 
                'Nama Siswa', 
                'Jenis Kelamin', 
                'Total Hari', 
                'Hadir (H)', 
                'Sakit (S)', 
                'Izin (I)', 
                'Alfa (A)', 
                '% Kehadiran',
                'RawPercentage'
            ]
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function styles(Worksheet $sheet)
    {
        // 1. Freeze pane
        $sheet->freezePane('A6');

        $lastRow = $sheet->getHighestRow();
        $lastCol = 'I'; // We only show A to I (J is raw percentage)

        // 2. Hide raw percentage column (J)
        $sheet->getColumnDimension('J')->setVisible(false);

        // 3. Styling headers
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);

        $sheet->getStyle('A5:I5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF4F46E5'], // Indigo
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // 4. Set borders and auto size for data rows
        $dataRows = $lastRow - 4; // subtract headers and footers roughly
        $sheet->getStyle("A6:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 5. Highlight coloring for percentage (Loop through data rows)
        for ($i = 6; $i <= $lastRow; $i++) {
            $rawPct = $sheet->getCell("J{$i}")->getValue();
            if (is_numeric($rawPct)) {
                $color = 'FFFFFFFF';
                if ($rawPct >= 90) {
                    $color = 'FFD1FAE5'; // emerald-100 (green)
                } elseif ($rawPct >= 75) {
                    $color = 'FFFEF3C7'; // amber-100 (yellow)
                } else {
                    $color = 'FFFEE2E2'; // red-100 (red)
                }

                $sheet->getStyle("I{$i}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
            }
        }
        
        // Remove borders for footer area
        $footerStart = $lastRow - 3;
        if ($footerStart >= 6) {
            $sheet->getStyle("A{$footerStart}:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_NONE);
            $sheet->getStyle("A{$footerStart}:I{$lastRow}")->getFont()->setBold(true);
        }

        return [];
    }
}

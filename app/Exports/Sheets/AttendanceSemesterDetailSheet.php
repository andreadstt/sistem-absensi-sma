<?php

namespace App\Exports\Sheets;

use App\Models\ClassRoom;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AttendanceSemesterDetailSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
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
        $attendances = Attendance::with(['student', 'subject'])
            ->where('class_room_id', $this->classRoom->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->where('status', '!=', 'HADIR')
            ->orderBy('date', 'desc')
            ->get();

        if ($attendances->isEmpty()) {
            return collect([
                ['Tidak ada catatan ketidakhadiran pada periode ini', '', '', '', '', '']
            ]);
        }

        $rows = [];
        foreach ($attendances as $att) {
            $statusName = $att->status;
            
            $rows[] = [
                $att->date->format('Y-m-d'),
                $att->student?->nis ?? '-',
                $att->student?->name ?? 'Unknown Student',
                $att->subject?->name ?? '-',
                $statusName,
                $att->notes ?? '-'
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            ["DETAIL KETIDAKHADIRAN {$this->semesterLabel}"],
            ["Kelas: {$this->classRoom->name}"],
            ["Periode: {$this->startDate} s/d {$this->endDate}"],
            [''],
            [
                'Tanggal', 
                'NIS', 
                'Nama Siswa', 
                'Mata Pelajaran', 
                'Status', 
                'Catatan'
            ]
        ];
    }

    public function title(): string
    {
        return 'Detail Ketidakhadiran';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->freezePane('A6');
        $lastRow = $sheet->getHighestRow();
        $lastCol = 'F';

        // 1. Styling headers
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        $sheet->mergeCells('A3:F3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);

        $sheet->getStyle('A5:F5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF0F766E'], // Teal (Different from Sheet 1)
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // If no records, just return early style
        $firstCell = $sheet->getCell('A6')->getValue();
        if ($firstCell === 'Tidak ada catatan ketidakhadiran pada periode ini') {
            $sheet->mergeCells('A6:F6');
            $sheet->getStyle('A6')->getFont()->setItalic(true);
            return [];
        }

        // 2. Borders and auto size
        $sheet->getStyle("A6:F{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 3. Row coloring based on Status
        for ($i = 6; $i <= $lastRow; $i++) {
            $status = $sheet->getCell("E{$i}")->getValue();
            $color = null;

            if ($status === 'ALFA') {
                $color = 'FFFEE2E2'; // red-100 (pinkish)
            } elseif ($status === 'IZIN') {
                $color = 'FFFEF3C7'; // amber-100 (yellowish)
            } elseif ($status === 'SAKIT') {
                $color = 'FFE0F2FE'; // sky-100 (blueish)
            }

            if ($color) {
                $sheet->getStyle("A{$i}:F{$i}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
            }
        }

        return [];
    }
}

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
use Carbon\Carbon;

/**
 * PENTING soal baris kosong:
 * Jangan pernah push(([])) sebagai spacer. Maatwebsite Excel/PhpSpreadsheet
 * memperlakukan array benar-benar kosong sebagai "falsy" dan baris tsb bisa
 * hilang saat ditulis ke sheet, membuat SEMUA baris di bawahnya bergeser naik
 * (dan jumlah pergeserannya berbeda-beda tergantung berapa spacer yang sudah
 * dilewati). Solusinya: spacer selalu berupa [null] (array berisi 1 elemen
 * null), bukan [] (array kosong).
 *
 * PENTING soal nomor baris:
 * Jangan hardcode nomor baris di styles(). Di sini setiap baris yang perlu
 * distyle DICATAT posisinya secara dinamis saat ditulis di collection(),
 * lalu styles() membaca posisi itu dari $this->rows. Dengan begitu, walau
 * layout di atas tabel siswa berubah, style tetap kena baris yang tepat.
 */
class AttendanceSummaryExport implements FromCollection, WithStyles, WithColumnWidths
{
    protected $classRoom;
    protected $students;
    protected $classAttendanceSummary;
    protected $stats;

    /** @var array<string,int> Peta nama-section => nomor baris aktual di sheet */
    protected $rows = [];

    const LAST_COL = 'J';

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
        $currentRow = 0;

        // Helper: push satu baris data, kembalikan nomor baris aktualnya
        $push = function (array $rowData) use ($data, &$currentRow) {
            $data->push($rowData);
            $currentRow++;
            return $currentRow;
        };
        $spacer = function () use ($push) {
            $push([null]);
        };

        // Title
        $this->rows['title'] = $push(['LAPORAN KEHADIRAN SISWA']);
        $spacer();

        // School Info — kolom A-B = label (di-merge), C-J = value (di-merge)
        $this->rows['school_info_start'] = $push(['Kelas', null, $this->classRoom['name']]);
        $push(['Wali Kelas', null, $this->classRoom['wali_kelas'] ?? '-']);
        $push(['Tahun Ajaran', null, $this->classRoom['academic_year']]);
        $this->rows['school_info_end'] = $push([
            'Tanggal Cetak', null, now()->locale('id')->translatedFormat('d F Y'),
        ]);
        $spacer();
        $spacer();

        // Student Details section title
        $this->rows['detail_header'] = $push(['DETAIL KEHADIRAN PER SISWA']);
        $spacer();

        // Student table header row (bagian dari collection, bukan WithHeadings,
        // supaya nomor barisnya pasti sinkron dengan styles())
        $this->rows['table_header'] = $push([
            'No', 'NIS', 'Nama Siswa', 'Jenis Kelamin',
            'Hadir', 'Sakit', 'Izin', 'Alfa', 'Total', 'Persentase',
        ]);

        // Student data rows
        $this->rows['student_start'] = $currentRow + 1;
        foreach ($this->students as $index => $student) {
            $attendanceRate = $student['attendance_rate'];
            $push([
                $index + 1,
                $student['nis'],
                $student['name'],
                $student['gender'] === 'M' ? 'Laki-laki' : 'Perempuan',
                $student['attendance_stats']['hadir'],
                $student['attendance_stats']['sakit'],
                $student['attendance_stats']['izin'],
                $student['attendance_stats']['alfa'],
                $student['attendance_stats']['total'],
                // Simpan sebagai angka (0-1) supaya bisa diberi number_format
                // asli Excel (persen) dan tetap bisa dihitung/di-sort.
                is_numeric($attendanceRate) ? $attendanceRate / 100 : $attendanceRate,
            ]);
        }
        $this->rows['student_end'] = $currentRow;

        // ===== Keterangan Ketidakhadiran =====
        $spacer();
        $spacer();
        $this->rows['notes_header'] = $push(['KETERANGAN KETIDAKHADIRAN']);
        $spacer();

        $statusLabels = [
            'SAKIT' => 'Sakit',
            'IZIN' => 'Izin',
            'ALFA' => 'Alfa (Tanpa Keterangan)',
        ];

        // Kumpulkan semua catatan ketidakhadiran dari seluruh siswa jadi satu daftar
        $allNotes = [];
        foreach ($this->students as $student) {
            foreach (($student['attendance_notes'] ?? []) as $note) {
                $allNotes[] = [
                    'nis' => $student['nis'],
                    'name' => $student['name'],
                    'date' => $note['date'],
                    'subject_name' => $note['subject_name'],
                    'status' => $statusLabels[$note['status']] ?? $note['status'],
                ];
            }
        }

        if (empty($allNotes)) {
            $this->rows['notes_empty'] = $push(['Tidak ada catatan ketidakhadiran (sakit/izin/alfa) untuk periode ini.']);
        } else {
            $this->rows['notes_table_header'] = $push([
                'No', 'NIS', 'Nama Siswa', 'Tanggal', 'Mata Pelajaran', 'Keterangan',
            ]);

            $this->rows['notes_start'] = $currentRow + 1;
            foreach ($allNotes as $index => $note) {
                $date = $note['date'] instanceof \DateTimeInterface
                    ? Carbon::instance($note['date'])
                    : Carbon::parse($note['date']);

                $push([
                    $index + 1,
                    $note['nis'],
                    $note['name'],
                    $date->locale('id')->translatedFormat('d F Y'),
                    $note['subject_name'],
                    $note['status'],
                ]);
            }
            $this->rows['notes_end'] = $currentRow;
        }

        return $data;
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

        // ===== Title =====
        $sheet->mergeCells('A' . $r['title'] . ':' . self::LAST_COL . $r['title']);
        $sheet->getStyle('A' . $r['title'])->getFont()->setBold(true)->setSize(16)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle('A' . $r['title'])->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E7D32');
        $sheet->getStyle('A' . $r['title'])->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension((string) $r['title'])->setRowHeight(32);

        // ===== School Info =====
        for ($row = $r['school_info_start']; $row <= $r['school_info_end']; $row++) {
            $sheet->mergeCells("A{$row}:B{$row}");
            $sheet->mergeCells("C{$row}:" . self::LAST_COL . $row);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle("C{$row}")->getFont()->setSize(11);
            $sheet->getStyle("A{$row}:" . self::LAST_COL . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F8E9');
            $sheet->getStyle("A{$row}:B{$row}")->applyFromArray($thinBorder);
            $sheet->getStyle("C{$row}:" . self::LAST_COL . $row)->applyFromArray($thinBorder);
        }

        // ===== Student Details section title =====
        $this->sectionHeader($sheet, $r['detail_header'], 'FF6A1B9A', self::LAST_COL);

        // ===== Student Table Header =====
        $headerRange = 'A' . $r['table_header'] . ':' . self::LAST_COL . $r['table_header'];
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF455A64');
        $sheet->getStyle($headerRange)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getStyle($headerRange)->applyFromArray($thinBorder);
        $sheet->getRowDimension((string) $r['table_header'])->setRowHeight(22);

        // Freeze pane tepat di bawah header tabel siswa
        $sheet->freezePane('A' . $r['student_start']);

        // ===== Student Data Rows =====
        for ($row = $r['student_start']; $row <= $r['student_end']; $row++) {
            $range = "A{$row}:" . self::LAST_COL . "{$row}";

            if (($row - $r['student_start']) % 2 === 1) {
                $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');
            }
            $sheet->getStyle($range)->applyFromArray($thinBorder);
            $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('0.0%');

            $sheet->getRowDimension((string) $row)->setRowHeight(18);
        }

        $sheet->setShowGridlines(false);

        // ===== Keterangan Ketidakhadiran =====
        $this->sectionHeader($sheet, $r['notes_header'], 'FFC62828', self::LAST_COL);

        if (isset($r['notes_empty'])) {
            $row = $r['notes_empty'];
            $sheet->mergeCells("A{$row}:" . self::LAST_COL . $row);
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(11)->getColor()->setARGB('FF757575');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } elseif (isset($r['notes_table_header'])) {
            // Header tabel keterangan (No, NIS, Nama, Tanggal, Mapel, Keterangan)
            $headerRow = $r['notes_table_header'];
            $notesHeaderRange = "A{$headerRow}:F{$headerRow}";
            $sheet->getStyle($notesHeaderRange)->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFFFF'));
            $sheet->getStyle($notesHeaderRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF455A64');
            $sheet->getStyle($notesHeaderRange)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle($notesHeaderRange)->applyFromArray($thinBorder);
            $sheet->getRowDimension((string) $headerRow)->setRowHeight(22);

            for ($row = $r['notes_start']; $row <= $r['notes_end']; $row++) {
                $range = "A{$row}:F{$row}";

                if (($row - $r['notes_start']) % 2 === 1) {
                    $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEBEE');
                }
                $sheet->getStyle($range)->applyFromArray($thinBorder);
                $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension((string) $row)->setRowHeight(18);
            }
        }

        return [];
    }

    /**
     * Style baris header section (mis. "STATISTIK KELAS") yang isinya SUDAH
     * ditulis oleh collection() — di sini kita hanya menerapkan style pada
     * baris yang benar, tanpa menimpa nilai selnya lagi.
     */
    private function sectionHeader(Worksheet $sheet, int $row, string $argbColor, string $lastCol): void
    {
        $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12)->setColor(new Color('FFFFFFFF'));
        $sheet->getStyle("A{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($argbColor);
        $sheet->getStyle("A{$row}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension((string) $row)->setRowHeight(22);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 12,
            'C' => 26,
            'D' => 15,
            'E' => 10,
            'F' => 10,
            'G' => 10,
            'H' => 10,
            'I' => 10,
            'J' => 13,
        ];
    }
}
<?php

namespace App\Exports;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AttendanceSemesterExport implements WithMultipleSheets
{
    use Exportable;

    protected $classRoom;
    protected $semester;
    protected $academicYear;
    protected $startDate;
    protected $endDate;
    protected $semesterLabel;

    public function __construct(ClassRoom $classRoom, $semester, $yearName)
    {
        $this->classRoom = $classRoom;
        $this->semester = $semester;
        
        $this->academicYear = AcademicYear::where('name', $yearName)->first();

        if ($this->academicYear) {
            $semesterData = \App\Models\Semester::where('academic_year_id', $this->academicYear->id)
                ->where('type', $this->semester)
                ->first();
                
            if ($semesterData) {
                $this->startDate = $semesterData->start_date->format('Y-m-d');
                $this->endDate = $semesterData->end_date->format('Y-m-d');
                $typeLabel = $this->semester == '1' ? 'Semester 1' : 'Semester 2';
                $this->semesterLabel = "{$typeLabel} ({$this->academicYear->name})";
            }
        }
    }

    public function sheets(): array
    {
        if (!$this->academicYear) {
            return [];
        }

        return [
            new Sheets\AttendanceSemesterSummarySheet($this->classRoom, $this->startDate, $this->endDate, $this->semesterLabel),
            new Sheets\AttendanceSemesterDetailSheet($this->classRoom, $this->startDate, $this->endDate, $this->semesterLabel),
        ];
    }
}

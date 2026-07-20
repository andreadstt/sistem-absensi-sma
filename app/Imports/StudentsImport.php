<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassRoom;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    protected $errors = [];
    protected $successCount = 0;
    protected $skippedCount = 0;

    public function model(array $row)
    {
        $classRoom = ClassRoom::where('name', trim($row['kelas'] ?? ''))->first();

        if (!$classRoom) {
            $this->skippedCount++;
            $this->errors[] = "Baris {$row['nama']}: Kelas '{$row['kelas']}' tidak ditemukan";
            return null;
        }

        // Check if student already exists
        $existingStudent = Student::where('nis', trim($row['nis']))->first();
        if ($existingStudent) {
            $this->skippedCount++;
            $this->errors[] = "Baris {$row['nama']}: NIS '{$row['nis']}' sudah terdaftar";
            return null;
        }

        $gender = strtoupper(trim($row['jenis_kelamin'] ?? 'L'));
        if ($gender === 'P' || $gender === 'PEREMPUAN') {
            $gender = 'F';
        } else {
            $gender = 'M';
        }

        $this->successCount++;

        return new Student([
            'name' => trim((string)$row['nama']),
            'nis' => trim((string)$row['nis']),
            'gender' => $gender,
            'class_room_id' => $classRoom->id,
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nis' => 'required|max:50',
            'jenis_kelamin' => 'required|string',
            'kelas' => 'required|string',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama siswa wajib diisi',
            'nis.required' => 'NIS wajib diisi',
            'jenis_kelamin.required' => 'Jenis Kelamin wajib diisi',
            'kelas.required' => 'Kelas wajib diisi',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->skippedCount++;
        $this->errors[] = $e->getMessage();
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->skippedCount++;
            $this->errors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }
}

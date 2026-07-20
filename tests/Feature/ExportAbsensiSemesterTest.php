<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Teacher;
use App\Models\ClassRoom;
use App\Models\AcademicYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AttendanceSemesterExport;
use Livewire\Livewire;
use App\Filament\Pages\RekapAbsensi;

class ExportAbsensiSemesterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'guru']);
    }

    public function test_can_export_semester_attendance_via_livewire()
    {

        $user = User::factory()->create();
        $user->assignRole('admin');

        $academicYear = AcademicYear::create([
            'name' => '2024/2025',
            'start_year' => 2024,
            'end_year' => 2025,
            'start_date' => '2024-07-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);
        
        $academicYear->semesters()->create([
            'type' => '1',
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31'
        ]);
        
        $academicYear->semesters()->create([
            'type' => '2',
            'start_date' => '2025-01-01',
            'end_date' => '2025-06-30'
        ]);

        $teacher = Teacher::create([
            'user_id' => $user->id,
            'name' => 'Test Teacher',
            'nip' => '12345',
            'phone' => '08123456789',
        ]);

        $classRoom = ClassRoom::create([
            'academic_year_id' => $academicYear->id,
            'head_teacher_id' => $teacher->id,
            'name' => '11 MIPA 1',
            'grade_level' => 11,
            'section' => '1',
            'program_id' => null,
        ]);

        $subject = Subject::create([
            'code' => 'MAT',
            'name' => 'Matematika'
        ]);

        $student1 = Student::create([
            'class_room_id' => $classRoom->id,
            'nis' => '1001',
            'name' => 'Normal Student',
            'gender' => 'M'
        ]);

        Attendance::create([
            'student_id' => $student1->id,
            'class_room_id' => $classRoom->id,
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'status' => 'ALFA',
            'date' => '2024-08-01',
            'recorded_by' => $user->id
        ]);

        $this->actingAs($user);

        $livewire = Livewire::test(RekapAbsensi::class)
            ->set('data', [
                'export_type' => 'semester',
                'class_room_id' => $classRoom->id,
                'semester' => '1',
                'year' => '2024/2025'
            ])
            ->call('exportToCSV');

        $livewire->assertFileDownloaded("absensi_semester_1_11 1.xlsx");
    }
}

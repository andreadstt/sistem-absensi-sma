<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\TeachingAssignment;
use App\Models\Attendance;
use Carbon\Carbon;

class FullSemesterDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Demo sistem untuk 1 semester penuh (Juli - Desember 2025)
     * Tahun Ajaran: 2025/2026
     */
    public function run(): void
    {
        $this->command->info('🎓 Starting Full Semester Demo Seeder (2025/2026)...');
        
        // 1. Setup Academic Year 2025/2026
        $this->seedAcademicYear();
        
        // 2. Create Demo Teacher (andreadst@gmail.com)
        $teacher = $this->seedDemoTeacher();
        
        // 3. Create Additional Teachers
        $teachers = $this->seedAdditionalTeachers();
        $teachers[] = $teacher; // Include demo teacher
        
        // 4. Create Subjects
        $subjects = $this->seedSubjects();
        
        // 5. Create Classes
        $classes = $this->seedClasses();
        
        // 6. Create Students
        $this->seedStudents($classes);
        
        // 7. Create Teaching Assignments
        $this->seedTeachingAssignments($teacher, $teachers, $classes, $subjects);
        
        // 8. Create Schedules
        $this->seedSchedules($teacher, $teachers, $classes, $subjects);
        
        // 9. Create Attendance Records (Semester 1: Juli - November 2025)
        $this->seedAttendanceRecords($teacher, $teachers, $classes);
        
        $this->command->info('✅ Full Semester Demo Seeder completed successfully!');
        $this->command->newLine();
        $this->command->info('📧 Demo Teacher Account:');
        $this->command->info('   Email: andreadst@gmail.com');
        $this->command->info('   Password: password');
        $this->command->newLine();
    }

    private function seedAcademicYear()
    {
        $this->command->info('📅 Creating Academic Year 2025/2026...');
        
        // Deactivate previous academic years
        AcademicYear::query()->update(['is_active' => false]);
        
        $academicYear = AcademicYear::updateOrCreate(
            ['name' => '2025/2026'],
            [
                'start_year' => 2025,
                'end_year' => 2026,
                'start_date' => '2025-07-01',
                'end_date' => '2026-06-30',
                'is_active' => true,
            ]
        );
        
        $this->command->info("   ✓ Academic Year: {$academicYear->name} (Active)");
    }

    private function seedDemoTeacher()
    {
        $this->command->info('👨‍🏫 Creating Demo Teacher Account...');
        
        // Create or update user
        $user = User::updateOrCreate(
            ['email' => 'andreadst@gmail.com'],
            [
                'name' => 'Andrea adiesto',
                'password' => Hash::make('password'),
            ]
        );
        
        // Assign guru role
        $user->assignRole('guru');
        
        // Create or update teacher profile
        $teacher = Teacher::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Andrea DST',
                'nip' => 'DEMO001',
                'phone' => '081234567890',
            ]
        );
        
        $this->command->info('   ✓ Teacher: Andrea DST (andreadst@gmail.com)');
        
        return $teacher;
    }

    private function seedAdditionalTeachers()
    {
        $this->command->info('👥 Creating Additional Teachers...');
        
        $teachersData = [
            ['name' => 'Drs. Ahmad Hidayat, M.Pd', 'nip' => 'T2025001', 'email' => 'ahmad@school.com'],
            ['name' => 'Sri Wahyuni, S.Pd', 'nip' => 'T2025002', 'email' => 'sri@school.com'],
            ['name' => 'Budi Santoso, M.Pd', 'nip' => 'T2025003', 'email' => 'budi@school.com'],
            ['name' => 'Rina Marlina, S.Pd', 'nip' => 'T2025004', 'email' => 'rina@school.com'],
            ['name' => 'Dr. Agus Setiawan', 'nip' => 'T2025005', 'email' => 'agus@school.com'],
        ];
        
        $teachers = [];
        foreach ($teachersData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                ]
            );
            
            $user->assignRole('guru');
            
            $teacher = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $data['name'],
                    'nip' => $data['nip'],
                    'phone' => '0812' . rand(10000000, 99999999),
                ]
            );
            
            $teachers[] = $teacher;
            $this->command->info("   ✓ {$data['name']}");
        }
        
        return $teachers;
    }

    private function seedSubjects()
    {
        $this->command->info('📚 Creating Subjects...');
        
        $subjectsData = [
            ['code' => 'MAT', 'name' => 'Matematika'],
            ['code' => 'FIS', 'name' => 'Fisika'],
            ['code' => 'KIM', 'name' => 'Kimia'],
            ['code' => 'BIO', 'name' => 'Biologi'],
            ['code' => 'ING', 'name' => 'Bahasa Inggris'],
            ['code' => 'IND', 'name' => 'Bahasa Indonesia'],
            ['code' => 'EKO', 'name' => 'Ekonomi'],
            ['code' => 'SEJ', 'name' => 'Sejarah'],
            ['code' => 'GEO', 'name' => 'Geografi'],
            ['code' => 'SOS', 'name' => 'Sosiologi'],
        ];
        
        $subjects = [];
        foreach ($subjectsData as $data) {
            $subject = Subject::updateOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name']]
            );
            $subjects[] = $subject;
        }
        
        $this->command->info("   ✓ Created " . count($subjects) . " subjects");
        
        return $subjects;
    }

    private function seedClasses()
    {
        $this->command->info('🏫 Creating Classes...');
        
        $academicYear = AcademicYear::where('name', '2025/2026')->first();
        
        // Create or get programs
        $mipaProgram = Program::firstOrCreate(
            ['code' => 'MIPA'],
            [
                'name' => 'Matematika dan Ilmu Pengetahuan Alam',
                'short_name' => 'IPA',
                'description' => 'Program studi yang fokus pada matematika, fisika, kimia, dan biologi',
                'min_grade_level' => 10,
                'is_active' => true,
            ]
        );
        
        // Create 8 classes per grade (1-8)
        $classes = [];
        
        // Grade 10 - 8 kelas MIPA (1-8)
        for ($i = 1; $i <= 8; $i++) {
            $class = ClassRoom::updateOrCreate(
                [
                    'name' => "10 MIPA $i",
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'grade_level' => 10,
                    'program_id' => $mipaProgram->id,
                    'section' => (string) $i,
                ]
            );
            $classes[] = $class;
        }
        $this->command->info('   ✓ Kelas 10: 10 MIPA 1-8 (8 kelas)');
        
        // Grade 11 - 8 kelas MIPA (1-8)
        for ($i = 1; $i <= 8; $i++) {
            $class = ClassRoom::updateOrCreate(
                [
                    'name' => "11 MIPA $i",
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'grade_level' => 11,
                    'program_id' => $mipaProgram->id,
                    'section' => (string) $i,
                ]
            );
            $classes[] = $class;
        }
        $this->command->info('   ✓ Kelas 11: 11 MIPA 1-8 (8 kelas)');
        
        // Grade 12 - 8 kelas MIPA (1-8)
        for ($i = 1; $i <= 8; $i++) {
            $class = ClassRoom::updateOrCreate(
                [
                    'name' => "12 MIPA $i",
                    'academic_year_id' => $academicYear->id,
                ],
                [
                    'grade_level' => 12,
                    'program_id' => $mipaProgram->id,
                    'section' => (string) $i,
                ]
            );
            $classes[] = $class;
        }
        $this->command->info('   ✓ Kelas 12: 12 MIPA 1-8 (8 kelas)');
        
        return $classes;
    }

    private function seedStudents($classes)
    {
        $this->command->info('👨‍🎓 Creating Students...');
        
        $firstNames = ['Budi', 'Ani', 'Citra', 'Dedi', 'Eka', 'Farah', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nina', 'Omar', 'Putri', 'Qori', 'Rudi', 'Sari', 'Tari', 'Umar', 'Vina', 'Wawan', 'Yani', 'Zahra'];
        $lastNames = ['Santoso', 'Pratama', 'Kusuma', 'Hidayat', 'Wijaya', 'Permata', 'Saputra', 'Dewi', 'Ramadan', 'Mahendra'];
        
        $totalStudents = 0;
        foreach ($classes as $class) {
            $studentsPerClass = 30; // Full capacity
            
            for ($i = 1; $i <= $studentsPerClass; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $name = "{$firstName} {$lastName}";
                
                $nis = '2025' . str_pad($class->grade_level, 2, '0', STR_PAD_LEFT) . str_pad($class->id, 2, '0', STR_PAD_LEFT) . str_pad($i, 3, '0', STR_PAD_LEFT);
                
                Student::updateOrCreate(
                    ['nis' => $nis],
                    [
                        'name' => $name,
                        'gender' => rand(0, 1) ? 'M' : 'F',
                        'class_room_id' => $class->id,
                    ]
                );
                
                $totalStudents++;
            }
        }
        
        $this->command->info("   ✓ Created {$totalStudents} students across " . count($classes) . " classes");
    }

    private function seedTeachingAssignments($demoTeacher, $teachers, $classes, $subjects)
    {
        $this->command->info('📝 Creating Teaching Assignments...');
        
        // Demo teacher mengajar MATEMATIKA SAJA di 4 kelas (11 MIPA 1, 2, 3, 4)
        $matematika = Subject::where('code', 'MAT')->first();
        $demoClasses = ClassRoom::whereIn('name', ['11 MIPA 1', '11 MIPA 2', '11 MIPA 3', '11 MIPA 4'])->get();
        
        $assignmentCount = 0;
        foreach ($demoClasses as $class) {
            TeachingAssignment::create([
                'teacher_id' => $demoTeacher->id,
                'class_room_id' => $class->id,
                'subject_id' => $matematika->id,
            ]);
            $assignmentCount++;
        }
        
        $this->command->info("   ✓ Andrea DST: Matematika SAJA di 4 kelas (11 MIPA 1, 2, 3, 4)");
        
        // Assign other teachers - each teacher gets max 2 subjects, 2-3 classes per subject
        $subjectsPool = collect($subjects)->shuffle();
        $teacherIndex = 0;
        
        foreach ($teachers as $teacher) {
            if ($teacher->id === $demoTeacher->id) continue;
            
            // Each teacher gets 1-2 subjects max (to comply with validation rule < 3)
            $numSubjects = rand(1, 2);
            $teacherSubjects = $subjectsPool->slice($teacherIndex * 2, $numSubjects);
            
            foreach ($teacherSubjects as $subject) {
                // Skip Matematika for other teachers (demo teacher only)
                if ($subject->code === 'MAT') continue;
                
                // Each subject gets 2-3 classes
                $numClasses = rand(2, 3);
                $randomClasses = collect($classes)->shuffle()->take($numClasses);
                
                foreach ($randomClasses as $class) {
                    // Check if this class-subject combination already exists
                    $exists = TeachingAssignment::where('class_room_id', $class->id)
                        ->where('subject_id', $subject->id)
                        ->exists();
                    
                    if ($exists) continue; // Skip if already assigned
                    
                    try {
                        TeachingAssignment::create([
                            'teacher_id' => $teacher->id,
                            'class_room_id' => $class->id,
                            'subject_id' => $subject->id,
                        ]);
                        $assignmentCount++;
                    } catch (\Exception $e) {
                        // Skip if validation fails (max 3 subjects reached)
                        break 2; // Break out of both loops for this teacher
                    }
                }
            }
            
            $teacherIndex++;
        }
        
        $this->command->info("   ✓ Created {$assignmentCount} total teaching assignments");
    }

    private function seedSchedules($demoTeacher, $teachers, $classes, $subjects)
    {
        $this->command->info('📅 Creating Schedules...');
        
        // Demo teacher schedule - Matematika untuk 4 kelas
        $matematika = Subject::where('code', 'MAT')->first();
        $demoClasses = ClassRoom::whereIn('name', ['11 MIPA 1', '11 MIPA 2', '11 MIPA 3', '11 MIPA 4'])->get();
        
        $scheduleData = [
            // 11 MIPA 1: Senin & Rabu pagi
            ['class' => '11 MIPA 1', 'weekday' => 1, 'time_slot' => '07:00-08:30'], // Senin
            ['class' => '11 MIPA 1', 'weekday' => 3, 'time_slot' => '09:00-10:30'], // Rabu
            
            // 11 MIPA 2: Selasa & Kamis pagi
            ['class' => '11 MIPA 2', 'weekday' => 2, 'time_slot' => '08:30-10:00'], // Selasa
            ['class' => '11 MIPA 2', 'weekday' => 4, 'time_slot' => '10:30-12:00'], // Kamis
            
            // 11 MIPA 3: Senin & Rabu siang
            ['class' => '11 MIPA 3', 'weekday' => 1, 'time_slot' => '13:00-14:30'], // Senin
            ['class' => '11 MIPA 3', 'weekday' => 3, 'time_slot' => '13:00-14:30'], // Rabu
            
            // 11 MIPA 4: Selasa & Kamis siang
            ['class' => '11 MIPA 4', 'weekday' => 2, 'time_slot' => '13:00-14:30'], // Selasa
            ['class' => '11 MIPA 4', 'weekday' => 4, 'time_slot' => '13:00-14:30'], // Kamis
        ];
        
        $scheduleCount = 0;
        foreach ($scheduleData as $schedule) {
            $class = $demoClasses->firstWhere('name', $schedule['class']);
            if ($class) {
                Schedule::updateOrCreate([
                    'teacher_id' => $demoTeacher->id,
                    'class_room_id' => $class->id,
                    'subject_id' => $matematika->id,
                    'weekday' => $schedule['weekday'],
                ], [
                    'time_slot' => $schedule['time_slot'],
                ]);
                $scheduleCount++;
            }
        }
        
        $this->command->info("   ✓ Demo teacher schedules created (8 sessions/week for 4 classes)");
        
        // Create schedules for other teachers
        foreach ($teachers as $teacher) {
            if ($teacher->id === $demoTeacher->id) continue;
            
            $assignments = TeachingAssignment::where('teacher_id', $teacher->id)->get();
            
            foreach ($assignments as $assignment) {
                // 2 sessions per week per class
                for ($i = 0; $i < 2; $i++) {
                    $weekday = rand(1, 5); // Monday to Friday
                    $hour = rand(7, 14);
                    $timeSlot = sprintf('%02d:00-%02d:30', $hour, $hour + 1);
                    
                    Schedule::updateOrCreate([
                        'teacher_id' => $teacher->id,
                        'class_room_id' => $assignment->class_room_id,
                        'subject_id' => $assignment->subject_id,
                        'weekday' => $weekday,
                    ], [
                        'time_slot' => $timeSlot,
                    ]);
                    
                    $scheduleCount++;
                }
            }
        }
        
        $this->command->info("   ✓ Created {$scheduleCount} total schedules");
    }

    private function seedAttendanceRecords($demoTeacher, $teachers, $classes)
    {
        $this->command->info('📊 Creating Attendance Records (Juli - November 2025)...');
        
        // Semester 1: Juli - November 2025 (5 months of data)
        $startDate = Carbon::create(2025, 7, 1);
        $endDate = Carbon::create(2025, 11, 15); // Current date
        
        $totalRecords = 0;
        $dates = [];
        
        // Generate school days (Monday to Friday, skip holidays)
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                $dates[] = $current->copy();
            }
            $current->addDay();
        }
        
        $this->command->info("   ℹ Generating attendance for " . count($dates) . " school days...");
        
        // Get demo teacher's classes
        $demoTeacherClasses = TeachingAssignment::where('teacher_id', $demoTeacher->id)
            ->with('classRoom.students')
            ->get();
        
        // Create attendance for demo teacher's classes
        foreach ($demoTeacherClasses as $assignment) {
            $students = $assignment->classRoom->students;
            
            foreach ($dates as $date) {
                // Only create attendance on scheduled days
                $schedules = Schedule::where('teacher_id', $demoTeacher->id)
                    ->where('class_room_id', $assignment->class_room_id)
                    ->where('subject_id', $assignment->subject_id)
                    ->where('weekday', $date->dayOfWeekIso)
                    ->get();
                
                if ($schedules->isEmpty()) continue;
                
                foreach ($students as $student) {
                    // Realistic attendance distribution
                    $rand = rand(1, 100);
                    if ($rand <= 85) {
                        $status = 'HADIR'; // 85% hadir
                    } elseif ($rand <= 92) {
                        $status = 'SAKIT'; // 7% sakit
                    } elseif ($rand <= 97) {
                        $status = 'IZIN'; // 5% izin
                    } else {
                        $status = 'ALFA'; // 3% alfa
                    }
                    
                    Attendance::updateOrCreate([
                        'student_id' => $student->id,
                        'class_room_id' => $assignment->class_room_id,
                        'subject_id' => $assignment->subject_id,
                        'teacher_id' => $demoTeacher->id,
                        'date' => $date->format('Y-m-d'),
                    ], [
                        'status' => $status,
                        'recorded_by' => $demoTeacher->user_id,
                    ]);
                    
                    $totalRecords++;
                }
            }
        }
        
        $this->command->info("   ✓ Created {$totalRecords} attendance records for demo teacher");
        
        // Create attendance for other teachers
        foreach ($teachers as $teacher) {
            if ($teacher->id === $demoTeacher->id) continue;
            
            $assignments = TeachingAssignment::where('teacher_id', $teacher->id)
                ->with('classRoom.students')
                ->get();
            
            foreach ($assignments as $assignment) {
                $students = $assignment->classRoom->students;
                
                // Sample some dates (not all) to reduce processing time
                $sampleDates = collect($dates)->random(min(30, count($dates)));
                
                foreach ($sampleDates as $date) {
                    $schedules = Schedule::where('teacher_id', $teacher->id)
                        ->where('class_room_id', $assignment->class_room_id)
                        ->where('subject_id', $assignment->subject_id)
                        ->where('weekday', $date->dayOfWeekIso)
                        ->get();
                    
                    if ($schedules->isEmpty()) continue;
                    
                    foreach ($students as $student) {
                        // Check if attendance already exists (from demo teacher or other teacher)
                        $exists = Attendance::where('student_id', $student->id)
                            ->where('class_room_id', $assignment->class_room_id)
                            ->where('date', $date->format('Y-m-d'))
                            ->exists();
                        
                        if ($exists) continue; // Skip if already recorded
                        
                        $rand = rand(1, 100);
                        if ($rand <= 85) {
                            $status = 'HADIR';
                        } elseif ($rand <= 92) {
                            $status = 'SAKIT';
                        } elseif ($rand <= 97) {
                            $status = 'IZIN';
                        } else {
                            $status = 'ALFA';
                        }
                        
                        Attendance::create([
                            'student_id' => $student->id,
                            'class_room_id' => $assignment->class_room_id,
                            'subject_id' => $assignment->subject_id,
                            'teacher_id' => $teacher->id,
                            'date' => $date->format('Y-m-d'),
                            'status' => $status,
                            'recorded_by' => $teacher->user_id,
                        ]);
                        
                        $totalRecords++;
                    }
                }
            }
        }
        
        $this->command->info("   ✓ Total attendance records: {$totalRecords}");
        $this->command->info("   ✓ Period: Juli 1, 2025 - November 15, 2025");
    }
}

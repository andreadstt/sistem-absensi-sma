<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
use App\Models\TeacherAttendance;
use App\Models\TeacherRegistration;
use App\Models\AcademicEvent;
use Carbon\Carbon;

class FullSemesterDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🎓 Starting Full Semester Demo Seeder...');
        
        $this->seedAcademicYear();
        $this->seedAdminAccount();
        $demoTeacher = $this->seedDemoTeacher();
        
        $teachers = $this->seedAdditionalTeachers();
        $allTeachers = array_merge([$demoTeacher], $teachers);
        
        $subjects = $this->seedSubjects();
        
        $classes = $this->seedClassesAndHeadTeachers($allTeachers, $demoTeacher);
        
        $this->seedStudents($classes);
        
        $this->seedTeachingAssignmentsAndSchedules($demoTeacher, $allTeachers, $classes, $subjects);
        
        $this->seedAttendanceRecords($allTeachers, $classes);
        
        $this->seedTeacherAttendances($allTeachers);
        
        $this->seedTeacherRegistrations();
        $this->seedAcademicEvents();
        
        $this->command->info('✅ Full Semester Demo Seeder completed successfully!');
        $this->command->newLine();
        $this->command->info('📧 Admin Account:');
        $this->command->info('   Email: a9296691@gmail.com');
        $this->command->info('   Password: password');
        $this->command->newLine();
        $this->command->info('📧 Demo Teacher Account:');
        $this->command->info('   Email: adiestoa@gmail.com');
        $this->command->info('   Password: password');
        $this->command->newLine();
    }

    private function seedAcademicYear()
    {
        $this->command->info('📅 Creating Academic Year (Relative to now)...');
        
        AcademicYear::query()->update(['is_active' => false]);
        
        $now = now();
        $startYear = $now->month >= 7 ? $now->year : $now->year - 1;
        $endYear = $startYear + 1;
        
        $name = "{$startYear}/{$endYear}";
        
        $academicYear = AcademicYear::updateOrCreate(
            ['name' => $name],
            [
                'start_year' => $startYear,
                'end_year' => $endYear,
                'start_date' => "{$startYear}-07-01",
                'end_date' => "{$endYear}-06-30",
                'is_active' => true,
            ]
        );
        
        $academicYear->semesters()->firstOrCreate(
            ['type' => '1'],
            ['start_date' => "{$startYear}-07-01", 'end_date' => "{$startYear}-12-31"]
        );
        $academicYear->semesters()->firstOrCreate(
            ['type' => '2'],
            ['start_date' => "{$endYear}-01-01", 'end_date' => "{$endYear}-06-30"]
        );

        $this->command->info("   ✓ Academic Year: {$academicYear->name} (Active)");
    }

    private function seedAdminAccount()
    {
        $this->command->info('👨‍💼 Updating Admin Account...');
        
        $admin = User::role('admin')->first();
        if ($admin) {
            $admin->update([
                'name' => 'Administrator',
                'email' => 'a9296691@gmail.com',
                'password' => Hash::make('password'),
            ]);
        } else {
            $admin = User::updateOrCreate(
                ['email' => 'a9296691@gmail.com'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('password'),
                ]
            );
            $admin->assignRole('admin');
        }
        
        $this->command->info('   ✓ Admin: Administrator (a9296691@gmail.com)');
        return $admin;
    }

    private function seedDemoTeacher()
    {
        $this->command->info('👨‍🏫 Creating Demo Teacher Account...');
        
        $user = User::updateOrCreate(
            ['email' => 'adiestoa@gmail.com'],
            [
                'name' => 'Andre Adiesto Pramudya',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('guru');
        
        $teacher = Teacher::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => 'Andre Adiesto Pramudya',
                'nip' => 'DEMO001',
                'phone' => '081234567890',
            ]
        );
        
        $this->command->info('   ✓ Teacher: Andre Adiesto Pramudya (adiestoa@gmail.com)');
        return $teacher;
    }

    private function seedAdditionalTeachers()
    {
        $this->command->info('👥 Creating Additional Teachers...');
        
        $names = [
            'Drs. Ahmad Hidayat, M.Pd', 'Sri Wahyuni, S.Pd', 'Budi Santoso, M.Pd', 'Rina Marlina, S.Pd', 'Dr. Agus Setiawan',
            'Siti Aminah, S.Pd', 'Joko Susanto, S.Pd', 'Iwan Fals, S.Pd', 'Dewi Lestari, M.Pd', 'Eko Purnomo, S.Pd',
            'Fatimah Zahra, S.Pd', 'Gunawan Wibisono, M.Pd', 'Hadi Sucipto, S.Pd', 'Indah Permatasari, S.Pd', 'Jamaludin, S.Pd',
            'Kusuma Wardhani, M.Pd', 'Lukman Hakim, S.Pd', 'Mulyani, S.Pd', 'Nugroho, M.Pd', 'Oka Antara, S.Pd',
            'Puspita Sari, S.Pd', 'Qori Akbar, S.Pd', 'Ratna Galih, M.Pd', 'Sigit Pramono, S.Pd', 'Tuti Alawiyah, S.Pd',
            'Usman Ali, S.Pd', 'Vina Panduwinata, M.Pd', 'Wawan Setiawan, S.Pd', 'Yani Haryanto, S.Pd'
        ];
        
        $teachers = [];
        foreach ($names as $index => $name) {
            $email = strtolower(explode(' ', trim(preg_replace('/[^a-zA-Z\s]/', '', $name)))[0]) . rand(10,99) . '@school.com';
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole('guru');
            
            $teachers[] = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $name,
                    'nip' => 'T2025' . str_pad($index + 2, 3, '0', STR_PAD_LEFT),
                    'phone' => '0812' . rand(10000000, 99999999),
                ]
            );
        }
        $this->command->info('   ✓ Created 29 additional teachers');
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
            ['code' => 'PJK', 'name' => 'Penjaskes'],
            ['code' => 'PKN', 'name' => 'Pendidikan Kewarganegaraan'],
            ['code' => 'SNB', 'name' => 'Seni Budaya'],
        ];
        
        $subjects = [];
        foreach ($subjectsData as $data) {
            $subjects[] = Subject::updateOrCreate(
                ['code' => $data['code']],
                ['name' => $data['name']]
            );
        }
        return $subjects;
    }

    private function seedClassesAndHeadTeachers($allTeachers, $demoTeacher)
    {
        $this->command->info('🏫 Creating 30 Classes and Assigning Head Teachers...');
        
        $academicYear = AcademicYear::where('is_active', true)->first();
        
        $mipaProgram = Program::firstOrCreate(
            ['short_name' => 'MIPA'],
            [
                'name' => 'Matematika dan Ilmu Pengetahuan Alam',
                'description' => 'Program MIPA',
                'min_grade_level' => 10,
                'is_active' => true,
            ]
        );
        
        $ipsProgram = Program::firstOrCreate(
            ['short_name' => 'IPS'],
            [
                'name' => 'Ilmu Pengetahuan Sosial',
                'description' => 'Program IPS',
                'min_grade_level' => 10,
                'is_active' => true,
            ]
        );
        
        $shuffledTeachers = collect($allTeachers)->shuffle()->values();
        
        // Ensure Demo Teacher is head teacher of 11 MIPA 1
        $demoTeacherAssigned = false;
        
        $classes = [];
        $teacherIndex = 0;
        
        foreach ([10, 11, 12] as $grade) {
            foreach (['MIPA' => $mipaProgram, 'IPS' => $ipsProgram] as $progName => $progModel) {
                for ($i = 1; $i <= 5; $i++) {
                    $teacherForClass = $shuffledTeachers[$teacherIndex];
                    
                    // Force demo teacher to be head of 11 MIPA 1 if not yet assigned
                    if ($grade == 11 && $progName == 'MIPA' && $i == 1) {
                        $teacherForClass = $demoTeacher;
                        $demoTeacherAssigned = true;
                        // remove demo teacher from their original shuffled position and replace with the one we just bumped
                        $originalDemoIndex = $shuffledTeachers->search(function($t) use ($demoTeacher) {
                            return $t->id == $demoTeacher->id;
                        });
                        if ($originalDemoIndex !== false && $originalDemoIndex != $teacherIndex) {
                            $shuffledTeachers[$originalDemoIndex] = $shuffledTeachers[$teacherIndex];
                        }
                    } else if ($teacherForClass->id == $demoTeacher->id && !$demoTeacherAssigned) {
                        // Skip if we hit demo teacher naturally before 11 MIPA 1
                        // Just swap with the next one
                        $nextTeacher = $shuffledTeachers[$teacherIndex + 1];
                        $shuffledTeachers[$teacherIndex + 1] = $teacherForClass;
                        $teacherForClass = $nextTeacher;
                    }
                    
                    $classes[] = ClassRoom::updateOrCreate(
                        [
                            'academic_year_id' => $academicYear->id,
                            'grade_level' => $grade,
                            'program_id' => $progModel->id,
                            'section' => (string) $i,
                        ],
                        [
                            'head_teacher_id' => $teacherForClass->id,
                        ]
                    );
                    $teacherIndex++;
                }
            }
        }
        
        $this->command->info("   ✓ Created 30 classes with 30 unique head teachers");
        return collect($classes);
    }

    private function seedStudents($classes)
    {
        $this->command->info('👨‍🎓 Creating Students (30-36 per class)...');
        
        $firstNames = ['Budi', 'Ani', 'Citra', 'Dedi', 'Eka', 'Farah', 'Gita', 'Hadi', 'Indah', 'Joko', 'Kartika', 'Lina', 'Made', 'Nina', 'Omar', 'Putri', 'Qori', 'Rudi', 'Sari', 'Tari', 'Umar', 'Vina', 'Wawan', 'Yani', 'Zahra', 'Arief', 'Rizal', 'Nanda', 'Fauzan', 'Maya'];
        $lastNames = ['Santoso', 'Pratama', 'Kusuma', 'Hidayat', 'Wijaya', 'Permata', 'Saputra', 'Dewi', 'Ramadan', 'Mahendra', 'Lestari', 'Putra', 'Setiawan', 'Nugroho', 'Wibowo'];
        
        $totalStudents = 0;
        foreach ($classes as $class) {
            $studentsCount = rand(30, 36);
            for ($i = 1; $i <= $studentsCount; $i++) {
                $name = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
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

        // ADDITIVE SCENARIO: Append 2 special students to 11 MIPA 1 (Demo Class) at the VERY END.
        // This ensures the autoincrement ID generation order for all original students remains exactly the same.
        $demoClass = collect($classes)->first(function($c) {
            return $c->grade_level == 11 && str_contains($c->name, 'MIPA') && $c->section == '1';
        });

        if ($demoClass) {
            Student::updateOrCreate(
                ['nis' => 'TRANSFER001'],
                [
                    'name' => 'Siswa Pindahan (Demo)',
                    'gender' => 'M',
                    'class_room_id' => $demoClass->id,
                ]
            );
            
            Student::updateOrCreate(
                ['nis' => 'NODATA002'],
                [
                    'name' => 'Siswa Tanpa Data (Demo)',
                    'gender' => 'F',
                    'class_room_id' => $demoClass->id,
                ]
            );
            $totalStudents += 2;
        }

        $this->command->info("   ✓ Created {$totalStudents} students");
    }

    private function seedTeachingAssignmentsAndSchedules($demoTeacher, $allTeachers, $classes, $subjects)
    {
        $this->command->info('📝 Creating Teaching Assignments & Schedules...');
        
        $matematika = collect($subjects)->firstWhere('code', 'MAT');
        $assignmentCount = 0;
        $scheduleCount = 0;
        
        $teacherSchedules = []; // teacher_id => ['weekday_timeslot']
        
        // 1. Assign Demo Teacher (Math for 11 MIPA 1 to 4)
        $demoClasses = $classes->filter(function($c) {
            return $c->grade_level == 11 && str_contains($c->name, 'MIPA') && in_array($c->section, ['1', '2', '3', '4']);
        });
        
        $demoScheduledForToday = false;
        foreach ($demoClasses as $class) {
            TeachingAssignment::firstOrCreate([
                'teacher_id' => $demoTeacher->id,
                'class_room_id' => $class->id,
                'subject_id' => $matematika->id,
            ]);
            $assignmentCount++;
            
            // 2 schedules per class
            for ($i = 0; $i < 2; $i++) {
                if (!$demoScheduledForToday) {
                    $scheduleCount += $this->createUniqueSchedule($demoTeacher->id, $class->id, $matematika->id, $teacherSchedules, now()->dayOfWeekIso, 15);
                    $demoScheduledForToday = true;
                } else {
                    $scheduleCount += $this->createUniqueSchedule($demoTeacher->id, $class->id, $matematika->id, $teacherSchedules);
                }
            }
        }
        
        // 2. Assign other teachers
        $subjectsPool = collect($subjects)->where('code', '!=', 'MAT')->values();
        foreach ($allTeachers as $teacher) {
            if ($teacher->id === $demoTeacher->id) continue;
            
            $numSubjects = rand(1, 2);
            $mySubjects = $subjectsPool->random($numSubjects);
            
            foreach ($mySubjects as $subject) {
                $numClasses = rand(2, 4);
                $randomClasses = $classes->random($numClasses);
                
                foreach ($randomClasses as $class) {
                    $exists = TeachingAssignment::where('class_room_id', $class->id)
                        ->where('subject_id', $subject->id)
                        ->exists();
                        
                    if ($exists) continue; // Subject already taught in this class
                    
                    try {
                        TeachingAssignment::create([
                            'teacher_id' => $teacher->id,
                            'class_room_id' => $class->id,
                            'subject_id' => $subject->id,
                        ]);
                        $assignmentCount++;
                        
                        for ($i = 0; $i < 2; $i++) {
                            $scheduleCount += $this->createUniqueSchedule($teacher->id, $class->id, $subject->id, $teacherSchedules);
                        }
                    } catch (\Exception $e) {}
                }
            }
        }
        
        $this->command->info("   ✓ Created {$assignmentCount} assignments, {$scheduleCount} schedules without conflicts");
    }
    
    private function createUniqueSchedule($teacherId, $classId, $subjectId, &$teacherSchedules, $forceWeekday = null, $forceHour = null)
    {
        if (!isset($teacherSchedules[$teacherId])) {
            $teacherSchedules[$teacherId] = [];
        }
        
        $attempts = 0;
        do {
            $weekday = $forceWeekday ?? rand(1, 5);
            $hour = $forceHour ?? rand(7, 14);
            $timeSlot = sprintf('%02d:00-%02d:30', $hour, $hour + 1);
            $key = "{$weekday}_{$timeSlot}";
            $attempts++;
            if ($attempts > 100) return 0; // prevent infinite loop
            
            if ($forceWeekday && $forceHour) break; // bypass loop check if forced (assumes it's the first schedule)
        } while (in_array($key, $teacherSchedules[$teacherId]));
        
        $teacherSchedules[$teacherId][] = $key;
        
        Schedule::updateOrCreate([
            'teacher_id' => $teacherId,
            'class_room_id' => $classId,
            'subject_id' => $subjectId,
            'weekday' => $weekday,
        ], [
            'time_slot' => $timeSlot,
        ]);
        
        return 1;
    }

    private function seedAttendanceRecords($allTeachers, $classes)
    {
        $this->command->info('📊 Creating Student Attendance Records (Last 5 Months)...');
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $semesters = $activeYear->semesters()->get();
        $activeSemester = $semesters->first(function ($s) {
            $now = now();
            return $s->start_date <= $now && $s->end_date >= $now;
        }) ?? $semesters->firstWhere('type', '1');
        
        $startDate = $activeSemester->start_date->copy();
        $endDate = $activeSemester->end_date->copy();
        $dates = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                $dates[] = $current->copy();
            }
            $current->addDay();
        }
        
        $totalRecords = 0;
        $demoRecords = 0;
        
        $demoTeacherId = User::where('email', 'adiestoa@gmail.com')->first()->teacher->id;
        $insertData = [];
        
        foreach ($allTeachers as $teacher) {
            $assignments = TeachingAssignment::where('teacher_id', $teacher->id)
                ->with('classRoom.students')
                ->get();
                
            foreach ($assignments as $assignment) {
                $students = $assignment->classRoom->students;
                if ($students->isEmpty()) continue;
                
                $schedules = Schedule::where('teacher_id', $teacher->id)
                    ->where('class_room_id', $assignment->class_room_id)
                    ->get()
                    ->groupBy('weekday');
                    
                $todayStr = now()->toDateString();
                
                // Full generation for all teachers (100% dates)
                foreach ($dates as $date) {
                    $dateStr = $date->toDateString();
                    $dayOfWeek = $date->dayOfWeekIso;
                    if (!$schedules->has($dayOfWeek)) continue;
                    
                    if ($dateStr === $todayStr) {
                        if ($teacher->id === $demoTeacherId) {
                            continue; // Skip student attendance for demo teacher today
                        }
                    } else {
                        // Deterministic chance for teacher to skip filling attendance for the whole class on a past day
                        $teacherHash = md5($teacher->id . '_' . $dateStr);
                        if (hexdec(substr($teacherHash, 0, 4)) % 100 + 1 > 90) continue; 
                    }
                    
                    foreach ($students as $student) {
                        // Intercept special students BEFORE hashing to ensure they do not shift loop timing/logic
                        if ($student->nis === 'NODATA002') {
                            continue; // No data ever
                        }
                        
                        if ($student->nis === 'TRANSFER001') {
                            // Only generate attendance for the last 5 days of the semester dates array
                            // Actually, just check if date is within 5 days of now()
                            if ($date->diffInDays(now()) > 5) {
                                continue;
                            }
                        }

                        // Deterministic hash per student per day!
                        $hash = md5($student->id . '_' . $dateStr);
                        $rand = hexdec(substr($hash, 0, 4)) % 100 + 1;
                        
                        if ($rand <= 90) $status = 'HADIR';
                        elseif ($rand <= 94) $status = 'IZIN';
                        elseif ($rand <= 97) $status = 'SAKIT';
                        else $status = 'ALFA';
                        
                        $insertData[] = [
                            'student_id' => $student->id,
                            'class_room_id' => $assignment->class_room_id,
                            'subject_id' => $assignment->subject_id,
                            'teacher_id' => $teacher->id,
                            'date' => $date->format('Y-m-d'),
                            'status' => $status,
                            'recorded_by' => $teacher->user_id,
                            'created_at' => $date->format('Y-m-d H:i:s'),
                            'updated_at' => $date->format('Y-m-d H:i:s'),
                        ];
                        
                        if ($teacher->id === $demoTeacherId) $demoRecords++;
                        $totalRecords++;
                        
                        // FLUSH to prevent memory leak
                        if (count($insertData) >= 1000) {
                            Attendance::insertOrIgnore($insertData);
                            $insertData = [];
                        }
                    }
                }
            }
        }
        
        // Final flush
        if (!empty($insertData)) {
            Attendance::insertOrIgnore($insertData);
            $insertData = [];
        }
        
        $this->command->info("   ✓ Created ~{$totalRecords} attendance records ({$demoRecords} for demo teacher)");
    }
    
    private function seedTeacherAttendances($allTeachers)
    {
        $this->command->info('👨‍🏫 Creating Teacher Attendances...');
        
        $activeYear = AcademicYear::where('is_active', true)->first();
        $semesters = $activeYear->semesters()->get();
        $activeSemester = $semesters->first(function ($s) {
            $now = now();
            return $s->start_date <= $now && $s->end_date >= $now;
        }) ?? $semesters->firstWhere('type', '1');
        
        $startDate = $activeSemester->start_date->copy();
        $endDate = $activeSemester->end_date->copy();
        $dates = [];
        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            if ($current->isWeekday()) {
                $dates[] = $current->copy();
            }
            $current->addDay();
        }
        
        $demoTeacherId = User::where('email', 'adiestoa@gmail.com')->first()->teacher->id;
        $todayStr = now()->toDateString();
        
        $insertData = [];
        foreach ($allTeachers as $teacher) {
            $schedules = Schedule::where('teacher_id', $teacher->id)->get();
            
            foreach ($dates as $date) {
                $daySchedules = $schedules->where('weekday', $date->dayOfWeekIso);
                $dateStr = $date->toDateString();
                
                foreach ($daySchedules as $schedule) {
                    $shouldRecord = false;
                    
                    if ($dateStr === $todayStr) {
                        if ($teacher->id === $demoTeacherId) {
                            $shouldRecord = false; // Demo teacher is empty today for live demo
                        } else {
                            $shouldRecord = true; // All other teachers are present today
                        }
                    } else {
                        // 100% chance they recorded their attendance on past days to prevent empty blocks in the calendar
                        $shouldRecord = true;
                    }
                    
                    if ($shouldRecord) {
                        // Status is also deterministic so it's consistent if they teach multiple subjects a day
                        $teacherDayHash = md5($teacher->id . '_' . $dateStr . '_t');
                        $status = (hexdec(substr($teacherDayHash, 0, 4)) % 100 + 1) <= 95 ? 'HADIR' : 'TIDAK_HADIR';
                        
                        $insertData[] = [
                            'teacher_id' => $teacher->id,
                            'schedule_id' => $schedule->id,
                            'date' => $dateStr,
                            'status' => $status,
                            'notes' => null,
                            'created_at' => $date->format('Y-m-d H:i:s'),
                            'updated_at' => $date->format('Y-m-d H:i:s'),
                        ];
                        
                        // FLUSH to prevent memory leak
                        if (count($insertData) >= 1000) {
                            TeacherAttendance::insertOrIgnore($insertData);
                            $insertData = [];
                        }
                    }
                }
            }
        }
        
        // Final flush
        if (!empty($insertData)) {
            TeacherAttendance::insertOrIgnore($insertData);
            $insertData = [];
        }
        
        $this->command->info("   ✓ Created teacher attendance records");
    }

    private function seedTeacherRegistrations()
    {
        $this->command->info('📝 Creating Pending Teacher Registrations...');
        
        TeacherRegistration::create([
            'email' => 'calonguru1@gmail.com',
            'name' => 'Ahmad Calon',
            'password' => Hash::make('password'),
            'nip' => 'C2025001',
            'phone' => '0811111111',
            'status' => 'pending',
            'created_at' => now()->subDays(2),
        ]);
        
        TeacherRegistration::create([
            'email' => 'calonguru2@gmail.com',
            'name' => 'Siti Calon',
            'password' => Hash::make('password'),
            'nip' => 'C2025002',
            'phone' => '0822222222',
            'status' => 'pending',
            'created_at' => now()->subDays(1),
        ]);
        
        $this->command->info("   ✓ Created 2 pending registrations");
    }

    private function seedAcademicEvents()
    {
        $this->command->info('🗓 Creating Academic Events...');
        
        $admin = User::role('admin')->first();
        
        AcademicEvent::create([
            'title' => 'Libur Semester Ganjil',
            'type' => 'holiday',
            'start_date' => now()->subMonths(1)->format('Y-m-d'),
            'end_date' => now()->subMonths(1)->addDays(14)->format('Y-m-d'),
            'description' => 'Libur akhir semester ganjil',
            'created_by' => $admin->id,
        ]);
        
        AcademicEvent::create([
            'title' => 'Rapat Wali Kelas',
            'type' => 'meeting',
            'start_date' => now()->subDays(5)->format('Y-m-d'),
            'end_date' => now()->subDays(5)->format('Y-m-d'),
            'description' => 'Evaluasi bulanan',
            'created_by' => $admin->id,
        ]);
        
        AcademicEvent::create([
            'title' => 'Ujian Akhir Sekolah',
            'type' => 'exam',
            'start_date' => now()->addDays(10)->format('Y-m-d'),
            'end_date' => now()->addDays(24)->format('Y-m-d'),
            'description' => 'UAS Semester Genap',
            'created_by' => $admin->id,
        ]);
        
        $this->command->info("   ✓ Created academic events");
    }
}
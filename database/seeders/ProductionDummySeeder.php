<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\AcademicYear;
use App\Models\Program;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ProductionDummySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Starting Production Dummy Data Seeding...');

        // Get active academic year and programs
        $academicYear = AcademicYear::where('is_active', true)->first();
        $programMIPA = Program::where('code', 'MIPA')->first();
        $programIPS = Program::where('code', 'IPS')->first();
        $programBahasa = Program::where('code', 'Bahasa')->first();

        if (!$academicYear || !$programMIPA || !$programIPS) {
            $this->command->error('❌ Academic Year or Programs not found. Run AcademicSystemSeeder first!');
            return;
        }

        // 1. Create Subjects
        $this->command->info('📚 Creating Subjects...');
        $subjects = [
            ['code' => 'MTK', 'name' => 'Matematika'],
            ['code' => 'FIS', 'name' => 'Fisika'],
            ['code' => 'KIM', 'name' => 'Kimia'],
            ['code' => 'BIO', 'name' => 'Biologi'],
            ['code' => 'IND', 'name' => 'Bahasa Indonesia'],
            ['code' => 'ING', 'name' => 'Bahasa Inggris'],
            ['code' => 'SEJ', 'name' => 'Sejarah'],
            ['code' => 'GEO', 'name' => 'Geografi'],
            ['code' => 'EKO', 'name' => 'Ekonomi'],
            ['code' => 'SOS', 'name' => 'Sosiologi'],
            ['code' => 'PJOK', 'name' => 'Pendidikan Jasmani'],
            ['code' => 'SEN', 'name' => 'Seni Budaya'],
        ];

        $subjectModels = [];
        foreach ($subjects as $subject) {
            $subjectModels[$subject['code']] = Subject::firstOrCreate(
                ['code' => $subject['code']],
                ['name' => $subject['name']]
            );
        }
        $this->command->info('✅ Created ' . count($subjectModels) . ' subjects');

        // 2. Create Classes
        $this->command->info('🏫 Creating Classes...');
        $classes = [];

        // Kelas 10 MIPA A & B
        $classes[] = ClassRoom::firstOrCreate([
            'name' => '10 MIPA A',
            'grade_level' => 10,
            'academic_year_id' => $academicYear->id,
            'program_id' => $programMIPA->id,
            'section' => 'A',
        ]);

        $classes[] = ClassRoom::firstOrCreate([
            'name' => '10 MIPA B',
            'grade_level' => 10,
            'academic_year_id' => $academicYear->id,
            'program_id' => $programMIPA->id,
            'section' => 'B',
        ]);

        // Kelas 10 IPS A
        $classes[] = ClassRoom::firstOrCreate([
            'name' => '10 IPS A',
            'grade_level' => 10,
            'academic_year_id' => $academicYear->id,
            'program_id' => $programIPS->id,
            'section' => 'A',
        ]);

        // Kelas 11 MIPA A
        $classes[] = ClassRoom::firstOrCreate([
            'name' => '11 MIPA A',
            'grade_level' => 11,
            'academic_year_id' => $academicYear->id,
            'program_id' => $programMIPA->id,
            'section' => 'A',
        ]);

        // Kelas 12 IPS A
        $classes[] = ClassRoom::firstOrCreate([
            'name' => '12 IPS A',
            'grade_level' => 12,
            'academic_year_id' => $academicYear->id,
            'program_id' => $programIPS->id,
            'section' => 'A',
        ]);

        $this->command->info('✅ Created ' . count($classes) . ' classes');

        // 3. Create Teachers
        $this->command->info('👨‍🏫 Creating Teachers...');
        $teachers = [
            ['name' => 'Budi Santoso, S.Pd', 'nip' => '198501012010011001', 'email' => 'budi@sekolah.id', 'phone' => '081234567801'],
            ['name' => 'Siti Nurjanah, S.Pd', 'nip' => '198602022010012002', 'email' => 'siti@sekolah.id', 'phone' => '081234567802'],
            ['name' => 'Ahmad Yani, M.Pd', 'nip' => '198703032011011003', 'email' => 'ahmad@sekolah.id', 'phone' => '081234567803'],
            ['name' => 'Rina Wijaya, S.Pd', 'nip' => '198804042011012004', 'email' => 'rina@sekolah.id', 'phone' => '081234567804'],
            ['name' => 'Dedi Kusuma, S.Pd', 'nip' => '198905052012011005', 'email' => 'dedi@sekolah.id', 'phone' => '081234567805'],
        ];

        $teacherModels = [];
        foreach ($teachers as $index => $teacherData) {
            $user = User::firstOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'password' => Hash::make('password'),
                ]
            );
            $user->assignRole('guru');

            $teacherModels[] = Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'nip' => $teacherData['nip'],
                    'phone' => $teacherData['phone'],
                ]
            );
        }
        $this->command->info('✅ Created ' . count($teacherModels) . ' teachers');

        // 4. Create Students for each class
        $this->command->info('👨‍🎓 Creating Students...');
        $studentCount = 0;

        $firstNames = [
            'Ahmad',
            'Budi',
            'Citra',
            'Deni',
            'Eka',
            'Fajar',
            'Gita',
            'Hadi',
            'Indra',
            'Joko',
            'Kartika',
            'Lina',
            'Maya',
            'Nina',
            'Oka',
            'Putri',
            'Rara',
            'Sari',
            'Tuti',
            'Umar',
            'Vina',
            'Wati',
            'Yudi',
            'Zaki',
            'Ayu',
            'Bayu',
            'Dewi',
            'Endi',
            'Fitri',
            'Galuh'
        ];

        $lastNames = [
            'Pratama',
            'Wijaya',
            'Santoso',
            'Kusuma',
            'Saputra',
            'Saputri',
            'Hidayat',
            'Rahmawati',
            'Permana',
            'Nugroho',
            'Handoko',
            'Susanti',
            'Kurniawan',
            'Lestari',
            'Budiman'
        ];

        foreach ($classes as $classIndex => $class) {
            $studentsPerClass = rand(25, 32); // Random 25-32 siswa per kelas

            for ($i = 1; $i <= $studentsPerClass; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = $firstName . ' ' . $lastName;

                $nis = sprintf('2024%02d%03d', $classIndex + 1, $i);
                $gender = (rand(0, 1) === 0) ? 'M' : 'F';

                Student::firstOrCreate(
                    ['nis' => $nis],
                    [
                        'name' => $fullName,
                        'gender' => $gender,
                        'class_room_id' => $class->id,
                    ]
                );
                $studentCount++;
            }
        }
        $this->command->info('✅ Created ' . $studentCount . ' students');

        // 5. Create Teaching Assignments
        $this->command->info('📝 Creating Teaching Assignments...');
        $assignments = [
            // Teacher 0 - Budi (MTK untuk MIPA)
            [$teacherModels[0]->id, $subjectModels['MTK']->id, $classes[0]->id], // 10 MIPA A
            [$teacherModels[0]->id, $subjectModels['MTK']->id, $classes[1]->id], // 10 MIPA B
            [$teacherModels[0]->id, $subjectModels['MTK']->id, $classes[3]->id], // 11 MIPA A

            // Teacher 1 - Siti (FIS untuk MIPA)
            [$teacherModels[1]->id, $subjectModels['FIS']->id, $classes[0]->id],
            [$teacherModels[1]->id, $subjectModels['FIS']->id, $classes[1]->id],

            // Teacher 2 - Ahmad (EKO untuk IPS)
            [$teacherModels[2]->id, $subjectModels['EKO']->id, $classes[2]->id], // 10 IPS A
            [$teacherModels[2]->id, $subjectModels['EKO']->id, $classes[4]->id], // 12 IPS A
            [$teacherModels[2]->id, $subjectModels['SEJ']->id, $classes[2]->id],

            // Teacher 3 - Rina (IND untuk semua)
            [$teacherModels[3]->id, $subjectModels['IND']->id, $classes[0]->id],
            [$teacherModels[3]->id, $subjectModels['IND']->id, $classes[2]->id],
            [$teacherModels[3]->id, $subjectModels['IND']->id, $classes[3]->id],

            // Teacher 4 - Dedi (PJOK untuk semua)
            [$teacherModels[4]->id, $subjectModels['PJOK']->id, $classes[0]->id],
            [$teacherModels[4]->id, $subjectModels['PJOK']->id, $classes[1]->id],
            [$teacherModels[4]->id, $subjectModels['PJOK']->id, $classes[2]->id],
        ];

        foreach ($assignments as $assignment) {
            TeachingAssignment::firstOrCreate([
                'teacher_id' => $assignment[0],
                'subject_id' => $assignment[1],
                'class_room_id' => $assignment[2],
            ]);
        }
        $this->command->info('✅ Created ' . count($assignments) . ' teaching assignments');

        // 6. Create Schedules
        $this->command->info('📅 Creating Schedules...');
        $schedules = [
            // Senin
            [$teacherModels[0]->id, $subjectModels['MTK']->id, $classes[0]->id, 1, '07:00-08:00'], // 10 MIPA A
            [$teacherModels[1]->id, $subjectModels['FIS']->id, $classes[0]->id, 1, '08:00-09:00'], // 10 MIPA A
            [$teacherModels[3]->id, $subjectModels['IND']->id, $classes[0]->id, 1, '09:15-10:15'], // 10 MIPA A

            // Selasa
            [$teacherModels[0]->id, $subjectModels['MTK']->id, $classes[1]->id, 2, '07:00-08:00'], // 10 MIPA B
            [$teacherModels[2]->id, $subjectModels['EKO']->id, $classes[2]->id, 2, '08:00-09:00'], // 10 IPS A
            [$teacherModels[4]->id, $subjectModels['PJOK']->id, $classes[0]->id, 2, '13:00-14:00'], // 10 MIPA A

            // Rabu
            [$teacherModels[1]->id, $subjectModels['FIS']->id, $classes[1]->id, 3, '07:00-08:00'], // 10 MIPA B
            [$teacherModels[3]->id, $subjectModels['IND']->id, $classes[2]->id, 3, '08:00-09:00'], // 10 IPS A

            // Kamis
            [$teacherModels[0]->id, $subjectModels['MTK']->id, $classes[3]->id, 4, '07:00-08:00'], // 11 MIPA A
            [$teacherModels[2]->id, $subjectModels['EKO']->id, $classes[4]->id, 4, '08:00-09:00'], // 12 IPS A

            // Jumat
            [$teacherModels[4]->id, $subjectModels['PJOK']->id, $classes[1]->id, 5, '07:00-08:00'], // 10 MIPA B
            [$teacherModels[4]->id, $subjectModels['PJOK']->id, $classes[2]->id, 5, '08:00-09:00'], // 10 IPS A
        ];

        foreach ($schedules as $schedule) {
            Schedule::firstOrCreate([
                'teacher_id' => $schedule[0],
                'subject_id' => $schedule[1],
                'class_room_id' => $schedule[2],
                'weekday' => $schedule[3],
                'time_slot' => $schedule[4],
            ]);
        }
        $this->command->info('✅ Created ' . count($schedules) . ' schedules');

        // 7. Create Attendance Records (Last 30 days untuk Semester 2)
        $this->command->info('📋 Creating Attendance Records...');
        $attendanceCount = 0;

        // Generate untuk 30 hari terakhir
        $endDate = Carbon::now();
        $startDate = Carbon::now()->subDays(30);

        foreach ($classes as $class) {
            $students = Student::where('class_room_id', $class->id)->get();
            $classSchedules = Schedule::where('class_room_id', $class->id)->get();

            // Loop tanggal
            for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                // Skip weekend
                if ($date->isWeekend()) continue;

                $weekday = $date->dayOfWeekIso; // 1=Monday, 7=Sunday

                // Cek ada jadwal di hari ini
                $todaySchedules = $classSchedules->where('weekday', $weekday);

                foreach ($todaySchedules as $schedule) {
                    // Get teacher's user_id for recorded_by
                    $teacher = Teacher::find($schedule->teacher_id);
                    $recordedBy = $teacher->user_id;

                    // Create attendance untuk setiap siswa
                    foreach ($students as $student) {
                        // Random status dengan distribusi realistis
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

                        Attendance::firstOrCreate([
                            'class_room_id' => $class->id,
                            'student_id' => $student->id,
                            'teacher_id' => $schedule->teacher_id,
                            'subject_id' => $schedule->subject_id,
                            'date' => $date->format('Y-m-d'),
                        ], [
                            'status' => $status,
                            'recorded_by' => $recordedBy,
                        ]);
                        $attendanceCount++;
                    }
                }
            }
        }

        $this->command->info('✅ Created ' . $attendanceCount . ' attendance records');

        $this->command->info('');
        $this->command->info('🎉 Production Dummy Data Seeding Completed!');
        $this->command->info('');
        $this->command->info('📊 Summary:');
        $this->command->info('   - Subjects: ' . count($subjectModels));
        $this->command->info('   - Classes: ' . count($classes));
        $this->command->info('   - Teachers: ' . count($teacherModels));
        $this->command->info('   - Students: ' . $studentCount);
        $this->command->info('   - Teaching Assignments: ' . count($assignments));
        $this->command->info('   - Schedules: ' . count($schedules));
        $this->command->info('   - Attendance Records: ' . $attendanceCount);
        $this->command->info('');
        $this->command->info('🔑 Teacher Login Credentials:');
        foreach ($teachers as $teacher) {
            $this->command->info('   Email: ' . $teacher['email'] . ' | Password: password');
        }
    }
}

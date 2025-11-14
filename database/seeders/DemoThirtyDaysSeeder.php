<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\Subject;
use App\Models\ClassRoom;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\TeachingAssignment;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoThirtyDaysSeeder extends Seeder
{
    /**
     * Seeder untuk simulasi 30 hari penggunaan sistem
     * Data ini mencerminkan aktivitas nyata setelah sistem digunakan selama 1 bulan
     */
    public function run(): void
    {
        $this->command->info('🚀 Memulai seeding data demo 30 hari...');

        // Create roles first
        $adminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin']);
        $guruRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'guru']);
        $this->command->info('✓ Roles: admin, guru');

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@sman10.sch.id'],
            [
                'name' => 'Administrator SMAN 10',
                'password' => Hash::make('password'),
            ]
        );
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }
        $this->command->info('✓ Admin user: ' . $adminUser->email);

        // Mendapatkan atau membuat tahun ajaran aktif
        $academicYear = AcademicYear::firstOrCreate(
            ['name' => '2024/2025'],
            [
                'start_year' => 2024,
                'end_year' => 2025,
                'start_date' => '2024-07-01',
                'end_date' => '2025-06-30',
                'is_active' => true,
            ]
        );
        $this->command->info('✓ Tahun ajaran: ' . $academicYear->name);

        // Program
        $programs = [
            ['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam', 'short_name' => 'IPA'],
            ['code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial', 'short_name' => 'IPS'],
            ['code' => 'BHS', 'name' => 'Bahasa', 'short_name' => 'BAHASA'],
        ];

        foreach ($programs as $programData) {
            Program::firstOrCreate(
                ['code' => $programData['code']],
                $programData
            );
        }
        $this->command->info('✓ Program: 3 program');

        // Mata Pelajaran
        $subjects = [
            // Umum
            ['name' => 'Matematika', 'code' => 'MTK'],
            ['name' => 'Bahasa Indonesia', 'code' => 'BIND'],
            ['name' => 'Bahasa Inggris', 'code' => 'BING'],
            ['name' => 'Pendidikan Agama Islam', 'code' => 'PAI'],
            ['name' => 'Pendidikan Pancasila', 'code' => 'PPKN'],
            ['name' => 'Pendidikan Jasmani', 'code' => 'PJOK'],
            ['name' => 'Sejarah Indonesia', 'code' => 'SEJ'],
            ['name' => 'Seni Budaya', 'code' => 'SB'],
            // IPA
            ['name' => 'Fisika', 'code' => 'FIS'],
            ['name' => 'Kimia', 'code' => 'KIM'],
            ['name' => 'Biologi', 'code' => 'BIO'],
            // IPS
            ['name' => 'Ekonomi', 'code' => 'EKO'],
            ['name' => 'Sosiologi', 'code' => 'SOS'],
            ['name' => 'Geografi', 'code' => 'GEO'],
            // Bahasa
            ['name' => 'Bahasa Jepang', 'code' => 'BJPN'],
            ['name' => 'Bahasa Mandarin', 'code' => 'BMND'],
            ['name' => 'Sastra Indonesia', 'code' => 'SAST'],
        ];

        foreach ($subjects as $subjectData) {
            Subject::firstOrCreate(
                ['code' => $subjectData['code']],
                $subjectData
            );
        }
        $this->command->info('✓ Mata Pelajaran: ' . count($subjects) . ' mapel');

        // Kelas - 18 kelas (6 kelas per tingkat: X, XI, XII)
        $classes = [];
        $programs = Program::all();
        
        foreach ([10, 11, 12] as $grade) {
            foreach ($programs as $program) {
                for ($section = 1; $section <= 2; $section++) {
                    $className = "X-{$program->short_name}-{$section}";
                    if ($grade == 11) $className = "XI-{$program->short_name}-{$section}";
                    if ($grade == 12) $className = "XII-{$program->short_name}-{$section}";
                    
                    $classes[] = ClassRoom::firstOrCreate(
                        [
                            'name' => $className,
                            'academic_year_id' => $academicYear->id,
                        ],
                        [
                            'grade_level' => $grade,
                            'section' => $section,
                            'program_id' => $program->id,
                        ]
                    );
                }
            }
        }
        $this->command->info('✓ Kelas: ' . count($classes) . ' kelas');

        // Guru - 15 guru dengan berbagai mata pelajaran
        $teachersData = [
            ['name' => 'Drs. Budi Santoso, M.Pd', 'nip' => '196505121990031001', 'email' => 'budi.santoso@sman10.sch.id', 'phone' => '081234567801'],
            ['name' => 'Dr. Siti Aminah, S.Pd, M.Si', 'nip' => '197203151995122001', 'email' => 'siti.aminah@sman10.sch.id', 'phone' => '081234567802'],
            ['name' => 'Ahmad Hidayat, S.Pd, M.Pd', 'nip' => '198008202005011001', 'email' => 'ahmad.hidayat@sman10.sch.id', 'phone' => '081234567803'],
            ['name' => 'Rina Puspitasari, S.Pd', 'nip' => '198506102008012001', 'email' => 'rina.puspita@sman10.sch.id', 'phone' => '081234567804'],
            ['name' => 'Dedi Kurniawan, S.Si, M.Pd', 'nip' => '197912252006041001', 'email' => 'dedi.kurniawan@sman10.sch.id', 'phone' => '081234567805'],
            ['name' => 'Maya Sari Dewi, S.Pd, M.Hum', 'nip' => '198209182009032001', 'email' => 'maya.sari@sman10.sch.id', 'phone' => '081234567806'],
            ['name' => 'Hendra Wijaya, S.Pd', 'nip' => '198703152010011001', 'email' => 'hendra.wijaya@sman10.sch.id', 'phone' => '081234567807'],
            ['name' => 'Fitri Handayani, S.Pd, M.Pd', 'nip' => '198405202011012001', 'email' => 'fitri.handayani@sman10.sch.id', 'phone' => '081234567808'],
            ['name' => 'Bambang Suryanto, S.E, M.Pd', 'nip' => '197608122003121001', 'email' => 'bambang.surya@sman10.sch.id', 'phone' => '081234567809'],
            ['name' => 'Lestari Wulandari, S.Sos, M.Pd', 'nip' => '198112282007032001', 'email' => 'lestari.wulan@sman10.sch.id', 'phone' => '081234567810'],
            ['name' => 'Agus Prasetyo, S.Pd', 'nip' => '198906152012011001', 'email' => 'agus.prasetyo@sman10.sch.id', 'phone' => '081234567811'],
            ['name' => 'Diah Permatasari, S.Pd, M.Pd', 'nip' => '198302102009022001', 'email' => 'diah.permata@sman10.sch.id', 'phone' => '081234567812'],
            ['name' => 'Rudi Hartono, S.Pd.I', 'nip' => '197807252005011001', 'email' => 'rudi.hartono@sman10.sch.id', 'phone' => '081234567813'],
            ['name' => 'Nurul Hidayah, S.Pd', 'nip' => '198504182010012001', 'email' => 'nurul.hidayah@sman10.sch.id', 'phone' => '081234567814'],
            ['name' => 'Eko Prasetyo, S.Pd, M.Or', 'nip' => '198001102008011001', 'email' => 'eko.prasetyo@sman10.sch.id', 'phone' => '081234567815'],
        ];

        $teachers = [];
        foreach ($teachersData as $teacherData) {
            // Create user first
            $user = User::firstOrCreate(
                ['email' => $teacherData['email']],
                [
                    'name' => $teacherData['name'],
                    'password' => Hash::make('password'),
                ]
            );
            
            // Assign guru role
            if (!$user->hasRole('guru')) {
                $user->assignRole('guru');
            }

            // Then create teacher
            $teacher = Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $teacherData['name'],
                    'nip' => $teacherData['nip'],
                    'phone' => $teacherData['phone'],
                ]
            );

            $teachers[] = $teacher;
        }
        $this->command->info('✓ Guru: ' . count($teachers) . ' guru');

        // Siswa - 540 siswa (30 siswa per kelas x 18 kelas)
        $this->command->info('Membuat siswa...');
        $students = [];
        $studentNames = [
            'L' => [
                'Ahmad Rizki', 'Budi Santoso', 'Dedi Firmansyah', 'Eko Prasetyo', 'Fajar Nugroho',
                'Galih Pratama', 'Hendra Wijaya', 'Indra Gunawan', 'Joko Susilo', 'Kurniawan',
                'Lukman Hakim', 'Muhamad Iqbal', 'Nur Hadi', 'Oki Saputra', 'Putra Pratama',
            ],
            'P' => [
                'Ani Susanti', 'Bella Safira', 'Citra Dewi', 'Dina Amelia', 'Eka Putri',
                'Fitri Handayani', 'Gita Maharani', 'Hana Pertiwi', 'Intan Permata', 'Jasmine',
                'Kartika Sari', 'Lina Marlina', 'Maya Anggraini', 'Nadia Puspita', 'Olivia',
            ],
        ];

        $nisCounter = 20240001;
        foreach ($classes as $classRoom) {
            for ($i = 1; $i <= 30; $i++) {
                $gender = ($i % 2 == 0) ? 'F' : 'M';
                $namePool = $studentNames[$gender == 'M' ? 'L' : 'P'];
                $firstName = $namePool[array_rand($namePool)];
                $lastName = ['Putra', 'Pratama', 'Wijaya', 'Santoso', 'Hidayat'][array_rand(['Putra', 'Pratama', 'Wijaya', 'Santoso', 'Hidayat'])];
                if ($gender == 'F') {
                    $lastName = ['Putri', 'Dewi', 'Sari', 'Wati', 'Anggraini'][array_rand(['Putri', 'Dewi', 'Sari', 'Wati', 'Anggraini'])];
                }

                $students[] = Student::create([
                    'nis' => (string)$nisCounter++,
                    'name' => $firstName . ' ' . $lastName,
                    'gender' => $gender,
                    'class_room_id' => $classRoom->id,
                ]);
            }
        }
        $this->command->info('✓ Siswa: ' . count($students) . ' siswa');

        // Teaching Assignments - setiap guru mengajar 2-4 mata pelajaran di beberapa kelas
        $this->command->info('Membuat penugasan mengajar...');
        $allSubjects = Subject::all();
        $assignments = [];
        
        // Mapping guru ke mata pelajaran (sesuai keahlian)
        $teacherSubjects = [
            0 => ['MTK'], // Matematika
            1 => ['FIS'], // Fisika
            2 => ['KIM'], // Kimia
            3 => ['BIO'], // Biologi
            4 => ['BIND', 'SAST'], // Bahasa Indonesia & Sastra
            5 => ['BING'], // Bahasa Inggris
            6 => ['EKO'], // Ekonomi
            7 => ['SOS'], // Sosiologi
            8 => ['GEO'], // Geografi
            9 => ['SEJ', 'PPKN'], // Sejarah & PPKn
            10 => ['PAI'], // PAI
            11 => ['BJPN', 'BMND'], // Bahasa Jepang & Mandarin
            12 => ['PAI'], // PAI
            13 => ['SB'], // Seni Budaya
            14 => ['PJOK'], // PJOK
        ];

        foreach ($teachers as $idx => $teacher) {
            $subjectCodes = $teacherSubjects[$idx] ?? ['MTK'];
            $teacherSubjects = Subject::whereIn('code', $subjectCodes)->get();
            
            // Setiap guru mengajar di 4-6 kelas
            $assignedClasses = collect($classes)->random(rand(4, 6));
            
            foreach ($teacherSubjects as $subject) {
                foreach ($assignedClasses as $classRoom) {
                    $assignments[] = TeachingAssignment::firstOrCreate([
                        'teacher_id' => $teacher->id,
                        'subject_id' => $subject->id,
                        'class_room_id' => $classRoom->id,
                    ]);
                }
            }
        }
        $this->command->info('✓ Penugasan Mengajar: ' . count($assignments) . ' penugasan');

        // Jadwal - membuat jadwal untuk setiap penugasan (Senin-Jumat)
        $this->command->info('Membuat jadwal pelajaran...');
        $schedules = [];
        $timeSlots = [
            '07:00-08:00',
            '08:00-09:00',
            '09:00-10:00',
            '10:15-11:15',
            '11:15-12:15',
            '12:45-13:45',
            '13:45-14:45',
        ];

        foreach ($assignments as $assignment) {
            // Setiap assignment mendapat 2 jadwal per minggu
            for ($i = 0; $i < 2; $i++) {
                $weekday = rand(1, 5); // Senin-Jumat
                $timeSlot = $timeSlots[array_rand($timeSlots)];
                
                $schedules[] = Schedule::firstOrCreate([
                    'teacher_id' => $assignment->teacher_id,
                    'subject_id' => $assignment->subject_id,
                    'class_room_id' => $assignment->class_room_id,
                    'weekday' => $weekday,
                    'time_slot' => $timeSlot,
                ]);
            }
        }
        $this->command->info('✓ Jadwal: ' . count($schedules) . ' jadwal');

        // Attendance - membuat data absensi untuk 30 hari terakhir (hanya hari kerja)
        $this->command->info('Membuat data absensi 30 hari...');
        $attendanceCount = 0;
        
        // Tanggal mulai: 30 hari yang lalu
        $startDate = Carbon::now()->subDays(30);
        
        // Loop untuk setiap hari dalam 30 hari terakhir
        for ($day = 0; $day < 30; $day++) {
            $date = $startDate->copy()->addDays($day);
            
            // Skip weekend (Sabtu & Minggu)
            if ($date->isWeekend()) {
                continue;
            }
            
            $weekday = $date->dayOfWeekIso; // 1=Senin, 5=Jumat
            
            // Ambil semua jadwal untuk hari ini
            $todaySchedules = collect($schedules)->filter(function($schedule) use ($weekday) {
                return $schedule->weekday == $weekday;
            });
            
            // Untuk setiap jadwal hari ini, buat absensi untuk semua siswa di kelas tersebut
            foreach ($todaySchedules as $schedule) {
                $classStudents = Student::where('class_room_id', $schedule->class_room_id)->get();
                
                foreach ($classStudents as $student) {
                    // Probabilitas kehadiran realistis:
                    // 90% hadir, 5% sakit, 3% izin, 2% alfa
                    $rand = rand(1, 100);
                    if ($rand <= 90) {
                        $status = 'HADIR';
                    } elseif ($rand <= 95) {
                        $status = 'SAKIT';
                    } elseif ($rand <= 98) {
                        $status = 'IZIN';
                    } else {
                        $status = 'ALFA';
                    }
                    
                    // Ambil user_id guru untuk recorded_by
                    $teacher = Teacher::find($schedule->teacher_id);
                    
                    // Cek apakah sudah ada record untuk kombinasi ini
                    $existing = Attendance::where([
                        'date' => $date->format('Y-m-d'),
                        'class_room_id' => $schedule->class_room_id,
                        'subject_id' => $schedule->subject_id,
                        'student_id' => $student->id,
                    ])->first();
                    
                    if (!$existing) {
                        Attendance::create([
                            'date' => $date->format('Y-m-d'),
                            'class_room_id' => $schedule->class_room_id,
                            'subject_id' => $schedule->subject_id,
                            'teacher_id' => $schedule->teacher_id,
                            'student_id' => $student->id,
                            'status' => $status,
                            'recorded_by' => $teacher->user_id,
                        ]);
                        
                        $attendanceCount++;
                    }
                }
            }
            
            $this->command->info("  → Absensi tanggal {$date->format('Y-m-d')} ({$date->format('l')}): " . $todaySchedules->count() . " jadwal");
        }
        
        $this->command->info('✓ Total Absensi: ' . $attendanceCount . ' records');

        // Summary
        $this->command->info('');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('🎉 SEEDING DATA DEMO 30 HARI SELESAI!');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('📊 RINGKASAN DATA:');
        $this->command->info('   • Tahun Ajaran: ' . $academicYear->name);
        $this->command->info('   • Program: ' . Program::count() . ' program');
        $this->command->info('   • Mata Pelajaran: ' . Subject::count() . ' mapel');
        $this->command->info('   • Kelas: ' . ClassRoom::count() . ' kelas');
        $this->command->info('   • Guru: ' . Teacher::count() . ' guru');
        $this->command->info('   • Siswa: ' . Student::count() . ' siswa');
        $this->command->info('   • Penugasan Mengajar: ' . TeachingAssignment::count() . ' penugasan');
        $this->command->info('   • Jadwal: ' . Schedule::count() . ' jadwal');
        $this->command->info('   • Absensi (30 hari): ' . Attendance::count() . ' records');
        $this->command->info('═══════════════════════════════════════════════════════════');
        $this->command->info('');
        $this->command->info('🔑 KREDENSIAL LOGIN:');
        $this->command->info('   Admin: admin@sman10.sch.id / password');
        $this->command->info('   Guru: budi.santoso@sman10.sch.id / password');
        $this->command->info('   (dan guru lainnya dengan format: [nama]@sman10.sch.id)');
        $this->command->info('═══════════════════════════════════════════════════════════');
    }
}

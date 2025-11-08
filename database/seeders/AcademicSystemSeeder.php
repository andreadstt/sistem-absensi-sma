<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Program;

class AcademicSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Academic Years
        $currentYear = AcademicYear::create([
            'name' => '2024/2025',
            'start_year' => 2024,
            'end_year' => 2025,
            'start_date' => '2024-07-15',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        AcademicYear::create([
            'name' => '2023/2024',
            'start_year' => 2023,
            'end_year' => 2024,
            'start_date' => '2023-07-15',
            'end_date' => '2024-06-30',
            'is_active' => false,
        ]);

        // Create Programs (Jurusan)
        Program::create([
            'code' => 'MIPA',
            'name' => 'Matematika dan Ilmu Pengetahuan Alam',
            'short_name' => 'MIPA',
            'description' => 'Program studi yang fokus pada matematika, fisika, kimia, dan biologi',
            'min_grade_level' => 10,
            'is_active' => true,
        ]);

        Program::create([
            'code' => 'IPS',
            'name' => 'Ilmu Pengetahuan Sosial',
            'short_name' => 'IPS',
            'description' => 'Program studi yang fokus pada ekonomi, sosiologi, geografi, dan sejarah',
            'min_grade_level' => 10,
            'is_active' => true,
        ]);

        Program::create([
            'code' => 'BAHASA',
            'name' => 'Bahasa dan Budaya',
            'short_name' => 'Bahasa',
            'description' => 'Program studi yang fokus pada bahasa Indonesia, bahasa asing, dan budaya',
            'min_grade_level' => 10,
            'is_active' => true,
        ]);

        $this->command->info('Academic system data seeded successfully!');
        $this->command->info("Active Academic Year: {$currentYear->name}");
        $this->command->info('Programs: MIPA, IPS, Bahasa');
    }
}

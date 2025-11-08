<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\Schedule;

class GuruTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create guru user
        $guruUser = User::where('email', 'guru@example.com')->first();
        if (!$guruUser) {
            $guruUser = User::create([
                'name' => 'Guru User',
                'email' => 'guru@example.com',
                'password' => bcrypt('password')
            ]);
            $guruUser->assignRole('guru');
        }

        // Create teacher profile for guru
        $teacher = Teacher::where('user_id', $guruUser->id)->first();
        if (!$teacher) {
            $teacher = Teacher::create([
                'user_id' => $guruUser->id,
                'name' => 'Pak Budi Santoso',
                'nip' => 'T12345',
                'phone' => '081234567890'
            ]);
        }

        // Get today's weekday (1=Monday, 7=Sunday)
        $today = now()->dayOfWeekIso;

        // Create some test classes and subjects
        $class1 = ClassRoom::firstOrCreate(['name' => '10A'], ['grade_level' => 10]);
        $class2 = ClassRoom::firstOrCreate(['name' => '10B'], ['grade_level' => 10]);

        $math = Subject::firstOrCreate(['code' => 'MTK'], ['name' => 'Matematika']);
        $physics = Subject::firstOrCreate(['code' => 'FIS'], ['name' => 'Fisika']);

        // Create teaching assignments
        TeachingAssignment::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $math->id,
            'class_room_id' => $class1->id
        ]);

        TeachingAssignment::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $physics->id,
            'class_room_id' => $class2->id
        ]);

        // Create schedules for TODAY
        Schedule::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $math->id,
            'class_room_id' => $class1->id,
            'weekday' => $today,
            'time_slot' => '07:00-08:00'
        ]);

        Schedule::firstOrCreate([
            'teacher_id' => $teacher->id,
            'subject_id' => $physics->id,
            'class_room_id' => $class2->id,
            'weekday' => $today,
            'time_slot' => '09:00-10:00'
        ]);

        $this->command->info('Guru test data created!');
        $this->command->info('Guru user: guru@example.com (password: password)');
        $this->command->info('Teacher: ' . $teacher->name);
        $this->command->info('Today is weekday: ' . $today . ' (' . now()->format('l') . ')');
        $this->command->info('2 schedules created for today.');
    }
}

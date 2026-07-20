<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$teacher = \App\Models\Teacher::first();

$academicYear = \App\Models\AcademicYear::create([
    'name' => '9999/9999',
    'start_year' => 9999,
    'end_year' => 9999,
    'start_date' => '9999-07-01',
    'end_date' => '9999-06-30',
    'is_active' => false,
]);

$academicYear->semesters()->create([
    'type' => '1',
    'start_date' => '9999-07-01',
    'end_date' => '9999-12-31'
]);

$classRoom = \App\Models\ClassRoom::create([
    'academic_year_id' => $academicYear->id,
    'head_teacher_id' => clone $teacher->id,
    'name' => '11 MIPA X',
    'grade_level' => 11,
    'section' => 'X',
    'program_id' => null,
]);

Livewire\Livewire::actingAs($user)
    ->test(App\Filament\Pages\RekapAbsensi::class)
    ->set('data', [
        'export_type' => 'semester',
        'class_room_id' => $classRoom->id,
        'semester' => '1',
        'year' => '9999/9999'
    ])
    ->call('exportToCSV')
    ->assertFileDownloaded("absensi_semester_1_11 MIPA X.xlsx");
echo "ASSERTION PASSED\n";

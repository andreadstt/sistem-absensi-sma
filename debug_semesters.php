<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$semesters = \App\Models\Semester::all();
foreach ($semesters as $s) {
    echo "Semester {$s->id}: Type {$s->type}, {$s->start_date->format('Y-m-d')} to {$s->end_date->format('Y-m-d')} (Academic Year: {$s->academic_year_id})\n";
}

$attendances = \App\Models\Attendance::where('class_room_id', 13)->orderBy('date', 'asc')->get();
if ($attendances->count() > 0) {
    echo "\nClass 13 Attendance: from {$attendances->first()->date} to {$attendances->last()->date} ({$attendances->count()} records)\n";
} else {
    echo "\nNo attendance for Class 13\n";
}

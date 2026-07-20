<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$classRoom = \App\Models\ClassRoom::first();
$date = \Carbon\Carbon::now()->subDays(2)->toDateString(); // Just a date

$export = new \App\Exports\AttendanceDailyExport($classRoom, $date);
\Maatwebsite\Excel\Facades\Excel::store($export, 'test_export.xlsx', 'local');
echo "Export success to storage/app/test_export.xlsx\n";

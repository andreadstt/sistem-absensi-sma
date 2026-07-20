<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Livewire\Livewire::test(App\Filament\Pages\RekapAbsensi::class)
        ->set('data', [
            'export_type' => 'semester',
            'class_room_id' => 1, // Assume 1 exists
            'semester' => '1',
            'year' => '2024/2025'
        ])
        ->call('exportToCSV');
    echo "NO EXCEPTION\n";
} catch (\Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$school = App\Models\School::first();
if ($school) {
    echo "School: " . $school->name . PHP_EOL;
    echo "2025 Christmas Start: " . ($school->christmas_start_date ? $school->christmas_start_date->format('Y-m-d') : 'NULL') . PHP_EOL;
    echo "2025 Christmas End: " . ($school->christmas_end_date ? $school->christmas_end_date->format('Y-m-d') : 'NULL') . PHP_EOL;
    echo "2026 Christmas Start: " . ($school->christmas_start_date_2026 ? $school->christmas_start_date_2026->format('Y-m-d') : 'NULL') . PHP_EOL;
    echo "2026 Christmas End: " . ($school->christmas_end_date_2026 ? $school->christmas_end_date_2026->format('Y-m-d') : 'NULL') . PHP_EOL;
    echo "Christmas Fee Per Week: £" . ($school->christmas_fee_per_week ?? 'NULL') . PHP_EOL;
    echo "Christmas Fee Per Week 2026: £" . ($school->christmas_fee_per_week_2026 ?? 'NULL') . PHP_EOL;
} else {
    echo "No school found" . PHP_EOL;
}
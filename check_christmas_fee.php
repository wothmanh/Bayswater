<?php

require_once 'vendor/autoload.php';

// Load Laravel application
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Query the school's Christmas fee configuration
$school = \App\Models\School::first(['id', 'name', 'christmas_fee_per_week', 'christmas_start_date', 'christmas_end_date']);

if ($school) {
    echo "School Information:\n";
    echo "ID: " . $school->id . "\n";
    echo "Name: " . $school->name . "\n";
    echo "Christmas Fee Per Week: " . ($school->christmas_fee_per_week ?? 'NULL') . "\n";
    echo "Christmas Start Date: " . ($school->christmas_start_date ?? 'NULL') . "\n";
    echo "Christmas End Date: " . ($school->christmas_end_date ?? 'NULL') . "\n";
} else {
    echo "No school found in database.\n";
}
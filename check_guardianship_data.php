<?php

require 'vendor/autoload.php';

$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Accommodation;
use App\Models\School;

echo "=== Guardianship Data Check ===\n";
echo "Accommodations with guardianship required: " . Accommodation::where('requires_guardianship', true)->count() . "\n";
echo "Schools with guardianship fee: " . School::whereNotNull('guardianship_fee_per_week')->where('guardianship_fee_per_week', '>', 0)->count() . "\n\n";

echo "=== All Accommodations ===\n";
Accommodation::all()->each(function($acc) {
    echo "ID: {$acc->id}, Name: {$acc->name}, Guardianship Required: " . ($acc->requires_guardianship ? 'YES' : 'NO') . "\n";
});

echo "\n=== All Schools ===\n";
School::all()->each(function($school) {
    echo "ID: {$school->id}, Name: {$school->name}, Guardianship Fee Per Week: " . ($school->guardianship_fee_per_week ?? 'NULL') . "\n";
});

echo "\n=== Test Data Check ===\n";
$testAccommodation = Accommodation::find(1);
if ($testAccommodation) {
    echo "Test Accommodation (ID 1): {$testAccommodation->name}\n";
    echo "Requires Guardianship: " . ($testAccommodation->requires_guardianship ? 'YES' : 'NO') . "\n";
} else {
    echo "No accommodation with ID 1 found\n";
}

$testSchool = School::find(1);
if ($testSchool) {
    echo "Test School (ID 1): {$testSchool->name}\n";
    echo "Guardianship Fee Per Week: " . ($testSchool->guardianship_fee_per_week ?? 'NULL') . "\n";
} else {
    echo "No school with ID 1 found\n";
}
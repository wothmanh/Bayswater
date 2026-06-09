<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Course;
use App\Models\Accommodation;
use App\Models\School;

echo "Available Schools:\n";
foreach(School::all() as $school) {
    echo "ID: {$school->id}, Name: {$school->name}, Guardianship Fee: £{$school->guardianship_fee_per_week}\n";
}

echo "\nAvailable Courses:\n";
foreach(Course::all() as $course) {
    echo "ID: {$course->id}, Name: {$course->name}, School: {$course->school_id}\n";
}

echo "\nAvailable Accommodations:\n";
foreach(Accommodation::all() as $accommodation) {
    $guardianship = $accommodation->requires_guardianship ? 'YES' : 'NO';
    echo "ID: {$accommodation->id}, Name: {$accommodation->name}, School: {$accommodation->school_id}, Guardianship: {$guardianship}\n";
}

echo "\nTest completed.\n";
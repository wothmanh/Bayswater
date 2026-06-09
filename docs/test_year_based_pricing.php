<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\School;
use App\Models\Course;
use App\Models\Accommodation;
use App\Services\FeeCalculatorService;
use Carbon\Carbon;

echo "Testing Year-Based Pricing Implementation\n";
echo "========================================\n\n";

// Get the first school for testing
$school = School::first();
if (!$school) {
    echo "No schools found in database. Please add a school first.\n";
    exit(1);
}

echo "Testing with School: {$school->name}\n\n";

// Update the school with test fees for both years
$school->update([
    'registration_fee' => 100,
    'registration_fee_2026' => 120,
    'accommodation_fee' => 200,
    'accommodation_fee_2026' => 250,
    'christmas_fee_per_week' => 50,
    'christmas_fee_per_week_2026' => 60,
    'guardianship_fee_per_week' => 30,
    'guardianship_fee_per_week_2026' => 35
]);

echo "Set up test fees:\n";
echo "- Registration: £100 (2025) / £120 (2026)\n";
echo "- Accommodation: £200 (2025) / £250 (2026)\n";
echo "- Christmas per week: £50 (2025) / £60 (2026)\n";
echo "- Guardianship per week: £30 (2025) / £35 (2026)\n\n";

// Test getFeeByYear method
echo "Testing School->getFeeByYear() method:\n";
echo "- Registration fee for 2025: £" . $school->getFeeByYear('registration_fee', '2025-06-01') . "\n";
echo "- Registration fee for 2026: £" . $school->getFeeByYear('registration_fee', '2026-06-01') . "\n";
echo "- Accommodation fee for 2025: £" . $school->getFeeByYear('accommodation_fee', '2025-06-01') . "\n";
echo "- Accommodation fee for 2026: £" . $school->getFeeByYear('accommodation_fee', '2026-06-01') . "\n\n";

// Get a course for testing
$course = Course::where('school_id', $school->id)->first();
if (!$course) {
    echo "No courses found for this school. Skipping FeeCalculatorService test.\n";
} else {
    // Test FeeCalculatorService with 2025 course
    $feeCalculator = new FeeCalculatorService();
    
    $quoteParams2025 = [
        'school_id' => $school->id,
        'course_id' => 1, // Add course_id
        'course_start_date' => '2025-06-01',
        'course_duration_weeks' => 12, // Use course_duration_weeks instead of course_weeks
        'accommodation_weeks' => 12,
        'accommodation_id' => 1,
        'guardianship_weeks' => 12
    ];
    
    echo "\n=== Testing FeeCalculatorService with 2025 course ===\n";
    $result2025 = $feeCalculator->calculateQuote($quoteParams2025);
    echo "Full result structure:\n";
    print_r($result2025);
    
    // Test FeeCalculatorService with 2026 course
    $quoteParams2026 = [
        'school_id' => $school->id,
        'course_id' => 1, // Add course_id
        'course_start_date' => '2026-06-01',
        'course_duration_weeks' => 12, // Use course_duration_weeks instead of course_weeks
        'accommodation_weeks' => 12,
        'accommodation_id' => 1,
        'guardianship_weeks' => 12
    ];
    
    echo "\n=== Testing FeeCalculatorService with 2026 course ===\n";
    $result2026 = $feeCalculator->calculateQuote($quoteParams2026);
    echo "Full result structure:\n";
    print_r($result2026);
}

echo "\nTest completed!\n";
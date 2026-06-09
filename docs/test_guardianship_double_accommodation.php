<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FeeCalculatorService;
use Carbon\Carbon;

echo "Testing Guardianship Fee Double-Charging for Two Accommodations\n";
echo "================================================================\n\n";

// Test scenario: Student under 18 with two courses and two accommodations
$testData = [
    'school_id' => 1,
    'course_id' => 1,
    'course_duration_weeks' => 12,
    'course_start_date' => '2024-09-02', // Monday
    'accommodation_id' => 1, // This has guardianship required
    'accommodation_duration_weeks' => 12,
    'requires_guardianship' => true,
    'guardianship_fee' => 50.00, // £50 per week
    'client_birthday' => '2007-12-15', // Student will be under 18 during both accommodations
    
    // Second course and accommodation
    'second_course_id' => 2,
    'second_course_duration_weeks' => 8,
    'second_course_start_date' => '2024-11-25', // 12 weeks after first course
    'second_accommodation_id' => 2, // This also has guardianship required
    'second_accommodation_duration_weeks' => 8,
    
    'region_id' => 1
];

try {
    echo "\nDebug: Test data being sent to calculator:\n";
    print_r($testData);

    $calculator = new FeeCalculatorService($testData);
    $breakdown = $calculator->calculateQuote($testData);

    echo "\nDebug: Raw breakdown structure:\n";
    print_r($breakdown);
    
    echo "\nDebug: All items in breakdown:\n";
    if (isset($breakdown['items']) && is_array($breakdown['items'])) {
        foreach ($breakdown['items'] as $index => $item) {
            echo "Item {$index}: {$item['name']} - £{$item['amount']} (Category: {$item['category']})\n";
        }
    } else {
        echo "No items found in breakdown\n";
    }
    
    echo "Test Data:\n";
    echo "- Student Birthday: {$testData['client_birthday']} (under 18)\n";
    echo "- First Course: {$testData['course_duration_weeks']} weeks starting {$testData['course_start_date']}\n";
    echo "- First Accommodation: {$testData['accommodation_duration_weeks']} weeks\n";
    echo "- Second Course: {$testData['second_course_duration_weeks']} weeks starting {$testData['second_course_start_date']}\n";
    echo "- Second Accommodation: {$testData['second_accommodation_duration_weeks']} weeks\n";
    echo "- Guardianship Fee Rate: £{$testData['guardianship_fee']} per week\n\n";
    
    echo "Calculated Dates:\n";
    if (isset($breakdown['accommodation_start_date'])) {
        echo "- First Accommodation Start: {$breakdown['accommodation_start_date']}\n";
    }
    if (isset($breakdown['accommodation_end_date'])) {
        echo "- First Accommodation End: {$breakdown['accommodation_end_date']}\n";
    }
    if (isset($breakdown['second_accommodation_start_date'])) {
        echo "- Second Accommodation Start: {$breakdown['second_accommodation_start_date']}\n";
    }
    if (isset($breakdown['second_accommodation_end_date'])) {
        echo "- Second Accommodation End: {$breakdown['second_accommodation_end_date']}\n";
    }
    echo "\n";
    
    // Debug: Print the full breakdown structure
    echo "Debug - Full breakdown keys: " . implode(', ', array_keys($breakdown)) . "\n\n";
    
    // Check for guardianship fees in both accommodations
    $firstAccommodationGuardianship = null;
    $secondAccommodationGuardianship = null;
    
    // Look for both guardianship fees
    foreach ($breakdown['items'] as $item) {
        if ($item['category'] === 'fees' && strpos($item['name'], 'Guardianship') !== false) {
            if (strpos($item['name'], 'Second Accommodation') !== false) {
                $secondAccommodationGuardianship = $item;
            } else {
                $firstAccommodationGuardianship = $item;
            }
        }
    }
    
    echo "\n";
    echo "Guardianship Fee Analysis:\n";
    echo "==========================\n";
    
    if ($firstAccommodationGuardianship) {
        $firstWeeks = $firstAccommodationGuardianship['amount'] / $testData['guardianship_fee'];
        echo "First Accommodation Guardianship: £{$firstAccommodationGuardianship['amount']} ({$firstWeeks} weeks)\n";
    } else {
        echo "First Accommodation Guardianship: NOT FOUND\n";
    }
    
    if ($secondAccommodationGuardianship) {
        $secondWeeks = $secondAccommodationGuardianship['amount'] / $testData['guardianship_fee'];
        echo "Second Accommodation Guardianship: £{$secondAccommodationGuardianship['amount']} ({$secondWeeks} weeks)\n";
    } else {
        echo "Second Accommodation Guardianship: NOT FOUND\n";
    }
    
    $totalGuardianshipFee = 0;
    if ($firstAccommodationGuardianship) $totalGuardianshipFee += $firstAccommodationGuardianship['amount'];
    if ($secondAccommodationGuardianship) $totalGuardianshipFee += $secondAccommodationGuardianship['amount'];
    
    echo "Total Guardianship Fee: £{$totalGuardianshipFee}\n";
    
    // Verify double-charging is working
    echo "\nVerification Results:\n";
    echo "====================\n";
    
    if ($firstAccommodationGuardianship && $secondAccommodationGuardianship) {
        echo "✓ PASS: Both accommodations have guardianship fees\n";
        echo "✓ PASS: Double-charging is working correctly\n";
    } elseif ($firstAccommodationGuardianship || $secondAccommodationGuardianship) {
        echo "⚠ PARTIAL: Only one accommodation has guardianship fee\n";
    } else {
        echo "✗ FAIL: No guardianship fees found\n";
    }
    
    // Calculate expected total weeks
    $studentBirthday = Carbon::parse($testData['client_birthday']);
    $eighteenthBirthday = $studentBirthday->copy()->addYears(18);
    $startDate = Carbon::parse($testData['course_start_date']);
    
    echo "\nAge Analysis:\n";
    echo "Student turns 18 on: {$eighteenthBirthday->format('Y-m-d')}\n";
    echo "Course starts on: {$startDate->format('Y-m-d')}\n";
    
    if ($startDate->lt($eighteenthBirthday)) {
        echo "✓ Student is under 18 at course start - guardianship fees should apply\n";
    } else {
        echo "✗ Student is 18+ at course start - no guardianship fees should apply\n";
    }
    
    echo "\nFull Cost Breakdown:\n";
    echo "===================\n";
    echo "Total: £{$breakdown['total']}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
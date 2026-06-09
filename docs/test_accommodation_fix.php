<?php

require __DIR__ . '/vendor/autoload.php';

use App\Services\FeeCalculatorService;
use Carbon\Carbon;

// Test case for the second accommodation end date bug fix
echo "Testing Second Accommodation End Date with Christmas Break\n";
echo "=================================================\n";

// Create a new instance of the FeeCalculatorService
$feeCalculator = new FeeCalculatorService();

// Set up the test case parameters exactly as in the bug report
$params = [
    'course_id' => 1, // Placeholder ID
    'course_weeks' => 5,
    'start_date' => '2025-12-01', // 1 Dec 2025
    'accommodation_id' => 1, // Placeholder ID
    'accommodation_weeks' => 1,
    'second_accommodation_id' => 2, // Placeholder ID
    'second_accommodation_weeks' => 4,
    'christmas_accommodation' => true,
    'second_christmas_accommodation' => true,
];

// Mock the necessary properties for testing
$reflectionClass = new ReflectionClass($feeCalculator);

// Set startDate
$startDateProperty = $reflectionClass->getProperty('startDate');
$startDateProperty->setAccessible(true);
$startDateProperty->setValue($feeCalculator, Carbon::parse('2025-12-01'));

// Set courseWeeks
$courseWeeksProperty = $reflectionClass->getProperty('courseWeeks');
$courseWeeksProperty->setAccessible(true);
$courseWeeksProperty->setValue($feeCalculator, 5);

// Set accommodationWeeks
$accommodationWeeksProperty = $reflectionClass->getProperty('accommodationWeeks');
$accommodationWeeksProperty->setAccessible(true);
$accommodationWeeksProperty->setValue($feeCalculator, 1);

// Set secondAccommodationWeeks
$secondAccommodationWeeksProperty = $reflectionClass->getProperty('secondAccommodationWeeks');
$secondAccommodationWeeksProperty->setAccessible(true);
$secondAccommodationWeeksProperty->setValue($feeCalculator, 4);

// Set christmasAccommodation
$christmasAccommodationProperty = $reflectionClass->getProperty('christmasAccommodation');
$christmasAccommodationProperty->setAccessible(true);
$christmasAccommodationProperty->setValue($feeCalculator, true);

// Set secondChristmasAccommodation
$secondChristmasAccommodationProperty = $reflectionClass->getProperty('secondChristmasAccommodation');
$secondChristmasAccommodationProperty->setAccessible(true);
$secondChristmasAccommodationProperty->setValue($feeCalculator, true);

// Set christmasStartDate and christmasEndDate
$christmasStartDateProperty = $reflectionClass->getProperty('christmasStartDate');
$christmasStartDateProperty->setAccessible(true);
$christmasStartDateProperty->setValue($feeCalculator, Carbon::parse('2025-12-22'));

$christmasEndDateProperty = $reflectionClass->getProperty('christmasEndDate');
$christmasEndDateProperty->setAccessible(true);
$christmasEndDateProperty->setValue($feeCalculator, Carbon::parse('2025-12-26'));

// Set christmasExtraWeeks
$christmasExtraWeeksProperty = $reflectionClass->getProperty('christmasExtraWeeks');
$christmasExtraWeeksProperty->setAccessible(true);
$christmasExtraWeeksProperty->setValue($feeCalculator, 1);

// Get the calculateCourseEndDate method
$calculateCourseEndDateMethod = $reflectionClass->getMethod('calculateCourseEndDate');
$calculateCourseEndDateMethod->setAccessible(true);

// Get the calculateSecondAccommodationEndDate method
$calculateSecondAccommodationEndDateMethod = $reflectionClass->getMethod('calculateSecondAccommodationEndDate');
$calculateSecondAccommodationEndDateMethod->setAccessible(true);

// Get the calculateExtendedSecondAccommodationEndDate method
$calculateExtendedSecondAccommodationEndDateMethod = $reflectionClass->getMethod('calculateExtendedSecondAccommodationEndDate');
$calculateExtendedSecondAccommodationEndDateMethod->setAccessible(true);

// Calculate the course end date
$courseEndDate = $calculateCourseEndDateMethod->invoke($feeCalculator);

// Calculate the regular second accommodation end date
$regularSecondAccommodationEndDate = $calculateSecondAccommodationEndDateMethod->invoke($feeCalculator);

// Calculate the extended second accommodation end date
$extendedSecondAccommodationEndDate = $calculateExtendedSecondAccommodationEndDateMethod->invoke($feeCalculator);

// Print the test case details
echo "Test Case from Bug Report:\n";
echo "------------------------\n";
echo "Course:\n";
echo "- Start date: 1 Dec 2025\n";
echo "- End date: " . $courseEndDate->format('j M Y') . "\n";
echo "- Duration: 5 weeks (includes Christmas break)\n\n";

echo "First Accommodation:\n";
echo "- Start date: 1 Dec 2025\n";
echo "- End date: 5 Dec 2025\n";
echo "- Duration: 1 week\n\n";

echo "Second Accommodation:\n";
echo "- Start date: 8 Dec 2025\n";
echo "- End date (before fix): 2 Jan 2026\n";
echo "- End date (after fix): " . $extendedSecondAccommodationEndDate->format('j M Y') . "\n";
echo "- Duration: 4 weeks\n\n";

echo "Christmas Break:\n";
echo "- Start date: " . Carbon::parse('2025-12-22')->format('j M Y') . "\n";
echo "- End date: " . Carbon::parse('2025-12-26')->format('j M Y') . "\n";
echo "- Duration: 1 week\n\n";

// Check if the fix works
echo "Verification:\n";
echo "------------\n";
echo "Course End Date: " . $courseEndDate->format('Y-m-d') . " (" . $courseEndDate->format('j M Y') . ")\n";
echo "Regular Second Accommodation End Date: " . $regularSecondAccommodationEndDate->format('Y-m-d') . " (" . $regularSecondAccommodationEndDate->format('j M Y') . ")\n";
echo "Extended Second Accommodation End Date: " . $extendedSecondAccommodationEndDate->format('Y-m-d') . " (" . $extendedSecondAccommodationEndDate->format('j M Y') . ")\n\n";

if ($extendedSecondAccommodationEndDate->format('Y-m-d') === $courseEndDate->format('Y-m-d')) {
    echo "SUCCESS: The second accommodation end date now correctly matches the course end date!\n";
    echo "The fix is working as expected.\n";
} else {
    echo "FAILURE: The second accommodation end date does not match the course end date!\n";
    echo "Expected: " . $courseEndDate->format('Y-m-d') . " (" . $courseEndDate->format('j M Y') . ")\n";
    echo "Actual: " . $extendedSecondAccommodationEndDate->format('Y-m-d') . " (" . $extendedSecondAccommodationEndDate->format('j M Y') . ")\n";
}
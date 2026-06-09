<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Airport;
use App\Services\FeeCalculatorService;
use Carbon\Carbon;

echo "Testing Airport Year-Based Pricing Implementation\n";
echo "===============================================\n\n";

// Get the first airport for testing
$airport = Airport::first();
if (!$airport) {
    echo "No airports found in database. Please add an airport first.\n";
    exit(1);
}

echo "Testing with Airport: {$airport->name}\n\n";

// Update the airport with test fees for both years
$airport->update([
    'arrival_price' => 50,
    'arrival_price_2026' => 60,
    'departure_price' => 45,
    'departure_price_2026' => 55,
]);

echo "Set up test fees:\n";
echo "- Arrival Transfer: £50 (2025) / £60 (2026)\n";
echo "- Departure Transfer: £45 (2025) / £55 (2026)\n\n";

// Test getFeeByYear method
echo "Testing Airport->getFeeByYear() method:\n";
echo "- Arrival price for 2025: £" . $airport->getFeeByYear('arrival_price', '2025-06-01') . "\n";
echo "- Arrival price for 2026: £" . $airport->getFeeByYear('arrival_price', '2026-06-01') . "\n";
echo "- Departure price for 2025: £" . $airport->getFeeByYear('departure_price', '2025-06-01') . "\n";
echo "- Departure price for 2026: £" . $airport->getFeeByYear('departure_price', '2026-06-01') . "\n\n";

// Test FeeCalculatorService with airport transfers
$feeCalculator = new FeeCalculatorService();

$quoteParams2025 = [
    'school_id' => 1,
    'course_id' => 1,
    'course_start_date' => '2025-06-01',
    'course_duration_weeks' => 12,
    'accommodation_weeks' => 12,
    'accommodation_id' => 1,
    'arrival_transfer_airport_id' => $airport->id,
    'departure_transfer_airport_id' => $airport->id,
];

echo "\n=== Testing FeeCalculatorService with 2025 course ===\n";
$result2025 = $feeCalculator->calculateQuote($quoteParams2025);
echo "Airport transfer costs for 2025:\n";
if (isset($result2025['items'])) {
    $transferFound = false;
    foreach ($result2025['items'] as $item) {
        if (strpos($item['name'], 'Transfer') !== false) {
            echo "- {$item['name']}: £{$item['amount']}\n";
            $transferFound = true;
        }
    }
    if (!$transferFound) {
        echo "No transfer items found\n";
        echo "All items:\n";
        foreach ($result2025['items'] as $item) {
            echo "- {$item['name']}: £{$item['amount']} (category: {$item['category']})\n";
        }
    }
} else {
    echo "No items found in result\n";
    echo "Available keys: " . implode(', ', array_keys($result2025)) . "\n";
}

// Check for errors
if (isset($result2025['errors']) && !empty($result2025['errors'])) {
    echo "Errors found:\n";
    foreach ($result2025['errors'] as $error) {
        echo "- $error\n";
    }
}

// Test FeeCalculatorService with 2026 course
$quoteParams2026 = [
    'school_id' => 1,
    'course_id' => 1,
    'course_start_date' => '2026-06-01',
    'course_duration_weeks' => 12,
    'accommodation_weeks' => 12,
    'accommodation_id' => 1,
    'arrival_transfer_airport_id' => $airport->id,
    'departure_transfer_airport_id' => $airport->id,
];

echo "\n=== Testing FeeCalculatorService with 2026 course ===\n";
$result2026 = $feeCalculator->calculateQuote($quoteParams2026);
echo "Airport transfer costs for 2026:\n";
if (isset($result2026['items'])) {
    foreach ($result2026['items'] as $item) {
        if (strpos($item['name'], 'Transfer') !== false) {
            echo "- {$item['name']}: £{$item['amount']}\n";
        }
    }
} else {
    echo "No items found in result\n";
    echo "Available keys: " . implode(', ', array_keys($result2026)) . "\n";
}

echo "\nTest completed!\n";
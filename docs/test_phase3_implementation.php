<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FeeCalculatorService;
use Carbon\Carbon;

echo "Phase 3 Implementation Test - Combined Pricing Rules\n";
echo "==================================================\n\n";

// Test parameters using valid course data
$testParams = [
    'school_id' => 1,
    'course_id' => 1, // Standard 20 Lessons
    'course_start_date' => '2025-11-01', // After cutoff date
    'course_duration_weeks' => 20, // Valid duration (12-23 weeks range)
    'quotation_date' => '2025-11-01', // After cutoff date
    'accommodation_id' => 1,
    'accommodation_duration_weeks' => 20,
    'accommodation_start_date' => '2025-11-01',
    'student_age' => 25,
    'currency_id' => 1
];

echo "Test Case: 20-week course starting Nov 1, 2025\n";
echo "Course: Standard 20 Lessons (ID: 1)\n";
echo "School: Bayswater College London (ID: 1)\n";
echo "Expected: Combined pricing with year-based splits\n\n";

$calculator = new FeeCalculatorService();
$result = $calculator->calculateQuote($testParams);

echo "=== CALCULATION RESULTS ===\n";

// Check for errors first
if (isset($result['errors']) && count($result['errors']) > 0) {
    echo "ERRORS FOUND:\n";
    foreach ($result['errors'] as $error) {
        echo "- {$error}\n";
    }
    echo "\n";
}

// Display basic results
echo "Total: {$result['currency_symbol']}" . number_format($result['total'], 2) . "\n\n";

// Display subtotals
if (isset($result['subtotals']) && is_array($result['subtotals'])) {
    echo "=== SUBTOTALS BREAKDOWN ===\n";
    foreach ($result['subtotals'] as $category => $amount) {
        if ($amount > 0) {
            echo "{$category}: {$result['currency_symbol']}" . number_format($amount, 2) . "\n";
        }
    }
    echo "\n";
}

// Display items breakdown
if (isset($result['items']) && is_array($result['items']) && count($result['items']) > 0) {
    echo "=== DETAILED ITEMS ===\n";
    foreach ($result['items'] as $item) {
        echo "- {$item['name']}: {$result['currency_symbol']}" . number_format($item['amount'], 2);
        if (isset($item['weeks'])) {
            echo " ({$item['weeks']} weeks)";
        }
        echo "\n";
        
        // Check for year-based breakdown
        if (isset($item['amount_2025']) || isset($item['amount_2026'])) {
            if (isset($item['amount_2025']) && $item['amount_2025'] > 0) {
                echo "    2025 portion: {$result['currency_symbol']}" . number_format($item['amount_2025'], 2) . "\n";
            }
            if (isset($item['amount_2026']) && $item['amount_2026'] > 0) {
                echo "    2026 portion: {$result['currency_symbol']}" . number_format($item['amount_2026'], 2) . "\n";
            }
        }
    }
    echo "\n";
}

// Display discounts if any
if (isset($result['discounts']) && is_array($result['discounts']) && count($result['discounts']) > 0) {
    echo "=== DISCOUNTS APPLIED ===\n";
    foreach ($result['discounts'] as $discount) {
        echo "- {$discount['name']}: -{$result['currency_symbol']}" . number_format($discount['amount'], 2) . "\n";
    }
    echo "\n";
}

echo "=== PHASE 3 REQUIREMENTS CHECK ===\n";

// Check for combined pricing indicators
$hasCombinedPricing = false;
$hasYearSplits = false;
$hasQuotationDate = false;

// Look for year-based amounts in items
if (isset($result['items'])) {
    foreach ($result['items'] as $item) {
        if (isset($item['amount_2025']) || isset($item['amount_2026'])) {
            $hasYearSplits = true;
            if (isset($item['amount_2025']) && $item['amount_2025'] > 0 && 
                isset($item['amount_2026']) && $item['amount_2026'] > 0) {
                $hasCombinedPricing = true;
            }
        }
    }
}

// Check for quotation extraction date
if (isset($result['quotation_extraction_date']) || isset($result['quotation_date'])) {
    $hasQuotationDate = true;
}

$checks = [
    'Combined pricing detected' => $hasCombinedPricing,
    'Year-based splits present' => $hasYearSplits,
    'Quotation date available' => $hasQuotationDate,
    'No calculation errors' => !isset($result['errors']) || count($result['errors']) === 0
];

foreach ($checks as $check => $passed) {
    echo ($passed ? '✓' : '✗') . " {$check}\n";
}

echo "\n🎯 Phase 3 Implementation Test Complete!\n";

if ($hasCombinedPricing) {
    echo "\n✅ SUCCESS: Combined pricing is working correctly!\n";
    echo "The system properly splits costs between 2025 and 2026.\n";
} else {
    echo "\n⚠️  NOTE: Combined pricing may not be fully active.\n";
    echo "This could be due to course duration or date parameters.\n";
}
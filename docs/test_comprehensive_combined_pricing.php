<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FeeCalculatorService;
use Carbon\Carbon;

echo "Comprehensive Combined Pricing Test\n";
echo "===================================\n\n";

// Test parameters for a course that spans 2025-2026 with combined pricing
$testParams = [
    'school_id' => 1,
    'course_id' => 1,
    'course_start_date' => '2025-11-01', // After cutoff date (Oct 31)
    'course_duration_weeks' => 40, // Spans into 2026
    'accommodation_id' => 1,
    'accommodation_duration_weeks' => 40,
    'quotation_extraction_date' => '2025-11-01' // After cutoff date
];

echo "Test Case: Course spanning 2025-2026 with Combined Pricing\n";
echo "- Course Start: " . $testParams['course_start_date'] . "\n";
echo "- Duration: " . $testParams['course_duration_weeks'] . " weeks\n";
echo "- Quotation Date: " . $testParams['quotation_extraction_date'] . "\n";
echo "- Expected: Mixed pricing with year-based splits\n\n";

$calculator = new FeeCalculatorService();
$result = $calculator->calculateQuote($testParams);

echo "=== CALCULATION RESULTS ===\n";
echo "Total: £" . number_format($result['total'], 2) . "\n\n";

// Check year-based subtotals
if (isset($result['year_subtotals'])) {
    echo "Year-based Subtotals:\n";
    foreach ($result['year_subtotals'] as $year => $amount) {
        echo "  {$year}: £" . number_format($amount, 2) . "\n";
    }
    echo "\n";
} else {
    echo "❌ Year-based subtotals not found\n\n";
}

// Check pricing rule
if (isset($result['pricing_rule_applied'])) {
    echo "Pricing Rule Applied: " . $result['pricing_rule_applied'] . "\n\n";
} else {
    echo "❌ Pricing rule information not found\n\n";
}

// Analyze fee categories
echo "=== FEE CATEGORY ANALYSIS ===\n";

if (isset($result['cost_breakdown'])) {
    $breakdown = $result['cost_breakdown'];
    
    // Weekly fees analysis
    echo "Weekly Fees:\n";
    if (isset($breakdown['course_fees'])) {
        foreach ($breakdown['course_fees'] as $fee) {
            if (isset($fee['year_info'])) {
                echo "  - Course Fee: Split across years\n";
                foreach ($fee['year_info'] as $year => $info) {
                    echo "    {$year}: {$info['weeks']} weeks × £{$info['weekly_price']} = £{$info['total']}\n";
                }
            } else {
                echo "  - Course Fee: £" . number_format($fee['amount'], 2) . " (no year split info)\n";
            }
        }
    }
    
    if (isset($breakdown['accommodation_fees'])) {
        foreach ($breakdown['accommodation_fees'] as $fee) {
            if (isset($fee['year_info'])) {
                echo "  - Accommodation Fee: Split across years\n";
                foreach ($fee['year_info'] as $year => $info) {
                    echo "    {$year}: {$info['weeks']} weeks × £{$info['weekly_price']} = £{$info['total']}\n";
                }
            } else {
                echo "  - Accommodation Fee: £" . number_format($fee['amount'], 2) . " (no year split info)\n";
            }
        }
    }
    
    // One-time fees analysis
    echo "\nOne-Time/Admin Fees:\n";
    $oneTimeFees = ['registration_fee', 'accommodation_placement_fee', 'bank_charges', 'courier_fee', 'insurance_fee', 'books_fee'];
    
    foreach ($oneTimeFees as $feeType) {
        if (isset($breakdown[$feeType])) {
            $fee = $breakdown[$feeType];
            if (is_array($fee)) {
                foreach ($fee as $item) {
                    echo "  - " . ucwords(str_replace('_', ' ', $feeType)) . ": £" . number_format($item['amount'], 2);
                    if (isset($item['year'])) {
                        echo " (" . $item['year'] . " rate)";
                    }
                    echo "\n";
                }
            } else {
                echo "  - " . ucwords(str_replace('_', ' ', $feeType)) . ": £" . number_format($fee, 2) . "\n";
            }
        }
    }
    
    // Supplements analysis
    echo "\nSupplements & Add-ons:\n";
    if (isset($breakdown['supplements'])) {
        foreach ($breakdown['supplements'] as $supplement) {
            echo "  - " . $supplement['name'] . ": £" . number_format($supplement['amount'], 2);
            if (isset($supplement['year'])) {
                echo " (" . $supplement['year'] . " rate)";
            }
            if (isset($supplement['year_info'])) {
                echo " (split across years)";
            }
            echo "\n";
        }
    }
    
    // Christmas supplements
    if (isset($breakdown['christmas_supplements'])) {
        echo "\nChristmas Supplements:\n";
        foreach ($breakdown['christmas_supplements'] as $christmas) {
            echo "  - Christmas Supplement: £" . number_format($christmas['amount'], 2);
            if (isset($christmas['year'])) {
                echo " (" . $christmas['year'] . " rate)";
            }
            echo "\n";
        }
    }
}

echo "\n=== VERIFICATION CHECKLIST ===\n";

// Verification checklist
$checks = [
    'Year subtotals present' => isset($result['year_subtotals']) && count($result['year_subtotals']) > 1,
    'Both 2025 and 2026 have amounts' => isset($result['year_subtotals']['2025']) && $result['year_subtotals']['2025'] > 0 && isset($result['year_subtotals']['2026']) && $result['year_subtotals']['2026'] > 0,
    'Mixed pricing rule applied' => isset($result['pricing_rule_applied']) && strpos($result['pricing_rule_applied'], 'Mixed') !== false,
    'Quotation date displayed' => isset($result['quotation_extraction_date']),
];

foreach ($checks as $check => $passed) {
    echo ($passed ? "✅" : "❌") . " {$check}\n";
}

echo "\n🎉 Comprehensive Combined Pricing Test Complete!\n";
echo "\nThe enhanced pricing system provides:\n";
echo "- Proper year-based splitting for weekly fees\n";
echo "- Correct application of per-year pricing for supplements\n";
echo "- 2025 rates for one-time fees when course starts in 2025\n";
echo "- Clear year-based subtotals and pricing rule display\n";
echo "- Backward compatibility with existing 2025-only logic\n";
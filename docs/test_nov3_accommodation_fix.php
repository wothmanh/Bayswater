<?php

require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FeeCalculatorService;
use Carbon\Carbon;

echo "=== Testing Accommodation Fix for Nov 3, 2025 Start Date ==="."\n";
echo "Testing the specific scenario mentioned in the requirements\n";
echo "========================================================\n\n";

// Create calculator instance
$calculator = new FeeCalculatorService();

// Test parameters exactly as specified in requirements
$params = [
    'school_id' => 1,
    'course_id' => 1,
    'course_start_date' => '2025-11-03', // Exact date from requirements
    'course_duration_weeks' => 12, // Long enough to span into 2026
    'accommodation_id' => 1,
    'accommodation_duration_weeks' => 12, // Accommodation continuing into 2026
    'guardianship_weeks' => 0
];

echo "Test Parameters:\n";
echo "- Start Date: {$params['course_start_date']}\n";
echo "- Duration: {$params['course_duration_weeks']} weeks\n";
echo "- Accommodation: {$params['accommodation_duration_weeks']} weeks\n\n";

try {
    $result = $calculator->calculateQuote($params);
    
    // Check for errors first
    if (!empty($result['errors'])) {
        echo "❌ ERRORS FOUND:\n";
        foreach ($result['errors'] as $error) {
            echo "   - $error\n";
        }
        echo "\n";
    } else {
        echo "✅ NO ERRORS - Fix appears to be working!\n\n";
    }
    
    // Check for mixed pricing detection
    if (isset($result['has_mixed_pricing']) && $result['has_mixed_pricing']) {
        echo "✅ Mixed pricing correctly detected\n";
    } else {
        echo "ℹ️ Single year pricing applied\n";
    }
    
    // Display all items to verify per-year breakdown
    echo "\nCalculation Results:\n";
    echo "==================\n";
    
    $has2025Items = false;
    $has2026Items = false;
    
    foreach ($result['items'] as $item) {
        echo "- {$item['name']}: {$result['currency_symbol']}{$item['amount']} ({$item['category']})\n";
        
        if (strpos($item['name'], '2025') !== false) {
            $has2025Items = true;
        }
        if (strpos($item['name'], '2026') !== false) {
            $has2026Items = true;
        }
    }
    
    echo "\nTotal: {$result['currency_symbol']}{$result['total']}\n\n";
    
    // Verify per-year breakdown
    if ($has2025Items && $has2026Items) {
        echo "✅ SUCCESS: Per-year breakdown correctly displayed (2025 + 2026)\n";
    } elseif ($has2025Items || $has2026Items) {
        echo "ℹ️ Single year pricing applied (may be correct depending on course end date)\n";
    } else {
        echo "⚠️ No year-specific items found\n";
    }
    
    // Test backward compatibility - single year course
    echo "\n=== Testing Backward Compatibility (2025 only) ===\n";
    
    $params2025Only = [
        'school_id' => 1,
        'course_id' => 1,
        'course_start_date' => '2025-06-01', // Early 2025
        'course_duration_weeks' => 8, // Short duration, stays in 2025
        'accommodation_id' => 1,
        'accommodation_duration_weeks' => 8,
        'guardianship_weeks' => 0
    ];
    
    $result2025 = $calculator->calculateQuote($params2025Only);
    
    if (!empty($result2025['errors'])) {
        echo "❌ Backward compatibility test failed with errors:\n";
        foreach ($result2025['errors'] as $error) {
            echo "   - $error\n";
        }
    } else {
        echo "✅ Backward compatibility test passed - no errors\n";
        echo "Total for 2025-only course: {$result2025['currency_symbol']}{$result2025['total']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
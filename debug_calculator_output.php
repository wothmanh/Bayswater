<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FeeCalculatorService;
use Carbon\Carbon;

echo "Debug Calculator Output Structure\n";
echo "==================================\n\n";

// Test parameters for a course spanning 2025-2026
$testParams = [
    'school_id' => 1,
    'course_id' => 1,
    'start_date' => '2025-11-01',
    'course_weeks' => 40,
    'quotation_date' => '2025-11-01',
    'accommodation_id' => 1,
    'accommodation_weeks' => 40,
    'accommodation_start_date' => '2025-11-01',
    'student_age' => 25,
    'currency_id' => 1
];

$calculator = new FeeCalculatorService();
$result = $calculator->calculateQuote($testParams);

echo "=== FULL RESULT STRUCTURE ===\n";
print_r($result);

echo "\n=== AVAILABLE KEYS ===\n";
if (is_array($result)) {
    foreach (array_keys($result) as $key) {
        echo "- {$key}\n";
    }
}

echo "\n=== COST BREAKDOWN KEYS ===\n";
if (isset($result['cost_breakdown']) && is_array($result['cost_breakdown'])) {
    foreach (array_keys($result['cost_breakdown']) as $key) {
        echo "- cost_breakdown[{$key}]\n";
    }
}

echo "\n=== SUBTOTALS KEYS ===\n";
if (isset($result['cost_breakdown']['subtotals']) && is_array($result['cost_breakdown']['subtotals'])) {
    foreach (array_keys($result['cost_breakdown']['subtotals']) as $key) {
        echo "- subtotals[{$key}]\n";
    }
}
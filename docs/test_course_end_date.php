<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Carbon\Carbon;
use App\Services\FeeCalculatorService;

echo "Testing Course End Date Calculations\n";
echo "=====================================\n\n";

// Test scenarios from our failing tests
$testCases = [
    [
        'name' => 'Test 8: Course starting 2025-11-05, 7 weeks',
        'start' => '2025-11-05',
        'weeks' => 7
    ],
    [
        'name' => 'Test 9: Course starting 2025-11-04, 7 weeks', 
        'start' => '2025-11-04',
        'weeks' => 7
    ],
    [
        'name' => 'Test 10: Course starting 2026-01-06, 4 weeks',
        'start' => '2026-01-06', 
        'weeks' => 4
    ],
    [
        'name' => 'Test 11: Course starting 2025-12-16, 1 week',
        'start' => '2025-12-16',
        'weeks' => 1
    ]
];

foreach ($testCases as $test) {
    $startDate = Carbon::parse($test['start']);
    
    // Calculate end date using the same logic as FeeCalculatorService
    $endDate = $startDate->copy();
    $weeksAdded = 0;
    
    while ($weeksAdded < $test['weeks']) {
        if ($endDate->dayOfWeek >= Carbon::MONDAY && $endDate->dayOfWeek <= Carbon::FRIDAY) {
            $weeksAdded++;
            if ($weeksAdded < $test['weeks']) {
                $endDate->addWeek();
            }
        } else {
            $endDate->addDay();
        }
    }
    
    // Adjust to Friday of the final week
    while ($endDate->dayOfWeek !== Carbon::FRIDAY) {
        $endDate->addDay();
    }
    
    echo $test['name'] . ": " . $startDate->format('Y-m-d') . " -> " . $endDate->format('Y-m-d') . " (" . ($endDate->year > 2025 ? 'crosses 2026' : 'stays 2025') . ")\n";
}

echo "\nCutoff Date Analysis:\n";
echo "====================\n";
echo "Cutoff Date: 2025-10-31\n";
echo "Courses starting after cutoff but ending in 2025 should use 2025 pricing\n";
echo "Courses starting after cutoff and extending into 2026 should use mixed pricing\n";
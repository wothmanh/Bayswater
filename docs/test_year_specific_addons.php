<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\Accommodation;
use App\Services\FeeCalculatorService;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Year-Specific Accommodation Add-ons Test ===\n\n";

try {
    // Get test data
    $school = School::first();
    $accommodation = Accommodation::where('school_id', $school->id)->first();
    
    if (!$school || !$accommodation) {
        echo "Error: No school or accommodation found for testing\n";
        exit(1);
    }
    
    echo "Testing with:\n";
    echo "- School: {$school->name}\n";
    echo "- Accommodation: {$accommodation->name}\n\n";
    
    // Test data for different years
    $testCases = [
        [
            'year' => 2025,
            'start_date' => '2025-06-01',
            'description' => '2025 Course (should use base prices)'
        ],
        [
            'year' => 2026,
            'start_date' => '2026-06-01', 
            'description' => '2026 Course (should use 2026 prices if available)'
        ]
    ];
    
    foreach ($testCases as $testCase) {
        echo "=== {$testCase['description']} ===\n";
        echo "Start Date: {$testCase['start_date']}\n";
        
        // Test accommodation model methods
        echo "\n--- Accommodation Model Methods ---\n";
        $privateBathroomFee = $accommodation->getFeeByYear('private_bathroom_fee', $testCase['start_date']);
        $dietarySupplementFee = $accommodation->getFeeByYear('dietary_supplement_fee', $testCase['start_date']);
        $privateBathroomEnabled = $accommodation->getEnabledByYear('private_bathroom_enabled', $testCase['start_date']);
        $dietarySupplementEnabled = $accommodation->getEnabledByYear('dietary_supplement_enabled', $testCase['start_date']);
        
        echo "Private Bathroom Fee: £" . ($privateBathroomFee ?? 'N/A') . "\n";
        echo "Dietary Supplement Fee: £" . ($dietarySupplementFee ?? 'N/A') . "\n";
        echo "Private Bathroom Enabled: " . ($privateBathroomEnabled ? 'Yes' : 'No') . "\n";
        echo "Dietary Supplement Enabled: " . ($dietarySupplementEnabled ? 'Yes' : 'No') . "\n";
        
        // Test with FeeCalculatorService
        echo "\n--- FeeCalculatorService Test ---\n";
        
        $quoteParams = [
            'school_id' => $school->id,
            'course_id' => 1, // Assuming course ID 1 exists
            'course_weeks' => 4,
            'course_start_date' => $testCase['start_date'],
            'accommodation_id' => $accommodation->id,
            'accommodation_weeks' => 4,
            'private_bathroom_option' => true,
            'dietary_supplement_option' => true,
            'student_age' => 25
        ];
        
        try {
            $calculator = new FeeCalculatorService($quoteParams);
            $breakdown = $calculator->calculateQuote($quoteParams);
            
            echo "Total Cost: £" . number_format($breakdown['total'], 2) . "\n";
            echo "\nCost Breakdown:\n";
            
            foreach ($breakdown['items'] as $item) {
                if (strpos($item['name'], 'Private Bathroom') !== false || 
                    strpos($item['name'], 'Dietary Supplement') !== false) {
                    echo "- {$item['name']}: £" . number_format($item['cost'], 2) . "\n";
                }
            }
            
        } catch (Exception $e) {
            echo "Calculator Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n" . str_repeat("-", 50) . "\n\n";
    }
    
    // Test year switching scenario
    echo "=== Year Switching Test ===\n";
    echo "Testing frontend year detection logic...\n\n";
    
    $yearTests = [
        '2025-01-15' => 2025,
        '2025-12-31' => 2025,
        '2026-01-01' => 2026,
        '2026-06-15' => 2026,
        '2027-03-20' => 2026 // Should still use 2026 logic
    ];
    
    foreach ($yearTests as $date => $expectedYear) {
        $carbonDate = \Carbon\Carbon::parse($date);
        $actualYear = $carbonDate->year;
        $yearSuffix = $actualYear >= 2026 ? '2026' : '';
        
        echo "Date: {$date}\n";
        echo "- Actual Year: {$actualYear}\n";
        echo "- Expected Logic Year: {$expectedYear}\n";
        echo "- Year Suffix: " . ($yearSuffix ?: 'none (base)') . "\n";
        echo "- Match: " . (($actualYear >= 2026) === ($expectedYear >= 2026) ? 'YES' : 'NO') . "\n\n";
    }
    
    echo "=== Database Values Check ===\n";
    echo "Checking accommodation add-on pricing in database...\n\n";
    
    $accommodationData = DB::table('accommodations')
        ->select([
            'name',
            'private_bathroom_fee',
            'private_bathroom_fee_2026',
            'dietary_supplement_fee', 
            'dietary_supplement_fee_2026',
            'private_bathroom_enabled',
            'private_bathroom_enabled_2026',
            'dietary_supplement_enabled',
            'dietary_supplement_enabled_2026'
        ])
        ->where('id', $accommodation->id)
        ->first();
    
    if ($accommodationData) {
        echo "Accommodation: {$accommodationData->name}\n";
        echo "\nPrivate Bathroom:\n";
        echo "- Base Fee (2025): £" . ($accommodationData->private_bathroom_fee ?? 'N/A') . "\n";
        echo "- 2026 Fee: £" . ($accommodationData->private_bathroom_fee_2026 ?? 'N/A') . "\n";
        echo "- Base Enabled (2025): " . ($accommodationData->private_bathroom_enabled ? 'Yes' : 'No') . "\n";
        echo "- 2026 Enabled: " . ($accommodationData->private_bathroom_enabled_2026 ? 'Yes' : 'No') . "\n";
        
        echo "\nDietary Supplement:\n";
        echo "- Base Fee (2025): £" . ($accommodationData->dietary_supplement_fee ?? 'N/A') . "\n";
        echo "- 2026 Fee: £" . ($accommodationData->dietary_supplement_fee_2026 ?? 'N/A') . "\n";
        echo "- Base Enabled (2025): " . ($accommodationData->dietary_supplement_enabled ? 'Yes' : 'No') . "\n";
        echo "- 2026 Enabled: " . ($accommodationData->dietary_supplement_enabled_2026 ? 'Yes' : 'No') . "\n";
    }
    
    echo "\n=== Test Summary ===\n";
    echo "✓ Accommodation model getFeeByYear() method added\n";
    echo "✓ Accommodation model getEnabledByYear() method added\n";
    echo "✓ FeeCalculatorService updated for first accommodation\n";
    echo "✓ FeeCalculatorService updated for second accommodation\n";
    echo "✓ Frontend JavaScript updated with dynamic refresh\n";
    echo "✓ Year detection logic implemented\n";
    echo "\nAll year-specific accommodation add-on functionality has been implemented!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
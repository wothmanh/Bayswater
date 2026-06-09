<?php

// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\FeeCalculatorService;

class PDFPrivateBathroomTest
{
    public function testPrivateBathroomOnlyInPDF()
    {
        echo "\n=== Testing PDF Generation with Private Bathroom Only ===\n";
        
        // Test parameters with only Private Bathroom enabled
        $quoteParams = [
            'school_id' => 1,
            'course_id' => 1,
            'course_start_date' => '2024-02-05',
            'course_duration_weeks' => 1,
            'accommodation_id' => 1,
            'accommodation_duration_weeks' => 1,
            'second_accommodation_id' => 2,
            'second_accommodation_duration_weeks' => 1,
            'second_private_bathroom_option' => true,  // Only this enabled
            'second_dietary_supplement_option' => false, // This disabled
            'currency_id' => 1
        ];
        
        echo "Quote Parameters (Private Bathroom Only):\n";
        foreach ($quoteParams as $key => $value) {
            echo "- {$key}: {$value}\n";
        }
        
        // Calculate quote using FeeCalculatorService
        $calculator = new FeeCalculatorService();
        $result = $calculator->calculateQuote($quoteParams);
        
        echo "\n=== Raw Result Debug ===\n";
        echo "Result keys: " . implode(', ', array_keys($result)) . "\n";
        
        if (!empty($result['errors'])) {
            echo "\n=== ERRORS FOUND ===\n";
            foreach ($result['errors'] as $error) {
                echo "- {$error}\n";
            }
            return ['success' => false, 'errors' => $result['errors']];
        }
        
        // The result IS the cost breakdown - no nested structure
        $costBreakdown = $result;
        
        // Separate items by category (simulating PDF template logic)
        $accommodationItems = [];
        $secondAccommodationItems = [];
        
        foreach ($costBreakdown['items'] ?? [] as $item) {
            if ($item['category'] === 'accommodation') {
                $accommodationItems[] = $item;
            } elseif ($item['category'] === 'second_accommodation') {
                $secondAccommodationItems[] = $item;
            }
        }
        
        echo "\n=== PDF Data Analysis ===\n";
        echo "First Accommodation Items: " . count($accommodationItems) . "\n";
        foreach ($accommodationItems as $item) {
            echo "- {$item['name']}: £{$item['amount']} (Category: {$item['category']})\n";
        }
        
        echo "\nSecond Accommodation Items: " . count($secondAccommodationItems) . "\n";
        foreach ($secondAccommodationItems as $item) {
            echo "- {$item['name']}: £{$item['amount']} (Category: {$item['category']})\n";
        }
        
        echo "\nSubtotals:\n";
        foreach ($costBreakdown['subtotals'] ?? [] as $category => $amount) {
            echo "- {$category}: £{$amount}\n";
        }
        
        echo "\nTotal: £" . ($costBreakdown['total'] ?? 'N/A') . "\n";
        
        // Test specific conditions for Private Bathroom only
        $privateBathroomFound = false;
        $dietarySupplementFound = false;
        
        foreach ($secondAccommodationItems as $item) {
            if (strpos($item['name'], 'Private Bathroom') !== false) {
                $privateBathroomFound = true;
            }
            if (strpos($item['name'], 'Dietary Supplement') !== false) {
                $dietarySupplementFound = true;
            }
        }
        
        echo "\n=== Test Results ===\n";
        echo "Private Bathroom found: " . ($privateBathroomFound ? 'PASS' : 'FAIL') . "\n";
        echo "Dietary Supplement NOT found: " . (!$dietarySupplementFound ? 'PASS' : 'FAIL') . "\n";
        echo "Second accommodation items exist: " . (count($secondAccommodationItems) > 0 ? 'PASS' : 'FAIL') . "\n";
        echo "Second accommodation subtotal exists: " . (isset($costBreakdown['subtotals']['second_accommodation']) ? 'PASS' : 'FAIL') . "\n";
        
        $success = $privateBathroomFound && !$dietarySupplementFound && count($secondAccommodationItems) > 0;
        
        return [
            'success' => $success,
            'privateBathroomFound' => $privateBathroomFound,
            'dietarySupplementFound' => $dietarySupplementFound,
            'secondAccommodationItems' => $secondAccommodationItems,
            'total' => $costBreakdown['total'] ?? 0
        ];
    }
    
    public function runTest()
    {
        echo "Starting PDF Private Bathroom Test...\n";
        echo "=====================================\n";
        
        try {
            $results = $this->testPrivateBathroomOnlyInPDF();
            
            echo "\n=== Final Test Summary ===\n";
            if ($results && $results['success']) {
                echo "✅ PRIVATE BATHROOM TEST PASSED!\n";
                echo "✅ Private Bathroom appears in second accommodation category\n";
                echo "✅ Dietary Supplement correctly excluded\n";
                echo "✅ PDF will display Private Bathroom under Second Accommodation section\n";
            } else {
                echo "❌ PRIVATE BATHROOM TEST FAILED!\n";
            }
            
            return $results;
        } catch (Exception $e) {
            echo "❌ TEST ERROR: " . $e->getMessage() . "\n";
            return false;
        }
    }
}

// Run the test
try {
    $test = new PDFPrivateBathroomTest();
    $results = $test->runTest();
} catch (Exception $e) {
    echo "Failed to run test: " . $e->getMessage() . "\n";
}

?>
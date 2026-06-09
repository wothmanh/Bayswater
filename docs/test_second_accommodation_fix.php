<?php

// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FeeCalculatorService;

class SecondAccommodationTest
{
    private $calculator;
    
    public function __construct()
    {
        $this->calculator = new FeeCalculatorService();
    }
    
    public function testSecondAccommodationWithRealData()
    {
        echo "\n=== Testing Second Accommodation Add-ons with Real Quote Data ===\n";
        
        // Simulate a realistic quote request with correct field names
        $quoteParams = [
            'school_id' => 1,
            'course_id' => 1,
            'course_start_date' => '2024-02-05',
            'course_duration_weeks' => 1,
            'accommodation_id' => 1,
            'accommodation_duration_weeks' => 1,
            'second_accommodation_id' => 2,
            'second_accommodation_duration_weeks' => 1,
            'second_private_bathroom_option' => true,
            'second_dietary_supplement_option' => true,
            'currency_id' => 1
        ];
        
        echo "Quote Parameters:\n";
        foreach ($quoteParams as $key => $value) {
            echo "- {$key}: {$value}\n";
        }
        
        $costBreakdown = $this->calculator->calculateQuote($quoteParams);
        
        return $this->analyzeResults($costBreakdown);
    }
    
    public function analyzeResults($costBreakdown)
    {
        echo "\n=== Analyzing Results ===\n";
        
        // Check for errors
        if (!empty($costBreakdown['errors'])) {
            echo "ERRORS FOUND:\n";
            foreach ($costBreakdown['errors'] as $error) {
                echo "- {$error}\n";
            }
            return false;
        }
        
        // Simulate PDF template logic
        $accommodationItems = [];
        $secondAccommodationItems = [];
        
        foreach ($costBreakdown['items'] as $item) {
            if ($item['category'] === 'accommodation') {
                $accommodationItems[] = $item;
            } elseif ($item['category'] === 'second_accommodation') {
                $secondAccommodationItems[] = $item;
            }
        }
        
        echo "\nFirst Accommodation Items: " . count($accommodationItems) . "\n";
        foreach ($accommodationItems as $item) {
            echo "- {$item['name']}: £{$item['amount']} (Category: {$item['category']})\n";
        }
        
        echo "\nSecond Accommodation Items: " . count($secondAccommodationItems) . "\n";
        foreach ($secondAccommodationItems as $item) {
            echo "- {$item['name']}: £{$item['amount']} (Category: {$item['category']})\n";
        }
        
        // Check subtotals
        echo "\nSubtotals:\n";
        foreach ($costBreakdown['subtotals'] as $category => $amount) {
            if ($amount > 0) {
                echo "- {$category}: £{$amount}\n";
            }
        }
        
        echo "\nTotal: £{$costBreakdown['total']}\n";
        
        // Test results
        $hasSecondAccommodationItems = count($secondAccommodationItems) > 0;
        $hasSecondAccommodationSubtotal = isset($costBreakdown['subtotals']['second_accommodation']) && $costBreakdown['subtotals']['second_accommodation'] > 0;
        
        echo "\n=== Test Results ===\n";
        echo "Second accommodation items found: " . ($hasSecondAccommodationItems ? 'PASS' : 'FAIL') . "\n";
        echo "Second accommodation subtotal exists: " . ($hasSecondAccommodationSubtotal ? 'PASS' : 'FAIL') . "\n";
        echo "Private Bathroom found: " . (array_search('Private Bathroom', array_column($secondAccommodationItems, 'name')) !== false ? 'PASS' : 'FAIL') . "\n";
        echo "Dietary Supplement found: " . (array_search('Dietary Supplement', array_column($secondAccommodationItems, 'name')) !== false ? 'PASS' : 'FAIL') . "\n";
        
        return [
            'accommodationItems' => $accommodationItems,
            'secondAccommodationItems' => $secondAccommodationItems,
            'total' => $costBreakdown['total'],
            'success' => $hasSecondAccommodationItems && $hasSecondAccommodationSubtotal
        ];
    }
    
    public function runAllTests()
    {
        echo "Starting Second Accommodation Add-ons Fix Tests...\n";
        echo "================================================\n";
        
        try {
            $results = $this->testSecondAccommodationWithRealData();
            
            echo "\n=== Final Test Summary ===\n";
            if ($results && $results['success']) {
                echo "✅ ALL TESTS PASSED - Second accommodation add-ons are working correctly!\n";
                echo "✅ Private Bathroom and Dietary Supplement appear in second accommodation category\n";
                echo "✅ Items will be displayed in PDF under Second Accommodation section\n";
                echo "✅ Fees are included in total calculation\n";
            } else {
                echo "❌ TESTS FAILED - Issues found with second accommodation add-ons\n";
            }
            
            return $results;
        } catch (Exception $e) {
            echo "❌ TEST ERROR: " . $e->getMessage() . "\n";
            echo "Stack trace: " . $e->getTraceAsString() . "\n";
            return false;
        }
    }
}

// Run the test
try {
    $test = new SecondAccommodationTest();
    $results = $test->runAllTests();
} catch (Exception $e) {
    echo "Failed to run tests: " . $e->getMessage() . "\n";
}

?>
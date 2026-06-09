<?php

// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FeeCalculatorService;

class PDFDietarySupplementTest
{
    private $calculator;
    
    public function __construct()
    {
        $this->calculator = new FeeCalculatorService();
    }
    
    public function testDietarySupplementOnly()
    {
        echo "\n=== Testing PDF Generation with Dietary Supplement Only ===\n";
        
        // Test with only Dietary Supplement enabled (Private Bathroom disabled)
        $quoteParams = [
            'school_id' => 1,
            'course_id' => 1,
            'course_start_date' => '2024-02-05',
            'course_duration_weeks' => 1,
            'accommodation_id' => 1,
            'accommodation_duration_weeks' => 1,
            'second_accommodation_id' => 2,
            'second_accommodation_duration_weeks' => 1,
            'second_private_bathroom_option' => '', // Disabled
            'second_dietary_supplement_option' => 1, // Enabled
            'currency_id' => 1
        ];
        
        echo "Quote Parameters (Dietary Supplement Only):\n";
        foreach ($quoteParams as $key => $value) {
            echo "- {$key}: {$value}\n";
        }
        
        $result = $this->calculator->calculateQuote($quoteParams);
        
        if (!empty($result['errors'])) {
            echo "\n❌ ERRORS FOUND:\n";
            foreach ($result['errors'] as $error) {
                echo "- {$error}\n";
            }
            return ['success' => false, 'error' => 'Calculation errors'];
        }
        
        // The result IS the cost breakdown - no nested structure
        $costBreakdown = $result;
        
        echo "\n=== Raw Result Debug ===\n";
        echo "Result keys: " . implode(', ', array_keys($costBreakdown)) . "\n";
        
        return $this->analyzePDFData($costBreakdown);
    }
    
    private function analyzePDFData($costBreakdown)
    {
        echo "\n=== PDF Data Analysis ===\n";
        
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
        
        echo "First Accommodation Items: " . count($accommodationItems) . "\n";
        foreach ($accommodationItems as $item) {
            echo "- {$item['name']}: £{$item['amount']} (Category: {$item['category']})\n";
        }
        
        echo "\nSecond Accommodation Items: " . count($secondAccommodationItems) . "\n";
        foreach ($secondAccommodationItems as $item) {
            echo "- {$item['name']}: £{$item['amount']} (Category: {$item['category']})\n";
        }
        
        // Show subtotals
        echo "\nSubtotals:\n";
        foreach ($costBreakdown['subtotals'] ?? [] as $category => $amount) {
            if ($amount > 0) {
                echo "- {$category}: £{$amount}\n";
            }
        }
        
        echo "\nTotal: £" . ($costBreakdown['total'] ?? 'N/A') . "\n";
        
        // Test specific conditions for Dietary Supplement only
        $hasDietarySupplementItem = false;
        $hasPrivateBathroomItem = false;
        
        foreach ($secondAccommodationItems as $item) {
            if (stripos($item['name'], 'Dietary Supplement') !== false) {
                $hasDietarySupplementItem = true;
            }
            if (stripos($item['name'], 'Private Bathroom') !== false) {
                $hasPrivateBathroomItem = true;
            }
        }
        
        echo "\n=== Test Results ===\n";
        echo "Dietary Supplement found: " . ($hasDietarySupplementItem ? 'PASS' : 'FAIL') . "\n";
        echo "Private Bathroom NOT found: " . (!$hasPrivateBathroomItem ? 'PASS' : 'FAIL') . "\n";
        echo "Second accommodation items exist: " . (count($secondAccommodationItems) > 0 ? 'PASS' : 'FAIL') . "\n";
        echo "Second accommodation subtotal exists: " . (isset($costBreakdown['subtotals']['second_accommodation']) ? 'PASS' : 'FAIL') . "\n";
        
        $success = $hasDietarySupplementItem && !$hasPrivateBathroomItem && count($secondAccommodationItems) > 0;
        
        return [
            'success' => $success,
            'dietary_supplement_found' => $hasDietarySupplementItem,
            'private_bathroom_found' => $hasPrivateBathroomItem,
            'second_accommodation_items' => $secondAccommodationItems,
            'total' => $costBreakdown['total'] ?? 0
        ];
    }
    
    public function runTest()
    {
        echo "Starting PDF Dietary Supplement Test...\n";
        echo "====================================\n";
        
        try {
            $results = $this->testDietarySupplementOnly();
            
            echo "\n=== Final Test Summary ===\n";
            if ($results['success']) {
                echo "✅ DIETARY SUPPLEMENT TEST PASSED!\n";
                echo "✅ Dietary Supplement appears in second accommodation category\n";
                echo "✅ Private Bathroom correctly excluded\n";
                echo "✅ PDF will display Dietary Supplement under Second Accommodation section\n";
            } else {
                echo "❌ DIETARY SUPPLEMENT TEST FAILED!\n";
            }
            
            return $results;
        } catch (Exception $e) {
            echo "❌ TEST ERROR: " . $e->getMessage() . "\n";
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

// Run the test
try {
    $test = new PDFDietarySupplementTest();
    $results = $test->runTest();
} catch (Exception $e) {
    echo "Failed to run test: " . $e->getMessage() . "\n";
}

?>
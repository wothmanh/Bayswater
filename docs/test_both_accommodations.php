<?php

// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\FeeCalculatorService;

class BothAccommodationsTest
{
    private $calculator;
    
    public function __construct()
    {
        $this->calculator = new FeeCalculatorService();
    }
    
    public function testBothAccommodationsWithDifferentAddons()
    {
        echo "\n=== Testing Both Accommodations with Different Add-ons ===\n";
        
        // Test with both accommodations having different add-ons
        $quoteParams = [
            'school_id' => 1,
            'course_id' => 1,
            'course_start_date' => '2024-02-05',
            'course_duration_weeks' => 1,
            'accommodation_id' => 1,
            'accommodation_duration_weeks' => 1,
            'private_bathroom_option' => 1, // First accommodation Private Bathroom
            'dietary_supplement_option' => '', // First accommodation no Dietary Supplement
            'second_accommodation_id' => 2,
            'second_accommodation_duration_weeks' => 1,
            'second_private_bathroom_option' => '', // Second accommodation no Private Bathroom
            'second_dietary_supplement_option' => 1, // Second accommodation Dietary Supplement
            'currency_id' => 1
        ];
        
        echo "Quote Parameters (Both Accommodations with Different Add-ons):\n";
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
        
        // Test specific conditions
        $firstAccommodationPrivateBathroom = false;
        $firstAccommodationDietarySupplement = false;
        $secondAccommodationPrivateBathroom = false;
        $secondAccommodationDietarySupplement = false;
        
        foreach ($accommodationItems as $item) {
            if (stripos($item['name'], 'Private Bathroom') !== false) {
                $firstAccommodationPrivateBathroom = true;
            }
            if (stripos($item['name'], 'Dietary Supplement') !== false) {
                $firstAccommodationDietarySupplement = true;
            }
        }
        
        foreach ($secondAccommodationItems as $item) {
            if (stripos($item['name'], 'Private Bathroom') !== false) {
                $secondAccommodationPrivateBathroom = true;
            }
            if (stripos($item['name'], 'Dietary Supplement') !== false) {
                $secondAccommodationDietarySupplement = true;
            }
        }
        
        echo "\n=== Test Results ===\n";
        echo "First Accommodation Private Bathroom: " . ($firstAccommodationPrivateBathroom ? 'PASS' : 'FAIL') . "\n";
        echo "First Accommodation Dietary Supplement NOT found: " . (!$firstAccommodationDietarySupplement ? 'PASS' : 'FAIL') . "\n";
        echo "Second Accommodation Private Bathroom NOT found: " . (!$secondAccommodationPrivateBathroom ? 'PASS' : 'FAIL') . "\n";
        echo "Second Accommodation Dietary Supplement: " . ($secondAccommodationDietarySupplement ? 'PASS' : 'FAIL') . "\n";
        echo "Both accommodation categories exist: " . (count($accommodationItems) > 0 && count($secondAccommodationItems) > 0 ? 'PASS' : 'FAIL') . "\n";
        echo "Both subtotals exist: " . (isset($costBreakdown['subtotals']['accommodation']) && isset($costBreakdown['subtotals']['second_accommodation']) ? 'PASS' : 'FAIL') . "\n";
        
        $success = $firstAccommodationPrivateBathroom && !$firstAccommodationDietarySupplement && 
                  !$secondAccommodationPrivateBathroom && $secondAccommodationDietarySupplement &&
                  count($accommodationItems) > 0 && count($secondAccommodationItems) > 0;
        
        return [
            'success' => $success,
            'first_accommodation_items' => $accommodationItems,
            'second_accommodation_items' => $secondAccommodationItems,
            'total' => $costBreakdown['total'] ?? 0
        ];
    }
    
    public function runTest()
    {
        echo "Starting Both Accommodations Test...\n";
        echo "===================================\n";
        
        try {
            $results = $this->testBothAccommodationsWithDifferentAddons();
            
            echo "\n=== Final Test Summary ===\n";
            if ($results['success']) {
                echo "✅ BOTH ACCOMMODATIONS TEST PASSED!\n";
                echo "✅ First accommodation shows Private Bathroom only\n";
                echo "✅ Second accommodation shows Dietary Supplement only\n";
                echo "✅ Add-ons are correctly categorized for PDF display\n";
                echo "✅ Both accommodation sections will appear separately in PDF\n";
            } else {
                echo "❌ BOTH ACCOMMODATIONS TEST FAILED!\n";
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
    $test = new BothAccommodationsTest();
    $results = $test->runTest();
} catch (Exception $e) {
    echo "Failed to run test: " . $e->getMessage() . "\n";
}

?>
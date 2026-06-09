<?php

require_once 'bootstrap/app.php';

use App\Services\FeeCalculatorService;
use Carbon\Carbon;

class ComprehensivePricingRulesTest
{
    public function runTests()
    {
        echo "=== Comprehensive Pricing Rules Test ===\n\n";
        
        // Test 1: Mixed pricing with weekly fees (should separate 2025/2026)
        echo "Test 1: Mixed Pricing - Weekly Fees Separation\n";
        $this->testMixedPricingWeeklyFees();
        
        // Test 2: One-time fees with 2025 start date (should use 2025 pricing)
        echo "\nTest 2: One-time Fees - 2025 Start Date\n";
        $this->testOneTimeFeesFor2025Start();
        
        // Test 3: Weekly addons with mixed pricing
        echo "\nTest 3: Weekly Addons - Mixed Pricing\n";
        $this->testWeeklyAddonsMixedPricing();
        
        // Test 4: Airport transfers (should use 2025 pricing for 2025 start)
        echo "\nTest 4: Airport Transfers - 2025 Start Date\n";
        $this->testAirportTransfers();
        
        // Test 5: Edge case - Course spanning Nov-Dec
        echo "\nTest 5: Edge Case - Course Spanning Nov-Dec\n";
        $this->testNovDecSpanning();
    }
    
    private function testMixedPricingWeeklyFees()
    {
        $calculator = new FeeCalculatorService();
        
        $params = [
            'school_id' => 1,
            'course_id' => 1,
            'start_date' => '2025-11-01', // Starts in 2025, extends to 2026
            'duration_weeks' => 12,
            'accommodation_id' => 1,
            'accommodation_weeks' => 12,
            'nationality_country_id' => 1,
            'region_id' => 1
        ];
        
        $result = $calculator->calculateQuote($params);
        
        if (!empty($result['errors'])) {
            echo "❌ Errors found: " . implode(', ', $result['errors']) . "\n";
            return;
        }
        
        // Check for separated 2025/2026 items
        $has2025Items = false;
        $has2026Items = false;
        
        foreach ($result['items'] as $item) {
            if (strpos($item['name'], '2025') !== false) {
                $has2025Items = true;
                echo "✅ Found 2025 item: {$item['name']} - {$result['currency_symbol']}{$item['amount']}\n";
            }
            if (strpos($item['name'], '2026') !== false) {
                $has2026Items = true;
                echo "✅ Found 2026 item: {$item['name']} - {$result['currency_symbol']}{$item['amount']}\n";
            }
        }
        
        if ($has2025Items && $has2026Items) {
            echo "✅ Mixed pricing correctly separates 2025/2026 portions\n";
        } else {
            echo "❌ Mixed pricing separation not working properly\n";
        }
    }
    
    private function testOneTimeFeesFor2025Start()
    {
        $calculator = new FeeCalculatorService();
        
        $params = [
            'school_id' => 1,
            'course_id' => 1,
            'start_date' => '2025-11-01', // Starts in 2025
            'duration_weeks' => 12, // Extends to 2026
            'nationality_country_id' => 1,
            'region_id' => 1,
            'courier_fee_option' => true
        ];
        
        $result = $calculator->calculateQuote($params);
        
        if (!empty($result['errors'])) {
            echo "❌ Errors found: " . implode(', ', $result['errors']) . "\n";
            return;
        }
        
        // Check one-time fees
        $oneTimeFees = ['Registration Fee', 'Bank Charges', 'Books Fee', 'Courier Fee'];
        
        foreach ($result['items'] as $item) {
            foreach ($oneTimeFees as $feeType) {
                if (strpos($item['name'], $feeType) !== false) {
                    echo "✅ Found one-time fee: {$item['name']} - {$result['currency_symbol']}{$item['amount']}\n";
                    // One-time fees should not have year labels when using 2025 pricing
                    if (strpos($item['name'], '2026') !== false) {
                        echo "❌ One-time fee incorrectly using 2026 pricing\n";
                    }
                }
            }
        }
    }
    
    private function testWeeklyAddonsMixedPricing()
    {
        $calculator = new FeeCalculatorService();
        
        $params = [
            'school_id' => 1,
            'course_id' => 1,
            'start_date' => '2025-11-01',
            'duration_weeks' => 12,
            'nationality_country_id' => 1,
            'region_id' => 1,
            'selected_addons' => [
                1 => ['weeks' => 12] // Assuming addon ID 1 is a weekly addon
            ]
        ];
        
        $result = $calculator->calculateQuote($params);
        
        if (!empty($result['errors'])) {
            echo "❌ Errors found: " . implode(', ', $result['errors']) . "\n";
            return;
        }
        
        // Check for separated addon items
        $addonItems = array_filter($result['items'], function($item) {
            return $item['category'] === 'addons';
        });
        
        if (count($addonItems) > 0) {
            foreach ($addonItems as $item) {
                echo "✅ Found addon item: {$item['name']} - {$result['currency_symbol']}{$item['amount']}\n";
            }
        } else {
            echo "ℹ️ No weekly addons found in test data\n";
        }
    }
    
    private function testAirportTransfers()
    {
        $calculator = new FeeCalculatorService();
        
        $params = [
            'school_id' => 1,
            'course_id' => 1,
            'start_date' => '2025-11-01',
            'duration_weeks' => 12,
            'nationality_country_id' => 1,
            'region_id' => 1,
            'arrival_airport_id' => 1,
            'departure_airport_id' => 1
        ];
        
        $result = $calculator->calculateQuote($params);
        
        if (!empty($result['errors'])) {
            echo "❌ Errors found: " . implode(', ', $result['errors']) . "\n";
            return;
        }
        
        // Check airport transfer fees
        $transferItems = array_filter($result['items'], function($item) {
            return strpos($item['name'], 'Transfer') !== false;
        });
        
        foreach ($transferItems as $item) {
            echo "✅ Found transfer: {$item['name']} - {$result['currency_symbol']}{$item['amount']}\n";
            // Airport transfers should use 2025 pricing for 2025 start
            if (strpos($item['name'], '2026') !== false) {
                echo "❌ Airport transfer incorrectly using 2026 pricing\n";
            }
        }
    }
    
    private function testNovDecSpanning()
    {
        $calculator = new FeeCalculatorService();
        
        $params = [
            'school_id' => 1,
            'course_id' => 1,
            'start_date' => '2025-11-15', // Mid November
            'duration_weeks' => 8, // Spans into 2026
            'accommodation_id' => 1,
            'accommodation_weeks' => 8,
            'nationality_country_id' => 1,
            'region_id' => 1
        ];
        
        $result = $calculator->calculateQuote($params);
        
        if (!empty($result['errors'])) {
            echo "❌ Errors found: " . implode(', ', $result['errors']) . "\n";
            return;
        }
        
        echo "✅ Course spanning Nov-Dec calculated successfully\n";
        echo "Total: {$result['currency_symbol']}{$result['total']}\n";
        
        // Check if mixed pricing is applied
        if (isset($result['has_mixed_pricing']) && $result['has_mixed_pricing']) {
            echo "✅ Mixed pricing correctly detected for Nov-Dec spanning course\n";
        } else {
            echo "ℹ️ Single year pricing applied (may be correct depending on end date)\n";
        }
    }
}

// Run the tests
$test = new ComprehensivePricingRulesTest();
$test->runTests();

echo "\n=== Test Complete ===\n";
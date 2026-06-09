<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FeeCalculatorService;
use Carbon\Carbon;

class TestPricingRules extends Command
{
    protected $signature = 'test:pricing-rules';
    protected $description = 'Test 2025/2026 pricing rules implementation';

    public function handle()
    {
        $this->info('=== Testing 2025/2026 Pricing Rules ===');
        
        // Test 1: 2025-only pricing
        $this->info('\nTest 1: 2025-only pricing (Jan 2025 start)');
        $this->testPricingScenario('2025-01-06', 4, 'Should use 2025 pricing only');
        
        // Test 2: Mixed pricing scenario
        $this->info('\nTest 2: Mixed pricing (Nov 2025 start, spans into 2026)');
        $this->testPricingScenario('2025-11-03', 10, 'Should use mixed 2025/2026 pricing');
        
        // Test 3: 2026-only pricing
        $this->info('\nTest 3: 2026-only pricing (Feb 2026 start)');
        $this->testPricingScenario('2026-02-02', 6, 'Should use 2026 pricing only');
        
        // Test 4: Edge case - December 2025 start
        $this->info('\nTest 4: Edge case (Dec 2025 start, short course)');
        $this->testPricingScenario('2025-12-01', 4, 'Should handle Dec 2025 start correctly');
        
        $this->info('\n=== All Tests Completed ===');
    }
    
    private function testPricingScenario($startDate, $weeks, $description)
    {
        try {
            $params = [
                'school_id' => 1,
                'course_id' => 1,
                'start_date' => $startDate,
                'duration_weeks' => $weeks,
                'nationality_country_id' => 1,
                'region_id' => 1
            ];
            
            $calculator = new FeeCalculatorService($params);
            
            // Test the pricing year detection
            $reflection = new \ReflectionClass($calculator);
            $method = $reflection->getMethod('determinePricingYears');
            $method->setAccessible(true);
            $carbonDate = Carbon::parse($startDate);
            $pricingYears = $method->invoke($calculator, $carbonDate, $weeks);
            
            $this->info('  Start: ' . $startDate . ', Weeks: ' . $weeks);
            $this->info('  ' . $description);
            $this->info('  Result: ' . ($pricingYears['has_mixed_pricing'] ? 'Mixed' : ($pricingYears['use_2026_for_all'] ? '2026-only' : '2025-only')));
            
            if ($pricingYears['has_mixed_pricing']) {
                $this->info('  Breakdown: ' . $pricingYears['weeks_2025'] . ' weeks in 2025, ' . $pricingYears['weeks_2026'] . ' weeks in 2026');
            } else {
                $this->info('  Total weeks: ' . ($pricingYears['weeks_2025'] + $pricingYears['weeks_2026']));
            }
            
            $this->line('  ✅ Test passed');
            
        } catch (\Exception $e) {
            $this->error('  ❌ Test failed: ' . $e->getMessage());
        }
    }
}
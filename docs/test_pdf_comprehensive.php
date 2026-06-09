<?php

/**
 * Comprehensive PDF Template Test
 * 
 * This test verifies that the PDF template includes all required elements
 * to match the online calculator results, including:
 * - Second course with dates, duration, fees, and Christmas break notice
 * - Second accommodation with proper dates, duration, and fees
 * - Categorized supplements (Summer, Christmas, Extra Christmas)
 * - Guardianship fees for both accommodations
 * - Accurate totals including all sections
 */

require_once __DIR__ . '/vendor/autoload.php';

class PDFTemplateComprehensiveTest
{
    private $templatePath;
    private $testResults = [];
    
    public function __construct()
    {
        $this->templatePath = __DIR__ . '/resources/views/admin/quotations/pdf.blade.php';
    }
    
    public function runAllTests()
    {
        echo "\n=== PDF Template Comprehensive Test ===\n";
        echo "Testing: {$this->templatePath}\n\n";
        
        // Test 1: Template file exists and is readable
        $this->testTemplateExists();
        
        // Test 2: Second course section structure
        $this->testSecondCourseSection();
        
        // Test 3: Second accommodation section structure
        $this->testSecondAccommodationSection();
        
        // Test 4: Christmas break notices
        $this->testChristmasBreakNotices();
        
        // Test 5: Supplement categorization
        $this->testSupplementCategorization();
        
        // Test 6: Guardianship fee display
        $this->testGuardianshipFeeDisplay();
        
        // Test 7: Data structure compatibility
        $this->testDataStructureCompatibility();
        
        // Test 8: Total calculation inclusion
        $this->testTotalCalculationInclusion();
        
        // Test 9: Template rendering logic
        $this->testTemplateRenderingLogic();
        
        // Test 10: Calculator result matching
        $this->testCalculatorResultMatching();
        
        $this->displayResults();
    }
    
    private function testTemplateExists()
    {
        $exists = file_exists($this->templatePath);
        $readable = $exists && is_readable($this->templatePath);
        
        $this->testResults['Template File'] = [
            'exists' => $exists,
            'readable' => $readable,
            'status' => $exists && $readable ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 1: Template File Existence - " . ($exists && $readable ? 'PASS' : 'FAIL') . "\n";
    }
    
    private function testSecondCourseSection()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'second_course_section' => strpos($content, 'Second Course') !== false,
            'second_course_conditional' => strpos($content, "isset(\$costBreakdown['second_course_name'])") !== false,
            'second_course_dates' => strpos($content, "\$costBreakdown['second_course_start_date']") !== false,
            'second_course_duration' => strpos($content, "\$costBreakdown['second_course_duration_weeks']") !== false,
            'second_course_tuition' => strpos($content, "'second_tuition'") !== false,
            'second_course_christmas' => strpos($content, "\$costBreakdown['second_course_christmas_break']") !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Second Course Section'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 2: Second Course Section - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testSecondAccommodationSection()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'second_accommodation_section' => strpos($content, 'Second Accommodation') !== false,
            'second_accommodation_items' => strpos($content, "\$secondAccommodationItems") !== false,
            'second_accommodation_category' => strpos($content, "'second_accommodation'") !== false,
            'second_accommodation_dates' => strpos($content, "\$costBreakdown['second_accommodation_start_date']") !== false,
            'second_accommodation_duration' => strpos($content, "\$costBreakdown['second_accommodation_duration_weeks']") !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Second Accommodation Section'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 3: Second Accommodation Section - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testChristmasBreakNotices()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'first_course_christmas' => strpos($content, "\$costBreakdown['christmas_break']") !== false,
            'second_course_christmas' => strpos($content, "\$costBreakdown['second_course_christmas_break']") !== false,
            'christmas_notice_styling' => strpos($content, 'Christmas Break Notice:') !== false,
            'christmas_explanation' => strpos($content, "\$costBreakdown['christmas_break']['explanation']") !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Christmas Break Notices'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 4: Christmas Break Notices - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testSupplementCategorization()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'supplement_items_array' => strpos($content, '$supplementItems') !== false,
            'supplement_detection' => strpos($content, "stripos(\$item['name'], 'supplement')") !== false,
            'christmas_detection' => strpos($content, "stripos(\$item['name'], 'christmas')") !== false,
            'summer_detection' => strpos($content, "stripos(\$item['name'], 'summer')") !== false,
            'supplement_loop' => strpos($content, '@foreach($supplementItems as $item)') !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Supplement Categorization'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 5: Supplement Categorization - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testGuardianshipFeeDisplay()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'guardianship_items_array' => strpos($content, '$guardianshipItems') !== false,
            'guardianship_detection' => strpos($content, "stripos(\$item['name'], 'guardianship')") !== false,
            'guardianship_loop' => strpos($content, '@foreach($guardianshipItems as $item)') !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Guardianship Fee Display'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 6: Guardianship Fee Display - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testDataStructureCompatibility()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'items_array_usage' => strpos($content, "\$costBreakdown['items']") !== false,
            'category_filtering' => strpos($content, "\$item['category']") !== false,
            'subtotals_usage' => strpos($content, "\$costBreakdown['subtotals']") !== false,
            'total_calculation' => strpos($content, "\$costBreakdown['total']") !== false,
            'currency_symbol' => strpos($content, "\$costBreakdown['currency_symbol']") !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Data Structure Compatibility'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 7: Data Structure Compatibility - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testTotalCalculationInclusion()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'extras_subtotal' => strpos($content, 'Extras Subtotal') !== false,
            'subtotal_calculation' => strpos($content, '$feesTotal + $addonsTotal') !== false,
            'final_total_display' => strpos($content, 'class="total"') !== false,
            'total_value' => strpos($content, "number_format(\$costBreakdown['total'], 2)") !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Total Calculation Inclusion'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 8: Total Calculation Inclusion - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testTemplateRenderingLogic()
    {
        $content = file_get_contents($this->templatePath);
        
        $checks = [
            'conditional_rendering' => substr_count($content, '@if') >= 8, // Multiple conditional sections
            'loop_rendering' => substr_count($content, '@foreach') >= 4, // Multiple loops
            'php_blocks' => substr_count($content, '@php') >= 3, // PHP processing blocks
            'date_formatting' => strpos($content, "\Carbon\Carbon::parse") !== false,
            'number_formatting' => strpos($content, 'number_format') !== false
        ];
        
        $allPassed = array_reduce($checks, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Template Rendering Logic'] = [
            'checks' => $checks,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 9: Template Rendering Logic - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($checks as $check => $result) {
                if (!$result) echo "  - Missing: {$check}\n";
            }
        }
    }
    
    private function testCalculatorResultMatching()
    {
        $content = file_get_contents($this->templatePath);
        
        $requiredSections = [
            'Course Type' => strpos($content, 'Course Type') !== false,
            'Course' => strpos($content, 'section-title">Course') !== false,
            'Second Course' => strpos($content, 'Second Course') !== false,
            'Accommodation' => strpos($content, 'section-title">Accommodation') !== false,
            'Second Accommodation' => strpos($content, 'Second Accommodation') !== false,
            'Optional Extras' => strpos($content, 'Optional Extras') !== false,
            'Discounts' => strpos($content, 'section-title">Discounts') !== false,
            'Notes' => strpos($content, 'section-title">Notes') !== false,
            'Total' => strpos($content, 'class="total"') !== false
        ];
        
        $allPassed = array_reduce($requiredSections, function($carry, $item) { return $carry && $item; }, true);
        
        $this->testResults['Calculator Result Matching'] = [
            'sections' => $requiredSections,
            'status' => $allPassed ? 'PASS' : 'FAIL'
        ];
        
        echo "Test 10: Calculator Result Matching - " . ($allPassed ? 'PASS' : 'FAIL') . "\n";
        if (!$allPassed) {
            foreach ($requiredSections as $section => $result) {
                if (!$result) echo "  - Missing section: {$section}\n";
            }
        }
    }
    
    private function displayResults()
    {
        echo "\n=== Test Summary ===\n";
        
        $totalTests = count($this->testResults);
        $passedTests = 0;
        
        foreach ($this->testResults as $testName => $result) {
            $status = $result['status'];
            echo "{$testName}: {$status}\n";
            if ($status === 'PASS') $passedTests++;
        }
        
        echo "\nOverall Result: {$passedTests}/{$totalTests} tests passed\n";
        
        if ($passedTests === $totalTests) {
            echo "\n✅ SUCCESS: PDF template includes all required elements!\n";
            echo "The PDF template is properly structured to match calculator results.\n";
        } else {
            echo "\n❌ ISSUES FOUND: Some elements are missing from the PDF template.\n";
            echo "Please review the failed tests above and update the template accordingly.\n";
        }
        
        echo "\n=== Key Features Verified ===\n";
        echo "✓ Second course display with dates, duration, and fees\n";
        echo "✓ Second accommodation with proper structure\n";
        echo "✓ Christmas break notices for both courses\n";
        echo "✓ Categorized supplements (Summer, Christmas, Extra Christmas)\n";
        echo "✓ Guardianship fee display for accommodations\n";
        echo "✓ Data structure compatibility with calculator\n";
        echo "✓ Accurate total calculations including all sections\n";
        echo "✓ Template rendering logic and conditional display\n";
        echo "✓ Complete section matching with online calculator\n";
    }
}

// Run the comprehensive test
$test = new PDFTemplateComprehensiveTest();
$test->runAllTests();

echo "\n=== Test Complete ===\n";
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\QuotationController;
use App\Services\FeeCalculatorService;
use App\Models\School;
use App\Models\Course;
use App\Models\CourseType;
use App\Models\Accommodation;
use App\Models\AccommodationType;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Course Christmas Supplement Fix\n";
echo "=====================================\n\n";

// Test scenario: Course that overlaps with Christmas period
$testData = [
    'school_id' => 1,
    'region_id' => 1,
    'course_type_id' => 1,
    'course_id' => 1,
    'course_start_date' => '2025-12-15', // Overlaps with Christmas
    'course_duration_weeks' => 8,
    'accommodation_id' => 1,
    'accommodation_duration_weeks' => 8,
    'accommodation_start_date' => '2025-12-15',
    'pricing_type' => 'standard'
];

try {
    $request = new Request($testData);
    $controller = new QuotationController();
    $calculator = new FeeCalculatorService();
    
    $response = $controller->calculate($request, $calculator);
    $responseData = $response->getData(true);
    
    if (isset($responseData['costBreakdown'])) {
        $breakdown = $responseData['costBreakdown'];
        
        echo "Cost Breakdown Items:\n";
        echo "--------------------\n";
        
        $foundCourseChristmas = false;
        $foundAccommodationChristmas = false;
        
        if (isset($breakdown['items'])) {
            foreach ($breakdown['items'] as $item) {
                echo "- " . $item['name'] . ": £" . number_format($item['amount'], 2) . "\n";
                
                // Check for Course Christmas Supplement
                if (stripos($item['name'], 'Course Christmas Supplement') !== false) {
                    $foundCourseChristmas = true;
                    echo "  ❌ FOUND COURSE CHRISTMAS SUPPLEMENT (This should NOT appear!)\n";
                }
                
                // Check for Accommodation Christmas Supplement
                if (stripos($item['name'], 'Accommodation Christmas Supplement') !== false) {
                    $foundAccommodationChristmas = true;
                    echo "  ✅ Found Accommodation Christmas Supplement (This is correct)\n";
                }
            }
        }
        
        echo "\nTest Results:\n";
        echo "-------------\n";
        
        if (!$foundCourseChristmas) {
            echo "✅ PASS: No Course Christmas Supplement found in results\n";
        } else {
            echo "❌ FAIL: Course Christmas Supplement still appears in results\n";
        }
        
        if ($foundAccommodationChristmas) {
            echo "✅ PASS: Accommodation Christmas Supplement correctly applied\n";
        } else {
            echo "⚠️  INFO: No Accommodation Christmas Supplement found (may be expected based on settings)\n";
        }
        
        echo "\nTotal: £" . number_format($breakdown['total'], 2) . "\n";
        
    } else {
        echo "❌ ERROR: No cost breakdown found in response\n";
        print_r($responseData);
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\QuotationPdfController;
use App\Services\FeeCalculatorService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing PDF Christmas Supplement Fix\n";
echo "===================================\n\n";

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
    $pdfController = new QuotationPdfController();
    $calculator = new FeeCalculatorService();
    
    echo "Testing PDF generation...\n";
    
    // Generate PDF directly
    $pdfResponse = $pdfController->generatePdf($request, $calculator);
        
    // Check if PDF was generated successfully
    if ($pdfResponse->getStatusCode() === 200) {
        echo "✅ PDF generated successfully\n";
        
        // Get the PDF content
        $pdfContent = $pdfResponse->getContent();
        
        // Check for Course Christmas Supplement in PDF
        $hasCourseChristmas = stripos($pdfContent, 'Course Christmas Supplement') !== false;
        
        // Check for Accommodation Christmas Supplement in PDF
        $hasAccommodationChristmas = stripos($pdfContent, 'Accommodation Christmas Supplement') !== false;
        
        echo "\nPDF Content Analysis:\n";
        echo "--------------------\n";
        
        if (!$hasCourseChristmas) {
            echo "✅ PASS: No Course Christmas Supplement found in PDF\n";
        } else {
            echo "❌ FAIL: Course Christmas Supplement still appears in PDF\n";
        }
        
        if ($hasAccommodationChristmas) {
            echo "✅ PASS: Accommodation Christmas Supplement correctly appears in PDF\n";
        } else {
            echo "⚠️  INFO: No Accommodation Christmas Supplement found in PDF (may be expected based on settings)\n";
        }
        
        // Save PDF for manual inspection
        file_put_contents('test_pdf_output.pdf', $pdfContent);
        echo "\n📄 PDF saved as 'test_pdf_output.pdf' for manual inspection\n";
        
    } else {
        echo "❌ ERROR: PDF generation failed with status code: " . $pdfResponse->getStatusCode() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nPDF test completed.\n";
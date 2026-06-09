<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use Illuminate\Support\Facades\Session;

echo "=== Direct Form Submission Test ===\n\n";

try {
    // Get school before test
    $school = School::find(2);
    echo "INITIAL STATE:\n";
    echo "School: {$school->name}\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n\n";
    
    // Get CSRF token
    Session::start();
    $csrfToken = csrf_token();
    
    echo "CSRF Token: {$csrfToken}\n\n";
    
    // Prepare form data exactly as browser would send it
    $formData = [
        '_token' => $csrfToken,
        '_method' => 'PUT',
        'name' => $school->name,
        'city_id' => $school->city_id,
        'currency_id' => $school->currency_id,
        'active' => '0', // Unchecked state - only hidden input
        'courier_fee_enabled' => $school->courier_fee_enabled ? '1' : '0',
        'extra_accommodation_weeks' => $school->extra_accommodation_weeks ?? 0,
        'order' => $school->order ?? 0,
        'registration_fee' => $school->registration_fee ?? 0,
        'accommodation_fee' => $school->accommodation_fee ?? 0,
        'bank_charges' => $school->bank_charges ?? 0,
        'books_fee' => $school->books_fee ?? 0,
        'books_weeks' => $school->books_weeks ?? 1,
        'insurance_fee_per_week' => $school->insurance_fee_per_week ?? 0,
        'courier_fee' => $school->courier_fee ?? 0,
        'guardianship_fee_per_week' => $school->guardianship_fee_per_week ?? 0,
        'custodianship_fee' => $school->custodianship_fee ?? 0,
        'christmas_fee_per_week' => $school->christmas_fee_per_week ?? 0,
        'summer_fee_per_week' => $school->summer_fee_per_week ?? 0,
        'summer_fee_weeks_off' => $school->summer_fee_weeks_off ?? 0,
    ];
    
    // Convert form data to URL-encoded string
    $postData = http_build_query($formData);
    
    echo "=== TESTING UNCHECKED STATE (active=0) ===\n";
    echo "Form data: active='{$formData['active']}'\n";
    
    // Use curl to submit the form
    $url = 'http://127.0.0.1:8000/admin/schools/2';
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'X-Requested-With: XMLHttpRequest', // Optional: simulate AJAX
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "CURL Error: {$error}\n";
    } else {
        echo "HTTP Response Code: {$httpCode}\n";
        
        // Extract response headers and body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        echo "Response Headers:\n";
        $headerLines = explode("\n", $headers);
        foreach ($headerLines as $line) {
            if (trim($line) && (strpos($line, 'Location:') !== false || strpos($line, 'Set-Cookie:') !== false)) {
                echo "  " . trim($line) . "\n";
            }
        }
        
        // Check if response contains success indicators
        if (strpos($body, 'success') !== false || $httpCode == 302) {
            echo "Form submission appears successful\n";
        } else {
            echo "Form submission may have failed\n";
            echo "Response body (first 500 chars):\n";
            echo substr($body, 0, 500) . "\n";
        }
    }
    
    // Check database state after submission
    $school->refresh();
    echo "\nAFTER CURL SUBMISSION:\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Check calculator
    $inCalculator = School::where('active', true)->where('id', 2)->exists();
    echo "Appears in calculator: " . ($inCalculator ? 'YES' : 'NO') . "\n";
    
    // Clean up
    if (file_exists('cookies.txt')) {
        unlink('cookies.txt');
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
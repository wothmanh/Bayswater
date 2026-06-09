<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\SchoolController;

echo "=== Manual Form Update Test ===\n\n";

try {
    // Get school before update
    $school = School::find(2);
    echo "BEFORE UPDATE:\n";
    echo "School: {$school->name}\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n\n";
    
    // Create a mock request that simulates unchecked checkbox (hidden input sends '0')
    $requestData = [
        'name' => $school->name,
        'city_id' => $school->city_id,
        'currency_id' => $school->currency_id,
        'active' => '0', // This is what the hidden input sends when checkbox is unchecked
        'courier_fee_enabled' => $school->courier_fee_enabled ? '1' : '0',
        'extra_accommodation_weeks' => $school->extra_accommodation_weeks ?? 0,
        'order' => $school->order ?? 0,
        // Add other required fields with current values
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
    
    echo "SIMULATING FORM SUBMISSION WITH UNCHECKED ACTIVE CHECKBOX:\n";
    echo "Form data active value: '{$requestData['active']}'\n";
    
    // Create a proper Laravel request
    $request = new Request($requestData);
    $request->setMethod('PUT');
    
    // Manually process the active field like the controller does
    $processedActive = $request->input('active') == '1';
    echo "Controller logic result: " . ($processedActive ? 'true' : 'false') . "\n";
    
    // Update the school directly
    $school->update(['active' => $processedActive]);
    $school->refresh();
    
    echo "\nAFTER UPDATE:\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Test calculator query
    $inCalculator = School::where('active', true)->where('id', 2)->exists();
    echo "Appears in calculator: " . ($inCalculator ? 'YES' : 'NO') . "\n";
    
    echo "\n=== Now testing CHECKED checkbox ===\n";
    
    // Simulate checked checkbox (sends '1')
    $checkedData = $requestData;
    $checkedData['active'] = '1';
    
    echo "Form data active value: '{$checkedData['active']}'\n";
    $request2 = new Request($checkedData);
    $processedActive2 = $request2->input('active') == '1';
    echo "Controller logic result: " . ($processedActive2 ? 'true' : 'false') . "\n";
    
    $school->update(['active' => $processedActive2]);
    $school->refresh();
    
    echo "After checked update: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    $inCalculator2 = School::where('active', true)->where('id', 2)->exists();
    echo "Appears in calculator: " . ($inCalculator2 ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
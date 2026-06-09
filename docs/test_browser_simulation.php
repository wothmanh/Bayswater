<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\SchoolController;
use Illuminate\Support\Facades\Log;

echo "=== Browser Form Simulation Test ===\n\n";

try {
    // Get school before test
    $school = School::find(2);
    echo "INITIAL STATE:\n";
    echo "School: {$school->name}\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n\n";
    
    // Clear any existing logs
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        // Get current log size to read only new entries
        $initialLogSize = filesize($logFile);
    } else {
        $initialLogSize = 0;
    }
    
    echo "=== TEST 1: Simulating UNCHECKED checkbox (browser sends hidden input only) ===\n";
    
    // When checkbox is unchecked, browser only sends the hidden input value
    $uncheckedFormData = [
        '_token' => csrf_token(),
        '_method' => 'PUT',
        'name' => $school->name,
        'city_id' => $school->city_id,
        'currency_id' => $school->currency_id,
        'active' => '0', // Only hidden input is sent
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
    
    echo "Form data being sent:\n";
    echo "- active: '{$uncheckedFormData['active']}'\n";
    echo "- _method: '{$uncheckedFormData['_method']}'\n";
    echo "- _token: present\n\n";
    
    // Create request exactly like Laravel would
    $request = Request::create(
        route('admin.schools.update', $school),
        'POST', // Laravel uses POST with _method override
        $uncheckedFormData
    );
    $request->headers->set('Content-Type', 'application/x-www-form-urlencoded');
    
    // Instantiate controller
    $controller = new SchoolController();
    
    echo "Calling controller update method...\n";
    
    // Call the update method
    $response = $controller->update($request, $school);
    
    echo "Controller response: " . get_class($response) . "\n";
    
    // Refresh school from database
    $school->refresh();
    
    echo "\nAFTER UNCHECKED TEST:\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Check calculator
    $inCalculator = School::where('active', true)->where('id', 2)->exists();
    echo "Appears in calculator: " . ($inCalculator ? 'YES' : 'NO') . "\n";
    
    echo "\n=== TEST 2: Simulating CHECKED checkbox (browser sends both values) ===\n";
    
    // When checkbox is checked, browser sends both hidden input AND checkbox value
    // Laravel will use the last value (checkbox value '1')
    $checkedFormData = $uncheckedFormData;
    $checkedFormData['active'] = ['0', '1']; // This is how browsers send it
    
    echo "Form data being sent:\n";
    echo "- active: [hidden='0', checkbox='1'] (Laravel uses last value)\n";
    
    // Create request for checked state
    $request2 = Request::create(
        route('admin.schools.update', $school),
        'POST',
        array_merge($checkedFormData, ['active' => '1']) // Laravel processes this as '1'
    );
    $request2->headers->set('Content-Type', 'application/x-www-form-urlencoded');
    
    echo "Calling controller update method...\n";
    
    // Call the update method
    $response2 = $controller->update($request2, $school);
    
    // Refresh school from database
    $school->refresh();
    
    echo "\nAFTER CHECKED TEST:\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Check calculator
    $inCalculator2 = School::where('active', true)->where('id', 2)->exists();
    echo "Appears in calculator: " . ($inCalculator2 ? 'YES' : 'NO') . "\n";
    
    // Read new log entries
    echo "\n=== CHECKING DEBUG LOGS ===\n";
    if (file_exists($logFile)) {
        $currentLogSize = filesize($logFile);
        if ($currentLogSize > $initialLogSize) {
            $handle = fopen($logFile, 'r');
            fseek($handle, $initialLogSize);
            $newLogs = fread($handle, $currentLogSize - $initialLogSize);
            fclose($handle);
            
            echo "New log entries:\n";
            echo $newLogs;
        } else {
            echo "No new log entries found.\n";
        }
    } else {
        echo "Log file not found.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

echo "\n=== Test Complete ===\n";
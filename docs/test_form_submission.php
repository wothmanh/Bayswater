<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\SchoolController;

echo "=== Form Submission Test ===\n\n";

try {
    // Get school before update
    $school = School::find(2);
    echo "BEFORE UPDATE:\n";
    echo "School: {$school->name}\n";
    echo "Active: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n\n";
    
    // Test 1: Simulate unchecked checkbox (should send active=0)
    echo "TEST 1: Simulating UNCHECKED checkbox (active=0)\n";
    $school->update(['active' => false]);
    $school->refresh();
    echo "After setting active=false: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Test 2: Simulate checked checkbox (should send active=1)
    echo "\nTEST 2: Simulating CHECKED checkbox (active=1)\n";
    $school->update(['active' => true]);
    $school->refresh();
    echo "After setting active=true: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Test 3: Test the exact logic from controller
    echo "\nTEST 3: Testing controller logic\n";
    
    // Simulate form data when checkbox is UNCHECKED (hidden input sends '0')
    $uncheckedData = ['active' => '0'];
    $processedUnchecked = $uncheckedData['active'] == '1';
    echo "Unchecked form data: active='{$uncheckedData['active']}'\n";
    echo "Controller logic result: " . ($processedUnchecked ? 'true' : 'false') . "\n";
    
    // Apply the unchecked logic
    $school->update(['active' => $processedUnchecked]);
    $school->refresh();
    echo "Database after unchecked: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Simulate form data when checkbox is CHECKED
    echo "\n";
    $checkedData = ['active' => '1'];
    $processedChecked = $checkedData['active'] == '1';
    echo "Checked form data: active='{$checkedData['active']}'\n";
    echo "Controller logic result: " . ($processedChecked ? 'true' : 'false') . "\n";
    
    // Apply the checked logic
    $school->update(['active' => $processedChecked]);
    $school->refresh();
    echo "Database after checked: " . ($school->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    echo "\n=== Final Status ===\n";
    $finalSchool = School::find(2);
    echo "Final state - Active: " . ($finalSchool->active ? '1 (Active)' : '0 (Inactive)') . "\n";
    
    // Check calculator
    $activeInCalculator = School::where('active', true)->where('id', 2)->exists();
    echo "Appears in calculator: " . ($activeInCalculator ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
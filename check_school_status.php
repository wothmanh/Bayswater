<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use Illuminate\Support\Facades\DB;

echo "=== School Status Verification ===\n\n";

try {
    // Get school with ID 2
    $school = School::find(2);
    
    if (!$school) {
        echo "ERROR: School with ID 2 not found!\n";
        exit(1);
    }
    
    echo "School: {$school->name}\n";
    echo "ID: {$school->id}\n";
    echo "Active Status: " . ($school->active ? 'ACTIVE (1)' : 'INACTIVE (0)') . "\n";
    echo "Database Type: " . gettype($school->active) . "\n";
    echo "Raw DB Value: " . var_export($school->active, true) . "\n";
    
    // Check direct database query
    $directValue = DB::table('schools')->where('id', 2)->value('active');
    echo "Direct DB Query: " . var_export($directValue, true) . " (" . gettype($directValue) . ")\n";
    
    // Check if appears in calculator (active schools only)
    $inCalculator = School::where('active', true)->where('id', 2)->exists();
    echo "Appears in Calculator: " . ($inCalculator ? 'YES' : 'NO') . "\n";
    
    // Show all active schools for reference
    echo "\n=== All Active Schools ===\n";
    $activeSchools = School::where('active', true)->select('id', 'name', 'active')->get();
    
    if ($activeSchools->count() > 0) {
        foreach ($activeSchools as $activeSchool) {
            echo "ID {$activeSchool->id}: {$activeSchool->name} (active: {$activeSchool->active})\n";
        }
    } else {
        echo "No active schools found.\n";
    }
    
    echo "\n=== All Inactive Schools ===\n";
    $inactiveSchools = School::where('active', false)->select('id', 'name', 'active')->get();
    
    if ($inactiveSchools->count() > 0) {
        foreach ($inactiveSchools as $inactiveSchool) {
            echo "ID {$inactiveSchool->id}: {$inactiveSchool->name} (active: {$inactiveSchool->active})\n";
        }
    } else {
        echo "No inactive schools found.\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Status Check Complete ===\n";
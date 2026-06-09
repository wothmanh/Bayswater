<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING SCHOOLS TABLE STRUCTURE ===\n";

try {
    $columns = DB::select('DESCRIBE schools');
    
    echo "Courier-related columns in schools table:\n";
    foreach($columns as $col) {
        if(strpos($col->Field, 'courier') !== false) {
            echo "- " . $col->Field . " (" . $col->Type . ") - Default: " . $col->Default . "\n";
        }
    }
    
    // Check current schools data
    echo "\n=== CURRENT SCHOOLS DATA ===\n";
    $schools = DB::table('schools')->select('id', 'name', 'courier_fee', 'courier_fee_enabled')->get();
    
    foreach($schools as $school) {
        $enabled = isset($school->courier_fee_enabled) ? ($school->courier_fee_enabled ? 'Yes' : 'No') : 'Column not found';
        echo "School: {$school->name}\n";
        echo "  - Courier Fee: £{$school->courier_fee}\n";
        echo "  - Courier Fee Enabled: {$enabled}\n\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
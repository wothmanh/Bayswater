<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Accommodation;

try {
    $accommodations = Accommodation::select('id', 'name', 'private_bathroom_enabled', 'private_bathroom_fee', 'dietary_supplement_enabled', 'dietary_supplement_fee')
        ->get();
    
    echo "Total accommodations: " . $accommodations->count() . "\n\n";
    
    foreach ($accommodations as $accommodation) {
        echo "ID: {$accommodation->id}\n";
        echo "Name: {$accommodation->name}\n";
        echo "Private Bathroom Enabled: " . ($accommodation->private_bathroom_enabled ? 'Yes' : 'No') . "\n";
        echo "Private Bathroom Fee: {$accommodation->private_bathroom_fee}\n";
        echo "Dietary Supplement Enabled: " . ($accommodation->dietary_supplement_enabled ? 'Yes' : 'No') . "\n";
        echo "Dietary Supplement Fee: {$accommodation->dietary_supplement_fee}\n";
        echo "---\n";
    }
    
    // Check if any have these features enabled
    $withPrivateBathroom = $accommodations->where('private_bathroom_enabled', 1)->count();
    $withDietarySupplement = $accommodations->where('dietary_supplement_enabled', 1)->count();
    
    echo "\nAccommodations with Private Bathroom enabled: {$withPrivateBathroom}\n";
    echo "Accommodations with Dietary Supplement enabled: {$withDietarySupplement}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
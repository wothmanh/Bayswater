<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing accommodation data attributes...\n\n";

// Get the accommodation that should have features enabled
$accommodation = App\Models\Accommodation::find(1);

if ($accommodation) {
    echo "Accommodation: {$accommodation->name}\n";
    echo "Private Bathroom Enabled: " . ($accommodation->private_bathroom_enabled ? 'Yes' : 'No') . "\n";
    echo "Private Bathroom Fee: £{$accommodation->private_bathroom_fee}\n";
    echo "Dietary Supplement Enabled: " . ($accommodation->dietary_supplement_enabled ? 'Yes' : 'No') . "\n";
    echo "Dietary Supplement Fee: £{$accommodation->dietary_supplement_fee}\n\n";
    
    // Test the data attributes that would be generated
    echo "HTML data attributes that would be generated:\n";
    echo "data-private-bathroom-enabled=\"" . ($accommodation->private_bathroom_enabled ? '1' : '0') . "\"\n";
    echo "data-private-bathroom-fee=\"" . ($accommodation->private_bathroom_fee ?? 0) . "\"\n";
    echo "data-dietary-supplement-enabled=\"" . ($accommodation->dietary_supplement_enabled ? '1' : '0') . "\"\n";
    echo "data-dietary-supplement-fee=\"" . ($accommodation->dietary_supplement_fee ?? 0) . "\"\n";
} else {
    echo "Accommodation with ID 1 not found.\n";
}

// Check all accommodations with features enabled
echo "\n\nAll accommodations with features enabled:\n";
$accommodationsWithFeatures = App\Models\Accommodation::where('private_bathroom_enabled', true)
    ->orWhere('dietary_supplement_enabled', true)
    ->get(['id', 'name', 'private_bathroom_enabled', 'private_bathroom_fee', 'dietary_supplement_enabled', 'dietary_supplement_fee']);

foreach ($accommodationsWithFeatures as $accom) {
    echo "ID {$accom->id}: {$accom->name}\n";
    echo "  - Private Bathroom: " . ($accom->private_bathroom_enabled ? "Yes (£{$accom->private_bathroom_fee})" : 'No') . "\n";
    echo "  - Dietary Supplement: " . ($accom->dietary_supplement_enabled ? "Yes (£{$accom->dietary_supplement_fee})" : 'No') . "\n\n";
}
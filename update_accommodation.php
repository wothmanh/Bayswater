<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Updating accommodation with proper fees...\n";

// Update the first accommodation
$accommodation = App\Models\Accommodation::first();

if ($accommodation) {
    $accommodation->update([
        'private_bathroom_enabled' => true,
        'private_bathroom_fee' => 25.00,
        'dietary_supplement_enabled' => true,
        'dietary_supplement_fee' => 15.00
    ]);
    
    echo "Updated: {$accommodation->name}\n";
    echo "Private Bathroom: Enabled (£{$accommodation->private_bathroom_fee})\n";
    echo "Dietary Supplement: Enabled (£{$accommodation->dietary_supplement_fee})\n";
} else {
    echo "No accommodation found to update.\n";
}
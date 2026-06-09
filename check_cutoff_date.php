<?php

require_once 'vendor/autoload.php';

use App\Models\Setting;
use Carbon\Carbon;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Current Settings Check ===\n\n";

try {
    $settings = Setting::getAllSettings();
    
    if ($settings) {
        echo "Cutoff Date: " . ($settings->cutoff_date ?? 'Not set') . "\n";
        echo "Quotation Extraction Date Override: " . ($settings->quotation_extraction_date ?? 'Not set') . "\n";
        
        if ($settings->cutoff_date) {
            $cutoffDate = Carbon::parse($settings->cutoff_date);
            echo "Parsed Cutoff Date: " . $cutoffDate->format('j M Y') . "\n";
        }
        
        if ($settings->quotation_extraction_date) {
            $quotationDate = Carbon::parse($settings->quotation_extraction_date);
            echo "Parsed Quotation Date: " . $quotationDate->format('j M Y') . "\n";
        }
    } else {
        echo "No settings found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nCurrent System Date: " . Carbon::today()->format('j M Y') . "\n";
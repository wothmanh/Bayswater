<?php

require_once 'vendor/autoload.php';

use App\Models\Setting;
use Carbon\Carbon;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Current Settings:\n";
echo "================\n";

$settings = Setting::getAllSettings();

echo "Cutoff Date: " . ($settings->cutoff_date ?? 'Not set') . "\n";
echo "Quotation Extraction Date: " . ($settings->quotation_extraction_date ?? 'Not set') . "\n";

if ($settings->cutoff_date) {
    $cutoffDate = Carbon::parse($settings->cutoff_date);
    echo "Cutoff Date (parsed): " . $cutoffDate->format('Y-m-d (j M Y)') . "\n";
}

if ($settings->quotation_extraction_date) {
    $quotationDate = Carbon::parse($settings->quotation_extraction_date);
    echo "Quotation Date (parsed): " . $quotationDate->format('Y-m-d (j M Y)') . "\n";
}

echo "\nCurrent Date: " . Carbon::now()->format('Y-m-d (j M Y)') . "\n";
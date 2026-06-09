<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "Checking for 2026 columns in schools table:\n";
echo "==========================================\n";

$columns = Schema::getColumnListing('schools');
$columns2026 = [];

foreach ($columns as $column) {
    if (strpos($column, '2026') !== false) {
        $columns2026[] = $column;
    }
}

if (empty($columns2026)) {
    echo "No 2026 columns found!\n";
} else {
    echo "Found " . count($columns2026) . " columns with '2026':\n";
    foreach ($columns2026 as $column) {
        echo "- $column\n";
    }
}

echo "\nExpected 2026 columns based on the issue:\n";
$expected = [
    'bank_charges_2026',
    'courier_fee_2026', 
    'insurance_fee_per_week_2026',
    'books_fee_2026',
    'custodianship_fee_2026',
    'christmas_extra_accommodation_weeks_2026'
];

foreach ($expected as $expected_col) {
    $exists = in_array($expected_col, $columns2026);
    echo "- $expected_col: " . ($exists ? "EXISTS" : "MISSING") . "\n";
}
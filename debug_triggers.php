<?php

use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$triggers = DB::select('SHOW TRIGGERS');

echo "Checking for triggers...\n";

if (empty($triggers)) {
    echo "No triggers found in the database.\n";
} else {
    echo "Found " . count($triggers) . " triggers.\n";
    foreach ($triggers as $trigger) {
        echo "Trigger: " . $trigger->Trigger . "\n";
        echo "Event: " . $trigger->Event . "\n";
        echo "Table: " . $trigger->Table . "\n";
        echo "Statement: " . $trigger->Statement . "\n";
        echo "----------------------------------------\n";
    }
}

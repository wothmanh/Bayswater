<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ALL TABLES IN DATABASE ===\n";

try {
    // Get all tables
    $tables = DB::select('SHOW TABLES');
    $tableKey = 'Tables_in_bayswater_laravel';
    
    foreach ($tables as $table) {
        $tableName = $table->$tableKey;
        try {
            $count = DB::table($tableName)->count();
            $status = $count > 0 ? "✅ {$count} records" : "⚪ empty";
            echo "{$tableName}: {$status}\n";
        } catch (Exception $e) {
            echo "{$tableName}: ❌ Error\n";
        }
    }
    
    echo "\n=== CHECKING FOR BACKUP TABLES ===\n";
    
    // Look for tables that might contain your data
    $backupPatterns = ['%backup%', '%old%', '%_v2%', '%temp%'];
    
    foreach ($backupPatterns as $pattern) {
        $backupTables = DB::select("SHOW TABLES LIKE '{$pattern}'");
        if (!empty($backupTables)) {
            echo "Found tables matching '{$pattern}':\n";
            foreach ($backupTables as $table) {
                $tableName = $table->$tableKey;
                $count = DB::table($tableName)->count();
                echo "  - {$tableName}: {$count} records\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
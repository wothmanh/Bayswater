<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DATABASE CONNECTION TEST ===\n";
echo "Database Name: " . config('database.connections.mysql.database') . "\n";
echo "Database Host: " . config('database.connections.mysql.host') . "\n";
echo "Database Port: " . config('database.connections.mysql.port') . "\n";

try {
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
    
    // Check current database
    $currentDb = DB::select('SELECT DATABASE() as db')[0]->db;
    echo "Current database: " . $currentDb . "\n\n";
    
    echo "=== TABLE COUNTS ===\n";
    
    // Check if tables exist and count records
    $tables = ['countries', 'cities', 'schools', 'courses', 'accommodations', 'airports', 'regions'];
    
    foreach ($tables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "📊 {$table}: {$count} records\n";
        } catch (Exception $e) {
            echo "❌ {$table}: Error - " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
<?php

use App\Models\Course;
use Illuminate\Support\Facades\Log;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 77;
echo "Searching for Course ID: $id\n";

// Check raw database to ignore scopes
$course = Course::withoutGlobalScopes()->find($id);

if ($course) {
    echo "FOUND Course $id:\n";
    echo "  Name: " . $course->name . "\n";
    echo "  Category: " . ($course->category ?? 'NULL') . "\n";
    echo "  Active: " . ($course->active ? 'Yes' : 'No') . "\n";
    echo "  School ID: " . $course->school_id . "\n";
    echo "  Order: " . $course->order . "\n";
    echo "  Created At: " . $course->created_at . "\n";
    echo "  Updated At: " . $course->updated_at . "\n";
    echo "  Deleted At: " . ($course->deleted_at ?? 'NULL') . "\n"; // If soft deletes exist
} else {
    echo "Course $id NOT FOUND in database.\n";
    
    // Check if any course was updated recently
    $recent = Course::withoutGlobalScopes()
        ->orderBy('updated_at', 'desc')
        ->take(5)
        ->get();
        
    echo "\n5 Most Recently Updated Courses:\n";
    foreach ($recent as $c) {
        echo "  ID: {$c->id} | Name: {$c->name} | Cat: {$c->category} | Updated: {$c->updated_at}\n";
    }
}

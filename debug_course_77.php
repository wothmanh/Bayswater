<?php

use App\Models\Course;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$id = 77;
$course = Course::find($id);

if ($course) {
    echo "Course {$id} found.\n";
    echo "Name: " . $course->name . "\n";
    echo "Category: " . $course->category . "\n";
    echo "Active: " . ($course->active ? 'Yes' : 'No') . "\n";
    echo "Deleted At: " . ($course->deleted_at ?? 'NULL') . "\n";
} else {
    echo "Course {$id} NOT found.\n";
    
    // Check soft deleted if trait exists (need to check model first, but assuming standard Laravel)
    // accessing withTrashed() might fail if trait not used, so safe check via raw DB query if needed, 
    // but simpler to just try finding it via query builder if model find fails.
    $raw = \DB::table('courses')->where('id', $id)->first();
    if ($raw) {
        echo "Course {$id} exists in DB (raw query).\n";
        echo "Category: " . $raw->category . "\n";
        echo "Deleted At: " . ($raw->deleted_at ?? 'NULL') . "\n";
    } else {
         echo "Course {$id} is GONE from DB.\n";
    }
}

<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\School;
use App\Models\Course;
use App\Models\CoursePrice;
use App\Models\Accommodation;

echo "Available Courses and Pricing Information\n";
echo "========================================\n\n";

// Check available schools
echo "=== SCHOOLS ===\n";
$schools = School::with('currency')->get();
foreach ($schools as $school) {
    $cityName = isset($school->city->name) ? $school->city->name : 'Unknown City';
    echo "ID: {$school->id} - {$school->name} ({$cityName})\n";
}

echo "\n=== COURSES ===\n";
$courses = Course::all();
foreach ($courses as $course) {
    echo "ID: {$course->id} - {$course->name}\n";
    
    // Check course prices
    $prices = CoursePrice::where('course_id', $course->id)
        ->orderBy('min_weeks')
        ->get();
    
    if ($prices->count() > 0) {
        echo "  Price ranges:\n";
        foreach ($prices as $price) {
            $maxWeeks = $price->max_weeks ? $price->max_weeks : 'unlimited';
            echo "    {$price->min_weeks}-{$maxWeeks} weeks: £{$price->price_per_week}/week";
            if ($price->price_per_week_2026) {
                echo " (2026: £{$price->price_per_week_2026}/week)";
            }
            echo "\n";
        }
    } else {
        echo "  No active pricing found\n";
    }
}

echo "\n=== ACCOMMODATIONS ===\n";
$accommodations = Accommodation::all();
foreach ($accommodations as $accommodation) {
    echo "ID: {$accommodation->id} - {$accommodation->name}\n";
}

echo "\n=== SUGGESTED TEST PARAMETERS ===\n";
if ($schools->count() > 0 && $courses->count() > 0) {
    $school = $schools->first();
    $course = $courses->first();
    
    // Find a suitable price range
    $price = CoursePrice::where('course_id', $course->id)
        ->where('min_weeks', '<=', 20)
        ->where(function($query) {
            $query->where('max_weeks', '>=', 20)
                  ->orWhereNull('max_weeks');
        })
        ->first();
    
    if ($price) {
        echo "Recommended test parameters:\n";
        echo "- school_id: {$school->id}\n";
        echo "- course_id: {$course->id}\n";
        $maxWeeksDisplay = $price->max_weeks ? $price->max_weeks : 'unlimited';
        echo "- course_weeks: 20 (within range {$price->min_weeks}-{$maxWeeksDisplay})\n";
        echo "- start_date: '2025-11-01'\n";
        echo "- quotation_date: '2025-11-01'\n";
        if ($accommodations->count() > 0) {
            echo "- accommodation_id: {$accommodations->first()->id}\n";
        }
    }
}
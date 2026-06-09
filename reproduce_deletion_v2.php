<?php

use App\Models\Course;
use App\Models\CourseJuniorSetting;
use App\Models\School;
use App\Models\CourseType;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Starting reproduction script...\n";

// 1. Setup Data
$school = School::first();
$courseType = CourseType::first();

if (!$school || !$courseType) {
    die("Need at least one school and course type.\n");
}

// 2. Create Course
echo "Creating Course...\n";
$course = Course::create([
    'name' => 'Temp Deletion Test Course ' . uniqid(),
    'school_id' => $school->id,
    'course_type_id' => $courseType->id,
    'pricing_type' => 'per_week',
    'category' => 'junior',
    'active' => true,
]);

echo "Course created with ID: " . $course->id . "\n";

// 3. Create Settings (Simulate 'includes_accommodation' = true)
CourseJuniorSetting::create([
    'course_id' => $course->id,
    'includes_accommodation' => true,
    'buy_weeks_only' => true,
]);

echo "Settings created.\n";

// 4. Verify Existence
if (!Course::find($course->id)) {
    die("Course failed to create!\n");
}

// 5. Simulate Update (Uncheck 'includes_accommodation')
echo "Simulating Update...\n";

try {
    DB::beginTransaction();

    // Fetch fresh instance (like controller)
    // Controller: $course = Course::where('category', 'junior')->findOrFail($id);
    $courseToUpdate = Course::where('category', 'junior')->findOrFail($course->id);

    // Simulate Request Data (unchecked checkboxes are missing from request)
    $requestData = [
        'name' => $course->name . ' Updated',
        'school_id' => $school->id,
        'course_type_id' => $courseType->id,
        'pricing_type' => 'per_week',
        // 'active' missing
        // 'includes_accommodation' missing
        // 'buy_weeks_only' missing
        'accommodations' => [],
    ];

    // Controller Logic
    $courseData = [
        'name' => $requestData['name'],
        'school_id' => $requestData['school_id'],
        'course_type_id' => $requestData['course_type_id'],
        'pricing_type' => $requestData['pricing_type'],
        'lessons_per_week' => null,
        'hours_per_week' => null,
        'study_mode' => null,
        'description' => null,
        'notes' => null,
        'active' => false, // $request->has('active') is false
        'order' => $courseToUpdate->order,
        'category' => 'junior',
    ];

    $courseToUpdate->update($courseData);

    $settingsData = [
        'start_date' => null,
        'end_date' => null,
        'min_age' => null,
        'max_age' => null,
        'min_weeks' => null,
        'max_weeks' => null,
        'includes_accommodation' => false, // $request->has(...) is false
        'buy_weeks_only' => false, // $request->has(...) is false
    ];

    // Lazy load check
    if ($courseToUpdate->juniorSettings) {
        echo "Updating existing settings...\n";
        $courseToUpdate->juniorSettings->update($settingsData);
    } else {
        echo "Creating new settings...\n";
        $settingsData['course_id'] = $courseToUpdate->id;
        CourseJuniorSetting::create($settingsData);
    }

    $courseToUpdate->juniorAccommodations()->sync([]);

    DB::commit();
    echo "Update logic finished.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Exception: " . $e->getMessage() . "\n";
}

// 6. Verify Existence Again
$freshCourse = Course::find($course->id);
if ($freshCourse) {
    echo "SUCCESS: Course still exists.\n";
    echo "Active: " . ($freshCourse->active ? 'Yes' : 'No') . "\n";
    // Clean up
    $freshCourse->delete();
    echo "Cleaned up test course.\n";
} else {
    echo "FAILURE: Course was DELETED!\n";
    
    // Check raw
    $raw = DB::table('courses')->where('id', $course->id)->first();
    if ($raw) {
        echo "Wait, it exists in raw DB. Maybe Global Scope issue?\n";
    } else {
        echo "Gone from DB entirely.\n";
    }
}

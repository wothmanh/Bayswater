<?php

use App\Models\Course;
use App\Models\CourseJuniorSetting;
use App\Models\School;
use App\Models\CourseType;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "Starting reproduction script...\n";

// 1. Create dependencies
$school = School::first();
$courseType = CourseType::first();

if (!$school || !$courseType) {
    die("School or CourseType not found. Cannot proceed.\n");
}

// 2. Create a test Junior Course
$course = Course::create([
    'name' => 'Test Junior Course ' . uniqid(),
    'school_id' => $school->id,
    'course_type_id' => $courseType->id,
    'pricing_type' => 'per_week',
    'category' => 'junior',
    'active' => true,
    'order' => 10,
]);

echo "Created Course ID: " . $course->id . "\n";

// 3. Create Settings
CourseJuniorSetting::create([
    'course_id' => $course->id,
    'includes_accommodation' => true,
    'buy_weeks_only' => true,
    'min_age' => 10,
    'max_age' => 15,
]);

echo "Created Settings.\n";

// 4. Simulate Update Request Data
// User unchecks "includes_accommodation", so it is MISSING from request if using checkbox behavior,
// BUT controller uses $request->has().
// Let's simulate the controller logic directly.

$requestData = [
    'name' => 'Updated Name ' . uniqid(),
    'school_id' => $school->id,
    'course_type_id' => $courseType->id,
    'pricing_type' => 'per_week',
    // 'active' is missing (unchecked)
    // 'includes_accommodation' is missing (unchecked)
    // 'buy_weeks_only' is missing (unchecked)
    'accommodations' => [], // Empty array
];

// Controller Logic Simulation
try {
    DB::enableQueryLog();

    $courseToUpdate = Course::where('category', 'junior')->findOrFail($course->id);
    
    // Simulate $request->has(...)
    $isActive = false; // missing in request
    $includesAccommodation = false; // missing in request
    $buyWeeksOnly = false; // missing in request

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
        'active' => $isActive,
        'order' => $courseToUpdate->order,
        'category' => 'junior', // Enforce category
    ];

    $courseToUpdate->update($courseData);

    $settingsData = [
        'start_date' => null,
        'end_date' => null,
        'min_age' => null,
        'max_age' => null,
        'min_weeks' => null,
        'max_weeks' => null,
        'includes_accommodation' => $includesAccommodation,
        'buy_weeks_only' => $buyWeeksOnly,
    ];

    if ($courseToUpdate->juniorSettings) {
        $courseToUpdate->juniorSettings->update($settingsData);
    } else {
        $settingsData['course_id'] = $courseToUpdate->id;
        CourseJuniorSetting::create($settingsData);
    }

    // Sync logic (simplified)
    $courseToUpdate->juniorAccommodations()->sync([]);

    echo "Update completed.\n";

} catch (\Exception $e) {
    echo "Exception during update: " . $e->getMessage() . "\n";
}

// 5. Verify existence
$check = Course::find($course->id);
if ($check) {
    echo "Course {$course->id} EXISTS.\n";
    echo "Active: " . ($check->active ? 'Yes' : 'No') . "\n";
    echo "Includes Accommodation: " . ($check->juniorSettings->includes_accommodation ? 'Yes' : 'No') . "\n";
} else {
    echo "Course {$course->id} DELETED!\n";
    print_r(DB::getQueryLog());
}

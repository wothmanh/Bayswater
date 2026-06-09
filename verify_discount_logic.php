<?php
use App\Models\DiscountRule;
use App\Models\Course;

// 1. Create Test Data
echo "Creating Test Data...\n";
$course1 = Course::first();
if (!$course1) {
    echo "No courses found. Cannot test.\n";
    exit;
}
$course2 = Course::where('id', '!=', $course1->id)->first();
if (!$course2) {
    echo "Need at least 2 courses to test.\n";
    exit;
}

echo "Course 1: {$course1->name} (ID: {$course1->id})\n";
echo "Course 2: {$course2->name} (ID: {$course2->id})\n";

$rule = DiscountRule::create([
    'name' => 'Test Multi Rule',
    'discount_type' => 'fixed_amount',
    'discount_value' => 100,
    'applies_to' => 'course_tuition',
    'priority' => 999,
    'active' => true,
]);
echo "Created Rule ID: {$rule->id}\n";

// 2. Attach Courses (Pivot Test)
echo "Attaching courses [{$course1->id}, {$course2->id}]...\n";
$rule->courses()->sync([$course1->id, $course2->id]);

// Reload to check
$rule->load('courses');
$attached = $rule->courses->pluck('id')->toArray();
echo "Attached Course IDs: " . implode(', ', $attached) . "\n";

if (count($attached) === 2 && in_array($course1->id, $attached) && in_array($course2->id, $attached)) {
    echo "PASS: Pivot table attachment verified.\n";
} else {
    echo "FAIL: Pivot table attachment failed.\n";
}

// 3. Verify Calculation Logic Snippet
echo "Verifying Logic Snippet...\n";

// Mock Service Context
$mockService = new class {
    public $course;
    public $secondCourse;
    public function check(DiscountRule $rule) {
        // Logic copied from FeeCalculatorService
        $ruleCourses = $rule->courses->pluck('id')->toArray();
        // echo "Debug: Rule Courses: " . implode(',', $ruleCourses) . "\n";
        // echo "Debug: Checking Course ID: " . ($this->course ? $this->course->id : 'null') . "\n";
        
        if (!empty($ruleCourses)) {
            $matchesPrimaryCourse = ($this->course && in_array($this->course->id, $ruleCourses));
            $matchesSecondCourse = ($this->secondCourse && in_array($this->secondCourse->id, $ruleCourses));
            if (!$matchesPrimaryCourse && !$matchesSecondCourse) {
                return false;
            }
        } elseif ($rule->course_id !== null) {
            $matchesPrimaryCourse = ($this->course && $rule->course_id === $this->course->id);
            $matchesSecondCourse = ($this->secondCourse && $rule->course_id === $this->secondCourse->id);
            if (!$matchesPrimaryCourse && !$matchesSecondCourse) {
                return false;
            }
        }
        return true;
    }
};

// Case A: Course matches
$mockService->course = $course1;
$mockService->secondCourse = null;
$resultA = $mockService->check($rule);
echo "Case A (Match Course 1): " . ($resultA ? "PASS" : "FAIL") . "\n";

// Case B: Course matches second
$mockService->course = $course2;
$resultB = $mockService->check($rule);
echo "Case B (Match Course 2): " . ($resultB ? "PASS" : "FAIL") . "\n";

// Case C: No match
$course3 = Course::whereNotIn('id', [$course1->id, $course2->id])->first();
if ($course3) {
    $mockService->course = $course3;
    $resultC = $mockService->check($rule);
    echo "Case C (No Match - Course {$course3->id}): " . (!$resultC ? "PASS" : "FAIL") . "\n";
} else {
    echo "Skipping Case C (No 3rd course)\n";
}

// Cleanup
$rule->delete();
echo "Test Rule Deleted.\n";

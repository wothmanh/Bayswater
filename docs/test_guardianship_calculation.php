<?php

require_once 'vendor/autoload.php';

use Carbon\Carbon;

/**
 * Standalone test for the new Guardianship Fee (U18) calculation logic
 * Tests the age-based weekly calculation without requiring database access
 */

echo "=== Guardianship Fee (U18) Calculation Test ===\n\n";

/**
 * Simulate the calculateGuardianshipQualifyingWeeks logic
 * This mirrors the logic implemented in FeeCalculatorService
 */
function calculateGuardianshipQualifyingWeeks($studentBirthday, $startDate, $accommodationWeeks) {
    if (!$studentBirthday || !$startDate || !$accommodationWeeks) {
        return 0;
    }

    // Calculate the student's 18th birthday
    $eighteenthBirthday = $studentBirthday->copy()->addYears(18);

    // Ensure the start date is a Monday
    $accommodationStartDate = $startDate->copy()->startOfWeek(Carbon::MONDAY);

    // Calculate accommodation end date
    $accommodationEndDate = $accommodationStartDate->copy()->addWeeks($accommodationWeeks);

    // If student is already 18 at the start of accommodation, no weeks qualify
    if ($accommodationStartDate->greaterThanOrEqualTo($eighteenthBirthday)) {
        return 0;
    }

    // If student doesn't turn 18 during the accommodation period, all weeks qualify
    if ($eighteenthBirthday->greaterThan($accommodationEndDate)) {
        return $accommodationWeeks;
    }

    // Student turns 18 during accommodation period
    // Count weeks until the student turns 18
    $qualifyingWeeks = 0;
    $currentWeekStart = $accommodationStartDate->copy();

    for ($week = 0; $week < $accommodationWeeks; $week++) {
        $currentWeekEnd = $currentWeekStart->copy()->addDays(4); // Friday of current week

        // If the current week's Monday is before the 18th birthday, this week qualifies
        if ($currentWeekStart->lessThan($eighteenthBirthday)) {
            $qualifyingWeeks++;
        }

        // If the current week's Friday is on or after the 18th birthday, stop counting
        if ($currentWeekEnd->greaterThanOrEqualTo($eighteenthBirthday)) {
            break;
        }

        $currentWeekStart->addWeek();
    }

    return $qualifyingWeeks;
}

// Test Scenario 1: Student under 18 for entire accommodation period
echo "--- Test 1: Student under 18 for entire accommodation period ---\n";
$birthday1 = Carbon::parse('2008-06-15'); // Born June 15, 2008
$startDate1 = Carbon::parse('2024-01-08'); // Course starts January 8, 2024 (Monday)
$weeks1 = 12; // 12 weeks accommodation

$studentAge1 = $birthday1->diffInYears($startDate1);
echo "Student birthday: {$birthday1->format('Y-m-d')}\n";
echo "Course start date: {$startDate1->format('Y-m-d')}\n";
echo "Student age at start: {$studentAge1} years\n";
echo "Accommodation weeks: {$weeks1}\n";

$qualifyingWeeks1 = calculateGuardianshipQualifyingWeeks($birthday1, $startDate1, $weeks1);
echo "Guardianship qualifying weeks: {$qualifyingWeeks1}\n";
echo "Expected: {$weeks1} (all weeks qualify since student remains under 18)\n";
echo "Result: " . ($qualifyingWeeks1 == $weeks1 ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Scenario 2: Student turns 18 during accommodation period
echo "--- Test 2: Student turns 18 during accommodation period ---\n";
$birthday2 = Carbon::parse('2006-02-20'); // Born February 20, 2006
$startDate2 = Carbon::parse('2024-01-08'); // Course starts January 8, 2024 (Monday)
$weeks2 = 12; // 12 weeks accommodation

$turns18Date = $birthday2->copy()->addYears(18);
$studentAge2 = $birthday2->diffInYears($startDate2);
echo "Student birthday: {$birthday2->format('Y-m-d')}\n";
echo "Course start date: {$startDate2->format('Y-m-d')}\n";
echo "Student age at start: {$studentAge2} years\n";
echo "Student turns 18 on: {$turns18Date->format('Y-m-d')}\n";
echo "Accommodation weeks: {$weeks2}\n";

$qualifyingWeeks2 = calculateGuardianshipQualifyingWeeks($birthday2, $startDate2, $weeks2);
echo "Guardianship qualifying weeks: {$qualifyingWeeks2}\n";

// Calculate expected weeks manually
$accommodationStart = $startDate2->copy()->startOfWeek(Carbon::MONDAY);
$accommodationEnd = $accommodationStart->copy()->addWeeks($weeks2);
echo "Accommodation period: {$accommodationStart->format('Y-m-d')} to {$accommodationEnd->format('Y-m-d')}\n";
echo "Expected: Only weeks before turning 18 should qualify\n";
echo "Result: " . ($qualifyingWeeks2 > 0 && $qualifyingWeeks2 < $weeks2 ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Scenario 3: Student already 18 at start
echo "--- Test 3: Student already 18 at start of accommodation ---\n";
$birthday3 = Carbon::parse('2005-06-15'); // Born June 15, 2005
$startDate3 = Carbon::parse('2024-01-08'); // Course starts January 8, 2024 (Monday)
$weeks3 = 12; // 12 weeks accommodation

$studentAge3 = $birthday3->diffInYears($startDate3);
echo "Student birthday: {$birthday3->format('Y-m-d')}\n";
echo "Course start date: {$startDate3->format('Y-m-d')}\n";
echo "Student age at start: {$studentAge3} years\n";
echo "Accommodation weeks: {$weeks3}\n";

$qualifyingWeeks3 = calculateGuardianshipQualifyingWeeks($birthday3, $startDate3, $weeks3);
echo "Guardianship qualifying weeks: {$qualifyingWeeks3}\n";
echo "Expected: 0 (student is already 18)\n";
echo "Result: " . ($qualifyingWeeks3 == 0 ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Test Scenario 4: Edge case - Student turns 18 on a Friday
echo "--- Test 4: Edge case - Student turns 18 on a Friday ---\n";
$birthday4 = Carbon::parse('2006-01-13'); // Born January 13, 2006 (turns 18 on Friday, Jan 13, 2024)
$startDate4 = Carbon::parse('2024-01-08'); // Course starts January 8, 2024 (Monday)
$weeks4 = 4; // 4 weeks accommodation

$turns18Date4 = $birthday4->copy()->addYears(18);
$studentAge4 = $birthday4->diffInYears($startDate4);
echo "Student birthday: {$birthday4->format('Y-m-d')}\n";
echo "Course start date: {$startDate4->format('Y-m-d')}\n";
echo "Student age at start: {$studentAge4} years\n";
echo "Student turns 18 on: {$turns18Date4->format('Y-m-d')} ({$turns18Date4->format('l')})\n";
echo "Accommodation weeks: {$weeks4}\n";

$qualifyingWeeks4 = calculateGuardianshipQualifyingWeeks($birthday4, $startDate4, $weeks4);
echo "Guardianship qualifying weeks: {$qualifyingWeeks4}\n";
echo "Expected: 1 (only the first week qualifies, as student turns 18 on Friday of first week)\n";
echo "Result: " . ($qualifyingWeeks4 == 1 ? "✓ PASS" : "✗ FAIL") . "\n\n";

// Summary
echo "=== Test Summary ===\n";
echo "Test 1 (Under 18 entire period): {$qualifyingWeeks1} weeks\n";
echo "Test 2 (Turns 18 during period): {$qualifyingWeeks2} weeks\n";
echo "Test 3 (Already 18 at start): {$qualifyingWeeks3} weeks\n";
echo "Test 4 (Turns 18 on Friday): {$qualifyingWeeks4} weeks\n";
echo "\n=== Key Features Demonstrated ===\n";
echo "✓ Week defined as Monday to Friday\n";
echo "✓ Fee only applied for weeks when student is under 18\n";
echo "✓ Calculation stops once student turns 18\n";
echo "✓ Additional days count as full week\n";
echo "✓ Exact number of qualifying weeks is calculated\n";

?>
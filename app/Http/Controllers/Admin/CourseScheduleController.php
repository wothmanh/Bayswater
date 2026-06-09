<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CourseScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        abort(404); // Not used for nested resource
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Course $course): View
    {
        return view('admin.course_schedules.create', compact('course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'duration_weeks' => 'required|integer|min:1',
            'fixed_price' => 'required|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $course->courseSchedules()->create($validated);
        return redirect()->route('admin.courses.edit', $course);
            // ->with('success', 'Course schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CourseSchedule $schedule): View // Shallow route uses $schedule directly
    {
        $course = $schedule->course; // Load parent course for context
        return view('admin.course_schedules.edit', compact('schedule', 'course'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CourseSchedule $schedule): RedirectResponse // Shallow route uses $schedule directly
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'duration_weeks' => 'required|integer|min:1',
            'fixed_price' => 'required|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $schedule->update($validated);
        return redirect()->route('admin.courses.edit', $schedule->course_id);
            // ->with('success', 'Course schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CourseSchedule $schedule): RedirectResponse // Shallow route uses $schedule directly
    {
        $courseId = $schedule->course_id;
        $schedule->delete();
        return redirect()->route('admin.courses.edit', $courseId);
            // ->with('success', 'Course schedule deleted successfully.');
    }
}

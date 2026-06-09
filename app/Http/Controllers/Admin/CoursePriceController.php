<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CoursePrice;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CoursePriceController extends Controller
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
        return view('admin.course_prices.create', compact('course'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'min_weeks' => 'required|integer|min:1',
            'max_weeks' => 'required|integer|min:1|gte:min_weeks',
            'price_per_week' => 'required|numeric|min:0',
            'price_per_week_2026' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $course->coursePrices()->create($validated);
        
        if ($course->category === 'junior') {
            return redirect()->route('admin.junior-courses.edit', $course);
        }

        return redirect()->route('admin.courses.edit', $course);
            // ->with('success', 'Course price range created successfully.');
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
    public function edit(CoursePrice $price): View // Shallow route uses $price directly
    {
        $course = $price->course; // Load parent course for context
        return view('admin.course_prices.edit', compact('price', 'course'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CoursePrice $price): RedirectResponse // Shallow route uses $price directly
    {
        $validated = $request->validate([
            'min_weeks' => 'required|integer|min:1',
            'max_weeks' => 'required|integer|min:1|gte:min_weeks',
            'price_per_week' => 'required|numeric|min:0',
            'price_per_week_2026' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $price->update($validated);

        if ($price->course->category === 'junior') {
            return redirect()->route('admin.junior-courses.edit', $price->course_id);
        }

        return redirect()->route('admin.courses.edit', $price->course_id);
            // ->with('success', 'Course price range updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CoursePrice $price): RedirectResponse // Shallow route uses $price directly
    {
        $course = $price->course;
        $courseId = $price->course_id;
        $price->delete();
        
        if ($course->category === 'junior') {
            return redirect()->route('admin.junior-courses.edit', $courseId);
        }
        
        return redirect()->route('admin.courses.edit', $courseId);
            // ->with('success', 'Course price range deleted successfully.');
    }
}

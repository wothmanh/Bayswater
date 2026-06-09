<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\School;
use App\Models\City;
use App\Models\Country;
use App\Models\CourseType;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $courses = Course::with(['school', 'courseType'])
            ->where(function ($query) {
                $query->where('category', '!=', 'junior')
                      ->orWhereNull('category');
            })
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(20);
        // Preload countries, cities, and schools for filters (active only)
        $countries = Country::where('active', true)->orderBy('name')->get(['id', 'name']);
        $cities = City::where('active', true)->orderBy('name')->get(['id', 'name']);
        $schools = School::where('active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.courses.index', compact('courses', 'countries', 'cities', 'schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $courseTypes = CourseType::where('active', true)->orderBy('name')->pluck('name', 'id');
        $pricingTypes = ['per_week' => 'Per Week', 'fixed_schedule' => 'Fixed Schedule'];
        return view('admin.courses.create', compact('schools', 'courseTypes', 'pricingTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'course_type_id' => 'required|exists:course_types,id',
            'pricing_type' => ['required', Rule::in(['per_week', 'fixed_schedule'])],
            'lessons_per_week' => 'nullable|integer|min:0',
            'hours_per_week' => 'nullable|numeric|min:0',
            'study_mode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);
        $validated['active'] = $request->has('active');
        
        // Set default order if not provided
        if (!$request->has('order')) {
            $validated['order'] = 0;
        }
        
        Course::create($validated);
        return redirect()->route('admin.courses.index');
            // ->with('success', 'Course created successfully.');
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
    public function edit(Course $course): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $courseTypes = CourseType::where('active', true)->orderBy('name')->pluck('name', 'id');
        $pricingTypes = ['per_week' => 'Per Week', 'fixed_schedule' => 'Fixed Schedule'];
        // Eager load prices, schedules, and detail links for the edit view
        $course->load(['coursePrices', 'courseSchedules', 'detailLinks']);
        return view('admin.courses.edit', compact('course', 'schools', 'courseTypes', 'pricingTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        if ($course->category === 'junior') {
            return redirect()->route('admin.junior-courses.index');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'course_type_id' => 'required|exists:course_types,id',
            'pricing_type' => ['required', Rule::in(['per_week', 'fixed_schedule'])],
            'lessons_per_week' => 'nullable|integer|min:0',
            'hours_per_week' => 'nullable|numeric|min:0',
            'study_mode' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'detail_links' => 'nullable|array',
            'detail_links.*.url' => 'nullable|url|starts_with:http://,https://',
            'detail_links.*.button_text' => 'nullable|string|max:50',
        ]);
        
        $validated['active'] = $request->has('active');
        
        // Transactional update
        \Illuminate\Support\Facades\DB::transaction(function () use ($course, $validated, $request) {
            $course->update(\Illuminate\Support\Arr::except($validated, ['detail_links']));

            // Handle Course Detail Links
            // Delete existing links
            $course->detailLinks()->delete();

            // Create new links
            if ($request->has('detail_links')) {
                foreach ($request->detail_links as $index => $linkData) {
                    if (!empty($linkData['url']) && !empty($linkData['button_text'])) {
                        $course->detailLinks()->create([
                            'url' => $linkData['url'],
                            'button_text' => $linkData['button_text'],
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.courses.index');
            // ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Course $course): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info("CourseController::destroy called for ID: {$course->id}");
        $course->delete(); // Cascading deletes should handle prices/schedules
        return redirect()->route('admin.courses.index');
            // ->with('success', 'Course deleted successfully.');
    }

    /**
     * Update the order of courses via AJAX.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'courses' => 'required|array',
            'courses.*.id' => 'required|exists:courses,id',
            'courses.*.order' => 'required|integer|min:0'
        ]);

        foreach ($request->courses as $courseData) {
            Course::where('id', $courseData['id'])->update(['order' => $courseData['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Filter courses by country, city, and/or school via AJAX.
     */
    public function filter(Request $request)
    {
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');
        $schoolId = $request->query('school_id');

        $query = Course::with(['school', 'courseType'])
            ->where(function ($q) {
                $q->whereNull('category')->orWhere('category', '!=', 'junior');
            })
            ->orderBy('order')
            ->orderBy('name');

        if (!empty($schoolId)) {
            $query->where('school_id', $schoolId);
        }

        if (!empty($cityId)) {
            $query->whereHas('school', function ($q) use ($cityId) {
                $q->where('city_id', $cityId);
            });
        }

        if (!empty($countryId)) {
            $query->whereHas('school.city', function ($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
        }

        $courses = $query->paginate(20);

        // Render partial views for rows and pagination
        $rowsHtml = view('admin.courses._index_rows', compact('courses'))->render();
        $paginationHtml = view('admin.courses._pagination', compact('courses'))->render();

        return response()->json([
            'rows' => $rowsHtml,
            'pagination' => $paginationHtml,
        ]);
    }

    /**
     * Get cities for a given country (active only) via AJAX.
     */
    public function getCitiesForCountry(Request $request)
    {
        $countryId = $request->query('country_id');
        if (empty($countryId)) {
            $cities = City::where('active', true)->orderBy('name')->get(['id', 'name']);
        } else {
            $cities = City::where('active', true)->where('country_id', $countryId)->orderBy('name')->get(['id', 'name']);
        }
        return response()->json($cities);
    }

    /**
     * Get schools for a given country and/or city (active only) via AJAX.
     */
    public function getSchoolsForCountryCity(Request $request)
    {
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');

        $schoolsQuery = School::where('active', true)->orderBy('name');

        if (!empty($cityId)) {
            $schoolsQuery->where('city_id', $cityId);
        }

        if (!empty($countryId)) {
            $schoolsQuery->whereHas('city', function ($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
        }

        $schools = $schoolsQuery->get(['id', 'name']);

        return response()->json($schools);
    }
}

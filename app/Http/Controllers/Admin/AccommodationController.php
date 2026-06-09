<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\School; // Import School model
use App\Models\City; // Import City model
use App\Models\Country; // Import Country model
use App\Models\CourseType; // Import CourseType model
use App\Models\Course; // Import Course model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AccommodationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $accommodations = Accommodation::with('school')->orderBy('order')->orderBy('name')->paginate(20);
        // Preload countries, cities, and schools for filters (active only)
        $countries = Country::where('active', true)->orderBy('name')->get(['id', 'name']);
        $cities = City::where('active', true)->orderBy('name')->get(['id', 'name']);
        $schools = School::where('active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.accommodations.index', compact('accommodations', 'countries', 'cities', 'schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.accommodations.create', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'type' => 'nullable|string|max:100',
            'room_type' => 'nullable|string|max:100',
            'meal_plan' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0|gte:min_age',
            'requires_guardianship' => 'nullable|boolean',
            'requires_christmas_supplement' => 'nullable|boolean',
            // 2025 Summer Supplement fields
            'summer_fee_per_week' => 'nullable|numeric|min:0',
            'summer_start_date' => 'nullable|date',
            'summer_end_date' => 'nullable|date|after_or_equal:summer_start_date',
            'summer_fee_note' => 'nullable|string',
            // 2026 Summer Supplement fields
            'summer_fee_per_week_2026' => 'nullable|numeric|min:0',
            'summer_start_date_2026' => 'nullable|date',
            'summer_end_date_2026' => 'nullable|date|after_or_equal:summer_start_date_2026',
            'summer_fee_note_2026' => 'nullable|string',
            // Add-on fields
            'private_bathroom_enabled' => 'nullable|boolean',
            'private_bathroom_fee' => 'nullable|numeric|min:0',
            'private_bathroom_enabled_2026' => 'nullable|boolean',
            'private_bathroom_fee_2026' => 'nullable|numeric|min:0',
            'dietary_supplement_enabled' => 'nullable|boolean',
            'dietary_supplement_fee' => 'nullable|numeric|min:0',
            'dietary_supplement_enabled_2026' => 'nullable|boolean',
            'dietary_supplement_fee_2026' => 'nullable|numeric|min:0',
            'other_charge_enabled' => 'nullable|boolean',
            'other_charge_name' => 'nullable|string|max:255',
            'other_charge_amount' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);
        $validated['active'] = $request->has('active');
        $validated['requires_guardianship'] = $request->has('requires_guardianship');
        $validated['requires_christmas_supplement'] = $request->has('requires_christmas_supplement');
        $validated['private_bathroom_enabled'] = $request->has('private_bathroom_enabled');
        $validated['dietary_supplement_enabled'] = $request->has('dietary_supplement_enabled');
        $validated['private_bathroom_enabled_2026'] = $request->has('private_bathroom_enabled_2026');
        $validated['dietary_supplement_enabled_2026'] = $request->has('dietary_supplement_enabled_2026');
        $validated['other_charge_enabled'] = $request->has('other_charge_enabled');
        
        // Set default order if not provided
        if (!$request->has('order')) {
            $validated['order'] = 0;
        }
        
        Accommodation::create($validated);
        return redirect()->route('admin.accommodations.index');
            // ->with('success', 'Accommodation created successfully.');
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
    public function edit(Accommodation $accommodation): View
    {
        // Eager load the accommodation relationship with the prices and restrictions
        $accommodation->load(['accommodationPrices.accommodation', 'restrictedCourseTypes', 'restrictedCourses']);
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $courseTypes = CourseType::orderBy('name')->get();
        $courses = Course::where('active', true)->orderBy('name')->get();
        return view('admin.accommodations.edit', compact('accommodation', 'schools', 'courseTypes', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Accommodation $accommodation): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'type' => 'nullable|string|max:100',
            'room_type' => 'nullable|string|max:100',
            'meal_plan' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|min:0|gte:min_age',
            'requires_guardianship' => 'nullable|boolean',
            'requires_christmas_supplement' => 'nullable|boolean',
            // 2025 Summer Supplement fields
            'summer_fee_per_week' => 'nullable|numeric|min:0',
            'summer_start_date' => 'nullable|date',
            'summer_end_date' => 'nullable|date|after_or_equal:summer_start_date',
            'summer_fee_note' => 'nullable|string',
            // 2026 Summer Supplement fields
            'summer_fee_per_week_2026' => 'nullable|numeric|min:0',
            'summer_start_date_2026' => 'nullable|date',
            'summer_end_date_2026' => 'nullable|date|after_or_equal:summer_start_date_2026',
            'summer_fee_note_2026' => 'nullable|string',
            // Add-on fields
            'private_bathroom_enabled' => 'nullable|boolean',
            'private_bathroom_fee' => 'nullable|numeric|min:0',
            'private_bathroom_fee_2026' => 'nullable|numeric|min:0',
            'private_bathroom_enabled_2026' => 'nullable|boolean',
            'dietary_supplement_enabled' => 'nullable|boolean',
            'dietary_supplement_fee' => 'nullable|numeric|min:0',
            'dietary_supplement_fee_2026' => 'nullable|numeric|min:0',
            'dietary_supplement_enabled_2026' => 'nullable|boolean',
            'other_charge_enabled' => 'nullable|boolean',
            'other_charge_name' => 'nullable|string|max:255',
            'other_charge_amount' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);
        $validated['active'] = $request->has('active');
        $validated['requires_guardianship'] = $request->has('requires_guardianship');
        $validated['requires_christmas_supplement'] = $request->has('requires_christmas_supplement');
        $validated['private_bathroom_enabled'] = $request->has('private_bathroom_enabled');
        $validated['dietary_supplement_enabled'] = $request->has('dietary_supplement_enabled');
        $validated['private_bathroom_enabled_2026'] = $request->has('private_bathroom_enabled_2026');
        $validated['dietary_supplement_enabled_2026'] = $request->has('dietary_supplement_enabled_2026');
        $validated['other_charge_enabled'] = $request->has('other_charge_enabled');
        
        $accommodation->update($validated);

        // Sync restrictions
        $accommodation->restrictedCourseTypes()->sync($request->input('restricted_course_type_ids', []));
        $accommodation->restrictedCourses()->sync($request->input('restricted_course_ids', []));

        return redirect()->route('admin.accommodations.index');
            // ->with('success', 'Accommodation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Accommodation $accommodation): RedirectResponse
    {
        $accommodation->delete(); // Cascading deletes should handle prices
        return redirect()->route('admin.accommodations.index');
            // ->with('success', 'Accommodation deleted successfully.');
    }

    /**
     * Update the order of accommodations via AJAX.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'accommodations' => 'required|array',
            'accommodations.*.id' => 'required|exists:accommodations,id',
            'accommodations.*.order' => 'required|integer|min:0'
        ]);

        foreach ($request->accommodations as $accommodationData) {
            Accommodation::where('id', $accommodationData['id'])->update(['order' => $accommodationData['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Filter accommodations by country, city, and/or school via AJAX.
     */
    public function filter(Request $request)
    {
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');
        $schoolId = $request->query('school_id');

        $query = Accommodation::with('school')->orderBy('order')->orderBy('name');

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

        $accommodations = $query->paginate(20);

        // Render partial views for rows and pagination
        $rowsHtml = view('admin.accommodations._index_rows', compact('accommodations'))->render();
        $paginationHtml = view('admin.accommodations._pagination', compact('accommodations'))->render();

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

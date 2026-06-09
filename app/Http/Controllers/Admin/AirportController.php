<?php

namespace App\Http\Controllers\Admin;

use App\Models\Airport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View; // Import View
use Illuminate\Http\RedirectResponse; // Import RedirectResponse
use App\Models\School; // Import School model
use App\Models\City; // Import City model
use App\Models\Country; // Import Country model
use App\Models\CourseType; // Import CourseType model
use App\Models\Course; // Import Course model

class AirportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch airports with relationships for display
        $airports = Airport::with('school')->orderBy('order')->orderBy('name')->paginate(20);
        // Preload countries, cities, and schools for filters (active only)
        $countries = Country::where('active', true)->orderBy('name')->get(['id', 'name']);
        $cities = City::where('active', true)->orderBy('name')->get(['id', 'name']);
        $schools = School::where('active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.airports.index', compact('airports', 'countries', 'cities', 'schools'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Fetch necessary data for the form, e.g., schools
        $schools = \App\Models\School::orderBy('name')->pluck('name', 'id');
        return view('admin.airports.create', compact('schools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'arrival_price' => 'nullable|numeric|min:0',
            'departure_price' => 'nullable|numeric|min:0',
            'arrival_price_2026' => 'nullable|numeric|min:0',
            'departure_price_2026' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['active'] = $request->has('active');
        
        // Set default order if not provided
        if (!$request->has('order')) {
            $validated['order'] = 0;
        }

        // Fetch associated school to get city and country IDs
        $school = School::find($validated['school_id']);
        if ($school) {
            $validated['city_id'] = $school->city_id;
            $validated['country_id'] = $school->city->country_id; // Assuming City has country relationship
        } else {
             // Handle case where school might not be found, though validation should prevent this
             return back()->withErrors(['school_id' => 'Selected school not found.'])->withInput();
        }


        Airport::create($validated);

        return redirect()->route('admin.airports.index')->with('success', 'Airport created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Airport $airport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Airport $airport): View
    {
        $schools = School::orderBy('name')->pluck('name', 'id');
        $courseTypes = CourseType::orderBy('name')->get(['id', 'name']);
        $courses = Course::orderBy('name')->get(['id', 'name', 'school_id', 'course_type_id']);
        
        // Load relationships if not already loaded
        $airport->load(['restrictedCourseTypes', 'restrictedCourses']);

        return view('admin.airports.edit', compact('airport', 'schools', 'courseTypes', 'courses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Airport $airport): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'school_id' => 'required|exists:schools,id',
            'arrival_price' => 'nullable|numeric|min:0',
            'departure_price' => 'nullable|numeric|min:0',
            'arrival_price_2026' => 'nullable|numeric|min:0',
            'departure_price_2026' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        $validated['active'] = $request->has('active');

        // If school_id changed, update city_id and country_id
        if ($airport->school_id != $validated['school_id']) {
            $school = School::find($validated['school_id']);
             if ($school) {
                $validated['city_id'] = $school->city_id;
                $validated['country_id'] = $school->city->country_id;
            } else {
                 return back()->withErrors(['school_id' => 'Selected school not found.'])->withInput();
            }
        }


        $airport->update($validated);

        // Sync restrictions
        $airport->restrictedCourseTypes()->sync($request->input('restricted_course_type_ids', []));
        $airport->restrictedCourses()->sync($request->input('restricted_course_ids', []));

        return redirect()->route('admin.airports.index')->with('success', 'Airport updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Airport $airport): RedirectResponse
    {
        $airport->delete();
        return redirect()->route('admin.airports.index')->with('success', 'Airport deleted successfully.');
    }

    /**
     * Update the order of airports via AJAX.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'airports' => 'required|array',
            'airports.*.id' => 'required|exists:airports,id',
            'airports.*.order' => 'required|integer|min:0'
        ]);

        foreach ($request->airports as $airportData) {
            Airport::where('id', $airportData['id'])->update(['order' => $airportData['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Filter airports by country, city, and/or school via AJAX.
     */
    public function filter(Request $request)
    {
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');
        $schoolId = $request->query('school_id');

        $query = Airport::with('school')->orderBy('order')->orderBy('name');

        if (!empty($schoolId)) {
            $query->where('school_id', $schoolId);
        }
        if (!empty($cityId)) {
            $query->where('city_id', $cityId);
        }
        if (!empty($countryId)) {
            $query->where('country_id', $countryId);
        }

        $airports = $query->paginate(20);

        $rowsHtml = view('admin.airports._index_rows', compact('airports'))->render();
        $paginationHtml = view('admin.airports._pagination', compact('airports'))->render();

        return response()->json([
            'rowsHtml' => $rowsHtml,
            'paginationHtml' => $paginationHtml,
        ]);
    }

    /**
     * Get cities for a given country (or all active cities if not specified).
     */
    public function getCitiesForCountry(Request $request)
    {
        $countryId = $request->query('country_id');

        $citiesQuery = City::where('active', true)->orderBy('name');
        if (!empty($countryId)) {
            $citiesQuery->where('country_id', $countryId);
        }
        $cities = $citiesQuery->get(['id', 'name']);

        return response()->json($cities);
    }

    /**
     * Get schools for a given country and/or city (active only).
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

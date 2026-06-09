<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\City; // Import City model
use App\Models\Country; // Import Country model
use App\Models\Currency; // Import Currency model
use App\Models\Airport; // Import Airport model
use App\Models\CourseType; // Import CourseType model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $schools = School::with(['city.country', 'currency'])->orderBy('order')->orderBy('name')->paginate(20);
        // Preload countries and cities for filters (active only)
        $countries = Country::where('active', true)->orderBy('name')->get(['id', 'name']);
        $cities = City::where('active', true)->orderBy('name')->get(['id', 'name']);
        return view('admin.schools.index', compact('schools', 'countries', 'cities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $cities = City::where('active', true)->orderBy('name')->pluck('name', 'id');
        $currencies = Currency::where('active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.schools.create', compact('cities', 'currencies'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'currency_id' => 'required|exists:currencies,id',
            'registration_fee' => 'nullable|numeric|min:0',
            'registration_fee_2026' => 'nullable|numeric|min:0',
            'accommodation_fee' => 'nullable|numeric|min:0',
            'accommodation_fee_2026' => 'nullable|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'bank_charges_2026' => 'nullable|numeric|min:0',
            'books_fee' => 'nullable|numeric|min:0',
            'books_fee_2026' => 'nullable|numeric|min:0',
            'books_weeks' => 'nullable|integer|min:1',
            'books_weeks_2026' => 'nullable|integer|min:1',
            'insurance_fee_per_week' => 'nullable|numeric|min:0',
            'insurance_fee_per_week_2026' => 'nullable|numeric|min:0',
            'courier_fee' => 'nullable|numeric|min:0',
            'courier_fee_2026' => 'nullable|numeric|min:0',
            'courier_fee_enabled' => 'required|in:0,1',
            'guardianship_fee_per_week' => 'nullable|numeric|min:0',
            'guardianship_fee_per_week_2026' => 'nullable|numeric|min:0',
            'guardianship_fee_age' => 'nullable|integer|min:0',
            'custodianship_fee' => 'nullable|numeric|min:0',
            'custodianship_fee_2026' => 'nullable|numeric|min:0',
            'custodianship_fee_age' => 'nullable|integer|min:0',
            'christmas_fee_per_week' => 'nullable|numeric|min:0',
            'christmas_fee_per_week_2026' => 'nullable|numeric|min:0',
            'christmas_start_date' => 'nullable|date',
            'christmas_end_date' => 'nullable|date|after_or_equal:christmas_start_date',
            'christmas_start_date_2026' => 'nullable|date',
            'christmas_end_date_2026' => 'nullable|date|after_or_equal:christmas_start_date_2026',
            'extra_accommodation_weeks' => 'nullable|integer|min:0|max:4',
            'summer_fee_per_week' => 'nullable|numeric|min:0',
            'summer_fee_per_week_2026' => 'nullable|numeric|min:0',
            'summer_start_date' => 'nullable|date',
            'summer_end_date' => 'nullable|date|after_or_equal:summer_start_date',
            'summer_fee_weeks_off' => 'nullable|integer|min:0',
            'summer_fee_weeks_off_2026' => 'nullable|integer|min:0',
            'summer_fee_note' => 'nullable|string',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);
        $validated['active'] = $request->input('active') == '1';

        // Convert courier_fee_enabled to boolean
        $validated['courier_fee_enabled'] = (bool) $request->input('courier_fee_enabled', 0);

        // Ensure extra_accommodation_weeks is properly handled
        if ($request->has('extra_accommodation_weeks')) {
            $validated['extra_accommodation_weeks'] = (int) $request->input('extra_accommodation_weeks');
        } else {
            $validated['extra_accommodation_weeks'] = 0; // Default to 0 if not provided
        }

        // Set default order if not provided
        if (!$request->has('order')) {
            $validated['order'] = 0;
        }

        School::create($validated);

        return redirect()->route('admin.schools.index')->with('success', 'School created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school): View
    {
        $cities = City::where('active', true)->orderBy('name')->pluck('name', 'id');
        $currencies = Currency::where('active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.schools.edit', compact('school', 'cities', 'currencies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'currency_id' => 'required|exists:currencies,id',
            'registration_fee' => 'nullable|numeric|min:0',
            'registration_fee_2026' => 'nullable|numeric|min:0',
            'accommodation_fee' => 'nullable|numeric|min:0',
            'accommodation_fee_2026' => 'nullable|numeric|min:0',
            'bank_charges' => 'nullable|numeric|min:0',
            'bank_charges_2026' => 'nullable|numeric|min:0',
            'books_fee' => 'nullable|numeric|min:0',
            'books_fee_2026' => 'nullable|numeric|min:0',
            'books_weeks' => 'nullable|integer|min:1',
            'books_weeks_2026' => 'nullable|integer|min:1',
            'insurance_fee_per_week' => 'nullable|numeric|min:0',
            'insurance_fee_per_week_2026' => 'nullable|numeric|min:0',
            'courier_fee' => 'nullable|numeric|min:0',
            'courier_fee_2026' => 'nullable|numeric|min:0',
            'courier_fee_enabled' => 'required|in:0,1',
            'guardianship_fee_per_week' => 'nullable|numeric|min:0',
            'guardianship_fee_per_week_2026' => 'nullable|numeric|min:0',
            'guardianship_fee_age' => 'nullable|integer|min:0',
            'custodianship_fee' => 'nullable|numeric|min:0',
            'custodianship_fee_2026' => 'nullable|numeric|min:0',
            'custodianship_fee_age' => 'nullable|integer|min:0',
            'christmas_fee_per_week' => 'nullable|numeric|min:0',
            'christmas_fee_per_week_2026' => 'nullable|numeric|min:0',
            'christmas_start_date' => 'nullable|date',
            'christmas_end_date' => 'nullable|date|after_or_equal:christmas_start_date',
            'christmas_start_date_2026' => 'nullable|date',
            'christmas_end_date_2026' => 'nullable|date|after_or_equal:christmas_start_date_2026',
            'extra_accommodation_weeks' => 'nullable|integer|min:0|max:4',
            'summer_fee_per_week' => 'nullable|numeric|min:0',
            'summer_fee_per_week_2026' => 'nullable|numeric|min:0',
            'summer_start_date' => 'nullable|date',
            'summer_end_date' => 'nullable|date|after_or_equal:summer_start_date',
            'summer_fee_weeks_off' => 'nullable|integer|min:0',
            'summer_fee_weeks_off_2026' => 'nullable|integer|min:0',
            'summer_fee_note' => 'nullable|string',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
            'social_accounts.*.platform' => 'nullable|in:instagram,facebook,tiktok,linkedin,youtube,x,website',
            'social_accounts.*.url' => 'nullable|url|regex:/^https:\/\//',
        ]);
        $validated['active'] = $request->input('active') == '1';

        // Convert courier_fee_enabled to boolean
        $validated['courier_fee_enabled'] = (bool) $request->input('courier_fee_enabled', 0);

        // Ensure extra_accommodation_weeks is properly handled
        if ($request->has('extra_accommodation_weeks')) {
            $validated['extra_accommodation_weeks'] = (int) $request->input('extra_accommodation_weeks');
        } else {
             $validated['extra_accommodation_weeks'] = 0; // Default to 0 if not provided
        }

        DB::transaction(function () use ($school, $validated, $request) {
            $school->update($validated);

            // Sync Social Accounts
            $school->socialAccounts()->delete();

            if ($request->has('social_accounts')) {
                $socialAccounts = $request->input('social_accounts');
                foreach ($socialAccounts as $index => $account) {
                    if (!empty($account['platform']) && !empty($account['url'])) {
                        $school->socialAccounts()->create([
                            'platform' => $account['platform'],
                            'url' => $account['url'],
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.schools.index')->with('success', 'School updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school): RedirectResponse
    {
        $school->delete();

        return redirect()->route('admin.schools.index')->with('success', 'School deleted successfully.');
    }

    /**
     * Filter schools by city (AJAX).
     */
    public function filter(Request $request)
    {
        $cityId = $request->query('city_id');
        $schools = School::where('city_id', $cityId)
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($schools);
    }

    /**
     * Get cities for a specific country (AJAX).
     * Used for hierarchical dropdowns (Country -> City -> School)
     */
    public function getCitiesForCountry(Request $request)
    {
        $countryId = $request->query('country_id');
        
        if (!$countryId) {
            return response()->json([]);
        }

        $cities = City::where('country_id', $countryId)
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($cities);
    }

    /**
     * Update the order of schools.
     */
    public function updateOrder(Request $request)
    {
        $order = $request->input('order');

        if ($order) {
            foreach ($order as $index => $id) {
                School::where('id', $id)->update(['order' => $index]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Get details for a specific school (AJAX).
     * Returns Christmas dates, fees, and other school-specific settings.
     */
    public function getDetails(School $school)
    {
        // Return relevant details as JSON
        return response()->json([
            'christmas_start_date' => $school->christmas_start_date ? $school->christmas_start_date->format('Y-m-d') : null,
            'christmas_end_date' => $school->christmas_end_date ? $school->christmas_end_date->format('Y-m-d') : null,
            'extra_accommodation_weeks' => $school->extra_accommodation_weeks,
            'courier_fee_enabled' => $school->courier_fee_enabled,
            'insurance_fee_per_week' => $school->insurance_fee_per_week,
            'insurance_fee_per_week_2026' => $school->insurance_fee_per_week_2026,
        ]);
    }

    /**
     * Get airports for a specific school (AJAX).
     */
    public function getAirports(School $school)
    {
        // Fetch airports associated with this school via the pivot table
        // Include restriction data (pivot columns or related data if needed)
        // We need restricted_course_type_ids and restricted_course_ids
        // The Airport model should have methods or scopes to get these, or we can load them here.
        
        // Assuming Airport has relationships 'restrictedCourseTypes' and 'restrictedCourses' 
        // defined in the Airport model, but they are ManyToMany.
        // For the frontend, we need arrays of IDs.
        
        $airports = $school->airports()
            ->with(['restrictedCourseTypes:id', 'restrictedCourses:id'])
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
            
        // Transform the collection to include flat arrays of IDs for easier frontend checking
        $transformed = $airports->map(function ($airport) {
            return [
                'id' => $airport->id,
                'name' => $airport->name,
                'restricted_course_type_ids' => $airport->restrictedCourseTypes->pluck('id')->toArray(),
                'restricted_course_ids' => $airport->restrictedCourses->pluck('id')->toArray(),
            ];
        });

        return response()->json($transformed);
    }

    /**
     * Get course types for a specific school via AJAX.
     */
    public function getCourseTypes(School $school)
    {
        $courseTypes = CourseType::whereHas('courses', function ($query) use ($school) {
                $query->where('school_id', $school->id)
                      ->where('active', true);
            })
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($courseTypes);
    }
}

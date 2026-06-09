<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Addon;
use App\Models\City; // Import City
use App\Models\Country; // Import Country
use App\Models\Course;
use App\Models\CoursePrice; // Import CoursePrice
use App\Models\Region; // Import Region
use App\Models\School;
use App\Services\FeeCalculatorService; // Import the service
use Carbon\Carbon; // Import Carbon for date handling
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\View\View; // Import View
use Illuminate\Http\RedirectResponse; // Import RedirectResponse

class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() // Keep for potential future use (listing saved quotes)
    {
        // Placeholder for listing saved quotations
        // return view('admin.quotations.index'); // Assuming a view exists or will be created
        // For now, redirect to the create form as index is not implemented
        return redirect()->route('admin.quotations.create');
    }

    /**
     * Show the form for creating a new quotation (the calculator interface).
     */
    public function create(): View // Added return type
    {
        // Fetch data needed for the form dropdowns/options
        $countries = Country::where('active', true)->orderBy('name')->pluck('name', 'id');
        // Get cities with country_id for filtering
        $cities = City::where('active', true)->orderBy('name')->get(['id', 'name', 'country_id']);
        // Get schools with city_id for filtering
        $schools = School::where('active', true)->orderBy('name')->get(['id', 'name', 'city_id']);
        // Get courses with school_id for filtering
        $courses = Course::where('active', true)->orderBy('name')->get(['id', 'name', 'school_id']);
        // Get accommodations with school_id for filtering
        $accommodations = Accommodation::where('active', true)->orderBy('name')->get(['id', 'name', 'school_id']);
        // Get global addons and potentially school-specific ones if needed later
        $addons = Addon::where('active', true)->whereNull('school_id')->orderBy('name')->get();
        $regions = Region::where('active', true)->orderBy('name')->pluck('name', 'id'); // Fetch Regions
        // Fetch and group course prices by course_id
        $allCoursePrices = CoursePrice::select('course_id', 'min_weeks', 'max_weeks')->get()->groupBy('course_id');
        // TODO: Add Airport Transfer addons specifically if they are stored as Addons

        // Get the first school to use as default for the Christmas accommodation settings
        $school = School::where('active', true)->first();

        // Log the school's details only if a school is found
        if ($school) {
            \Illuminate\Support\Facades\Log::info('QuotationController create method - Default school found:', [
                'school_id' => $school->id,
                'extra_accommodation_weeks' => $school->extra_accommodation_weeks
            ]);
        } else {
             \Illuminate\Support\Facades\Log::warning('QuotationController create method - No active school found to use as default.');
        }


        return view('admin.quotations.create', compact(
            'countries',
            'cities',
            'schools',
            'courses',
            'accommodations',
            'addons',
            'regions', // Pass regions
            'allCoursePrices', // Pass course prices
            'school' // Pass the default school
        ));
    }

     /**
      * Handle the calculation request.
      * This replaces the standard 'store' for now as we aren't saving yet.
      */
     public function calculate(Request $request, FeeCalculatorService $calculator) // Removed return type hint for now
     {
         // --- 1. Validation (Add comprehensive validation based on requirements) ---
         try {
             $validatedData = $request->validate([
             'school_id' => 'required|exists:schools,id',
             'region_id' => 'required|exists:regions,id', // Make region required
             'course_id' => 'required|exists:courses,id',
             'course_start_date' => 'required|date|date_format:Y-m-d',
             'course_duration_weeks' => 'required|integer|min:1',
             'accommodation_id' => 'nullable|exists:accommodations,id',
             'accommodation_duration_weeks' => 'nullable|required_with:accommodation_id|integer|min:1',
             'client_birthday' => 'nullable|date|date_format:Y-m-d',
             'client_nationality_country_id' => 'nullable|exists:countries,id', // Assuming countries table exists
             'selected_addons' => 'nullable|array',
             'selected_addons.*' => 'sometimes|boolean', // Basic check, might need more complex validation if addons have quantities/options
             'arrival_transfer_airport_id' => 'nullable|exists:airports,id', // Validate arrival airport
             'departure_transfer_airport_id' => 'nullable|exists:airports,id', // Validate departure airport
             // Add validation for addon details if they have quantities, etc.
         ]);
         } catch (\Illuminate\Validation\ValidationException $e) {
             // For AJAX requests, return validation errors as JSON
             if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                 return response()->json(['errors' => $e->errors()], 422);
             }

             // For regular requests, let Laravel handle it (will redirect back with errors)
             throw $e;
         }

         // --- 2. Prepare Parameters for the Service ---
         // The validated data largely matches the service's expected format
         $quoteParams = $validatedData;

         // Ensure accommodation duration is null if accommodation_id is null
         if (empty($validatedData['accommodation_id'])) {
             $quoteParams['accommodation_duration_weeks'] = null;
         }

         // Get the school to check for extra accommodation weeks
         $school = School::findOrFail($validatedData['school_id']);

         // --- Explicitly add optional Christmas parameters from the request ---
         if ($request->input('christmas_accommodation') === 'yes') {
             $quoteParams['christmas_accommodation'] = true; // Service expects boolean

             // Log the Christmas accommodation details from the request
             \Illuminate\Support\Facades\Log::info('Christmas accommodation requested', [
                 'accommodation_id' => $validatedData['accommodation_id'] ?? null, // Use validated ID if available
                 'school_extra_weeks' => $school->extra_accommodation_weeks,
                 'request_christmas_accommodation' => $request->input('christmas_accommodation'),
                 'request_christmas_extra_weeks' => $request->input('christmas_extra_weeks')
             ]);

             // Add Christmas extra weeks if provided in the request
             if ($request->filled('christmas_extra_weeks') && is_numeric($request->input('christmas_extra_weeks'))) {
                  $quoteParams['christmas_extra_weeks'] = (int) $request->input('christmas_extra_weeks');
             }

             // Add Christmas dates if provided in the request (Service will use school dates otherwise)
             if ($request->filled('christmas_start_date')) {
                  $quoteParams['christmas_start_date'] = $request->input('christmas_start_date');
             }
              if ($request->filled('christmas_end_date')) {
                  $quoteParams['christmas_end_date'] = $request->input('christmas_end_date');
             }
         } else {
              // Ensure these are not accidentally set if 'no' or not present
              $quoteParams['christmas_accommodation'] = false;
              unset($quoteParams['christmas_extra_weeks']);
              unset($quoteParams['christmas_start_date']);
              unset($quoteParams['christmas_end_date']);
         }



         // Format selected_addons if necessary (e.g., if form sends 'on' instead of true)
         // Example: Convert checkbox values if needed
         if (isset($quoteParams['selected_addons'])) {
             $formattedAddons = [];
             foreach ($quoteParams['selected_addons'] as $id => $value) {
                 // Assuming form sends addon ID as key and 'on' or 1 for checked
                 if ($value) {
                     $formattedAddons[$id] = true; // Service expects true or array for details
                     // If addons could have quantities/options, adjust here:
                     // $formattedAddons[$id] = ['quantity' => $request->input("addon_qty_{$id}")];
                 }
             }
             $quoteParams['selected_addons'] = $formattedAddons;
         }

         // Explicitly add airport transfer IDs if they exist in validated data
         if (isset($validatedData['arrival_transfer_airport_id'])) {
             $quoteParams['arrival_transfer_airport_id'] = $validatedData['arrival_transfer_airport_id'];
         }
         if (isset($validatedData['departure_transfer_airport_id'])) {
             $quoteParams['departure_transfer_airport_id'] = $validatedData['departure_transfer_airport_id'];
         }


         // --- 3. Call the Fee Calculator Service ---
         try {
             Log::info('Calculating quote with params:', $quoteParams);
             $costBreakdown = $calculator->calculateQuote($quoteParams);

             // Add course start date to the cost breakdown for display
             $costBreakdown['course_start_date'] = $validatedData['course_start_date'];

             Log::info('Calculation result:', $costBreakdown);

             // Check if there are errors in the calculation
             if (!empty($costBreakdown['errors'])) {
                 Log::warning('Calculation completed with errors:', $costBreakdown['errors']);
             }
         } catch (\Exception $e) {
             Log::error('Exception in QuotationController::calculate: ' . $e->getMessage(), [
                 'exception' => $e,
                 'params' => $quoteParams
             ]);

             // Create a basic cost breakdown with the error
             $costBreakdown = [
                 'items' => [],
                 'discounts' => [],
                 'subtotals' => [
                     'tuition' => 0,
                     'accommodation' => 0,
                     'fees' => 0,
                     'addons' => 0,
                 ],
                 'total' => 0,
                 'currency_code' => 'GBP',
                 'currency_symbol' => '£',
                 'errors' => ['An unexpected error occurred: ' . $e->getMessage()]
             ];
         }

         // --- 4. Return the View with Results (and original input) ---
         // Check if this is an AJAX request
         if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
             \Illuminate\Support\Facades\Log::info('AJAX request received for calculation, returning JSON');
             // For AJAX requests, return the cost breakdown as JSON
             return response()->json(['costBreakdown' => $costBreakdown]);
         }

         // For regular (non-AJAX) requests, fetch all data again for the form
         $countries = Country::where('active', true)->orderBy('name')->pluck('name', 'id');
         $cities = City::where('active', true)->orderBy('name')->get(['id', 'name', 'country_id']);
         $schools = School::where('active', true)->orderBy('name')->get(['id', 'name', 'city_id']);
         $courses = Course::where('active', true)->orderBy('name')->get(['id', 'name', 'school_id']);
         $accommodations = Accommodation::where('active', true)->orderBy('name')->get(['id', 'name', 'school_id']);
         $addons = Addon::where('active', true)->whereNull('school_id')->orderBy('name')->get();
         $regions = Region::where('active', true)->orderBy('name')->pluck('name', 'id'); // Fetch Regions again
         // Fetch and group course prices by course_id again
         $allCoursePrices = CoursePrice::select('course_id', 'min_weeks', 'max_weeks')->get()->groupBy('course_id');

         return view('admin.quotations.create', compact(
             'countries',
             'cities',
             'schools',
             'courses',
             'accommodations',
             'addons',
             'regions', // Pass regions again
             'allCoursePrices', // Pass course prices again
             'costBreakdown' // Pass the results to the view
         ))->withInput($request->input()); // Repopulate form with old input
     }


    /**
     * Store a newly created resource in storage. (Placeholder for saving quotes)
     */
    public function store(Request $request) // Keep for future use
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Addon;
use App\Models\City; // Import City
use App\Models\Country; // Import Country
use App\Models\Course;
use App\Models\CoursePrice; // Import CoursePrice
use App\Models\CourseJuniorSetting;
use App\Models\CourseType; // Import CourseType
use App\Models\CourseSchedule; // Import CourseSchedule
use App\Models\Region; // Import Region
use App\Models\School;
use App\Models\Setting;
use App\Models\DiscountRule; // Import DiscountRule
use App\Services\FeeCalculatorService; // Import the service
use Carbon\Carbon; // Import Carbon for date handling
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\View\View; // Import View
use Illuminate\Http\RedirectResponse; // Import RedirectResponse
use App\Models\ExchangeName; // Import ExchangeName

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
        $countries = Country::where('active', true)->orderBy('order')->orderBy('name')->pluck('name', 'id');
        // Get cities with country_id for filtering
        $cities = City::where('active', true)->orderBy('order')->orderBy('name')->get(['id', 'name', 'country_id']);
        // Get schools with city_id for filtering - ordered by custom order then name
        $schools = School::with('socialAccounts')->where('active', true)->orderBy('order')->orderBy('name')->get(['id', 'name', 'city_id']);
        // Get course types for filtering - ordered by custom order then name
        $courseTypes = CourseType::where('active', true)->orderBy('order')->orderBy('name')->get(['id', 'name']);
        // Get courses with school_id, course_type_id, pricing_type, and category for filtering - ordered by custom order then name
        // Eager load detailLinks and juniorDetailLinks
        $courses = Course::with([
            'detailLinks' => function($q) { $q->orderBy('sort_order')->orderBy('id'); },
            'juniorDetailLinks' => function($q) { $q->orderBy('sort_order')->orderBy('id'); }
        ])
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get(['id', 'name', 'school_id', 'course_type_id', 'pricing_type', 'category']);
            
        // Prepare Course Detail Links Map for JS
        $courseDetailLinksMap = $courses->mapWithKeys(function ($course) {
            return [$course->id => $course->detailLinks->map(fn($l) => [
                'url' => $l->url,
                'button_text' => $l->button_text
            ])];
        })->toArray();

        // Prepare Junior Course Detail Links Map for JS
        $juniorCourseDetailLinksMap = $courses->mapWithKeys(function ($course) {
            return [$course->id => $course->juniorDetailLinks->map(fn($l) => [
                'url' => $l->url,
                'button_text' => $l->button_text
            ])];
        })->toArray();

        // Get course details button text setting
        $courseDetailsButtonText = Setting::first()?->course_details_button_text ?? 'Course details';

        // Get accommodations with school_id for filtering - ordered by custom order then name
        // Eager load restrictions
        $accommodations = Accommodation::with(['restrictedCourseTypes:id', 'restrictedCourses:id'])
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get([
            'id', 
            'name', 
            'school_id',
            'private_bathroom_enabled',
            'private_bathroom_fee',
            'dietary_supplement_enabled',
            'dietary_supplement_fee',
            'private_bathroom_enabled_2026',
            'private_bathroom_fee_2026',
            'dietary_supplement_enabled_2026',
            'dietary_supplement_fee_2026',
            'other_charge_enabled',
            'other_charge_name',
            'other_charge_amount',
            'summer_fee_per_week',
            'summer_start_date',
            'summer_end_date',
            'summer_fee_note',
            'summer_fee_per_week_2026',
            'summer_start_date_2026',
            'summer_end_date_2026',
            'summer_fee_note_2026',
            'requires_christmas_supplement'
        ]);
        // Get global addons and potentially school-specific ones if needed later
        $addons = Addon::where('active', true)->whereNull('school_id')->orderBy('name')->get();

        // Filter regions based on assigned user regions
        $user = auth()->user();
        if ($user && $user->regions()->exists()) {
            $regions = $user->regions()->where('regions.active', true)->orderBy('regions.order')->orderBy('regions.name')->pluck('regions.name', 'regions.id');
        } else {
            $regions = Region::where('active', true)->orderBy('order')->orderBy('name')->pluck('name', 'id');
        }
        
        // Fetch and group course prices by course_id
        $allCoursePrices = CoursePrice::select('course_id', 'min_weeks', 'max_weeks')->get()->groupBy('course_id');
        
        // Fetch and group course schedules by course_id for fixed schedule courses
        $allCourseSchedules = CourseSchedule::select('course_id', 'start_date', 'duration_weeks', 'fixed_price', 'active')
            ->where('active', true)
            ->get()
            ->groupBy('course_id');
        // TODO: Add Airport Transfer addons specifically if they are stored as Addons

        $juniorCourseAccommodationMap = Course::where('active', true)
            ->where('category', 'junior')
            ->with(['juniorAccommodations' => function ($query) {
                $query->select('accommodations.id');
            }])
            ->get()
            ->mapWithKeys(function ($course) {
                $ids = $course->juniorAccommodations->pluck('id')->filter()->values()->all();
                return [$course->id => $ids];
            })
            ->toArray();

        $juniorCourseSettings = CourseJuniorSetting::with(['course' => function ($query) {
            $query->select('id', 'category', 'active');
        }])
            ->get()
            ->filter(function ($setting) {
                return $setting->course && $setting->course->active && $setting->course->category === 'junior';
            })
            ->mapWithKeys(function ($setting) {
                return [
                    $setting->course_id => [
                        'start_date' => optional($setting->start_date)->format('Y-m-d'),
                        'end_date' => optional($setting->end_date)->format('Y-m-d'),
                        'min_age' => $setting->min_age,
                        'max_age' => $setting->max_age,
                        'min_weeks' => $setting->min_weeks,
                        'max_weeks' => $setting->max_weeks,
                        'includes_accommodation' => (bool) $setting->includes_accommodation,
                        'buy_weeks_only' => (bool) $setting->buy_weeks_only,
                    ],
                ];
            })
            ->toArray();

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

        // Determine which view to use based on the current route
        $viewName = request()->routeIs('calculator.*') ? 'calculator.create' : 'admin.quotations.create';

        // Get active nationality-specific discounts with proper date validation
        $today = Carbon::today();
        $nationalityDiscounts = DiscountRule::where('active', true)
            ->whereNotNull('nationality')
            ->whereNotNull('nationality_title')
            ->where(function ($query) use ($today) {
                $query->where(function ($subQuery) use ($today) {
                    // Check valid_from_date and valid_to_date
                    $subQuery->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('valid_from_date')
                                  ->orWhere('valid_from_date', '<=', $today);
                    })->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('valid_to_date')
                                  ->orWhere('valid_to_date', '>=', $today);
                    });
                })->where(function ($subQuery) use ($today) {
                    // Check quotation_extraction_date_from and quotation_extraction_date_to
                    $subQuery->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('quotation_extraction_date_from')
                                  ->orWhere('quotation_extraction_date_from', '<=', $today);
                    })->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('quotation_extraction_date_to')
                                  ->orWhere('quotation_extraction_date_to', '>=', $today);
                    });
                });
            })
            ->get(['id', 'nationality', 'nationality_title', 'combinable']);

        $exchangeNamesQuery = ExchangeName::orderBy('order')->orderBy('name');
        
        // Filter exchange names based on user regions
        if (!$user || !$user->isAdmin()) {
             if ($user && $user->regions()->exists()) {
                 $userRegionIds = $user->regions()->pluck('regions.id');
                 $exchangeNamesQuery->whereHas('regions', function($q) use ($userRegionIds) {
                     $q->whereIn('regions.id', $userRegionIds);
                 });
             } else {
                 // User has no specific regions (sees all regions), but Exchange Names must have regions to be visible to non-admins
                 $exchangeNamesQuery->has('regions');
             }
        }
        $exchangeNames = $exchangeNamesQuery->get(['name', 'label']);

        return view($viewName, compact(
            'countries',
            'cities',
            'schools',
            'courseTypes',
            'courses',
            'accommodations',
            'addons',
            'regions', // Pass regions
            'allCoursePrices', // Pass course prices
            'allCourseSchedules', // Pass course schedules
            'school', // Pass the default school
            'nationalityDiscounts', // Pass nationality-specific discounts
            'exchangeNames',
            'juniorCourseAccommodationMap',
            'juniorCourseSettings',
            'courseDetailLinksMap',
            'juniorCourseDetailLinksMap',
            'courseDetailsButtonText'
        ));
    }

    /**
     * Check applicable nationality discounts dynamically based on inputs.
     */
    public function checkNationalityDiscounts(Request $request)
    {
        $request->validate([
            'region_id' => 'required|integer',
            'school_id' => 'required|integer',
            'course_id' => 'nullable|integer',
            'course_type_id' => 'nullable|integer',
            'accommodation_id' => 'nullable|integer',
            'course_start_date' => 'required|date',
            'course_duration_weeks' => 'nullable|integer',
            'nationality_country_id' => 'nullable|integer',
        ]);

        $regionId = (int) $request->input('region_id');
        $schoolId = (int) $request->input('school_id');
        $courseId = $request->input('course_id');
        $courseTypeId = $request->input('course_type_id');
        $accommodationId = $request->input('accommodation_id');
        $startDate = Carbon::parse($request->input('course_start_date'));
        $durationWeeks = $request->input('course_duration_weeks');
        $nationalityCountryId = $request->input('nationality_country_id');

        $today = Carbon::today();

        // Candidate nationality discounts (active with nationality fields)
        $candidateRules = DiscountRule::where('active', true)
            ->whereNotNull('nationality')
            ->whereNotNull('nationality_title')
            ->where(function ($query) use ($today) {
                $query->where(function ($subQuery) use ($today) {
                    $subQuery->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('valid_from_date')
                                  ->orWhere('valid_from_date', '<=', $today);
                    })->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('valid_to_date')
                                  ->orWhere('valid_to_date', '>=', $today);
                    });
                })->where(function ($subQuery) use ($today) {
                    $subQuery->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('quotation_extraction_date_from')
                                  ->orWhere('quotation_extraction_date_from', '<=', $today);
                    })->where(function ($dateQuery) use ($today) {
                        $dateQuery->whereNull('quotation_extraction_date_to')
                                  ->orWhere('quotation_extraction_date_to', '>=', $today);
                    });
                });
            })
            ->where(function ($query) use ($regionId) {
                $query->whereNull('region_id')->orWhere('region_id', $regionId);
            })
            ->where(function ($query) use ($schoolId) {
                $query->whereNull('school_id')->orWhere('school_id', $schoolId);
            })
            ->orderBy('priority', 'asc')
            ->get();

        $applicable = [];

        foreach ($candidateRules as $rule) {
            // Check nationality condition if provided on the rule
            if ($rule->country_id && $nationalityCountryId && (int)$rule->country_id !== (int)$nationalityCountryId) {
                continue;
            }

            // Course-specific checks
            if ($rule->course_id && $courseId && (int)$rule->course_id !== (int)$courseId) {
                continue;
            }
            if ($rule->course_type_id && $courseTypeId && (int)$rule->course_type_id !== (int)$courseTypeId) {
                continue;
            }

            // Accommodation check
            if ($rule->accommodation_id && $accommodationId && (int)$rule->accommodation_id !== (int)$accommodationId) {
                continue;
            }

            // Weeks condition (if provided); durationWeeks may be null until selected
            if ($durationWeeks) {
                if ($rule->min_course_weeks && $durationWeeks < $rule->min_course_weeks) {
                    continue;
                }
                if ($rule->max_course_weeks && $durationWeeks > $rule->max_course_weeks) {
                    continue;
                }
            }

            // Date overlap conditions: start date must overlap with either range
            $validFrom = $rule->valid_from_date ? Carbon::parse($rule->valid_from_date) : null;
            $validTo = $rule->valid_to_date ? Carbon::parse($rule->valid_to_date) : null;
            $extractFrom = $rule->quotation_extraction_date_from ? Carbon::parse($rule->quotation_extraction_date_from) : null;
            $extractTo = $rule->quotation_extraction_date_to ? Carbon::parse($rule->quotation_extraction_date_to) : null;

            $overlapsValidity = (!$validFrom || $startDate->gte($validFrom)) && (!$validTo || $startDate->lte($validTo));
            $overlapsExtraction = (!$extractFrom || $startDate->gte($extractFrom)) && (!$extractTo || $startDate->lte($extractTo));

            if (!$overlapsValidity && !$overlapsExtraction) {
                continue;
            }

            $applicable[] = [
                'id' => $rule->id,
                'title' => $rule->nationality_title,
                'combinable' => (bool)$rule->combinable,
            ];
        }

        return response()->json([
            'applicable' => $applicable,
        ]);
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
             'course_type_id' => 'required|exists:course_types,id',
             'course_id' => 'required|exists:courses,id',
             'course_start_date' => 'required|date|date_format:Y-m-d',
             'course_duration_weeks' => 'required|integer|min:1',
             'accommodation_id' => 'nullable|exists:accommodations,id',
             'accommodation_duration_weeks' => [
                 'nullable',
                 'required_with:accommodation_id',
                 'integer',
                 'min:1',
                 function ($attribute, $value, $fail) use ($request) {
                     // Calculate total combined course duration
                     $firstCourseDuration = (int) $request->input('course_duration_weeks', 0);
                     $secondCourseDuration = (int) $request->input('second_course_duration_weeks', 0);
                     $totalCourseDuration = $firstCourseDuration + $secondCourseDuration;
                     
                     if ($value > $totalCourseDuration) {
                         $fail('Accommodation duration cannot exceed course duration.');
                     }
                 }
             ],
             'client_birthday' => 'nullable|date|date_format:Y-m-d',
             'client_nationality_country_id' => 'nullable|exists:countries,id', // Assuming countries table exists
             'selected_addons' => 'nullable|array',
             'selected_addons.*' => 'sometimes|boolean', // Basic check, might need more complex validation if addons have quantities/options
             'arrival_transfer_airport_id' => 'nullable|exists:airports,id', // Validate arrival airport
             'departure_transfer_airport_id' => 'nullable|exists:airports,id', // Validate departure airport
             'courier_fee_option' => 'nullable|in:yes,no', // Add validation for courier fee option
             'private_bathroom' => 'nullable|boolean', // Add validation for private bathroom option
             'dietary_supplement' => 'nullable|boolean', // Add validation for dietary supplement option
             'insurance_selected' => 'nullable|boolean', // Add validation for insurance option
             'christmas_accommodation' => 'nullable|in:yes,no',
             'christmas_extra_weeks' => 'nullable|integer|min:0',
             // Second course validation
             'second_course_city' => 'nullable|exists:cities,id',
             'second_course_type' => 'nullable|exists:course_types,id',
             'second_course_id' => 'nullable|exists:courses,id',
             'second_course_start_date' => [
                 'nullable',
                 'date',
                 'date_format:Y-m-d',
                 function ($attribute, $value, $fail) use ($request) {
                     if ($value && $request->input('course_start_date') && $request->input('course_duration_weeks')) {
                         try {
                             $firstCourseStart = \Carbon\Carbon::parse($request->input('course_start_date'));
                             $firstCourseDuration = (int) $request->input('course_duration_weeks');
                             $secondCourseStart = \Carbon\Carbon::parse($value);
                             
                             // Ensure first course start is Monday (adjust if necessary)
                             if ($firstCourseStart->dayOfWeek !== 1) {
                                 $firstCourseStart = $firstCourseStart->next(\Carbon\Carbon::MONDAY);
                             }
                             
                             // Calculate first course end date (Friday of final week)
                             $firstCourseEnd = $firstCourseStart->copy()->addWeeks($firstCourseDuration - 1)->addDays(4);
                             
                             // Find the first Monday after first course completion
                             $minSecondCourseStart = $firstCourseEnd->copy()->addDay()->next(\Carbon\Carbon::MONDAY);
                             
                             // Check if second course start date is valid
                             if ($secondCourseStart->lt($minSecondCourseStart)) {
                                 $fail('The second course start date must be on or after ' . $minSecondCourseStart->format('Y-m-d') . ' (the first Monday after the first course completion).');
                             }
                             
                             // Ensure second course start date is a Monday
                             if ($secondCourseStart->dayOfWeek !== 1) {
                                 $fail('The second course start date must be a Monday.');
                             }
                         } catch (\Exception $e) {
                             $fail('Invalid date format for second course start date.');
                         }
                     }
                 }
             ],
             'second_course_duration_weeks' => 'nullable|integer|min:1',
             // Second accommodation validation
             'second_accommodation_id' => 'nullable|exists:accommodations,id',
             'second_accommodation_duration_weeks' => 'nullable|integer|min:1',
             'second_private_bathroom' => 'nullable|boolean',
             'second_dietary_supplement' => 'nullable|boolean',
             'second_christmas_accommodation' => 'nullable|in:true,false,1,0',
             'second_christmas_extra_weeks' => 'nullable|integer|min:0',
             'second_christmas_start_date' => 'nullable|date|date_format:Y-m-d',
             'second_christmas_end_date' => 'nullable|date|date_format:Y-m-d',
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

         // Additional validation for fixed schedule courses: ensure selected date matches a defined schedule and duration aligns
         try {
             $course = Course::find($validatedData['course_id']);
             if ($course && $course->pricing_type === 'fixed_schedule') {
                 $schedule = CourseSchedule::where('course_id', $course->id)
                     ->whereDate('start_date', $validatedData['course_start_date'])
                     ->where('active', true)
                     ->first();
                 if (!$schedule) {
                     $errorMsg = 'Selected start date is not available for this fixed schedule course.';
                     if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                         return response()->json(['errors' => ['course_start_date' => [$errorMsg]]], 422);
                     }
                     return back()->withErrors(['course_start_date' => $errorMsg])->withInput();
                 }
                 if ((int) $validatedData['course_duration_weeks'] !== (int) $schedule->duration_weeks) {
                     $errorMsg = 'Course duration must match the fixed schedule duration (' . (int) $schedule->duration_weeks . ' weeks).';
                     if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                         return response()->json(['errors' => ['course_duration_weeks' => [$errorMsg]]], 422);
                     }
                     return back()->withErrors(['course_duration_weeks' => $errorMsg])->withInput();
                 }
             }

             // Second course checks if provided
             if (!empty($validatedData['second_course_id']) && !empty($validatedData['second_course_start_date'])) {
                 $secondCourse = Course::find($validatedData['second_course_id']);
                 if ($secondCourse && $secondCourse->pricing_type === 'fixed_schedule') {
                     $secondSchedule = CourseSchedule::where('course_id', $secondCourse->id)
                         ->whereDate('start_date', $validatedData['second_course_start_date'])
                         ->where('active', true)
                         ->first();
                     if (!$secondSchedule) {
                         $errorMsg = 'Selected start date is not available for the fixed schedule second course.';
                         if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                             return response()->json(['errors' => ['second_course_start_date' => [$errorMsg]]], 422);
                         }
                         return back()->withErrors(['second_course_start_date' => $errorMsg])->withInput();
                     }
                     if (!empty($validatedData['second_course_duration_weeks']) && (int) $validatedData['second_course_duration_weeks'] !== (int) $secondSchedule->duration_weeks) {
                         $errorMsg = 'Second course duration must match the fixed schedule duration (' . (int) $secondSchedule->duration_weeks . ' weeks).';
                         if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                             return response()->json(['errors' => ['second_course_duration_weeks' => [$errorMsg]]], 422);
                         }
                         return back()->withErrors(['second_course_duration_weeks' => $errorMsg])->withInput();
                     }
                 }
             }
         } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::warning('Fixed schedule validation exception: ' . $e->getMessage());
         }

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

         // Add courier fee option if selected
         if (isset($validatedData['courier_fee_option']) && $validatedData['courier_fee_option'] === 'yes') {
             $quoteParams['courier_fee_option'] = true;
         } else {
             $quoteParams['courier_fee_option'] = false;
         }

         // Add private bathroom option if selected
         if ($request->input('private_bathroom') === 'on' || $request->input('private_bathroom') === '1' || $request->input('private_bathroom') === true) {
             $quoteParams['private_bathroom_option'] = true;
         } else {
             $quoteParams['private_bathroom_option'] = false;
         }

         // Add dietary supplement option if selected
         if ($request->input('dietary_supplement') === 'on' || $request->input('dietary_supplement') === '1' || $request->input('dietary_supplement') === true) {
             $quoteParams['dietary_supplement_option'] = true;
         } else {
             $quoteParams['dietary_supplement_option'] = false;
         }

         // Add insurance option if selected
         if ($request->input('insurance_selected') === 'on' || $request->input('insurance_selected') === '1' || $request->input('insurance_selected') === true) {
             $quoteParams['insurance_option'] = true;
         } else {
             $quoteParams['insurance_option'] = false;
         }

         // Add nationality discounts if selected
         if ($request->has('nationality_discounts') && is_array($request->input('nationality_discounts'))) {
             $quoteParams['nationality_discounts'] = $request->input('nationality_discounts');
         } else {
             $quoteParams['nationality_discounts'] = [];
         }

         // Add second course data if provided
         if (!empty($validatedData['second_course_id'])) {
             $quoteParams['second_course_city'] = $validatedData['second_course_city'] ?? null;
             $quoteParams['second_course_type'] = $validatedData['second_course_type'] ?? null;
             $quoteParams['second_course_id'] = $validatedData['second_course_id'];
             $quoteParams['second_course_start_date'] = $validatedData['second_course_start_date'] ?? null;
             $quoteParams['second_course_duration_weeks'] = $validatedData['second_course_duration_weeks'] ?? null;
         }

         // Add second accommodation data if provided
         if (!empty($validatedData['second_accommodation_id'])) {
             $quoteParams['second_accommodation_id'] = $validatedData['second_accommodation_id'];
             $quoteParams['second_accommodation_duration_weeks'] = $validatedData['second_accommodation_duration_weeks'] ?? null;
             
             // Add second accommodation options if selected
             if ($request->input('second_private_bathroom') === 'on' || $request->input('second_private_bathroom') === '1' || $request->input('second_private_bathroom') === true) {
                 $quoteParams['second_private_bathroom_option'] = true;
             } else {
                 $quoteParams['second_private_bathroom_option'] = false;
             }
             
             if ($request->input('second_dietary_supplement') === 'on' || $request->input('second_dietary_supplement') === '1' || $request->input('second_dietary_supplement') === true) {
                 $quoteParams['second_dietary_supplement_option'] = true;
             } else {
                 $quoteParams['second_dietary_supplement_option'] = false;
             }
             
             if ($request->input('second_christmas_accommodation') === 'on' || $request->input('second_christmas_accommodation') === '1' || $request->input('second_christmas_accommodation') === true || $request->input('second_christmas_accommodation') === 'true') {
                 $quoteParams['second_christmas_accommodation'] = true;
                 
                 // Add second Christmas extra weeks if provided in the request
                 if ($request->filled('second_christmas_extra_weeks') && is_numeric($request->input('second_christmas_extra_weeks'))) {
                     $quoteParams['second_christmas_extra_weeks'] = (int) $request->input('second_christmas_extra_weeks');
                 }
                 
                 // Add second Christmas dates if provided in the request
                 if ($request->filled('second_christmas_start_date')) {
                     $quoteParams['second_christmas_start_date'] = $request->input('second_christmas_start_date');
                 }
                 if ($request->filled('second_christmas_end_date')) {
                     $quoteParams['second_christmas_end_date'] = $request->input('second_christmas_end_date');
                 }
             } else {
                 $quoteParams['second_christmas_accommodation'] = false;
                 // Ensure these are not accidentally set if 'no' or not present
                 unset($quoteParams['second_christmas_extra_weeks']);
                 unset($quoteParams['second_christmas_start_date']);
                 unset($quoteParams['second_christmas_end_date']);
             }
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
        $countries = Country::where('active', true)->orderBy('order')->orderBy('name')->pluck('name', 'id');
        $cities = City::where('active', true)->orderBy('order')->orderBy('name')->get(['id', 'name', 'country_id']);
        $schools = School::where('active', true)->orderBy('order')->orderBy('name')->get(['id', 'name', 'city_id']);
        $courseTypes = CourseType::where('active', true)->orderBy('order')->orderBy('name')->get(['id', 'name']);
        $courses = Course::where('active', true)->orderBy('order')->orderBy('name')->get(['id', 'name', 'school_id', 'course_type_id', 'pricing_type', 'category']);
        $accommodations = Accommodation::where('active', true)->orderBy('order')->orderBy('name')->get([
            'id',
            'name',
            'school_id',
            'other_charge_enabled',
            'other_charge_name',
            'other_charge_amount',
        ]);
         $addons = Addon::where('active', true)->whereNull('school_id')->orderBy('name')->get();
         $regions = Region::where('active', true)->orderBy('order')->orderBy('name')->pluck('name', 'id'); // Fetch Regions again with custom order
         // Fetch and group course prices by course_id again
         $allCoursePrices = CoursePrice::select('course_id', 'min_weeks', 'max_weeks')->get()->groupBy('course_id');

         // Fetch and group course schedules by course_id again
         $allCourseSchedules = CourseSchedule::select('course_id', 'start_date', 'duration_weeks', 'fixed_price', 'active')
             ->where('active', true)
             ->get()
             ->groupBy('course_id');

         // Determine which view to use based on the current route
         $viewName = request()->routeIs('calculator.*') ? 'calculator.create' : 'admin.quotations.create';

         $user = auth()->user();
         $exchangeNamesQuery = ExchangeName::orderBy('order')->orderBy('name');
         
         // Filter exchange names based on user regions
         if (!$user || !$user->isAdmin()) {
              if ($user && $user->regions()->exists()) {
                  $userRegionIds = $user->regions()->pluck('regions.id');
                  $exchangeNamesQuery->whereHas('regions', function($q) use ($userRegionIds) {
                      $q->whereIn('regions.id', $userRegionIds);
                  });
              } else {
                  // User has no specific regions (sees all regions), but Exchange Names must have regions to be visible to non-admins
                  $exchangeNamesQuery->has('regions');
              }
         }
         $exchangeNames = $exchangeNamesQuery->get(['name', 'label']);

         return view($viewName, compact(
             'countries',
             'cities',
             'schools',
             'courseTypes',
             'courses',
             'accommodations',
             'addons',
             'regions', // Pass regions again
             'allCoursePrices', // Pass course prices again
             'allCourseSchedules', // Pass course schedules again
             'costBreakdown', // Pass the results to the view
             'exchangeNames' // Pass Exchange Names
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

<?php

namespace App\Services;

use App\Models\Course;
use App\Models\School;
use App\Models\Accommodation;
use App\Models\Airport; // Import Airport model
use App\Models\Currency;
use App\Models\DiscountRule;
use App\Models\CoursePrice;
use App\Models\CourseSchedule;
use App\Models\AccommodationPrice;
use App\Models\Addon;
use App\Models\Setting;
use Carbon\Carbon; // For date calculations
use Illuminate\Support\Facades\Log; // For logging errors

class FeeCalculatorService
{
    private array $quoteDetails = [];
    private ?School $school = null;
    private ?Currency $currency = null;
    private ?Course $course = null;
    private ?Accommodation $accommodation = null;
    private ?Carbon $startDate = null;
    private ?int $courseWeeks = null;
    private ?int $accommodationWeeks = null;
    private array $selectedAddons = []; // e.g., ['insurance' => true, 'courier' => true, 'airport_arrival_id' => 5]
    private ?int $studentAge = null;
    private ?Carbon $studentBirthday = null;
    private ?int $nationalityCountryId = null;
    private ?int $regionId = null; // Add property for region ID
    private bool $christmasAccommodation = false; // Flag for Christmas accommodation option
    private ?Carbon $christmasStartDate = null;
    private ?Carbon $christmasEndDate = null;
    private ?int $christmasExtraWeeks = null; // Number of extra weeks for Christmas accommodation
    private ?int $arrivalAirportId = null; // New property for arrival airport ID
    private ?int $departureAirportId = null; // New property for departure airport ID
    private bool $courierFeeOption = false; // Flag for courier fee option
    private bool $insuranceOption = false; // Flag for insurance option
    private bool $privateBathroomOption = false; // Flag for private bathroom option
    private bool $dietarySupplementOption = false; // Flag for dietary supplement option
    private array $nationalityDiscounts = []; // Array of selected nationality discount IDs
    
    // Second course properties
    private ?School $secondSchool = null;
    private ?Course $secondCourse = null;
    private ?Carbon $secondStartDate = null;
    private ?int $secondCourseWeeks = null;
    
    // Second accommodation properties
    private ?Accommodation $secondAccommodation = null;
    private ?int $secondAccommodationWeeks = null;
    private bool $secondPrivateBathroomOption = false;
    private bool $secondDietarySupplementOption = false;
    private bool $secondChristmasAccommodation = false;
    private ?int $secondChristmasExtraWeeks = null; // Number of extra weeks for second accommodation Christmas

    private array $costBreakdown = [
        'items' => [],
        'discounts' => [],
        'subtotals' => [
            'tuition' => 0,
            'accommodation' => 0,
            'fees' => 0,
            'addons' => 0,
        ],
        'total' => 0,
        'currency_code' => '',
        'currency_symbol' => '',
        'errors' => [], // To store calculation errors
        'notes' => [], // To store additional notes about the calculation
    ];

    /**
     * Calculate the full quote based on input parameters.
     *
     * @param array $quoteParams Parameters like school_id, course_id, start_date, course_weeks, etc.
     * @return array The detailed cost breakdown.
     */
    public function calculateQuote(array $quoteParams): array
    {
        try {
            $this->reset();
            if (!$this->loadQuoteDetails($quoteParams)) {
                return $this->costBreakdown; // Return early if loading failed
            }

            // --- Main Calculation Steps ---
            $this->calculateSchoolFees();
            $this->calculateCourseTuition();
            $this->calculateSecondCourseTuition(); // Calculate second course if present
            $this->calculateAccommodationCost();
            $this->calculateSecondAccommodationCost(); // Calculate second accommodation if present
            $this->calculateAddonCosts();
            $this->calculateAirportTransferCosts(); // Calculate airport transfer costs
            $this->addIncludedGenericItems(); // Add included items with no explicit pricing logic
            $this->applyDiscounts(); // Apply discounts after initial costs are calculated

            $this->calculateTotal();
            
            // Add pricing rule explanations to notes
            $this->addPricingRuleExplanations();

            // Add metadata to the cost breakdown
            $this->costBreakdown['school_name'] = $this->school->name ?? 'Unknown School';
            $this->costBreakdown['city_name'] = $this->school->city->name ?? 'Unknown City';
            $this->costBreakdown['country_name'] = ($this->school && $this->school->city && $this->school->city->country)
                ? $this->school->city->country->name
                : 'Unknown Country';
            
            // Add school social accounts to cost breakdown
            $this->costBreakdown['school_social_accounts'] = $this->school && $this->school->socialAccounts 
                ? $this->school->socialAccounts->map(function($account) {
                    return [
                        'platform' => $account->platform,
                        'url' => $account->url
                    ];
                })->toArray() 
                : [];

            $this->costBreakdown['course_name'] = $this->course->name ?? 'Unknown Course';
            $this->costBreakdown['course_type_name'] = $this->course->courseType->name ?? 'Unknown Course Type';
            $this->costBreakdown['course_duration_weeks'] = $this->courseWeeks;
            // Add course start date here as well, as it's used in display
            $this->costBreakdown['course_start_date'] = $this->startDate ? $this->startDate->format('Y-m-d') : null;
            // Add course end date using Monday-to-Friday logic with Christmas breaks
            $courseEndDate = $this->calculateCourseEndDate();
            $this->costBreakdown['course_end_date'] = $courseEndDate ? $courseEndDate->format('Y-m-d') : null;
            // Add quotation extraction date for pricing reference
            $this->costBreakdown['quotation_extraction_date'] = $this->getQuotationExtractionDate()->format('Y-m-d');
            $this->costBreakdown['quotation_extraction_date_formatted'] = $this->getQuotationExtractionDate()->format('j M Y');
            
            // Add accommodation details if accommodation is present
            if ($this->accommodation && $this->accommodationWeeks) {
                // Calculate accommodation start date (same as course start, adjusted to Monday)
                $accommodationStart = $this->startDate->copy();
                if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
                    $accommodationStart = $accommodationStart->next(Carbon::MONDAY);
                }
                $this->costBreakdown['accommodation_start_date'] = $accommodationStart->format('Y-m-d');
                $this->costBreakdown['accommodation_end_date'] = $this->calculateAccommodationEndDate()->format('Y-m-d');
                $this->costBreakdown['accommodation_duration_weeks'] = $this->accommodationWeeks;
            }
            
            // Add second accommodation details if present
            if ($this->secondAccommodation && $this->secondAccommodationWeeks) {
                $this->costBreakdown['second_accommodation_start_date'] = $this->calculateSecondAccommodationStartDate()->format('Y-m-d');
                $this->costBreakdown['second_accommodation_end_date'] = $this->calculateSecondAccommodationEndDate()->format('Y-m-d');
                $this->costBreakdown['second_accommodation_duration_weeks'] = $this->secondAccommodationWeeks;
            }

            // Add Christmas break information if applicable
            $this->addChristmasBreakInformation();

            // Add second course metadata if present
            if ($this->secondCourse && $this->secondSchool) {
                $this->costBreakdown['second_course_name'] = $this->secondCourse->name;
                $this->costBreakdown['second_course_type_name'] = $this->secondCourse->courseType->name ?? 'Unknown Course Type';
                $this->costBreakdown['second_course_duration_weeks'] = $this->secondCourseWeeks;
                $this->costBreakdown['second_course_start_date'] = $this->secondStartDate ? $this->secondStartDate->format('Y-m-d') : null;
                $this->costBreakdown['second_course_end_date'] = $this->secondStartDate && $this->secondCourseWeeks ? $this->calculateSecondCourseEndDate()->format('Y-m-d') : null;
                $this->costBreakdown['second_school_name'] = $this->secondSchool->name;
                $this->costBreakdown['second_city_name'] = $this->secondSchool->city->name ?? 'Unknown City';
                
                // Add second course Christmas break information if applicable
                $this->addSecondCourseChristmasBreakInformation();
            }

            return $this->costBreakdown;
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error in FeeCalculatorService: ' . $e->getMessage(), [
                'exception' => $e,
                'params' => $quoteParams
            ]);

            // Add error to the cost breakdown
            $this->addError('An unexpected error occurred during calculation: ' . $e->getMessage());
            return $this->costBreakdown;
        }
    }

    /**
     * Apply nationality-specific discounts based on selected nationality discount IDs.
     */
    private function applyNationalityDiscounts(): void
    {
        if (empty($this->nationalityDiscounts)) {
            return;
        }

        // Fetch the selected nationality discount rules
        $nationalityRules = DiscountRule::with('courses')->whereIn('id', $this->nationalityDiscounts)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('school_id') // Global rules
                      ->orWhere('school_id', $this->school->id); // School-specific rules
            })
            ->where(function ($query) {
                 $query->whereNull('region_id') // Global discounts (no region specified)
                       ->orWhere('region_id', $this->regionId); // Region-specific discounts
            })
            ->orderBy('priority', 'asc')
            ->get();

        foreach ($nationalityRules as $rule) {
            // Check if this rule's conditions are met (excluding nationality check since it's pre-selected)
            if ($this->checkNationalityDiscountConditions($rule)) {
                $discountAmount = $this->calculateDiscountAmount($rule);

                // Display priority: use Specific Nationality Title when available
                $displayName = $rule->nationality_title ?? $rule->name;

                if ($discountAmount > 0) {
                    // Include course attribution for course tuition or fixed schedule discounts
                    $primaryApplies = null;
                    $secondApplies = null;
                    if ($rule->applies_to === 'course_tuition') {
                        [$primaryApplies, $secondApplies] = $this->getCourseAttributionForRule($rule);
                    } elseif ($rule->applies_to === 'fixed_schedule_courses') {
                        [$primaryApplies, $secondApplies] = $this->getFixedScheduleCourseAttributionForRule($rule);
                    }
                    $this->addDiscount(
                        $displayName,
                        $discountAmount,
                        $rule->applies_to,
                        (bool)($rule->hide_rule_name_in_calculator ?? false),
                        true,
                        $primaryApplies,
                        $secondApplies
                    );
                } elseif ($rule->discount_type === 'fee_waiver') {
                    $this->addDiscount($displayName, 0, $rule->applies_to . '_waiver', (bool)($rule->hide_rule_name_in_calculator ?? false), true);
                }
            }
        }
    }

    /**
     * Check discount conditions for nationality-specific discounts (excluding nationality check).
     *
     * @param DiscountRule $rule
     * @return bool
     */
    private function checkNationalityDiscountConditions(DiscountRule $rule): bool
    {
        // School condition
        if ($rule->school_id !== null && $rule->school_id !== $this->school->id) {
            return false;
        }

        // Region condition
        if ($rule->region_id !== null && $rule->region_id !== $this->regionId) {
            return false;
        }

        // Course condition: allow match on either primary or second course
        $ruleCourses = $rule->courses->pluck('id')->toArray();

        if (!empty($ruleCourses)) {
            $matchesPrimaryCourse = ($this->course && in_array($this->course->id, $ruleCourses));
            $matchesSecondCourse = ($this->secondCourse && in_array($this->secondCourse->id, $ruleCourses));
            if (!$matchesPrimaryCourse && !$matchesSecondCourse) {
                return false;
            }
        } elseif ($rule->course_id !== null) {
            // Legacy check
            $matchesPrimaryCourse = ($this->course && $rule->course_id === $this->course->id);
            $matchesSecondCourse = ($this->secondCourse && $rule->course_id === $this->secondCourse->id);
            if (!$matchesPrimaryCourse && !$matchesSecondCourse) {
                return false;
            }
        }

        // Course Type condition: allow match on either primary or second course
        if ($rule->course_type_id !== null) {
            $primaryTypeId = $this->course ? $this->course->course_type_id : null;
            $secondTypeId = $this->secondCourse ? $this->secondCourse->course_type_id : null;
            if ($rule->course_type_id !== $primaryTypeId && $rule->course_type_id !== $secondTypeId) {
                return false;
            }
        }

        // Accommodation condition
        if ($rule->accommodation_id !== null && (!$this->accommodation || $rule->accommodation_id !== $this->accommodation->id)) {
            return false;
        }

        // Accommodation Type condition
        if ($rule->accommodation_type !== null && (!$this->accommodation || !str_contains(strtolower($this->accommodation->type ?? ''), strtolower($rule->accommodation_type)))) {
             return false;
        }

        // Course Weeks condition - use combined weeks when second course is present
        $weeksForDiscount = $this->getTotalCombinedWeeks();
        if ($rule->min_course_weeks !== null && $weeksForDiscount < $rule->min_course_weeks) {
            return false;
        }
        if ($rule->max_course_weeks !== null && $weeksForDiscount > $rule->max_course_weeks) {
            return false;
        }

        // Accommodation Weeks condition
        if ($rule->min_accommodation_weeks !== null && $this->accommodationDurationWeeks < $rule->min_accommodation_weeks) {
            return false;
        }
        if ($rule->max_accommodation_weeks !== null && $this->accommodationDurationWeeks > $rule->max_accommodation_weeks) {
            return false;
        }

        // Date conditions
        if ($rule->start_date && $this->courseStartDate < $rule->start_date) {
            return false;
        }
        if ($rule->end_date && $this->courseStartDate > $rule->end_date) {
            return false;
        }
        if ($rule->booking_start_date && now() < $rule->booking_start_date) {
            return false;
        }
        if ($rule->booking_end_date && now() > $rule->booking_end_date) {
            return false;
        }

        // Quotation extraction date range
        if ($rule->quotation_extraction_start_date && now() < $rule->quotation_extraction_start_date) {
            return false;
        }
        if ($rule->quotation_extraction_end_date && now() > $rule->quotation_extraction_end_date) {
            return false;
        }

        return true;
    }

    /**
     * Reset calculation state.
     */
    private function reset(): void
    {
        $this->quoteDetails = [];
        $this->school = null;
        $this->currency = null;
        $this->course = null;
        $this->accommodation = null;
        $this->startDate = null;
        $this->courseWeeks = null;
        $this->accommodationWeeks = null;
        $this->selectedAddons = [];
        $this->studentAge = null;
        $this->studentBirthday = null;
        $this->nationalityCountryId = null;
        $this->regionId = null;
        $this->christmasAccommodation = false;
        $this->christmasStartDate = null;
        $this->christmasEndDate = null;
        $this->christmasExtraWeeks = null;
        $this->arrivalAirportId = null; // Reset airport IDs
        $this->departureAirportId = null;
        $this->courierFeeOption = false; // Reset courier fee option
        $this->privateBathroomOption = false; // Reset private bathroom option
        $this->dietarySupplementOption = false; // Reset dietary supplement option
        $this->nationalityDiscounts = []; // Reset nationality discounts
        
        // Reset second course properties
        $this->secondSchool = null;
        $this->secondCourse = null;
        $this->secondStartDate = null;
        $this->secondCourseWeeks = null;
        
        // Reset second accommodation properties
        $this->secondAccommodation = null;
        $this->secondAccommodationWeeks = null;
        $this->secondPrivateBathroomOption = false;
        $this->secondDietarySupplementOption = false;
        $this->secondChristmasAccommodation = false;
        $this->secondChristmasExtraWeeks = null;

        $this->costBreakdown = [
            'items' => [],
            'discounts' => [],
            'subtotals' => [
                'tuition' => 0,
                'accommodation' => 0,
                'fees' => 0,
                'addons' => 0,
            ],
            'total' => 0,
            'currency_code' => '',
            'currency_symbol' => '',
            'errors' => [],
            'notes' => [],
        ];
    }

    /**
     * Load and validate necessary models and details from input parameters.
     * Returns false if essential data is missing or invalid.
     *
     * @param array $quoteParams
     * @return bool Success status
     */
    private function loadQuoteDetails(array $quoteParams): bool
    {
        $this->quoteDetails = $quoteParams;

        // --- Load Essential Models ---
        $this->school = School::with('currency', 'city', 'socialAccounts')->find($quoteParams['school_id'] ?? null); // Eager load city and social accounts
        if (!$this->school) {
            $this->addError('Invalid School ID provided.');
            return false;
        }
        $this->currency = $this->school->currency;
        if (!$this->currency) {
             $this->addError('School is missing a currency configuration.');
             return false;
        }
        $this->costBreakdown['currency_code'] = $this->currency->code;
        $this->costBreakdown['currency_symbol'] = $this->currency->symbol ?? $this->currency->code;

        $this->course = Course::with('courseType')->find($quoteParams['course_id'] ?? null);
        if (!$this->course) {
            $this->addError('Invalid Course ID provided.');
            return false;
        }
        if ($this->course->school_id !== $this->school->id) {
             $this->addError('Selected course does not belong to the selected school.');
             return false;
        }

        // --- Load Dates and Durations ---
        try {
            $this->startDate = Carbon::parse($quoteParams['course_start_date'] ?? null);
        } catch (\Exception $e) {
            $this->addError('Invalid Course Start Date provided.');
            return false;
        }
        $this->courseWeeks = isset($quoteParams['course_duration_weeks']) ? (int) $quoteParams['course_duration_weeks'] : null;
        if ($this->courseWeeks === null || $this->courseWeeks < 1) {
             $this->addError('Invalid Course Duration provided.');
             return false;
        }

        // --- Load Optional Second Course ---
        if (!empty($quoteParams['second_course_id'])) {
            $this->secondCourse = Course::with('courseType')->find($quoteParams['second_course_id']);
            if (!$this->secondCourse) {
                $this->addError('Invalid Second Course ID provided.');
            } else {
                // Second course must belong to the same school as the first course
                if ($this->secondCourse->school_id !== $this->school->id) {
                    $this->addError('Selected second course does not belong to the selected school.');
                    $this->secondCourse = null;
                } else {
                    // Use the same school as the first course
                    $this->secondSchool = $this->school;
                    
                    // Load second course dates and duration
                    try {
                        $this->secondStartDate = Carbon::parse($quoteParams['second_course_start_date'] ?? null);
                    } catch (\Exception $e) {
                        $this->addError('Invalid Second Course Start Date provided.');
                        $this->secondCourse = null;
                        $this->secondSchool = null;
                    }
                    
                    $this->secondCourseWeeks = isset($quoteParams['second_course_duration_weeks']) ? (int) $quoteParams['second_course_duration_weeks'] : null;
                    if ($this->secondCourseWeeks === null || $this->secondCourseWeeks < 1) {
                        $this->addError('Invalid Second Course Duration provided.');
                        $this->secondCourse = null;
                        $this->secondSchool = null;
                        $this->secondStartDate = null;
                    }
                }
            }
        }

        // --- Load Optional Accommodation ---
        if (!empty($quoteParams['accommodation_id'])) {
            $this->accommodation = Accommodation::find($quoteParams['accommodation_id']);
            if (!$this->accommodation) {
                $this->addError('Invalid Accommodation ID provided.');
            } elseif ($this->accommodation->school_id !== $this->school->id) {
                 $this->addError('Selected accommodation does not belong to the selected school.');
                 $this->accommodation = null;
            } else {
                 if ($this->course && $this->course->category === 'junior') {
                     $allowedAccommodationIds = $this->course->juniorAccommodations()
                         ->pluck('accommodations.id')
                         ->filter()
                         ->all();

                     if (!empty($allowedAccommodationIds) && !in_array($this->accommodation->id, $allowedAccommodationIds, true)) {
                         $this->addError('Selected accommodation is not allowed for this junior course.');
                         $this->accommodation = null;
                     }
                 }

                 if ($this->accommodation) {
                     $this->accommodationWeeks = isset($quoteParams['accommodation_duration_weeks']) ? (int) $quoteParams['accommodation_duration_weeks'] : null;
                     if ($this->accommodationWeeks === null || $this->accommodationWeeks < 1) {
                          $this->addError('Invalid Accommodation Duration provided.');
                          $this->accommodation = null;
                     }
                 }
            }
        }
        
        // --- Load Optional Second Accommodation ---
        if (!empty($quoteParams['second_accommodation_id'])) {
            $this->secondAccommodation = Accommodation::find($quoteParams['second_accommodation_id']);
            if (!$this->secondAccommodation) {
                $this->addError('Invalid Second Accommodation ID provided.');
            } elseif ($this->secondAccommodation->school_id !== $this->school->id) {
                 $this->addError('Selected second accommodation does not belong to the selected school.');
                 $this->secondAccommodation = null; // Unset invalid accommodation
            } else {
                 $this->secondAccommodationWeeks = isset($quoteParams['second_accommodation_duration_weeks']) ? (int) $quoteParams['second_accommodation_duration_weeks'] : null;
                 if ($this->secondAccommodationWeeks === null || $this->secondAccommodationWeeks < 1) {
                      $this->addError('Invalid Second Accommodation Duration provided.');
                      $this->secondAccommodation = null; // Unset if duration invalid
                 }
            }
        }

        // --- Load Student Details (Optional but needed for some fees/discounts) ---
        if (!empty($quoteParams['client_birthday'])) {
            try {
                $this->studentBirthday = Carbon::parse($quoteParams['client_birthday']);
                $this->studentAge = $this->studentBirthday->age;
            } catch (\Exception $e) {
                 $this->addError('Invalid Client Birthday provided.');
                 // Don't fail, but age-dependent calculations might be skipped
            }
        }

        // --- Load Christmas Accommodation Option ---
        // Use the value directly from the request if present
        // Check for truthiness, as controller now sends boolean true
        $this->christmasAccommodation = !empty($quoteParams['christmas_accommodation']); // Corrected check
        $this->christmasExtraWeeks = isset($quoteParams['christmas_extra_weeks']) ? (int) $quoteParams['christmas_extra_weeks'] : null;

        // Load Christmas dates from request OR school settings
        try {
            if (!empty($quoteParams['christmas_start_date'])) {
                $this->christmasStartDate = Carbon::parse($quoteParams['christmas_start_date']);
            } elseif (!is_null($this->school->christmas_start_date)) {
                 $this->christmasStartDate = Carbon::parse($this->school->christmas_start_date);
            }

            if (!empty($quoteParams['christmas_end_date'])) {
                $this->christmasEndDate = Carbon::parse($quoteParams['christmas_end_date']);
            } elseif (!is_null($this->school->christmas_end_date)) {
                 $this->christmasEndDate = Carbon::parse($this->school->christmas_end_date);
            }
        } catch (\Exception $e) {
            $this->addError('Invalid Christmas period dates provided.');
            // Don't fail, but Christmas accommodation might not be calculated correctly
        }

        Log::info('Loaded Christmas Details:', [
            'christmasAccommodation' => $this->christmasAccommodation,
            'christmasExtraWeeks' => $this->christmasExtraWeeks,
            'christmasStartDate' => $this->christmasStartDate ? $this->christmasStartDate->toDateString() : null,
            'christmasEndDate' => $this->christmasEndDate ? $this->christmasEndDate->toDateString() : null,
        ]);


        $this->nationalityCountryId = $quoteParams['client_nationality_country_id'] ?? null;
        $this->regionId = $quoteParams['region_id'] ?? null; // Load region ID

        // --- Load Selected Addons ---
        $this->selectedAddons = $quoteParams['selected_addons'] ?? []; // Expecting an array like ['addon_id_1' => true, 'addon_id_5' => ['weeks' => 10]]

        // --- Load Airport Transfer IDs ---
        $this->arrivalAirportId = $quoteParams['arrival_transfer_airport_id'] ?? null;
        $this->departureAirportId = $quoteParams['departure_transfer_airport_id'] ?? null;

        // --- Load Courier Fee Option ---
        $this->courierFeeOption = $quoteParams['courier_fee_option'] ?? false;

        // --- Load Insurance Option ---
        $this->insuranceOption = $quoteParams['insurance_option'] ?? false;

        // --- Load Accommodation Options ---
        $this->privateBathroomOption = $quoteParams['private_bathroom_option'] ?? false;
        $this->dietarySupplementOption = $quoteParams['dietary_supplement_option'] ?? false;
        
        // --- Load Nationality Discounts ---
        $this->nationalityDiscounts = $quoteParams['nationality_discounts'] ?? [];
        
        // --- Load Second Accommodation Options ---
        $this->secondPrivateBathroomOption = $quoteParams['second_private_bathroom_option'] ?? false;
        $this->secondDietarySupplementOption = $quoteParams['second_dietary_supplement_option'] ?? false;
        $this->secondChristmasAccommodation = $quoteParams['second_christmas_accommodation'] ?? false;
        $this->secondChristmasExtraWeeks = $quoteParams['second_christmas_extra_weeks'] ?? null;

        return true; // Loading successful
    }

    // Deprecated: shouldSkipJuniorFee removed in favor of explicit inclusion flags per item

    private function validateJuniorAgeForCourse(): void
    {
        if (!$this->course || $this->course->category !== 'junior') {
            return;
        }

        $settings = $this->course->juniorSettings;
        if (!$settings) {
            return;
        }

        if (!$this->studentBirthday && $this->studentAge === null) {
            return;
        }

        $ageAtStart = null;

        if ($this->studentBirthday && $this->startDate) {
            try {
                $ageAtStart = $this->studentBirthday->diffInYears($this->startDate);
            } catch (\Exception $e) {
                $ageAtStart = null;
            }
        } elseif ($this->studentAge !== null) {
            $ageAtStart = $this->studentAge;
        }

        if ($ageAtStart === null) {
            return;
        }

        if ($settings->min_age !== null && $ageAtStart < $settings->min_age) {
            $this->addError('Selected junior course requires minimum age of ' . $settings->min_age . '.');
        }

        if ($settings->max_age !== null && $ageAtStart > $settings->max_age) {
            $this->addError('Selected junior course has maximum age of ' . $settings->max_age . '.');
        }
    }

    /**
     * Calculate mandatory school fees (registration, bank charges, etc.).
     */
    private function calculateSchoolFees(): void
    {
        if (!$this->school) return;

        $this->validateJuniorAgeForCourse();

        // Check for registration fee waiver before adding
        $hasRegFeeWaiver = collect($this->costBreakdown['discounts'])->contains('applied_to', 'registration_fee_waiver');
        
        // For one-time/admin fees: Always use 2025 values when course starts in 2025
        $useOneTimeFee2025 = $this->startDate->year === 2025;
        
        // Registration Fee - One-time fee rule
        $registrationFee2025 = (float) $this->school->registration_fee;
        $registrationFee2026 = $this->school->registration_fee_2026 !== null ? (float) $this->school->registration_fee_2026 : $registrationFee2025;
        $registrationFee = $useOneTimeFee2025 ? $registrationFee2025 : $registrationFee2026;
        
        $juniorSettings = $this->course?->juniorSettings;
        if ($juniorSettings && $juniorSettings->includes_registration_fee) {
            $this->addItem('Registration Fee', 0, 'fees', true);
        } elseif ($registrationFee > 0 && !$hasRegFeeWaiver) {
            $this->addItem('Registration Fee', $registrationFee, 'fees');
        } elseif ($hasRegFeeWaiver) {
             Log::info('Registration fee waived.'); // Optional logging
        }

        // Bank Charges - One-time fee rule
        $bankCharges2025 = (float) $this->school->bank_charges;
        $bankCharges2026 = $this->school->bank_charges_2026 !== null ? (float) $this->school->bank_charges_2026 : $bankCharges2025;
        $bankCharges = $useOneTimeFee2025 ? $bankCharges2025 : $bankCharges2026;
        
        if ($bankCharges > 0) {
             $this->addItem('Bank Charges', $bankCharges, 'fees');
         }

        // Books Fee - One-time fee rule
        $booksFee2025 = (float) $this->school->books_fee;
        $booksFee2026 = $this->school->books_fee_2026 !== null ? (float) $this->school->books_fee_2026 : $booksFee2025;
        $booksFee = $useOneTimeFee2025 ? $booksFee2025 : $booksFee2026;
        if ($juniorSettings && $juniorSettings->includes_books_fee) {
            $this->addItem('Books Fee', 0, 'fees', true);
        } elseif ($booksFee > 0) {
            $booksFeeName = 'Books Fee';
            // Use year-aware books_weeks based on the first course start date
            $booksWeeks = $this->school->getIntByYear('books_weeks', $this->startDate);
            if ($booksWeeks && $booksWeeks > 0 && $this->courseWeeks > 0) {
                // Apply fee for every X weeks, rounding up
                // Use combined weeks from both courses when second course is present
                $totalWeeks = $this->getTotalCombinedWeeks();
                $multiplier = ceil($totalWeeks / $booksWeeks);
                $booksFee *= $multiplier;
                $booksFeeName .= " (Applied every {$booksWeeks} weeks)";
            }
            $this->addItem($booksFeeName, $booksFee, 'fees');
        }

        // Courier Fee (if enabled for the school and selected by user) - One-time fee rule
        if ($this->courierFeeOption && $this->school->courier_fee_enabled) {
            $courierFee2025 = (float) $this->school->courier_fee;
            $courierFee2026 = $this->school->courier_fee_2026 !== null ? (float) $this->school->courier_fee_2026 : $courierFee2025;
            $courierFee = $useOneTimeFee2025 ? $courierFee2025 : $courierFee2026;
            
            if ($courierFee > 0) {
                $this->addItem('Courier Fee', $courierFee, 'fees');
            }
        }

        // Insurance Fee inclusion: if included in junior package, add as included (£0)
        $juniorSettings = $this->course?->juniorSettings;
        if ($juniorSettings && $juniorSettings->includes_insurance) {
            $this->addItem('Insurance', 0, 'fees', true);
        } elseif ($this->insuranceOption) {
            $insuranceFee2025 = (float) $this->school->insurance_fee_per_week;
            $insuranceFee2026 = $this->school->insurance_fee_per_week_2026 !== null ? (float) $this->school->insurance_fee_per_week_2026 : $insuranceFee2025;
            
            // Insurance is a per-week fee, so use the same pricing logic as course tuition
            // Use combined weeks from both courses when second course is present
            $totalInsuranceWeeks = $this->getTotalCombinedWeeks();
            
            // Get pricing years for both courses to determine if mixed pricing is needed
            $firstCoursePricingYears = $this->determinePricingYears($this->startDate, $this->courseWeeks);
            $insuranceWeeks2025 = $firstCoursePricingYears['weeks_2025'];
            $insuranceWeeks2026 = $firstCoursePricingYears['weeks_2026'];
            $hasMixedPricing = $firstCoursePricingYears['has_mixed_pricing'];
            
            // Check if second course also has mixed pricing or contributes to different years
            if ($this->secondCourse && $this->secondCourseWeeks) {
                $secondCoursePricingYears = $this->determinePricingYears($this->secondStartDate, $this->secondCourseWeeks);
                $insuranceWeeks2025 += $secondCoursePricingYears['weeks_2025'];
                $insuranceWeeks2026 += $secondCoursePricingYears['weeks_2026'];
                
                // If either course has mixed pricing, or courses span different years, use mixed pricing
                if ($secondCoursePricingYears['has_mixed_pricing'] || 
                    ($insuranceWeeks2025 > 0 && $insuranceWeeks2026 > 0)) {
                    $hasMixedPricing = true;
                }
            }
            
            if ($hasMixedPricing && $insuranceWeeks2025 > 0 && $insuranceWeeks2026 > 0) {
                // Mixed pricing: calculate for each year separately
                if ($insuranceFee2025 > 0 && $insuranceWeeks2025 > 0) {
                    $insuranceCost2025 = $insuranceFee2025 * $insuranceWeeks2025;
                    $this->addItem('Insurance (2025) (' . $insuranceWeeks2025 . ' weeks)', $insuranceCost2025, 'fees');
                }
                if ($insuranceFee2026 > 0 && $insuranceWeeks2026 > 0) {
                    $insuranceCost2026 = $insuranceFee2026 * $insuranceWeeks2026;
                    $this->addItem('Insurance (2026) (' . $insuranceWeeks2026 . ' weeks)', $insuranceCost2026, 'fees');
                }
            } else {
                // Single year pricing - determine which year to use
                $useFirstCourse = true;
                $pricingYears = $firstCoursePricingYears;
                
                // If only second course has weeks, use second course pricing
                if ($this->secondCourse && $this->secondCourseWeeks && 
                    $firstCoursePricingYears['weeks_2025'] == 0 && $firstCoursePricingYears['weeks_2026'] == 0) {
                    $pricingYears = $this->determinePricingYears($this->secondStartDate, $this->secondCourseWeeks);
                    $useFirstCourse = false;
                }
                
                $insuranceFee = $pricingYears['use_2026_pricing'] ? $insuranceFee2026 : $insuranceFee2025;
                $yearLabel = $pricingYears['use_2026_pricing'] ? '2026' : '2025';
                
                if ($insuranceFee > 0) {
                    $insuranceCost = $insuranceFee * $totalInsuranceWeeks;
                    $this->addItem('Insurance (' . $yearLabel . ') (' . $totalInsuranceWeeks . ' weeks)', $insuranceCost, 'fees');
                }
            }
            
            // Add Christmas accommodation insurance (1 extra week) if applicable
            // Only show when Christmas accommodation is selected AND there's actual overlap with Christmas period
            $shouldShowChristmasInsurance = false;
            
            // Check first accommodation Christmas conditions
            if ($this->christmasAccommodation && $this->christmasStartDate && $this->christmasEndDate && $this->accommodationWeeks) {
                $accommodationStartDate = $this->startDate;
                $accommodationEndDate = $this->calculateAccommodationEndDate();
                $overlapStart = $accommodationStartDate->gt($this->christmasStartDate) ? $accommodationStartDate : $this->christmasStartDate;
                $overlapEnd = $accommodationEndDate->lt($this->christmasEndDate) ? $accommodationEndDate : $this->christmasEndDate;
                
                if ($overlapStart->lte($overlapEnd)) {
                    $shouldShowChristmasInsurance = true;
                }
            }
            
            // Check second accommodation Christmas conditions
            if ($this->secondChristmasAccommodation && $this->christmasStartDate && $this->christmasEndDate && $this->secondAccommodationWeeks) {
                $secondAccommodationStartDate = $this->secondStartDate;
                $secondAccommodationEndDate = $this->calculateSecondAccommodationEndDate();
                $secondOverlapStart = $secondAccommodationStartDate->gt($this->christmasStartDate) ? $secondAccommodationStartDate : $this->christmasStartDate;
                $secondOverlapEnd = $secondAccommodationEndDate->lt($this->christmasEndDate) ? $secondAccommodationEndDate : $this->christmasEndDate;
                
                if ($secondOverlapStart->lte($secondOverlapEnd)) {
                    $shouldShowChristmasInsurance = true;
                }
            }
            
            if ($shouldShowChristmasInsurance) {
                // Determine which year the Christmas week falls in (Christmas is typically in December)
                $christmasYear = 2025; // Default to 2025
                if ($this->christmasStartDate) {
                    $christmasYear = $this->christmasStartDate->year;
                }
                
                // Use the appropriate year's insurance fee
                $christmasInsuranceFee = ($christmasYear == 2026) ? $insuranceFee2026 : $insuranceFee2025;
                
                if ($christmasInsuranceFee > 0) {
                    $christmasInsuranceCost = $christmasInsuranceFee * 1; // Always 1 week
                    $this->addItem('Insurance During Christmas (1 week)', $christmasInsuranceCost, 'fees');
                }
            }
        }

        // Guardianship/Custodianship Fees (Under configured age)
        if ($this->studentAge !== null && $this->studentAge < ($this->school->guardianship_fee_age ?? 18)) {
            $guardianshipAge = $this->school->guardianship_fee_age ?? 18;
            $custodianshipAge = $this->school->custodianship_fee_age ?? 18;
            // Check if accommodation is selected AND requires guardianship
            if ($this->accommodation && $this->accommodation->requires_guardianship && $this->accommodationWeeks > 0) {
                $qualifyingWeeks = $this->calculateGuardianshipQualifyingWeeks();
                if ($qualifyingWeeks > 0) {
                    // Guardianship is a weekly fee, so use combined pricing rules
                    $accommodationStartDate = $this->startDate;
                    $guardianshipPricingYears = $this->determinePricingYears($accommodationStartDate, $qualifyingWeeks);
                    
                    $guardianshipFee2025 = (float) $this->school->guardianship_fee_per_week;
                    $guardianshipFee2026 = $this->school->guardianship_fee_per_week_2026 !== null 
                        ? (float) $this->school->guardianship_fee_per_week_2026 
                        : $guardianshipFee2025;
                    
                    if ($guardianshipPricingYears['has_mixed_pricing']) {
                        // Mixed pricing: separate 2025 and 2026 portions
                        $weeks2025 = $guardianshipPricingYears['weeks_2025'];
                        $weeks2026 = $guardianshipPricingYears['weeks_2026'];
                        
                        // Add 2025 portion
                        if ($weeks2025 > 0 && $guardianshipFee2025 > 0) {
                            $guardianshipTotal2025 = $guardianshipFee2025 * $weeks2025;
                            $itemName2025 = 'Guardianship Fee (U' . $guardianshipAge . ') - ' . $weeks2025 . ' week' . ($weeks2025 > 1 ? 's' : '') . ' (2025)';
                            $this->addItem($itemName2025, $guardianshipTotal2025, 'fees');
                        }
                        
                        // Add 2026 portion
                        if ($weeks2026 > 0 && $guardianshipFee2026 > 0) {
                            $guardianshipTotal2026 = $guardianshipFee2026 * $weeks2026;
                            $itemName2026 = 'Guardianship Fee (U' . $guardianshipAge . ') - ' . $weeks2026 . ' week' . ($weeks2026 > 1 ? 's' : '') . ' (2026)';
                            $this->addItem($itemName2026, $guardianshipTotal2026, 'fees');
                        }
                    } else {
                        // Single year pricing
                        $guardianshipFeePerWeek = $guardianshipPricingYears['use_2026_pricing'] ? $guardianshipFee2026 : $guardianshipFee2025;
                        $year = $guardianshipPricingYears['use_2026_pricing'] ? '2026' : '2025';
                        
                        if ($guardianshipFeePerWeek > 0) {
                            $guardianshipTotal = $guardianshipFeePerWeek * $qualifyingWeeks;
                            $itemName = 'Guardianship Fee (U' . $guardianshipAge . ') - ' . $qualifyingWeeks . ' week' . ($qualifyingWeeks > 1 ? 's' : '') . ' (' . $year . ')';
                            $this->addItem($itemName, $guardianshipTotal, 'fees');
                        }
                    }
                }
            }
            
            // Christmas Guardianship Fee (U18) - Only when Christmas accommodation is active
            if ($this->christmasAccommodation && $this->christmasExtraWeeks > 0) {
                // Check if accommodation requires guardianship
                if ($this->accommodation && $this->accommodation->requires_guardianship) {
                    // Christmas guardianship is always 1 week
                    $christmasGuardianshipWeeks = 1;
                    
                    // Determine pricing year for Christmas period
                    $christmasStartDate = $this->christmasStartDate;
                    $christmasGuardianshipPricingYears = $this->determinePricingYears($christmasStartDate, $christmasGuardianshipWeeks);
                    
                    $guardianshipFee2025 = (float) $this->school->guardianship_fee_per_week;
                    $guardianshipFee2026 = $this->school->guardianship_fee_per_week_2026 !== null 
                        ? (float) $this->school->guardianship_fee_per_week_2026 
                        : $guardianshipFee2025;
                    
                    // Use appropriate pricing based on Christmas period year
                    $christmasGuardianshipFeePerWeek = $christmasGuardianshipPricingYears['use_2026_pricing'] ? $guardianshipFee2026 : $guardianshipFee2025;
                    $christmasYear = $christmasGuardianshipPricingYears['use_2026_pricing'] ? '2026' : '2025';
                    
                    // Apply guardianship only if student is under configured age at Christmas start
                    $ageAtChristmas = ($this->studentBirthday && $christmasStartDate) ? $this->studentBirthday->diffInYears($christmasStartDate) : $this->studentAge;
                    
                    if ($christmasGuardianshipFeePerWeek > 0 && $ageAtChristmas !== null && $ageAtChristmas < $guardianshipAge) {
                        $christmasGuardianshipTotal = $christmasGuardianshipFeePerWeek * $christmasGuardianshipWeeks;
                        $christmasItemName = 'Guardianship Fee (U' . $guardianshipAge . ') During Christmas (' . $christmasYear . ')';
                        $this->addItem($christmasItemName, $christmasGuardianshipTotal, 'fees');
                    }
                }
            }

        }
        
        // Custodianship Fee (Under configured age) - One-time fee
        $custodianshipAge = $this->school->custodianship_fee_age ?? 18;
        $applyCustodianship = false;
        
        // Exact date comparison: apply fee only if student is younger than configured age on first course start date
        if ($this->studentBirthday && $this->startDate) {
            $thresholdBirthday = $this->studentBirthday->copy()->addYears($custodianshipAge);
            $applyCustodianship = $this->startDate->lt($thresholdBirthday);
        } elseif ($this->studentAge !== null) {
            // Fallback to year-only comparison when exact dates are unavailable
            $applyCustodianship = $this->studentAge < $custodianshipAge;
        }
        
        if ($applyCustodianship) {
            $custodianshipFee2025 = (float) $this->school->custodianship_fee;
            $custodianshipFee2026 = $this->school->custodianship_fee_2026 !== null ? (float) $this->school->custodianship_fee_2026 : $custodianshipFee2025;
            $custodianshipFee = $useOneTimeFee2025 ? $custodianshipFee2025 : $custodianshipFee2026;
            
            if ($custodianshipFee > 0) {
                $this->addItem('Custodianship Fee (U' . $custodianshipAge . ')', $custodianshipFee, 'fees');
            }
        }
    }

    /**
     * Calculate the course tuition based on pricing type (per week or fixed schedule).
     */
    private function calculateCourseTuition(): void
    {
        if (!$this->course || !$this->courseWeeks || !$this->startDate) return;

        $tuitionPrice = 0;
        $itemName = $this->course->name;

        if ($this->course->pricing_type === 'per_week') {
            // Get pricing information to check for mixed pricing
            $pricingInfo = $this->determinePricingYears($this->startDate, $this->courseWeeks);
            
            if ($pricingInfo['has_mixed_pricing']) {
                // Handle mixed pricing - create separate line items for 2025 and 2026 portions
                $price = $this->getCoursePrice();
                if (!$price) {
                    $this->addError("Could not find price for '{$this->course->name}' for {$this->courseWeeks} weeks.");
                    return;
                }
                
                $price2025 = (float) $price->price_per_week;
                $price2026 = $price->price_per_week_2026 !== null ? (float) $price->price_per_week_2026 : $price2025;
                
                // Add 2025 portion
                if ($pricingInfo['weeks_2025'] > 0) {
                    $tuition2025 = $price2025 * $pricingInfo['weeks_2025'];
                    $itemName2025 = $itemName . ' (' . $pricingInfo['weeks_2025'] . ' weeks - 2025)';
                    $this->addItem($itemName2025, $tuition2025, 'tuition');
                }
                
                // Add 2026 portion
                if ($pricingInfo['weeks_2026'] > 0) {
                    $tuition2026 = $price2026 * $pricingInfo['weeks_2026'];
                    $itemName2026 = $itemName . ' (' . $pricingInfo['weeks_2026'] . ' weeks - 2026)';
                    $this->addItem($itemName2026, $tuition2026, 'tuition');
                }
            } else {
                // Single pricing year - use existing logic
                $pricePerWeek = $this->getCoursePricePerWeek();
                if ($pricePerWeek === null) {
                    $this->addError("Could not find weekly price for '{$this->course->name}' for {$this->courseWeeks} weeks.");
                    return;
                }
                $tuitionPrice = $pricePerWeek * $this->courseWeeks;
                $year = $pricingInfo['use_2026_pricing'] ? '2026' : '2025';
                $itemName .= ' (' . $this->courseWeeks . ' weeks - ' . $year . ')';
                $this->addItem($itemName, $tuitionPrice, 'tuition');
            }
        } elseif ($this->course->pricing_type === 'fixed_schedule') {
            $schedule = $this->getCourseFixedSchedule();
            if (!$schedule) {
                 $this->addError("Could not find schedule for '{$this->course->name}' starting {$this->startDate->toDateString()} for {$this->courseWeeks} weeks.");
                 return;
            }
            $tuitionPrice = $schedule->fixed_price;
            // Fixed schedule pricing - determine year based on start date
            $year = $this->startDate->year >= 2026 ? '2026' : '2025';
            $itemName .= ' (' . $schedule->start_date->format('Y-m-d') . ' - ' . $schedule->duration_weeks . ' weeks - ' . $year . ')';
            $this->addItem($itemName, $tuitionPrice, 'tuition');
        }

        // Add Course Summer Supplement
        if ($this->school->summer_fee_per_week > 0 && $this->school->summer_start_date && $this->school->summer_end_date) {
            $courseEndDate = $this->calculateCourseEndDate();
            $overlapWeeks = $this->calculateSummerSupplementOverlapWeeks(
                $this->startDate,
                $courseEndDate,
                Carbon::parse($this->school->summer_start_date),
                Carbon::parse($this->school->summer_end_date)
            );

            // Check if the supplement should be waived based on course duration
            $waiveSupplement = $this->school->summer_fee_weeks_off !== null && $this->courseWeeks >= $this->school->summer_fee_weeks_off;

            if ($overlapWeeks > 0 && !$waiveSupplement) {
                $summerFee = $overlapWeeks * $this->school->summer_fee_per_week;
                $this->addItem('Course Summer Supplement', $summerFee, 'fees');
            }
        }


    }

    /**
     * Calculate course end date using Monday-to-Friday logic.
     * Courses start on Monday and end on Friday of the final week.
     */
    private function calculateCourseEndDate(): ?Carbon
    {
        if (!$this->startDate || !$this->courseWeeks) {
            return null;
        }

        // Ensure the start date is a Monday
        $courseStart = $this->startDate->copy();
        if ($courseStart->dayOfWeek !== Carbon::MONDAY) {
            // If not Monday, move to the next Monday
            $courseStart = $courseStart->next(Carbon::MONDAY);
        }

        // Calculate end date accounting for Christmas breaks
        $courseEnd = $this->calculateCourseEndDateWithChristmasBreaks($courseStart, $this->courseWeeks);

        return $courseEnd;
    }

    /**
     * Calculate course end date accounting for Christmas breaks.
     * If the course period overlaps with Christmas supplement period,
     * extend the course end date to account for the Christmas break.
     */
    private function calculateCourseEndDateWithChristmasBreaks(Carbon $courseStart, int $courseWeeks): Carbon
    {
        // Check if school has Christmas supplement dates defined
        if (!$this->school || !$this->school->christmas_start_date || !$this->school->christmas_end_date) {
            // No Christmas period defined, use standard calculation
            return $courseStart->copy()
                ->addWeeks($courseWeeks - 1)
                ->endOfWeek()
                ->subDays(2); // Go back to Friday
        }

        // Calculate the standard course end date first
        $standardEndDate = $courseStart->copy()
            ->addWeeks($courseWeeks - 1)
            ->endOfWeek()
            ->subDays(2); // Go back to Friday

        // Get Christmas period dates (keep original dates from school settings)
        $christmasStart = $this->school->christmas_start_date->copy();
        $christmasEnd = $this->school->christmas_end_date->copy();

        // Check if there's an overlap between course period and Christmas period
        $courseEndForOverlapCheck = $standardEndDate->copy();
        if ($courseStart->lte($christmasEnd) && $courseEndForOverlapCheck->gte($christmasStart)) {
            // There is an overlap, calculate Christmas break weeks
            $christmasBreakWeeks = $this->calculateChristmasOverlapWeeks($courseStart, $courseEndForOverlapCheck, $christmasStart, $christmasEnd);

            if ($christmasBreakWeeks > 0) {
                // Extend the course end date by the number of Christmas break weeks
                $adjustedEndDate = $standardEndDate->copy()->addWeeks($christmasBreakWeeks);

                // Ensure the adjusted end date ends on a Friday
                if ($adjustedEndDate->dayOfWeek !== Carbon::FRIDAY) {
                    $adjustedEndDate = $adjustedEndDate->endOfWeek(Carbon::SUNDAY)->subDays(2);
                }

                return $adjustedEndDate;
            }
        }

        return $standardEndDate;
    }

    /**
     * Calculate the number of weeks that overlap between course period and Christmas period.
     * This represents the Christmas break weeks that should be excluded from course duration.
     */
    private function calculateChristmasOverlapWeeks(Carbon $courseStart, Carbon $courseEnd, Carbon $christmasStart, Carbon $christmasEnd): int
    {
        // Find the actual overlap period
        $overlapStart = $courseStart->max($christmasStart);
        $overlapEnd = $courseEnd->min($christmasEnd);

        // Check if there is any overlap
        if ($overlapStart->gt($overlapEnd)) {
            return 0; // No overlap
        }

        // Calculate weeks by counting Monday-to-Friday periods
        $weeks = 0;
        $current = $overlapStart->copy();

        // Ensure we start from a Monday
        if ($current->dayOfWeek !== Carbon::MONDAY) {
            $current = $current->next(Carbon::MONDAY);
        }

        // Count complete weeks (Monday to Friday) within the overlap period
        while ($current->copy()->addDays(4)->lte($overlapEnd)) { // Monday + 4 days = Friday
            $weeks++;
            $current->addWeek(); // Move to next Monday
        }

        return $weeks;
    }

    /**
     * Add Christmas break information to the cost breakdown if applicable.
     */
    private function addChristmasBreakInformation(): void
    {
        // Initialize Christmas break info
        $this->costBreakdown['christmas_break'] = [
            'has_break' => false,
            'break_weeks' => 0,
            'break_start_date' => null,
            'break_end_date' => null,
            'explanation' => null,
        ];

        // Check if school has Christmas supplement dates defined and course details are available
        if (!$this->school || !$this->school->christmas_start_date || !$this->school->christmas_end_date || !$this->startDate || !$this->courseWeeks) {
            return;
        }

        // Ensure the start date is a Monday
        $courseStart = $this->startDate->copy();
        if ($courseStart->dayOfWeek !== Carbon::MONDAY) {
            $courseStart = $courseStart->next(Carbon::MONDAY);
        }

        // Calculate the standard course end date (without Christmas breaks)
        $standardEndDate = $courseStart->copy()
            ->addWeeks($this->courseWeeks - 1)
            ->endOfWeek()
            ->subDays(2); // Go back to Friday

        // Get Christmas period dates (keep original dates from school settings)
        $christmasStart = $this->school->christmas_start_date->copy();
        $christmasEnd = $this->school->christmas_end_date->copy();

        // Check if there's an overlap
        if ($courseStart->lte($christmasEnd) && $standardEndDate->gte($christmasStart)) {
            $christmasBreakWeeks = $this->calculateChristmasOverlapWeeks($courseStart, $standardEndDate, $christmasStart, $christmasEnd);

            if ($christmasBreakWeeks > 0) {
                // Calculate the actual course end date with Christmas break
                $actualEndDate = $this->calculateCourseEndDate();

                $this->costBreakdown['christmas_break'] = [
                    'has_break' => true,
                    'break_weeks' => $christmasBreakWeeks,
                    'break_start_date' => $christmasStart->format('Y-m-d'),
                    'break_end_date' => $christmasEnd->format('Y-m-d'),
                    'explanation' => $this->generateChristmasBreakExplanation($courseStart, $actualEndDate, $christmasStart, $christmasEnd, $christmasBreakWeeks),
                ];
            }
        }
    }

    /**
     * Generate a clear explanation of the Christmas break impact on course duration.
     */
    private function generateChristmasBreakExplanation(Carbon $courseStart, Carbon $actualEndDate, Carbon $christmasStart, Carbon $christmasEnd, int $breakWeeks): string
    {
        $explanation = "Your {$this->courseWeeks}-week course includes a {$breakWeeks}-week Christmas break ";
        $explanation .= "from " . $christmasStart->format('d M Y') . " to " . $christmasEnd->format('d M Y') . ". ";
        $explanation .= "The course will resume after the Christmas period, extending your end date to " . $actualEndDate->format('d M Y') . " ";
        $explanation .= "to ensure you receive the full {$this->courseWeeks} weeks of instruction.";

        return $explanation;
    }

    /**
     * Add Christmas break information for the second course to the cost breakdown.
     */
    private function addSecondCourseChristmasBreakInformation(): void
    {
        // Initialize second course Christmas break info
        $this->costBreakdown['second_course_christmas_break'] = [
            'has_break' => false,
            'break_weeks' => 0,
            'break_start_date' => null,
            'break_end_date' => null,
            'explanation' => null,
        ];

        // Check if school has Christmas supplement dates defined and second course details are available
        if (!$this->secondSchool || !$this->secondSchool->christmas_start_date || !$this->secondSchool->christmas_end_date || !$this->secondStartDate || !$this->secondCourseWeeks) {
            return;
        }

        // Ensure the start date is a Monday
        $courseStart = $this->secondStartDate->copy();
        if ($courseStart->dayOfWeek !== Carbon::MONDAY) {
            $courseStart = $courseStart->next(Carbon::MONDAY);
        }

        // Calculate the standard course end date (without Christmas breaks)
        $standardEndDate = $courseStart->copy()
            ->addWeeks($this->secondCourseWeeks - 1)
            ->endOfWeek()
            ->subDays(2); // Go back to Friday

        // Get Christmas period dates (keep original dates from school settings)
        $christmasStart = $this->secondSchool->christmas_start_date->copy();
        $christmasEnd = $this->secondSchool->christmas_end_date->copy();

        // Check if there's an overlap
        if ($courseStart->lte($christmasEnd) && $standardEndDate->gte($christmasStart)) {
            $christmasBreakWeeks = $this->calculateChristmasOverlapWeeks($courseStart, $standardEndDate, $christmasStart, $christmasEnd);

            if ($christmasBreakWeeks > 0) {
                // Calculate the actual course end date with Christmas break
                $actualEndDate = $this->calculateSecondCourseEndDate();

                $this->costBreakdown['second_course_christmas_break'] = [
                    'has_break' => true,
                    'break_weeks' => $christmasBreakWeeks,
                    'break_start_date' => $christmasStart->format('Y-m-d'),
                    'break_end_date' => $christmasEnd->format('Y-m-d'),
                    'explanation' => $this->generateSecondCourseChristmasBreakExplanation($courseStart, $actualEndDate, $christmasStart, $christmasEnd, $christmasBreakWeeks),
                ];
            }
        }
    }

    /**
     * Generate a clear explanation of the Christmas break impact on second course duration.
     */
    private function generateSecondCourseChristmasBreakExplanation(Carbon $courseStart, Carbon $actualEndDate, Carbon $christmasStart, Carbon $christmasEnd, int $breakWeeks): string
    {
        $explanation = "Your {$this->secondCourseWeeks}-week second course includes a {$breakWeeks}-week Christmas break ";
        $explanation .= "from " . $christmasStart->format('d M Y') . " to " . $christmasEnd->format('d M Y') . ". ";
        $explanation .= "The second course will resume after the Christmas period, extending your end date to " . $actualEndDate->format('d M Y') . " ";
        $explanation .= "to ensure you receive the full {$this->secondCourseWeeks} weeks of instruction.";

        return $explanation;
    }

    /**
     * Calculate second course tuition and fees.
     */
    private function calculateSecondCourseTuition(): void
    {
        if (!$this->secondCourse || !$this->secondSchool || !$this->secondStartDate || !$this->secondCourseWeeks) {
            return;
        }

        $tuitionPrice = 0;
        $itemName = 'Second Course: ' . $this->secondCourse->name;

        if ($this->secondCourse->pricing_type === 'per_week') {
            // Get pricing information to check for mixed pricing
            $pricingInfo = $this->determinePricingYears($this->secondStartDate, $this->secondCourseWeeks);
            
            if ($pricingInfo['has_mixed_pricing']) {
                // Handle mixed pricing - create separate line items for 2025 and 2026 portions
                $price = $this->getSecondCoursePrice();
                if (!$price) {
                    $this->addError("Could not find price for '{$this->secondCourse->name}' for {$this->secondCourseWeeks} weeks.");
                    return;
                }
                
                $price2025 = (float) $price->price_per_week;
                $price2026 = $price->price_per_week_2026 !== null ? (float) $price->price_per_week_2026 : $price2025;
                
                // Add 2025 portion
                if ($pricingInfo['weeks_2025'] > 0) {
                    $tuition2025 = $price2025 * $pricingInfo['weeks_2025'];
                    $itemName2025 = $itemName . ' (' . $pricingInfo['weeks_2025'] . ' weeks - 2025)';
                    $this->addItem($itemName2025, $tuition2025, 'second_tuition');
                }
                
                // Add 2026 portion
                if ($pricingInfo['weeks_2026'] > 0) {
                    $tuition2026 = $price2026 * $pricingInfo['weeks_2026'];
                    $itemName2026 = $itemName . ' (' . $pricingInfo['weeks_2026'] . ' weeks - 2026)';
                    $this->addItem($itemName2026, $tuition2026, 'second_tuition');
                }
            } else {
                // Single pricing year - use existing logic
                $pricePerWeek = $this->getSecondCoursePricePerWeek();
                if ($pricePerWeek === null) {
                    $this->addError("Could not find weekly price for '{$this->secondCourse->name}' for {$this->secondCourseWeeks} weeks.");
                    return;
                }
                $tuitionPrice = $pricePerWeek * $this->secondCourseWeeks;
                $year = $pricingInfo['use_2026_pricing'] ? '2026' : '2025';
                $itemName .= ' (' . $this->secondCourseWeeks . ' weeks - ' . $year . ')';
                $this->addItem($itemName, $tuitionPrice, 'second_tuition');
            }
        } elseif ($this->secondCourse->pricing_type === 'fixed_schedule') {
            $schedule = $this->getSecondCourseFixedSchedule();
            if (!$schedule) {
                $this->addError("Could not find schedule for '{$this->secondCourse->name}' starting {$this->secondStartDate->toDateString()} for {$this->secondCourseWeeks} weeks.");
                return;
            }
            $tuitionPrice = $schedule->fixed_price;
            // Fixed schedule pricing - determine year based on start date
            $year = $this->secondStartDate->year >= 2026 ? '2026' : '2025';
            $itemName .= ' (' . $schedule->start_date->format('Y-m-d') . ' - ' . $schedule->duration_weeks . ' weeks - ' . $year . ')';
            $this->addItem($itemName, $tuitionPrice, 'second_tuition');
        }

        // Add Second Course Summer Supplement
        if ($this->secondSchool->summer_fee_per_week > 0 && $this->secondSchool->summer_start_date && $this->secondSchool->summer_end_date) {
            $secondCourseEndDate = $this->calculateSecondCourseEndDate();
            $overlapWeeks = $this->calculateSummerSupplementOverlapWeeks(
                $this->secondStartDate,
                $secondCourseEndDate,
                Carbon::parse($this->secondSchool->summer_start_date),
                Carbon::parse($this->secondSchool->summer_end_date)
            );

            // Check if the supplement should be waived based on course duration
            $waiveSupplement = $this->secondSchool->summer_fee_weeks_off !== null && $this->secondCourseWeeks >= $this->secondSchool->summer_fee_weeks_off;

            if ($overlapWeeks > 0 && !$waiveSupplement) {
                $summerFee = $overlapWeeks * $this->secondSchool->summer_fee_per_week;
                $this->addItem('Second Course Summer Supplement', $summerFee, 'fees');
            }
        }
    }

    /**
     * Calculate second course end date using Monday-to-Friday logic.
     */
    private function calculateSecondCourseEndDate(): Carbon
    {
        if (!$this->secondStartDate || !$this->secondCourseWeeks) {
            return $this->secondStartDate->copy();
        }

        // Ensure the start date is a Monday
        $courseStart = $this->secondStartDate->copy();
        if ($courseStart->dayOfWeek !== Carbon::MONDAY) {
            // If not Monday, move to the next Monday
            $courseStart = $courseStart->next(Carbon::MONDAY);
        }

        // Calculate end date accounting for Christmas breaks
        $courseEnd = $this->calculateSecondCourseEndDateWithChristmasBreaks($courseStart, $this->secondCourseWeeks);

        return $courseEnd;
    }

    /**
     * Calculate second course end date accounting for Christmas breaks.
     */
    private function calculateSecondCourseEndDateWithChristmasBreaks(Carbon $courseStart, int $courseWeeks): Carbon
    {
        // Check if school has Christmas supplement dates defined
        if (!$this->secondSchool || !$this->secondSchool->christmas_start_date || !$this->secondSchool->christmas_end_date) {
            // No Christmas period defined, use standard calculation
            return $courseStart->copy()
                ->addWeeks($courseWeeks - 1)
                ->endOfWeek()
                ->subDays(2); // Go back to Friday
        }

        // Calculate the standard course end date first
        $standardEndDate = $courseStart->copy()
            ->addWeeks($courseWeeks - 1)
            ->endOfWeek()
            ->subDays(2); // Go back to Friday

        // Get Christmas period dates (keep original dates from school settings)
        $christmasStart = $this->secondSchool->christmas_start_date->copy();
        $christmasEnd = $this->secondSchool->christmas_end_date->copy();

        // Check if there's an overlap between course period and Christmas period
        $courseEndForOverlapCheck = $standardEndDate->copy();
        if ($courseStart->lte($christmasEnd) && $courseEndForOverlapCheck->gte($christmasStart)) {
            // There is an overlap, calculate Christmas break weeks
            $christmasBreakWeeks = $this->calculateChristmasOverlapWeeks($courseStart, $courseEndForOverlapCheck, $christmasStart, $christmasEnd);

            if ($christmasBreakWeeks > 0) {
                // Extend the course end date by the number of Christmas break weeks
                $adjustedEndDate = $standardEndDate->copy()->addWeeks($christmasBreakWeeks);

                // Ensure the adjusted end date ends on a Friday
                if ($adjustedEndDate->dayOfWeek !== Carbon::FRIDAY) {
                    $adjustedEndDate = $adjustedEndDate->endOfWeek(Carbon::SUNDAY)->subDays(2);
                }

                return $adjustedEndDate;
            }
        }

        return $standardEndDate;
    }

    /**
     * Get the fixed schedule for the second course.
     */
    private function getSecondCourseFixedSchedule()
    {
        if (!$this->secondCourse || $this->secondCourse->pricing_type !== 'fixed_schedule') {
            return null;
        }

        return $this->secondCourse->courseSchedules()
            ->where('start_date', '<=', $this->secondStartDate)
            ->where('duration_weeks', $this->secondCourseWeeks)
            ->orderBy('start_date', 'desc')
            ->first();
    }

    /**
     * Calculate accommodation end date using Monday-to-Friday logic.
     * Accommodation follows the same Monday-to-Friday pattern as courses.
     */
    private function calculateAccommodationEndDate(): Carbon
    {
        if (!$this->startDate || !$this->accommodationWeeks) {
            return $this->startDate->copy();
        }

        $accommodationStart = $this->startDate->copy();
        if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
            $accommodationStart = $accommodationStart->next(Carbon::MONDAY);
        }

        if (!$this->school || !$this->school->christmas_start_date || !$this->school->christmas_end_date) {
            return $accommodationStart->copy()
                ->addWeeks($this->accommodationWeeks - 1)
                ->endOfWeek()
                ->subDays(2);
        }

        $standardEndDate = $accommodationStart->copy()
            ->addWeeks($this->accommodationWeeks - 1)
            ->endOfWeek()
            ->subDays(2);

        $christmasStart = $this->school->christmas_start_date->copy();
        $christmasEnd = $this->school->christmas_end_date->copy();

        $accommodationEndForOverlapCheck = $standardEndDate->copy();
        if ($accommodationStart->lte($christmasEnd) && $accommodationEndForOverlapCheck->gte($christmasStart)) {
            $christmasBreakWeeks = $this->calculateChristmasOverlapWeeks($accommodationStart, $accommodationEndForOverlapCheck, $christmasStart, $christmasEnd);

            if ($christmasBreakWeeks > 0) {
                $adjustedEndDate = $standardEndDate->copy()->addWeeks($christmasBreakWeeks);

                if ($adjustedEndDate->dayOfWeek !== Carbon::FRIDAY) {
                    $adjustedEndDate = $adjustedEndDate->endOfWeek(Carbon::SUNDAY)->subDays(2);
                }

                return $adjustedEndDate;
            }
        }

        return $standardEndDate;
    }

    /**
     * Calculate extended accommodation end date including Christmas extra weeks.
     * This method extends the accommodation end date when Christmas accommodation is selected
     * and there are actual overlapping Christmas extra weeks.
     */
    private function calculateExtendedAccommodationEndDate(): Carbon
    {
        // Start with the regular accommodation end date
        $accommodationEnd = $this->calculateAccommodationEndDate();
        
        // Only extend if Christmas accommodation is selected and we have Christmas dates
        if ($this->christmasAccommodation && $this->christmasStartDate && $this->christmasEndDate && $this->christmasExtraWeeks > 0) {
            // Check if accommodation period overlaps with Christmas period
            $accommodationStartDate = $this->startDate;
            $regularAccommodationEndDate = $this->calculateAccommodationEndDate();
            
            // Calculate overlap using proper logic
            $overlapStart = $accommodationStartDate->gt($this->christmasStartDate) ? $accommodationStartDate : $this->christmasStartDate;
            $overlapEnd = $regularAccommodationEndDate->lt($this->christmasEndDate) ? $regularAccommodationEndDate : $this->christmasEndDate;
            
            // Only proceed if there is actual overlap
            if ($overlapStart->lte($overlapEnd)) {
                // Calculate actual Monday-Friday weeks for the overlapping Christmas period
                $actualExtraWeeks = $this->calculateExtraChristmasWeeks($overlapStart, $overlapEnd);
                
                if ($actualExtraWeeks > 0) {
                    // Extend the accommodation end date by the actual extra weeks
                    // Follow Monday-to-Friday pattern for the extension
                    $accommodationEnd = $accommodationEnd->copy()->addWeeks($actualExtraWeeks);
                }
            }
        }
        
        return $accommodationEnd;
    }

    /**
     * Calculate when the second accommodation should start.
     * Second accommodation starts on the Monday after the first accommodation ends.
     */
    private function calculateSecondAccommodationStartDate(): Carbon
    {
        if (!$this->startDate || !$this->accommodationWeeks) {
            // If no first accommodation, second accommodation starts from course start
            $accommodationStart = $this->startDate->copy();
            if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
                $accommodationStart = $accommodationStart->next(Carbon::MONDAY);
            }
            return $accommodationStart;
        }

        // Calculate when first accommodation ends
        $firstAccommodationEnd = $this->calculateAccommodationEndDate();
        
        // Second accommodation starts on the Monday after first accommodation ends
        $secondAccommodationStart = $firstAccommodationEnd->copy()->addDay()->next(Carbon::MONDAY);
        
        return $secondAccommodationStart;
    }

    /**
     * Calculate second accommodation end date using Monday-to-Friday logic.
     * Second accommodation follows the same Monday-to-Friday pattern as courses.
     */
    private function calculateSecondAccommodationEndDate(): Carbon
    {
        if (!$this->secondAccommodationWeeks) {
            return $this->calculateSecondAccommodationStartDate();
        }

        $accommodationStart = $this->calculateSecondAccommodationStartDate();

        $standardEndDate = $accommodationStart->copy()
            ->addWeeks($this->secondAccommodationWeeks - 1)
            ->endOfWeek()
            ->subDays(2);

        $christmasStart = null;
        $christmasEnd = null;
        if ($this->secondSchool && $this->secondSchool->christmas_start_date && $this->secondSchool->christmas_end_date) {
            $christmasStart = $this->secondSchool->christmas_start_date->copy();
            $christmasEnd = $this->secondSchool->christmas_end_date->copy();
        } elseif ($this->school && $this->school->christmas_start_date && $this->school->christmas_end_date) {
            $christmasStart = $this->school->christmas_start_date->copy();
            $christmasEnd = $this->school->christmas_end_date->copy();
        }

        if ($christmasStart && $christmasEnd) {
            $accommodationEndForOverlapCheck = $standardEndDate->copy();
            if ($accommodationStart->lte($christmasEnd) && $accommodationEndForOverlapCheck->gte($christmasStart)) {
                $christmasBreakWeeks = $this->calculateChristmasOverlapWeeks($accommodationStart, $accommodationEndForOverlapCheck, $christmasStart, $christmasEnd);

                if ($christmasBreakWeeks > 0) {
                    $adjustedEndDate = $standardEndDate->copy()->addWeeks($christmasBreakWeeks);

                    if ($adjustedEndDate->dayOfWeek !== Carbon::FRIDAY) {
                        $adjustedEndDate = $adjustedEndDate->endOfWeek(Carbon::SUNDAY)->subDays(2);
                    }

                    return $adjustedEndDate;
                }
            }
        }

        return $standardEndDate;
    }

    /**
     * Calculate extended second accommodation end date including Christmas extra weeks.
     * This method extends the second accommodation end date when Christmas accommodation is selected
     * and there are actual overlapping Christmas extra weeks.
     * The second accommodation end date should match the course end date when Christmas break applies.
     */
    private function calculateExtendedSecondAccommodationEndDate(): Carbon
    {
        // Start with the regular second accommodation end date
        $accommodationEnd = $this->calculateSecondAccommodationEndDate();
        
        // Only extend if Christmas accommodation is selected and we have Christmas dates
        if ($this->secondChristmasAccommodation && $this->christmasStartDate && $this->christmasEndDate) {
            // Get the course end date to ensure accommodation aligns with it
            // Use the second course end date if we're dealing with a second course
            $courseEndDate = $this->secondCourse ? $this->calculateSecondCourseEndDate() : $this->calculateCourseEndDate();
            
            // When Christmas accommodation is selected, the second accommodation end date
            // should always match the course end date to ensure full coverage of the course duration
            // including any Christmas break periods
            return $courseEndDate;
        }
        
        return $accommodationEnd;
    }

    /**
     * Calculate overlap weeks for summer supplements.
     * Counts actual Monday-to-Friday periods within the overlap.
     * This ensures students pay summer supplement for all overlapping weeks.
     */
    private function calculateSummerSupplementOverlapWeeks(Carbon $range1Start, Carbon $range1End, Carbon $range2Start, Carbon $range2End): int
    {
        $overlapData = $this->calculateSummerSupplementOverlapDetails($range1Start, $range1End, $range2Start, $range2End);
        return $overlapData['weeks'];
    }

    /**
     * Calculate overlap details for summer supplements including weeks count and actual dates.
     * Returns array with weeks count, start date, and end date of the overlap period.
     */
    private function calculateSummerSupplementOverlapDetails(Carbon $range1Start, Carbon $range1End, Carbon $range2Start, Carbon $range2End): array
    {
        // Find the actual overlap period
        $overlapStart = $range1Start->max($range2Start);
        $overlapEnd = $range1End->min($range2End);

        // Check if there is any overlap
        if ($overlapStart->gt($overlapEnd)) {
            return ['weeks' => 0, 'start_date' => null, 'end_date' => null]; // No overlap
        }

        // Calculate weeks by counting Monday-to-Friday periods
        $weeks = 0;
        $current = $overlapStart->copy();
        $actualStartDate = null;
        $actualEndDate = null;

        // Ensure we start from a Monday
        if ($current->dayOfWeek !== Carbon::MONDAY) {
            $current = $current->next(Carbon::MONDAY);
        }

        // Count complete weeks (Monday to Friday) within the overlap period
        while ($current->copy()->addDays(4)->lte($overlapEnd)) { // Monday + 4 days = Friday
            if ($weeks === 0) {
                $actualStartDate = $current->copy(); // First Monday
            }
            $actualEndDate = $current->copy()->addDays(4); // Friday of current week
            $weeks++;
            $current->addWeek(); // Move to next Monday
        }

        return [
            'weeks' => $weeks,
            'start_date' => $actualStartDate,
            'end_date' => $actualEndDate
        ];
    }

    /**
     * Calculate overlap details for Christmas supplements including weeks count and actual dates.
     * Returns array with weeks count, start date, and end date of the overlap period.
     */
    private function calculateChristmasSupplementOverlapDetails(Carbon $range1Start, Carbon $range1End, Carbon $range2Start, Carbon $range2End): array
    {
        // Find the actual overlap period
        $overlapStart = $range1Start->max($range2Start);
        $overlapEnd = $range1End->min($range2End);

        // Check if there is any overlap
        if ($overlapStart->gt($overlapEnd)) {
            return ['weeks' => 0, 'start_date' => null, 'end_date' => null]; // No overlap
        }

        // Calculate weeks by counting Monday-to-Friday periods
        $weeks = 0;
        $current = $overlapStart->copy();
        $actualStartDate = null;
        $actualEndDate = null;

        // Ensure we start from a Monday
        if ($current->dayOfWeek !== Carbon::MONDAY) {
            $current = $current->next(Carbon::MONDAY);
        }

        // Count complete weeks (Monday to Friday) within the overlap period
        while ($current->copy()->addDays(4)->lte($overlapEnd)) { // Monday + 4 days = Friday
            if ($weeks === 0) {
                $actualStartDate = $current->copy(); // First Monday
            }
            $actualEndDate = $current->copy()->addDays(4); // Friday of current week
            $weeks++;
            $current->addWeek(); // Move to next Monday
        }

        return [
            'weeks' => $weeks,
            'start_date' => $actualStartDate,
            'end_date' => $actualEndDate
        ];
    }

    /**
     * Calculate Monday-Friday weeks for Extra Christmas Accommodation.
     * This method ensures that only full Monday-Friday weeks are counted for extra accommodation.
     */
    private function calculateExtraChristmasWeeks(Carbon $christmasStart, Carbon $christmasEnd): int
    {
        // Calculate weeks by counting Monday-to-Friday periods
        $weeks = 0;
        $current = $christmasStart->copy();

        // Ensure we start from a Monday
        if ($current->dayOfWeek !== Carbon::MONDAY) {
            $current = $current->next(Carbon::MONDAY);
        }

        // Count complete weeks (Monday to Friday) within the Christmas period
        while ($current->copy()->addDays(4)->lte($christmasEnd)) { // Monday + 4 days = Friday
            $weeks++;
            $current->addWeek(); // Move to next Monday
        }

        return $weeks;
    }

    /**
     * Calculate the number of weeks during which the student is under the configured guardianship age.
     * A week is defined as Monday to Friday, and any additional days count as a full week.
     * The calculation stops once the student reaches the guardianship threshold age.
     */
    private function calculateGuardianshipQualifyingWeeks(): int
    {
        if (!$this->studentBirthday || !$this->startDate || !$this->accommodationWeeks) {
            return 0;
        }

        // Calculate the student's threshold birthday based on school-configured guardianship age
        $guardianshipAge = $this->school->guardianship_fee_age ?? 18;
        $thresholdBirthday = $this->studentBirthday->copy()->addYears($guardianshipAge);

        // Ensure the start date is a Monday
        $accommodationStart = $this->startDate->copy();
        if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
            $accommodationStart = $accommodationStart->next(Carbon::MONDAY);
        }

        // Calculate accommodation end date
        $accommodationEnd = $accommodationStart->copy()
            ->addWeeks($this->accommodationWeeks - 1)
            ->endOfWeek()
            ->subDays(2); // Go back to Friday

        // If student is already at or above the guardianship age at the start, no qualifying weeks
        if ($accommodationStart->gte($thresholdBirthday)) {
            return 0;
        }

        // If student doesn't reach the guardianship age during accommodation period, all weeks qualify
        if ($accommodationEnd->lt($thresholdBirthday)) {
            return $this->accommodationWeeks;
        }

        // Student reaches guardianship age during accommodation period - count weeks until threshold birthday
        $qualifyingWeeks = 0;
        $current = $accommodationStart->copy();

        while ($current->lt($thresholdBirthday)) {
            // Check if the current week (Monday to Friday) is before threshold birthday
            $weekEnd = $current->copy()->addDays(4); // Friday of current week
            
            if ($current->lt($thresholdBirthday)) {
                $qualifyingWeeks++;
                
                // If student reaches guardianship age during this week, this is the last qualifying week
                if ($weekEnd->gte($thresholdBirthday)) {
                    break;
                }
            }
            
            $current->addWeek(); // Move to next Monday
        }

        return $qualifyingWeeks;
    }

    /**
     * Calculate the number of weeks the student is under the configured guardianship age for second accommodation.
     * Uses the same Monday-to-Friday logic as the first accommodation but considers the second accommodation period.
     * The calculation considers the combined course duration when determining the second accommodation period.
     */
    private function calculateSecondAccommodationGuardianshipQualifyingWeeks(): int
    {
        if (!$this->studentBirthday || !$this->startDate || !$this->secondAccommodationWeeks) {
            return 0;
        }

        // Calculate the student's threshold birthday based on school-configured guardianship age
        $guardianshipAge = $this->school->guardianship_fee_age ?? 18;
        $thresholdBirthday = $this->studentBirthday->copy()->addYears($guardianshipAge);

        // Calculate second accommodation start date (after first course + first accommodation)
        $secondAccommodationStart = $this->calculateSecondAccommodationStartDate();
        
        // Ensure the start date is a Monday
        if ($secondAccommodationStart->dayOfWeek !== Carbon::MONDAY) {
            $secondAccommodationStart = $secondAccommodationStart->next(Carbon::MONDAY);
        }

        // Calculate second accommodation end date
        $secondAccommodationEnd = $secondAccommodationStart->copy()
            ->addWeeks($this->secondAccommodationWeeks - 1)
            ->endOfWeek()
            ->subDays(2); // Go back to Friday

        // If student is already at or above the guardianship age at the start of second accommodation, no qualifying weeks
        if ($secondAccommodationStart->gte($thresholdBirthday)) {
            return 0;
        }

        // If student doesn't reach the guardianship age during second accommodation period, all weeks qualify
        if ($secondAccommodationEnd->lt($thresholdBirthday)) {
            return $this->secondAccommodationWeeks;
        }

        // Student reaches guardianship age during second accommodation period - count weeks until threshold birthday
        $qualifyingWeeks = 0;
        $current = $secondAccommodationStart->copy();

        while ($current->lt($thresholdBirthday)) {
            // Check if the current week (Monday to Friday) is before threshold birthday
            $weekEnd = $current->copy()->addDays(4); // Friday of current week
            
            if ($current->lt($thresholdBirthday)) {
                $qualifyingWeeks++;
                
                // If student reaches guardianship age during this week, this is the last qualifying week
                if ($weekEnd->gte($thresholdBirthday)) {
                    break;
                }
            }
            
            $current->addWeek(); // Move to next Monday
        }

        return $qualifyingWeeks;
    }

     /**
      * Calculate total weeks from both courses when second course is present.
      * Returns combined weeks if both courses exist, otherwise returns first course weeks.
      */
     private function getTotalCombinedWeeks(): int
     {
         $totalWeeks = $this->courseWeeks ?? 0;
         
         // Add second course weeks if second course is present
         if ($this->secondCourse && $this->secondCourseWeeks) {
             $totalWeeks += $this->secondCourseWeeks;
         }
         
         return $totalWeeks;
     }

     /**
      * Get the cutoff date from settings.
      * Returns null if no cutoff date is set.
      */
     private function getCutoffDate(): ?Carbon
     {
         $settings = Setting::getAllSettings();
         if ($settings && $settings->cutoff_date) {
             try {
                 return Carbon::parse($settings->cutoff_date);
             } catch (\Exception $e) {
                 Log::warning('Invalid cutoff date in settings: ' . $settings->cutoff_date);
                 return null;
             }
         }
         return null;
     }

     /**
      * Get the quotation extraction date from settings or use current date.
      * Returns the override date if set, otherwise returns current system date.
      */
     private function getQuotationExtractionDate(): Carbon
     {
         $settings = Setting::getAllSettings();
         if ($settings && $settings->quotation_extraction_date) {
             try {
                 return Carbon::parse($settings->quotation_extraction_date);
             } catch (\Exception $e) {
                 Log::warning('Invalid quotation extraction date in settings: ' . $settings->quotation_extraction_date);
                 return Carbon::now();
             }
         }
         return Carbon::now();
     }

     /**
      * Determine pricing year(s) based on quotation extraction date, cutoff date and course dates.
      * Returns array with pricing information for each week.
      */
     private function determinePricingYears(Carbon $courseStartDate, int $courseWeeks): array
     {
         $cutoffDate = $this->getCutoffDate();
         $quotationExtractionDate = $this->getQuotationExtractionDate();
         $courseEndDate = $this->calculateCourseEndDateFromStart($courseStartDate, $courseWeeks);
         
         // If no cutoff date is set, use simple year-based logic
         if (!$cutoffDate) {
             $startYear = $courseStartDate->year;
             return [
                 'use_2025_for_all' => $startYear < 2026,
                 'use_2026_for_all' => $startYear >= 2026,
                 'use_2026_pricing' => $startYear >= 2026,
                 'has_mixed_pricing' => false,
                 'mixed_pricing' => false,
                 'weeks_2025' => $startYear < 2026 ? $courseWeeks : 0,
                 'weeks_2026' => $startYear >= 2026 ? $courseWeeks : 0
             ];
         }
         
         // NEW RULE: If quotation extraction date < cutoff date → Always apply 2025 pricing (even if course runs into 2026)
         if ($quotationExtractionDate->lt($cutoffDate)) {
             return [
                 'use_2025_for_all' => true,
                 'use_2026_for_all' => false,
                 'use_2026_pricing' => false,
                 'has_mixed_pricing' => false,
                 'mixed_pricing' => false,
                 'weeks_2025' => $courseWeeks,
                 'weeks_2026' => 0
             ];
         }
         
         // If quotation extraction date >= cutoff date, apply combined pricing rules based on course start date
         
         // Rule 1: If course start date < cutoff date → Apply 2025 prices for entire duration
         if ($courseStartDate->lt($cutoffDate)) {
             return [
                 'use_2025_for_all' => true,
                 'use_2026_for_all' => false,
                 'use_2026_pricing' => false,
                 'has_mixed_pricing' => false,
                 'mixed_pricing' => false,
                 'weeks_2025' => $courseWeeks,
                 'weeks_2026' => 0
             ];
         }
         
         // Rule 4: If course start date is in 2026 → Always apply 2026 prices
         if ($courseStartDate->year >= 2026) {
             return [
                 'use_2025_for_all' => false,
                 'use_2026_for_all' => true,
                 'use_2026_pricing' => true,
                 'has_mixed_pricing' => false,
                 'mixed_pricing' => false,
                 'weeks_2025' => 0,
                 'weeks_2026' => $courseWeeks
             ];
         }
         
         // Rule 2: If course start date ≥ cutoff date and course is fully within 2025
         $endOf2025 = Carbon::create(2025, 12, 31);
         if ($courseStartDate->gte($cutoffDate) && $courseEndDate->lte($endOf2025)) {
             return [
                 'use_2025_for_all' => true,
                 'use_2026_for_all' => false,
                 'use_2026_pricing' => false,
                 'has_mixed_pricing' => false,
                 'mixed_pricing' => false,
                 'weeks_2025' => $courseWeeks,
                 'weeks_2026' => 0
             ];
         }
         
         // Rule 3: If course start date ≥ cutoff date and course continues into 2026
         if ($courseStartDate->gte($cutoffDate) && $courseEndDate->gt($endOf2025)) {
             // Calculate weeks in 2025 vs 2026
             $startOf2026 = Carbon::create(2026, 1, 1);
             
             // Count Monday-to-Friday weeks in 2025
             $weeks2025 = 0;
             $currentDate = $courseStartDate->copy();
             while ($currentDate->lte($endOf2025) && $weeks2025 < $courseWeeks) {
                 if ($currentDate->dayOfWeek === Carbon::MONDAY) {
                     $weeks2025++;
                 }
                 $currentDate->addDay();
             }
             
             $weeks2026 = $courseWeeks - $weeks2025;
             
             return [
                 'use_2025_for_all' => false,
                 'use_2026_for_all' => false,
                 'use_2026_pricing' => false,
                 'has_mixed_pricing' => true,
                 'mixed_pricing' => true,
                 'weeks_2025' => $weeks2025,
                 'weeks_2026' => $weeks2026
             ];
         }
         
         // Fallback to 2025 pricing
         return [
             'use_2025_for_all' => true,
             'use_2026_for_all' => false,
             'use_2026_pricing' => false,
             'has_mixed_pricing' => false,
             'mixed_pricing' => false,
             'weeks_2025' => $courseWeeks,
             'weeks_2026' => 0
         ];
     }

     /**
      * Calculate course end date from start date and weeks (helper method).
      */
     private function calculateCourseEndDateFromStart(Carbon $startDate, int $weeks): Carbon
     {
         $endDate = $startDate->copy();
         $weeksAdded = 0;
         
         while ($weeksAdded < $weeks) {
             if ($endDate->dayOfWeek >= Carbon::MONDAY && $endDate->dayOfWeek <= Carbon::FRIDAY) {
                 $weeksAdded++;
                 if ($weeksAdded < $weeks) {
                     $endDate->addWeek();
                 }
             } else {
                 $endDate->addDay();
             }
         }
         
         // Adjust to Friday of the final week
         while ($endDate->dayOfWeek !== Carbon::FRIDAY) {
             $endDate->addDay();
         }
         
         return $endDate;
     }

     /**
      * Get course price object based on duration.
      * Uses combined weeks from both courses when second course is present.
      * Returns null if no matching active price range is found.
      */
     private function getCoursePrice(): ?CoursePrice
     {
         if (!$this->course || !$this->courseWeeks) {
             return null;
         }

         // Use combined weeks if second course is present, otherwise use individual course weeks
         $weeksForPricing = $this->secondCourse && $this->secondCourseWeeks ? $this->getTotalCombinedWeeks() : $this->courseWeeks;

         // Find the active price range that includes the requested number of weeks
         return CoursePrice::where('course_id', $this->course->id)
                           ->where('min_weeks', '<=', $weeksForPricing)
                           ->where('max_weeks', '>=', $weeksForPricing)
                           ->where('active', true)
                           ->first();
     }

     /**
      * Get course price per week based on duration and pricing rules.
      * Uses combined weeks from both courses when second course is present.
      * Returns null if no matching active price range is found.
      */
     private function getCoursePricePerWeek(): ?float
     {
         if (!$this->course || !$this->courseWeeks) {
             return null;
         }

         // Use combined weeks if second course is present, otherwise use individual course weeks
         $weeksForPricing = $this->secondCourse && $this->secondCourseWeeks ? $this->getTotalCombinedWeeks() : $this->courseWeeks;

         // Find the active price range that includes the requested number of weeks
         $price = CoursePrice::where('course_id', $this->course->id)
                             ->where('min_weeks', '<=', $weeksForPricing)
                             ->where('max_weeks', '>=', $weeksForPricing)
                             ->where('active', true)
                             ->orderBy('min_weeks', 'desc') // Prioritize the narrowest matching range if overlapping
                             ->first();

         if (!$price) {
             return null;
         }

         // Apply pricing rules based on cutoff date
         $pricingInfo = $this->determinePricingYears($this->startDate, $this->courseWeeks);
         
         if ($pricingInfo['has_mixed_pricing']) {
             // For mixed pricing, we need to calculate weighted average
             $price2025 = (float) $price->price_per_week;
             $price2026 = $price->price_per_week_2026 !== null ? (float) $price->price_per_week_2026 : $price2025;
             
             $totalWeeks = $pricingInfo['weeks_2025'] + $pricingInfo['weeks_2026'];
             if ($totalWeeks > 0) {
                 return (($price2025 * $pricingInfo['weeks_2025']) + ($price2026 * $pricingInfo['weeks_2026'])) / $totalWeeks;
             }
         }
         
         if ($pricingInfo['use_2026_pricing'] && $price->price_per_week_2026 !== null) {
             return (float) $price->price_per_week_2026;
         }
         
         return (float) $price->price_per_week;
     }

     /**
      * Get second course price object based on duration.
      * Uses combined weeks from both courses for pricing calculation.
      * Returns null if no matching active price range is found.
      */
     private function getSecondCoursePrice(): ?CoursePrice
     {
         if (!$this->secondCourse || !$this->secondCourseWeeks) {
             return null;
         }

         // Use combined weeks for pricing calculation
         $weeksForPricing = $this->getTotalCombinedWeeks();

         // Find the active price range that includes the combined number of weeks
         return CoursePrice::where('course_id', $this->secondCourse->id)
                           ->where('min_weeks', '<=', $weeksForPricing)
                           ->where('max_weeks', '>=', $weeksForPricing)
                           ->where('active', true)
                           ->orderBy('min_weeks', 'desc') // Prioritize the narrowest matching range if overlapping
                           ->first();
     }

     /**
      * Get second course price per week based on duration and pricing rules.
      * Uses combined weeks from both courses for pricing calculation.
      * Returns null if no matching active price range is found.
      */
     private function getSecondCoursePricePerWeek(): ?float
     {
         if (!$this->secondCourse || !$this->secondCourseWeeks) {
             return null;
         }

         // Use combined weeks for pricing calculation
         $weeksForPricing = $this->getTotalCombinedWeeks();

         // Find the active price range that includes the combined number of weeks
         $price = CoursePrice::where('course_id', $this->secondCourse->id)
                             ->where('min_weeks', '<=', $weeksForPricing)
                             ->where('max_weeks', '>=', $weeksForPricing)
                             ->where('active', true)
                             ->orderBy('min_weeks', 'desc') // Prioritize the narrowest matching range if overlapping
                             ->first();

         if (!$price) {
             return null;
         }

         // Apply pricing rules based on cutoff date for second course
         $pricingInfo = $this->determinePricingYears($this->secondStartDate, $this->secondCourseWeeks);
         
         if ($pricingInfo['has_mixed_pricing']) {
             // For mixed pricing, we need to calculate weighted average
             $price2025 = (float) $price->price_per_week;
             $price2026 = $price->price_per_week_2026 !== null ? (float) $price->price_per_week_2026 : $price2025;
             
             $totalWeeks = $pricingInfo['weeks_2025'] + $pricingInfo['weeks_2026'];
             if ($totalWeeks > 0) {
                 return (($price2025 * $pricingInfo['weeks_2025']) + ($price2026 * $pricingInfo['weeks_2026'])) / $totalWeeks;
             }
         }
         
         if ($pricingInfo['use_2026_pricing'] && $price->price_per_week_2026 !== null) {
             return (float) $price->price_per_week_2026;
         }
         
         return (float) $price->price_per_week;
     }

     /**
      * Get fixed course schedule based on start date and duration.
      * Returns null if no matching active schedule is found.
      */
     private function getCourseFixedSchedule(): ?CourseSchedule
     {
         if (!$this->course || !$this->startDate || !$this->courseWeeks) {
             return null;
         }

         // Find the active schedule matching the exact start date and duration
         return CourseSchedule::where('course_id', $this->course->id)
                              ->where('start_date', $this->startDate->toDateString())
                              ->where('duration_weeks', $this->courseWeeks)
                              ->where('active', true)
                              ->first();
     }


    /**
     * Calculate the accommodation cost.
     */
    private function calculateAccommodationCost(): void
    {
        if (!$this->accommodation || !$this->accommodationWeeks) return;

        // Get pricing information to check for mixed pricing
        $pricingInfo = $this->determinePricingYears($this->startDate, $this->accommodationWeeks);
        
        $juniorSettings = $this->course?->juniorSettings;
        if ($juniorSettings && $juniorSettings->includes_accommodation) {
            if ($pricingInfo['has_mixed_pricing']) {
                if ($pricingInfo['weeks_2025'] > 0) {
                    $itemName2025 = $this->accommodation->name . ' (' . $pricingInfo['weeks_2025'] . ' weeks - 2025)';
                    $this->addItem($itemName2025, 0, 'accommodation', true);
                }
                if ($pricingInfo['weeks_2026'] > 0) {
                    $itemName2026 = $this->accommodation->name . ' (' . $pricingInfo['weeks_2026'] . ' weeks - 2026)';
                    $this->addItem($itemName2026, 0, 'accommodation', true);
                }
            } else {
                $year = $pricingInfo['use_2026_pricing'] ? '2026' : '2025';
                $itemName = $this->accommodation->name . ' (' . $this->accommodationWeeks . ' weeks - ' . $year . ')';
                $this->addItem($itemName, 0, 'accommodation', true);
            }
        } else {
        // Initialize price per week variables for both mixed and single pricing scenarios
        $pricePerWeek = null;
        $pricePerWeek2025 = null;
        $pricePerWeek2026 = null;
        
        if ($pricingInfo['has_mixed_pricing']) {
            // Handle mixed pricing - create separate line items for 2025 and 2026 portions
            $accommodationPrice = $this->getAccommodationPrice();
            if ($accommodationPrice) {
                $pricePerWeek2025 = (float) $accommodationPrice->price_per_week;
                $pricePerWeek2026 = $accommodationPrice->price_per_week_2026 !== null ? (float) $accommodationPrice->price_per_week_2026 : $pricePerWeek2025;
                
                // Add 2025 portion
                if ($pricingInfo['weeks_2025'] > 0) {
                    $accommodation2025 = $pricePerWeek2025 * $pricingInfo['weeks_2025'];
                    $itemName2025 = $this->accommodation->name . ' (' . $pricingInfo['weeks_2025'] . ' weeks - 2025)';
                    $this->addItem($itemName2025, $accommodation2025, 'accommodation');
                }
                
                // Add 2026 portion
                if ($pricingInfo['weeks_2026'] > 0) {
                    $accommodation2026 = $pricePerWeek2026 * $pricingInfo['weeks_2026'];
                    $itemName2026 = $this->accommodation->name . ' (' . $pricingInfo['weeks_2026'] . ' weeks - 2026)';
                    $this->addItem($itemName2026, $accommodation2026, 'accommodation');
                }
                
                // For backward compatibility with Christmas extra weeks calculation,
                // set pricePerWeek to the 2025 price (since Christmas is typically in 2025)
                $pricePerWeek = $pricePerWeek2025;
            } else {
                throw new \Exception("No accommodation price defined for {$this->accommodation->name} for {$this->accommodationWeeks} weeks");
            }
        } else {
            // Single pricing year - use existing logic with year label
            $pricePerWeek = $this->getAccommodationPricePerWeek();
            if ($pricePerWeek !== null) {
                $accommodationPrice = $pricePerWeek * $this->accommodationWeeks;
                $year = $pricingInfo['use_2026_pricing'] ? '2026' : '2025';
                $itemName = $this->accommodation->name . ' (' . $this->accommodationWeeks . ' weeks - ' . $year . ')';
                $this->addItem($itemName, $accommodationPrice, 'accommodation');
            } else {
                $this->addError("No accommodation price defined for {$this->accommodation->name} for {$this->accommodationWeeks} weeks in " . ($pricingInfo['use_2026_pricing'] ? '2026' : '2025'));
                return;
            }
        }
        }

        // Add Placement Fee (doesn't depend on pricePerWeek) - one-time fee uses 2025 pricing if course starts in 2025
        $hasAccommFeeWaiver = collect($this->costBreakdown['discounts'])->contains('applied_to', 'accommodation_fee_waiver');
        
        // One-time fee: use 2025 pricing if course starts in 2025, otherwise use 2026
        $yearForPricing = $this->startDate->year === 2025 ? 2025 : 2026;
        $accommodationFee = $this->school->getFeeByYear('accommodation_fee', Carbon::create($yearForPricing, 1, 1));
        
        if ($juniorSettings && $juniorSettings->includes_accommodation_placement) {
            $this->addItem('Accommodation Placement Fee', 0, 'fees', true);
        } elseif ($accommodationFee > 0 && !$hasAccommFeeWaiver) {
             $this->addItem('Accommodation Placement Fee', $accommodationFee, 'fees');
        } elseif ($hasAccommFeeWaiver) {
             Log::info('Accommodation placement fee waived.');
        }

        // Calculate accommodation end date using Monday-to-Friday logic
        $accommodationEndDate = $this->calculateAccommodationEndDate();

        // Add Accommodation Summer Supplement (using cutoff date pricing rules)
        $pricingYears = $this->determinePricingYears($this->startDate, $this->accommodationWeeks);
        
        // Get 2025 and 2026 summer supplement details
        $summerFee2025 = $this->accommodation->summer_fee_per_week;
        $summerStart2025 = $this->accommodation->summer_start_date;
        $summerEnd2025 = $this->accommodation->summer_end_date;
        
        $summerFee2026 = $this->accommodation->summer_fee_per_week_2026;
        $summerStart2026 = $this->accommodation->summer_start_date_2026;
        $summerEnd2026 = $this->accommodation->summer_end_date_2026;
        
        // Apply summer supplement based on pricing rules
        if ($pricingYears['has_mixed_pricing']) {
            // Mixed pricing: apply both 2025 and 2026 supplements if applicable
            if ($summerFee2025 > 0 && $summerStart2025 && $summerEnd2025) {
                $overlapDetails = $this->calculateSummerSupplementOverlapDetails(
                    $this->startDate,
                    $accommodationEndDate,
                    Carbon::parse($summerStart2025),
                    Carbon::parse($summerEnd2025)
                );
                if ($overlapDetails['weeks'] > 0) {
                    $summerFee = $overlapDetails['weeks'] * $summerFee2025;
                    $itemName = "Accommodation Summer Supplement (2025):\n" .
                               "Start date: " . $overlapDetails['start_date']->format('j M Y') . "\n" .
                               "End date: " . $overlapDetails['end_date']->format('j M Y') . "\n" .
                               "Duration: " . $overlapDetails['weeks'] . " " . ($overlapDetails['weeks'] === 1 ? 'week' : 'weeks');
                    $this->addItem($itemName, $summerFee, 'fees');
                }
            }
            
            if ($summerFee2026 > 0 && $summerStart2026 && $summerEnd2026) {
                $overlapDetails = $this->calculateSummerSupplementOverlapDetails(
                    $this->startDate,
                    $accommodationEndDate,
                    Carbon::parse($summerStart2026),
                    Carbon::parse($summerEnd2026)
                );
                if ($overlapDetails['weeks'] > 0) {
                    $summerFee = $overlapDetails['weeks'] * $summerFee2026;
                    $itemName = "Accommodation Summer Supplement (2026):\n" .
                               "Start date: " . $overlapDetails['start_date']->format('j M Y') . "\n" .
                               "End date: " . $overlapDetails['end_date']->format('j M Y') . "\n" .
                               "Duration: " . $overlapDetails['weeks'] . " " . ($overlapDetails['weeks'] === 1 ? 'week' : 'weeks');
                    $this->addItem($itemName, $summerFee, 'fees');
                }
            }
        } else {
            // Single year pricing
            $yearLabel = $pricingYears['use_2026_pricing'] ? '2026' : '2025';
            $summerFeePerWeek = $pricingYears['use_2026_pricing'] ? $summerFee2026 : $summerFee2025;
            $summerStartDate = $pricingYears['use_2026_pricing'] ? $summerStart2026 : $summerStart2025;
            $summerEndDate = $pricingYears['use_2026_pricing'] ? $summerEnd2026 : $summerEnd2025;
            
            if ($summerFeePerWeek > 0 && $summerStartDate && $summerEndDate) {
                $overlapDetails = $this->calculateSummerSupplementOverlapDetails(
                    $this->startDate,
                    $accommodationEndDate,
                    Carbon::parse($summerStartDate),
                    Carbon::parse($summerEndDate)
                );
                if ($overlapDetails['weeks'] > 0) {
                    $summerFee = $overlapDetails['weeks'] * $summerFeePerWeek;
                    $itemName = "Accommodation Summer Supplement ({$yearLabel}):\n" .
                               "Start date: " . $overlapDetails['start_date']->format('j M Y') . "\n" .
                               "End date: " . $overlapDetails['end_date']->format('j M Y') . "\n" .
                               "Duration: " . $overlapDetails['weeks'] . " " . ($overlapDetails['weeks'] === 1 ? 'week' : 'weeks');
                    $this->addItem($itemName, $summerFee, 'fees');
                }
            }
        }



        // Add Accommodation Christmas Supplement & Extra Weeks Cost
        // Apply Christmas supplement only when user selected Christmas AND admin did not block it
        $christmasSupplementApplies = $this->christmasAccommodation && !$this->accommodation->requires_christmas_supplement;

        if ($christmasSupplementApplies) {
            // Check for 2025 Christmas overlap
            if ($this->christmasStartDate && $this->christmasEndDate) {
                $overlapDetails2025 = $this->calculateChristmasSupplementOverlapDetails(
                    $this->startDate,
                    $accommodationEndDate,
                    $this->christmasStartDate,
                    $this->christmasEndDate
                );

                if ($overlapDetails2025['weeks'] > 0) {
                    $christmasFee2025 = $this->school->getFeeByYear('christmas_fee_per_week', Carbon::create(2025, 1, 1));
                    
                    if ($christmasFee2025 > 0) {
                        $christmasFee = $overlapDetails2025['weeks'] * $christmasFee2025;
                        $itemName = "Accommodation Christmas Supplement (2025):\n" .
                                   "Start date: " . $overlapDetails2025['start_date']->format('j M Y') . "\n" .
                                   "End date: " . $overlapDetails2025['end_date']->format('j M Y') . "\n" .
                                   "Duration: " . $overlapDetails2025['weeks'] . " " . ($overlapDetails2025['weeks'] === 1 ? 'week' : 'weeks');
                        $this->addItem($itemName, $christmasFee, 'fees');
                    }
                }
            }
            
            // Check for 2026 Christmas overlap
            if ($this->school->christmas_start_date_2026 && $this->school->christmas_end_date_2026) {
                $overlapDetails2026 = $this->calculateChristmasSupplementOverlapDetails(
                    $this->startDate,
                    $accommodationEndDate,
                    $this->school->christmas_start_date_2026,
                    $this->school->christmas_end_date_2026
                );

                if ($overlapDetails2026['weeks'] > 0) {
                    $christmasFee2026 = $this->school->getFeeByYear('christmas_fee_per_week', Carbon::create(2026, 1, 1));
                    
                    if ($christmasFee2026 > 0) {
                        $christmasFee = $overlapDetails2026['weeks'] * $christmasFee2026;
                        $itemName = "Accommodation Christmas Supplement (2026):\n" .
                                   "Start date: " . $overlapDetails2026['start_date']->format('j M Y') . "\n" .
                                   "End date: " . $overlapDetails2026['end_date']->format('j M Y') . "\n" .
                                   "Duration: " . $overlapDetails2026['weeks'] . " " . ($overlapDetails2026['weeks'] === 1 ? 'week' : 'weeks');
                        $this->addItem($itemName, $christmasFee, 'fees');
                    }
                }
            }
        }

        // Add note and extra weeks cost ONLY if Christmas accommodation was explicitly requested
        if ($this->christmasAccommodation && ($this->christmasStartDate && $this->christmasEndDate)) {
            $noteText = 'Includes accommodation during Christmas period: ' .
                $this->christmasStartDate->format('M j, Y') . ' to ' . $this->christmasEndDate->format('M j, Y');

            // Add extra weeks cost if applicable - calculate Monday-Friday weeks only
            if ($this->christmasExtraWeeks > 0) {
                Log::info('Attempting to calculate Extra Christmas Weeks cost.', [
                    'christmasExtraWeeks' => $this->christmasExtraWeeks
                ]);
                
                // Check if accommodation period overlaps with Christmas period
                $accommodationStartDate = $this->startDate;
                $accommodationEndDate = $this->calculateAccommodationEndDate();
                
                // Calculate overlap using proper logic
                $overlapStart = $accommodationStartDate->gt($this->christmasStartDate) ? $accommodationStartDate : $this->christmasStartDate;
                $overlapEnd = $accommodationEndDate->lt($this->christmasEndDate) ? $accommodationEndDate : $this->christmasEndDate;
                
                // Debug information for overlap calculation
                Log::info('Overlap calculation debug:', [
                    'accommodationStartDate' => $accommodationStartDate->format('Y-m-d'),
                    'accommodationEndDate' => $accommodationEndDate->format('Y-m-d'),
                    'christmasStartDate' => $this->christmasStartDate->format('Y-m-d'),
                    'christmasEndDate' => $this->christmasEndDate->format('Y-m-d'),
                    'overlapStart' => $overlapStart->format('Y-m-d'),
                    'overlapEnd' => $overlapEnd->format('Y-m-d'),
                    'hasOverlap' => $overlapStart->lte($overlapEnd) ? 'Yes' : 'No'
                ]);
                
                // Only proceed if there is actual overlap
                if ($overlapStart->lte($overlapEnd)) {
                    // Calculate actual Monday-Friday weeks for the overlapping Christmas period
                    $actualExtraWeeks = $this->calculateExtraChristmasWeeks($overlapStart, $overlapEnd);
                    
                    Log::info('Extra Christmas weeks calculation:', ['actualExtraWeeks' => $actualExtraWeeks]);
                    
                    // Force at least 1 week if Christmas accommodation was explicitly requested
                    // This ensures the Extra Christmas Accommodation always appears when requested
                    if ($this->christmasExtraWeeks > 0 && $actualExtraWeeks == 0) {
                        $actualExtraWeeks = 1;
                        Log::info('Forcing minimum 1 week for Extra Christmas Accommodation');
                    }
                    
                    if ($actualExtraWeeks > 0) {
                        $extraWeeksText = $actualExtraWeeks . ' extra ' .
                            ($actualExtraWeeks === 1 ? 'week' : 'weeks');
                        $noteText .= ' (' . $extraWeeksText . ')';

                        // Use the previously fetched $pricePerWeek for the calculation
                        if ($pricePerWeek !== null) {
                            $extraAccommodationItem = "Extra Christmas Accommodation:\n" .
                                                      "Start date: " . $overlapStart->format('j M Y') . "\n" .
                                                      "End date: " . $overlapEnd->format('j M Y') . "\n" .
                                                      "Duration: " . $actualExtraWeeks . " " . ($actualExtraWeeks === 1 ? 'week' : 'weeks');
                            $extraWeeksCost = $actualExtraWeeks * $pricePerWeek;
                            Log::info('Adding Extra Christmas Accommodation item:', ['name' => $extraAccommodationItem, 'amount' => $extraWeeksCost]);
                            $this->addItem($extraAccommodationItem, $extraWeeksCost, 'fees');
                        } else {
                            // Log warning and add error if price is missing but extra weeks were requested
                            Log::warning('Cannot add Extra Christmas Accommodation cost because base pricePerWeek is null.', ['weeks' => $actualExtraWeeks]);
                            $this->addError("Could not calculate cost for extra Christmas weeks because the base accommodation price is missing.");
                        }
                    } else {
                        Log::info('No full Monday-Friday weeks overlap between accommodation and Christmas period.');
                    }
                } else {
                    Log::info('No overlap between accommodation period and Christmas period - Extra Christmas Accommodation not applicable.');
                }
            }
            $this->costBreakdown['notes'][] = $noteText;
        }

        // Add Private Bathroom Option Fee for normal accommodation weeks only (using cutoff date pricing rules)
        if ($this->privateBathroomOption && $this->accommodation->getEnabledByYear('private_bathroom_enabled', $this->startDate)) {
            $pricingYears = $this->determinePricingYears($this->startDate, $this->accommodationWeeks);
            
            if ($pricingYears['has_mixed_pricing']) {
                // Mixed pricing: create separate line items for 2025 and 2026 portions
                $privateBathroomFee2025 = $this->accommodation->getFeeByYear('private_bathroom_fee', Carbon::create(2025, 1, 1));
                $privateBathroomFee2026 = $this->accommodation->getFeeByYear('private_bathroom_fee', Carbon::create(2026, 1, 1));
                
                if ($pricingYears['weeks_2025'] > 0 && $privateBathroomFee2025 > 0) {
                    $weeks2025 = min($this->accommodationWeeks, $pricingYears['weeks_2025']);
                    $privateBathroomFeeTotal2025 = $privateBathroomFee2025 * $weeks2025;
                    $itemName2025 = "Private Bathroom (2025) (" . $weeks2025 . ' weeks)';
                    $this->addItem($itemName2025, $privateBathroomFeeTotal2025, 'accommodation');
                }
                
                if ($pricingYears['weeks_2026'] > 0 && $privateBathroomFee2026 > 0) {
                    $weeks2026 = min($this->accommodationWeeks, $pricingYears['weeks_2026']);
                    $privateBathroomFeeTotal2026 = $privateBathroomFee2026 * $weeks2026;
                    $itemName2026 = "Private Bathroom (2026) (" . $weeks2026 . ' weeks)';
                    $this->addItem($itemName2026, $privateBathroomFeeTotal2026, 'accommodation');
                }
            } else {
                // Single year pricing
                $yearForPricing = $pricingYears['use_2026_pricing'] ? 2026 : 2025;
                $privateBathroomFee = $this->accommodation->getFeeByYear('private_bathroom_fee', Carbon::create($yearForPricing, 1, 1));
                
                if ($privateBathroomFee > 0) {
                    $privateBathroomFeeTotal = $privateBathroomFee * $this->accommodationWeeks;
                    $itemName = "Private Bathroom ({$yearForPricing}) (" . $this->accommodationWeeks . ' weeks)';
                    $this->addItem($itemName, $privateBathroomFeeTotal, 'accommodation');
                }
            }
        }

        // Add separate Private Bathroom fee for Christmas extra weeks if applicable
        if ($this->privateBathroomOption && $this->christmasAccommodation && ($this->christmasExtraWeeks ?? 0) > 0 && 
            $this->accommodation->getEnabledByYear('private_bathroom_enabled', $this->startDate)) {
            
            // Calculate actual Christmas extra weeks (same logic as Extra Christmas Accommodation)
            $accommodationStartDate = $this->startDate;
            $accommodationEndDate = $this->calculateAccommodationEndDate();
            $overlapStart = $accommodationStartDate->gt($this->christmasStartDate) ? $accommodationStartDate : $this->christmasStartDate;
            $overlapEnd = $accommodationEndDate->lt($this->christmasEndDate) ? $accommodationEndDate : $this->christmasEndDate;
            
            if ($overlapStart->lte($overlapEnd)) {
                $actualExtraWeeks = $this->calculateExtraChristmasWeeks($overlapStart, $overlapEnd);
                
                // Force at least 1 week if Christmas accommodation was explicitly requested
                if ($this->christmasExtraWeeks > 0 && $actualExtraWeeks == 0) {
                    $actualExtraWeeks = 1;
                }
                
                if ($actualExtraWeeks > 0) {
                    // Use 2025 pricing for Christmas weeks (Christmas is typically in 2025)
                    $privateBathroomFee = $this->accommodation->getFeeByYear('private_bathroom_fee', Carbon::create(2025, 1, 1));
                    
                    if ($privateBathroomFee > 0) {
                        $christmasPrivateBathroomFee = $privateBathroomFee * $actualExtraWeeks;
                        $itemName = "Private Bathroom (Christmas Week): £" . number_format($christmasPrivateBathroomFee, 2);
                        $this->addItem($itemName, $christmasPrivateBathroomFee, 'accommodation');
                    }
                }
            }
        }

        // Add Dietary Supplement (Halal) Option Fee for normal accommodation weeks only (using cutoff date pricing rules)
        if ($this->dietarySupplementOption && $this->accommodation->getEnabledByYear('dietary_supplement_enabled', $this->startDate)) {
            $pricingYears = $this->determinePricingYears($this->startDate, $this->accommodationWeeks);
            
            if ($pricingYears['has_mixed_pricing']) {
                // Mixed pricing: create separate line items for 2025 and 2026 portions
                $dietarySupplementFee2025 = $this->accommodation->getFeeByYear('dietary_supplement_fee', Carbon::create(2025, 1, 1));
                $dietarySupplementFee2026 = $this->accommodation->getFeeByYear('dietary_supplement_fee', Carbon::create(2026, 1, 1));
                
                if ($pricingYears['weeks_2025'] > 0 && $dietarySupplementFee2025 > 0) {
                    $weeks2025 = min($this->accommodationWeeks, $pricingYears['weeks_2025']);
                    $dietarySupplementFeeTotal2025 = $dietarySupplementFee2025 * $weeks2025;
                    $itemName2025 = "Dietary Supplement (Halal) (2025) (" . $weeks2025 . ' weeks)';
                    $this->addItem($itemName2025, $dietarySupplementFeeTotal2025, 'accommodation');
                }
                
                if ($pricingYears['weeks_2026'] > 0 && $dietarySupplementFee2026 > 0) {
                    $weeks2026 = min($this->accommodationWeeks, $pricingYears['weeks_2026']);
                    $dietarySupplementFeeTotal2026 = $dietarySupplementFee2026 * $weeks2026;
                    $itemName2026 = "Dietary Supplement (Halal) (2026) (" . $weeks2026 . ' weeks)';
                    $this->addItem($itemName2026, $dietarySupplementFeeTotal2026, 'accommodation');
                }
            } else {
                // Single year pricing
                $yearForPricing = $pricingYears['use_2026_pricing'] ? 2026 : 2025;
                $dietarySupplementFee = $this->accommodation->getFeeByYear('dietary_supplement_fee', Carbon::create($yearForPricing, 1, 1));
                
                if ($dietarySupplementFee > 0) {
                    $dietarySupplementFeeTotal = $dietarySupplementFee * $this->accommodationWeeks;
                    $itemName = "Dietary Supplement (Halal) ({$yearForPricing}) (" . $this->accommodationWeeks . ' weeks)';
                    $this->addItem($itemName, $dietarySupplementFeeTotal, 'accommodation');
                }
            }
        }

        // Add separate Dietary Supplement fee for Christmas extra weeks if applicable
        if ($this->dietarySupplementOption && $this->christmasAccommodation && ($this->christmasExtraWeeks ?? 0) > 0 && 
            $this->accommodation->getEnabledByYear('dietary_supplement_enabled', $this->startDate)) {
            
            // Calculate actual Christmas extra weeks (same logic as Extra Christmas Accommodation)
            $accommodationStartDate = $this->startDate;
            $accommodationEndDate = $this->calculateAccommodationEndDate();
            $overlapStart = $accommodationStartDate->gt($this->christmasStartDate) ? $accommodationStartDate : $this->christmasStartDate;
            $overlapEnd = $accommodationEndDate->lt($this->christmasEndDate) ? $accommodationEndDate : $this->christmasEndDate;
            
            if ($overlapStart->lte($overlapEnd)) {
                $actualExtraWeeks = $this->calculateExtraChristmasWeeks($overlapStart, $overlapEnd);
                
                // Force at least 1 week if Christmas accommodation was explicitly requested
                if ($this->christmasExtraWeeks > 0 && $actualExtraWeeks == 0) {
                    $actualExtraWeeks = 1;
                }
                
                if ($actualExtraWeeks > 0) {
                    // Use 2025 pricing for Christmas weeks (Christmas is typically in 2025)
                    $dietarySupplementFee = $this->accommodation->getFeeByYear('dietary_supplement_fee', Carbon::create(2025, 1, 1));
                    
                    if ($dietarySupplementFee > 0) {
                        $christmasDietarySupplementFee = $dietarySupplementFee * $actualExtraWeeks;
                        $itemName = "Dietary Supplement (Halal) (Christmas Week): £" . number_format($christmasDietarySupplementFee, 2);
                        $this->addItem($itemName, $christmasDietarySupplementFee, 'accommodation');
                    }
                }
            }
        }
    }

    /**
     * Calculate the second accommodation cost.
     */
    private function calculateSecondAccommodationCost(): void
    {
        if (!$this->secondAccommodation || !$this->secondAccommodationWeeks) return;

        // Get the accommodation price model
        $accommodationPrice = $this->getSecondAccommodationPrice();
        if (!$accommodationPrice) {
            $this->addError("Could not find weekly price for 'Second {$this->secondAccommodation->name}' for {$this->secondAccommodationWeeks} weeks.");
            return;
        }

        // Determine pricing years for mixed pricing
        $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
        $pricingYears = $this->determinePricingYears($secondAccommodationStartDate, $this->secondAccommodationWeeks);
        
        // Initialize price per week variables for backward compatibility
        $pricePerWeek = null;
        $pricePerWeek2025 = $accommodationPrice->price_per_week ?? 0;
        $pricePerWeek2026 = $accommodationPrice->price_per_week_2026 ?? $pricePerWeek2025;
        
        if ($pricingYears['has_mixed_pricing']) {
            // Create separate line items for 2025 and 2026 portions
            if ($pricingYears['weeks_2025'] > 0) {
                if ($pricePerWeek2025 <= 0) {
                    $this->addError('No second accommodation price defined for ' . $pricingYears['weeks_2025'] . ' weeks in 2025');
                } else {
                    $cost2025 = $pricingYears['weeks_2025'] * $pricePerWeek2025;
                    $itemName2025 = 'Second ' . $this->secondAccommodation->name . ' (' . $pricingYears['weeks_2025'] . ' weeks) - 2025';
                    $this->addItem($itemName2025, $cost2025, 'second_accommodation');
                }
            }
            
            if ($pricingYears['weeks_2026'] > 0) {
                if ($pricePerWeek2026 <= 0) {
                    $this->addError('No second accommodation price defined for ' . $pricingYears['weeks_2026'] . ' weeks in 2026');
                } else {
                    $cost2026 = $pricingYears['weeks_2026'] * $pricePerWeek2026;
                    $itemName2026 = 'Second ' . $this->secondAccommodation->name . ' (' . $pricingYears['weeks_2026'] . ' weeks) - 2026';
                    $this->addItem($itemName2026, $cost2026, 'second_accommodation');
                }
            }
            
            // Set pricePerWeek for backward compatibility (use 2025 price as fallback)
            $pricePerWeek = $pricePerWeek2025;
        } else {
            // Single pricing year
            $pricePerWeek = $pricingYears['use_2026_pricing'] ? $pricePerWeek2026 : $pricePerWeek2025;
            $year = $pricingYears['use_2026_pricing'] ? 2026 : 2025;
            
            if ($pricePerWeek <= 0) {
                $this->addError('No second accommodation price defined for ' . $this->secondAccommodationWeeks . ' weeks in ' . $year);
            } else {
                $totalCost = $pricePerWeek * $this->secondAccommodationWeeks;
                $itemName = 'Second ' . $this->secondAccommodation->name . ' (' . $this->secondAccommodationWeeks . ' weeks) - ' . $year;
                $this->addItem($itemName, $totalCost, 'second_accommodation');
            }
        }
        // Continue to calculate other fees even if base price is missing
        
        // Calculate accommodation end date using Monday-to-Friday logic
        $accommodationEndDate = $this->calculateSecondAccommodationEndDate();

        // Add Accommodation Summer Supplement (doesn't depend on pricePerWeek)
        if ($this->secondAccommodation->summer_fee_per_week > 0 && $this->secondAccommodation->summer_start_date && $this->secondAccommodation->summer_end_date) {
            $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
            $overlapDetails = $this->calculateSummerSupplementOverlapDetails(
                $secondAccommodationStartDate,
                $accommodationEndDate,
                Carbon::parse($this->secondAccommodation->summer_start_date),
                Carbon::parse($this->secondAccommodation->summer_end_date)
            );
            if ($overlapDetails['weeks'] > 0) {
                $summerFee = $overlapDetails['weeks'] * $this->secondAccommodation->summer_fee_per_week;
                $itemName = "Second Accommodation Summer Supplement:\n" .
                           "Start date: " . $overlapDetails['start_date']->format('j M Y') . "\n" .
                           "End date: " . $overlapDetails['end_date']->format('j M Y') . "\n" .
                           "Duration: " . $overlapDetails['weeks'] . " " . ($overlapDetails['weeks'] === 1 ? 'week' : 'weeks');
                $this->addItem($itemName, $summerFee, 'fees');
            }
        }

        // Add Accommodation Christmas Supplement & Extra Weeks Cost
        // Apply second accommodation Christmas supplement only when user selected Christmas AND admin did not block it
        $christmasSupplementApplies = $this->secondChristmasAccommodation && !$this->secondAccommodation->requires_christmas_supplement;

        if ($christmasSupplementApplies) {
            $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
            
            // Check for 2025 Christmas overlap
            if ($this->christmasStartDate && $this->christmasEndDate) {
                $overlapDetails2025 = $this->calculateChristmasSupplementOverlapDetails(
                    $secondAccommodationStartDate,
                    $accommodationEndDate,
                    $this->christmasStartDate,
                    $this->christmasEndDate
                );

                if ($overlapDetails2025['weeks'] > 0) {
                    $christmasFee2025 = $this->school->getFeeByYear('christmas_fee_per_week', Carbon::create(2025, 1, 1));
                    
                    if ($christmasFee2025 > 0) {
                        $christmasFee = $overlapDetails2025['weeks'] * $christmasFee2025;
                        $itemName = "Second Accommodation Christmas Supplement (2025):\n" .
                                   "Start date: " . $overlapDetails2025['start_date']->format('j M Y') . "\n" .
                                   "End date: " . $overlapDetails2025['end_date']->format('j M Y') . "\n" .
                                   "Duration: " . $overlapDetails2025['weeks'] . " " . ($overlapDetails2025['weeks'] === 1 ? 'week' : 'weeks');
                        $this->addItem($itemName, $christmasFee, 'fees');
                    }
                }
            }
            
            // Check for 2026 Christmas overlap
            if ($this->school->christmas_start_date_2026 && $this->school->christmas_end_date_2026) {
                $overlapDetails2026 = $this->calculateChristmasSupplementOverlapDetails(
                    $secondAccommodationStartDate,
                    $accommodationEndDate,
                    $this->school->christmas_start_date_2026,
                    $this->school->christmas_end_date_2026
                );

                if ($overlapDetails2026['weeks'] > 0) {
                    $christmasFee2026 = $this->school->getFeeByYear('christmas_fee_per_week', Carbon::create(2026, 1, 1));
                    
                    if ($christmasFee2026 > 0) {
                        $christmasFee = $overlapDetails2026['weeks'] * $christmasFee2026;
                        $itemName = "Second Accommodation Christmas Supplement (2026):\n" .
                                   "Start date: " . $overlapDetails2026['start_date']->format('j M Y') . "\n" .
                                   "End date: " . $overlapDetails2026['end_date']->format('j M Y') . "\n" .
                                   "Duration: " . $overlapDetails2026['weeks'] . " " . ($overlapDetails2026['weeks'] === 1 ? 'week' : 'weeks');
                        $this->addItem($itemName, $christmasFee, 'fees');
                    }
                }
            }

            // Add note and extra weeks cost ONLY if Christmas accommodation was explicitly requested
            if ($this->secondChristmasAccommodation) {
                 $noteText = 'Second accommodation includes Christmas period: ' .
                     $this->christmasStartDate->format('M j, Y') . ' to ' . $this->christmasEndDate->format('M j, Y');

                 // Add extra weeks cost if applicable - calculate Monday-Friday weeks only
                 if ($this->secondChristmasExtraWeeks > 0) {
                     // Check if second accommodation period overlaps with Christmas period
                     $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
                     $secondAccommodationEndDate = $this->calculateSecondAccommodationEndDate();
                     
                     // Calculate overlap using proper logic
                     $overlapStart = $secondAccommodationStartDate->gt($this->christmasStartDate) ? $secondAccommodationStartDate : $this->christmasStartDate;
                     $overlapEnd = $secondAccommodationEndDate->lt($this->christmasEndDate) ? $secondAccommodationEndDate : $this->christmasEndDate;
                     
                     // Debug information for overlap calculation
                     Log::info('Second Accommodation Overlap calculation debug:', [
                         'secondAccommodationStartDate' => $secondAccommodationStartDate->format('Y-m-d'),
                         'secondAccommodationEndDate' => $secondAccommodationEndDate->format('Y-m-d'),
                         'christmasStartDate' => $this->christmasStartDate->format('Y-m-d'),
                         'christmasEndDate' => $this->christmasEndDate->format('Y-m-d'),
                         'overlapStart' => $overlapStart->format('Y-m-d'),
                         'overlapEnd' => $overlapEnd->format('Y-m-d'),
                         'hasOverlap' => $overlapStart->lte($overlapEnd) ? 'Yes' : 'No'
                     ]);
                     
                     // Only proceed if there is actual overlap
                     if ($overlapStart->lte($overlapEnd)) {
                         // Calculate actual Monday-Friday weeks for the overlapping Christmas period
                         $actualExtraWeeks = $this->calculateExtraChristmasWeeks($overlapStart, $overlapEnd);
                         
                         Log::info('Second Accommodation Extra Christmas weeks calculation:', ['actualExtraWeeks' => $actualExtraWeeks]);
                         
                         // Force at least 1 week if Christmas accommodation was explicitly requested
                         // This ensures the Extra Christmas Accommodation always appears when requested
                         if ($this->secondChristmasExtraWeeks > 0 && $actualExtraWeeks == 0) {
                             $actualExtraWeeks = 1;
                             Log::info('Forcing minimum 1 week for Second Accommodation Extra Christmas Accommodation');
                         }
                         
                         if ($actualExtraWeeks > 0) {
                             $extraWeeksText = $actualExtraWeeks . ' extra ' .
                                 ($actualExtraWeeks === 1 ? 'week' : 'weeks');
                             $noteText .= ' (' . $extraWeeksText . ')';

                             // Use the previously fetched $pricePerWeek for the calculation
                             if ($pricePerWeek !== null) {
                                 $extraAccommodationItem = "Extra Christmas Second Accommodation:\n" .
                                                           "Start date: " . $overlapStart->format('j M Y') . "\n" .
                                                           "End date: " . $overlapEnd->format('j M Y') . "\n" .
                                                           "Duration: " . $actualExtraWeeks . " " . ($actualExtraWeeks === 1 ? 'week' : 'weeks');
                                 $extraWeeksCost = $actualExtraWeeks * $pricePerWeek;
                                 $this->addItem($extraAccommodationItem, $extraWeeksCost, 'fees');
                             } else {
                                 $this->addError("Could not calculate cost for extra Christmas weeks for second accommodation because the base accommodation price is missing.");
                             }
                         } else {
                             Log::info('No full Monday-Friday weeks overlap between second accommodation and Christmas period.');
                         }
                     } else {
                         Log::info('No overlap between second accommodation period and Christmas period - Extra Christmas Second Accommodation not applicable.');
                     }
                 }
                 $this->costBreakdown['notes'][] = $noteText;
            }
        }

        // Add Private Bathroom Option Fee for normal second accommodation weeks only
        $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
        if ($this->secondPrivateBathroomOption && $this->secondAccommodation->getEnabledByYear('private_bathroom_enabled', $secondAccommodationStartDate)) {
            
            // Calculate second accommodation start date
            $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
            $pricingYears = $this->determinePricingYears($secondAccommodationStartDate, $this->secondAccommodationWeeks);
            
            $privateBathroomFee2025 = (float) $this->secondAccommodation->private_bathroom_fee;
            $privateBathroomFee2026 = $this->secondAccommodation->private_bathroom_fee_2026 !== null 
                ? (float) $this->secondAccommodation->private_bathroom_fee_2026 
                : $privateBathroomFee2025;
            
            if ($pricingYears['has_mixed_pricing']) {
                // Mixed pricing: create separate line items for 2025 and 2026 portions
                if ($pricingYears['weeks_2025'] > 0 && $privateBathroomFee2025 > 0) {
                    $weeks2025 = min($this->secondAccommodationWeeks, $pricingYears['weeks_2025']);
                    $privateBathroomFeeTotal2025 = $privateBathroomFee2025 * $weeks2025;
                    $itemName2025 = 'Second Accommodation Private Bathroom (2025) (' . $weeks2025 . ' weeks)';
                    $this->addItem($itemName2025, $privateBathroomFeeTotal2025, 'second_accommodation');
                }
                
                if ($pricingYears['weeks_2026'] > 0 && $privateBathroomFee2026 > 0) {
                    $weeks2026 = min($this->secondAccommodationWeeks, $pricingYears['weeks_2026']);
                    $privateBathroomFeeTotal2026 = $privateBathroomFee2026 * $weeks2026;
                    $itemName2026 = 'Second Accommodation Private Bathroom (2026) (' . $weeks2026 . ' weeks)';
                    $this->addItem($itemName2026, $privateBathroomFeeTotal2026, 'second_accommodation');
                }
            } else {
                // Single year pricing
                $privateBathroomFee = $pricingYears['use_2026_pricing'] ? $privateBathroomFee2026 : $privateBathroomFee2025;
                $year = $pricingYears['use_2026_pricing'] ? 2026 : 2025;
                
                if ($privateBathroomFee > 0) {
                    $privateBathroomFeeTotal = $privateBathroomFee * $this->secondAccommodationWeeks;
                    $itemName = 'Second Accommodation Private Bathroom (' . $year . ') (' . $this->secondAccommodationWeeks . ' weeks)';
                    $this->addItem($itemName, $privateBathroomFeeTotal, 'second_accommodation');
                }
            }
        }

        // Add separate Private Bathroom fee for Christmas extra weeks in second accommodation if applicable
        if ($this->secondPrivateBathroomOption && $this->secondChristmasAccommodation && ($this->secondChristmasExtraWeeks ?? 0) > 0 && 
            $this->secondAccommodation->getEnabledByYear('private_bathroom_enabled', $secondAccommodationStartDate)) {
            
            // Calculate actual Christmas extra weeks for second accommodation (same logic as Extra Christmas Second Accommodation)
            $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
            $secondAccommodationEndDate = $this->calculateSecondAccommodationEndDate();
            $overlapStart = $secondAccommodationStartDate->gt($this->christmasStartDate) ? $secondAccommodationStartDate : $this->christmasStartDate;
            $overlapEnd = $secondAccommodationEndDate->lt($this->christmasEndDate) ? $secondAccommodationEndDate : $this->christmasEndDate;
            
            if ($overlapStart->lte($overlapEnd)) {
                $actualExtraWeeks = $this->calculateExtraChristmasWeeks($overlapStart, $overlapEnd);
                
                // Force at least 1 week if Christmas accommodation was explicitly requested
                if ($this->secondChristmasExtraWeeks > 0 && $actualExtraWeeks == 0) {
                    $actualExtraWeeks = 1;
                }
                
                if ($actualExtraWeeks > 0) {
                    // Use 2025 pricing for Christmas weeks (Christmas is typically in 2025)
                    $privateBathroomFee = (float) $this->secondAccommodation->private_bathroom_fee;
                    
                    if ($privateBathroomFee > 0) {
                        $christmasPrivateBathroomFee = $privateBathroomFee * $actualExtraWeeks;
                        $itemName = "Second Accommodation Private Bathroom (Christmas Week): £" . number_format($christmasPrivateBathroomFee, 2);
                        $this->addItem($itemName, $christmasPrivateBathroomFee, 'second_accommodation');
                    }
                }
            }
        }

        // Add Dietary Supplement (Halal) Option Fee for normal second accommodation weeks only
        $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
        if ($this->secondDietarySupplementOption && $this->secondAccommodation->getEnabledByYear('dietary_supplement_enabled', $secondAccommodationStartDate)) {
            
            // Calculate second accommodation start date
            $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
            $pricingYears = $this->determinePricingYears($secondAccommodationStartDate, $this->secondAccommodationWeeks);
            
            $dietarySupplementFee2025 = (float) $this->secondAccommodation->dietary_supplement_fee;
            $dietarySupplementFee2026 = $this->secondAccommodation->dietary_supplement_fee_2026 !== null 
                ? (float) $this->secondAccommodation->dietary_supplement_fee_2026 
                : $dietarySupplementFee2025;
            
            if ($pricingYears['has_mixed_pricing']) {
                // Mixed pricing: create separate line items for 2025 and 2026 portions
                if ($pricingYears['weeks_2025'] > 0 && $dietarySupplementFee2025 > 0) {
                    $weeks2025 = min($this->secondAccommodationWeeks, $pricingYears['weeks_2025']);
                    $dietarySupplementFeeTotal2025 = $dietarySupplementFee2025 * $weeks2025;
                    $itemName2025 = 'Second Accommodation Dietary Supplement (Halal) (2025) (' . $weeks2025 . ' weeks)';
                    $this->addItem($itemName2025, $dietarySupplementFeeTotal2025, 'second_accommodation');
                }
                
                if ($pricingYears['weeks_2026'] > 0 && $dietarySupplementFee2026 > 0) {
                    $weeks2026 = min($this->secondAccommodationWeeks, $pricingYears['weeks_2026']);
                    $dietarySupplementFeeTotal2026 = $dietarySupplementFee2026 * $weeks2026;
                    $itemName2026 = 'Second Accommodation Dietary Supplement (Halal) (2026) (' . $weeks2026 . ' weeks)';
                    $this->addItem($itemName2026, $dietarySupplementFeeTotal2026, 'second_accommodation');
                }
            } else {
                // Single year pricing
                $dietarySupplementFee = $pricingYears['use_2026_pricing'] ? $dietarySupplementFee2026 : $dietarySupplementFee2025;
                $year = $pricingYears['use_2026_pricing'] ? 2026 : 2025;
                
                if ($dietarySupplementFee > 0) {
                    $dietarySupplementFeeTotal = $dietarySupplementFee * $this->secondAccommodationWeeks;
                    $itemName = 'Second Accommodation Dietary Supplement (Halal) (' . $year . ') (' . $this->secondAccommodationWeeks . ' weeks)';
                    $this->addItem($itemName, $dietarySupplementFeeTotal, 'second_accommodation');
                }
            }
        }

        // Add separate Dietary Supplement fee for Christmas extra weeks in second accommodation if applicable
        if ($this->secondDietarySupplementOption && $this->secondChristmasAccommodation && ($this->secondChristmasExtraWeeks ?? 0) > 0 && 
            $this->secondAccommodation->getEnabledByYear('dietary_supplement_enabled', $secondAccommodationStartDate)) {
            
            // Calculate actual Christmas extra weeks for second accommodation (same logic as Extra Christmas Second Accommodation)
            $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
            $secondAccommodationEndDate = $this->calculateSecondAccommodationEndDate();
            $overlapStart = $secondAccommodationStartDate->gt($this->christmasStartDate) ? $secondAccommodationStartDate : $this->christmasStartDate;
            $overlapEnd = $secondAccommodationEndDate->lt($this->christmasEndDate) ? $secondAccommodationEndDate : $this->christmasEndDate;
            
            if ($overlapStart->lte($overlapEnd)) {
                $actualExtraWeeks = $this->calculateExtraChristmasWeeks($overlapStart, $overlapEnd);
                
                // Force at least 1 week if Christmas accommodation was explicitly requested
                if ($this->secondChristmasExtraWeeks > 0 && $actualExtraWeeks == 0) {
                    $actualExtraWeeks = 1;
                }
                
                if ($actualExtraWeeks > 0) {
                    // Use 2025 pricing for Christmas weeks (Christmas is typically in 2025)
                    $dietarySupplementFee = (float) $this->secondAccommodation->dietary_supplement_fee;
                    
                    if ($dietarySupplementFee > 0) {
                        $christmasDietarySupplementFee = $dietarySupplementFee * $actualExtraWeeks;
                        $itemName = "Second Accommodation Dietary Supplement (Halal) (Christmas Week): £" . number_format($christmasDietarySupplementFee, 2);
                        $this->addItem($itemName, $christmasDietarySupplementFee, 'second_accommodation');
                    }
                }
            }
        }

        // Add Guardianship Fee (U18) for second accommodation - using cutoff date pricing rules
        if ($this->secondAccommodation->requires_guardianship) {
            $qualifyingWeeks = $this->calculateSecondAccommodationGuardianshipQualifyingWeeks();
            if ($qualifyingWeeks > 0) {
                // Calculate second accommodation start date for pricing determination
                $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
                $pricingYears = $this->determinePricingYears($secondAccommodationStartDate, $this->secondAccommodationWeeks);
                
                $guardianshipFee2025 = $this->school->getFeeByYear('guardianship_fee_per_week', Carbon::create(2025, 1, 1));
                $guardianshipFee2026 = $this->school->getFeeByYear('guardianship_fee_per_week', Carbon::create(2026, 1, 1));
                
                $guardianshipAge = $this->school->guardianship_fee_age ?? 18;
                
                if ($pricingYears['has_mixed_pricing']) {
                    // Mixed pricing: create separate line items for 2025 and 2026 portions
                    if ($pricingYears['weeks_2025'] > 0 && $guardianshipFee2025 > 0) {
                        $weeks2025 = min($qualifyingWeeks, $pricingYears['weeks_2025']);
                        $guardianshipTotal2025 = $guardianshipFee2025 * $weeks2025;
                        $itemName2025 = 'Second Accommodation Guardianship Fee (U' . $guardianshipAge . ') (2025) (' . $weeks2025 . ' weeks)';
                        $this->addItem($itemName2025, $guardianshipTotal2025, 'fees');
                    }
                    
                    if ($pricingYears['weeks_2026'] > 0 && $guardianshipFee2026 > 0) {
                        $weeks2026 = min($qualifyingWeeks, $pricingYears['weeks_2026']);
                        $guardianshipTotal2026 = $guardianshipFee2026 * $weeks2026;
                        $itemName2026 = 'Second Accommodation Guardianship Fee (U' . $guardianshipAge . ') (2026) (' . $weeks2026 . ' weeks)';
                        $this->addItem($itemName2026, $guardianshipTotal2026, 'fees');
                    }
                } else {
                    // Single year pricing
                    $guardianshipFeePerWeek = $pricingYears['use_2026_pricing'] ? $guardianshipFee2026 : $guardianshipFee2025;
                    $year = $pricingYears['use_2026_pricing'] ? 2026 : 2025;
                    
                    if ($guardianshipFeePerWeek > 0) {
                        $guardianshipTotal = $guardianshipFeePerWeek * $qualifyingWeeks;
                        $itemName = 'Second Accommodation Guardianship Fee (U' . $guardianshipAge . ') (' . $year . ') (' . $qualifyingWeeks . ' weeks)';
                        $this->addItem($itemName, $guardianshipTotal, 'fees');
                    }
                }
            }

            // Christmas Guardianship Fee (U18) for Second Accommodation - Only when Christmas accommodation is active
            if ($this->secondChristmasAccommodation && $this->secondChristmasExtraWeeks > 0) {
                // Check if second accommodation requires guardianship
                if ($this->secondAccommodation && $this->secondAccommodation->requires_guardianship) {
                    // Christmas guardianship is always 1 week
                    $christmasGuardianshipWeeks = 1;
                    
                    // Determine pricing year for Christmas period
                    $christmasStartDate = $this->christmasStartDate;
                    $christmasGuardianshipPricingYears = $this->determinePricingYears($christmasStartDate, $christmasGuardianshipWeeks);
                    
                    $guardianshipFee2025 = $this->school->getFeeByYear('guardianship_fee_per_week', Carbon::create(2025, 1, 1));
                    $guardianshipFee2026 = $this->school->getFeeByYear('guardianship_fee_per_week', Carbon::create(2026, 1, 1));
                    
                    // Use appropriate pricing based on Christmas period year
                    $christmasGuardianshipFeePerWeek = $christmasGuardianshipPricingYears['use_2026_pricing'] ? $guardianshipFee2026 : $guardianshipFee2025;
                    $christmasYear = $christmasGuardianshipPricingYears['use_2026_pricing'] ? '2026' : '2025';
                    
                    $guardianshipAge = $this->school->guardianship_fee_age ?? 18;
                    // Apply guardianship only if student is under configured age at Christmas start
                    $ageAtChristmas = ($this->studentBirthday && $christmasStartDate) ? $this->studentBirthday->diffInYears($christmasStartDate) : $this->studentAge;
                    
                    if ($christmasGuardianshipFeePerWeek > 0 && $ageAtChristmas !== null && $ageAtChristmas < $guardianshipAge) {
                        $christmasGuardianshipTotal = $christmasGuardianshipFeePerWeek * $christmasGuardianshipWeeks;
                        $christmasItemName = 'Second Accommodation Guardianship Fee (U' . $guardianshipAge . ') During Christmas (' . $christmasYear . ')';
                        $this->addItem($christmasItemName, $christmasGuardianshipTotal, 'fees');
                    }
                }
            }
        }
    }


     /**
      * Get accommodation price model based on duration.
      * Returns null if no matching active price range is found.
      */
     private function getAccommodationPrice(): ?AccommodationPrice
     {
         if (!$this->accommodation || !$this->accommodationWeeks) {
             return null;
         }

         // Find the active price range that includes the requested number of weeks
         return AccommodationPrice::where('accommodation_id', $this->accommodation->id)
                                  ->where('min_weeks', '<=', $this->accommodationWeeks)
                                  ->where('max_weeks', '>=', $this->accommodationWeeks)
                                  ->where('active', true)
                                  ->orderBy('min_weeks', 'desc') // Prioritize the narrowest matching range
                                  ->first();
     }

     /**
      * Get accommodation price per week based on duration.
      * Returns null if no matching active price range is found.
      */
     private function getAccommodationPricePerWeek(): ?float
     {
         if (!$this->accommodation || !$this->accommodationWeeks) {
             return null;
         }

         // Find the active price range that includes the requested number of weeks
         $price = AccommodationPrice::where('accommodation_id', $this->accommodation->id)
                                    ->where('min_weeks', '<=', $this->accommodationWeeks)
                                    ->where('max_weeks', '>=', $this->accommodationWeeks)
                                    ->where('active', true)
                                    ->orderBy('min_weeks', 'desc') // Prioritize the narrowest matching range
                                    ->first();

         if (!$price) {
             return null;
         }

         // Calculate accommodation start and end dates
         $accommodationStartDate = $this->startDate;
         $accommodationEndDate = $this->calculateAccommodationEndDate();
         
         // Use new pricing rules logic
         $pricingYears = $this->determinePricingYears($accommodationStartDate, $this->accommodationWeeks);
         
         $price2025 = (float) $price->price_per_week;
         $price2026 = $price->price_per_week_2026 !== null ? (float) $price->price_per_week_2026 : $price2025;
         
         // Calculate weighted average if mixed pricing
         if ($pricingYears['has_mixed_pricing']) {
             $weeks2025 = $pricingYears['weeks_2025'];
             $weeks2026 = $pricingYears['weeks_2026'];
             $totalWeeks = $weeks2025 + $weeks2026;
             
             if ($totalWeeks > 0) {
                 return (($price2025 * $weeks2025) + ($price2026 * $weeks2026)) / $totalWeeks;
             }
         }
         
         // Use single year pricing
         return $pricingYears['use_2026_pricing'] ? $price2026 : $price2025;
     }

     /**
      * Get second accommodation price model based on duration.
      * Returns null if no matching active price range is found.
      */
     private function getSecondAccommodationPrice(): ?AccommodationPrice
     {
         if (!$this->secondAccommodation || !$this->secondAccommodationWeeks) {
             return null;
         }

         // Find the active price range that includes the requested number of weeks
         return AccommodationPrice::where('accommodation_id', $this->secondAccommodation->id)
                                  ->where('min_weeks', '<=', $this->secondAccommodationWeeks)
                                  ->where('max_weeks', '>=', $this->secondAccommodationWeeks)
                                  ->where('active', true)
                                  ->orderBy('min_weeks', 'desc') // Prioritize the narrowest matching range
                                  ->first();
     }

     /**
      * Get second accommodation price per week based on duration.
      * Returns null if no matching active price range is found.
      */
     private function getSecondAccommodationPricePerWeek(): ?float
     {
         if (!$this->secondAccommodation || !$this->secondAccommodationWeeks) {
             return null;
         }

         // Find the active price range that includes the requested number of weeks
         $price = AccommodationPrice::where('accommodation_id', $this->secondAccommodation->id)
                                    ->where('min_weeks', '<=', $this->secondAccommodationWeeks)
                                    ->where('max_weeks', '>=', $this->secondAccommodationWeeks)
                                    ->where('active', true)
                                    ->orderBy('min_weeks', 'desc') // Prioritize the narrowest matching range
                                    ->first();

         if (!$price) {
             return null;
         }

         // Calculate second accommodation start date and use new pricing rules logic
         $secondAccommodationStartDate = $this->calculateSecondAccommodationStartDate();
         $pricingYears = $this->determinePricingYears($secondAccommodationStartDate, $this->secondAccommodationWeeks);
         
         $price2025 = (float) $price->price_per_week;
         $price2026 = $price->price_per_week_2026 !== null ? (float) $price->price_per_week_2026 : $price2025;
         
         // Calculate weighted average if mixed pricing
         if ($pricingYears['has_mixed_pricing']) {
             $weeks2025 = $pricingYears['weeks_2025'];
             $weeks2026 = $pricingYears['weeks_2026'];
             $totalWeeks = $weeks2025 + $weeks2026;
             
             if ($totalWeeks > 0) {
                 return (($price2025 * $weeks2025) + ($price2026 * $weeks2026)) / $totalWeeks;
             }
         }
         
         // Use single year pricing
         return $pricingYears['use_2026_pricing'] ? $price2026 : $price2025;
     }

    /**
     * Calculate costs for selected addons (insurance, courier, transfers).
     */
    private function calculateAddonCosts(): void
    {
        if (empty($this->selectedAddons)) return;

        $addonIds = array_keys($this->selectedAddons); // Assuming keys are addon IDs
        $addons = Addon::whereIn('id', $addonIds)->where('active', true)->get()->keyBy('id');

        foreach ($this->selectedAddons as $addonId => $details) {
            if (!isset($addons[$addonId])) {
                $this->addError("Selected addon ID {$addonId} not found or inactive.");
                continue;
            }

            $addon = $addons[$addonId];
            $addonCost = 0;
            $itemName = $addon->name;

            if ($addon->price_type === 'per_week') {
                // Assume addon duration matches course duration if not specified otherwise
                $weeks = $details['weeks'] ?? $this->courseWeeks;
                if ($weeks === null || $weeks < 1) {
                     $this->addError("Invalid duration for weekly addon '{$addon->name}'.");
                     continue;
                }
                
                // Weekly addons apply per-year pricing when combined pricing is active
                // Get pricing years for the course duration
                $pricingYears = $this->calculatePricingYears($this->startDate, $this->courseWeeks);
                $weeks2025 = $pricingYears['weeks_2025'];
                $weeks2026 = $pricingYears['weeks_2026'];
                $hasMixedPricing = $weeks2025 > 0 && $weeks2026 > 0;
                
                if ($hasMixedPricing && $weeks2025 > 0 && $weeks2026 > 0) {
                    // Apply per-year pricing: full addon cost for each year the course spans
                    $price2025 = $addon->getFeeByYear('price', Carbon::create(2025, 1, 1));
                    $price2026 = $addon->getFeeByYear('price', Carbon::create(2026, 1, 1));
                    
                    // Add 2025 addon cost for the full duration
                    if ($price2025 > 0) {
                        $cost2025 = $price2025 * $weeks;
                        $this->addItem($addon->name . " (2025) ({$weeks} weeks)", $cost2025, 'addons');
                    }
                    
                    // Add 2026 addon cost for the full duration
                    if ($price2026 > 0) {
                        $cost2026 = $price2026 * $weeks;
                        $this->addItem($addon->name . " (2026) ({$weeks} weeks)", $cost2026, 'addons');
                    }
                    continue; // Skip the single item addition below
                } else {
                    // Single year pricing
                    $pricingYears = $this->determinePricingYears($this->startDate, $this->courseWeeks);
                    $yearForPricing = $pricingYears['use_2026_pricing'] ? 2026 : 2025;
                    $pricingDate = Carbon::create($yearForPricing, 1, 1);
                    $addonPrice = $addon->getFeeByYear('price', $pricingDate);
                    $addonCost = $addonPrice * $weeks;
                    $itemName .= " ({$yearForPricing}) ({$weeks} weeks)";
                }
            } else { // one_time
                // One-time addons: always use 2025 pricing when course starts in 2025 (even if extending into 2026)
                $yearForPricing = $this->startDate->year === 2025 ? 2025 : 2026;
                $addonCost = $addon->getFeeByYear('price', Carbon::create($yearForPricing, 1, 1));
                $itemName .= " ({$yearForPricing})";
            }

            $this->addItem($itemName, $addonCost, 'addons');
        }
    }

    /**
     * Calculate costs for selected airport transfers.
     */
    private function calculateAirportTransferCosts(): void
    {
        $juniorSettings = $this->course?->juniorSettings;
        if ($juniorSettings && $juniorSettings->includes_airport_transfer) {
            $this->addItem('Airport Transfer', 0, 'fees', true);
            return;
        }

        // Process Arrival Airport
        if ($this->arrivalAirportId) {
            $airport = Airport::find($this->arrivalAirportId);
            if ($airport) {
                // Validate restrictions
                if (!$this->validateAirportRestrictions($airport)) {
                    $this->addError("Selected arrival airport '{$airport->name}' is not available for the selected course or course type.");
                } else {
                    // Airport transfer is a one-time fee: use 2025 pricing when course starts in 2025
                    $yearForPricing = $this->startDate->year === 2025 ? 2025 : 2026;
                    $arrivalPrice = $airport->getFeeByYear('arrival_price', Carbon::create($yearForPricing, 1, 1));
                    
                    if ($arrivalPrice > 0) {
                        $itemName = 'Arrival Transfer: ' . $airport->name;
                        Log::info('Adding Arrival Transfer item', ['name' => $itemName, 'amount' => $arrivalPrice]); // Log adding item
                        $this->addItem($itemName, $arrivalPrice, 'fees');
                    } else {
                        Log::warning('Selected arrival airport has no arrival price configured for ' . $yearForPricing, ['airport_id' => $this->arrivalAirportId]);
                    }
                }
            } else {
                 $this->addError("Selected arrival airport ID {$this->arrivalAirportId} not found.");
            }
        }

        // Process Departure Airport
        if ($this->departureAirportId) {
            $airport = Airport::find($this->departureAirportId);
            if ($airport) {
                // Validate restrictions
                if (!$this->validateAirportRestrictions($airport)) {
                    $this->addError("Selected departure airport '{$airport->name}' is not available for the selected course or course type.");
                } else {
                    // Airport transfer is a one-time fee: use 2025 pricing when course starts in 2025
                    $yearForPricing = $this->startDate->year === 2025 ? 2025 : 2026;
                    $departurePrice = $airport->getFeeByYear('departure_price', Carbon::create($yearForPricing, 1, 1));
                    
                    if ($departurePrice > 0) {
                        $itemName = 'Departure Transfer: ' . $airport->name;
                        Log::info('Adding Departure Transfer item', ['name' => $itemName, 'amount' => $departurePrice]); // Log adding item
                        $this->addItem($itemName, $departurePrice, 'fees');
                    } else {
                         Log::warning('Selected departure airport has no departure price configured for ' . $yearForPricing, ['airport_id' => $this->departureAirportId]);
                    }
                }
            } else {
                 $this->addError("Selected departure airport ID {$this->departureAirportId} not found.");
            }
        }
    }

    /**
     * Validate if the airport is allowed for the current course and course type.
     *
     * @param Airport $airport
     * @return bool
     */
    private function validateAirportRestrictions(Airport $airport): bool
    {
        // Load restrictions if not already loaded
        if (!$airport->relationLoaded('restrictedCourseTypes')) {
            $airport->load('restrictedCourseTypes');
        }
        if (!$airport->relationLoaded('restrictedCourses')) {
            $airport->load('restrictedCourses');
        }

        // 1. Check Course Type restrictions
        // If specific course types are restricted, the current course's type MUST be one of them
        if ($airport->restrictedCourseTypes->isNotEmpty()) {
            if (!$this->course || !$this->course->courseType || 
                !$airport->restrictedCourseTypes->contains('id', $this->course->course_type_id)) {
                return false;
            }
        }

        // 2. Check Course restrictions
        // If specific courses are restricted, the current course MUST be one of them
        if ($airport->restrictedCourses->isNotEmpty()) {
            if (!$this->course || !$airport->restrictedCourses->contains('id', $this->course->id)) {
                return false;
            }
        }

        return true;
    }


    /**
     * Apply relevant discounts based on the calculated items and quote details.
     */
    private function applyDiscounts(): void
    {
        // First, apply nationality-specific discounts if any are selected
        $this->applyNationalityDiscounts();
        if (!empty($this->nationalityDiscounts)) {
            return;
        }
        
        // Then apply regular discount rules
        // Fetch potentially applicable discount rules (active, global or for the specific school)
        $rules = DiscountRule::with('courses')->where('active', true)
            ->where(function ($query) {
                $query->whereNull('school_id') // Global rules
                      ->orWhere('school_id', $this->school->id); // School-specific rules
            })
            // Add region condition to the query
            ->where(function ($query) {
                 $query->whereNull('region_id') // Global discounts (no region specified)
                       ->orWhere('region_id', $this->regionId); // Region-specific discounts
            })
            ->orderBy('priority', 'asc') // Apply lower priority numbers first
            ->get();

        $appliedDiscounts = []; // Track applied non-combinable discounts per category

        foreach ($rules as $rule) {
            // If the rule is marked to hide in calculator, skip applying it entirely
            // Nationality-specific discounts are applied via applyNationalityDiscounts above
            if ((bool)($rule->hide_rule_name_in_calculator ?? false) === true) {
                continue;
            }
            if ($this->checkDiscountConditions($rule)) {
                // Check if a non-combinable discount for this category has already been applied
                $appliesToCategory = $this->getDiscountCategory($rule->applies_to);
                if (!$rule->combinable && isset($appliedDiscounts[$appliesToCategory])) {
                    continue; // Skip if a non-combinable discount already applied to this category
                }

                $discountAmount = $this->calculateDiscountAmount($rule);

                if ($discountAmount > 0) {
                    // Include course attribution for course tuition or fixed schedule discounts
                    $primaryApplies = null;
                    $secondApplies = null;
                    if ($rule->applies_to === 'course_tuition') {
                        [$primaryApplies, $secondApplies] = $this->getCourseAttributionForRule($rule);
                    } elseif ($rule->applies_to === 'fixed_schedule_courses') {
                        [$primaryApplies, $secondApplies] = $this->getFixedScheduleCourseAttributionForRule($rule);
                    }
                    $this->addDiscount(
                        $rule->name,
                        $discountAmount,
                        $rule->applies_to,
                        false,
                        false,
                        $primaryApplies,
                        $secondApplies
                    );

                    // Mark this category as having a non-combinable discount applied
                    if (!$rule->combinable) {
                        $appliedDiscounts[$appliesToCategory] = true;
                    }
                } elseif ($rule->discount_type === 'fee_waiver') {
                    // Handle fee waiver specifically (amount is 0, but we mark it)
                     $this->addDiscount($rule->name, 0, $rule->applies_to . '_waiver', false); // Mark as waiver
                     if (!$rule->combinable) {
                         $appliedDiscounts[$appliesToCategory] = true;
                     }
                     // Note: Actual fee removal might need adjustment in calculateSchoolFees or other methods
                     // Or adjust total calculation to consider waivers. Simpler for now to just list it.
                }
            }
        }
    }

    /**
     * Check if the conditions of a discount rule match the current quote details.
     *
     * @param DiscountRule $rule
     * @return bool
     */
    private function checkDiscountConditions(DiscountRule $rule): bool
    {
        // School condition (already pre-filtered, but good for clarity)
        if ($rule->school_id !== null && $rule->school_id !== $this->school->id) {
            return false;
        }

        // School Country condition
        // Apply only when a specific school is NOT set on the rule.
        // If a country is specified, the discount should apply to all schools within that country.
        if ($rule->school_id === null && $rule->country_id !== null) {
            $schoolCountryId = null;
            try {
                $schoolCountryId = ($this->school && $this->school->city && $this->school->city->country)
                    ? $this->school->city->country->id
                    : null;
            } catch (\Throwable $e) {
                $schoolCountryId = null; // Defensive: if relations are missing
            }

            if ($schoolCountryId === null || $rule->country_id !== $schoolCountryId) {
                return false;
            }
        }

        // Region condition (double-check, though filtered in query)
        if ($rule->region_id !== null && $rule->region_id !== $this->regionId) {
            return false;
        }

        // Course condition: allow match on either primary or second course
        $ruleCourses = $rule->courses->pluck('id')->toArray();
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

        // Course Type condition: allow match on either primary or second course
        if ($rule->course_type_id !== null) {
            $primaryTypeId = $this->course ? $this->course->course_type_id : null;
            $secondTypeId = $this->secondCourse ? $this->secondCourse->course_type_id : null;
            if ($rule->course_type_id !== $primaryTypeId && $rule->course_type_id !== $secondTypeId) {
                return false;
            }
        }

        // Accommodation condition (match either first or second accommodation)
        if ($rule->accommodation_id !== null) {
            $matchesPrimaryAccommodation = ($this->accommodation && $rule->accommodation_id === $this->accommodation->id);
            $matchesSecondAccommodation = ($this->secondAccommodation && $rule->accommodation_id === $this->secondAccommodation->id);
            if (!$matchesPrimaryAccommodation && !$matchesSecondAccommodation) {
                return false;
            }
        }
        // Accommodation Type condition (match either first or second accommodation)
        if ($rule->accommodation_type !== null) {
            $primaryType = $this->accommodation ? strtolower($this->accommodation->type ?? '') : null;
            $secondType = $this->secondAccommodation ? strtolower($this->secondAccommodation->type ?? '') : null;
            $needle = strtolower($rule->accommodation_type);
            $matchesPrimaryType = ($primaryType !== null && str_contains($primaryType, $needle));
            $matchesSecondType = ($secondType !== null && str_contains($secondType, $needle));
            if (!$matchesPrimaryType && !$matchesSecondType) {
                return false;
            }
        }


        // Course Weeks condition - use combined weeks when second course is present
        $weeksForDiscount = $this->getTotalCombinedWeeks();
        if ($rule->min_course_weeks !== null && $weeksForDiscount < $rule->min_course_weeks) {
            return false;
        }
        if ($rule->max_course_weeks !== null && $weeksForDiscount > $rule->max_course_weeks) {
            return false;
        }

        // Accommodation Weeks condition (validate against either first or second accommodation weeks)
        if ($rule->min_accommodation_weeks !== null) {
            $primaryWeeksValid = ($this->accommodationWeeks && $this->accommodationWeeks >= $rule->min_accommodation_weeks);
            $secondWeeksValid = ($this->secondAccommodationWeeks && $this->secondAccommodationWeeks >= $rule->min_accommodation_weeks);
            if (!$primaryWeeksValid && !$secondWeeksValid) {
                return false;
            }
        }
        if ($rule->max_accommodation_weeks !== null) {
            $primaryWeeksValid = ($this->accommodationWeeks && $this->accommodationWeeks <= $rule->max_accommodation_weeks);
            $secondWeeksValid = ($this->secondAccommodationWeeks && $this->secondAccommodationWeeks <= $rule->max_accommodation_weeks);
            if (!$primaryWeeksValid && !$secondWeeksValid) {
                return false;
            }
        }

        // Date conditions
        if ($rule->valid_from_date !== null || $rule->valid_to_date !== null) {
            // Handle new overlapping_duration date condition type
            if ($rule->date_condition_type === 'overlapping_duration') {
                if (!$rule->valid_from_date || !$rule->valid_to_date) {
                    return false;
                }
                $discountStartDate = Carbon::parse($rule->valid_from_date);
                $discountEndDate = Carbon::parse($rule->valid_to_date);

                $hasOverlap = false;

                if (in_array($rule->applies_to, ['course_tuition', 'fixed_schedule_courses'])) {
                    // Check overlap for primary course
                    if ($this->courseWeeks && $this->startDate) {
                        $courseStart = $this->startDate->copy();
                        if ($courseStart->dayOfWeek !== Carbon::MONDAY) {
                            $courseStart = $courseStart->next(Carbon::MONDAY);
                        }
                        $courseEnd = $this->calculateCourseEndDate();
                        $overlapWeeks = $this->calculateOverlapWeeks($courseStart, $courseEnd, $discountStartDate, $discountEndDate);
                        if ($overlapWeeks > 0) {
                            $hasOverlap = true;
                        }
                    }

                    // Check overlap for second course (if present)
                    if (!$hasOverlap && $this->secondCourseWeeks && $this->secondStartDate) {
                        $secondStart = $this->secondStartDate->copy();
                        if ($secondStart->dayOfWeek !== Carbon::MONDAY) {
                            $secondStart = $secondStart->next(Carbon::MONDAY);
                        }
                        $secondEnd = $this->calculateSecondCourseEndDate();
                        $overlapWeeks2 = $this->calculateOverlapWeeks($secondStart, $secondEnd, $discountStartDate, $discountEndDate);
                        if ($overlapWeeks2 > 0) {
                            $hasOverlap = true;
                        }
                    }
                } elseif ($rule->applies_to === 'accommodation_price') {
                    if ($this->accommodationWeeks && $this->startDate) {
                        $accommodationStart = $this->startDate->copy();
                        if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
                            $accommodationStart = $accommodationStart->next(Carbon::MONDAY);
                        }
                        $accommodationEnd = $this->calculateAccommodationEndDate();
                        $overlapWeeks = $this->calculateOverlapWeeks($accommodationStart, $accommodationEnd, $discountStartDate, $discountEndDate);
                        if ($overlapWeeks > 0) {
                            $hasOverlap = true;
                        }
                    }
                    if (!$hasOverlap && $this->secondAccommodationWeeks) {
                        $secondAccommodationStart = $this->calculateSecondAccommodationStartDate();
                        $secondAccommodationEnd = $this->calculateSecondAccommodationEndDate();
                        $overlapWeeks2 = $this->calculateOverlapWeeks($secondAccommodationStart, $secondAccommodationEnd, $discountStartDate, $discountEndDate);
                        if ($overlapWeeks2 > 0) {
                            $hasOverlap = true;
                        }
                    }
                } else {
                    // For other applies_to values, default to existing start/booking date containment
                    $comparisonDate = $this->startDate;
                    if ($comparisonDate) {
                        if ($rule->valid_from_date !== null && $comparisonDate->lt($discountStartDate)) {
                            return false;
                        }
                        if ($rule->valid_to_date !== null && $comparisonDate->gt($discountEndDate)) {
                            return false;
                        }
                        $hasOverlap = true; // start date within range
                    }
                }

                if (!$hasOverlap) {
                    return false;
                }
            } else {
                // Existing containment checks for start_date and booking_date
                $comparisonDate = null;
                if ($rule->date_condition_type === 'start_date') {
                    $comparisonDate = $this->startDate;
                } elseif ($rule->date_condition_type === 'booking_date') {
                    $comparisonDate = $this->getQuotationExtractionDate(); // Use quotation extraction date for booking date check
                }

                if ($comparisonDate) {
                    if ($rule->valid_from_date !== null && $comparisonDate->lt(Carbon::parse($rule->valid_from_date))) {
                        return false;
                    }
                    if ($rule->valid_to_date !== null && $comparisonDate->gt(Carbon::parse($rule->valid_to_date))) {
                        return false;
                    }
                } else if ($rule->valid_from_date !== null || $rule->valid_to_date !== null) {
                    // If date condition type is not set but dates are, rule is invalid/incomplete
                    return false;
                }
            }
        }

        // Quotation extraction date range conditions
        if ($rule->quotation_extraction_date_from !== null || $rule->quotation_extraction_date_to !== null) {
            $quotationDate = $this->getQuotationExtractionDate();
            
            if ($rule->quotation_extraction_date_from !== null && $quotationDate->lt(Carbon::parse($rule->quotation_extraction_date_from))) {
                return false;
            }
            if ($rule->quotation_extraction_date_to !== null && $quotationDate->gt(Carbon::parse($rule->quotation_extraction_date_to))) {
                return false;
            }
        }

        // Addon condition (checked when applying discount, not here)

        return true; // All conditions passed
    }

    /**
     * Calculate the actual discount amount based on the rule type and value.
     *
     * @param DiscountRule $rule
     * @return float
     */
    private function calculateDiscountAmount(DiscountRule $rule): float
    {
        $baseAmount = 0;
        $overlapFactor = 1.0; // Default: full application unless overlapping_duration requires proration
        $noteRanges = [];

        switch ($rule->applies_to) {
            case 'course_tuition':
                // If the rule targets a specific course or course type, only discount matching course tuition
                $includePrimary = true;
                $includeSecond = true;

                $ruleCourses = $rule->courses->pluck('id')->toArray();
                if (!empty($ruleCourses) || $rule->course_id !== null || $rule->course_type_id !== null) {
                    $includePrimary = false;
                    $includeSecond = false;

                    // Match by specific course (multi-select)
                    if (!empty($ruleCourses)) {
                        if ($this->course && in_array($this->course->id, $ruleCourses)) {
                            $includePrimary = true;
                        }
                        if ($this->secondCourse && in_array($this->secondCourse->id, $ruleCourses)) {
                            $includeSecond = true;
                        }
                    }
                    // Match by specific course (legacy)
                    elseif ($rule->course_id !== null) {
                        if ($this->course && $rule->course_id === $this->course->id) {
                            $includePrimary = true;
                        }
                        if ($this->secondCourse && $rule->course_id === $this->secondCourse->id) {
                            $includeSecond = true;
                        }
                    }

                    // Match by specific course type
                    if ($rule->course_type_id !== null) {
                        $primaryTypeId = $this->course ? $this->course->course_type_id : null;
                        $secondTypeId = $this->secondCourse ? $this->secondCourse->course_type_id : null;
                        if ($rule->course_type_id === $primaryTypeId) {
                            $includePrimary = true;
                        }
                        if ($rule->course_type_id === $secondTypeId) {
                            $includeSecond = true;
                        }
                    }
                }

                $baseAmount = 0;
                if ($includePrimary) {
                    $baseAmount += ($this->costBreakdown['subtotals']['tuition'] ?? 0);
                }
                if ($includeSecond) {
                    $baseAmount += ($this->costBreakdown['subtotals']['second_tuition'] ?? 0);
                }

                // Proration for overlapping_duration
                if ($rule->date_condition_type === 'overlapping_duration' && $rule->valid_from_date && $rule->valid_to_date) {
                    $discountStartDate = Carbon::parse($rule->valid_from_date);
                    $discountEndDate = Carbon::parse($rule->valid_to_date);

                    $totalWeeksIncluded = 0;
                    $overlapWeeksIncluded = 0;

                    if ($includePrimary && $this->courseWeeks && $this->startDate) {
                        $courseStart = $this->startDate->copy();
                        if ($courseStart->dayOfWeek !== Carbon::MONDAY) {
                            $courseStart = $courseStart->next(Carbon::MONDAY);
                        }
                        $courseEnd = $this->calculateCourseEndDate();
                        $totalWeeksIncluded += $this->courseWeeks;
                        $overlapWeeks = $this->calculateOverlapWeeks($courseStart, $courseEnd, $discountStartDate, $discountEndDate);
                        $overlapWeeksIncluded += $overlapWeeks;
                        if ($overlapWeeks > 0) {
                            $appliedStart = $courseStart->max($discountStartDate);
                            $appliedEnd = $courseEnd->min($discountEndDate);
                            $noteRanges[] = sprintf('Discount applied for %s – %s (overlapping duration, Course).', $appliedStart->format('d M Y'), $appliedEnd->format('d M Y'));
                        }
                    }

                    if ($includeSecond && $this->secondCourseWeeks && $this->secondStartDate) {
                        $secondStart = $this->secondStartDate->copy();
                        if ($secondStart->dayOfWeek !== Carbon::MONDAY) {
                            $secondStart = $secondStart->next(Carbon::MONDAY);
                        }
                        $secondEnd = $this->calculateSecondCourseEndDate();
                        $totalWeeksIncluded += $this->secondCourseWeeks;
                        $overlapWeeks2 = $this->calculateOverlapWeeks($secondStart, $secondEnd, $discountStartDate, $discountEndDate);
                        $overlapWeeksIncluded += $overlapWeeks2;
                        if ($overlapWeeks2 > 0) {
                            $appliedStart2 = $secondStart->max($discountStartDate);
                            $appliedEnd2 = $secondEnd->min($discountEndDate);
                            $noteRanges[] = sprintf('Discount applied for %s – %s (overlapping duration, Second Course).', $appliedStart2->format('d M Y'), $appliedEnd2->format('d M Y'));
                        }
                    }

                    if ($totalWeeksIncluded > 0) {
                        $overlapFactor = max(0.0, min(1.0, $overlapWeeksIncluded / $totalWeeksIncluded));
                    } else {
                        $overlapFactor = 0.0;
                    }
                }
                break;
            case 'fixed_schedule_courses':
                // Similar to course_tuition but only when the selected course(s) have pricing_type = 'fixed_schedule'
                $includePrimary = true;
                $includeSecond = true;

                if ($rule->course_id !== null || $rule->course_type_id !== null) {
                    $includePrimary = false;
                    $includeSecond = false;

                    // Match by specific course
                    if ($rule->course_id !== null) {
                        if ($this->course && $rule->course_id === $this->course->id) {
                            $includePrimary = true;
                        }
                        if ($this->secondCourse && $rule->course_id === $this->secondCourse->id) {
                            $includeSecond = true;
                        }
                    }

                    // Match by specific course type
                    if ($rule->course_type_id !== null) {
                        $primaryTypeId = $this->course ? $this->course->course_type_id : null;
                        $secondTypeId = $this->secondCourse ? $this->secondCourse->course_type_id : null;
                        if ($rule->course_type_id === $primaryTypeId) {
                            $includePrimary = true;
                        }
                        if ($rule->course_type_id === $secondTypeId) {
                            $includeSecond = true;
                        }
                    }
                }

                $baseAmount = 0;
                if ($includePrimary && $this->course && $this->course->pricing_type === 'fixed_schedule') {
                    $baseAmount += ($this->costBreakdown['subtotals']['tuition'] ?? 0);
                }
                if ($includeSecond && $this->secondCourse && $this->secondCourse->pricing_type === 'fixed_schedule') {
                    $baseAmount += ($this->costBreakdown['subtotals']['second_tuition'] ?? 0);
                }
                break;
            case 'accommodation_price':
                 $baseAmount = $this->costBreakdown['subtotals']['accommodation'];
                 // Proration for overlapping_duration
                 if ($rule->date_condition_type === 'overlapping_duration' && $rule->valid_from_date && $rule->valid_to_date && $this->accommodationWeeks && $this->startDate) {
                     $discountStartDate = Carbon::parse($rule->valid_from_date);
                     $discountEndDate = Carbon::parse($rule->valid_to_date);
                     $accommodationStart = $this->startDate->copy();
                     if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
                         $accommodationStart = $accommodationStart->next(Carbon::MONDAY);
                     }
                     $accommodationEnd = $this->calculateAccommodationEndDate();
                     $overlapWeeks = $this->calculateOverlapWeeks($accommodationStart, $accommodationEnd, $discountStartDate, $discountEndDate);
                     $overlapFactor = max(0.0, min(1.0, $overlapWeeks / max(1, $this->accommodationWeeks)));
                     if ($overlapWeeks > 0) {
                         $appliedStart = $accommodationStart->max($discountStartDate);
                         $appliedEnd = $accommodationEnd->min($discountEndDate);
                         $noteRanges[] = sprintf('Discount applied for %s – %s (overlapping duration, Accommodation).', $appliedStart->format('d M Y'), $appliedEnd->format('d M Y'));
                     }
                 }
                 break;
            case 'registration_fee':
                 // Find registration fee item if added
                 foreach ($this->costBreakdown['items'] as $item) {
                     if ($item['name'] === 'Registration Fee' && $item['category'] === 'fees') {
                         $baseAmount = $item['amount'];
                         break;
                     }
                 }
                 break;
             case 'accommodation_fee':
                  // Find accommodation placement fee item if added
                  foreach ($this->costBreakdown['items'] as $item) {
                      if ($item['name'] === 'Accommodation Placement Fee' && $item['category'] === 'fees') {
                          $baseAmount = $item['amount'];
                          break;
                      }
                  }
                  break;
            case 'addon':
                if ($rule->addon_id === null) return 0; // Should not happen if validation is correct
                // Find the specific addon item cost
                $addon = Addon::find($rule->addon_id); // Fetch addon details if needed (e.g., name)
                if (!$addon) return 0;
                foreach ($this->costBreakdown['items'] as $item) {
                    // Match based on name - might need refinement if names aren't unique enough
                    // A better approach might be to store addon_id with the item in addItem
                    if (str_starts_with($item['name'], $addon->name) && $item['category'] === 'addons') {
                        $baseAmount = $item['amount'];
                        break;
                    }
                }
                break;
        }

        if ($baseAmount <= 0) {
            return 0; // No base amount to apply discount to
        }

        // Calculate discount
        if ($rule->discount_type === 'percentage') {
            $amount = ($baseAmount * $rule->discount_value) / 100;
            // Apply proration factor if overlapping_duration
            if ($rule->date_condition_type === 'overlapping_duration') {
                $amount *= $overlapFactor;
            }
            // Add transparency notes
            foreach ($noteRanges as $nr) { $this->costBreakdown['notes'][] = $nr; }
            return $amount;
        } elseif ($rule->discount_type === 'fixed_amount') {
            // Ensure fixed amount doesn't exceed the base amount
            $amount = min($baseAmount, $rule->discount_value);
            if ($rule->date_condition_type === 'overlapping_duration') {
                $amount *= $overlapFactor;
            }
            foreach ($noteRanges as $nr) { $this->costBreakdown['notes'][] = $nr; }
            return $amount;
        } elseif ($rule->discount_type === 'fee_waiver') {
             // Return the full base amount for waiver, handled by addDiscount
             // No proration for fee waiver (waives the full applicable base)
             foreach ($noteRanges as $nr) { $this->costBreakdown['notes'][] = $nr; }
             return $baseAmount;
        } elseif ($rule->discount_type === 'fixed_amount_per_week') {
            if ($rule->applies_to !== 'accommodation_price' || !$rule->valid_from_date || !$rule->valid_to_date) {
                return 0;
            }

            $discountStartDate = Carbon::parse($rule->valid_from_date);
            $discountEndDate = Carbon::parse($rule->valid_to_date);

            $includePrimaryAccommodation = true;
            $includeSecondAccommodation = true;
            if ($rule->accommodation_id !== null || $rule->accommodation_type !== null) {
                $includePrimaryAccommodation = false;
                $includeSecondAccommodation = false;
                if ($rule->accommodation_id !== null) {
                    if ($this->accommodation && $rule->accommodation_id === $this->accommodation->id) {
                        $includePrimaryAccommodation = true;
                    }
                    if ($this->secondAccommodation && $rule->accommodation_id === $this->secondAccommodation->id) {
                        $includeSecondAccommodation = true;
                    }
                }
                if ($rule->accommodation_type !== null) {
                    $needle = strtolower($rule->accommodation_type);
                    $primaryType = $this->accommodation ? strtolower($this->accommodation->type ?? '') : null;
                    $secondType = $this->secondAccommodation ? strtolower($this->secondAccommodation->type ?? '') : null;
                    if ($primaryType !== null && str_contains($primaryType, $needle)) {
                        $includePrimaryAccommodation = true;
                    }
                    if ($secondType !== null && str_contains($secondType, $needle)) {
                        $includeSecondAccommodation = true;
                    }
                }
            }

            $total = 0;

            if ($includePrimaryAccommodation && $this->accommodationWeeks && $this->startDate) {
                $accommodationStart = $this->startDate->copy();
                if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
                    $accommodationStart = $accommodationStart->next(Carbon::MONDAY);
                }
                $accommodationEnd = $this->calculateAccommodationEndDate();
                $weeks = $this->calculateOverlapWeeks($accommodationStart, $accommodationEnd, $discountStartDate, $discountEndDate);
                if ($weeks > 0) {
                    $amount = $weeks * $rule->discount_value;
                    $basePrimary = $this->costBreakdown['subtotals']['accommodation'] ?? 0;
                    $total += min($basePrimary, $amount);
                    $appliedStart = $accommodationStart->max($discountStartDate);
                    $appliedEnd = $accommodationEnd->min($discountEndDate);
                    $this->costBreakdown['notes'][] = sprintf('Discount applied for %s – %s (overlapping duration, Accommodation).', $appliedStart->format('d M Y'), $appliedEnd->format('d M Y'));
                }
            }

            if ($includeSecondAccommodation && $this->secondAccommodationWeeks) {
                $secondStart = $this->calculateSecondAccommodationStartDate();
                $secondEnd = $this->calculateSecondAccommodationEndDate();
                $weeks2 = $this->calculateOverlapWeeks($secondStart, $secondEnd, $discountStartDate, $discountEndDate);
                if ($weeks2 > 0) {
                    $amount2 = $weeks2 * $rule->discount_value;
                    $baseSecond = $this->costBreakdown['subtotals']['second_accommodation'] ?? 0;
                    $total += min($baseSecond, $amount2);
                    $appliedStart2 = $secondStart->max($discountStartDate);
                    $appliedEnd2 = $secondEnd->min($discountEndDate);
                    $this->costBreakdown['notes'][] = sprintf('Discount applied for %s – %s (overlapping duration, Second Accommodation).', $appliedStart2->format('d M Y'), $appliedEnd2->format('d M Y'));
                }
            }

            if ($total > 0) {
                return $total;
            }
        }

        return 0;
    }

    /**
     * Determine which course(s) a course_tuition discount rule applies to.
     * Returns [primaryApplies, secondApplies] booleans.
     */
    private function getCourseAttributionForRule(DiscountRule $rule): array
    {
        $primaryApplies = true;
        $secondApplies = true;

        $ruleCourses = $rule->courses->pluck('id')->toArray();
        // If targeting specific course or type, restrict attribution to matching course(s)
        if (!empty($ruleCourses) || $rule->course_id !== null || $rule->course_type_id !== null) {
            $primaryApplies = false;
            $secondApplies = false;

            if (!empty($ruleCourses)) {
                if ($this->course && in_array($this->course->id, $ruleCourses)) {
                    $primaryApplies = true;
                }
                if ($this->secondCourse && in_array($this->secondCourse->id, $ruleCourses)) {
                    $secondApplies = true;
                }
            } elseif ($rule->course_id !== null) {
                if ($this->course && $rule->course_id === $this->course->id) {
                    $primaryApplies = true;
                }
                if ($this->secondCourse && $rule->course_id === $this->secondCourse->id) {
                    $secondApplies = true;
                }
            }

            if ($rule->course_type_id !== null) {
                $primaryTypeId = $this->course ? $this->course->course_type_id : null;
                $secondTypeId = $this->secondCourse ? $this->secondCourse->course_type_id : null;
                if ($rule->course_type_id === $primaryTypeId) {
                    $primaryApplies = true;
                }
                if ($rule->course_type_id === $secondTypeId) {
                    $secondApplies = true;
                }
            }
        }

        return [$primaryApplies, $secondApplies];
    }

    /**
     * Determine which course(s) a fixed_schedule_courses discount rule applies to,
     * considering course/type targeting and pricing_type filter.
     * Returns [primaryApplies, secondApplies] booleans.
     */
    private function getFixedScheduleCourseAttributionForRule(DiscountRule $rule): array
    {
        $primaryApplies = true;
        $secondApplies = true;

        // Restrict by specific course or type if set
        if ($rule->courses->isNotEmpty() || $rule->course_id !== null || $rule->course_type_id !== null) {
            $primaryApplies = false;
            $secondApplies = false;

            $ruleCourses = $rule->courses->pluck('id')->toArray();
            if (!empty($ruleCourses)) {
                if ($this->course && in_array($this->course->id, $ruleCourses)) {
                    $primaryApplies = true;
                }
                if ($this->secondCourse && in_array($this->secondCourse->id, $ruleCourses)) {
                    $secondApplies = true;
                }
            } elseif ($rule->course_id !== null) {
                if ($this->course && $rule->course_id === $this->course->id) {
                    $primaryApplies = true;
                }
                if ($this->secondCourse && $rule->course_id === $this->secondCourse->id) {
                    $secondApplies = true;
                }
            }

            if ($rule->course_type_id !== null) {
                $primaryTypeId = $this->course ? $this->course->course_type_id : null;
                $secondTypeId = $this->secondCourse ? $this->secondCourse->course_type_id : null;
                if ($rule->course_type_id === $primaryTypeId) {
                    $primaryApplies = true;
                }
                if ($rule->course_type_id === $secondTypeId) {
                    $secondApplies = true;
                }
            }
        }

        // Pricing type filter: only fixed_schedule courses
        if (!($this->course && $this->course->pricing_type === 'fixed_schedule')) {
            $primaryApplies = false;
        }
        if (!($this->secondCourse && $this->secondCourse->pricing_type === 'fixed_schedule')) {
            $secondApplies = false;
        }

        return [$primaryApplies, $secondApplies];
    }

    /**
     * Get the cost category associated with a discount's 'applies_to' field.
     * Used for tracking non-combinable discounts.
     *
     * @param string $appliesTo
     * @return string
      */
     private function getDiscountCategory(string $appliesTo): string
     {
         return match ($appliesTo) {
            'course_tuition', 'fixed_schedule_courses' => 'tuition',
            'accommodation_price' => 'accommodation',
            'registration_fee', 'accommodation_fee' => 'fees',
            'addon' => 'addons', // Or potentially more granular if needed
            default => 'unknown',
         };
      }

    /**
     * Add pricing rule explanations to the notes based on quotation extraction date and course dates.
     */
    private function addPricingRuleExplanations(): void
    {
        if (!$this->startDate) {
            return;
        }

        $quotationDate = $this->getQuotationExtractionDate();
        $cutoffDate = $this->getCutoffDate();
        $courseEndDate = $this->calculateCourseEndDate();
        $endOf2025 = Carbon::create(2025, 12, 31);
        
        // Determine which pricing rule was applied
        $pricingExplanation = "";
        
        if ($quotationDate->lt($cutoffDate)) {
            // Rule: Quotation date before cutoff → Always 2025 pricing
            $pricingExplanation = "2025 pricing applied for entire duration because quotation extraction date (" . 
                                $quotationDate->format('j M Y') . ") is before the cutoff date (" . 
                                $cutoffDate->format('j M Y') . ").";
        } else {
            // Quotation date >= cutoff → Apply course-based rules
            if ($this->startDate->lt($cutoffDate)) {
                // Course starts before cutoff → 2025 pricing
                $pricingExplanation = "2025 pricing applied for entire duration because course starts (" . 
                                    $this->startDate->format('j M Y') . ") before the cutoff date (" . 
                                    $cutoffDate->format('j M Y') . ").";
            } elseif ($this->startDate->gte($cutoffDate) && $courseEndDate->lte($endOf2025)) {
                // Course starts after cutoff but fully within 2025 → 2025 pricing
                $pricingExplanation = "2025 pricing applied because course is fully within 2025 (starts " . 
                                    $this->startDate->format('j M Y') . ", ends " . 
                                    $courseEndDate->format('j M Y') . ").";
            } elseif ($this->startDate->gte($cutoffDate) && $courseEndDate->gt($endOf2025)) {
                // Course starts after cutoff and continues into 2026 → Mixed pricing
                $pricingExplanation = "Mixed pricing applied: 2025 rates for weeks in 2025, 2026 rates for weeks in 2026. " .
                                    "Course starts " . $this->startDate->format('j M Y') . " and continues into 2026 (ends " . 
                                    $courseEndDate->format('j M Y') . ").";
            } elseif ($this->startDate->year >= 2026) {
                // Course starts in 2026 → 2026 pricing
                $pricingExplanation = "2026 pricing applied because course starts in 2026 (" . 
                                    $this->startDate->format('j M Y') . ").";
            }
        }
        
        if ($pricingExplanation) {
            $this->costBreakdown['notes'][] = "Pricing Rule: " . $pricingExplanation;
        }
        
        // Add quotation extraction date explanation
        $quotationExplanation = "Quotation extraction date: " . $quotationDate->format('j M Y');
        if ($quotationDate->ne(Carbon::today())) {
            $quotationExplanation .= " (overridden from system date " . Carbon::today()->format('j M Y') . ")";
        }
        $this->costBreakdown['notes'][] = $quotationExplanation;
        
        // Add year range breakdown explanations
        $this->addYearRangeExplanations();
    }
    
    /**
     * Add detailed year range explanations to help clarify the year-based pricing structure.
     */
    private function addYearRangeExplanations(): void
    {
        if (!$this->startDate) {
            return;
        }
        
        $courseEndDate = $this->calculateCourseEndDate();
        $endOf2025 = Carbon::create(2025, 12, 31);
        
        // Check if we have year-split items (course fees or supplements)
        $has2025Items = false;
        $has2026Items = false;
        $hasChristmasSupplementSplit = false;
        
        foreach ($this->costBreakdown['items'] as $item) {
            if (str_contains($item['name'], '(2025)')) {
                $has2025Items = true;
            }
            if (str_contains($item['name'], '(2026)')) {
                $has2026Items = true;
            }
            if (str_contains($item['name'], 'Christmas Supplement') && (str_contains($item['name'], '(2025)') || str_contains($item['name'], '(2026)'))) {
                $hasChristmasSupplementSplit = true;
            }
        }
        
        // Add explanations for year-split course fees
        if ($has2025Items && $has2026Items) {
            $this->costBreakdown['notes'][] = "Course fees are split by calendar year: 2025 fees apply to weeks taught in 2025, and 2026 fees apply to weeks taught in 2026.";
            
            // Calculate and explain the year split
            $weeksIn2025 = $this->calculateWeeksInYear($this->startDate, $courseEndDate, 2025);
            $weeksIn2026 = $this->calculateWeeksInYear($this->startDate, $courseEndDate, 2026);
            
            if ($weeksIn2025 > 0 && $weeksIn2026 > 0) {
                $this->costBreakdown['notes'][] = "Course duration breakdown: {$weeksIn2025} weeks in 2025, {$weeksIn2026} weeks in 2026.";
            }
        }
        
        // Add explanation for Christmas supplement year assignment
        if ($hasChristmasSupplementSplit) {
            $this->costBreakdown['notes'][] = "Christmas supplement is applied only to the year where the Christmas break occurs during the course.";
        }
        
        // Add explanation for accommodation year assignment if applicable
        if ($this->accommodation && $this->accommodationWeeks) {
            $accommodationStart = $this->startDate->copy();
            if ($accommodationStart->dayOfWeek !== Carbon::MONDAY) {
                $accommodationStart = $accommodationStart->previous(Carbon::MONDAY);
            }
            $accommodationEnd = $accommodationStart->copy()->addWeeks($this->accommodationWeeks)->subDay();
            
            if ($accommodationStart->year !== $accommodationEnd->year) {
                $this->costBreakdown['notes'][] = "Accommodation fees are assigned to years based on the same pricing rules as course fees.";
            }
        }
        
        // Add cutoff date explanation for clarity
        $cutoffDate = $this->getCutoffDate();
        $this->costBreakdown['notes'][] = "Pricing cutoff date: " . $cutoffDate->format('j M Y') . " - courses starting before this date use 2025 pricing throughout.";
    }

    /**
     * Calculate the final total based on subtotals and applied discounts.
     */
    private function calculateTotal(): void
    {
        $total = 0;
        foreach ($this->costBreakdown['subtotals'] as $subtotal) {
            $total += $subtotal;
        }

        // Subtract discounts
        $totalDiscount = 0;
        foreach ($this->costBreakdown['discounts'] as $discount) {
            $totalDiscount += $discount['amount'];
        }
        $total -= $totalDiscount;

        $this->costBreakdown['total'] = max(0, $total); // Ensure total is not negative
        
        // Calculate year-based subtotals for PDF display
        $this->calculateYearSubtotals();
        
        // Store pricing rule information for PDF display
        $this->storePricingRuleForDisplay();
    }

    /**
     * Helper to add an item to the cost breakdown.
     */
    private function addItem(string $name, float $amount, string $category, bool $included = false): void
    {
        // Define core item categories that should be displayed even if the amount is 0
        $coreCategories = ['tuition', 'second_tuition', 'accommodation', 'second_accommodation'];
        $isCoreItem = in_array($category, $coreCategories);

        if ($amount <= 0 && !$included && !$isCoreItem) return;

        $this->costBreakdown['items'][] = [
            'name' => $included ? ($name . ' (Included)') : $name,
            'amount' => round($amount, 2), // Round to 2 decimal places
            'category' => $category,
            'is_included' => $included,
        ];
        // Ensure the subtotal category exists before adding
        if (!isset($this->costBreakdown['subtotals'][$category])) {
            $this->costBreakdown['subtotals'][$category] = 0;
        }
        $this->costBreakdown['subtotals'][$category] += round($amount, 2);
    }


     /**
      * Helper to add a discount to the cost breakdown.
      */
    private function addDiscount(string $name, float $amount, string $appliedTo, bool $hidden = false, bool $isNationality = false, ?bool $appliesToPrimaryCourse = null, ?bool $appliesToSecondCourse = null): void
    {
        // Allow 0 amount only for waivers, otherwise amount must be positive
        if ($amount <= 0 && !str_ends_with($appliedTo, '_waiver')) return;

        $this->costBreakdown['discounts'][] = [
            'name' => $name,
            'amount' => round($amount, 2), // Store as positive value representing deduction
            'applied_to' => $appliedTo,
            'hidden' => $hidden,
            'is_nationality' => $isNationality,
            // Course attribution flags for UI display when applies_to is course_tuition or fixed_schedule_courses
            'applies_to_primary_course' => $appliesToPrimaryCourse,
            'applies_to_second_course' => $appliesToSecondCourse,
        ];
    }

    /**
     * Add included generic items that don't have explicit pricing rules in the system.
     * For Junior packages, when flagged as included, they should appear with £0 and be labeled "Included".
     */
    private function addIncludedGenericItems(): void
    {
        if (!$this->course || $this->course->category !== 'junior') {
            return;
        }
        $settings = $this->course->juniorSettings;
        if (!$settings) {
            return;
        }
        if ($settings->includes_activities) {
            $this->addItem('Activities', 0, 'fees', true);
        }
        if ($settings->includes_local_travel) {
            $this->addItem('Local Travel', 0, 'fees', true);
        }
        // If airport transfer is included and no specific transfer items were added, ensure a generic entry exists.
        if ($settings->includes_airport_transfer) {
            // Prevent duplicate entry if already added by calculateAirportTransferCosts
            $exists = false;
            foreach ($this->costBreakdown['items'] ?? [] as $item) {
                if ($item['name'] === 'Airport Transfer' && $item['category'] === 'fees') {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $this->addItem('Airport Transfer', 0, 'fees', true);
            }
        }
    }

     /**
      * Add an error message to the breakdown.
      */
     private function addError(string $message): void
     {
         Log::error("FeeCalculatorService Error: " . $message, $this->quoteDetails);
         $this->costBreakdown['errors'][] = $message;
     }

    /**
     * Calculate the number of full weeks overlapping between two date ranges.
     *
     * @param Carbon $range1Start
     * @param Carbon $range1End
     * @param Carbon $range2Start
     * @param Carbon $range2End
     * @return int Number of overlapping weeks
     */
    private function calculateOverlapWeeks(Carbon $range1Start, Carbon $range1End, Carbon $range2Start, Carbon $range2End): int
    {
        // Find the actual start and end of the overlap period
        $overlapStart = $range1Start->max($range2Start);
        $overlapEnd = $range1End->min($range2End);

        // Check if there is any overlap
        if ($overlapStart->greaterThanOrEqualTo($overlapEnd)) {
            return 0; // No overlap or touches at the boundary
        }

        // Calculate the difference in days and convert to full weeks
        // Add 1 day because diffInDays is exclusive of the end date for full days
        $overlapDays = $overlapStart->diffInDays($overlapEnd) +1;

        // Calculate full weeks, rounding up.
        // Use 7 days per week for calculation.
        return ceil($overlapDays / 7);
    }

    /**
     * Calculate year-based subtotals for PDF display.
     * Updated to properly handle year-split course fees and single Christmas supplements.
     */
    private function calculateYearSubtotals(): void
    {
        $yearSubtotals = ['2025' => 0, '2026' => 0];
        
        // Sum up all items by year based on their names and categories
        foreach ($this->costBreakdown['items'] as $item) {
            $itemYear = $this->determineItemYear($item);
            
            if ($itemYear === '2025') {
                $yearSubtotals['2025'] += $item['amount'];
            } elseif ($itemYear === '2026') {
                $yearSubtotals['2026'] += $item['amount'];
            } else {
                // For items without clear year specification, use pricing logic
                $assignedYear = $this->determineItemYearByPricingLogic($item);
                $yearSubtotals[$assignedYear] += $item['amount'];
            }
        }
        
        // Apply discounts proportionally to each year
        $totalBeforeDiscounts = $yearSubtotals['2025'] + $yearSubtotals['2026'];
        if ($totalBeforeDiscounts > 0) {
            $totalDiscounts = 0;
            foreach ($this->costBreakdown['discounts'] as $discount) {
                $totalDiscounts += $discount['amount'];
            }
            
            if ($totalDiscounts > 0) {
                $discount2025 = ($yearSubtotals['2025'] / $totalBeforeDiscounts) * $totalDiscounts;
                $discount2026 = ($yearSubtotals['2026'] / $totalBeforeDiscounts) * $totalDiscounts;
                
                $yearSubtotals['2025'] -= $discount2025;
                $yearSubtotals['2026'] -= $discount2026;
            }
        }
        
        // Only store non-zero subtotals
        $this->costBreakdown['year_subtotals'] = [];
        if ($yearSubtotals['2025'] > 0) {
            $this->costBreakdown['year_subtotals']['2025'] = round($yearSubtotals['2025'], 2);
        }
        if ($yearSubtotals['2026'] > 0) {
            $this->costBreakdown['year_subtotals']['2026'] = round($yearSubtotals['2026'], 2);
        }
    }

    /**
     * Determine the year for an item based on its name and category.
     */
    private function determineItemYear(array $item): ?string
    {
        $name = $item['name'];
        
        // Check for explicit year mentions in item names
        if (str_contains($name, '(2025)') || str_contains($name, '– 2025)')) {
            return '2025';
        }
        if (str_contains($name, '(2026)') || str_contains($name, '– 2026)')) {
            return '2026';
        }
        
        // Check for Christmas supplements with year specification
        if (str_contains($name, 'Christmas Supplement (2025)')) {
            return '2025';
        }
        if (str_contains($name, 'Christmas Supplement (2026)')) {
            return '2026';
        }
        
        return null; // No clear year specification
    }

    /**
     * Determine item year based on pricing logic when no explicit year is specified.
     */
    private function determineItemYearByPricingLogic(array $item): string
    {
        $quotationDate = $this->getQuotationExtractionDate();
        $cutoffDate = $this->getCutoffDate();
        
        // Use the same logic as determinePricingYears for consistency
        if ($quotationDate->lt($cutoffDate)) {
            return '2025'; // Pre-cutoff quotations use 2025 pricing
        }
        
        if ($this->startDate && $this->startDate->lt($cutoffDate)) {
            return '2025'; // Courses starting before cutoff use 2025 pricing
        }
        
        // For courses starting after cutoff, check if they're fully in 2025
        if ($this->startDate && $this->startDate->year == 2025) {
            $courseEndDate = $this->calculateCourseEndDate();
            if ($courseEndDate->year == 2025) {
                return '2025'; // Course fully within 2025
            }
        }
        
        // Default assignment based on course start year
        if ($this->startDate && $this->startDate->year == 2025) {
            return '2025';
        }
        
        return '2026';
    }

    /**
     * Calculate the number of weeks that fall within a specific year for a course.
     *
     * @param Carbon $courseStart
     * @param Carbon $courseEnd
     * @param int $year
     * @return int Number of weeks in the specified year
     */
    private function calculateWeeksInYear(Carbon $courseStart, Carbon $courseEnd, int $year): int
    {
        $yearStart = Carbon::create($year, 1, 1);
        $yearEnd = Carbon::create($year, 12, 31);
        
        // Find the overlap between course dates and the specified year
        $overlapStart = $courseStart->max($yearStart);
        $overlapEnd = $courseEnd->min($yearEnd);
        
        // If no overlap, return 0
        if ($overlapStart->gt($overlapEnd)) {
            return 0;
        }
        
        // Count Monday-to-Friday weeks within the overlap period
        $weeks = 0;
        $currentDate = $overlapStart->copy();
        
        // Find the first Monday in the overlap period
        while ($currentDate->dayOfWeek !== Carbon::MONDAY && $currentDate->lte($overlapEnd)) {
            $currentDate->addDay();
        }
        
        // Count weeks (Monday to Friday)
        while ($currentDate->lte($overlapEnd)) {
            if ($currentDate->dayOfWeek === Carbon::MONDAY) {
                // Check if we have a full week (Monday to Friday) within the overlap
                $weekEnd = $currentDate->copy()->addDays(4); // Friday of the same week
                if ($weekEnd->lte($overlapEnd)) {
                    $weeks++;
                }
            }
            $currentDate->addDay();
        }
        
        return $weeks;
    }

    /**
     * Store pricing rule information for PDF display.
     */
    private function storePricingRuleForDisplay(): void
    {
        if (!$this->startDate) {
            return;
        }

        $quotationDate = $this->getQuotationExtractionDate();
        $cutoffDate = $this->getCutoffDate();
        $courseEndDate = $this->calculateCourseEndDate();
        $endOf2025 = Carbon::create(2025, 12, 31);
        
        // Determine which pricing rule was applied
        if ($quotationDate->lt($cutoffDate)) {
            $this->costBreakdown['pricing_rule_applied'] = "2025 pricing (quotation date before cutoff)";
        } else {
            if ($this->startDate->lt($cutoffDate)) {
                $this->costBreakdown['pricing_rule_applied'] = "2025 pricing (course starts before cutoff)";
            } elseif ($this->startDate->gte($cutoffDate) && $courseEndDate->lte($endOf2025)) {
                $this->costBreakdown['pricing_rule_applied'] = "2025 pricing (course fully within 2025)";
            } elseif ($this->startDate->gte($cutoffDate) && $courseEndDate->gt($endOf2025)) {
                $this->costBreakdown['pricing_rule_applied'] = "Mixed pricing (2025 rates for 2025 weeks, 2026 rates for 2026 weeks)";
            } elseif ($this->startDate->year >= 2026) {
                $this->costBreakdown['pricing_rule_applied'] = "2026 pricing (course starts in 2026)";
            }
        }
    }
}

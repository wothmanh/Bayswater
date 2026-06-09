<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DiscountRuleRequest;
use App\Models\Accommodation;
use App\Models\Addon;
use App\Models\Country;
use App\Models\Course;
use App\Models\CourseType;
use App\Models\DiscountRule;
use App\Models\Region;
use App\Models\School;
use App\Http\Controllers\Admin\NationalityHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Import DB facade
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class DiscountRuleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = DiscountRule::with([
            'school',
            'country',
            'region',
            'course',
            'courseType',
            'accommodation',
            'addon'
        ]);

        // Filter by Name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Filter by Discount Type
        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->input('discount_type'));
        }

        // Filter by Applies To
        if ($request->filled('applies_to')) {
            $query->where('applies_to', $request->input('applies_to'));
        }

        // Filter by School
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }

        // Filter by Course Type
        if ($request->filled('course_type_id')) {
            $query->where('course_type_id', $request->input('course_type_id'));
        }

        // Filter by Course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->input('course_id'));
        }

        // Filter by Active Status
        if ($request->filled('active')) {
            $query->where('active', $request->input('active') == '1');
        }

        // Filter by Date Condition Type
        if ($request->filled('date_condition_type')) {
            $query->where('date_condition_type', $request->input('date_condition_type'));
        }

        // Filter by Valid From Date
        if ($request->filled('valid_from_date')) {
            $query->whereDate('valid_from_date', '>=', $request->input('valid_from_date'));
        }

        // Filter by Valid To Date
        if ($request->filled('valid_to_date')) {
            $query->whereDate('valid_to_date', '<=', $request->input('valid_to_date'));
        }

        // Filter by Region
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->input('region_id'));
        }

        $discountRules = $query->orderBy('name')->paginate(20);

        // Fetch options for filters
        $schools = School::orderBy('name')->pluck('name', 'id');
        $courseTypes = CourseType::orderBy('name')->pluck('name', 'id');
        $courses = Course::orderBy('name')->pluck('name', 'id');
        $regions = Region::orderBy('name')->pluck('name', 'id');
        $discountTypes = ['percentage' => 'Percentage', 'fixed_amount' => 'Fixed Amount', 'fee_waiver' => 'Fee Waiver', 'fixed_amount_per_week' => 'Fixed Amount Per Week'];
        $appliesToOptions = [
            'course_tuition' => 'Course Tuition',
            'fixed_schedule_courses' => 'Fixed Schedule Courses',
            'accommodation_price' => 'Accommodation Price',
            'registration_fee' => 'Registration Fee',
            'accommodation_fee' => 'Accommodation Fee',
            'addon' => 'Specific Addon'
        ];
        $dateConditionTypes = [
            'booking_date' => 'Booking Date',
            'start_date' => 'Start Date',
            'overlapping_duration' => 'Overlapping Duration'
        ];

        return view('admin.discount_rules.index', compact(
            'discountRules',
            'schools',
            'courseTypes',
            'courses',
            'regions',
            'discountTypes',
            'appliesToOptions',
            'dateConditionTypes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $countries = Country::where('active', true)->orderBy('name')->pluck('name', 'id');
        $regions = Region::where('active', true)->orderBy('name')->pluck('name', 'id'); // Fetch Regions
        $courses = Course::where('active', true)->orderBy('name')->get(['id', 'name', 'course_type_id']); // Fetch full course objects for filtering
        $courseTypes = CourseType::where('active', true)->orderBy('name')->pluck('name', 'id');
        $accommodations = Accommodation::where('active', true)->orderBy('name')->pluck('name', 'id');
        $addons = Addon::where('active', true)->orderBy('name')->pluck('name', 'id');
        $nationalities = NationalityHelper::getNationalities(); // Add nationalities
        $discountTypes = ['percentage' => 'Percentage', 'fixed_amount' => 'Fixed Amount', 'fee_waiver' => 'Fee Waiver', 'fixed_amount_per_week' => 'Fixed Amount Per Week']; // Added new type
        $appliesToOptions = [
            'course_tuition' => 'Course Tuition',
            'fixed_schedule_courses' => 'Fixed Schedule Courses',
            'accommodation_price' => 'Accommodation Price',
            'registration_fee' => 'Registration Fee',
            'accommodation_fee' => 'Accommodation Fee',
            'addon' => 'Specific Addon'
        ];
        $dateConditionTypes = [
            'booking_date' => 'Booking Date',
            'start_date' => 'Start Date',
            'overlapping_duration' => 'Overlapping Duration (Start or Overlap Period)'
        ];
        return view('admin.discount_rules.create', compact(
            'schools',
            'countries',
            'regions',
            'courses',
            'courseTypes',
            'accommodations',
            'addons',
            'nationalities', // Pass regions and nationalities
            'discountTypes',
            'appliesToOptions',
            'dateConditionTypes'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hide_rule_name_in_calculator' => 'nullable|boolean',
            'description' => 'nullable|string',
            'discount_type' => ['required', Rule::in(['percentage', 'fixed_amount', 'fee_waiver', 'fixed_amount_per_week'])], // Added new type to validation
            'discount_value' => ['nullable', 'numeric', 'min:0', Rule::requiredIf(fn() => $request->input('discount_type') !== 'fee_waiver')], // Value still required unless fee waiver
            'applies_to' => ['required', Rule::in(['course_tuition', 'fixed_schedule_courses', 'accommodation_price', 'registration_fee', 'accommodation_fee', 'addon'])],
            'addon_id' => ['nullable', 'exists:addons,id', Rule::requiredIf(fn() => $request->input('applies_to') === 'addon')],
            'school_id' => 'nullable|exists:schools,id',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id', // Add validation for region_id
            'course_id' => 'nullable|exists:courses,id',
            'specific_course_ids' => 'nullable|array',
            'specific_course_ids.*' => 'exists:courses,id',
            'course_type_id' => 'nullable|exists:course_types,id',
            'accommodation_id' => 'nullable|exists:accommodations,id',
            'accommodation_type' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:255', // Add nationality validation
            'nationality_title' => 'nullable|string|max:255', // Add nationality_title validation
            'min_course_weeks' => 'nullable|integer|min:1',
            'max_course_weeks' => 'nullable|integer|min:1|gte:min_course_weeks',
            'min_accommodation_weeks' => 'nullable|integer|min:1',
            'max_accommodation_weeks' => 'nullable|integer|min:1|gte:min_accommodation_weeks',
            'valid_from_date' => 'nullable|date',
            'valid_to_date' => 'nullable|date|after_or_equal:valid_from_date',
            'quotation_extraction_date_from' => 'nullable|date',
            'quotation_extraction_date_to' => 'nullable|date|after_or_equal:quotation_extraction_date_from',
            'date_condition_type' => ['nullable', Rule::in(['booking_date', 'start_date', 'overlapping_duration'])],
            'combinable' => 'nullable|boolean',
            'priority' => 'required|integer',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $validated['combinable'] = $request->has('combinable');
        $validated['hide_rule_name_in_calculator'] = $request->has('hide_rule_name_in_calculator');
        if ($validated['discount_type'] === 'fee_waiver')
            $validated['discount_value'] = null;
        if ($validated['applies_to'] !== 'addon')
            $validated['addon_id'] = null;

        DB::transaction(function () use ($validated, $request) {
            $discountRule = DiscountRule::create($validated);
            // Sync specific courses
            $discountRule->courses()->sync((array) $request->input('specific_course_ids', []));
        });

        return redirect()->route('admin.discount-rules.index')
            ->with('success', 'Discount Rule created successfully.'); // Add flash message
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
    public function edit(DiscountRule $discountRule): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $countries = Country::where('active', true)->orderBy('name')->pluck('name', 'id');
        $regions = Region::where('active', true)->orderBy('name')->pluck('name', 'id'); // Fetch Regions
        $courses = Course::where('active', true)->orderBy('name')->get(['id', 'name', 'course_type_id']); // Fetch full course objects for filtering
        $courseTypes = CourseType::where('active', true)->orderBy('name')->pluck('name', 'id');
        $accommodations = Accommodation::where('active', true)->orderBy('name')->pluck('name', 'id');
        $addons = Addon::where('active', true)->orderBy('name')->pluck('name', 'id');
        $nationalities = NationalityHelper::getNationalities(); // Add nationalities
        $discountTypes = ['percentage' => 'Percentage', 'fixed_amount' => 'Fixed Amount', 'fee_waiver' => 'Fee Waiver', 'fixed_amount_per_week' => 'Fixed Amount Per Week']; // Added new type
        $appliesToOptions = [
            'course_tuition' => 'Course Tuition',
            'fixed_schedule_courses' => 'Fixed Schedule Courses',
            'accommodation_price' => 'Accommodation Price',
            'registration_fee' => 'Registration Fee',
            'accommodation_fee' => 'Accommodation Fee',
            'addon' => 'Specific Addon'
        ];
        $dateConditionTypes = [
            'booking_date' => 'Booking Date',
            'start_date' => 'Start Date',
            'overlapping_duration' => 'Overlapping Duration (Start or Overlap Period)'
        ];
        return view('admin.discount_rules.edit', compact(
            'discountRule',
            'schools',
            'countries',
            'regions',
            'courses',
            'courseTypes',
            'accommodations',
            'addons',
            'nationalities', // Pass regions and nationalities
            'discountTypes',
            'appliesToOptions',
            'dateConditionTypes'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DiscountRule $discountRule): RedirectResponse
    {
        // --- Debug: Log incoming request data ---
        Log::info('DiscountRule Update Request Data:', $request->all());
        // --- End Debug ---

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'hide_rule_name_in_calculator' => 'nullable|boolean',
            'description' => 'nullable|string',
            'discount_type' => ['required', Rule::in(['percentage', 'fixed_amount', 'fee_waiver', 'fixed_amount_per_week'])], // Added new type to validation
            'discount_value' => ['nullable', 'numeric', 'min:0', Rule::requiredIf(fn() => $request->input('discount_type') !== 'fee_waiver')], // Value still required unless fee waiver
            'applies_to' => ['required', Rule::in(['course_tuition', 'fixed_schedule_courses', 'accommodation_price', 'registration_fee', 'accommodation_fee', 'addon'])],
            'addon_id' => ['nullable', 'exists:addons,id', Rule::requiredIf(fn() => $request->input('applies_to') === 'addon')],
            'school_id' => 'nullable|exists:schools,id',
            'country_id' => 'nullable|exists:countries,id',
            'region_id' => 'nullable|exists:regions,id', // Add validation for region_id
            'course_id' => 'nullable|exists:courses,id',
            'specific_course_ids' => 'nullable|array',
            'specific_course_ids.*' => 'exists:courses,id',
            'course_type_id' => 'nullable|exists:course_types,id',
            'accommodation_id' => 'nullable|exists:accommodations,id',
            'accommodation_type' => 'nullable|string|max:100',
            'nationality' => 'nullable|string|max:255', // Add nationality validation
            'nationality_title' => 'nullable|string|max:255', // Add nationality_title validation
            'min_course_weeks' => 'nullable|integer|min:1',
            'max_course_weeks' => 'nullable|integer|min:1|gte:min_course_weeks',
            'min_accommodation_weeks' => 'nullable|integer|min:1',
            'max_accommodation_weeks' => 'nullable|integer|min:1|gte:min_accommodation_weeks',
            'valid_from_date' => 'nullable|date',
            'valid_to_date' => 'nullable|date|after_or_equal:valid_from_date',
            'quotation_extraction_date_from' => 'nullable|date',
            'quotation_extraction_date_to' => 'nullable|date|after_or_equal:quotation_extraction_date_from',
            'date_condition_type' => ['nullable', Rule::in(['booking_date', 'start_date', 'overlapping_duration'])],
            'combinable' => 'nullable|boolean',
            'priority' => 'required|integer',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $validated['combinable'] = $request->has('combinable');
        $validated['hide_rule_name_in_calculator'] = $request->has('hide_rule_name_in_calculator');
        if ($validated['discount_type'] === 'fee_waiver')
            $validated['discount_value'] = null;
        if ($validated['applies_to'] !== 'addon')
            $validated['addon_id'] = null;

        DB::transaction(function () use ($discountRule, $validated, $request) {
            // Explicitly set dates before mass update
            $discountRule->valid_from_date = $validated['valid_from_date'] ?? null;
            $discountRule->valid_to_date = $validated['valid_to_date'] ?? null;
            $discountRule->quotation_extraction_date_from = $validated['quotation_extraction_date_from'] ?? null;
            $discountRule->quotation_extraction_date_to = $validated['quotation_extraction_date_to'] ?? null;

            // Remove dates from validated array before mass update
            unset($validated['valid_from_date']);
            unset($validated['valid_to_date']);
            unset($validated['quotation_extraction_date_from']);
            unset($validated['quotation_extraction_date_to']);

            $discountRule->update($validated); // Update remaining fields

            // Sync specific courses
            // Use input with default empty array to handle case where field is missing (e.g. empty selection)
            // Cast to array to ensure safe sync if input is null
            $discountRule->courses()->sync((array) $request->input('specific_course_ids', []));

            // Save the model again to persist the explicitly set dates (update doesn't save automatically after explicit set)
            // Although update() calls save(), setting attributes directly doesn't trigger save for those attributes in the same call.
            $discountRule->save();
        });


        return redirect()->route('admin.discount-rules.index')
            ->with('success', 'Discount Rule updated successfully.'); // Add flash message
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiscountRule $discountRule): RedirectResponse
    {
        $discountRule->delete();
        return redirect()->route('admin.discount-rules.index')
            ->with('success', 'Discount Rule deleted successfully.'); // Add flash message
    }
}

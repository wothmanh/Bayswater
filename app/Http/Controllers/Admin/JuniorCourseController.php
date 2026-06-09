<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Course;
use App\Models\CourseJuniorSetting;
use App\Models\CourseType;
use App\Models\School;
use App\Models\City;
use App\Models\Country;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JuniorCourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::with(['school', 'courseType', 'juniorSettings'])
            ->where('category', 'junior')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(20);

        // Preload countries, cities, and schools for filters (active only)
        $countries = Country::where('active', true)->orderBy('name')->get(['id', 'name']);
        $cities = City::where('active', true)->orderBy('name')->get(['id', 'name']);
        $schools = School::where('active', true)->orderBy('name')->get(['id', 'name']);

        return view('admin.junior-courses.index', compact('courses', 'countries', 'cities', 'schools'));
    }

    public function create(): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $courseTypes = CourseType::where('active', true)->orderBy('name')->pluck('name', 'id');
        $pricingTypes = ['per_week' => 'Per Week', 'fixed_schedule' => 'Fixed Schedule'];
        $accommodations = Accommodation::where('active', true)
            ->with('school')
            ->orderBy('name')
            ->get();

        return view('admin.junior-courses.create', compact('schools', 'courseTypes', 'pricingTypes', 'accommodations'));
    }

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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|gte:min_age',
            'min_weeks' => 'nullable|integer|min:1',
            'max_weeks' => 'nullable|integer|gte:min_weeks',
            'includes_accommodation' => 'nullable|boolean',
            'buy_weeks_only' => 'nullable|boolean',
            'includes_registration_fee' => 'nullable|boolean',
            'includes_books_fee' => 'nullable|boolean',
            'includes_accommodation_placement' => 'nullable|boolean',
            'includes_activities' => 'nullable|boolean',
            'includes_local_travel' => 'nullable|boolean',
            'includes_airport_transfer' => 'nullable|boolean',
            'includes_insurance' => 'nullable|boolean',
            'accommodations' => 'nullable|array',
            'accommodations.*' => 'integer|exists:accommodations,id',
            'detail_links' => 'nullable|array',
            'detail_links.*.button_text' => 'nullable|string|max:50',
            'detail_links.*.url' => 'nullable|url',
        ]);

        $courseData = [
            'name' => $validated['name'],
            'school_id' => $validated['school_id'],
            'course_type_id' => $validated['course_type_id'],
            'pricing_type' => $validated['pricing_type'],
            'lessons_per_week' => $validated['lessons_per_week'] ?? null,
            'hours_per_week' => $validated['hours_per_week'] ?? null,
            'study_mode' => $validated['study_mode'] ?? null,
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'active' => $request->has('active'),
            'order' => $validated['order'] ?? 0,
            'category' => 'junior',
        ];

        $course = Course::create($courseData);

        $settingsData = [
            'course_id' => $course->id,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'min_age' => $validated['min_age'] ?? null,
            'max_age' => $validated['max_age'] ?? null,
            'min_weeks' => $validated['min_weeks'] ?? null,
            'max_weeks' => $validated['max_weeks'] ?? null,
            'includes_accommodation' => $request->boolean('includes_accommodation'),
            'buy_weeks_only' => $request->boolean('buy_weeks_only'),
            'includes_registration_fee' => $request->boolean('includes_registration_fee'),
            'includes_books_fee' => $request->boolean('includes_books_fee'),
            'includes_accommodation_placement' => $request->boolean('includes_accommodation_placement'),
            'includes_activities' => $request->boolean('includes_activities'),
            'includes_local_travel' => $request->boolean('includes_local_travel'),
            'includes_airport_transfer' => $request->boolean('includes_airport_transfer'),
            'includes_insurance' => $request->boolean('includes_insurance'),
        ];

        CourseJuniorSetting::create($settingsData);

        $accommodationIds = $validated['accommodations'] ?? [];

        if (!empty($accommodationIds)) {
            $allowedAccommodationIds = Accommodation::whereIn('id', $accommodationIds)
                ->where('school_id', $course->school_id)
                ->pluck('id')
                ->all();

            $course->juniorAccommodations()->sync($allowedAccommodationIds);
        }

        // Handle Junior Course Detail Links
        $detailLinks = $validated['detail_links'] ?? [];
        if (!empty($detailLinks)) {
            DB::transaction(function () use ($course, $detailLinks) {
                foreach ($detailLinks as $index => $linkData) {
                    if (!empty($linkData['url']) && !empty($linkData['button_text'])) {
                        $course->juniorDetailLinks()->create([
                            'url' => $linkData['url'],
                            'button_text' => $linkData['button_text'],
                            'sort_order' => $index,
                        ]);
                    }
                }
            });
        }

        return redirect()->route('admin.junior-courses.index');
    }

    public function edit(int $id): View
    {
        $course = Course::with(['juniorSettings', 'juniorAccommodations', 'juniorDetailLinks'])
            ->where('category', 'junior')
            ->findOrFail($id);

        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $courseTypes = CourseType::where('active', true)->orderBy('name')->pluck('name', 'id');
        $pricingTypes = ['per_week' => 'Per Week', 'fixed_schedule' => 'Fixed Schedule'];
        $accommodations = Accommodation::where('active', true)
            ->with('school')
            ->orderBy('name')
            ->get();
        $selectedAccommodations = $course->juniorAccommodations->pluck('id')->all();

        return view('admin.junior-courses.edit', compact('course', 'schools', 'courseTypes', 'pricingTypes', 'accommodations', 'selectedAccommodations'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $course = Course::where('category', 'junior')->findOrFail($id);

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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'min_age' => 'nullable|integer|min:0',
            'max_age' => 'nullable|integer|gte:min_age',
            'min_weeks' => 'nullable|integer|min:1',
            'max_weeks' => 'nullable|integer|gte:min_weeks',
            'includes_accommodation' => 'nullable|boolean',
            'includes_registration_fee' => 'nullable|boolean',
            'includes_books_fee' => 'nullable|boolean',
            'includes_accommodation_placement' => 'nullable|boolean',
            'includes_activities' => 'nullable|boolean',
            'includes_local_travel' => 'nullable|boolean',
            'includes_airport_transfer' => 'nullable|boolean',
            'includes_insurance' => 'nullable|boolean',
            'accommodations' => 'nullable|array',
            'accommodations.*' => 'integer|exists:accommodations,id',
            'detail_links' => 'nullable|array',
            'detail_links.*.button_text' => 'nullable|string|max:50',
            'detail_links.*.url' => 'nullable|url',
        ]);

        $courseData = [
            'name' => $validated['name'],
            'school_id' => $validated['school_id'],
            'course_type_id' => $validated['course_type_id'],
            'pricing_type' => $validated['pricing_type'],
            'lessons_per_week' => $validated['lessons_per_week'] ?? null,
            'hours_per_week' => $validated['hours_per_week'] ?? null,
            'study_mode' => $validated['study_mode'] ?? null,
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'active' => $request->has('active'),
            'order' => $validated['order'] ?? $course->order,
            'category' => 'junior', // Enforce category to prevent accidental loss
        ];

        $course->update($courseData);

        $settingsData = [
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'min_age' => $validated['min_age'] ?? null,
            'max_age' => $validated['max_age'] ?? null,
            'min_weeks' => $validated['min_weeks'] ?? null,
            'max_weeks' => $validated['max_weeks'] ?? null,
            'includes_accommodation' => $request->boolean('includes_accommodation'),
            'includes_registration_fee' => $request->boolean('includes_registration_fee'),
            'includes_books_fee' => $request->boolean('includes_books_fee'),
            'includes_accommodation_placement' => $request->boolean('includes_accommodation_placement'),
            'includes_activities' => $request->boolean('includes_activities'),
            'includes_local_travel' => $request->boolean('includes_local_travel'),
            'includes_airport_transfer' => $request->boolean('includes_airport_transfer'),
            'includes_insurance' => $request->boolean('includes_insurance'),
        ];

        if ($course->juniorSettings) {
            $course->juniorSettings->update($settingsData);
        } else {
            $settingsData['course_id'] = $course->id;
            CourseJuniorSetting::create($settingsData);
        }

        $accommodationIds = $validated['accommodations'] ?? [];

        $allowedAccommodationIds = [];

        if (!empty($accommodationIds)) {
            $allowedAccommodationIds = Accommodation::whereIn('id', $accommodationIds)
                ->where('school_id', $course->school_id)
                ->pluck('id')
                ->all();
        }

        $course->juniorAccommodations()->sync($allowedAccommodationIds);

        // Handle Junior Course Detail Links
        $detailLinks = $validated['detail_links'] ?? [];
        DB::transaction(function () use ($course, $detailLinks) {
            $course->juniorDetailLinks()->delete();

            if (!empty($detailLinks)) {
                foreach ($detailLinks as $index => $linkData) {
                    if (!empty($linkData['url']) && !empty($linkData['button_text'])) {
                        $course->juniorDetailLinks()->create([
                            'url' => $linkData['url'],
                            'button_text' => $linkData['button_text'],
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.junior-courses.index');
    }

    public function destroy(int $id): RedirectResponse
    {
        $course = Course::where('category', 'junior')->findOrFail($id);
        $course->delete();

        return redirect()->route('admin.junior-courses.index');
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
     * Filter junior courses by country, city, and/or school via AJAX.
     */
    public function filter(Request $request)
    {
        $countryId = $request->query('country_id');
        $cityId = $request->query('city_id');
        $schoolId = $request->query('school_id');

        $query = Course::with(['school', 'courseType', 'juniorSettings'])
            ->where('category', 'junior')
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
        $rowsHtml = view('admin.junior-courses._index_rows', compact('courses'))->render();
        $paginationHtml = view('admin.junior-courses._pagination', compact('courses'))->render();

        return response()->json([
            'rows' => $rowsHtml,
            'pagination' => $paginationHtml,
        ]);
    }
}

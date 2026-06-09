<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country; // Import Country model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $cities = City::with('country')->orderBy('order')->orderBy('name')->paginate(20);
        $countries = Country::where('active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.cities.index', compact('cities', 'countries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $countries = Country::where('active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.cities.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);
        $validated['active'] = $request->has('active');

        // Set default order if not provided
        if (!$request->has('order')) {
            $validated['order'] = 0;
        }

        City::create($validated);
        return redirect()->route('admin.cities.index');
            // ->with('success', 'City created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        abort(404); // Not typically needed for admin CRUD
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(City $city): View
    {
        $countries = Country::where('active', true)->orderBy('name')->pluck('name', 'id');
        return view('admin.cities.edit', compact('city', 'countries'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, City $city): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);
        $validated['active'] = $request->has('active');
        $city->update($validated);
        return redirect()->route('admin.cities.index');
            // ->with('success', 'City updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city): RedirectResponse
    {
        $city->delete();
        return redirect()->route('admin.cities.index');
            // ->with('success', 'City deleted successfully.');
    }

    /**
     * Update the order of cities via AJAX.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'cities' => 'required|array',
            'cities.*.id' => 'required|exists:cities,id',
            'cities.*.order' => 'required|integer|min:0'
        ]);

        foreach ($request->cities as $cityData) {
            City::where('id', $cityData['id'])->update(['order' => $cityData['order']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Filter cities by country via AJAX.
     */
    public function filter(Request $request)
    {
        $countryId = $request->query('country_id');

        $query = City::with('country')->orderBy('order')->orderBy('name');
        if (!empty($countryId)) {
            $query->where('country_id', $countryId);
        }

        $cities = $query->paginate(20);

        $rowsHtml = view('admin.cities._index_rows', compact('cities'))->render();
        $paginationHtml = view('admin.cities._pagination', compact('cities'))->render();
        return response()->json(['rowsHtml' => $rowsHtml, 'paginationHtml' => $paginationHtml]);
    }
}

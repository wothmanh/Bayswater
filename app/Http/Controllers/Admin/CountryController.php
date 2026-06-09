<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country; // Import Country model
use Illuminate\Http\Request;
use Illuminate\View\View; // Import View
use Illuminate\Http\RedirectResponse; // Import RedirectResponse

class CountryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View // Add return type hint
    {
        $countries = Country::orderBy('order')->orderBy('name')->paginate(20); // Get paginated countries
        return view('admin.countries.index', compact('countries')); // Pass data to the view
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View // Add return type hint
    {
        return view('admin.countries.create'); // Return the create view
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse // Add return type hint
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:countries,name|max:255',
            'iso_code_2' => 'nullable|string|unique:countries,iso_code_2|size:2',
            'iso_code_3' => 'nullable|string|unique:countries,iso_code_3|size:3',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        // Handle checkbox value
        $validated['active'] = $request->has('active');

        // Set default order if not provided
        if (!$request->has('order')) {
            $validated['order'] = 0;
        }

        Country::create($validated);

        // TODO: Add flash message for success
        return redirect()->route('admin.countries.index');
            // ->with('success', 'Country created successfully.');
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
    public function edit(Country $country): View // Use route model binding & add return type hint
    {
        return view('admin.countries.edit', compact('country')); // Pass the country to the edit view
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Country $country): RedirectResponse // Use route model binding & add return type hint
    {
        $validated = $request->validate([
            // Ignore current country's name for unique check
            'name' => 'required|string|unique:countries,name,'.$country->id.'|max:255',
            'iso_code_2' => 'nullable|string|unique:countries,iso_code_2,'.$country->id.'|size:2',
            'iso_code_3' => 'nullable|string|unique:countries,iso_code_3,'.$country->id.'|size:3',
            'active' => 'nullable|boolean',
            'order' => 'nullable|integer|min:0',
        ]);

        // Handle checkbox value
        $validated['active'] = $request->has('active');

        $country->update($validated);

        // TODO: Add flash message for success
        return redirect()->route('admin.countries.index');
            // ->with('success', 'Country updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country): RedirectResponse // Use route model binding & add return type hint
    {
        // Consider implications: Deleting a country might cascade delete cities, schools, etc.
        // Add checks here if needed, or rely on database constraints / soft deletes.

        $country->delete();

        // TODO: Add flash message for success
        return redirect()->route('admin.countries.index');
            // ->with('success', 'Country deleted successfully.');
    }

    /**
     * Update the order of countries via AJAX.
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'countries' => 'required|array',
            'countries.*.id' => 'required|exists:countries,id',
            'countries.*.order' => 'required|integer|min:0'
        ]);

        foreach ($request->countries as $countryData) {
            Country::where('id', $countryData['id'])->update(['order' => $countryData['order']]);
        }

        return response()->json(['success' => true]);
    }
}

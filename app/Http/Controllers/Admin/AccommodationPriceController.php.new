<?php

namespace App\Http\Controllers\Admin;

use App\Models\AccommodationPrice;
use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\View\View; // Import View
use Illuminate\Http\RedirectResponse; // Import RedirectResponse

class AccommodationPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Accommodation $accommodation) // Not typically used directly
    {
         abort(404); // Redirect to parent edit page instead
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Accommodation $accommodation): View
    {
        return view('admin.accommodation_prices.create', compact('accommodation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Accommodation $accommodation): RedirectResponse
    {
        $validated = $request->validate([
            'min_weeks' => 'required|integer|min:1',
            'max_weeks' => 'required|integer|min:1|gte:min_weeks',
            'price_per_week' => 'required|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $accommodation->accommodationPrices()->create($validated);
        return redirect()->route('admin.accommodations.edit', $accommodation);
            // ->with('success', 'Accommodation price range created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AccommodationPrice $accommodation_price) 
    {
         abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccommodationPrice $accommodation_price): View
    {
        // Get the parent accommodation from the price relationship
        $accommodation = $accommodation_price->accommodation;
        return view('admin.accommodation_prices.edit', compact('accommodation_price', 'accommodation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccommodationPrice $accommodation_price): RedirectResponse
    {
        $validated = $request->validate([
            'min_weeks' => 'required|integer|min:1',
            'max_weeks' => 'required|integer|min:1|gte:min_weeks',
            'price_per_week' => 'required|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $accommodation_price->update($validated);
        // Redirect back to the parent accommodation's edit page
        return redirect()->route('admin.accommodations.edit', $accommodation_price->accommodation);
            // ->with('success', 'Accommodation price range updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccommodationPrice $accommodation_price): RedirectResponse
    {
        $accommodation = $accommodation_price->accommodation;
        $accommodation_price->delete();
        // Redirect back to the parent accommodation's edit page
        return redirect()->route('admin.accommodations.edit', $accommodation);
            // ->with('success', 'Accommodation price range deleted successfully.');
    }
}

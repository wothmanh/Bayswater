<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule; // Import Rule

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $currencies = Currency::orderBy('name')->paginate(20);
        return view('admin.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.currencies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:currencies,code|size:3',
            'symbol' => 'nullable|string|max:5',
            'sar_price' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        Currency::create($validated);
        return redirect()->route('admin.currencies.index');
            // ->with('success', 'Currency created successfully.');
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
    public function edit(Currency $currency): View
    {
        return view('admin.currencies.edit', compact('currency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Currency $currency): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:currencies,code,'.$currency->id.'|size:3',
            'symbol' => 'nullable|string|max:5',
            'sar_price' => 'nullable|numeric|min:0',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $currency->update($validated);
        return redirect()->route('admin.currencies.index');
            // ->with('success', 'Currency updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency): RedirectResponse
    {
        // Consider checking if currency is used by schools before deleting
        // if ($currency->schools()->exists()) { ... }
        $currency->delete();
        return redirect()->route('admin.currencies.index');
            // ->with('success', 'Currency deleted successfully.');
    }
}

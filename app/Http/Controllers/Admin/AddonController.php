<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\School; // Import School model
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;

class AddonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $addons = Addon::with('school')->orderBy('name')->paginate(20);
        return view('admin.addons.index', compact('addons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $priceTypes = ['one_time' => 'One Time', 'per_week' => 'Per Week'];
        return view('admin.addons.create', compact('schools', 'priceTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'school_id' => 'nullable|exists:schools,id',
            'price' => 'required|numeric|min:0',
            'price_type' => ['required', Rule::in(['one_time', 'per_week'])],
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        Addon::create($validated);
        return redirect()->route('admin.addons.index');
            // ->with('success', 'Addon created successfully.');
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
    public function edit(Addon $addon): View
    {
        $schools = School::where('active', true)->orderBy('name')->pluck('name', 'id');
        $priceTypes = ['one_time' => 'One Time', 'per_week' => 'Per Week'];
        return view('admin.addons.edit', compact('addon', 'schools', 'priceTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Addon $addon): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'school_id' => 'nullable|exists:schools,id',
            'price' => 'required|numeric|min:0',
            'price_type' => ['required', Rule::in(['one_time', 'per_week'])],
            'description' => 'nullable|string',
            'active' => 'nullable|boolean',
        ]);
        $validated['active'] = $request->has('active');
        $validated['school_id'] = $validated['school_id'] ?: null; // Ensure null if empty
        $addon->update($validated);
        return redirect()->route('admin.addons.index');
            // ->with('success', 'Addon updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Addon $addon): RedirectResponse
    {
        $addon->delete();
        return redirect()->route('admin.addons.index');
            // ->with('success', 'Addon deleted successfully.');
    }
}

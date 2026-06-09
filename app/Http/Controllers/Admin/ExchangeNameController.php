<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeName;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExchangeNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $exchangeNames = ExchangeName::with('regions')->orderBy('order')->orderBy('name')->paginate(10);
        return view('admin.exchange-names.index', compact('exchangeNames'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.exchange-names.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:10'],
            'label' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['name'] = strtoupper(trim($validated['name']));

        ExchangeName::create($validated);

        return redirect()->route('admin.exchange-names.index')
            ->with('success', 'Exchange currency added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ExchangeName $exchangeName): View
    {
        $regions = Region::orderBy('order')->orderBy('name')->get();
        return view('admin.exchange-names.edit', compact('exchangeName', 'regions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ExchangeName $exchangeName): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:10'],
            'label' => ['nullable', 'string', 'max:100'],
            'regions' => ['nullable', 'array'],
            'regions.*' => ['exists:regions,id'],
        ]);

        $validated['name'] = strtoupper(trim($validated['name']));

        $exchangeName->update($validated);

        // Sync regions
        $exchangeName->regions()->sync($request->input('regions', []));

        return redirect()->route('admin.exchange-names.index')
            ->with('success', 'Exchange currency updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ExchangeName $exchangeName): RedirectResponse
    {
        $exchangeName->delete();

        return redirect()->route('admin.exchange-names.index')
            ->with('success', 'Exchange currency deleted successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'exchange_names' => 'required|array',
            'exchange_names.*.id' => 'required|integer|exists:exchange_names,id',
            'exchange_names.*.order' => 'required|integer',
        ]);

        foreach ($validated['exchange_names'] as $item) {
            ExchangeName::where('id', $item['id'])->update(['order' => $item['order']]);
        }

        return response()->json(['status' => 'success']);
    }
}
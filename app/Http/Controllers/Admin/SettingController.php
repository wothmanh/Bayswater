<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\MarketDiscount;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Redirect to edit page since we only have one settings record
        return $this->edit();
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not needed as we only have one settings record
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Not needed as we only have one settings record
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Not needed as we only have one settings record
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(): View
    {
        // Get or create settings
        $settings = Setting::first() ?: new Setting();

        $activeSection = 'system';

        return view('admin.settings.edit', compact('settings', 'activeSection'));
    }

    public function marketDiscountEdit(): View
    {
        $marketDiscounts = MarketDiscount::with('regions')->get();
        $regions = Region::orderBy('order')->orderBy('name')->get();

        return view('admin.market-discount.edit', compact('marketDiscounts', 'regions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request): RedirectResponse
    {
        // Handle Market Discount Update separate from System Settings
        if ($request->input('_settings_section') === 'market-discount') {
            $validated = $request->validate([
                'discounts' => 'nullable|array',
                'discounts.*.id' => 'nullable|exists:market_discounts,id',
                'discounts.*.title' => 'required|string|max:255',
                'discounts.*.iframe_url' => 'required|url|max:2048',
                'discounts.*.region_ids' => 'nullable|array',
                'discounts.*.region_ids.*' => 'exists:regions,id',
            ]);

            $incomingDiscounts = $request->input('discounts', []);
            $incomingIds = array_filter(array_column($incomingDiscounts, 'id'));

            // Delete removed discounts
            MarketDiscount::whereNotIn('id', $incomingIds)->delete();

            foreach ($incomingDiscounts as $discountData) {
                $discount = MarketDiscount::updateOrCreate(
                    ['id' => $discountData['id'] ?? null],
                    [
                        'title' => $discountData['title'],
                        'iframe_url' => $discountData['iframe_url'],
                    ]
                );

                $discount->regions()->sync($discountData['region_ids'] ?? []);
            }

            return redirect()->route('admin.market-discount.edit')
                ->with('success', 'Market Discount settings updated successfully');
        }

        // Validate the request
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'cutoff_date' => 'nullable|date',
            'quotation_extraction_date' => 'nullable|date',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|file|mimes:ico,png,jpg,svg|max:512',
            // WhatsApp chat fields
            'whatsapp_number' => 'nullable|string|max:30',
            'whatsapp_default_message' => 'nullable|string|max:255',
            // Search Accommodation fields
            'search_accommodation_tab_title' => 'nullable|string|max:255',
            'search_accommodation_page_link' => 'nullable|url|max:2048',
            // Partner Zone fields
            'partner_zone_tab_title' => 'nullable|string|max:255',
            'partner_zone_page_link' => 'nullable|url|max:2048',
        ]);

        // Get or create settings
        $settings = Setting::first() ?: new Setting();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            // Store new logo
            $logoPath = $request->file('logo')->store('logos', 'public');
            $settings->logo_path = $logoPath;
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            // Delete old favicon if exists
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }

            // Store new favicon
            $faviconPath = $request->file('favicon')->store('favicons', 'public');
            $settings->favicon_path = $faviconPath;
        }

        // Update other settings
        $settings->company_name = $validated['company_name'] ?? $settings->company_name;
        $settings->company_email = $validated['company_email'] ?? $settings->company_email;
        $settings->company_phone = $validated['company_phone'] ?? $settings->company_phone;
        $settings->company_address = $validated['company_address'] ?? $settings->company_address;
        $settings->cutoff_date = $validated['cutoff_date'] ?? $settings->cutoff_date;
        // Allow clearing the quotation extraction date override by setting it to null when input is empty
        if ($request->filled('quotation_extraction_date')) {
            $settings->quotation_extraction_date = $validated['quotation_extraction_date'];
        } elseif ($request->has('quotation_extraction_date')) {
            // Field is present but empty: clear the override
            $settings->quotation_extraction_date = null;
        }

        // Update WhatsApp chat fields with clearing when empty
        if ($request->filled('whatsapp_number')) {
            $settings->whatsapp_number = $validated['whatsapp_number'];
        } elseif ($request->has('whatsapp_number')) {
            $settings->whatsapp_number = null;
        }

        if ($request->filled('whatsapp_default_message')) {
            $settings->whatsapp_default_message = $validated['whatsapp_default_message'];
        } elseif ($request->has('whatsapp_default_message')) {
            $settings->whatsapp_default_message = null;
        }

        // Update Search Accommodation fields with clearing when empty
        if ($request->filled('search_accommodation_tab_title')) {
            $settings->search_accommodation_tab_title = $validated['search_accommodation_tab_title'];
        } elseif ($request->has('search_accommodation_tab_title')) {
            $settings->search_accommodation_tab_title = null;
        }

        if ($request->filled('search_accommodation_page_link')) {
            $settings->search_accommodation_page_link = $validated['search_accommodation_page_link'];
        } elseif ($request->has('search_accommodation_page_link')) {
            $settings->search_accommodation_page_link = null;
        }

        // Update Partner Zone fields with clearing when empty
        if ($request->filled('partner_zone_tab_title')) {
            $settings->partner_zone_tab_title = $validated['partner_zone_tab_title'];
        } elseif ($request->has('partner_zone_tab_title')) {
            $settings->partner_zone_tab_title = null;
        }

        if ($request->filled('partner_zone_page_link')) {
            $settings->partner_zone_page_link = $validated['partner_zone_page_link'];
        } elseif ($request->has('partner_zone_page_link')) {
            $settings->partner_zone_page_link = null;
        }

        // Save settings
        $settings->save();

        $redirectRoute = $request->input('_settings_section') === 'market-discount'
            ? 'admin.market-discount.edit'
            : 'admin.settings.edit';

        return redirect()->route($redirectRoute)
            ->with('success', 'Settings updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Not needed as we only have one settings record
        abort(404);
    }

    /**
     * Remove the logo.
     */
    public function removeLogo(): RedirectResponse
    {
        $settings = Setting::first();

        if ($settings && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $settings->logo_path = null;
            $settings->save();
        }

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Logo removed successfully');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MarketDiscount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MarketDiscountController extends Controller
{
    /**
     * Redirect to the first available Market Discount.
     */
    public function index(): Response|RedirectResponse
    {
        $discounts = MarketDiscount::with('regions')->get();
        
        foreach ($discounts as $discount) {
            if ($this->checkAccess($discount)) {
                return redirect()->route('market-discount.show', $discount);
            }
        }

        abort(404);
    }

    /**
     * Display the Market Discount iframe page.
     */
    public function show(MarketDiscount $marketDiscount): Response|RedirectResponse
    {
        if (!$this->checkAccess($marketDiscount)) {
            abort(403, 'Unauthorized access to this Market Discount tab.');
        }

        return response()->view('market_discount.index', [
            'tabTitle' => $marketDiscount->title,
            'iframeUrl' => $marketDiscount->iframe_url,
        ])->header('Content-Security-Policy', "frame-src 'self' https://cdn.bfldr.com https://*.bfldr.com https://*.brandfolder.com data: blob:;")
          ->header('X-Frame-Options', 'SAMEORIGIN');
    }

    /**
     * Local pdf.js viewer fallback route.
     * Note: This likely needs to support ID as well if used per tab.
     * For now, we'll assume it takes an ID or we leave it as legacy for the 'first' one if needed, 
     * but strictly speaking it should be viewer/{marketDiscount}.
     */
    public function viewer(MarketDiscount $marketDiscount = null): Response|RedirectResponse
    {
        if (!$marketDiscount) {
             $marketDiscount = MarketDiscount::first();
        }
        
        if (!$marketDiscount || empty($marketDiscount->iframe_url)) {
            abort(404);
        }

        if (!$this->checkAccess($marketDiscount)) {
            abort(403, 'Unauthorized access to Market Discount.');
        }

        return response()->view('market_discount.pdfjs_viewer', [
            'fileUrl' => $marketDiscount->iframe_url,
        ])->header('Content-Security-Policy', "default-src 'self'; script-src 'self' https://cdnjs.cloudflare.com 'unsafe-inline'; style-src 'self' 'unsafe-inline'; connect-src 'self' https://cdn.bfldr.com https://*.bfldr.com https://*.brandfolder.com; img-src 'self' data: blob: https:; font-src 'self' data:; object-src 'none'; frame-ancestors 'self';")
          ->header('X-Frame-Options', 'SAMEORIGIN');
    }
    
    /**
     * Check if user can access the discount.
     */
    private function checkAccess(MarketDiscount $discount): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if ($user->isAdmin()) {
            return true;
        }

        // Secure by default: if no regions assigned, nobody (except admin) sees it
        if ($discount->regions->isEmpty()) {
            return false;
        }

        $userRegionIds = $user->regions->pluck('id');
        $discountRegionIds = $discount->regions->pluck('id');

        return $userRegionIds->intersect($discountRegionIds)->isNotEmpty();
    }
}

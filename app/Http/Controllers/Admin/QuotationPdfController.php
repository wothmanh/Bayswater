<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\FeeCalculatorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class QuotationPdfController extends Controller
{
    /**
     * Generate a PDF from the quotation data
     *
     * @param Request $request
     * @param FeeCalculatorService $calculator
     * @return Response
     */
    public function generatePdf(Request $request, FeeCalculatorService $calculator)
    {
        // Validate the request
        $validatedData = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'region_id' => 'required|exists:regions,id',
            'course_id' => 'required|exists:courses,id',
            'course_start_date' => 'required|date',
            'course_duration_weeks' => 'required|integer|min:1',
            // Nationality discounts
            'nationality_discounts' => 'sometimes|array',
            'nationality_discounts.*' => 'integer|exists:discount_rules,id',
            // Second course validation
            'second_course_id' => 'nullable|exists:courses,id',
            'second_course_start_date' => 'nullable|date',
            'second_course_duration_weeks' => 'nullable|integer|min:1',
            'accommodation_id' => 'nullable|exists:accommodations,id',
            'accommodation_duration_weeks' => [
                'nullable',
                'required_with:accommodation_id',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    // Calculate total combined course duration
                    $firstCourseDuration = (int) $request->input('course_duration_weeks', 0);
                    $secondCourseDuration = (int) $request->input('second_course_duration_weeks', 0);
                    $totalCourseDuration = $firstCourseDuration + $secondCourseDuration;
                    
                    if ($value > $totalCourseDuration) {
                        $fail('Accommodation duration cannot exceed course duration.');
                    }
                }
            ],
            // Second accommodation validation
            'second_accommodation_id' => 'nullable|exists:accommodations,id',
            'second_accommodation_duration_weeks' => 'nullable|integer|min:1',
            'client_birthday' => 'nullable|date',
            'client_nationality_country_id' => 'nullable|exists:countries,id',
            'selected_addons' => 'nullable|array',
            'selected_addons.*' => 'sometimes|boolean',
            'arrival_transfer_airport_id' => 'nullable|exists:airports,id', // Add validation
            'departure_transfer_airport_id' => 'nullable|exists:airports,id', // Add validation
            'private_bathroom' => 'nullable|boolean',
            'dietary_supplement' => 'nullable|boolean',
            'second_private_bathroom' => 'nullable|boolean',
            'second_dietary_supplement' => 'nullable|boolean',
            // Christmas accommodation parameters
            'christmas_accommodation' => 'nullable|string|in:yes,no',
            'christmas_extra_weeks' => 'nullable|integer|min:1',
            'christmas_start_date' => 'nullable|date',
            'christmas_end_date' => 'nullable|date',
            'second_christmas_accommodation' => 'nullable|string|in:true,false',
            'second_christmas_extra_weeks' => 'nullable|integer|min:1',
            'insurance_selected' => 'nullable|boolean',
        ]);

        // Prepare parameters for the service
        $quoteParams = $validatedData;

        // Ensure accommodation duration is null if accommodation_id is null
        if (empty($validatedData['accommodation_id'])) {
            $quoteParams['accommodation_duration_weeks'] = null;
        }

        // Handle Christmas accommodation if provided
        if (!empty($validatedData['accommodation_id']) && isset($request->christmas_accommodation)) {
            if ($request->christmas_accommodation === 'yes') {
                $quoteParams['christmas_accommodation'] = true;

                // Add Christmas extra weeks if provided
                if (isset($request->christmas_extra_weeks)) {
                    $quoteParams['christmas_extra_weeks'] = (int) $request->christmas_extra_weeks;
                }

                // Add Christmas dates if provided
                if (isset($request->christmas_start_date) && isset($request->christmas_end_date)) {
                    $quoteParams['christmas_start_date'] = $request->christmas_start_date;
                    $quoteParams['christmas_end_date'] = $request->christmas_end_date;
                }
            }
        }

        // Handle second Christmas accommodation if provided
        if (!empty($validatedData['second_accommodation_id']) && isset($request->second_christmas_accommodation)) {
            if ($request->second_christmas_accommodation === 'true') {
                $quoteParams['second_christmas_accommodation'] = true;

                // Add second Christmas extra weeks if provided
                if (isset($request->second_christmas_extra_weeks)) {
                    $quoteParams['second_christmas_extra_weeks'] = (int) $request->second_christmas_extra_weeks;
                }
            }
        }

        // Handle Christmas accommodation if provided
        if (!empty($validatedData['accommodation_id']) && isset($request->christmas_accommodation)) {
            if ($request->christmas_accommodation === 'yes') {
                $quoteParams['christmas_accommodation'] = true;

                // Add Christmas extra weeks if provided
                if (isset($request->christmas_extra_weeks)) {
                    $quoteParams['christmas_extra_weeks'] = (int) $request->christmas_extra_weeks;
                }

                // Add Christmas dates if provided
                if (isset($request->christmas_start_date) && isset($request->christmas_end_date)) {
                    $quoteParams['christmas_start_date'] = $request->christmas_start_date;
                    $quoteParams['christmas_end_date'] = $request->christmas_end_date;
                }
            }
        }

        // Handle second Christmas accommodation if provided
        if (!empty($validatedData['second_accommodation_id']) && isset($request->second_christmas_accommodation)) {
            if ($request->second_christmas_accommodation === 'true') {
                $quoteParams['second_christmas_accommodation'] = true;

                // Add second Christmas extra weeks if provided
                if (isset($request->second_christmas_extra_weeks)) {
                    $quoteParams['second_christmas_extra_weeks'] = (int) $request->second_christmas_extra_weeks;
                }
            }
        }

        // Format selected_addons if necessary
        if (isset($quoteParams['selected_addons'])) {
            $formattedAddons = [];
            foreach ($quoteParams['selected_addons'] as $id => $value) {
                if ($value) {
                    $formattedAddons[$id] = true;
                }
            }
            $quoteParams['selected_addons'] = $formattedAddons;
        }

        // Explicitly add airport transfer IDs if they exist in validated data
        if (isset($validatedData['arrival_transfer_airport_id'])) {
            $quoteParams['arrival_transfer_airport_id'] = $validatedData['arrival_transfer_airport_id'];
        }
        if (isset($validatedData['departure_transfer_airport_id'])) {
            $quoteParams['departure_transfer_airport_id'] = $validatedData['departure_transfer_airport_id'];
        }

        // Add private bathroom and dietary supplement options with correct parameter names
        if (isset($validatedData['private_bathroom']) && $validatedData['private_bathroom']) {
            $quoteParams['private_bathroom_option'] = true;
        }
        if (isset($validatedData['dietary_supplement']) && $validatedData['dietary_supplement']) {
            $quoteParams['dietary_supplement_option'] = true;
        }

        // Add nationality discounts if selected
        if ($request->has('nationality_discounts') && is_array($request->input('nationality_discounts'))) {
            $quoteParams['nationality_discounts'] = $request->input('nationality_discounts');
        } else {
            $quoteParams['nationality_discounts'] = [];
        }
        
        // Add second accommodation add-on options
        if (isset($validatedData['second_private_bathroom']) && $validatedData['second_private_bathroom']) {
            $quoteParams['second_private_bathroom_option'] = true;
        }
        if (isset($validatedData['second_dietary_supplement']) && $validatedData['second_dietary_supplement']) {
            $quoteParams['second_dietary_supplement_option'] = true;
        }

        // Add insurance option
        if (isset($validatedData['insurance_selected']) && $validatedData['insurance_selected']) {
            $quoteParams['insurance_option'] = true;
        }

        // Add insurance option
        if (isset($validatedData['insurance']) && $validatedData['insurance']) {
            $quoteParams['insurance_option'] = true;
        }

        // Calculate the quote
        \Illuminate\Support\Facades\Log::info('PDF Generation - Calculating quote with params:', $quoteParams); // Log params for PDF
        $costBreakdown = $calculator->calculateQuote($quoteParams);

        // Add course start date to the cost breakdown for display
        $costBreakdown['course_start_date'] = $validatedData['course_start_date'];

        // Get settings for company info and logo
        $settings = Setting::first();

        // Render the printable HTML and rewrite asset URLs to local file paths
        $html = view('admin.quotations.print', [
            'costBreakdown' => $costBreakdown,
            'settings' => $settings
        ])->render();

        // Hide printable-only controls in downloadable PDF
        $html = preg_replace('#</head>#i', '<style>.print-controls{display:none !important;}</style></head>', $html, 1);

        // Helper to build data URI for an image file
        $toDataUri = function(string $filePath): ?string {
            if (!is_file($filePath)) return null;
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $map = ['jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png', 'gif' => 'gif', 'svg' => 'svg+xml', 'webp' => 'webp'];
            $mime = 'image/' . ($map[$ext] ?? $ext);
            $data = @file_get_contents($filePath);
            if ($data === false) return null;
            return 'data:' . $mime . ';base64,' . base64_encode($data);
        };

        // Embed main Bayswater logo directly from Laravel storage.
        if ($settings && !empty($settings->logo_path)) {
            $mainLogoFile = storage_path('app/public/' . $settings->logo_path);
            if ($dataUri = $toDataUri($mainLogoFile)) {
                $pattern = '#src=["\'](?:https?://[^"\']+)?/storage/' . preg_quote($settings->logo_path, '#') . '["\']#i';
                $html = preg_replace($pattern, 'src="' . $dataUri . '"', $html, 1);
            }
        }

        // Embed the private agent logo for PDF output without requiring a public storage link.
        $agent = \Auth::check() && \Auth::user()->isAgent() ? \Auth::user()->agentSetting : null;
        if ($agent && !empty($agent->brand_logo_path)) {
            $agentLogoFile = storage_path('app/public/' . $agent->brand_logo_path);
            if ($dataUri = $toDataUri($agentLogoFile)) {
                $pattern = '#src=["\']' . preg_quote($agent->brandLogoUrl(), '#') . '["\']#i';
                $html = preg_replace($pattern, 'src="' . $dataUri . '"', $html, 1);
            }
        }

        // Convert remaining /storage and /images asset URLs to absolute local file paths (no scheme)
        $publicPath = public_path();
        $publicPathUnix = str_replace('\\', '/', $publicPath);
        $storagePathUnix = $publicPathUnix . '/storage';
        $imagesPathUnix = $publicPathUnix . '/images';
        $patterns = [
            '#src=["\'](?:https?://[^"\']+)?/storage/([^"\']+)["\']#i' => 'src="' . $storagePathUnix . '/$1"',
            '#href=["\'](?:https?://[^"\']+)?/storage/([^"\']+)["\']#i' => 'href="' . $storagePathUnix . '/$1"',
            '#src=["\'](?:https?://[^"\']+)?/images/([^"\']+)["\']#i' => 'src="' . $imagesPathUnix . '/$1"',
            '#href=["\'](?:https?://[^"\']+)?/images/([^"\']+)["\']#i' => 'href="' . $imagesPathUnix . '/$1"',
        ];
        foreach ($patterns as $pattern => $replacement) {
            $html = preg_replace($pattern, $replacement, $html);
        }

        // Load the transformed HTML into DomPDF
        $pdf = PDF::loadHTML($html);
        $pdf->setBasePath($publicPath);
        $pdf->setOptions(['isRemoteEnabled' => false]); // prevent remote HTTP during render

        // Set PDF options
        $pdf->setPaper('a4');

        // Return the PDF as a download
        return $pdf->download('quotation-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Print the quotation
     *
     * @param Request $request
     * @param FeeCalculatorService $calculator
     * @return Response
     */
    public function printQuotation(Request $request, FeeCalculatorService $calculator)
    {
        // Validate the request
        $validatedData = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'region_id' => 'required|exists:regions,id',
            'course_id' => 'required|exists:courses,id',
            'course_start_date' => 'required|date',
            'course_duration_weeks' => 'required|integer|min:1',
            // Nationality discounts
            'nationality_discounts' => 'sometimes|array',
            'nationality_discounts.*' => 'integer|exists:discount_rules,id',
            // Second course validation
            'second_course_id' => 'nullable|exists:courses,id',
            'second_course_start_date' => 'nullable|date',
            'second_course_duration_weeks' => 'nullable|integer|min:1',
            'accommodation_id' => 'nullable|exists:accommodations,id',
            'accommodation_duration_weeks' => [
                'nullable',
                'required_with:accommodation_id',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    // Calculate total combined course duration
                    $firstCourseDuration = (int) $request->input('course_duration_weeks', 0);
                    $secondCourseDuration = (int) $request->input('second_course_duration_weeks', 0);
                    $totalCourseDuration = $firstCourseDuration + $secondCourseDuration;
                    
                    if ($value > $totalCourseDuration) {
                        $fail('Accommodation duration cannot exceed course duration.');
                    }
                }
            ],
            // Second accommodation validation
            'second_accommodation_id' => 'nullable|exists:accommodations,id',
            'second_accommodation_duration_weeks' => 'nullable|integer|min:1',
            'client_birthday' => 'nullable|date',
            'client_nationality_country_id' => 'nullable|exists:countries,id',
            'selected_addons' => 'nullable|array',
            'selected_addons.*' => 'sometimes|boolean',
            'arrival_transfer_airport_id' => 'nullable|exists:airports,id', // Add validation
            'departure_transfer_airport_id' => 'nullable|exists:airports,id', // Add validation
            'private_bathroom' => 'nullable|boolean',
            'dietary_supplement' => 'nullable|boolean',
            'second_private_bathroom' => 'nullable|boolean',
            'second_dietary_supplement' => 'nullable|boolean',
            // Christmas accommodation parameters
            'christmas_accommodation' => 'nullable|string|in:yes,no',
            'christmas_extra_weeks' => 'nullable|integer|min:1',
            'christmas_start_date' => 'nullable|date',
            'christmas_end_date' => 'nullable|date',
            'second_christmas_accommodation' => 'nullable|string|in:true,false',
            'second_christmas_extra_weeks' => 'nullable|integer|min:1',
            'insurance_selected' => 'nullable|boolean',
        ]);

        // Prepare parameters for the service
        $quoteParams = $validatedData;

        // Ensure accommodation duration is null if accommodation_id is null
        if (empty($validatedData['accommodation_id'])) {
            $quoteParams['accommodation_duration_weeks'] = null;
        }

        // Handle Christmas accommodation if provided
        if (!empty($validatedData['accommodation_id']) && isset($request->christmas_accommodation)) {
            if ($request->christmas_accommodation === 'yes') {
                $quoteParams['christmas_accommodation'] = true;

                // Add Christmas extra weeks if provided
                if (isset($request->christmas_extra_weeks)) {
                    $quoteParams['christmas_extra_weeks'] = (int) $request->christmas_extra_weeks;
                }

                // Add Christmas dates if provided
                if (isset($request->christmas_start_date) && isset($request->christmas_end_date)) {
                    $quoteParams['christmas_start_date'] = $request->christmas_start_date;
                    $quoteParams['christmas_end_date'] = $request->christmas_end_date;
                }
            }
        }

        // Format selected_addons if necessary
        if (isset($quoteParams['selected_addons'])) {
            $formattedAddons = [];
            foreach ($quoteParams['selected_addons'] as $id => $value) {
                if ($value) {
                    $formattedAddons[$id] = true;
                }
            }
            $quoteParams['selected_addons'] = $formattedAddons;
        }

        // Explicitly add airport transfer IDs if they exist in validated data
        if (isset($validatedData['arrival_transfer_airport_id'])) {
            $quoteParams['arrival_transfer_airport_id'] = $validatedData['arrival_transfer_airport_id'];
        }
        if (isset($validatedData['departure_transfer_airport_id'])) {
            $quoteParams['departure_transfer_airport_id'] = $validatedData['departure_transfer_airport_id'];
        }

        // Add private bathroom and dietary supplement options with correct parameter names
        if (isset($validatedData['private_bathroom']) && $validatedData['private_bathroom']) {
            $quoteParams['private_bathroom_option'] = true;
        }
        if (isset($validatedData['dietary_supplement']) && $validatedData['dietary_supplement']) {
            $quoteParams['dietary_supplement_option'] = true;
        }

        // Add nationality discounts if selected
        if ($request->has('nationality_discounts') && is_array($request->input('nationality_discounts'))) {
            $quoteParams['nationality_discounts'] = $request->input('nationality_discounts');
        } else {
            $quoteParams['nationality_discounts'] = [];
        }
        
        // Add second accommodation add-on options
        if (isset($validatedData['second_private_bathroom']) && $validatedData['second_private_bathroom']) {
            $quoteParams['second_private_bathroom_option'] = true;
        }
        if (isset($validatedData['second_dietary_supplement']) && $validatedData['second_dietary_supplement']) {
            $quoteParams['second_dietary_supplement_option'] = true;
        }

        // Add insurance option
        if (isset($validatedData['insurance_selected']) && $validatedData['insurance_selected']) {
            $quoteParams['insurance_option'] = true;
        }

        // Calculate the quote
        \Illuminate\Support\Facades\Log::info('Print Quotation - Calculating quote with params:', $quoteParams); // Log params for Print
        $costBreakdown = $calculator->calculateQuote($quoteParams);

        // Add course start date to the cost breakdown for display
        $costBreakdown['course_start_date'] = $validatedData['course_start_date'];

        // Get settings for company info and logo
        $settings = Setting::first();

        // Return the view for printing
        return view('admin.quotations.print', [
            'costBreakdown' => $costBreakdown,
            'settings' => $settings
        ]);
    }
}

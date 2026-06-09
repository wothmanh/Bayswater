<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote - {{ now()->format('Y-m-d') }}</title>
    
    <!-- Favicon -->
    @php
        $settings = \App\Models\Setting::first();
    @endphp
    @if($settings && $settings->favicon_path)
        <link rel="icon" href="{{ asset('storage/' . $settings->favicon_path) }}" type="image/x-icon">
    @else
        <link rel="icon" href="{{ asset('images/bayswater-leaf-icon.svg') }}" type="image/svg+xml">
    @endif
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #003366;
            padding-bottom: 20px;
        }
        .logo {
            max-width: 200px;
            height: auto;
        }
        .quote-info {
            text-align: right;
        }
        .agent-logo-container {
            margin: 0;
            padding: 0;
        }
        .quote-date {
            font-size: 14px;
            color: #666;
        }
        .quote-number {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
        }
        .section-title {
            background-color: #003366;
            color: white;
            padding: 10px 15px;
            font-size: 18px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .item-name {
            flex: 2;
        }
        .item-price {
            flex: 1;
            text-align: right;
            font-weight: bold;
        }
        .item-details {
            font-size: 12px;
            color: #555;
            margin-bottom: 15px;
            padding-left: 10px;
        }
        .subtotal {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-weight: bold;
            border-top: 1px solid #ccc;
            margin-top: 15px;
        }
        .total {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            font-weight: bold;
            font-size: 20px;
            border-top: 2px solid #003366;
            margin-top: 15px;
            color: #003366;
        }
        .discount {
            color: #28a745;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .terms {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .print-controls {
            text-align: center;
            margin: 20px 0;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .print-controls button {
            padding: 10px 20px;
            background-color: #003366;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-controls button:hover {
            background-color: #002244;
        }
        @media print {
            .print-controls {
                display: none;
            }
        }
    </style>
</head>
<body>
    @php
        $exchangeEnabled = request('exchange_enabled') === '1';
        $exchangeRate = floatval(request('exchange_rate'));
        $exchangeCurrency = request('exchange_target_currency');
        $symbolToCode = ['£' => 'GBP', '€' => 'EUR', '$' => 'USD', '₺' => 'TRY'];
        $baseSymbol = $costBreakdown['currency_symbol'] ?? '';
        $baseCode = $symbolToCode[$baseSymbol] ?? '';
        $formatCurrency = function($amount) use ($exchangeEnabled, $exchangeRate, $exchangeCurrency, $baseSymbol, $baseCode) {
            $val = floatval($amount);
            if ($exchangeEnabled && $exchangeRate > 0) {
                return $exchangeCurrency . ' ' . number_format($val * $exchangeRate, 2);
            }
            return ($baseCode ? $baseCode . ' ' : '') . $baseSymbol . number_format($val, 2);
        };
    @endphp
    <div class="print-controls">
        <button onclick="window.print()">Print Now</button>
        <button onclick="window.close()">Close</button>
    </div>

    <div class="container">
        <div class="header">
            <div>
                @if($settings && $settings->logo_path)
                    <img src="{{ asset('storage/' . $settings->logo_path) }}" alt="Bayswater Logo" class="logo">
                @else
                    <h1 style="color: #003366;">Bayswater Education</h1>
                @endif
            </div>
            <div class="quote-info">
                @if(Auth::check() && Auth::user()->isAgent())
                    @php
                        $agent = Auth::user()->agentSetting;
                    @endphp
                    @if($agent && $agent->brand_logo_path)
                        <div style="text-align: right;">
                            <div class="agent-logo-container">
                                <img src="{{ $agent->brandLogoUrl() }}" alt="Agent Brand Logo" style="max-height:60px; width:auto; display:block; margin-left:auto;" />
                            </div>
                            @if(!empty($agent->contact_email) || !empty($agent->contact_phone))
                                <div style="font-size:12px; color:#333; margin-top:6px;">
                                    @if(!empty($agent->contact_email))
                                        <div>{{ $agent->contact_email }}</div>
                                    @endif
                                    @if(!empty($agent->contact_phone))
                                        <div>{{ $agent->contact_phone }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
                <div class="quote-date">Date: {{ now()->format('d M Y, H:i') }}</div>
                <div class="quote-number">Quote #{{ time() }}</div>
            </div>
        </div>

        <h2>Your Quote</h2>

        <!-- Location Header -->
        <div class="item-details" style="margin-top: 10px;">
            <strong>Country:</strong> {{ $costBreakdown['country_name'] ?? 'Unknown Country' }}<br>
            <strong>City:</strong> {{ $costBreakdown['city_name'] ?? 'Unknown City' }}<br>
            <strong>School/Centre:</strong> {{ $costBreakdown['school_name'] ?? 'Unknown School' }}
        </div>

        <!-- Course Type Section -->
        @if(isset($costBreakdown['course_type_name']) && !empty($costBreakdown['course_type_name']))
        <div class="section-title">Course Type</div>
        <div class="item">
            <div class="item-name">{{ $costBreakdown['course_type_name'] }}</div>
            <div class="item-price">Selected</div>
        </div>
        @endif

        <!-- Course Section -->
        <div class="section-title">Course</div>
        @php
            // Initialize arrays for categorizing course items by year
            $courseTuition2025Items = [];
            $courseTuition2026Items = [];
            $courseTuitionOtherItems = [];
            $courseTuition = 0;
            
            // Initialize arrays for categorizing second course items by year
            $secondCourseTuition2025Items = [];
            $secondCourseTuition2026Items = [];
            $secondCourseTuitionOtherItems = [];
            $secondCourseTuition = 0;
            
            // Process all items and categorize them
            foreach ($costBreakdown['items'] as $item) {
                if ($item['category'] === 'tuition') {
                    $courseTuition += $item['amount'];
                    
                    // Categorize by year based on item name
                    if (strpos($item['name'], '2025') !== false) {
                        $courseTuition2025Items[] = $item;
                    } elseif (strpos($item['name'], '2026') !== false) {
                        $courseTuition2026Items[] = $item;
                    } else {
                        $courseTuitionOtherItems[] = $item;
                    }
                } elseif ($item['category'] === 'second_tuition') {
                    $secondCourseTuition += $item['amount'];
                    
                    // Categorize second course by year based on item name
                    if (strpos($item['name'], '2025') !== false) {
                        $secondCourseTuition2025Items[] = $item;
                    } elseif (strpos($item['name'], '2026') !== false) {
                        $secondCourseTuition2026Items[] = $item;
                    } else {
                        $secondCourseTuitionOtherItems[] = $item;
                    }
                }
            }
            
            $totalCourseTuition = $courseTuition + $secondCourseTuition;
        @endphp
        
        {{-- Display year-specific course sections when applicable --}}
        @if(count($courseTuition2025Items) > 0 && count($courseTuition2026Items) > 0)
            {{-- Mixed pricing: show separate 2025 and 2026 sections --}}
            <div class="section-title" style="background-color: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b;">Course Fees - 2025</div>
            @foreach($courseTuition2025Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
            
            <div class="section-title" style="background-color: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; margin-top: 20px;">Course Fees - 2026</div>
            @foreach($courseTuition2026Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @else
            {{-- Single year or no year-specific items: show standard course section --}}
            @foreach($courseTuition2025Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
            @foreach($courseTuition2026Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
            @foreach($courseTuitionOtherItems as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @endif
        <div class="item-details">
            {{ $costBreakdown['school_name'] }}, {{ $costBreakdown['city_name'] }} - {{ $costBreakdown['course_duration_weeks'] }} weeks<br>
            <strong>Start date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['course_start_date'])->format('d M Y') }}<br>
            <strong>End date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['course_end_date'])->format('d M Y') }}

            @if(isset($costBreakdown['christmas_break']) && $costBreakdown['christmas_break']['has_break'])
                <br><br>
                <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 8px; margin-top: 8px; border-radius: 4px;">
                    <strong style="color: #1e40af;">Christmas Break Notice:</strong><br>
                    <span style="color: #1e3a8a; font-size: 12px;">{{ $costBreakdown['christmas_break']['explanation'] }}</span>
                </div>
            @endif
        </div>

        <!-- Second Course Section -->
        @if(isset($costBreakdown['second_course_name']) && !empty($costBreakdown['second_course_name']))
        
        {{-- Display year-specific second course sections when applicable --}}
        @if(count($secondCourseTuition2025Items) > 0 && count($secondCourseTuition2026Items) > 0)
            {{-- Mixed pricing: show separate 2025 and 2026 sections --}}
            <div class="section-title" style="background-color: #fef3c7; color: #92400e; border-left: 4px solid #f59e0b;">Second Course Fees - 2025</div>
            @foreach($secondCourseTuition2025Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
            
            <div class="section-title" style="background-color: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; margin-top: 20px;">Second Course Fees - 2026</div>
            @foreach($secondCourseTuition2026Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @else
            {{-- Single year or no year-specific items: show standard second course section --}}
            <div class="section-title">Second Course</div>
            @foreach($secondCourseTuition2025Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
            @foreach($secondCourseTuition2026Items as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
            @foreach($secondCourseTuitionOtherItems as $item)
            <div class="item">
                <div class="item-name">{{ $item['name'] }}</div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @endif
        
        <div class="item-details">
            {{ $costBreakdown['second_school_name'] ?? $costBreakdown['school_name'] }}, {{ $costBreakdown['second_city_name'] ?? $costBreakdown['city_name'] }} - {{ $costBreakdown['second_course_duration_weeks'] }} weeks<br>
            <strong>Start date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['second_course_start_date'])->format('d M Y') }}<br>
            <strong>End date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['second_course_end_date'])->format('d M Y') }}

            @if(isset($costBreakdown['second_course_christmas_break']) && $costBreakdown['second_course_christmas_break']['has_break'])
                <br><br>
                <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; padding: 8px; margin-top: 8px; border-radius: 4px;">
                    <strong style="color: #1e40af;">Christmas Break Notice:</strong><br>
                    <span style="color: #1e3a8a; font-size: 12px;">{{ $costBreakdown['second_course_christmas_break']['explanation'] }}</span>
                </div>
            @endif
        </div>
        @endif

        <!-- Accommodation Section -->
        @php
            $accommodationTotal = $costBreakdown['subtotals']['accommodation'] ?? 0;
            $accommodationItems = [];
            $secondAccommodationItems = [];
            foreach ($costBreakdown['items'] as $item) {
                if ($item['category'] === 'accommodation') {
                    $accommodationItems[] = $item;
                } elseif ($item['category'] === 'second_accommodation') {
                    $secondAccommodationItems[] = $item;
                }
            }
        @endphp
        @if($accommodationTotal > 0 || count($accommodationItems) > 0)
        <div class="section-title">Accommodation</div>
        @foreach($accommodationItems as $item)
        <div class="item">
            <div class="item-name">
                @include('admin.quotations._included_badge', ['item' => $item, 'context' => 'pdf'])
            </div>
            <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
        </div>
        @endforeach
        
        @if(isset($costBreakdown['accommodation_start_date']) && isset($costBreakdown['accommodation_end_date']))
        <div class="item-details">
            <strong>Duration:</strong> {{ $costBreakdown['accommodation_duration_weeks'] }} weeks<br>
            <strong>Start date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['accommodation_start_date'])->format('d M Y') }}<br>
            <strong>End date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['accommodation_end_date'])->format('d M Y') }}
        </div>
        @endif
        @endif

        <!-- Second Accommodation Section -->
        @if(count($secondAccommodationItems) > 0)
        <div class="section-title">Second Accommodation</div>
        @foreach($secondAccommodationItems as $item)
        <div class="item">
            <div class="item-name">
                @include('admin.quotations._included_badge', ['item' => $item, 'context' => 'pdf'])
            </div>
            <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
        </div>
        @endforeach
        
        @if(isset($costBreakdown['second_accommodation_start_date']) && isset($costBreakdown['second_accommodation_end_date']))
        <div class="item-details">
            <strong>Duration:</strong> {{ $costBreakdown['second_accommodation_duration_weeks'] }} weeks<br>
            <strong>Start date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['second_accommodation_start_date'])->format('d M Y') }}<br>
            <strong>End date:</strong> {{ \Carbon\Carbon::parse($costBreakdown['second_accommodation_end_date'])->format('d M Y') }}
        </div>
        @endif
        @endif

        <!-- Optional Extras Section -->
        @php
            $feesTotal = $costBreakdown['subtotals']['fees'] ?? 0;
            $addonsTotal = $costBreakdown['subtotals']['addons'] ?? 0;
            $supplementItems = [];
            $guardianshipItems = [];
            $insuranceItems = [];
            $otherItems = [];
            
            foreach ($costBreakdown['items'] as $item) {
                if ($item['category'] === 'fees' || $item['category'] === 'addons') {
                    if (stripos($item['name'], 'insurance') !== false) {
                        $insuranceItems[] = $item;
                    } elseif (stripos($item['name'], 'supplement') !== false || 
                        stripos($item['name'], 'christmas') !== false || 
                        stripos($item['name'], 'summer') !== false) {
                        $supplementItems[] = $item;
                    } elseif (stripos($item['name'], 'guardianship') !== false) {
                        $guardianshipItems[] = $item;
                    } else {
                        $otherItems[] = $item;
                    }
                }
            }
        @endphp
        @if($feesTotal > 0 || $addonsTotal > 0)
        <div class="section-title">Optional Extras</div>
        
        {{-- Insurance Items --}}
        @if(count($insuranceItems) > 0)
            @foreach($insuranceItems as $item)
            <div class="item">
                <div class="item-name">
                    @include('admin.quotations._included_badge', ['item' => $item, 'context' => 'pdf'])
                </div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @endif
        
        {{-- Supplement Items --}}
        @if(count($supplementItems) > 0)
            @foreach($supplementItems as $item)
            <div class="item">
                <div class="item-name">
                    @include('admin.quotations._included_badge', ['item' => $item, 'context' => 'pdf'])
                </div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @endif
        
        {{-- Guardianship Items --}}
        @if(count($guardianshipItems) > 0)
            @foreach($guardianshipItems as $item)
            <div class="item">
                <div class="item-name">
                    @include('admin.quotations._included_badge', ['item' => $item, 'context' => 'pdf'])
                </div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @endif
        
        {{-- Other Items --}}
        @if(count($otherItems) > 0)
            @foreach($otherItems as $item)
            <div class="item">
                <div class="item-name">
                    @include('admin.quotations._included_badge', ['item' => $item, 'context' => 'pdf'])
                </div>
                <div class="item-price">{{ $formatCurrency($item['amount']) }}</div>
            </div>
            @endforeach
        @endif
        
        <div class="subtotal">
            <div>Extras Subtotal</div>
            <div>{{ $formatCurrency($costBreakdown['subtotals']['fees'] + $costBreakdown['subtotals']['addons']) }}</div>
        </div>
        @endif

        <!-- Discounts Section -->
        @if (!empty($costBreakdown['discounts']))
        <div class="section-title">Discounts</div>
        @foreach ($costBreakdown['discounts'] as $discount)
            <div class="item discount">
                <div class="item-name">@if(!empty($discount['is_nationality']) || empty($discount['hidden'])){{ $discount['name'] }}@endif</div>
                <div class="item-price">-{{ $formatCurrency($discount['amount']) }}</div>
            </div>
        @endforeach
        @endif

        <!-- Total Section -->
        <div class="total">
            <div>Total</div>
            <div>{{ $formatCurrency($costBreakdown['total']) }}</div>
        </div>
        @php
            $exchangeEnabled = request('exchange_enabled') === '1';
            $exchangeRate = floatval(request('exchange_rate'));
            $exchangeCurrency = request('exchange_target_currency');
            $symbolMap = ['GBP'=>'£','EUR'=>'€','USD'=>'$','TRY'=>'₺'];
            $exchangeSymbol = $symbolMap[$exchangeCurrency] ?? '';
        @endphp

        <div class="terms">
            <p><strong>Terms and Conditions:</strong></p>
            <p>This quote is valid for 30 days from the date of issue. Prices are subject to change without notice. All fees are payable in advance.</p>
            <p>For full terms and conditions, please visit our website or contact our admissions team.</p>
        </div>

        <div class="footer">
            <p>{{ $settings->company_name ?? 'Bayswater Education' }} | {{ $settings->company_email ?? 'info@bayswater.ac' }} | {{ $settings->company_phone ?? '+44 (0)20 7221 7259' }}</p>
            <p>© {{ date('Y') }} Bayswater Education. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Auto-print when the page loads
        window.onload = function() {
            // Wait a moment for the page to fully render
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

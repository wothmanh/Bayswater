{{-- Vertical Sidebar Navigation --}}
<div id="sidebar" class="w-64 h-screen fixed top-0 left-0 bg-bayswater-blue text-gray-100 flex flex-col shadow-lg overflow-auto transition-transform duration-300 ease-in-out transform z-50"> {{-- Use Primary Dark Blue --}}
    {{-- Logo/Brand --}}
    <div class="h-16 flex items-center justify-center bg-bayswater-blue-dark"> {{-- Use Darker Shade --}}
        <a href="{{ route('calculator.create') }}" class="flex items-center justify-center">
            <x-application-logo class="h-10 w-auto" />
            {{-- Agent brand logo/name shown next to Bayswater logo --}}
            @if(Auth::user()->isAgent())
                @php
                    $agent = Auth::user()->agentSetting;
                @endphp
                @if($agent)
                    @if($agent->brand_logo_path)
                         {{-- Logo logic if needed --}}
                    @elseif($agent->brand_display_name)
                        <span class="ml-3 text-white font-semibold">{{ $agent->brand_display_name }}</span>
                    @endif
                @endif
            @endif
        </a>
    </div>

    {{-- User Info --}}
    <div class="p-4 border-b border-bayswater-blue-dark"> {{-- Subtle border --}}
         {{-- Placeholder - Replace with dynamic user data --}}
        <div class="flex items-center">
            {{-- User Avatar with Bayswater colors --}}
            <div class="w-10 h-10 rounded-full bg-bayswater-orange mr-3 flex items-center justify-center text-white font-bold">
                {{-- Initials Placeholder --}}
                {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
            </div>
            <div>
                <div class="font-semibold text-white">{{ Auth::user()->name ?? 'Admin User' }}</div>
                @if(Auth::user()->isAgent() && optional(Auth::user()->agentSetting)->brand_display_name)
                    <div class="text-xs text-gray-200">Brand: {{ Auth::user()->agentSetting->brand_display_name }}</div>
                @endif
                @if(Auth::user()->isAgent() && optional(Auth::user()->agentSetting)->contact_email)
                    <div class="text-xs text-gray-300">{{ Auth::user()->agentSetting->contact_email }}</div>
                @endif
                @if(Auth::user()->isAgent() && optional(Auth::user()->agentSetting)->contact_phone)
                    <div class="text-xs text-gray-300">{{ Auth::user()->agentSetting->contact_phone }}</div>
                @endif
                <div class="text-xs text-green-400 flex items-center">
                    <span class="w-2 h-2 bg-bayswater-yellow rounded-full mr-1"></span> Online
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation Links --}}
    <nav class="flex-1 overflow-y-auto py-4 space-y-1">
        {{-- Fee Calculator - Available to all authenticated users --}}
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.quotations.create') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.quotations.*') || request()->routeIs('calculator.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
        @else
            <a href="{{ route('calculator.create') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('calculator.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
        @endif
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <span class="nav-text">Fees Calculator</span>
        </a>

        {{-- Configurable Market Discount Tab - visible when both fields are set and regions match --}}
        {{-- Market Discount Tabs --}}
            @php
                $marketDiscounts = \App\Models\MarketDiscount::with('regions')->get();
                $userIsAdmin = Auth::user()->isAdmin();
                // Fetch user regions once if not admin
                $userRegionIds = $userIsAdmin ? collect() : Auth::user()->regions()->pluck('regions.id');
                $settings = \App\Models\Setting::first();
            @endphp

            @foreach($marketDiscounts as $discount)
                @php
                    $canSee = false;
                    if (!empty($discount->title) && !empty($discount->iframe_url)) {
                        if ($userIsAdmin) {
                            $canSee = true;
                        } elseif ($discount->regions->isNotEmpty()) {
                             $discountRegionIds = $discount->regions->pluck('id');
                             if ($userRegionIds->intersect($discountRegionIds)->isNotEmpty()) {
                                 $canSee = true;
                             }
                        }
                    }
                @endphp

                @if($canSee)
                    <a href="{{ route('market-discount.show', $discount) }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ (request()->routeIs('market-discount.show') && request()->route('marketDiscount') && request()->route('marketDiscount')->id == $discount->id) ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a8 8 0 100 15.292 8 8 0 000-15.292zM12 2v20M2 12h20" />
                        </svg>
                        <span class="nav-text">{{ $discount->title }}</span>
                    </a>
                @endif
            @endforeach

            {{-- Search Accommodation Button --}}
            @if($settings && !empty($settings->search_accommodation_tab_title) && !empty($settings->search_accommodation_page_link))
                <a href="{{ $settings->search_accommodation_page_link }}" target="_blank" rel="noopener noreferrer" class="flex items-center px-4 py-2 text-sm font-medium rounded-md text-white hover:text-white mt-1" style="background-color: #003fbc;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span class="nav-text">{{ $settings->search_accommodation_tab_title }}</span>
                </a>
            @endif

            {{-- Partner Zone Button --}}
            @if($settings && !empty($settings->partner_zone_tab_title) && !empty($settings->partner_zone_page_link))
                <a href="{{ $settings->partner_zone_page_link }}" target="_blank" rel="noopener noreferrer" class="flex items-center px-4 py-2 text-sm font-medium rounded-md text-white hover:text-white mt-1" style="background-color: #003fbc;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="nav-text">{{ $settings->partner_zone_tab_title }}</span>
                </a>
            @endif

        {{-- Agent Settings - visible only to Agent users --}}
        @if(Auth::user()->isAgent())
            <a href="{{ route('agent.settings.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('agent.settings.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3 0 1.657 1.343 3 3 3s3-1.343 3-3c0-1.657-1.343-3-3-3zm8 3a8 8 0 11-16 0 8 8 0 0116 0z" />
                </svg>
                <span class="nav-text">Agent Settings</span>
            </a>
        @endif

        {{-- Admin-only navigation --}}
        @if(Auth::user()->isAdmin())
            <a href="http://127.0.0.1:8000/dashboard" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('dashboard') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z" />
                </svg>
                <span class="nav-text">Dashboard</span>
            </a>
        @endif

        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.settings.edit') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.settings.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756.426-1.756 2.924 0 3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="nav-text">System Settings</span>
            </a>
        @endif

        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.market-discount.edit') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.market-discount.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a8 8 0 100 15.292 8 8 0 000-15.292zM12 2v20M2 12h20" />
                </svg>
                <span class="nav-text">Market discount</span>
            </a>
        @endif

        @if(Auth::user()->isAdmin())
            {{-- Admin-only data management links --}}
            <a href="{{ route('admin.regions.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.regions.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-200 hover:text-white' }}">
                 <span class="ml-3 nav-text">Regions</span>
            </a>

            <a href="{{ route('admin.countries.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.countries.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span class="nav-text">Countries</span>
            </a>
            <a href="{{ route('admin.cities.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.cities.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span class="nav-text">Cities</span>
            </a>
             <a href="{{ route('admin.schools.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.schools.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Schools</span>
            </a>
            <a href="{{ route('admin.airports.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.airports.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                <span class="nav-text">Airports</span>
           </a>
            <a href="{{ route('admin.courses.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.courses.*') || request()->routeIs('admin.prices.*') || request()->routeIs('admin.schedules.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Courses</span>
            </a>
            <a href="{{ route('admin.junior-courses.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.junior-courses.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Junior Courses</span>
            </a>
             <a href="{{ route('admin.course-types.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.course-types.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Course Types</span>
            </a>
             <a href="{{ route('admin.accommodations.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.accommodations.*') || request()->routeIs('admin.accommodation-prices.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Accommodation</span>
            </a>
             <a href="{{ route('admin.currencies.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.currencies.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Currency</span>
            </a>
             <a href="{{ route('admin.exchange-names.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.exchange-names.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Exchange Name</span>
            </a>
             <a href="{{ route('admin.addons.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.addons.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Addons</span>
            </a>
             <a href="{{ route('admin.discount-rules.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.discount-rules.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Discounts</span>
            </a>
             <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-md hover:bg-bayswater-blue-dark {{ request()->routeIs('admin.users.*') ? 'bg-bayswater-blue-dark text-white' : 'text-gray-100 hover:text-white' }}">
                 <span class="w-6 h-6 mr-3"></span> {{-- Icon Placeholder --}}
                 <span>Users</span>
            </a>
        @endif

    </nav>

    {{-- Footer/Logout Section --}}
    <div class="mt-auto p-4 border-t border-bayswater-blue-dark">
        <div class="flex justify-between items-center">
            @if(!Auth::user()->isCalculator())
                <a href="{{ route('profile.edit') }}" class="flex items-center text-sm font-medium text-gray-100 hover:text-bayswater-yellow transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ __('Profile') }}
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center text-sm font-medium text-gray-100 hover:text-bayswater-yellow transition duration-150 ease-in-out">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>

</div>

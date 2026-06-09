<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// --- Admin Controllers ---
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\SchoolController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\ExchangeNameController;
use App\Http\Controllers\Admin\CourseTypeController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\JuniorCourseController;
use App\Http\Controllers\Admin\AccommodationController;
use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\DiscountRuleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CoursePriceController;
use App\Http\Controllers\Admin\CourseScheduleController;
use App\Http\Controllers\Admin\AccommodationPriceController;
use App\Http\Controllers\Admin\RegionController;
use App\Http\Controllers\Admin\AirportController; // Import AirportController
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\QuotationPdfController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\AgentBrandLogoController;
use App\Http\Controllers\MarketDiscountController;
use App\Http\Middleware\IsAdmin;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified', \App\Http\Middleware\RestrictCalculatorDashboard::class])->name('dashboard');

Route::middleware(['auth', \App\Http\Middleware\RestrictCalculatorProfile::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// --- Agent Routes (Accessible to both agents and admins) ---
Route::middleware('auth')->group(function () {
    Route::get('/agent-brand-logo/{agentSetting}', [AgentBrandLogoController::class, 'show'])
        ->name('agent-brand-logo.show');

    // Fee Calculator Routes for Agents
    Route::get('calculator', [QuotationController::class, 'create'])->name('calculator.create');
    Route::post('calculator/calculate', [QuotationController::class, 'calculate'])->name('calculator.calculate');
    Route::get('calculator/nationality-discounts/check', [QuotationController::class, 'checkNationalityDiscounts'])->name('calculator.nationality-discounts.check');
    Route::post('calculator/pdf', [QuotationPdfController::class, 'generatePdf'])->name('calculator.pdf');
    Route::post('calculator/print', [QuotationPdfController::class, 'printQuotation'])->name('calculator.print');

    // Market Discount iframe page (visible to all authenticated users when configured)
    Route::get('/market-discount', [MarketDiscountController::class, 'index'])->name('market-discount.index');
    Route::get('/market-discount/viewer', [MarketDiscountController::class, 'viewer'])->name('market-discount.viewer');
    Route::get('/market-discount/{marketDiscount}/viewer', [MarketDiscountController::class, 'viewer'])->name('market-discount.viewer.show');
    Route::get('/market-discount/{marketDiscount}', [MarketDiscountController::class, 'show'])->name('market-discount.show');

    // Agent Settings page (Agent-only)
    Route::middleware([\App\Http\Middleware\IsAgent::class])->group(function () {
        Route::get('/agent/settings', [\App\Http\Controllers\Agent\AgentSettingsController::class, 'index'])
            ->name('agent.settings.index');
        Route::post('/agent/settings', [\App\Http\Controllers\Agent\AgentSettingsController::class, 'update'])
            ->name('agent.settings.update');
        Route::delete('/agent/settings/logo', [\App\Http\Controllers\Agent\AgentSettingsController::class, 'removeLogo'])
            ->name('agent.settings.logo.remove');
    });

    // AJAX routes for school details and airports (accessible to agents)
    Route::get('schools/{school}/details', [SchoolController::class, 'getDetails'])->name('schools.get-details');
    Route::get('schools/{school}/airports', [SchoolController::class, 'getAirports'])->name('schools.get-airports');
    Route::get('schools/{school}/course-types', [SchoolController::class, 'getCourseTypes'])->name('schools.get-course-types');
});

// --- Admin Routes ---
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', \App\Http\Middleware\IsAdmin::class])
    ->group(function () {

        // Country CRUD routes
        Route::resource('countries', CountryController::class);
        // AJAX filter by country for cities (place before resource to avoid collision with cities/{city} show route)
        Route::get('cities/filter', [CityController::class, 'filter'])->name('cities.filter');
        // City CRUD routes
        Route::resource('cities', CityController::class);
        // Schools AJAX filter routes (place before resource to avoid collision with schools/{school} show route)
        Route::get('schools/filter', [SchoolController::class, 'filter'])->name('schools.filter');
        Route::get('schools/cities-for-country', [SchoolController::class, 'getCitiesForCountry'])->name('schools.cities-for-country');
        // School CRUD routes
        Route::resource('schools', SchoolController::class);
        // Currency CRUD routes
        Route::resource('currencies', CurrencyController::class);
        // Exchange Name CRUD routes
        Route::resource('exchange-names', ExchangeNameController::class);
        // Add update-order route for Exchange Names
        Route::post('exchange-names/update-order', [ExchangeNameController::class, 'updateOrder'])->name('exchange-names.update-order');
        // Course Type CRUD routes
        Route::resource('course-types', CourseTypeController::class); // Use kebab-case for route names
        // Courses AJAX filter routes (place before resource to avoid collision with courses/{course} routes)
        Route::get('courses/filter', [CourseController::class, 'filter'])->name('courses.filter');
        Route::get('courses/cities-for-country', [CourseController::class, 'getCitiesForCountry'])->name('courses.cities-for-country');
        Route::get('courses/schools-for-country-city', [CourseController::class, 'getSchoolsForCountryCity'])->name('courses.schools-for-country-city');
        // Course CRUD routes (with nested pricing and schedules)
        Route::resource('courses', CourseController::class);
        Route::resource('courses.prices', CoursePriceController::class)->shallow()->except(['show', 'index']); // Nested under courses
        Route::resource('junior-courses.prices', CoursePriceController::class)
            ->parameters(['junior-courses' => 'course'])
            ->shallow()
            ->except(['show', 'index']); // Nested under junior-courses
        Route::resource('courses.schedules', CourseScheduleController::class)->shallow()->except(['show', 'index']); // Nested under courses
        // Accommodations AJAX filter routes (place before resource to avoid collision with accommodations/{accommodation} routes)
        Route::get('accommodations/filter', [AccommodationController::class, 'filter'])->name('accommodations.filter');
        Route::get('accommodations/cities-for-country', [AccommodationController::class, 'getCitiesForCountry'])->name('accommodations.cities-for-country');
        Route::get('accommodations/schools-for-country-city', [AccommodationController::class, 'getSchoolsForCountryCity'])->name('accommodations.schools-for-country-city');
        // Accommodation CRUD routes (with nested pricing)
        Route::resource('accommodations', AccommodationController::class);
        // Use a completely different URL pattern for accommodation prices to avoid route collision
        Route::resource('accommodations.prices', AccommodationPriceController::class)
             ->parameters(['prices' => 'accommodation_price']) // Rename parameter
             ->names([
                 'create' => 'accommodations.prices.create',
                 'store' => 'accommodations.prices.store',
                 'edit' => 'accommodation-prices.edit',
                 'update' => 'accommodation-prices.update',
                 'destroy' => 'accommodation-prices.destroy',
             ])
             ->shallow()
             ->except(['show', 'index']);

        // Add custom routes for accommodation prices with a different URL pattern
        Route::get('accommodation-prices/{accommodation_price}/edit', [AccommodationPriceController::class, 'edit'])
             ->name('accommodation-prices.edit');
        Route::put('accommodation-prices/{accommodation_price}', [AccommodationPriceController::class, 'update'])
             ->name('accommodation-prices.update');
        Route::delete('accommodation-prices/{accommodation_price}', [AccommodationPriceController::class, 'destroy'])
             ->name('accommodation-prices.destroy');
        // Addon CRUD routes
        Route::resource('addons', AddonController::class);
        // Discount Rule CRUD routes
        Route::resource('discount-rules', DiscountRuleController::class);
        // User CRUD routes
        Route::resource('users', UserController::class);
        Route::delete('users/{user}/agent-branding/logo', [UserController::class, 'removeAgentLogo'])
            ->name('users.agent-branding.logo.remove');
        // Add alias route for deleting only the user logo to avoid confusion with destroy
        Route::delete('users/{user}/delete-logo', [UserController::class, 'removeAgentLogo'])
            ->name('users.delete-logo');
        // Region CRUD routes
        Route::resource('regions', RegionController::class);
        Route::post('regions/update-order', [RegionController::class, 'updateOrder'])->name('regions.update-order');
        // Airport CRUD routes
        // Airports AJAX filter routes (place before resource to avoid collision with airports/{airport} routes)
        Route::get('airports/filter', [AirportController::class, 'filter'])->name('airports.filter');
        Route::get('airports/cities-for-country', [AirportController::class, 'getCitiesForCountry'])->name('airports.cities-for-country');
        Route::get('airports/schools-for-country-city', [AirportController::class, 'getSchoolsForCountryCity'])->name('airports.schools-for-country-city');
        Route::resource('airports', AirportController::class);
        Route::post('airports/update-order', [AirportController::class, 'updateOrder'])->name('airports.update-order');
        
        // Update order routes for other resources
        Route::post('countries/update-order', [CountryController::class, 'updateOrder'])->name('countries.update-order');
        Route::post('cities/update-order', [CityController::class, 'updateOrder'])->name('cities.update-order');
        Route::post('schools/update-order', [SchoolController::class, 'updateOrder'])->name('schools.update-order');
        Route::post('courses/update-order', [CourseController::class, 'updateOrder'])->name('courses.update-order');
        Route::post('accommodations/update-order', [AccommodationController::class, 'updateOrder'])->name('accommodations.update-order');
        Route::post('course-types/update-order', [CourseTypeController::class, 'updateOrder'])->name('course-types.update-order');
        Route::post('junior-courses/update-order', [JuniorCourseController::class, 'updateOrder'])->name('junior-courses.update-order');
        Route::get('junior-courses/filter', [JuniorCourseController::class, 'filter'])->name('junior-courses.filter');

        Route::resource('junior-courses', JuniorCourseController::class)->except(['show']);

        // Quotation Calculator Routes
        Route::get('quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('quotations/calculate', [QuotationController::class, 'calculate'])->name('quotations.calculate');
        Route::post('quotations/pdf', [QuotationPdfController::class, 'generatePdf'])->name('quotations.pdf');
        Route::post('quotations/print', [QuotationPdfController::class, 'printQuotation'])->name('quotations.print');
        // Add routes for index, show, store later if needed for saving quotes

        // Settings Routes
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::get('settings/edit', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('settings/remove-logo', [SettingController::class, 'removeLogo'])->name('settings.remove-logo');

        Route::get('market-discount/edit', [SettingController::class, 'marketDiscountEdit'])->name('market-discount.edit');
});


require __DIR__.'/auth.php';

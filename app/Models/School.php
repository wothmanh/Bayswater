<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'city_id',
        'currency_id',
        'name',
        'description',
        'address',
        'phone',
        'email',
        'website',
        'registration_fee',
        'registration_fee_2026',
        'accommodation_fee',
        'accommodation_fee_2026',
        'bank_charges',
        'bank_charges_2026',
        'courier_fee',
        'courier_fee_2026',
        'courier_fee_enabled',
        'insurance_fee_per_week',
        'insurance_fee_per_week_2026',
        'books_fee',
        'books_fee_2026',
        'books_weeks',
        'books_weeks_2026',
        'guardianship_fee_per_week',
        'guardianship_fee_per_week_2026',
        'guardianship_fee_age',
        'custodianship_fee',
        'custodianship_fee_2026',
        'custodianship_fee_age',
        'summer_fee_per_week',
        'summer_fee_per_week_2026',
        'summer_start_date',
        'summer_start_date_2026',
        'summer_end_date',
        'summer_end_date_2026',
        'summer_fee_weeks_off',
        'summer_fee_weeks_off_2026',
        'christmas_supplement_per_week',
        'christmas_fee_per_week',
        'christmas_fee_per_week_2026',
        'christmas_start_date',
        'christmas_end_date',
        'christmas_supplement_start_date',
        'christmas_supplement_end_date',
        'christmas_start_date_2026',
        'christmas_end_date_2026',
        'extra_accommodation_weeks',
        'christmas_extra_accommodation_weeks',
        'order',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'christmas_start_date' => 'date',
        'christmas_end_date' => 'date',
        'christmas_start_date_2026' => 'date',
        'christmas_end_date_2026' => 'date',
        'summer_start_date' => 'date',
        'summer_end_date' => 'date',
        'summer_start_date_2026' => 'date',
        'summer_end_date_2026' => 'date',
        'christmas_supplement_start_date' => 'date',
        'christmas_supplement_end_date' => 'date',
        'active' => 'boolean',
        'courier_fee_enabled' => 'boolean',
        'extra_accommodation_weeks' => 'integer',
        'guardianship_fee_age' => 'integer',
        'custodianship_fee_age' => 'integer',
    ];

    /**
     * Backward-compatible alias for christmas_extra_accommodation_weeks
     * Proxies to the canonical extra_accommodation_weeks column.
     */
    public function getChristmasExtraAccommodationWeeksAttribute()
    {
        return $this->attributes['extra_accommodation_weeks'] ?? null;
    }

    public function setChristmasExtraAccommodationWeeksAttribute($value)
    {
        $this->attributes['extra_accommodation_weeks'] = is_numeric($value) ? (int) $value : null;
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('order')->orderBy('name');
        });
    }

    /**
     * Get the city that owns the school.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the currency for the school.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the courses for the school.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Get the accommodations for the school.
     */
    public function accommodations(): HasMany
    {
        return $this->hasMany(Accommodation::class);
    }

    /**
     * Get the addons for the school.
     */
    public function addons(): HasMany
    {
        return $this->hasMany(Addon::class);
    }

    /**
     * Get the discount rules specific to this school.
     */
    public function discountRules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }

     /**
     * Get the quotations associated with this school.
     */
    public function quotations(): HasMany
    {
         return $this->hasMany(Quotation::class);
    }

     /**
      * Get the airports associated with the school.
      */
     public function airports(): HasMany
     {
         return $this->hasMany(Airport::class);
     }

    /**
     * Get the social accounts for the school.
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SchoolSocialAccount::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the appropriate fee value based on the course start year.
     * 
     * @param string $feeType The base fee type (e.g., 'registration_fee')
     * @param Carbon|string|null $courseStartDate The course start date
     * @return float|null
     */
    public function getFeeByYear(string $feeType, $courseStartDate = null): ?float
    {
        if (!$courseStartDate) {
            return $this->{$feeType};
        }

        $startDate = $courseStartDate instanceof \Carbon\Carbon ? $courseStartDate : \Carbon\Carbon::parse($courseStartDate);
        $year = $startDate->year;

        // For 2026 and beyond, use 2026 values if available, otherwise fall back to base values
        if ($year >= 2026) {
            $yearSpecificField = $feeType . '_2026';
            if (isset($this->attributes[$yearSpecificField]) && $this->{$yearSpecificField} !== null) {
                return $this->{$yearSpecificField};
            }
        }

        // Default to base fee value (2025 or current year)
        return $this->{$feeType};
    }

    /**
     * Get the appropriate date value based on the course start year.
     * 
     * @param string $dateType The base date type (e.g., 'christmas_supplement_start_date')
     * @param Carbon|string|null $courseStartDate The course start date
     * @return Carbon|null
     */
    public function getDateByYear(string $dateType, $courseStartDate = null): ?\Carbon\Carbon
    {
        if (!$courseStartDate) {
            return $this->{$dateType};
        }

        $startDate = $courseStartDate instanceof \Carbon\Carbon ? $courseStartDate : \Carbon\Carbon::parse($courseStartDate);
        $year = $startDate->year;

        // For 2026 and beyond, use 2026 values if available, otherwise fall back to base values
        if ($year >= 2026) {
            $yearSpecificField = $dateType . '_2026';
            if (isset($this->attributes[$yearSpecificField]) && $this->{$yearSpecificField} !== null) {
                return $this->{$yearSpecificField};
            }
        }

        // Default to base date value (2025 or current year)
        return $this->{$dateType};
    }

    /**
     * Get the appropriate integer value based on the course start year.
     * 
     * @param string $intType The base integer type (e.g., 'christmas_extra_accommodation_weeks')
     * @param Carbon|string|null $courseStartDate The course start date
     * @return int|null
     */
    public function getIntByYear(string $intType, $courseStartDate = null): ?int
    {
        if (!$courseStartDate) {
            return $this->{$intType};
        }

        $startDate = $courseStartDate instanceof \Carbon\Carbon ? $courseStartDate : \Carbon\Carbon::parse($courseStartDate);
        $year = $startDate->year;

        // For 2026 and beyond, use 2026 values if available, otherwise fall back to base values
        if ($year >= 2026) {
            $yearSpecificField = $intType . '_2026';
            if (isset($this->attributes[$yearSpecificField]) && $this->{$yearSpecificField} !== null) {
                return $this->{$yearSpecificField};
            }
        }

        // Default to base integer value (2025 or current year)
        return $this->{$intType};
    }
}

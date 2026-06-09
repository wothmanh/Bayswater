<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\CourseType;
use App\Models\Course;

class Accommodation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'school_id',
        'type',
        'room_type',
        'meal_plan',
        'description',
        'min_age',
        'max_age',
        'min_weeks',
        'max_weeks',
        'summer_fee_per_week',
        'summer_fee_per_week_2026',
        'private_bathroom_fee',
        'private_bathroom_fee_2026',
        'dietary_supplement_fee',
        'dietary_supplement_fee_2026',
        'order',
        'active',
        'summer_start_date',
        'summer_start_date_2026',
        'summer_end_date',
        'summer_end_date_2026',
        'summer_fee_note',
        'summer_fee_note_2026',
        'requires_guardianship',
        'requires_christmas_supplement',
        'private_bathroom_enabled',
        'dietary_supplement_enabled',
        'private_bathroom_enabled_2026',
        'dietary_supplement_enabled_2026',
        'other_charge_enabled',
        'other_charge_name',
        'other_charge_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_guardianship' => 'boolean',
        'requires_christmas_supplement' => 'boolean',
        'summer_start_date' => 'date',
        'summer_start_date_2026' => 'date',
        'summer_end_date' => 'date',
        'summer_end_date_2026' => 'date',
        'active' => 'boolean',
        'private_bathroom_enabled' => 'boolean',
        'dietary_supplement_enabled' => 'boolean',
        'private_bathroom_enabled_2026' => 'boolean',
        'dietary_supplement_enabled_2026' => 'boolean',
        'other_charge_enabled' => 'boolean',
        'other_charge_amount' => 'decimal:2',
    ];

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
     * Get the school that offers the accommodation.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the prices for the accommodation.
     */
    public function accommodationPrices(): HasMany
    {
        return $this->hasMany(AccommodationPrice::class);
    }

    public function restrictedCourseTypes(): BelongsToMany
    {
        return $this->belongsToMany(CourseType::class, 'accommodation_course_type');
    }

    public function restrictedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'accommodation_course');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_accommodation');
    }


    /**
     * Get the discount rules associated with this accommodation.
     */
    public function discountRules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }

    /**
     * Get the quotations that include this accommodation.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /**
     * Get the appropriate fee value based on the course start year.
     * 
     * @param string $feeType The base fee type (e.g., 'private_bathroom_fee', 'dietary_supplement_fee')
     * @param Carbon|string|null $courseStartDate The course start date
     * @return float|null The fee value for the appropriate year
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
     * Get the appropriate enabled status based on the course start year.
     * 
     * @param string $enabledType The base enabled type (e.g., 'private_bathroom_enabled', 'dietary_supplement_enabled')
     * @param Carbon|string|null $courseStartDate The course start date
     * @return bool The enabled status for the appropriate year
     */
    public function getEnabledByYear(string $enabledType, $courseStartDate = null): bool
    {
        if (!$courseStartDate) {
            return $this->{$enabledType} ?? false;
        }

        $startDate = $courseStartDate instanceof \Carbon\Carbon ? $courseStartDate : \Carbon\Carbon::parse($courseStartDate);
        $year = $startDate->year;

        // For 2026 and beyond, use 2026 values if available, otherwise fall back to base values
        if ($year >= 2026) {
            $yearSpecificField = $enabledType . '_2026';
            if (isset($this->attributes[$yearSpecificField])) {
                return $this->{$yearSpecificField} ?? false;
            }
        }

        // Default to base enabled value (2025 or current year)
        return $this->{$enabledType} ?? false;
    }
}

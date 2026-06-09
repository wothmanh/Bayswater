<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Airport extends Model
{
    use HasFactory; // Optional: Add if you plan to use factories

    protected $fillable = [
        'name',
        'school_id',
        'arrival_price',
        'departure_price',
        'arrival_price_2026',
        'departure_price_2026',
        'active',
        'city_id',
        'country_id',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'arrival_price' => 'decimal:2',
        'arrival_price_2026' => 'decimal:2',
        'departure_price' => 'decimal:2',
        'departure_price_2026' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Get the appropriate fee value based on the course start year.
     * 
     * @param string $feeType The base fee type (e.g., 'arrival_price', 'departure_price')
     * @param \Carbon\Carbon|string|null $courseStartDate The course start date
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
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('order')->orderBy('name');
        });
    }

    /**
     * Get the school that owns the airport.
     */
    public function school(): BelongsTo
    {
        // An airport might belong to one primary school (if applicable)
        // Or remove this if an airport isn't directly owned by one school
        // An airport might belong to one primary school (if applicable)
        // Or remove this if an airport isn't directly owned by one school
        // An airport might belong to one primary school (if applicable)
        // Or remove this if an airport isn't directly owned by one school
        return $this->belongsTo(School::class);
    }

    /**
     * Get the restricted course types for this airport.
     */
    public function restrictedCourseTypes(): BelongsToMany
    {
        return $this->belongsToMany(CourseType::class, 'airport_course_type');
    }

    /**
     * Get the restricted courses for this airport.
     */
    public function restrictedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'airport_course');
    }

    // Optional: Add relationships for city and country if needed
    // public function city(): BelongsTo
    // {
    //     return $this->belongsTo(City::class);
    // }

    // public function country(): BelongsTo
    // {
    //     return $this->belongsTo(Country::class);
    // }
}

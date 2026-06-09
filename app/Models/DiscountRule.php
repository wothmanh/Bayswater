<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Region; // Import Region model

class DiscountRule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'hide_rule_name_in_calculator',
        'description',
        'discount_type',
        'discount_value',
        'applies_to',
        'addon_id',
        'school_id',
        'country_id',
        'course_id',
        'course_type_id',
        'accommodation_id',
        'accommodation_type',
        'min_course_weeks',
        'max_course_weeks',
        'min_accommodation_weeks',
        'max_accommodation_weeks',
        'valid_from_date',
        'valid_to_date',
        'date_condition_type',
        'quotation_extraction_date_from',
        'quotation_extraction_date_to',
        'combinable',
        'priority',
        'active',
        'region_id', // Add region_id
        'nationality', // Add nationality
        'nationality_title', // Add nationality_title
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valid_from_date' => 'date',
        'valid_to_date' => 'date',
        'quotation_extraction_date_from' => 'date',
        'quotation_extraction_date_to' => 'date',
        'combinable' => 'boolean',
        'active' => 'boolean',
        'hide_rule_name_in_calculator' => 'boolean',
    ];

    /**
     * Get the addon the discount rule applies to (if applicable).
     */
    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }

    /**
     * Get the school the discount rule applies to (if applicable).
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the country the discount rule applies to (if applicable).
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the course the discount rule applies to (if applicable).
     * @deprecated Use courses() instead.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the courses the discount rule applies to.
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'discount_rule_course');
    }

    /**
     * Get the course type the discount rule applies to (if applicable).
     */
    public function courseType(): BelongsTo
    {
        return $this->belongsTo(CourseType::class);
    }

    /**
     * Get the accommodation the discount rule applies to (if applicable).
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * Get the region that owns the discount rule.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}

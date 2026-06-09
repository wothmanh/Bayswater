<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'course_type_id',
        'name',
        'lessons_per_week',
        'hours_per_week',
        'study_mode',
        'description',
        'notes',
        'pricing_type',
        'category',
        'active',
        'order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
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
     * Get the school that offers the course.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the type of the course.
     */
    public function courseType(): BelongsTo
    {
        return $this->belongsTo(CourseType::class);
    }

    /**
     * Get the duration-based prices for the course.
     */
    public function coursePrices(): HasMany
    {
        return $this->hasMany(CoursePrice::class);
    }

    /**
     * Get the fixed schedules for the course.
     */
    public function courseSchedules(): HasMany
    {
        return $this->hasMany(CourseSchedule::class);
    }

    public function juniorSettings(): HasOne
    {
        return $this->hasOne(CourseJuniorSetting::class);
    }

    public function juniorAccommodations(): BelongsToMany
    {
        return $this->belongsToMany(Accommodation::class, 'course_accommodation');
    }

    /**
     * Get the detail links for the course.
     */
    public function detailLinks(): HasMany
    {
        return $this->hasMany(CourseDetailLink::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the detail links for the junior course.
     */
    public function juniorDetailLinks(): HasMany
    {
        return $this->hasMany(JuniorCourseDetailLink::class, 'junior_course_id')->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the discount rules associated with this course.
     */
    public function discountRules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }

    /**
     * Get the quotations that include this course.
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}

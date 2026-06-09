<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseJuniorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'start_date',
        'end_date',
        'min_age',
        'max_age',
        'min_weeks',
        'max_weeks',
        'includes_accommodation',
        'buy_weeks_only',
        'includes_registration_fee',
        'includes_books_fee',
        'includes_accommodation_placement',
        'includes_activities',
        'includes_local_travel',
        'includes_airport_transfer',
        'includes_insurance',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'includes_accommodation' => 'boolean',
        'buy_weeks_only' => 'boolean',
        'includes_registration_fee' => 'boolean',
        'includes_books_fee' => 'boolean',
        'includes_accommodation_placement' => 'boolean',
        'includes_activities' => 'boolean',
        'includes_local_travel' => 'boolean',
        'includes_airport_transfer' => 'boolean',
        'includes_insurance' => 'boolean',
    ];



    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursePrice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'min_weeks',
        'max_weeks',
        'price_per_week',
        'price_per_week_2026',
        'active',
    ];

    /**
     * Get the course that owns the price range.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

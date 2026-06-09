<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JuniorCourseDetailLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'junior_course_id',
        'button_text',
        'url',
        'sort_order',
    ];

    /**
     * Get the junior course that owns the detail link.
     */
    public function juniorCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'junior_course_id');
    }
}

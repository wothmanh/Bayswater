<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'agent_id',
        'client_name',
        'client_email',
        'client_birthday',
        'client_nationality_country_id',
        'school_id',
        'course_id',
        'course_start_date',
        'course_duration_weeks',
        'accommodation_id',
        'accommodation_duration_weeks',
        'selected_addons',
        'currency_id',
        'calculated_result',
        'total_price',
        'status',
        'notes',
        'pdf_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'client_birthday' => 'date',
        'course_start_date' => 'date',
        'selected_addons' => 'array', // Cast JSON to array
        'calculated_result' => 'array', // Cast JSON to array
    ];

    /**
     * Get the agent user who created the quotation.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the client's nationality country.
     */
    public function clientNationality(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'client_nationality_country_id');
    }

    /**
     * Get the school for the quotation.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Get the course for the quotation.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the accommodation for the quotation.
     */
    public function accommodation(): BelongsTo
    {
        return $this->belongsTo(Accommodation::class);
    }

    /**
     * Get the currency for the quotation.
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the payments for the quotation.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}

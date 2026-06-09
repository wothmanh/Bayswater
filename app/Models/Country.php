<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough; // Import HasManyThrough

class Country extends Model
{
    use HasFactory;

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
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'iso_code_2',
        'iso_code_3',
        'active',
        'order',
    ];

    /**
     * Get the cities for the country.
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }

    /**
     * Get the schools for the country through cities.
     */
    public function schools(): HasManyThrough
    {
        return $this->hasManyThrough(School::class, City::class);
    }

     /**
     * Get the quotations associated with this country (client nationality).
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'client_nationality_country_id');
    }

     /**
     * Get the discount rules associated with this country.
     */
    public function discountRules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }
}

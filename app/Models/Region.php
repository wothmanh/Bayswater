<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // Import HasFactory
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // Import HasMany
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Region extends Model
{
    use HasFactory; // Add HasFactory trait

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'active',
        'order',
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
     * Get the discount rules associated with the region.
     */
    public function discountRules(): HasMany
    {
        return $this->hasMany(DiscountRule::class);
    }

    /**
     * The users assigned to the region.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * The market discounts assigned to the region.
     */
    public function marketDiscounts(): BelongsToMany
    {
        return $this->belongsToMany(MarketDiscount::class, 'market_discount_region');
    }

    /**
     * The exchange names assigned to the region.
     */
    public function exchangeNames(): BelongsToMany
    {
        return $this->belongsToMany(ExchangeName::class);
    }
}

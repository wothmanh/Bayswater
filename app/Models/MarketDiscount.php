<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MarketDiscount extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'iframe_url',
    ];

    /**
     * The regions assigned to the market discount.
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'market_discount_region');
    }
}

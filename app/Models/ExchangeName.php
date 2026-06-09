<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExchangeName extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'label', 'order'];

    /**
     * The regions that belong to the exchange name.
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AgentSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'brand_logo_path',
        'brand_display_name',
        'contact_email',
        'contact_phone',
        'contact_whatsapp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function brandLogoUrl(): ?string
    {
        if (!$this->brand_logo_path) {
            return null;
        }

        $version = Storage::disk('public')->exists($this->brand_logo_path)
            ? Storage::disk('public')->lastModified($this->brand_logo_path)
            : $this->updated_at?->timestamp;

        return route('agent-brand-logo.show', $this).($version ? '?v='.$version : '');
    }
}

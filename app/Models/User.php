<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role', // Add role here
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the quotations created by the user (if agent).
     */
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'agent_id');
    }

    /**
     * Get the payments recorded by the user.
     */
    public function recordedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'recorded_by_user_id');
    }

    /**
     * Check if the user is an admin.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is an agent.
     *
     * @return bool
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Check if the user is a calculator.
     *
     * @return bool
     */
    public function isCalculator(): bool
    {
        return $this->role === 'calculator';
    }

    /**
     * Check if the user is an agent or calculator (same permissions).
     *
     * @return bool
     */
    public function isAgentOrCalculator(): bool
    {
        return in_array($this->role, ['agent', 'calculator']);
    }

    /**
     * Agent settings relation.
     */
    public function agentSetting()
    {
        return $this->hasOne(AgentSetting::class);
    }

    /**
     * The regions assigned to the user.
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class);
    }
}

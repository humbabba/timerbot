<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('is_admin')->withTimestamps();
    }

    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->wherePivot('is_admin', true);
    }

    public function timers(): HasMany
    {
        return $this->hasMany(Timer::class);
    }

    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    public function hasAdmin(User $user): bool
    {
        return $this->admins()->where('user_id', $user->id)->exists();
    }
}

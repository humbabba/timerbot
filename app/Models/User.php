<?php

namespace App\Models;

use App\Traits\HasRoles;
use App\Traits\Loggable;
use App\Traits\Searchable;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Loggable, Notifiable, Searchable, Trashable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'starting_view',
        'last_login_at',
        'news_viewed_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
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
            'last_login_at' => 'datetime',
            'news_viewed_at' => 'datetime',
        ];
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Group::class)->withPivot('is_admin')->withTimestamps();
    }

    public function createdTimers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Timer::class, 'created_by');
    }

    public function isAppAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    protected function getRelationshipsForTrash(): array
    {
        return [
            'roles' => $this->roles()->pluck('roles.id')->toArray(),
        ];
    }

    public function getLoggableExcludedFields(): array
    {
        return ['updated_at', 'remember_token', 'last_login_at', 'news_viewed_at'];
    }
}

<?php

namespace App\Models;

use App\Traits\Copyable;
use App\Traits\Loggable;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use Copyable, Loggable, Trashable;

    public function duplicate(): static
    {
        $copy = $this->replicate();
        $copy->name = $this->name . ' (copy)';
        $copy->save();

        $copy->permissions()->attach($this->permissions->pluck('id'));
        $copy->assignableRoles()->attach($this->assignableRoles->pluck('id'));

        return $copy;
    }

    protected $fillable = [
        'name',
        'description',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function assignableRoles(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'assignable_role', 'role_id', 'assignable_role_id');
    }

    protected function getRelationshipsForTrash(): array
    {
        return [
            'permissions' => $this->permissions()->pluck('permissions.id')->toArray(),
            'assignableRoles' => $this->assignableRoles()->pluck('roles.id')->toArray(),
        ];
    }
}

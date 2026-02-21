<?php

namespace App\Models;

use App\Traits\Copyable;
use App\Traits\Loggable;
use App\Traits\Searchable;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timer extends Model
{
    use Copyable, Loggable, Searchable, Trashable;

    const LOCK_TIMEOUT_SECONDS = 30;

    protected $fillable = [
        'name',
        'visibility',
        'group_id',
        'created_by',
        'end_time',
        'participant_count',
        'warnings',
        'message',
        'run_state',
        'locked_by',
        'lock_refreshed_at',
    ];

    protected function casts(): array
    {
        return [
            'participant_count' => 'integer',
            'warnings' => 'array',
            'run_state' => 'array',
            'lock_refreshed_at' => 'datetime',
        ];
    }

    public function getLoggableExcludedFields(): array
    {
        return ['updated_at', 'run_state', 'locked_by', 'lock_refreshed_at'];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isLocked(): bool
    {
        if (!$this->locked_by) {
            return false;
        }

        if (!$this->lock_refreshed_at) {
            return false;
        }

        return $this->lock_refreshed_at->diffInSeconds(now()) < self::LOCK_TIMEOUT_SECONDS;
    }

    public function isLockedByOther(User $user): bool
    {
        return $this->isLocked() && $this->locked_by !== $user->id;
    }

    public function acquireLock(User $user): void
    {
        $this->update([
            'locked_by' => $user->id,
            'lock_refreshed_at' => now(),
        ]);
    }

    public function releaseLock(?User $user = null): void
    {
        if ($user && $this->locked_by !== $user->id) {
            return;
        }

        $this->update([
            'locked_by' => null,
            'lock_refreshed_at' => null,
        ]);
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    /**
     * Check if user can view this timer.
     * Public timers: anyone (even guests).
     * Private timers: group members or app admins.
     */
    public function canView(?User $user): bool
    {
        if ($this->isPublic()) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isAppAdmin()) {
            return true;
        }

        return $this->group && $this->group->hasMember($user);
    }

    /**
     * Check if user can run this timer.
     * Group members or app admins.
     */
    public function canRun(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAppAdmin()) {
            return true;
        }

        return $this->group && $this->group->hasMember($user);
    }

    /**
     * Check if user can edit/delete this timer.
     * Group admins or app admins.
     */
    public function canManage(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAppAdmin()) {
            return true;
        }

        return $this->group && $this->group->hasAdmin($user);
    }
}

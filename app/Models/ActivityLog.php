<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'loggable_name',
        'action',
        'changes',
        'user_id',
        'user_name',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function userExists(): bool
    {
        return $this->user_id !== null && $this->user()->exists();
    }

    public function loggableExists(): bool
    {
        return $this->loggable()->exists();
    }

    public function getLoggableDisplayName(): string
    {
        return $this->loggable_name ?? "#{$this->loggable_id}";
    }

    public function getModelName(): string
    {
        return class_basename($this->loggable_type);
    }

    public function getActionColorClass(): string
    {
        return match ($this->action) {
            'created' => 'badge-green',
            'updated' => 'badge-blue',
            'deleted' => 'badge-red',
            'run' => 'badge-orange',
            default => 'badge-lavender',
        };
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('loggable_type', $type);
    }

    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('loggable_name', 'like', "%{$search}%")
              ->orWhere('user_name', 'like', "%{$search}%");
        });
    }

    public function scopeCreatedBetween($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Trash extends Model
{
    protected $table = 'trash';

    protected $fillable = [
        'trashable_type',
        'trashable_id',
        'data',
        'relationships',
        'deleted_by',
        'deleted_at',
    ];

    protected $casts = [
        'data' => 'array',
        'relationships' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('trashable_type', $type);
    }

    public function scopeOlderThan(Builder $query, int $days): Builder
    {
        return $query->where('deleted_at', '<', now()->subDays($days));
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->whereRaw("JSON_EXTRACT(data, '$.name') LIKE ?", ["%{$search}%"])
              ->orWhereRaw("JSON_EXTRACT(data, '$.email') LIKE ?", ["%{$search}%"]);
        });
    }

    public function scopeDeletedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('deleted_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('deleted_at', '<=', $to);
        }

        return $query;
    }

    public function getModelNameAttribute(): string
    {
        return class_basename($this->trashable_type);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->data['name'] ?? $this->data['email'] ?? "#{$this->trashable_id}";
    }

    public function restore(): ?Model
    {
        $modelClass = $this->trashable_type;

        if (!class_exists($modelClass)) {
            return null;
        }

        $model = new $modelClass();
        $model->forceFill($this->data);
        $model->exists = false;
        $model->save();

        // Restore relationships
        if (!empty($this->relationships)) {
            foreach ($this->relationships as $relationName => $relatedIds) {
                if (method_exists($model, $relationName)) {
                    $relation = $model->$relationName();
                    if (method_exists($relation, 'sync')) {
                        $relation->sync($relatedIds);
                    }
                }
            }
        }

        $this->delete();

        return $model;
    }
}

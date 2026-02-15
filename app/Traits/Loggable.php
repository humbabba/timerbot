<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    protected static array $oldAttributes = [];

    public static function bootLoggable(): void
    {
        static::created(function ($model) {
            $model->logActivity('created', $model->attributesToArray());
        });

        static::updating(function ($model) {
            static::$oldAttributes[$model->getKey()] = $model->getOriginal();
        });

        static::updated(function ($model) {
            $oldAttributes = static::$oldAttributes[$model->getKey()] ?? [];
            $dirty = $model->getDirty();

            // Remove excluded fields
            foreach ($model->getLoggableExcludedFields() as $field) {
                unset($dirty[$field]);
            }

            if (empty($dirty)) {
                unset(static::$oldAttributes[$model->getKey()]);
                return;
            }

            $changes = [];
            foreach ($dirty as $key => $newValue) {
                $oldValue = $oldAttributes[$key] ?? null;

                // For JSON/array fields, diff the contents
                if (is_array($newValue) || is_array($oldValue)) {
                    $oldArray = is_array($oldValue) ? $oldValue : (is_string($oldValue) ? json_decode($oldValue, true) : []);
                    $newArray = is_array($newValue) ? $newValue : (is_string($newValue) ? json_decode($newValue, true) : []);

                    $arrayChanges = $model->diffArrays($oldArray ?? [], $newArray ?? []);
                    if (!empty($arrayChanges)) {
                        $changes[$key] = $arrayChanges;
                    }
                } else {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }

            if (empty($changes)) {
                unset(static::$oldAttributes[$model->getKey()]);
                return;
            }

            $model->logActivity('updated', $changes);

            unset(static::$oldAttributes[$model->getKey()]);
        });

        static::deleting(function ($model) {
            $model->logActivity('deleted', $model->attributesToArray());
        });
    }

    public function logActivity(string $action, array $changes): void
    {
        try {
            $user = Auth::user();

            ActivityLog::create([
                'loggable_type' => get_class($this),
                'loggable_id' => $this->getKey(),
                'loggable_name' => $this->getLoggableName(),
                'action' => $action,
                'changes' => $changes,
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'created_at' => now(),
            ]);
        } catch (\Illuminate\Database\QueryException) {
            // Table may not exist yet during migrations
        }
    }

    public function getLoggableName(): ?string
    {
        // Try common name attributes
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }
        if (isset($this->key)) {
            return $this->key;
        }
        if (isset($this->email)) {
            return $this->email;
        }

        return null;
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public function getLoggableExcludedFields(): array
    {
        return ['updated_at'];
    }

    protected function diffArrays(array $old, array $new): array
    {
        $changes = [];

        // Check for changed or added keys
        foreach ($new as $key => $newValue) {
            $oldValue = $old[$key] ?? null;

            if (is_array($newValue) && is_array($oldValue)) {
                $nestedChanges = $this->diffArrays($oldValue, $newValue);
                if (!empty($nestedChanges)) {
                    $changes[$key] = $nestedChanges;
                }
            } elseif ($oldValue !== $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        // Check for removed keys
        foreach ($old as $key => $oldValue) {
            if (!array_key_exists($key, $new)) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => null,
                ];
            }
        }

        return $changes;
    }
}

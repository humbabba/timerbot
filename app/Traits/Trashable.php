<?php

namespace App\Traits;

use App\Models\Trash;
use Illuminate\Support\Facades\Auth;

trait Trashable
{
    public static function bootTrashable(): void
    {
        static::deleting(function ($model) {
            $model->moveToTrash();
        });
    }

    public function moveToTrash(): void
    {
        Trash::create([
            'trashable_type' => get_class($this),
            'trashable_id' => $this->getKey(),
            'data' => $this->attributesToArray(),
            'relationships' => $this->getRelationshipsForTrash(),
            'deleted_by' => Auth::id(),
            'deleted_at' => now(),
        ]);
    }

    protected function getRelationshipsForTrash(): array
    {
        return [];
    }
}

<?php

namespace App\Models;

use App\Traits\Copyable;
use App\Traits\Loggable;
use App\Traits\Searchable;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Wave extends Model
{
    use Copyable, Loggable, Searchable, Trashable;

    protected $fillable = [
        'name',
        'description',
    ];

    public function nodes(): BelongsToMany
    {
        return $this->belongsToMany(Node::class)
            ->withPivot(['position', 'mappings', 'include_in_output'])
            ->withTimestamps()
            ->orderByPivot('position');
    }

    /**
     * Override duplicate to also copy node relationships.
     */
    public function duplicate(): static
    {
        $copy = $this->replicate();
        $copy->name = $this->name . ' (copy)';
        $copy->save();

        // Copy node associations with their pivot data
        foreach ($this->nodes as $node) {
            $copy->nodes()->attach($node->id, [
                'position' => $node->pivot->position,
                'mappings' => $node->pivot->mappings,
                'include_in_output' => $node->pivot->include_in_output,
            ]);
        }

        return $copy;
    }
}

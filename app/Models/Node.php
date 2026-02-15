<?php

namespace App\Models;

use App\Traits\Copyable;
use App\Traits\Loggable;
use App\Traits\Searchable;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Node extends Model
{
    use Copyable, Loggable, Searchable, Trashable;

    protected $fillable = [
        'name',
        'inputs',
        'system_text',
        'max_tokens',
        'url_source_field',
        'url_target_field',
        'voice_profile_id',
        'style_check',
    ];

    protected $casts = [
        'inputs' => 'array',
        'style_check' => 'boolean',
    ];

    /**
     * Ensure inputs is always a properly indexed array for JSON serialization.
     */
    public function getInputsAttribute($value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return is_array($decoded) ? array_values($decoded) : [];
    }

    public function voiceProfile(): BelongsTo
    {
        return $this->belongsTo(VoiceProfile::class);
    }

    public function waves(): BelongsToMany
    {
        return $this->belongsToMany(Wave::class)
            ->withPivot(['position', 'mappings'])
            ->withTimestamps();
    }
}

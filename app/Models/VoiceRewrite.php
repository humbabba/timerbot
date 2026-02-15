<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceRewrite extends Model
{
    use Loggable;

    protected $fillable = [
        'voice_profile_id',
        'original_text',
        'rewritten_text',
        'notes',
        'activity_log_id',
    ];

    public function voiceProfile(): BelongsTo
    {
        return $this->belongsTo(VoiceProfile::class);
    }

    public function getLoggableName(): ?string
    {
        return $this->notes ?? "Rewrite #{$this->id}";
    }
}

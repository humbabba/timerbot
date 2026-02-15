<?php

namespace App\Models;

use App\Traits\Loggable;
use App\Traits\Searchable;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VoiceProfile extends Model
{
    use Loggable, Searchable, Trashable;

    protected $fillable = [
        'name',
        'content',
    ];

    public function nodes(): HasMany
    {
        return $this->hasMany(Node::class);
    }

    public function rewrites(): HasMany
    {
        return $this->hasMany(VoiceRewrite::class);
    }
}

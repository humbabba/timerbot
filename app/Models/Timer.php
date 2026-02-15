<?php

namespace App\Models;

use App\Traits\Copyable;
use App\Traits\Loggable;
use App\Traits\Searchable;
use App\Traits\Trashable;
use Illuminate\Database\Eloquent\Model;

class Timer extends Model
{
    use Copyable, Loggable, Searchable, Trashable;

    protected $fillable = [
        'name',
        'end_time',
        'participant_count',
        'warnings',
    ];

    protected function casts(): array
    {
        return [
            'participant_count' => 'integer',
            'warnings' => 'array',
        ];
    }
}

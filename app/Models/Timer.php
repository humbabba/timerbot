<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timer extends Model
{
    use HasFactory;

    protected $fillable = [
      'name',
      'guys',
      'current_guy',
      'started',
      'end_time',
      'message'
  ];
}

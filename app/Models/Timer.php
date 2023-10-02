<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helpers;

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

  public function calculateTimePerGuy($currentGuyStart)
  {
    $remainingGuys = $this->guys - ($this->current_guy - 1);
    $interval = Helpers::calculateInterval($this->current_guy_start,$this->end_time);
    $remainingSeconds = Helpers::intervalToSeconds($interval);
    $secondsPerGuy = round($remainingSeconds/$remainingGuys);
    return sprintf('%02d:%02d:%02d', ($secondsPerGuy/ 3600),($secondsPerGuy/ 60 % 60), $secondsPerGuy% 60);
  }
}

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

  private function calculateSecondsPerGuy()
  {
    $remainingGuys = $this->guys - ($this->current_guy - 1);
    $interval = Helpers::calculateInterval($this->current_guy_start,$this->end_time);
    $remainingSeconds = Helpers::intervalToSeconds($interval);
    return round($remainingSeconds/$remainingGuys);
  }

  private function calculateCurrentGuyEndTime()
  {
    $secondsPerGuy = $this->calculateSecondsPerGuy();
    $start = $this->current_guy_start;
    $dateTime = \DateTime::createFromFormat('H:i:s',$start);
    $dateTime->modify("+ $secondsPerGuy seconds");
    return $dateTime->format('H:i:s');
  }

  public function calculateTimePerGuy()
  {
    $secondsPerGuy = $this->calculateSecondsPerGuy();
    return sprintf('%02d:%02d:%02d', ($secondsPerGuy/3600),($secondsPerGuy/60 % 60), $secondsPerGuy % 60);
  }

  public function calculateCurrentGuyRemainingInterval()
  {
    $endTime = $this->calculateCurrentGuyEndTime();
    $now = new \DateTime('now');
    return Helpers::calculateInterval($now->format('H:i:s'),$endTime);
  }

  public function adjustTimePerGuyByOverage()
  {
    $overageInterval = Helpers::calculateInterval('00:00:00',$this->current_guy_remaining);
    $overageSeconds = round(Helpers::intervalToSeconds($overageInterval)/($this->guys - $this->current_guy));
    $newTimePerGuy = \DateTime::createFromFormat('H:i:s',$this->time_per_guy);
    $newTimePerGuy->modify("- $overageSeconds seconds");
    return $newTimePerGuy->format('H:i:s');
  }

}

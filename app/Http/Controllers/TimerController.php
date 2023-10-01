<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timer;

class TimerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($timerName)
    {
        $timer = Timer::where('name',$timerName)->first();
        return view('timers.show')->with(['timer' => $timer]);
    }

    /**
    * Get current Timer info
    */
    public function info($timerName)
    {
      $timer = Timer::where('name',$timerName)->first();
      $timeNow = new \DateTimeImmutable('now');
      $timeEnd = \DateTimeImmutable::createFromFormat('H:i:s',$timer->end_time);
      $interval = $timeNow->diff($timeEnd);
      $timer->now = $timeNow->format('H:i:s');
      $timer->remaining = $interval->format('%H:%I:%S');
      $timer->over = $interval->invert;
      return $timer->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $timerName
     */
    public function edit($timerName)
    {
        $timer = Timer::where('name',$timerName)->first();
        return view('timers.edit')->with(['timer' => $timer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request)
    {
      $output = [
        'success' => false,
        'message' => 'Timer not updated',
      ];

      $data = $request->get('data');
      $timerName = $data['timerName'];
      $eventName = $data['eventName'];

      $timer = Timer::where('name',$timerName)->first();

      switch ($eventName) {
        case 'start':
          $timer->started = true;
          if(!$timer->current_guy) {
            $timer->current_guy = 1;
          }
          break;
        case 'stop':
          $timer->started = false;
          break;
        case 'reset':
          $timer->started = false;
          $timer->current_guy = 0;
          break;
        case 'pass':
          if($timer->current_guy === $timer->guys) {
            $timer->started = false;
            $timer->current_guy = 0;
          } else {
            $timer->current_guy += 1;
          }
          break;

        default:
          // code...
          break;
      }

      if($timer->save()) {
        $output['success'] = true;
        $output['message'] = 'Timer updated';
      }
      return json_encode($output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

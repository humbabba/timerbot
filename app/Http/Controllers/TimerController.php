<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timer;
use App\Helpers\Helpers;

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
    public function show($id)
    {
        $timer = Timer::find($id);
        return view('timers.show')->with(['timer' => $timer]);
    }

    /**
    * Get current Timer info
    */
    public function info($id)
    {
      $timer = Timer::find($id);
      $timer->now = date('H:i:s');
      $interval = Helpers::calculateInterval(date('H:i:s'),$timer->end_time);
      $timer->remaining = $interval->format('%H:%I:%S');
      $timer->over = $interval->invert;
      if($timer->current_guy_start) {
        $timer->time_per_guy = $timer->calculateTimePerGuy($timer->current_guy_start);
      }
      return $timer->toJson();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $timerName
     */
    public function edit($id)
    {
      $timer = Timer::find($id);
      return view('timers.edit')->with(['timer' => $timer]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request, $id)
    {

      $timer = Timer::find($id);

      $timer->name = $request->get('name') ?? 'uweo4hKk0BA0La0WMD1xXGxVStkvTVoGaNEkDCx';
      $timer->guys = $request->get('guys') ?? 8;
      $timer->end_time = $request->get('end_time') ?? '20:58:00';
      $timer->message = $request->get('message') ?? '';
      $timer->updated_at = date('Y-m-d H:i:s');

      if($timer->save()) {
        return redirect()->route('timers.edit',$timer->id)->with(['message' => 'Timer updated']);
      }
    }

    /**
    * Send event to timer.
    *
    * @param  \Illuminate\Http\Request  $request
    */
    public function event(Request $request)
    {
      $output = [
        'success' => false,
        'message' => 'Event not processed',
      ];

      $data = $request->get('data');
      $timerId = $data['timerId'];
      $eventName = $data['eventName'];

      $timer = Timer::find($timerId);

      switch ($eventName) {
        case 'start':
          $timer->started = true;
          if(!$timer->current_guy) {
            $timer->current_guy = 1;
            $timer->current_guy_start = date('H:i:s');
          }
          break;
        case 'stop':
          $timer->started = false;
          break;
        case 'reset':
          $timer->started = false;
          $timer->current_guy = 0;
          $timer->current_guy_start = null;
          break;
        case 'pass':
          if($timer->current_guy === $timer->guys) {
            $timer->started = false;
            $timer->current_guy = 0;
          } else {
            $timer->current_guy += 1;
            $timer->current_guy_start = date('H:i:s');
          }
          break;
      }

      if($timer->save()) {
        $output['success'] = true;
        $output['message'] = 'Event processed';
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

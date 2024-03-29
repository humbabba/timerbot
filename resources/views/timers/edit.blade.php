<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Timerbot</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- CSRF Token -->
        <meta name="csrf_token" content="{{ csrf_token() }}">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }
        </style>
    </head>
    <body class="p-[1rem]">
      <div class="max-w-[600px] w-full border m-[auto] p-[1rem]">
        <input type="hidden" id="timerId" value="{{$timer->id}}"/>
        <input type="hidden" id="over" value="0"/>
        <input type="hidden" id="current_guy_over" value="0"/>
        <input type="hidden" id="current_guy_alarm_status" value=""/>
        <h1 class="text-2xl underline mb-2">{{$timer->name}} Timerbot - admin</h1>
        <div class="flex flex-wrap gap-4 my-2">
          <p><b>Now:</b> <span id="now">{{ date('H:i:s') }}</span></p>
          <p><b>Remaining:</b> <span id="remaining"></span></p>
        </div>
        <hr class="my-[1rem]">
        <p class="p-[0.5rem] uppercase rounded text-white text-center{{ ($timer->started)? ' bg-blue-800':' bg-gray-300' }} mb-[1rem]" id="passButton">Pass</p>
        <p class="p-[0.5rem] uppercase rounded text-white text-center{{ ($timer->started)? ' bg-orange-600':' bg-gray-300' }} max-w-[50%]" id="passBackButton">&lt;&lt; Pass back</p>

        <hr class="my-[1rem]">
        <div id="editableInfo">
          <form id="timerForm" action="{{ route('timers.update',$timer->id) }}" method="post" />
            @csrf
            @method('PATCH')
            <div class="flex flex-wrap gap-x-[1rem]">
              <p class="text-lg my-2"><b>Name:</b> <input type="text" class="max-w-[100px] px-[1rem] border rounded" name="name" value="{{ $timer->name }}"/></p>
              <p class="text-lg my-2"><b>Guys:</b> <input type="number" class="max-w-[100px] px-[1rem] border rounded" name="guys" value="{{ $timer->guys }}"/></p>
              <p class="text-lg my-2"><b>Current guy:</b> <span id="current_guy">{{ $timer->current_guy }}</span></p>
              <p class="text-lg my-2"><b>Time per guy:</b> <span id="time_per_guy"></span></p>
              <p class="text-lg my-2"><b>Current guy remaining:</b> <span id="current_guy_remaining"></span></p>
              <p class="text-lg my-2"><b>End:</b> <input type="time" class="border rounded" name="end_time" value="{{ $timer->end_time }}"/></p>
            </div>
            <p class="text-lg my-2"><b>Message:</b><br><textarea class="border rounded w-full p-[0.5rem] min-h-[100px]" name="message">{{ $timer->message }}</textarea></p>
            <div class="flex my-[1rem] gap-[1rem]">
              <input type="submit" class="p-[0.5rem] uppercase rounded bg-cyan-800 text-white" value="Save" />
              <span class="p-[0.5rem] uppercase rounded text-white text-center bg-blue-400" id="warningButton">Warning</span>
              <audio preload="auto" class="hidden" id="warningAudio">
                <source src="{{ asset('audio/warning.wav') }}" type="audio/wav">
              </audio>
              <span class="p-[0.5rem] uppercase rounded text-white text-center bg-red-400" id="alarmButton">Alarm</span>
              <audio preload="auto" class="hidden" id="alarmAudio">
                <source src="{{ asset('audio/alarm.mp3') }}" type="audio/mp3">
              </audio>
            </div>
          </form>
          <hr class="my-[1rem]">
          <p class="p-[0.5rem] uppercase rounded bg-emerald-800 text-white text-center{{ ($timer->started)? ' hidden':'' }}" id="startButton">Start</p>
          <p class="p-[0.5rem] uppercase rounded bg-red-800 text-white text-center{{ ($timer->started)? '':' hidden' }}" id="stopButton">Stop</p>
          <p class="p-[0.5rem] uppercase rounded bg-black text-white text-center mt-[1rem]" id="resetButton">Reset</p>
        </div>
        <p class="my-[1rem]"><label><input type="checkbox" id="keepAwake"/> Keep screen from going to sleep</label></p>
      </div>
      <script src="{{ asset('js/app.js') }}" ></script>
    </body>
</html>

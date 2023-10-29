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
        <input type="hidden" id="started" value="{{$timer->started}}"/>
        <input type="hidden" id="over" value="0"/>
        <input type="hidden" id="current_guy_over" value="0"/>
        <h1 class="text-2xl underline mb-2">{{$timer->name}} Timerbot</h1>
        <p class="text-[green]{{ ($timer->started)? '':' hidden' }}" id="underway">Event is running</p>
        <p class="text-[red]{{ ($timer->started)? ' hidden':'' }}" id="notUnderway">Event is not not running</p>
        <div class="flex flex-wrap gap-4 my-2">
          <p><b>Guys:</b> <span id="guys">{{ $timer->guys }}</span></p>
          <p><b>Now:</b> <span id="now">{{ date('H:i:s') }}</span></p>
          <p><b>End:</b> <span id="end_time">{{ $timer->end_time }}</span></p>
          <p><b>Remaining:</b> <span id="remaining"></span></p>
          <p class="my-2 text-center text-2xl w-full"><b>Time per guy:</b> <span id="time_per_guy"></span></p>
        </div>
        <div id="runningInfo" class="{{ ($timer->started)? '':' hidden' }}">
          <hr>
          <p class="my-2 text-center text-3xl"><b>Current guy:</b> <span id="current_guy">{{ $timer->current_guy }}</span></p>
          <div class="my-2 text-center text-3xl text-emerald-800 font-bold" id="current_guy_remaining"></div>
          <hr>
        </div>
        <hr>
        <p class="my-2"><span id="message">{{ $timer->message }}</span></p>
        <p class="my-[1rem]"><label><input type="checkbox" id="keepAwake"/> Keep screen from going to sleep</label></p>
      </div>
      <script src="{{ asset('js/app.js') }}" ></script>
    </body>
</html>

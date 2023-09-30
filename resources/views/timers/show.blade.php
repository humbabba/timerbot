<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Timerbot</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

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
        <h1 class="text-2xl underline mb-2">{{$timer->name}} Timerbot</h1>
        @if($timer->started)
          <p class="text-[green]">Event is underway</p>
        @else
        <p class="text-[red]">Event has not yet started</p>
        @endif
        <div class="flex flex-wrap gap-4 my-2">
          <p><b>Guys:</b> {{ $timer->guys }}</p>
          <p><b>Now:</b> <span id="serverTime">{{ date('H:i:s') }}</span></p>
          <p><b>End:</b> <span id="endTime">{{ $timer->end_time }}</span></p>
          <p><b>Remaining:</b> <span id="timeRemaining">00:45:02</span></p>
        </div>
        <p class="text-lg my-2"><b>Time per guy:</b> <span id="timePerGuy"></span></p>
        <hr>
        <p class="my-2"><span id="message">{{ $timer->message }}</span></p>
        <hr>
        @if($timer->started)
        <p class="my-2 text-center text-3xl"><b>Current guy:</b> <span id="currentGuy">{{ $timer->current_guy }}</span></p>
        <hr>
        <div class="" id="timeLeftForCurrentGuy"></div>
        @endif
      </div>
    </body>
</html>

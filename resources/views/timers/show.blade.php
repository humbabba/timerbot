<x-layouts.app>
    @vite(['resources/js/timer-show.js'])

    @php
        $showConfig = [
            'id' => $timer->id,
            'name' => $timer->name,
            'end_time' => $timer->end_time,
            'participant_count' => $timer->participant_count,
            'participant_term' => $timer->participant_term,
            'participant_term_plural' => $timer->participant_term_plural,
            'state_url' => route('timers.state', $timer),
        ];
    @endphp

    <div class="p-4 md:p-8 max-w-3xl mx-auto" id="timer-show">
        <!-- Action Buttons -->
        @auth
            @if($timer->canRun(auth()->user()) || $timer->canManage(auth()->user()))
                <div class="flex justify-center gap-4 mb-6">
                    @if($timer->canRun(auth()->user()))
                        <a href="{{ route('timers.run', $timer) }}" class="btn btn-primary text-lg px-8 py-3 no-underline">
                            Run Timer
                        </a>
                    @endif
                    @if($timer->canManage(auth()->user()))
                        <a href="{{ route('timers.edit', $timer) }}" class="btn bg-timerbot-teal text-timerbot-black hover:bg-timerbot-teal/80 text-lg px-8 py-3 no-underline">
                            Edit Timer
                        </a>
                    @endif
                </div>
            @endif
        @endauth

        <!-- Timer Name -->
        <h1 class="text-center text-3xl md:text-4xl mb-4" style="font-family: var(--font-display);">{{ $timer->name }}</h1>

        <!-- Running Status -->
        <div class="text-center mb-6">
            <span id="status-badge" class="text-lg font-semibold" style="font-family: var(--font-display);">
                Loading...
            </span>
        </div>

        <!-- Stats Bar -->
        <div class="flex flex-wrap justify-center gap-x-6 gap-y-1 text-sm text-text-muted mb-2 tabular-nums">
            <span><span id="total-participants">{{ $timer->participant_count }}</span> {{ $timer->participant_term_plural }}</span>
            <span>Now: <span id="current-time">--:--</span></span>
            <span>Ends: <span id="end-time-display">{{ \Carbon\Carbon::parse($timer->end_time)->format('g:i A') }}</span></span>
            <span>Remaining: <span id="meeting-remaining">--:--</span></span>
        </div>

        <!-- Time Per Participant -->
        <div class="text-center mb-6">
            <div id="time-per-participant" class="text-3xl md:text-4xl font-bold text-timerbot-green tabular-nums" style="font-family: var(--font-display);">
                --:--
            </div>
            <div class="text-text-muted text-sm mt-1">per {{ $timer->participant_term }}</div>
        </div>

        <hr class="border-divider mb-6">

        <!-- Current Speaker -->
        <div id="speaker-section" class="text-center mb-6">
            <div id="speaker-label" class="text-xl text-timerbot-teal uppercase tracking-wider mb-2" style="font-family: var(--font-display);">
                —
            </div>
            <div id="speaker-time" class="text-7xl md:text-9xl font-bold tabular-nums text-text-muted" style="font-family: var(--font-display);">
                --:--
            </div>
        </div>

        <hr class="border-divider mb-6">

        <!-- Message -->
        @if($timer->message)
            <div class="mb-6 p-4 bg-timerbot-panel rounded-sm border border-divider prose prose-invert max-w-none text-text text-sm">
                {!! $timer->message !!}
            </div>
        @endif

        <!-- Wake Lock -->
        <div class="text-center">
            <label class="inline-flex items-center gap-2 cursor-pointer text-text-muted text-sm">
                <input type="checkbox" id="wake-lock-toggle" class="accent-timerbot-green">
                <span>Keep screen awake</span>
            </label>
        </div>
        @guest
        <p class="mt-6 text-sm">Want a timer like this for your meeting? <a href="{{ route('register') }}" target="_blank">Register for free</a>.</p>
        @endguest
    </div>

    <script>
        window.timerShowConfig = @json($showConfig);
    </script>
</x-layouts.app>

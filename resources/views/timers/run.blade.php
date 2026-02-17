<x-layouts.app>
    @vite(['resources/js/timer-runner.js'])

    <div class="p-4 md:p-8 max-w-4xl mx-auto">
        <div class="flex flex-wrap justify-between items-start gap-4 mb-8">
            <div class="flex items-start gap-4 min-w-0">
                <div class="w-3 h-10 bg-timerbot-green rounded-none shrink-0 mt-1"></div>
                <h1 class="break-words">{{ $timer->name }}</h1>
            </div>
            <div class="flex gap-2 shrink-0">
                @if($timer->canManage(auth()->user()))
                    <a href="{{ route('timers.edit', $timer) }}" class="btn bg-timerbot-cyan text-timerbot-black hover:bg-timerbot-cyan/80 no-underline whitespace-nowrap">
                        Edit Timer
                    </a>
                @endif
                <a href="{{ route('timers.show', $timer) }}" class="btn bg-timerbot-panel-light text-text hover:bg-gray no-underline whitespace-nowrap">
                    Back to Timer
                </a>
            </div>
        </div>

        <!-- Meeting Countdown -->
        <div class="mb-8 p-4 md:p-6 bg-timerbot-panel-light rounded-sm text-center">
            <h2 class="text-timerbot-lavender text-sm uppercase tracking-wider mb-2" style="font-family: var(--font-display);">Meeting Time Remaining</h2>
            <div id="meeting-countdown" class="text-4xl md:text-5xl font-bold text-timerbot-orange tabular-nums" style="font-family: var(--font-display);">
                --:--:--
            </div>
            <div id="time-per-person" class="mt-2 text-text-muted text-sm">
                <span id="time-per-person-label"></span>
            </div>
            <div class="mt-4 flex flex-wrap justify-center items-center gap-4 md:gap-6 text-text-muted text-sm">
                <label class="flex items-center gap-2">
                    End Time
                    <input type="time" id="setting-end-time" value="{{ $timer->end_time }}" class="bg-timerbot-panel border border-gray rounded px-2 py-1 text-text text-sm w-30">
                </label>
                <label class="flex items-center gap-2">
                    Participants
                    <input type="number" id="setting-participants" value="{{ $timer->participant_count }}" min="1" max="999" class="bg-timerbot-panel border border-gray rounded px-2 py-1 text-text text-sm w-20 text-center">
                </label>
            </div>
            <div class="mt-3 flex flex-wrap justify-center items-center gap-2 text-text-muted text-xs">
                <span>Test:</span>
                <button type="button" onclick="timerApp.playSound('beep')" class="px-2 py-0.5 bg-timerbot-panel border border-gray rounded hover:border-timerbot-cyan transition-colors">Beep</button>
                <button type="button" onclick="timerApp.playSound('buzzer')" class="px-2 py-0.5 bg-timerbot-panel border border-gray rounded hover:border-timerbot-cyan transition-colors">Buzzer</button>
                <button type="button" onclick="timerApp.playSound('chime')" class="px-2 py-0.5 bg-timerbot-panel border border-gray rounded hover:border-timerbot-cyan transition-colors">Chime</button>
                <button type="button" onclick="timerApp.playSound('bell')" class="px-2 py-0.5 bg-timerbot-panel border border-gray rounded hover:border-timerbot-cyan transition-colors">Bell</button>
                <button type="button" onclick="timerApp.playSound('horn')" class="px-2 py-0.5 bg-timerbot-panel border border-gray rounded hover:border-timerbot-cyan transition-colors">Horn</button>
            </div>
        </div>

        <!-- Current Speaker Panel -->
        <div id="speaker-panel" class="hidden mb-8 p-4 md:p-8 bg-timerbot-panel-light rounded-sm text-center border-2 border-timerbot-green">
            <h2 class="text-timerbot-lavender text-sm uppercase tracking-wider mb-1" style="font-family: var(--font-display);">
                Speaker <span id="speaker-number">1</span> of <span id="speaker-total">{{ $timer->participant_count }}</span>
            </h2>
            <div id="speaker-countdown" class="text-6xl md:text-8xl font-bold text-timerbot-green tabular-nums my-4" style="font-family: var(--font-display);">
                --:--
            </div>
            <div id="speaker-status" class="text-text-muted text-sm"></div>
        </div>

        <!-- Controls -->
        <div class="mb-8 flex flex-wrap justify-center gap-3 md:gap-4">
            <button id="btn-start" onclick="timerApp.start()" class="btn btn-primary text-lg px-6 md:px-8 py-3">
                Start
            </button>
            <button id="btn-next" onclick="timerApp.nextSpeaker()" class="hidden btn bg-timerbot-orange text-timerbot-black hover:bg-timerbot-peach text-lg px-6 md:px-8 py-3">
                Next Speaker
            </button>
            <button id="btn-pause" onclick="timerApp.togglePause()" class="hidden btn bg-timerbot-panel text-timerbot-cyan hover:bg-timerbot-cyan hover:text-timerbot-black text-lg px-6 md:px-8 py-3">
                Pause
            </button>
            <button id="btn-stop" onclick="timerApp.stop()" class="hidden btn bg-timerbot-red text-white hover:bg-timerbot-red/80 text-lg px-6 md:px-8 py-3">
                Stop
            </button>
        </div>

        <!-- Speaker History Table -->
        <div id="history-section" class="hidden">
            <h2 class="text-timerbot-lavender text-sm uppercase tracking-wider mb-4" style="font-family: var(--font-display);">Speaker History</h2>
            <div class="overflow-x-auto rounded-sm border border-gray">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="p-4 text-left border-b border-gray">Speaker</th>
                            <th class="p-4 text-left border-b border-gray">Allotted</th>
                            <th class="p-4 text-left border-b border-gray">Actual</th>
                            <th class="p-4 text-left border-b border-gray">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Completed Message -->
        <div id="completed-section" class="hidden mb-8 p-4 md:p-8 bg-timerbot-panel-light rounded-sm text-center border-2 border-timerbot-green">
            <h2 class="text-timerbot-green text-2xl font-bold mb-2" style="font-family: var(--font-display);">Meeting Complete</h2>
            <p class="text-text-muted">All speakers have finished.</p>
        </div>
    </div>

    @php
        $timerConfig = [
            'name' => $timer->name,
            'end_time' => $timer->end_time,
            'participant_count' => $timer->participant_count,
            'warnings' => $timer->warnings ?? [],
            'state_url' => route('timers.state.update', $timer),
            'settings_url' => route('timers.settings.update', $timer),
            'csrf_token' => csrf_token(),
        ];
    @endphp
    <script>
        window.timerConfig = @json($timerConfig);
    </script>
</x-layouts.app>

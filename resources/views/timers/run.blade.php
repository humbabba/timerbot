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
                    <a href="{{ route('timers.edit', $timer) }}" class="btn bg-timerbot-teal text-timerbot-black hover:bg-timerbot-teal/80 no-underline whitespace-nowrap">
                        Edit Timer
                    </a>
                @endif
                <a href="{{ route('timers.show', $timer) }}" class="btn bg-timerbot-panel-light text-text hover:bg-divider no-underline whitespace-nowrap">
                    Back to Timer
                </a>
            </div>
        </div>

        <!-- Meeting Countdown -->
        <div class="mb-8 p-4 md:p-6 bg-timerbot-panel-light rounded-sm text-center">
            <h2 class="text-timerbot-teal text-sm uppercase tracking-wider mb-2" style="font-family: var(--font-display);">Meeting Time Remaining</h2>
            <div id="meeting-countdown" class="text-4xl md:text-5xl font-bold text-timerbot-green tabular-nums" style="font-family: var(--font-display);">
                --:--:--
            </div>
            <div id="time-per-person" class="mt-2 text-text-muted text-sm">
                <span id="time-per-person-label"></span>
            </div>
            <div class="mt-4 flex flex-wrap justify-center items-center gap-4 md:gap-6 text-text-muted text-sm">
                <label class="flex items-center gap-2">
                    End time
                    <input type="time" id="setting-end-time" value="{{ $timer->end_time }}" class="bg-timerbot-panel border border-divider rounded px-2 py-1 text-text text-sm w-30">
                </label>
                <label class="flex items-center gap-2">
                    {{ ucfirst($timer->participant_term_plural) }}
                    <input type="number" id="setting-participants" value="{{ $timer->participant_count }}" min="1" max="999" class="bg-timerbot-panel border border-divider rounded px-2 py-1 text-text text-sm w-20 text-center">
                </label>
            </div>
            @php $timerSounds = collect($timer->warnings ?? [])->pluck('sound')->unique()->values(); @endphp
            @if($timerSounds->count())
                <div class="mt-3 flex flex-wrap justify-center items-center gap-2 text-text-muted text-xs">
                    @foreach($timerSounds as $sound)
                        <button type="button" onclick="timerApp.playSound('{{ $sound }}')" class="px-2 py-0.5 bg-timerbot-panel border border-divider rounded hover:border-timerbot-teal transition-colors">{{ ucfirst($sound) }}</button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Current Speaker Panel -->
        <div id="speaker-panel" class="hidden mb-8 p-4 md:p-8 bg-timerbot-panel-light rounded-sm text-center border-2 border-timerbot-green">
            <h2 class="text-timerbot-teal text-sm uppercase tracking-wider mb-1" style="font-family: var(--font-display);">
                {{ ucfirst($timer->participant_term) }} <span id="speaker-number">1</span> of <span id="speaker-total">{{ $timer->participant_count }}</span>
            </h2>
            <div id="speaker-countdown" class="text-6xl md:text-8xl font-bold text-timerbot-green tabular-nums my-4" style="font-family: var(--font-display);">
                --:--
            </div>
            <div id="speaker-status" class="text-text-muted text-sm"></div>
        </div>

        <!-- Controls -->
        <div class="mb-2 flex flex-wrap justify-center gap-3 md:gap-4">
            <button id="btn-start" onclick="timerApp.start()" class="btn btn-primary text-lg px-6 md:px-8 py-3">
                Start
            </button>
            <button id="btn-prev" onclick="timerApp.previousSpeaker()" class="hidden btn bg-timerbot-panel-light text-timerbot-green hover:bg-timerbot-green hover:text-timerbot-black text-lg px-6 md:px-8 py-3">
                Prev {{ ucfirst($timer->participant_term) }}
            </button>
            <button id="btn-next" onclick="timerApp.nextSpeaker()" class="hidden btn bg-timerbot-green text-timerbot-black hover:bg-timerbot-lime text-lg px-6 md:px-8 py-3">
                Next {{ ucfirst($timer->participant_term) }}
            </button>
            <button id="btn-pause" onclick="timerApp.togglePause()" class="hidden btn bg-timerbot-panel text-timerbot-teal hover:bg-timerbot-teal hover:text-timerbot-black text-lg px-6 md:px-8 py-3">
                Pause
            </button>
            <button id="btn-stop" onclick="timerApp.stop()" class="hidden btn bg-timerbot-red text-white hover:bg-timerbot-red/80 text-lg px-6 md:px-8 py-3">
                Stop
            </button>
        </div>
        <div class="mb-8 flex justify-center">
            <button id="btn-undo" onclick="timerApp.undoNextSpeaker()" class="hidden btn bg-timerbot-panel-light text-text-muted hover:text-timerbot-lime hover:bg-timerbot-panel text-xs px-24 py-1.5 uppercase tracking-wider" style="font-family: var(--font-display);">
                Undo
            </button>
        </div>

        <!-- Speaker History Table -->
        <div id="history-section" class="hidden">
            <h2 class="text-timerbot-teal text-sm uppercase tracking-wider mb-4" style="font-family: var(--font-display);">{{ ucfirst($timer->participant_term) }} History</h2>
            <div class="overflow-x-auto rounded-sm border border-divider">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="p-4 text-left border-b border-divider">{{ ucfirst($timer->participant_term) }}</th>
                            <th class="p-4 text-left border-b border-divider">Allotted</th>
                            <th class="p-4 text-left border-b border-divider">Actual</th>
                            <th class="p-4 text-left border-b border-divider">Status</th>
                        </tr>
                    </thead>
                    <tbody id="history-body">
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Completed Message -->
        <div id="completed-section" class="hidden mb-8 p-4 md:p-8 bg-timerbot-panel-light rounded-sm text-center border-2 border-timerbot-green">
            <h2 class="text-timerbot-green text-2xl font-bold mb-2" style="font-family: var(--font-display);">Meeting complete</h2>
            <p class="text-text-muted">All {{ $timer->participant_term_plural }} have finished.</p>
        </div>

        <!-- Wake Lock -->
        <div class="text-center mt-4">
            <label class="inline-flex items-center gap-2 cursor-pointer text-text-muted text-sm">
                <input type="checkbox" id="wake-lock-toggle" class="accent-timerbot-green">
                <span>Keep screen awake</span>
            </label>
        </div>
    </div>

    @php
        $timerConfig = [
            'name' => $timer->name,
            'end_time' => $timer->end_time,
            'participant_count' => $timer->participant_count,
            'participant_term' => $timer->participant_term,
            'participant_term_plural' => $timer->participant_term_plural,
            'warnings' => $timer->warnings ?? [],
            'overtime_reset_minutes' => $timer->overtime_reset_minutes,
            'state_url' => route('timers.state.update', $timer),
            'settings_url' => route('timers.settings.update', $timer),
            'lock_release_url' => route('timers.lock.release', $timer),
            'csrf_token' => csrf_token(),
        ];
    @endphp
    <script>
        window.timerConfig = @json($timerConfig);
    </script>
</x-layouts.app>

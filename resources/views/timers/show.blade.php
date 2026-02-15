<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <div class="flex items-center gap-4">
                <div class="w-3 h-10 bg-timerbot-orange rounded-none"></div>
                <h1>{{ $timer->name }}</h1>
            </div>
            <div class="flex gap-2 shrink-0">
                @if(auth()->user()->hasPermission('timers.run'))
                    <a href="{{ route('timers.run', $timer) }}" class="btn btn-primary whitespace-nowrap">
                        Run Timer
                    </a>
                @endif
                <a href="{{ route('timers.index') }}" class="btn bg-timerbot-panel-light text-text hover:bg-gray no-underline whitespace-nowrap">
                    Back to Timers
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-timerbot-green/20 border border-timerbot-green/50 text-timerbot-green rounded-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-timerbot-panel-light rounded-sm overflow-hidden">
            <div class="h-1 bg-timerbot-green"></div>
            <div class="p-6 space-y-6">
                <div>
                    <h2 class="text-timerbot-lavender text-sm uppercase tracking-wider mb-2" style="font-family: var(--font-display);">End Time</h2>
                    <p class="text-text text-lg">{{ \Carbon\Carbon::parse($timer->end_time)->format('g:i A') }}</p>
                </div>

                <div>
                    <h2 class="text-timerbot-lavender text-sm uppercase tracking-wider mb-2" style="font-family: var(--font-display);">Participants</h2>
                    <p class="text-text text-lg">{{ $timer->participant_count }}</p>
                </div>

                <div>
                    <h2 class="text-timerbot-lavender text-sm uppercase tracking-wider mb-2" style="font-family: var(--font-display);">Warnings</h2>
                    @if($timer->warnings && count($timer->warnings))
                        <div class="space-y-2">
                            @foreach($timer->warnings as $warning)
                                <div class="flex items-center gap-3 p-3 bg-timerbot-panel rounded-sm border border-gray">
                                    <span class="badge badge-peach">{{ $warning['seconds_before'] }}s</span>
                                    <span class="text-text capitalize">{{ $warning['sound'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-text-muted">No warnings configured.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            @if(auth()->user()->hasPermission('timers.edit'))
                <a href="{{ route('timers.edit', $timer) }}" class="btn btn-secondary">
                    Edit Timer
                </a>
            @endif
        </div>
    </div>
</x-layouts.app>

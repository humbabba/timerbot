<x-layouts.app>
    <div class="p-4 md:p-8 max-w-2xl mx-auto text-center">
        <div class="mb-8">
            <div class="w-3 h-10 bg-timerbot-red rounded-none mx-auto mb-4"></div>
            <h1 class="text-2xl md:text-3xl mb-2">Timer In Use</h1>
            <p class="text-text-muted text-lg">{{ $timer->name }}</p>
        </div>

        <div class="p-6 bg-timerbot-panel-light rounded-sm border border-dark-green mb-8">
            <p class="text-lg mb-2">
                This timer is currently being run by
                <span class="text-timerbot-mint font-semibold">{{ $timer->lockedByUser?->name ?? 'another user' }}</span>.
            </p>
            <p class="text-text-muted text-sm">
                Only one person can run a timer at a time. The lock will automatically expire if the other user disconnects.
            </p>
        </div>

        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('timers.show', $timer) }}" class="btn bg-timerbot-panel-light text-text hover:bg-dark-green no-underline px-6 py-3">
                View Timer (Read-Only)
            </a>
            <a href="{{ route('timers.run', $timer) }}" class="btn btn-primary no-underline px-6 py-3">
                Try Again
            </a>
        </div>
    </div>
</x-layouts.app>

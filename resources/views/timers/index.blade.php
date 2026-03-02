<x-layouts.app>
    @if(auth()->user()?->isAppAdmin())
        <script>
            (function() {
                var pref = localStorage.getItem('timers_show_all');
                if (pref === '1' && !new URLSearchParams(window.location.search).has('all')) {
                    var url = new URL(window.location);
                    url.searchParams.set('all', '1');
                    window.location.replace(url);
                }
            })();
        </script>
    @endif
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Timers</h1>
            <div class="flex gap-2 items-center">
                @if(auth()->user()?->isAppAdmin())
                    <a href="{{ route('timers.index', array_merge(request()->except('all'), $showAll ? [] : ['all' => 1])) }}"
                       class="btn btn-secondary whitespace-nowrap"
                       x-data
                       x-on:click="localStorage.setItem('timers_show_all', '{{ $showAll ? '0' : '1' }}')">
                        {{ $showAll ? 'My Timers' : 'All Timers' }}
                    </a>
                @endif
                @if(auth()->user()?->hasPermission('timers.create'))
                    <a href="{{ route('timers.create') }}" class="btn btn-primary">
                        Add Timer
                    </a>
                @endif
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-timerbot-green/20 border border-timerbot-green/50 text-timerbot-green rounded-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-timerbot-panel-light rounded-sm">
            <form method="GET" action="{{ route('timers.index') }}" class="flex flex-wrap gap-4 items-end">
                @if($showAll)
                    <input type="hidden" name="all" value="1">
                @endif
                <div>
                    <label class="block font-semibold text-timerbot-mint uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="bg-timerbot-panel border border-dark-green rounded-sm px-4 py-2 text-text min-w-[200px]">
                </div>
                <div>
                    <label class="block font-semibold text-timerbot-mint uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="bg-timerbot-panel border border-dark-green rounded-sm px-4 py-2 text-text">
                </div>
                <div>
                    <label class="block font-semibold text-timerbot-mint uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="bg-timerbot-panel border border-dark-green rounded-sm px-4 py-2 text-text">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    @if(request()->hasAny(['search', 'from', 'to']))
                        <a href="{{ route('timers.index', $showAll ? ['all' => 1] : []) }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-sm border border-dark-green">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="p-4 text-left border-b border-dark-green">Name</th>
                        <th class="p-4 text-left border-b border-dark-green">Visibility</th>
                        <th class="p-4 text-left border-b border-dark-green">End Time</th>
                        <th class="p-4 text-left border-b border-dark-green">Participants</th>
                        <th class="p-4 text-left border-b border-dark-green">Group</th>
                        <th class="p-4 text-left border-b border-dark-green w-80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timers as $timer)
                        <tr class="hover:bg-timerbot-panel-light transition-colors">
                            <td class="p-4 border-b border-dark-green/50">
                                <a href="{{ route('timers.show', $timer) }}" class="text-timerbot-neon hover:text-timerbot-lime font-semibold">{{ $timer->name }}</a>
                            </td>
                            <td class="p-4 border-b border-dark-green/50">
                                @if($timer->isPublic())
                                    <span class="badge badge-green text-xs">Public</span>
                                @else
                                    <span class="badge badge-lime text-xs">Private</span>
                                @endif
                            </td>
                            <td class="p-4 border-b border-dark-green/50 text-text-muted">
                                {{ \Carbon\Carbon::parse($timer->end_time)->format('g:i A') }}
                            </td>
                            <td class="p-4 border-b border-dark-green/50 text-text-muted">
                                {{ $timer->participant_count }}
                            </td>
                            <td class="p-4 border-b border-dark-green/50 text-text-muted">
                                {{ $timer->group?->name ?? '—' }}
                            </td>
                            <td class="p-4 border-b border-dark-green/50">
                                <div class="flex gap-2">
                                    @if(auth()->check() && $timer->canRun(auth()->user()))
                                        <a href="{{ route('timers.run', $timer) }}" class="px-3 py-1.5 rounded-none bg-timerbot-green text-timerbot-black hover:bg-timerbot-green/80 transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Run
                                        </a>
                                    @endif
                                    <a href="{{ route('timers.show', $timer) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-mint hover:bg-timerbot-mint hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                        View
                                    </a>
                                    @if(auth()->check() && $timer->canManage(auth()->user()))
                                        <a href="{{ route('timers.edit', $timer) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-mint hover:bg-timerbot-mint hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()?->hasPermission('timers.create'))
                                        <form method="POST" action="{{ route('timers.copy', $timer) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-lime hover:bg-timerbot-lime hover:text-timerbot-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                                Copy
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->check() && $timer->canManage(auth()->user()))
                                        <form method="POST" action="{{ route('timers.destroy', $timer) }}" id="delete-timer-{{ $timer->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                x-data
                                                class="px-3 py-1.5 rounded-none bg-timerbot-red text-white hover:bg-timerbot-red/80 transition-all text-xs uppercase tracking-wider"
                                                style="font-family: var(--font-display);"
                                                x-on:click="$dispatch('confirm-delete', {
                                                    title: 'Delete Timer',
                                                    message: 'Are you sure you want to delete Timer #{{ $timer->id }} (' + {{ Js::from($timer->name) }} + ')? This will move it to the trash.',
                                                    formId: 'delete-timer-{{ $timer->id }}'
                                                })"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-8 text-center text-text-muted">
                                No timers found. Create your first timer to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $timers->links() }}
        </div>
    </div>
</x-layouts.app>

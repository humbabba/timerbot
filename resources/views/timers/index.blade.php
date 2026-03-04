<x-layouts.app>
    @auth
        <script>
            (function() {
                var pref = localStorage.getItem('timers_show_mine');
                if (pref === '1' && !new URLSearchParams(window.location.search).has('mine')) {
                    var url = new URL(window.location);
                    url.searchParams.set('mine', '1');
                    window.location.replace(url);
                }
            })();
        </script>
    @endauth
    <div class="p-4 md:p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Timers</h1>
            <div class="flex gap-2 items-center">
                @auth
                    <a href="{{ route('timers.index', array_merge(request()->except('mine'), $showMine ? [] : ['mine' => 1])) }}"
                       class="btn btn-secondary whitespace-nowrap"
                       x-data
                       x-on:click="localStorage.setItem('timers_show_mine', '{{ $showMine ? '0' : '1' }}')">
                        {{ $showMine ? 'All Timers' : 'My Timers' }}
                    </a>
                @endauth
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
                @if($showMine)
                    <input type="hidden" name="mine" value="1">
                @endif
                @if(request('sort'))
                    <input type="hidden" name="sort" value="{{ request('sort') }}">
                    <input type="hidden" name="direction" value="{{ request('direction') }}">
                @endif
                <div>
                    <label class="block font-semibold text-timerbot-teal uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="bg-timerbot-panel border border-divider rounded-sm px-4 py-2 text-text min-w-[200px]">
                </div>
                <div>
                    <label class="block font-semibold text-timerbot-teal uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="bg-timerbot-panel border border-divider rounded-sm px-4 py-2 text-text">
                </div>
                <div>
                    <label class="block font-semibold text-timerbot-teal uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="bg-timerbot-panel border border-divider rounded-sm px-4 py-2 text-text">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    @if(request()->hasAny(['search', 'from', 'to', 'sort']))
                        <a href="{{ route('timers.index', $showMine ? ['mine' => 1] : []) }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-sm border border-divider">
            <table class="w-full">
                <thead>
                    <tr>
                        <x-sort-header column="name" label="Name" :sort="$sort" :direction="$direction" />
                        <th class="p-4 text-left border-b border-divider">Status</th>
                        <x-sort-header column="visibility" label="Visibility" :sort="$sort" :direction="$direction" />
                        <x-sort-header column="end_time" label="End time" :sort="$sort" :direction="$direction" />
                        <x-sort-header column="participant_count" label="Participants" :sort="$sort" :direction="$direction" />
                        <th class="p-4 text-left border-b border-divider">Group</th>
                        <th class="p-4 text-left border-b border-divider w-80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timers as $timer)
                        <tr class="hover:bg-timerbot-panel-light transition-colors">
                            <td class="p-4 border-b border-divider/50">
                                <a href="{{ route('timers.show', $timer) }}" class="text-timerbot-green hover:text-timerbot-lime font-semibold">{{ $timer->name }}</a>
                            </td>
                            <td class="p-4 border-b border-divider/50"
                                x-data="timerStatus({{ Js::from($timer->run_state) }}, {{ Js::from($timer->end_time) }}, {{ $timer->overtime_reset_minutes }}, '{{ route('timers.state', $timer) }}')"
                                x-init="start()"
                            >
                                <span x-text="display" :class="colorClass" class="font-mono text-sm"></span>
                            </td>
                            <td class="p-4 border-b border-divider/50">
                                @if($timer->isPublic())
                                    <span class="badge badge-green text-xs">Public</span>
                                @else
                                    <span class="badge badge-lime text-xs">Private</span>
                                @endif
                            </td>
                            <td class="p-4 border-b border-divider/50 text-text-muted">
                                {{ \Carbon\Carbon::parse($timer->end_time)->format('g:i A') }}
                            </td>
                            <td class="p-4 border-b border-divider/50 text-text-muted">
                                {{ $timer->participant_count }}
                            </td>
                            <td class="p-4 border-b border-divider/50 text-text-muted">
                                {{ $timer->group?->name ?? '—' }}
                            </td>
                            <td class="p-4 border-b border-divider/50">
                                <div class="flex gap-2">
                                    @if(auth()->check() && $timer->canRun(auth()->user()))
                                        <a href="{{ route('timers.run', $timer) }}" class="px-3 py-1.5 rounded-none bg-timerbot-green text-timerbot-black hover:bg-timerbot-green/80 transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Run
                                        </a>
                                    @endif
                                    <a href="{{ route('timers.show', $timer) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-teal hover:bg-timerbot-teal hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                        View
                                    </a>
                                    @if(auth()->check() && $timer->canManage(auth()->user()))
                                        <a href="{{ route('timers.edit', $timer) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-teal hover:bg-timerbot-teal hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
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
                            <td colspan="7" class="p-8 text-center text-text-muted">
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
    <script>
    function timerStatus(runState, endTime, overtimeMinutes, stateUrl) {
        return {
            display: '',
            colorClass: 'text-text-muted',
            interval: null,
            pollInterval: null,
            runState: runState,
            endTime: endTime,
            overtimeLimitMs: overtimeMinutes * 60000,
            stateUrl: stateUrl,
            start() {
                this.tick();
                this.interval = setInterval(() => this.tick(), 1000);
                this.pollInterval = setInterval(() => this.poll(), 5000);
            },
            async poll() {
                try {
                    const res = await fetch(this.stateUrl, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!res.ok) return;
                    const state = await res.json();
                    this.runState = (!state || !state.status || state.status === 'idle' || state.status === 'completed') ? null : state;
                } catch (e) {}
            },
            tick() {
                const state = this.runState;
                if (!state || !state.status || state.status === 'idle') {
                    this.display = 'Idle';
                    this.colorClass = 'text-text-muted';
                    return;
                }

                const endMs = state.end_time_ms || this.endTimeToMs();
                if (!endMs) {
                    this.display = state.status === 'paused' ? 'Paused' : 'Running';
                    this.colorClass = state.status === 'paused' ? 'text-timerbot-green' : 'text-timerbot-green';
                    return;
                }

                const now = Date.now();

                // Check overtime reset limit
                if (now > endMs + this.overtimeLimitMs) {
                    this.runState = null;
                    this.display = 'Idle';
                    this.colorClass = 'text-text-muted';
                    if (this.interval) clearInterval(this.interval);
                    return;
                }

                let remainMs;

                if (state.status === 'paused') {
                    remainMs = state.paused_remaining_ms || (endMs - now);
                    this.display = 'Paused | ' + this.fmt(remainMs);
                    this.colorClass = 'text-timerbot-green';
                    return;
                }

                // Running
                remainMs = endMs - now;
                this.display = this.fmt(remainMs);
                this.colorClass = remainMs >= 0 ? 'text-timerbot-green' : 'text-timerbot-red';
            },
            fmt(ms) {
                const neg = ms < 0;
                const total = Math.abs(Math.floor(ms / 1000));
                const h = Math.floor(total / 3600);
                const m = Math.floor((total % 3600) / 60);
                const s = total % 60;
                const pad = n => String(n).padStart(2, '0');
                const time = h > 0
                    ? h + ':' + pad(m) + ':' + pad(s)
                    : pad(m) + ':' + pad(s);
                return neg ? '-' + time : time;
            },
            endTimeToMs() {
                if (!this.endTime) return null;
                const parts = this.endTime.split(':');
                const now = new Date();
                now.setHours(parseInt(parts[0]), parseInt(parts[1]), parts[2] ? parseInt(parts[2]) : 0, 0);
                return now.getTime();
            },
            destroy() {
                if (this.interval) clearInterval(this.interval);
                if (this.pollInterval) clearInterval(this.pollInterval);
            }
        };
    }
    </script>
</x-layouts.app>

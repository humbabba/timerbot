<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Activity Log</h1>
        </div>

        <div class="mb-6 p-4 bg-timerbot-panel-light rounded-sm">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block font-semibold text-timerbot-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text min-w-[200px]">
                </div>
                <div>
                    <label for="type" class="block font-semibold text-timerbot-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Type</label>
                    <select name="type" id="type" class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text min-w-[150px]">
                        <option value="">All types</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="action" class="block font-semibold text-timerbot-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Action</label>
                    <select name="action" id="action" class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text min-w-[150px]">
                        <option value="">All actions</option>
                        <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                        <option value="run" {{ request('action') === 'run' ? 'selected' : '' }}>Run</option>
                    </select>
                </div>
                <div>
                    <label for="user" class="block font-semibold text-timerbot-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">User</label>
                    <select name="user" id="user" class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text min-w-[150px]">
                        <option value="">All users</option>
                        @foreach($users as $id => $name)
                            <option value="{{ $id }}" {{ request('user') == $id ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block font-semibold text-timerbot-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text">
                </div>
                <div>
                    <label class="block font-semibold text-timerbot-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    @if(request()->hasAny(['search', 'type', 'action', 'user', 'from', 'to']))
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        @if($logs->count() > 0)
            <div class="overflow-x-auto rounded-sm border border-gray">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="p-4 text-left border-b border-gray">Date/Time</th>
                            <th class="p-4 text-left border-b border-gray">Action</th>
                            <th class="p-4 text-left border-b border-gray">Type</th>
                            <th class="p-4 text-left border-b border-gray">Model</th>
                            <th class="p-4 text-left border-b border-gray">User</th>
                            <th class="p-4 text-left border-b border-gray">Changes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr class="hover:bg-timerbot-panel-light transition-colors">
                                <td class="p-4 border-b border-gray/50">
                                    <span class="text-text">{{ $log->created_at->format('M j, Y g:i A') }}</span>
                                    <span class="text-text-muted text-sm block">({{ $log->created_at->diffForHumans() }})</span>
                                </td>
                                <td class="p-4 border-b border-gray/50">
                                    <span class="badge {{ $log->getActionColorClass() }}">
                                        {{ ucfirst($log->action) }}
                                    </span>
                                </td>
                                <td class="p-4 border-b border-gray/50">
                                    <span class="badge badge-lavender">
                                        {{ $log->getModelName() }}
                                    </span>
                                </td>
                                <td class="p-4 border-b border-gray/50">
                                    @if($log->loggableExists())
                                        @php
                                            $modelRoutes = [
                                                'Node' => 'nodes.show',
                                                'User' => 'users.show',
                                                'Wave' => 'waves.show',
                                                'Role' => 'roles.edit',
                                            ];
                                            $routeName = $modelRoutes[$log->getModelName()] ?? null;
                                        @endphp
                                        @if($routeName)
                                            <a href="{{ route($routeName, $log->loggable_id) }}" class="text-timerbot-peach hover:text-timerbot-orange">
                                                {{ $log->getLoggableDisplayName() }}
                                            </a>
                                        @else
                                            <span class="text-timerbot-peach">{{ $log->getLoggableDisplayName() }}</span>
                                        @endif
                                        <span class="text-text-muted text-sm">#{{ $log->loggable_id }}</span>
                                    @else
                                        <span class="text-text-muted">{{ $log->getLoggableDisplayName() }}</span>
                                        <span class="text-text-muted text-sm">#{{ $log->loggable_id }}</span>
                                        <span class="text-timerbot-red text-xs">(deleted)</span>
                                    @endif
                                </td>
                                <td class="p-4 border-b border-gray/50">
                                    @if($log->user_id)
                                        @if($log->userExists())
                                            <a href="{{ route('users.edit', $log->user_id) }}" class="text-timerbot-cyan hover:text-timerbot-blue">
                                                {{ $log->user_name }}
                                            </a>
                                        @else
                                            <span class="text-text-muted">{{ $log->user_name ?? 'Unknown' }}</span>
                                            <span class="text-timerbot-red text-xs">(deleted)</span>
                                        @endif
                                    @else
                                        <span class="text-text-muted">System</span>
                                    @endif
                                </td>
                                <td class="p-4 border-b border-gray/50">
                                    @if($log->action === 'updated' && is_array($log->changes))
                                        <span class="text-text-muted text-sm">
                                            {{ count($log->changes) }} field(s)
                                        </span>
                                    @elseif($log->action === 'run' && is_array($log->changes))
                                        @if(isset($log->changes['node']))
                                            <span class="text-text-muted text-sm">
                                                Step {{ $log->changes['step'] ?? '?' }}: {{ $log->changes['node'] }}
                                            </span>
                                        @endif
                                    @endif
                                    <a href="{{ route('activity-logs.show', $log) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-blue hover:bg-timerbot-blue hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline ml-2" style="font-family: var(--font-display);">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $logs->links() }}
            </div>
        @else
            <div class="text-center py-16 text-text-muted">
                <div class="w-16 h-16 mx-auto mb-4 rounded-none bg-timerbot-panel-light flex items-center justify-center">
                    <svg class="w-8 h-8 text-timerbot-lavender" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <p class="text-lg" style="font-family: var(--font-display);">No activity logged yet</p>
            </div>
        @endif
    </div>
</x-layouts.app>

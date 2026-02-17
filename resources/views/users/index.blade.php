<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Users</h1>
            @if(auth()->user()->hasPermission('users.create'))
                <a href="{{ route('users.create') }}" class="btn btn-primary">
                    Add User
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-timerbot-green/20 border border-timerbot-green/50 text-timerbot-green rounded-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-timerbot-panel-light rounded-sm">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block font-semibold text-timerbot-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text min-w-[200px]">
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
                    @if(request()->hasAny(['search', 'from', 'to']))
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-sm border border-gray">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="p-4 text-left border-b border-gray">Name</th>
                        <th class="p-4 text-left border-b border-gray">Email</th>
                        <th class="p-4 text-left border-b border-gray">Role</th>
                        <th class="p-4 text-left border-b border-gray">Created</th>
                        <th class="p-4 text-left border-b border-gray">Last Login</th>
                        <th class="p-4 text-left border-b border-gray w-48">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="hover:bg-timerbot-panel-light transition-colors">
                            <td class="p-4 border-b border-gray/50">
                                <a href="{{ route('users.show', $user) }}" class="flex items-center gap-3 no-underline text-text hover:text-timerbot-cyan transition-colors">
                                    <x-avatar :user="$user" :size="8" />
                                    {{ $user->name }}
                                </a>
                            </td>
                            <td class="p-4 border-b border-gray/50 text-text-muted">{{ $user->email }}</td>
                            <td class="p-4 border-b border-gray/50">
                                @foreach($user->roles as $role)
                                    <span class="badge badge-lavender mr-1">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="p-4 border-b border-gray/50 text-text-muted text-sm">
                                {{ $user->created_at?->format('M j, Y') ?? '—' }}
                            </td>
                            <td class="p-4 border-b border-gray/50 text-text-muted text-sm">
                                {{ $user->last_login_at?->format('M j, Y g:i A') ?? 'Never' }}
                            </td>
                            <td class="p-4 border-b border-gray/50">
                                <div class="flex gap-2">
                                    @if(auth()->user()->hasPermission('users.edit') && !array_diff($user->roles->pluck('id')->toArray(), $assignableRoleIds))
                                        <a href="{{ route('users.edit', $user) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-cyan hover:bg-timerbot-cyan hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('users.delete'))
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" id="delete-user-{{ $user->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                x-data
                                                class="px-3 py-1.5 rounded-none bg-timerbot-red text-white hover:bg-timerbot-red/80 transition-all text-xs uppercase tracking-wider"
                                                style="font-family: var(--font-display);"
                                                x-on:click="$dispatch('confirm-delete', {
                                                    title: 'Delete User',
                                                    message: 'Are you sure you want to delete User #{{ $user->id }} ({{ $user->name }})? This will move it to the trash.',
                                                    formId: 'delete-user-{{ $user->id }}'
                                                })"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</x-layouts.app>

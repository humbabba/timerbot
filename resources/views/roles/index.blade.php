<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Roles</h1>
            @if(auth()->user()->hasPermission('roles.create'))
                <a href="{{ route('roles.create') }}" class="btn btn-primary">
                    Add Role
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-timerbot-green/20 border border-timerbot-green/50 text-timerbot-green rounded-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-x-auto rounded-sm border border-divider">
            <table class="w-full">
                <thead>
                    <tr>
                        <x-sort-header column="name" label="Name" :sort="$sort" :direction="$direction" />
                        <x-sort-header column="description" label="Description" :sort="$sort" :direction="$direction" />
                        <th class="p-4 text-left border-b border-divider">Permissions</th>
                        <th class="p-4 text-left border-b border-divider w-48">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr class="hover:bg-timerbot-panel-light transition-colors">
                            <td class="p-4 border-b border-divider/50">
                                <span class="text-timerbot-green font-semibold">{{ $role->name }}</span>
                            </td>
                            <td class="p-4 border-b border-divider/50 text-text-muted">{{ $role->description }}</td>
                            <td class="p-4 border-b border-divider/50">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($role->permissions as $permission)
                                        <span class="badge badge-lime">
                                            {{ $permission->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="p-4 border-b border-divider/50">
                                <div class="flex gap-2">
                                    @if(auth()->user()->hasPermission('roles.edit'))
                                        <a href="{{ route('roles.edit', $role) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-teal hover:bg-timerbot-teal hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('roles.create'))
                                        <form method="POST" action="{{ route('roles.copy', $role) }}">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-green hover:bg-timerbot-green hover:text-timerbot-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                                Copy
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->hasPermission('roles.delete'))
                                        <form method="POST" action="{{ route('roles.destroy', $role) }}" id="delete-role-{{ $role->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                x-data
                                                class="px-3 py-1.5 rounded-none bg-timerbot-red text-white hover:bg-timerbot-red/80 transition-all text-xs uppercase tracking-wider"
                                                style="font-family: var(--font-display);"
                                                x-on:click="$dispatch('confirm-delete', {
                                                    title: 'Delete Role',
                                                    message: 'Are you sure you want to delete Role #{{ $role->id }} (' + {{ Js::from($role->name) }} + ')? This will move it to the trash.',
                                                    formId: 'delete-role-{{ $role->id }}'
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
    </div>
</x-layouts.app>

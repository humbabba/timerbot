<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Trash</h1>
            @if($trashItems->count() > 0 && auth()->user()->hasPermission('trash.delete'))
                <form method="POST" action="{{ route('trash.empty', ['type' => request('type')]) }}" id="empty-trash-form">
                    @csrf
                    @method('DELETE')
                    <button
                        type="button"
                        x-data
                        class="btn btn-danger"
                        x-on:click="$dispatch('confirm-delete', {
                            title: 'Empty Trash',
                            message: 'Are you sure you want to permanently delete all {{ request('type') ? class_basename(request('type')) : '' }} items in trash? This cannot be undone.',
                            formId: 'empty-trash-form'
                        })"
                    >
                        Empty Trash
                    </button>
                </form>
            @endif
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
            <form method="GET" action="{{ route('trash.index') }}" class="flex flex-wrap gap-4 items-end">
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
                    <label for="type" class="block font-semibold text-timerbot-teal uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Type</label>
                    <select name="type" id="type" class="bg-timerbot-panel border border-divider rounded-sm px-4 py-2 text-text min-w-[150px]">
                        <option value="">All types</option>
                        @foreach($types as $value => $label)
                            <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
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
                    @if(request()->hasAny(['search', 'type', 'from', 'to', 'sort']))
                        <a href="{{ route('trash.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        @if($trashItems->count() > 0)
            <div class="overflow-x-auto rounded-sm border border-divider">
                <table class="w-full">
                    <thead>
                        <tr>
                            <x-sort-header column="trashable_type" label="Type" :sort="$sort" :direction="$direction" />
                            <th class="p-4 text-left border-b border-divider">Name</th>
                            <th class="p-4 text-left border-b border-divider">Deleted By</th>
                            <x-sort-header column="deleted_at" label="Deleted At" :sort="$sort" :direction="$direction" />
                            <th class="p-4 text-left border-b border-divider w-56">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trashItems as $item)
                            <tr class="hover:bg-timerbot-panel-light transition-colors">
                                <td class="p-4 border-b border-divider/50">
                                    <span class="badge badge-teal">
                                        {{ $item->model_name }}
                                    </span>
                                </td>
                                <td class="p-4 border-b border-divider/50 text-timerbot-lime">{{ $item->display_name }}</td>
                                <td class="p-4 border-b border-divider/50 text-text-muted">
                                    {{ $item->deletedByUser?->name ?? 'System' }}
                                </td>
                                <td class="p-4 border-b border-divider/50">
                                    <span class="text-text">{{ $item->deleted_at->format('M j, Y g:i A') }}</span>
                                    <span class="text-text-muted text-sm block">({{ $item->deleted_at->diffForHumans() }})</span>
                                </td>
                                <td class="p-4 border-b border-divider/50">
                                    <div class="flex gap-2">
                                        @if(auth()->user()->hasPermission('trash.view'))
                                            <a href="{{ route('trash.show', $item) }}" class="px-3 py-1.5 rounded-none bg-timerbot-panel-light text-timerbot-lime hover:bg-timerbot-lime hover:text-timerbot-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                                View
                                            </a>
                                        @endif
                                        @if(auth()->user()->hasPermission('trash.restore'))
                                            <form method="POST" action="{{ route('trash.restore', $item) }}">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 rounded-none bg-timerbot-panel border border-timerbot-green/50 text-timerbot-green hover:bg-timerbot-green hover:text-timerbot-black hover:border-timerbot-green transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                                    Restore
                                                </button>
                                            </form>
                                        @endif
                                        @if(auth()->user()->hasPermission('trash.delete'))
                                            <form method="POST" action="{{ route('trash.destroy', $item) }}" id="delete-trash-{{ $item->id }}">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="button"
                                                    x-data
                                                    class="px-3 py-1.5 rounded-none bg-timerbot-red text-white hover:bg-timerbot-red/80 transition-all text-xs uppercase tracking-wider"
                                                    style="font-family: var(--font-display);"
                                                    x-on:click="$dispatch('confirm-delete', {
                                                        title: 'Permanently Delete',
                                                        message: 'Are you sure you want to permanently delete {{ $item->model_name }} #{{ $item->trashable_id }} (' + {{ Js::from($item->display_name) }} + ')? This cannot be undone.',
                                                        formId: 'delete-trash-{{ $item->id }}'
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
                {{ $trashItems->links() }}
            </div>
        @else
            <div class="text-center py-16 text-text-muted">
                <div class="w-16 h-16 mx-auto mb-4 rounded-none bg-timerbot-panel-light flex items-center justify-center">
                    <svg class="w-8 h-8 text-timerbot-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <p class="text-lg" style="font-family: var(--font-display);">Trash is empty</p>
            </div>
        @endif
    </div>
</x-layouts.app>

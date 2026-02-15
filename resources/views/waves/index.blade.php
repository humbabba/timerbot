<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Waves</h1>
            @if(auth()->user()->hasPermission('waves.create'))
                <a href="{{ route('waves.create') }}" class="btn btn-primary">
                    Add Wave
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-cortex-green/20 border border-cortex-green/50 text-cortex-green rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-cortex-panel-light rounded-xl">
            <form method="GET" action="{{ route('waves.index') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block font-semibold text-cortex-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="bg-cortex-panel border border-gray rounded-lg px-4 py-2 text-text min-w-[200px]">
                </div>
                <div>
                    <label class="block font-semibold text-cortex-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="bg-cortex-panel border border-gray rounded-lg px-4 py-2 text-text">
                </div>
                <div>
                    <label class="block font-semibold text-cortex-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="bg-cortex-panel border border-gray rounded-lg px-4 py-2 text-text">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    @if(request()->hasAny(['search', 'from', 'to']))
                        <a href="{{ route('waves.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="p-4 text-left border-b border-gray">Name</th>
                        <th class="p-4 text-left border-b border-gray">Nodes</th>
                        <th class="p-4 text-left border-b border-gray w-72">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waves as $wave)
                        <tr class="hover:bg-cortex-panel-light transition-colors">
                            <td class="p-4 border-b border-gray/50">
                                <div class="flex items-center gap-2">
                                    @if(auth()->user()->hasPermission('waves.view'))
                                        <button
                                            x-data="{ favorited: {{ in_array($wave->id, $favoriteWaveIds) ? 'true' : 'false' }}, saving: false }"
                                            x-on:click.stop="
                                                if (saving) return;
                                                saving = true;
                                                fetch('{{ route('waves.toggle-favorite', $wave) }}', {
                                                    method: 'POST',
                                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                                                })
                                                .then(r => r.json())
                                                .then(data => { favorited = data.is_favorite; saving = false; })
                                                .catch(() => { saving = false; });
                                            "
                                            class="text-lg leading-none transition-transform hover:scale-110"
                                            :class="favorited ? 'text-cortex-orange' : 'text-text-muted hover:text-cortex-orange/60'"
                                            :title="favorited ? 'Remove from favorites' : 'Add to favorites'"
                                        >
                                            <span x-show="favorited" x-cloak>&#9733;</span>
                                            <span x-show="!favorited">&#9734;</span>
                                        </button>
                                    @endif
                                    <div>
                                        <a href="{{ route('waves.run', $wave) }}" class="text-cortex-orange hover:text-cortex-peach font-semibold">{{ $wave->name }}</a>
                                        @if($wave->description)
                                            <p class="text-text-muted text-sm mt-1">{{ $wave->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 border-b border-gray/50">
                                @if($wave->nodes->count())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($wave->nodes as $index => $node)
                                            <span class="badge badge-lavender">
                                                {{ $index + 1 }}. {{ $node->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-text-muted text-sm">No nodes configured</span>
                                @endif
                            </td>
                            <td class="p-4 border-b border-gray/50">
                                <div class="flex gap-2">
                                    @if(auth()->user()->hasPermission('waves.run'))
                                        <a href="{{ route('waves.run', $wave) }}" class="px-3 py-1.5 rounded-full bg-cortex-green text-cortex-black hover:bg-cortex-green/80 transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Run
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('waves.view'))
                                        <a href="{{ route('waves.show', $wave) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-lavender hover:bg-cortex-lavender hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            View
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('waves.edit'))
                                        <a href="{{ route('waves.edit', $wave) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('waves.create'))
                                        <form method="POST" action="{{ route('waves.copy', $wave) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-blue hover:bg-cortex-blue hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                                Copy
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->hasPermission('waves.delete'))
                                        <form method="POST" action="{{ route('waves.destroy', $wave) }}" id="delete-wave-{{ $wave->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                x-data
                                                class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-red hover:bg-cortex-red hover:text-white transition-all text-xs uppercase tracking-wider"
                                                style="font-family: var(--font-display);"
                                                x-on:click="$dispatch('confirm-delete', {
                                                    title: 'Delete Wave',
                                                    message: 'Are you sure you want to delete Wave #{{ $wave->id }} ({{ $wave->name }})? This will move it to the trash.',
                                                    formId: 'delete-wave-{{ $wave->id }}'
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
                            <td colspan="3" class="p-8 text-center text-text-muted">
                                No waves found. Create your first wave to chain nodes together.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $waves->links() }}
        </div>
    </div>
</x-layouts.app>

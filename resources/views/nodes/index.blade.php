<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Nodes</h1>
            @if(auth()->user()->hasPermission('nodes.create'))
                <a href="{{ route('nodes.create') }}" class="btn btn-primary">
                    Add Node
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-cortex-green/20 border border-cortex-green/50 text-cortex-green rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-cortex-panel-light rounded-xl">
            <form method="GET" action="{{ route('nodes.index') }}" class="flex flex-wrap gap-4 items-end">
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
                        <a href="{{ route('nodes.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="p-4 text-left border-b border-gray">Name</th>
                        <th class="p-4 text-left border-b border-gray">Inputs</th>
                        <th class="p-4 text-left border-b border-gray w-56">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($nodes as $node)
                        <tr class="hover:bg-cortex-panel-light transition-colors">
                            <td class="p-4 border-b border-gray/50">
                                <a href="{{ route('nodes.show', $node) }}" class="text-cortex-orange hover:text-cortex-peach font-semibold">{{ $node->name }}</a>
                            </td>
                            <td class="p-4 border-b border-gray/50">
                                @if($node->inputs)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($node->inputs as $input)
                                            <span class="badge badge-peach">
                                                {{ $input['label'] }} ({{ $input['type'] }})
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-text-muted text-sm">No inputs defined</span>
                                @endif
                            </td>
                            <td class="p-4 border-b border-gray/50">
                                <div class="flex gap-2">
                                    @if(auth()->user()->hasPermission('nodes.view'))
                                        <a href="{{ route('nodes.show', $node) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-lavender hover:bg-cortex-lavender hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            View
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('nodes.edit'))
                                        <a href="{{ route('nodes.edit', $node) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('nodes.create'))
                                        <form method="POST" action="{{ route('nodes.copy', $node) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-green hover:bg-cortex-green hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                                Copy
                                            </button>
                                        </form>
                                    @endif
                                    @if(auth()->user()->hasPermission('nodes.delete'))
                                        <form method="POST" action="{{ route('nodes.destroy', $node) }}" id="delete-node-{{ $node->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                x-data
                                                class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-red hover:bg-cortex-red hover:text-white transition-all text-xs uppercase tracking-wider"
                                                style="font-family: var(--font-display);"
                                                x-on:click="$dispatch('confirm-delete', {
                                                    title: 'Delete Node',
                                                    message: 'Are you sure you want to delete Node #{{ $node->id }} ({{ $node->name }})? This will move it to the trash.',
                                                    formId: 'delete-node-{{ $node->id }}'
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
            {{ $nodes->links() }}
        </div>
    </div>
</x-layouts.app>

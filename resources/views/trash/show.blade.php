<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Trash Item: {{ $trash->display_name }}</h1>
            <a href="{{ route('trash.index') }}" class="btn btn-secondary">
                Back to Trash
            </a>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-cortex-panel-light rounded-xl p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-3 h-8 bg-cortex-lavender rounded-full"></div>
                    <h2>Item Details</h2>
                </div>
                <dl class="space-y-4">
                    <div class="flex items-center">
                        <dt class="font-semibold text-cortex-blue w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Type</dt>
                        <dd>
                            <span class="badge badge-lavender">
                                {{ $trash->model_name }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center">
                        <dt class="font-semibold text-cortex-blue w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Original ID</dt>
                        <dd class="text-cortex-orange font-mono">{{ $trash->trashable_id }}</dd>
                    </div>
                    <div class="flex items-center">
                        <dt class="font-semibold text-cortex-blue w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Deleted By</dt>
                        <dd>{{ $trash->deletedByUser?->name ?? 'System' }}</dd>
                    </div>
                    <div class="flex items-center">
                        <dt class="font-semibold text-cortex-blue w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Deleted At</dt>
                        <dd>
                            {{ $trash->deleted_at->format('M j, Y g:i A') }}
                            <span class="text-text-muted">({{ $trash->deleted_at->diffForHumans() }})</span>
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="flex flex-col gap-4">
                @if(auth()->user()->hasPermission('trash.restore'))
                    <form method="POST" action="{{ route('trash.restore', $trash) }}">
                        @csrf
                        <button type="submit" class="w-full px-6 py-3 rounded-full bg-cortex-green text-cortex-black font-bold hover:shadow-lg hover:shadow-cortex-green/50 transition-all uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                            Restore Item
                        </button>
                    </form>
                @endif
                @if(auth()->user()->hasPermission('trash.delete'))
                    <form method="POST" action="{{ route('trash.destroy', $trash) }}" id="delete-trash-{{ $trash->id }}">
                        @csrf
                        @method('DELETE')
                        <button
                            type="button"
                            x-data
                            class="w-full px-6 py-3 rounded-full bg-cortex-red text-white hover:shadow-[0_0_20px_rgba(255,102,102,0.6)] transition-all uppercase text-sm tracking-wider font-semibold"
                            style="font-family: var(--font-display);"
                            x-on:click="$dispatch('confirm-delete', {
                                title: 'Permanently Delete',
                                message: 'Are you sure you want to permanently delete {{ $trash->model_name }} #{{ $trash->trashable_id }} ({{ $trash->display_name }})? This cannot be undone.',
                                formId: 'delete-trash-{{ $trash->id }}'
                            })"
                        >
                            Permanently Delete
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-3 h-8 bg-cortex-orange rounded-full"></div>
                <h2>Stored Data</h2>
            </div>
            <div class="bg-cortex-dark border border-gray rounded-xl p-6 overflow-x-auto">
                <pre class="text-sm text-cortex-cyan font-mono">{{ json_encode($trash->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        @if(!empty($trash->relationships))
            <div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-3 h-8 bg-cortex-blue rounded-full"></div>
                    <h2>Stored Relationships</h2>
                </div>
                <div class="bg-cortex-dark border border-gray rounded-xl p-6 overflow-x-auto">
                    <pre class="text-sm text-cortex-lavender font-mono">{{ json_encode($trash->relationships, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>

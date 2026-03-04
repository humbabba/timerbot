<x-layouts.app>
    <div class="p-4 md:p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Trash Item: {{ $trash->display_name }}</h1>
            <a href="{{ route('trash.index') }}" class="btn btn-secondary">
                Back to Trash
            </a>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-timerbot-panel-light rounded-sm p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-3 h-8 bg-timerbot-teal rounded-none"></div>
                    <h2>Item Details</h2>
                </div>
                <dl class="space-y-4">
                    <div class="flex items-center">
                        <dt class="font-semibold text-timerbot-lime w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Type</dt>
                        <dd>
                            <span class="badge badge-teal">
                                {{ $trash->model_name }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex items-center">
                        <dt class="font-semibold text-timerbot-lime w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Original ID</dt>
                        <dd class="text-timerbot-green font-mono">{{ $trash->trashable_id }}</dd>
                    </div>
                    <div class="flex items-center">
                        <dt class="font-semibold text-timerbot-lime w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Deleted By</dt>
                        <dd>{{ $trash->deletedByUser?->name ?? 'System' }}</dd>
                    </div>
                    <div class="flex items-center">
                        <dt class="font-semibold text-timerbot-lime w-36 uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Deleted At</dt>
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
                        <button type="submit" class="w-full px-6 py-3 rounded-none bg-timerbot-green text-timerbot-black font-bold hover:shadow-lg hover:shadow-timerbot-green/50 transition-all uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
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
                            class="w-full px-6 py-3 rounded-none bg-timerbot-red text-white hover:shadow-[0_0_20px_rgba(255,102,102,0.6)] transition-all uppercase text-sm tracking-wider font-semibold"
                            style="font-family: var(--font-display);"
                            x-on:click="$dispatch('confirm-delete', {
                                title: 'Permanently Delete',
                                message: 'Are you sure you want to permanently delete {{ $trash->model_name }} #{{ $trash->trashable_id }} (' + {{ Js::from($trash->display_name) }} + ')? This cannot be undone.',
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
                <div class="w-3 h-8 bg-timerbot-green rounded-none"></div>
                <h2>Stored Data</h2>
            </div>
            <div class="bg-timerbot-dark border border-divider rounded-sm p-6 overflow-x-auto">
                <pre class="text-sm text-timerbot-teal font-mono">{{ json_encode($trash->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>

        @if(!empty($trash->relationships))
            <div>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-3 h-8 bg-timerbot-lime rounded-none"></div>
                    <h2>Stored Relationships</h2>
                </div>
                <div class="bg-timerbot-dark border border-divider rounded-sm p-6 overflow-x-auto">
                    <pre class="text-sm text-timerbot-teal font-mono">{{ json_encode($trash->relationships, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>

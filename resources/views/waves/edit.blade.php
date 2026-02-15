<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-cyan rounded-full"></div>
            <h1>Edit Wave</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('waves.update', $wave) }}" id="wave-form" data-ajax-save class="bg-cortex-panel-light rounded-xl p-6">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $wave->name) }}"
                    required
                    autofocus
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >
            </div>

            <div class="mb-6">
                <label for="description" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="3"
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >{{ old('description', $wave->description) }}</textarea>
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Nodes in Chain</label>
                <p class="text-text-muted text-sm mb-4">Add at least 1 node. If adding multiple nodes, you can configure field mappings between them. Nodes execute in order.</p>

                <div id="nodes-container" class="space-y-4">
                    @php
                        $waveNodes = old('nodes') ? collect(old('nodes')) : $wave->nodes;
                    @endphp
                    @foreach($waveNodes as $index => $waveNode)
                        @php
                            if (old('nodes')) {
                                $nodeId = $waveNode['id'] ?? null;
                                $mappings = $waveNode['mappings'] ?? [];
                                $includeInOutput = isset($waveNode['include_in_output']) ? (bool) $waveNode['include_in_output'] : true;
                            } else {
                                $nodeId = $waveNode->id;
                                $mappings = json_decode($waveNode->pivot->mappings ?? '{}', true) ?: [];
                                $includeInOutput = $waveNode->pivot->include_in_output ?? true;
                            }
                            $selectedNode = $nodes->firstWhere('id', $nodeId);
                        @endphp
                        <div class="node-row p-4 bg-cortex-panel rounded-lg border border-gray" data-position="{{ $index }}">
                            <div class="flex gap-4">
                                <div class="flex flex-col gap-1 justify-center">
                                    <button type="button" onclick="moveNodeUp(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move up">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" onclick="moveNodeDown(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move down">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-4 mb-3">
                                        <span class="node-position text-cortex-orange font-bold text-lg" style="font-family: var(--font-display);">{{ $index + 1 }}</span>
                                        <select name="nodes[{{ $index }}][id]" onchange="updateMappingOptions(this)" required class="flex-1 p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan">
                                            <option value="">Select a node...</option>
                                            @foreach($nodes as $node)
                                                <option value="{{ $node->id }}" data-inputs='@json($node->inputs ?? [])' {{ $nodeId == $node->id ? 'selected' : '' }}>
                                                    {{ $node->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="button" onclick="this.closest('.node-row').remove(); updateNodePositions();" class="px-3 py-1.5 rounded-full bg-cortex-red text-white text-xs uppercase tracking-wider" style="font-family: var(--font-display);">Remove</button>
                                    </div>
                                    <div class="mappings-container {{ $index > 0 ? 'mt-3 p-3 bg-cortex-dark rounded-lg border border-cortex-cyan/30' : 'hidden' }}" data-initial-mappings='@json($mappings)'>
                                        @if($index > 0)
                                            <p class="text-text-muted text-sm">Loading mappings...</p>
                                        @endif
                                    </div>
                                    <label class="include-in-output-label flex items-center gap-2 text-sm text-text-muted cursor-pointer mt-3">
                                        <input type="hidden" name="nodes[{{ $index }}][include_in_output]" value="0">
                                        <input type="checkbox" name="nodes[{{ $index }}][include_in_output]" value="1" {{ $includeInOutput ? 'checked' : '' }} class="w-4 h-4 rounded border-gray bg-cortex-dark text-cortex-green">
                                        <span>Include in copy all</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addNode()" class="mt-4 btn btn-secondary">
                    + Add Node
                </button>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update Wave
                </button>
                <a href="{{ route('waves.index') }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @vite(['resources/js/wave-form.js'])
    <script>
        const allNodes = @json($nodes);
        let nodeIndex = {{ count($waveNodes) }};
    </script>
</x-layouts.app>

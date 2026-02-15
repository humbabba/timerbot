<x-layouts.app>
    @vite(['resources/js/wave-runner.js'])

    <div class="p-8 max-w-4xl">
        <div class="flex justify-between items-start mb-8">
            <div class="flex items-start gap-4">
                <div class="w-3 h-10 bg-cortex-green rounded-full shrink-0 mt-1"></div>
                <h1>Run: {{ $wave->name }}
                    @if(auth()->user()->hasPermission('waves.view'))
                        <button
                            x-data="{ favorited: {{ $isFavorite ? 'true' : 'false' }}, saving: false }"
                            x-on:click="
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
                            class="inline align-middle text-2xl leading-none transition-transform hover:scale-110 ml-1"
                            :class="favorited ? 'text-cortex-orange' : 'text-text-muted hover:text-cortex-orange/60'"
                            :title="favorited ? 'Remove from favorites' : 'Add to favorites'"
                        >
                            <span x-show="favorited" x-cloak>&#9733;</span>
                            <span x-show="!favorited">&#9734;</span>
                        </button>
                    @endif
                </h1>
            </div>
            <div class="flex gap-2 shrink-0">
                @if(auth()->user()->hasPermission('waves.edit'))
                    <a href="{{ route('waves.edit', $wave) }}" class="btn bg-cortex-cyan text-cortex-black hover:bg-cortex-cyan/80 no-underline whitespace-nowrap">
                        Edit Wave
                    </a>
                @endif
                <a href="{{ route('waves.show', $wave) }}" class="btn bg-cortex-panel-light text-text hover:bg-gray no-underline whitespace-nowrap">
                    Back to Wave
                </a>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-text-muted">Progress</span>
                <span id="progress-text" class="text-sm text-cortex-orange">Ready</span>
            </div>
            <div class="h-2 bg-cortex-panel rounded-full overflow-hidden">
                <div id="progress-bar" class="h-full bg-gradient-to-r from-cortex-green to-cortex-cyan transition-all duration-300" style="width: 0%"></div>
            </div>
            <div class="flex justify-between mt-2">
                @foreach($wave->nodes as $index => $node)
                    <div class="flex flex-col items-center">
                        <div id="step-indicator-{{ $index }}" class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold bg-cortex-panel text-text-muted">
                            {{ $index + 1 }}
                        </div>
                        <span class="text-xs text-text-muted mt-1 max-w-20 text-center truncate">{{ $node->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Error Container -->
        <div id="error-container" class="hidden mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg"></div>

        <!-- All Node Forms Container -->
        <div id="all-forms-container" class="space-y-6">
            <!-- Forms rendered by JS -->
        </div>

        <!-- Run Wave Button -->
        <div id="run-wave-container" class="mt-6">
            <button type="button" id="run-wave-button" onclick="runWave()" class="btn btn-primary">
                Run Wave
            </button>
        </div>

        <!-- Results Container (shown when complete) -->
        <div id="results-container" class="hidden">
            <div class="bg-cortex-panel-light rounded-xl overflow-hidden">
                <div class="h-1 bg-gradient-to-r from-cortex-green via-cortex-cyan to-cortex-blue"></div>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-cortex-green">Combined Results</h2>
                        <button id="copy-all-button" onclick="copyAllResults()" class="px-4 py-2 rounded-full bg-cortex-panel text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                            Copy All
                        </button>
                    </div>
                    <div id="final-results" class="space-y-6">
                        <!-- Results rendered by JS -->
                    </div>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <button onclick="restartWave()" class="btn btn-secondary">
                    Run Again
                </button>
                <a href="{{ route('waves.show', $wave) }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                    Back to Wave
                </a>
            </div>
        </div>
    </div>

    <script>
        const waveId = {{ $wave->id }};
        const waveNodes = @json($waveNodesJson);
        const runStepUrl = "{{ route('waves.run-step', $wave) }}";
        const rerunStepUrl = "{{ route('waves.rerun-step', $wave) }}";
        const csrfToken = "{{ csrf_token() }}";
    </script>
</x-layouts.app>

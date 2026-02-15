<x-layouts.app>
    <div class="p-8 max-w-4xl" x-data="voiceProfileShow()">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-lavender rounded-full"></div>
            <h1>{{ $voiceProfile->name }}</h1>
        </div>

        <div class="bg-cortex-panel-light rounded-xl p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="block font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Voice Profile Content</label>
                <div class="flex gap-2">
                    @if($voiceProfile->rewrites->isNotEmpty() && auth()->user()->hasPermission('voice-profiles.edit'))
                        <button
                            type="button"
                            x-on:click="refine()"
                            x-bind:disabled="refining"
                            class="px-3 py-1.5 rounded-full bg-cortex-panel text-cortex-lavender hover:bg-cortex-lavender hover:text-cortex-black transition-all text-xs uppercase tracking-wider disabled:opacity-50"
                            style="font-family: var(--font-display);"
                        >
                            <span x-show="!refining">Refine with AI</span>
                            <span x-show="refining" x-cloak class="flex items-center gap-1.5">
                                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Refining&hellip;
                            </span>
                        </button>
                    @endif
                    @if(auth()->user()->hasPermission('voice-profiles.edit'))
                        <a href="{{ route('voice-profiles.edit', $voiceProfile) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                            Edit
                        </a>
                    @endif
                </div>
            </div>
            <div class="prose prose-invert max-w-none bg-cortex-panel rounded-lg p-4 border border-gray text-sm">
                {!! Str::markdown($voiceProfile->content, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
            </div>
        </div>

        <!-- Refine Comparison Panel -->
        <template x-if="refinedContent">
            <div class="bg-cortex-panel-light rounded-xl p-6 mb-6 border border-cortex-lavender/30">
                <div class="flex justify-between items-center mb-4">
                    <label class="block font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">AI Refinement Suggestion</label>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            x-on:click="acceptRefinement()"
                            x-bind:disabled="accepting"
                            class="px-3 py-1.5 rounded-full bg-cortex-green/20 text-cortex-green hover:bg-cortex-green hover:text-cortex-black transition-all text-xs uppercase tracking-wider disabled:opacity-50"
                            style="font-family: var(--font-display);"
                        >
                            <span x-show="!accepting">Accept</span>
                            <span x-show="accepting" x-cloak>Saving&hellip;</span>
                        </button>
                        <button
                            type="button"
                            x-on:click="editFirst()"
                            class="px-3 py-1.5 rounded-full bg-cortex-panel text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider"
                            style="font-family: var(--font-display);"
                        >
                            Edit First
                        </button>
                        <button
                            type="button"
                            x-on:click="dismiss()"
                            class="px-3 py-1.5 rounded-full bg-cortex-panel text-text-muted hover:bg-gray hover:text-text transition-all text-xs uppercase tracking-wider"
                            style="font-family: var(--font-display);"
                        >
                            Dismiss
                        </button>
                    </div>
                </div>

                <pre class="bg-cortex-panel rounded-lg p-4 text-sm text-text whitespace-pre-wrap border border-gray overflow-x-auto leading-relaxed" x-html="diffHtml"></pre>
            </div>
        </template>

        <!-- Error message -->
        <template x-if="refineError">
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg text-sm" x-text="refineError"></div>
        </template>

        <!-- Rewrite Pairs Section -->
        <div class="bg-cortex-panel-light rounded-xl p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <label class="block font-semibold text-cortex-cyan uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                    Rewrite Pairs
                    <span class="text-text-muted font-normal normal-case tracking-normal">({{ $voiceProfile->rewrites->count() }} / {{ App\Http\Controllers\VoiceRewriteController::MAX_REWRITES }})</span>
                </label>
                @if(auth()->user()->hasPermission('voice-rewrites.create'))
                    <a href="{{ route('voice-rewrites.create', $voiceProfile) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel text-cortex-orange hover:bg-cortex-orange hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                        + Add Rewrite
                    </a>
                @endif
            </div>

            @if($voiceProfile->rewrites->isEmpty())
                <p class="text-text-muted text-sm">No rewrite pairs yet. Add before/after examples to teach the AI your voice by example.</p>
            @else
                <div class="space-y-3">
                    @foreach($voiceProfile->rewrites as $rewrite)
                        <div x-data="{ expanded: false }" class="bg-cortex-panel rounded-lg border border-gray overflow-hidden">
                            <button
                                type="button"
                                x-on:click="expanded = !expanded"
                                class="w-full text-left p-4 flex justify-between items-start hover:bg-cortex-dark transition-colors"
                            >
                                <div class="flex-1 min-w-0">
                                    @if($rewrite->notes)
                                        <span class="text-cortex-lavender text-sm font-semibold">{{ $rewrite->notes }}</span>
                                        <span class="text-text-muted mx-2">&mdash;</span>
                                    @endif
                                    <span class="text-text-muted text-sm">{{ Str::limit($rewrite->original_text, 100) }}</span>
                                </div>
                                <svg x-bind:class="expanded && 'rotate-180'" class="w-5 h-5 text-text-muted transition-transform flex-shrink-0 ml-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <div x-show="expanded" x-collapse x-cloak class="px-4 pb-4">
                                <div class="mb-4">
                                    <label class="block mb-1 text-xs font-semibold text-cortex-red uppercase tracking-wider" style="font-family: var(--font-display);">Original</label>
                                    <pre class="bg-cortex-dark rounded-lg p-3 text-sm text-text whitespace-pre-wrap border border-cortex-red/20 overflow-x-auto">{{ $rewrite->original_text }}</pre>
                                </div>

                                <div class="mb-4">
                                    <label class="block mb-1 text-xs font-semibold text-cortex-green uppercase tracking-wider" style="font-family: var(--font-display);">Rewritten</label>
                                    <pre class="bg-cortex-dark rounded-lg p-3 text-sm text-text whitespace-pre-wrap border border-cortex-green/20 overflow-x-auto">{{ $rewrite->rewritten_text }}</pre>
                                </div>

                                @if($rewrite->notes)
                                    <p class="text-text-muted text-sm mb-4">{{ $rewrite->notes }}</p>
                                @endif

                                <div class="flex gap-2">
                                    @if(auth()->user()->hasPermission('voice-rewrites.edit'))
                                        <a href="{{ route('voice-rewrites.edit', [$voiceProfile, $rewrite]) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('voice-rewrites.delete'))
                                        <form id="delete-rewrite-{{ $rewrite->id }}" method="POST" action="{{ route('voice-rewrites.destroy', [$voiceProfile, $rewrite]) }}">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <button
                                            type="button"
                                            x-on:click="$dispatch('confirm-delete', { title: 'Delete Rewrite', message: 'Are you sure you want to delete this rewrite pair?', formId: 'delete-rewrite-{{ $rewrite->id }}' })"
                                            class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-red hover:bg-cortex-red hover:text-white transition-all text-xs uppercase tracking-wider"
                                            style="font-family: var(--font-display);"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if($voiceProfile->nodes()->count() > 0)
            <div class="bg-cortex-panel-light rounded-xl p-6">
                <label class="block mb-4 font-semibold text-cortex-peach uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Nodes Using This Profile</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($voiceProfile->nodes as $node)
                        <a href="{{ route('nodes.show', $node) }}" class="badge badge-peach no-underline">{{ $node->name }}</a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <script>
        function voiceProfileShow() {
            return {
                currentContent: @json($voiceProfile->content),
                refining: false,
                accepting: false,
                refinedContent: null,
                diffHtml: '',
                refineError: null,

                computeDiff() {
                    const parts = window.diffWords(this.currentContent, this.refinedContent);
                    const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                    this.diffHtml = parts.map(p => {
                        if (p.removed) return `<span style="background:rgba(239,68,68,0.25);text-decoration:line-through;">${esc(p.value)}</span>`;
                        if (p.added) return `<span style="background:rgba(34,197,94,0.25);">${esc(p.value)}</span>`;
                        return esc(p.value);
                    }).join('');
                },

                async refine() {
                    this.refining = true;
                    this.refinedContent = null;
                    this.diffHtml = '';
                    this.refineError = null;

                    try {
                        const response = await fetch('{{ route("voice-profiles.refine", $voiceProfile) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.refinedContent = data.refined_content;
                            this.computeDiff();
                        } else {
                            this.refineError = data.error || 'Something went wrong.';
                        }
                    } catch (e) {
                        this.refineError = 'Request failed. Please try again.';
                    } finally {
                        this.refining = false;
                    }
                },

                async acceptRefinement() {
                    this.accepting = true;

                    try {
                        const response = await fetch('{{ route("voice-profiles.update", $voiceProfile) }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                name: @json($voiceProfile->name),
                                content: this.refinedContent,
                            }),
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.location.reload();
                        } else {
                            this.refineError = 'Failed to save. Please try again.';
                        }
                    } catch (e) {
                        this.refineError = 'Failed to save. Please try again.';
                    } finally {
                        this.accepting = false;
                    }
                },

                editFirst() {
                    sessionStorage.setItem('refined_content_{{ $voiceProfile->id }}', this.refinedContent);
                    window.location.href = '{{ route("voice-profiles.edit", $voiceProfile) }}';
                },

                dismiss() {
                    this.refinedContent = null;
                    this.refineError = null;
                },
            };
        }
    </script>
</x-layouts.app>

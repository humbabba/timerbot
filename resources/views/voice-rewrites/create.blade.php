<x-layouts.app>
    <div class="p-8 max-w-4xl" x-data="rewriteCreate()">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-orange rounded-full"></div>
            <h1>Add Rewrite Pair</h1>
        </div>

        <p class="text-text-muted mb-6">
            Voice Profile: <a href="{{ route('voice-profiles.show', $voiceProfile) }}" class="text-cortex-lavender">{{ $voiceProfile->name }}</a>
            <span class="ml-2 text-sm">({{ $rewriteCount }} / {{ App\Http\Controllers\VoiceRewriteController::MAX_REWRITES }})</span>
        </p>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

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

        @if ($atLimit)
            <!-- Limit reached: pick one to delete -->
            <div class="bg-cortex-panel-light rounded-xl p-6 mb-6 border border-cortex-orange/30">
                <div class="flex items-start gap-3 mb-4">
                    <svg class="w-6 h-6 text-cortex-orange flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                        <h3 class="text-cortex-orange font-semibold text-lg" style="font-family: var(--font-display);">Limit Reached</h3>
                        <p class="text-text-muted text-sm mt-1">
                            This voice profile has the maximum of {{ App\Http\Controllers\VoiceRewriteController::MAX_REWRITES }} rewrite pairs.
                            Delete one below to make room for a new one.
                        </p>
                    </div>
                </div>

                <!-- Delete oldest shortcut -->
                <form method="POST" action="{{ route('voice-rewrites.destroy-oldest', $voiceProfile) }}" class="mb-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-full bg-cortex-red/20 text-cortex-red hover:bg-cortex-red hover:text-white transition-all text-sm uppercase tracking-wider" style="font-family: var(--font-display);">
                        Delete the Oldest Pair
                    </button>
                    <span class="text-text-muted text-sm ml-3">
                        ({{ $voiceProfile->rewrites->sortBy('created_at')->first()?->notes ?: 'Rewrite #' . $voiceProfile->rewrites->sortBy('created_at')->first()?->id }}
                        &mdash; {{ $voiceProfile->rewrites->sortBy('created_at')->first()?->created_at->format('M j, Y') }})
                    </span>
                </form>

                <div class="border-t border-gray pt-4">
                    <p class="text-text-muted text-xs uppercase tracking-wider mb-3" style="font-family: var(--font-display);">Or choose a specific pair to delete:</p>
                    <div class="space-y-2">
                        @foreach($voiceProfile->rewrites->sortBy('created_at') as $rewrite)
                            <div class="flex items-center justify-between bg-cortex-panel rounded-lg p-3 border border-gray">
                                <div class="flex-1 min-w-0 mr-4">
                                    @if($rewrite->notes)
                                        <span class="text-cortex-lavender text-sm font-semibold">{{ $rewrite->notes }}</span>
                                        <span class="text-text-muted mx-1">&mdash;</span>
                                    @endif
                                    <span class="text-text-muted text-sm">{{ Str::limit($rewrite->original_text, 80) }}</span>
                                    <span class="text-text-muted text-xs ml-2">({{ $rewrite->created_at->format('M j, Y') }})</span>
                                </div>
                                <form method="POST" action="{{ route('voice-rewrites.destroy', [$voiceProfile, $rewrite]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="redirect_to_create" value="1">
                                    <button type="submit" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-red hover:bg-cortex-red hover:text-white transition-all text-xs uppercase tracking-wider flex-shrink-0" style="font-family: var(--font-display);">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-6">
                    <a href="{{ route('voice-profiles.show', $voiceProfile) }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                        Cancel
                    </a>
                </div>
            </div>
        @else
            <!-- Normal create form -->
            <form x-ref="form" method="POST" action="{{ route('voice-rewrites.store', $voiceProfile) }}" x-on:submit.prevent="compareTexts()" class="bg-cortex-panel-light rounded-xl p-6" x-show="!previewing">
                @csrf
                <input type="hidden" name="activity_log_id" x-model="activityLogId">

                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <label for="original_text" class="block font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Original Text</label>
                        <button type="button" x-on:click="openModal()" class="px-3 py-1.5 rounded-full bg-cortex-panel text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                            Select from Activity Log
                        </button>
                    </div>
                    <p class="text-text-muted text-sm mb-3">Paste the original AI output, or select from a previous wave run.</p>
                    <textarea
                        id="original_text"
                        name="original_text"
                        rows="10"
                        required
                        x-model="originalText"
                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan font-mono text-sm"
                    >{{ old('original_text') }}</textarea>
                </div>

                <div class="mb-6">
                    <label for="rewritten_text" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Rewritten Text</label>
                    <p class="text-text-muted text-sm mb-3">Paste the edited version that matches the desired voice.</p>
                    <textarea
                        id="rewritten_text"
                        name="rewritten_text"
                        rows="10"
                        required
                        x-model="rewrittenText"
                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan font-mono text-sm"
                    >{{ old('rewritten_text') }}</textarea>
                </div>

                <div class="mb-8">
                    <label for="notes" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Notes <span class="text-text-muted normal-case tracking-normal font-normal">(optional)</span></label>
                    <p class="text-text-muted text-sm mb-3">Brief explanation of what changed and why (max 255 chars).</p>
                    <input
                        type="text"
                        id="notes"
                        name="notes"
                        value="{{ old('notes') }}"
                        maxlength="255"
                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                        placeholder="e.g., Shortened sentences, removed jargon, added conversational tone"
                    >
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="btn btn-primary" x-bind:disabled="comparing">
                        <span x-show="!comparing">Create Rewrite Pair</span>
                        <span x-show="comparing" x-cloak>Comparing...</span>
                    </button>
                    <a href="{{ route('voice-profiles.show', $voiceProfile) }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                        Cancel
                    </a>
                </div>
            </form>

            <!-- Comparison preview panel -->
            <div x-show="previewing" x-cloak class="bg-cortex-panel-light rounded-xl p-6">
                <div class="flex items-center gap-3 mb-6">
                    <svg class="w-6 h-6 text-cortex-cyan flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <h3 class="text-xl font-semibold text-cortex-cyan" style="font-family: var(--font-display);">Rewrite Comparison</h3>
                </div>

                <!-- Percentage display -->
                <div class="flex items-center gap-4 mb-6 p-4 bg-cortex-panel rounded-xl border border-gray">
                    <div class="text-4xl font-bold" style="font-family: var(--font-display);"
                         x-bind:class="{
                            'text-cortex-green': diffPercentage <= 25,
                            'text-cortex-cyan': diffPercentage > 25 && diffPercentage <= 50,
                            'text-cortex-orange': diffPercentage > 50 && diffPercentage <= 75,
                            'text-cortex-red': diffPercentage > 75
                         }"
                         x-text="diffPercentage + '%'">
                    </div>
                    <div>
                        <p class="text-text font-semibold" style="font-family: var(--font-display);">Difference</p>
                        <p class="text-text-muted text-sm">Word-level changes between original and rewritten text</p>
                    </div>
                </div>

                <!-- Notes list -->
                <div class="mb-6" x-show="diffNotes.length > 0">
                    <h4 class="text-sm font-semibold text-cortex-lavender uppercase tracking-wider mb-3" style="font-family: var(--font-display);">Analysis</h4>
                    <ul class="space-y-2">
                        <template x-for="note in diffNotes" :key="note">
                            <li class="flex items-start gap-2 text-text-muted text-sm">
                                <span class="text-cortex-peach mt-0.5">&#8226;</span>
                                <span x-text="note"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <!-- Action buttons -->
                <div class="flex gap-4 pt-4 border-t border-gray">
                    <button type="button" x-on:click="submitForm()" class="btn btn-primary">
                        Save Rewrite Pair
                    </button>
                    <button type="button" x-on:click="previewing = false" class="btn bg-cortex-panel text-text hover:bg-gray">
                        Go Back
                    </button>
                </div>
            </div>
        @endif

        <!-- Activity Log Selector Modal -->
        <div
            x-show="modalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
        >
            <div class="flex items-center justify-center min-h-screen px-4">
                <div
                    x-show="modalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 bg-cortex-black/80"
                    x-on:click="modalOpen = false"
                ></div>

                <div
                    x-show="modalOpen"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative bg-cortex-panel border border-gray rounded-2xl shadow-2xl max-w-3xl w-full overflow-hidden"
                    x-on:click.stop
                >
                    <div class="h-2 bg-gradient-to-r from-cortex-cyan via-cortex-lavender to-cortex-blue"></div>

                    <div class="p-6">
                        <h3 class="text-xl font-semibold mb-4 text-cortex-cyan" style="font-family: var(--font-display);">Select from Activity Log</h3>

                        <div class="mb-4">
                            <input
                                type="text"
                                x-model="search"
                                x-on:input.debounce.300ms="fetchLogs(1)"
                                placeholder="Search by wave or node name..."
                                class="w-full p-3 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan"
                            >
                        </div>

                        <div class="max-h-96 overflow-y-auto space-y-2 mb-4">
                            <template x-if="loading">
                                <div class="text-center py-8 text-text-muted">Loading...</div>
                            </template>
                            <template x-if="!loading && logs.length === 0">
                                <div class="text-center py-8 text-text-muted">No wave outputs found.</div>
                            </template>
                            <template x-for="log in logs" :key="log.id">
                                <button
                                    type="button"
                                    x-on:click="selectLog(log)"
                                    class="w-full text-left p-4 bg-cortex-panel-light rounded-lg hover:bg-cortex-dark border border-transparent hover:border-cortex-cyan transition-all"
                                >
                                    <div class="flex justify-between items-start mb-1">
                                        <div>
                                            <span class="text-cortex-lavender font-semibold" x-text="log.wave_name"></span>
                                            <span class="text-text-muted mx-2">&rarr;</span>
                                            <span class="text-cortex-peach" x-text="log.node_name"></span>
                                            <span class="text-text-muted text-sm ml-2" x-text="'Step ' + log.step"></span>
                                        </div>
                                        <span class="text-text-muted text-xs whitespace-nowrap ml-4" x-text="log.created_at"></span>
                                    </div>
                                    <div class="text-text text-sm mt-2 line-clamp-3" x-text="log.output.substring(0, 200) + (log.output.length > 200 ? '...' : '')"></div>
                                </button>
                            </template>
                        </div>

                        <!-- Pagination -->
                        <div x-show="lastPage > 1" class="flex justify-between items-center">
                            <button
                                type="button"
                                x-on:click="fetchLogs(currentPage - 1)"
                                x-bind:disabled="currentPage <= 1"
                                class="px-4 py-2 rounded-full bg-cortex-panel-light text-text hover:bg-cortex-dark transition-all text-sm disabled:opacity-30 disabled:cursor-not-allowed"
                            >
                                Previous
                            </button>
                            <span class="text-text-muted text-sm" x-text="'Page ' + currentPage + ' of ' + lastPage"></span>
                            <button
                                type="button"
                                x-on:click="fetchLogs(currentPage + 1)"
                                x-bind:disabled="currentPage >= lastPage"
                                class="px-4 py-2 rounded-full bg-cortex-panel-light text-text hover:bg-cortex-dark transition-all text-sm disabled:opacity-30 disabled:cursor-not-allowed"
                            >
                                Next
                            </button>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button
                                type="button"
                                x-on:click="modalOpen = false"
                                class="px-5 py-2 rounded-full bg-cortex-panel-light text-text hover:bg-cortex-lavender hover:text-cortex-black transition-all duration-200 uppercase text-sm tracking-wider"
                                style="font-family: var(--font-display);"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function rewriteCreate() {
            return {
                modalOpen: false,
                search: '',
                logs: [],
                loading: false,
                currentPage: 1,
                lastPage: 1,
                originalText: @js(old('original_text', '')),
                rewrittenText: @js(old('rewritten_text', '')),
                activityLogId: @js(old('activity_log_id', '')),

                // Preview state
                previewing: false,
                comparing: false,
                diffPercentage: null,
                diffNotes: [],

                openModal() {
                    this.modalOpen = true;
                    if (this.logs.length === 0) {
                        this.fetchLogs(1);
                    }
                },

                async compareTexts() {
                    if (!this.originalText.trim() || !this.rewrittenText.trim()) {
                        this.$refs.form.reportValidity();
                        return;
                    }

                    this.comparing = true;
                    try {
                        const response = await fetch(`{{ route('voice-rewrites.compare', $voiceProfile) }}`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                original_text: this.originalText,
                                rewritten_text: this.rewrittenText,
                            }),
                        });
                        const data = await response.json();
                        this.diffPercentage = data.percentage;
                        this.diffNotes = data.notes;
                        this.previewing = true;
                    } catch (e) {
                        console.error('Comparison failed:', e);
                        // Fall back to submitting the form directly
                        this.$refs.form.removeEventListener('submit', this.compareTexts);
                        this.$refs.form.submit();
                    } finally {
                        this.comparing = false;
                    }
                },

                submitForm() {
                    // Remove the submit handler and submit the real form
                    this.$refs.form.removeAttribute('x-on:submit.prevent');
                    this.$nextTick(() => {
                        this.$refs.form.submit();
                    });
                },

                async fetchLogs(page) {
                    if (page < 1 || page > this.lastPage && this.lastPage > 0) return;
                    this.loading = true;
                    try {
                        const params = new URLSearchParams({ page });
                        if (this.search) params.set('search', this.search);
                        const response = await fetch(`{{ route('api.wave-outputs') }}?${params}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });
                        const data = await response.json();
                        this.logs = data.data;
                        this.currentPage = data.current_page;
                        this.lastPage = data.last_page;
                    } catch (e) {
                        console.error('Failed to fetch logs:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                selectLog(log) {
                    this.originalText = log.output;
                    this.activityLogId = log.id;
                    this.modalOpen = false;
                }
            };
        }
    </script>
</x-layouts.app>

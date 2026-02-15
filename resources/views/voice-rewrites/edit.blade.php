<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-cyan rounded-full"></div>
            <h1>Edit Rewrite Pair</h1>
        </div>

        <p class="text-text-muted mb-6">
            Voice Profile: <a href="{{ route('voice-profiles.show', $voiceProfile) }}" class="text-cortex-lavender">{{ $voiceProfile->name }}</a>
        </p>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('voice-rewrites.update', [$voiceProfile, $rewrite]) }}" id="rewrite-form" data-ajax-save class="bg-cortex-panel-light rounded-xl p-6">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="original_text" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Original Text</label>
                <textarea
                    id="original_text"
                    name="original_text"
                    rows="10"
                    required
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan font-mono text-sm"
                >{{ old('original_text', $rewrite->original_text) }}</textarea>
            </div>

            <div class="mb-6">
                <label for="rewritten_text" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Rewritten Text</label>
                <textarea
                    id="rewritten_text"
                    name="rewritten_text"
                    rows="10"
                    required
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan font-mono text-sm"
                >{{ old('rewritten_text', $rewrite->rewritten_text) }}</textarea>
            </div>

            <div class="mb-8">
                <label for="notes" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Notes <span class="text-text-muted normal-case tracking-normal font-normal">(optional)</span></label>
                <input
                    type="text"
                    id="notes"
                    name="notes"
                    value="{{ old('notes', $rewrite->notes) }}"
                    maxlength="255"
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                    placeholder="e.g., Shortened sentences, removed jargon, added conversational tone"
                >
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update Rewrite Pair
                </button>
                <a href="{{ route('voice-profiles.show', $voiceProfile) }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>

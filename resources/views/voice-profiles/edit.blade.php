<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-cyan rounded-full"></div>
            <h1>Edit Voice Profile</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('voice-profiles.update', $voiceProfile) }}" id="voice-profile-form" data-ajax-save class="bg-cortex-panel-light rounded-xl p-6">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $voiceProfile->name) }}"
                    required
                    autofocus
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >
            </div>

            <div class="mb-8">
                <label for="content" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Voice Profile Content</label>
                <p class="text-text-muted text-sm mb-3">This text will be prepended to the system prompt of any node that uses this voice profile.</p>
                <textarea
                    id="content"
                    name="content"
                    rows="20"
                    required
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan font-mono text-sm"
                >{{ old('content', $voiceProfile->content) }}</textarea>
            </div>

            @if($voiceProfile->nodes()->count() > 0)
                <div class="mb-8 p-4 bg-cortex-panel rounded-lg border border-gray">
                    <label class="block mb-2 font-semibold text-cortex-peach uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Nodes Using This Profile</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($voiceProfile->nodes as $node)
                            <a href="{{ route('nodes.edit', $node) }}" class="badge badge-peach no-underline">{{ $node->name }}</a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update Voice Profile
                </button>
                <a href="{{ route('voice-profiles.index') }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const key = 'refined_content_{{ $voiceProfile->id }}';
            const refined = sessionStorage.getItem(key);
            if (refined) {
                document.getElementById('content').value = refined;
                sessionStorage.removeItem(key);
            }
        });
    </script>
</x-layouts.app>

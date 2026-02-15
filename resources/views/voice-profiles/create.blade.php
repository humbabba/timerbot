<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-orange rounded-full"></div>
            <h1>Add Voice Profile</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('voice-profiles.store') }}" class="bg-cortex-panel-light rounded-xl p-6">
            @csrf

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                    placeholder="e.g., Kim Komando"
                >
            </div>

            <div class="mb-8">
                <label for="content" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Voice Profile Content</label>
                <p class="text-text-muted text-sm mb-3">This text will be prepended to the system prompt of any node that uses this voice profile. Describe the voice, tone, style, and include examples.</p>
                <textarea
                    id="content"
                    name="content"
                    rows="20"
                    required
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan font-mono text-sm"
                >{{ old('content') }}</textarea>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Create Voice Profile
                </button>
                <a href="{{ route('voice-profiles.index') }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>

<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Voice Profiles</h1>
            @if(auth()->user()->hasPermission('voice-profiles.create'))
                <a href="{{ route('voice-profiles.create') }}" class="btn btn-primary">
                    Add Voice Profile
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-cortex-green/20 border border-cortex-green/50 text-cortex-green rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-6 p-4 bg-cortex-panel-light rounded-xl">
            <form method="GET" action="{{ route('voice-profiles.index') }}" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label class="block font-semibold text-cortex-lavender uppercase text-sm tracking-wider mb-2" style="font-family: var(--font-display);">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="bg-cortex-panel border border-gray rounded-lg px-4 py-2 text-text min-w-[200px]">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-secondary">Filter</button>
                    @if(request()->hasAny(['search']))
                        <a href="{{ route('voice-profiles.index') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray">
            <table class="w-full">
                <thead>
                    <tr>
                        <th class="p-4 text-left border-b border-gray">Name</th>
                        <th class="p-4 text-left border-b border-gray">Nodes Using</th>
                        <th class="p-4 text-left border-b border-gray">Updated</th>
                        <th class="p-4 text-left border-b border-gray w-56">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($voiceProfiles as $profile)
                        <tr class="hover:bg-cortex-panel-light transition-colors">
                            <td class="p-4 border-b border-gray/50">
                                <a href="{{ route('voice-profiles.show', $profile) }}" class="text-cortex-orange hover:text-cortex-peach font-semibold">{{ $profile->name }}</a>
                            </td>
                            <td class="p-4 border-b border-gray/50">
                                <span class="badge badge-peach">{{ $profile->nodes()->count() }}</span>
                            </td>
                            <td class="p-4 border-b border-gray/50 text-text-muted text-sm">
                                {{ $profile->updated_at->diffForHumans() }}
                            </td>
                            <td class="p-4 border-b border-gray/50">
                                <div class="flex gap-2">
                                    @if(auth()->user()->hasPermission('voice-profiles.view'))
                                        <a href="{{ route('voice-profiles.show', $profile) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-lavender hover:bg-cortex-lavender hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            View
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('voice-profiles.edit'))
                                        <a href="{{ route('voice-profiles.edit', $profile) }}" class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider no-underline" style="font-family: var(--font-display);">
                                            Edit
                                        </a>
                                    @endif
                                    @if(auth()->user()->hasPermission('voice-profiles.delete'))
                                        <form method="POST" action="{{ route('voice-profiles.destroy', $profile) }}" id="delete-voice-profile-{{ $profile->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="button"
                                                x-data
                                                class="px-3 py-1.5 rounded-full bg-cortex-panel-light text-cortex-red hover:bg-cortex-red hover:text-white transition-all text-xs uppercase tracking-wider"
                                                style="font-family: var(--font-display);"
                                                x-on:click="$dispatch('confirm-delete', {
                                                    title: 'Delete Voice Profile',
                                                    message: 'Are you sure you want to delete the voice profile \'{{ $profile->name }}\'? Nodes using it will no longer have a voice profile assigned.',
                                                    formId: 'delete-voice-profile-{{ $profile->id }}'
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
            {{ $voiceProfiles->links() }}
        </div>
    </div>
</x-layouts.app>

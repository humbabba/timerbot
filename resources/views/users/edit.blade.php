<x-layouts.app>
    <div class="p-8 max-w-2xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-cyan rounded-full"></div>
            <h1>Edit User</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" data-ajax-save class="bg-cortex-panel-light rounded-xl p-6">
            @csrf
            @method('PUT')

            <div class="mb-6 flex flex-col items-center">
                <x-avatar :user="$user" :size="12" />
                <a href="https://gravatar.com/profile" target="_blank" rel="noopener noreferrer" class="mt-2 text-cortex-cyan text-xs hover:underline">
                    Edit Gravatar
                </a>
            </div>

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >
            </div>

            <div class="mb-6">
                <label for="email" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >
            </div>

            @if(auth()->id() === $user->id)
                <div class="mb-6">
                    <label for="starting_view" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Starting View</label>
                    <select
                        id="starting_view"
                        name="starting_view"
                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                    >
                        @foreach($startingViews as $value => $label)
                            <option value="{{ $value }}" {{ old('starting_view', $user->starting_view ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(auth()->user()->hasPermission('users.edit'))
                <div class="mb-8">
                    <label for="role" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Role</label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                    >
                        <option value="">Select a role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role', $userRoleIds[0] ?? null) == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update User
                </button>
                <a href="{{ route('users.show', $user) }}" class="btn bg-cortex-panel-light text-text hover:bg-gray no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>

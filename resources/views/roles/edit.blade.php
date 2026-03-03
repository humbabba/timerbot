<x-layouts.app>
    <div class="p-8 max-w-2xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-timerbot-teal rounded-none"></div>
            <h1>Edit Role</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('roles.update', $role) }}" data-ajax-save class="bg-timerbot-panel-light rounded-sm p-6">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $role->name) }}"
                    required
                    autofocus
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >
            </div>

            <div class="mb-6">
                <label for="description" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Description</label>
                <input
                    type="text"
                    id="description"
                    name="description"
                    value="{{ old('description', $role->description) }}"
                    required
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >
            </div>

            <div class="mb-8">
                <label class="block mb-3 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Permissions</label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($permissions as $permission)
                        <label class="flex items-center gap-3 p-3 bg-timerbot-panel rounded-sm cursor-pointer hover:bg-timerbot-panel-light transition-colors">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                {{ in_array($permission->id, old('permissions', $rolePermissionIds)) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-divider bg-timerbot-dark text-timerbot-lime focus:ring-timerbot-lime"
                            >
                            <div>
                                <span class="text-timerbot-lime font-semibold block">{{ $permission->name }}</span>
                                <span class="text-text-muted text-xs">{{ $permission->description }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-8">
                <label class="block mb-3 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Assignable Roles</label>
                <p class="text-text-muted text-xs mb-3">Which roles can users with this role assign to other users?</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($roles as $assignableRole)
                        <label class="flex items-center gap-3 p-3 bg-timerbot-panel rounded-sm cursor-pointer hover:bg-timerbot-panel-light transition-colors">
                            <input
                                type="checkbox"
                                name="assignable_roles[]"
                                value="{{ $assignableRole->id }}"
                                {{ in_array($assignableRole->id, old('assignable_roles', $assignableRoleIds)) ? 'checked' : '' }}
                                class="w-5 h-5 rounded border-divider bg-timerbot-dark text-timerbot-lime focus:ring-timerbot-lime"
                            >
                            <div>
                                <span class="text-timerbot-lime font-semibold block">{{ $assignableRole->name }}</span>
                                <span class="text-text-muted text-xs">{{ $assignableRole->description }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update Role
                </button>
                <a href="{{ route('roles.index') }}" class="btn bg-timerbot-panel-light text-text hover:bg-divider no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>

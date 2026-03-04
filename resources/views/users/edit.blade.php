<x-layouts.app>
    <div class="p-8 max-w-2xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-timerbot-teal rounded-none"></div>
            <h1>Edit User</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('users.update', $user) }}" data-ajax-save class="bg-timerbot-panel-light rounded-sm p-6">
            @csrf
            @method('PUT')

            <div class="mb-6 flex flex-col items-center">
                <x-avatar :user="$user" :size="12" />
                <a href="https://gravatar.com/profile" target="_blank" rel="noopener noreferrer" class="mt-2 text-timerbot-teal text-xs hover:underline">
                    Edit Gravatar
                </a>
            </div>

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    autofocus
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >
            </div>

            <div class="mb-6">
                <label for="email" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >
            </div>

            @if(auth()->id() === $user->id)
                <div class="mb-6">
                    <label for="starting_view" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Starting View</label>
                    <select
                        id="starting_view"
                        name="starting_view"
                        class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                    >
                        @foreach($startingViews as $value => $label)
                            <option value="{{ $value }}" {{ old('starting_view', $user->starting_view ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label for="theme" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Theme</label>
                    <select
                        id="theme"
                        name="theme"
                        class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                    >
                        <option value="light" {{ old('theme', $user->theme ?? 'light') === 'light' ? 'selected' : '' }}>Light</option>
                        <option value="dark" {{ old('theme', $user->theme ?? 'light') === 'dark' ? 'selected' : '' }}>Dark</option>
                    </select>
                </div>
            @endif

            @if(auth()->user()->hasPermission('users.edit'))
                <div class="mb-8">
                    <label for="role" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Role</label>
                    <select
                        id="role"
                        name="role"
                        required
                        class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
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

            @if($user->groups->count() > 0 || auth()->user()->hasPermission('users.edit'))
            <!-- Groups -->
            <div class="mb-8" x-data="{
                groups: {{ Js::from($user->groups->map(fn($g) => ['group_id' => $g->id, 'name' => $g->name, 'is_admin' => (bool) $g->pivot->is_admin])) }},
                soleAdminGroupIds: {{ Js::from($soleAdminGroupIds) }},
                @if(auth()->user()->hasPermission('users.edit'))
                allGroups: {{ Js::from($allGroups->map(fn($g) => ['id' => $g->id, 'name' => $g->name])) }},
                selectedGroupId: '',
                get availableGroups() {
                    const currentIds = this.groups.map(g => g.group_id);
                    return this.allGroups.filter(g => !currentIds.includes(g.id));
                },
                addGroup() {
                    if (!this.selectedGroupId) return;
                    const group = this.allGroups.find(g => g.id == this.selectedGroupId);
                    if (group && !this.groups.find(g => g.group_id == group.id)) {
                        this.groups.push({ group_id: group.id, name: group.name, is_admin: false });
                    }
                    this.selectedGroupId = '';
                },
                @endif
                removeGroup(index) {
                    this.groups.splice(index, 1);
                }
            }">
                <input type="hidden" name="has_groups_section" value="1">
                <label class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Groups</label>

                @if(auth()->user()->hasPermission('users.edit'))
                <!-- Add group -->
                <div class="flex gap-2 mb-3">
                    <select x-model="selectedGroupId" class="flex-1 p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal">
                        <option value="">Select a group...</option>
                        <template x-for="group in availableGroups" :key="group.id">
                            <option :value="group.id" x-text="group.name"></option>
                        </template>
                    </select>
                    <button type="button" @click="addGroup()" class="btn bg-timerbot-teal text-timerbot-black hover:bg-timerbot-teal/80">Add</button>
                </div>
                @endif

                <!-- Group list -->
                <div class="space-y-2">
                    <template x-for="(group, index) in groups" :key="group.group_id">
                        <div class="flex items-center justify-between p-2 bg-timerbot-panel rounded-sm border border-divider">
                            <input type="hidden" :name="'groups[' + index + '][group_id]'" :value="group.group_id">
                            <input type="hidden" :name="'groups[' + index + '][is_admin]'" :value="group.is_admin ? 1 : 0">
                            <span x-text="group.name" class="text-text text-sm font-semibold"></span>
                            <div class="flex items-center gap-3">
                                @if(auth()->user()->hasPermission('users.edit'))
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="checkbox" x-model="group.is_admin" class="accent-timerbot-green">
                                        <span class="text-text-muted text-xs">Admin</span>
                                    </label>
                                @else
                                    <span x-show="group.is_admin" class="text-timerbot-green text-xs font-semibold">Admin</span>
                                @endif
                                @if(auth()->user()->hasPermission('users.edit'))
                                    <button type="button" @click="removeGroup(index)" class="text-timerbot-red hover:text-timerbot-red/80 text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                        Remove
                                    </button>
                                @else
                                    <template x-if="!soleAdminGroupIds.includes(group.group_id)">
                                        <button type="button" @click="removeGroup(index)" class="text-timerbot-red hover:text-timerbot-red/80 text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                            Remove
                                        </button>
                                    </template>
                                    <template x-if="soleAdminGroupIds.includes(group.group_id)">
                                        <span class="text-text-muted text-xs" title="You are the only admin in this group">Sole admin</span>
                                    </template>
                                @endif
                            </div>
                        </div>
                    </template>
                    <p x-show="groups.length === 0" class="text-text-muted text-sm">No groups.</p>
                </div>
            </div>
            @endif

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update User
                </button>
                <a href="{{ route('users.show', $user) }}" class="btn bg-timerbot-panel-light text-text hover:bg-divider no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>

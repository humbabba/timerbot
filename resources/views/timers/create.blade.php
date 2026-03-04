<x-layouts.app>
    <div class="p-4 md:p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-timerbot-green rounded-none"></div>
            <h1>Create Timer</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('timers.store') }}" class="bg-timerbot-panel-light rounded-sm p-6"
              x-data="{
                  warnings: {{ json_encode(old('warnings', [
                      ['seconds_before' => 60, 'sound' => 'beep'],
                      ['seconds_before' => 30, 'sound' => 'chime'],
                      ['seconds_before' => 0, 'sound' => 'alarm'],
                  ])) }},
                  visibility: '{{ old('visibility', 'public') }}',
                  groupMode: '{{ old('group_id') ? 'existing' : (old('new_group_name') ? 'new' : 'new') }}',
                  groupId: '{{ old('group_id', '') }}',
                  newGroupName: '{{ old('new_group_name', '') }}',
                  members: {{ json_encode(old('members', [])) }},
                  userSearch: '',
                  searchResults: [],
                  searchTimeout: null,
                  addWarning() {
                      this.warnings.push({ seconds_before: 30, sound: 'beep' });
                  },
                  removeWarning(index) {
                      this.warnings.splice(index, 1);
                  },
                  async searchUsers() {
                      if (this.userSearch.length < 2) {
                          this.searchResults = [];
                          return;
                      }
                      clearTimeout(this.searchTimeout);
                      this.searchTimeout = setTimeout(async () => {
                          const res = await fetch(`{{ route('groups.search-users') }}?q=${encodeURIComponent(this.userSearch)}`);
                          this.searchResults = await res.json();
                      }, 300);
                  },
                  addMember(user) {
                      if (!this.members.find(m => m.user_id == user.id)) {
                          this.members.push({ user_id: user.id, name: user.name, email: user.email, is_admin: false });
                      }
                      this.userSearch = '';
                      this.searchResults = [];
                  },
                  removeMember(index) {
                      this.members.splice(index, 1);
                  },
                  loadGroupMembers() {
                      if (!this.groupId) return;
                      // Members will be managed inline — when switching to existing group,
                      // we load them from the groups data passed to the view
                      const group = {{ Js::from($groups->keyBy('id')) }}[this.groupId];
                      if (group && group.members) {
                          this.members = group.members
                              .filter(m => m.id != {{ auth()->id() }})
                              .map(m => ({
                                  user_id: m.id,
                                  name: m.name,
                                  email: m.email,
                                  is_admin: m.pivot?.is_admin || false
                              }));
                      } else {
                          this.members = [];
                      }
                  }
              }">
            @csrf

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >
            </div>

            <!-- Visibility -->
            <div class="mb-6">
                <label class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Visibility</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="visibility" value="public" x-model="visibility" class="accent-timerbot-green">
                        <span class="text-text">Public</span>
                        <span class="text-text-muted text-xs">(anyone can view)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="visibility" value="private" x-model="visibility" class="accent-timerbot-green">
                        <span class="text-text">Private</span>
                        <span class="text-text-muted text-xs">(group members only)</span>
                    </label>
                </div>
            </div>

            <!-- Group Management -->
            <div class="mb-6 p-4 bg-timerbot-panel rounded-sm border border-divider">
                <label class="block mb-3 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Group</label>
                <p class="text-text-muted text-sm mb-4">Assign a group to control who can run and manage this timer.</p>

                <div class="flex gap-4 mb-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" value="new" x-model="groupMode" class="accent-timerbot-green">
                        <span class="text-text text-sm">Create new group</span>
                    </label>
                    @if($groups->count())
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" value="existing" x-model="groupMode" class="accent-timerbot-green">
                            <span class="text-text text-sm">Use existing group</span>
                        </label>
                    @endif
                </div>

                <!-- New Group -->
                <div x-show="groupMode === 'new'" x-cloak class="mb-4">
                    <input
                        type="text"
                        name="new_group_name"
                        x-model="newGroupName"
                        placeholder="Group name..."
                        class="w-full p-3 bg-timerbot-dark border border-divider rounded-sm text-text focus:border-timerbot-teal"
                    >
                </div>

                <!-- Existing Group -->
                <div x-show="groupMode === 'existing'" x-cloak class="mb-4">
                    <select
                        name="group_id"
                        x-model="groupId"
                        @change="loadGroupMembers()"
                        class="w-full p-3 bg-timerbot-dark border border-divider rounded-sm text-text focus:border-timerbot-teal"
                    >
                        <option value="">Select a group...</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Member Management -->
                <div class="mt-4">
                    <label class="block mb-2 font-semibold text-timerbot-teal uppercase text-xs tracking-wider" style="font-family: var(--font-display);">Members</label>
                    <p class="text-text-muted text-xs mb-3">You are automatically added as a group admin.</p>

                    <!-- Search users -->
                    <div class="relative mb-3">
                        <input
                            type="text"
                            x-model="userSearch"
                            @input="searchUsers()"
                            placeholder="Search users by name or email..."
                            class="w-full p-2 bg-timerbot-dark border border-divider rounded-sm text-text text-sm focus:border-timerbot-teal"
                        >
                        <!-- Search results dropdown -->
                        <div x-show="searchResults.length > 0" x-cloak
                             class="absolute z-50 w-full mt-1 bg-timerbot-panel border border-divider rounded-sm max-h-48 overflow-y-auto">
                            <template x-for="result in searchResults" :key="result.id">
                                <button type="button"
                                        @click="addMember(result)"
                                        class="block w-full text-left px-3 py-2 hover:bg-timerbot-lime hover:text-timerbot-black transition-colors text-sm">
                                    <span x-text="result.name" class="font-semibold"></span>
                                    <span x-text="result.email" class="text-text-muted ml-2 text-xs"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Member list -->
                    <div class="space-y-2">
                        <template x-for="(member, index) in members" :key="member.user_id">
                            <div class="flex items-center justify-between p-2 bg-timerbot-dark rounded-sm border border-divider">
                                <input type="hidden" :name="'members[' + index + '][user_id]'" :value="member.user_id">
                                <div>
                                    <span x-text="member.name" class="text-text text-sm font-semibold"></span>
                                    <span x-text="member.email" class="text-text-muted text-xs ml-2"></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="checkbox"
                                               :name="'members[' + index + '][is_admin]'"
                                               x-model="member.is_admin"
                                               :value="1"
                                               class="accent-timerbot-green">
                                        <span class="text-text-muted text-xs">Admin</span>
                                    </label>
                                    <button type="button" @click="removeMember(index)" class="text-timerbot-red hover:text-timerbot-red/80 text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label for="end_time" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">End time</label>
                <input
                    type="time"
                    id="end_time"
                    name="end_time"
                    value="{{ old('end_time') }}"
                    required
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >
            </div>

            <div class="mb-6">
                <label for="participant_count" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Participants</label>
                <input
                    type="number"
                    id="participant_count"
                    name="participant_count"
                    value="{{ old('participant_count', 4) }}"
                    required
                    min="1"
                    max="999"
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Participant Term</label>
                <p class="text-text-muted text-sm mb-4">Customize the label used for participants (e.g. "speaker", "guy").</p>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label class="block text-xs text-text-muted mb-1">Singular</label>
                        <input
                            type="text"
                            name="participant_term"
                            value="{{ old('participant_term') }}"
                            placeholder="speaker"
                            maxlength="50"
                            class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                        >
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs text-text-muted mb-1">Plural</label>
                        <input
                            type="text"
                            name="participant_term_plural"
                            value="{{ old('participant_term_plural') }}"
                            placeholder="speakers"
                            maxlength="50"
                            class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                        >
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <label for="overtime_reset_minutes" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Overtime Reset (minutes)</label>
                        <p class="text-text-muted text-sm mb-4">Auto-reset if left running this many minutes past the end time.</p>
                        <input
                            type="number"
                            id="overtime_reset_minutes"
                            name="overtime_reset_minutes"
                            value="{{ old('overtime_reset_minutes', 5) }}"
                            required
                            min="1"
                            max="60"
                            class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                        >
                    </div>
                    <div class="flex-1">
                        <label for="undo_duration_seconds" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Undo Duration (seconds)</label>
                        <p class="text-text-muted text-sm mb-4">How long the undo button stays available after passing to the next participant.</p>
                        <input
                            type="number"
                            id="undo_duration_seconds"
                            name="undo_duration_seconds"
                            value="{{ old('undo_duration_seconds', 10) }}"
                            required
                            min="1"
                            max="60"
                            class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                        >
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Warnings</label>
                <p class="text-text-muted text-sm mb-4">Configure sound warnings before each {{ old('participant_term', 'speaker') }}'s time expires. Use negative values for warning sounds after expiration.</p>

                <div class="space-y-3">
                    <template x-for="(warning, index) in warnings" :key="index">
                        <div class="flex flex-col md:flex-row gap-3 md:items-center p-3 bg-timerbot-panel rounded-sm border border-divider">
                            <div class="flex-1">
                                <label class="block text-xs text-text-muted mb-1">Countdown</label>
                                <input
                                    type="number"
                                    :name="'warnings[' + index + '][seconds_before]'"
                                    x-model.number="warning.seconds_before"
                                    min="-3600"
                                    max="3600"
                                    class="w-full p-2 bg-timerbot-dark border border-divider rounded-sm text-text focus:border-timerbot-teal"
                                >
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs text-text-muted mb-1">Sound</label>
                                <select
                                    :name="'warnings[' + index + '][sound]'"
                                    x-model="warning.sound"
                                    class="w-full p-2 bg-timerbot-dark border border-divider rounded-sm text-text focus:border-timerbot-teal"
                                >
                                    <option value="alarm">Alarm</option>
                                    <option value="bell">Bell</option>
                                    <option value="beep">Beep</option>
                                    <option value="chime">Chime</option>
                                    <option value="clockRadio">Clock radio</option>
                                    <option value="dandelion">Dandelion</option>
                                    <option value="ding">Ding</option>
                                    <option value="twang">Twang</option>
                                    <option value="warning">Warning</option>
                                </select>
                            </div>
                            <div class="flex gap-2 md:mt-4">
                                <button type="button" @click="window.previewSound(warning.sound)" class="px-3 py-2 rounded-none bg-timerbot-panel border border-divider text-text text-xs uppercase tracking-wider hover:border-timerbot-teal transition-colors" style="font-family: var(--font-display);">
                                    &#9654;
                                </button>
                                <button type="button" @click="removeWarning(index)" class="px-3 py-2 rounded-none bg-timerbot-red text-white text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" @click="addWarning()" class="mt-4 btn btn-secondary">
                    + Add Warning
                </button>
            </div>

            <div class="mb-6">
                <label for="message" class="block mb-2 font-semibold text-timerbot-teal uppercase text-sm tracking-wider" style="font-family: var(--font-display);">{{ ucfirst(old('participant_term', 'speaker')) }} Message</label>
                <p class="text-text-muted text-sm mb-4">Displayed to {{ old('participant_term_plural', 'speakers') }} on the timer view. HTML is supported.</p>
                <textarea
                    id="message"
                    name="message"
                    rows="4"
                    class="w-full p-3 bg-timerbot-panel border border-divider rounded-sm text-text focus:border-timerbot-teal"
                >{{ old('message') }}</textarea>
            </div>

            <div class="flex flex-col md:flex-row gap-4">
                <button type="submit" class="btn btn-primary">
                    Create Timer
                </button>
                <a href="{{ route('timers.index') }}" class="btn bg-timerbot-panel text-text hover:bg-divider no-underline text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    @vite(['resources/js/sounds.js'])
</x-layouts.app>

<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-timerbot-cyan rounded-none"></div>
            <h1>Edit Timer</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if (session('status'))
            <div class="mb-6 p-4 bg-timerbot-green/20 border border-timerbot-green/50 text-timerbot-green rounded-sm">
                {{ session('status') }}
            </div>
        @endif

        @php
            $currentMembers = [];
            if ($timer->group) {
                $currentMembers = $timer->group->members
                    ->where('id', '!=', auth()->id())
                    ->map(fn($m) => [
                        'user_id' => $m->id,
                        'name' => $m->name,
                        'email' => $m->email,
                        'is_admin' => (bool) $m->pivot->is_admin,
                    ])->values()->toArray();
            }
        @endphp

        <form method="POST" action="{{ route('timers.update', $timer) }}" data-ajax-save class="bg-timerbot-panel-light rounded-sm p-6"
              x-data="{
                  warnings: {{ json_encode(old('warnings', $timer->warnings ?? [])) }},
                  visibility: '{{ old('visibility', $timer->visibility) }}',
                  groupMode: '{{ $timer->group_id ? 'existing' : 'new' }}',
                  groupId: '{{ old('group_id', $timer->group_id ?? '') }}',
                  newGroupName: '{{ old('new_group_name', '') }}',
                  members: {{ Js::from(old('members', $currentMembers)) }},
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
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $timer->name) }}"
                    required
                    autofocus
                    class="w-full p-3 bg-timerbot-panel border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                >
            </div>

            <!-- Visibility -->
            <div class="mb-6">
                <label class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Visibility</label>
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
            <div class="mb-6 p-4 bg-timerbot-panel rounded-sm border border-gray">
                <label class="block mb-3 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Group</label>
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
                        class="w-full p-3 bg-timerbot-dark border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                    >
                </div>

                <!-- Existing Group -->
                <div x-show="groupMode === 'existing'" x-cloak class="mb-4">
                    <select
                        name="group_id"
                        x-model="groupId"
                        @change="loadGroupMembers()"
                        class="w-full p-3 bg-timerbot-dark border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                    >
                        <option value="">Select a group...</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Member Management -->
                <div class="mt-4">
                    <label class="block mb-2 font-semibold text-timerbot-lavender uppercase text-xs tracking-wider" style="font-family: var(--font-display);">Members</label>
                    <p class="text-text-muted text-xs mb-3">You are automatically added as a group admin.</p>

                    <!-- Search users -->
                    <div class="relative mb-3">
                        <input
                            type="text"
                            x-model="userSearch"
                            @input="searchUsers()"
                            placeholder="Search users by name or email..."
                            class="w-full p-2 bg-timerbot-dark border border-gray rounded-sm text-text text-sm focus:border-timerbot-cyan"
                        >
                        <!-- Search results dropdown -->
                        <div x-show="searchResults.length > 0" x-cloak
                             class="absolute z-50 w-full mt-1 bg-timerbot-panel border border-gray rounded-sm max-h-48 overflow-y-auto">
                            <template x-for="result in searchResults" :key="result.id">
                                <button type="button"
                                        @click="addMember(result)"
                                        class="block w-full text-left px-3 py-2 hover:bg-timerbot-blue hover:text-timerbot-black transition-colors text-sm">
                                    <span x-text="result.name" class="font-semibold"></span>
                                    <span x-text="result.email" class="text-text-muted ml-2 text-xs"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Member list -->
                    <div class="space-y-2">
                        <template x-for="(member, index) in members" :key="member.user_id">
                            <div class="flex items-center justify-between p-2 bg-timerbot-dark rounded-sm border border-gray">
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
                <label for="end_time" class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">End Time</label>
                <input
                    type="time"
                    id="end_time"
                    name="end_time"
                    value="{{ old('end_time', \Illuminate\Support\Str::substr($timer->end_time, 0, 5)) }}"
                    required
                    class="w-full p-3 bg-timerbot-panel border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                >
            </div>

            <div class="mb-6">
                <label for="participant_count" class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Participants</label>
                <input
                    type="number"
                    id="participant_count"
                    name="participant_count"
                    value="{{ old('participant_count', $timer->participant_count) }}"
                    required
                    min="1"
                    max="999"
                    class="w-full p-3 bg-timerbot-panel border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                >
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Warnings</label>
                <p class="text-text-muted text-sm mb-4">Configure sound warnings before each speaker's time expires.</p>

                <div class="space-y-3">
                    <template x-for="(warning, index) in warnings" :key="index">
                        <div class="flex gap-3 items-center p-3 bg-timerbot-panel rounded-sm border border-gray">
                            <div class="flex-1">
                                <label class="block text-xs text-text-muted mb-1">Countdown</label>
                                <input
                                    type="number"
                                    :name="'warnings[' + index + '][seconds_before]'"
                                    x-model.number="warning.seconds_before"
                                    min="-3600"
                                    max="3600"
                                    class="w-full p-2 bg-timerbot-dark border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                                >
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs text-text-muted mb-1">Sound</label>
                                <select
                                    :name="'warnings[' + index + '][sound]'"
                                    x-model="warning.sound"
                                    class="w-full p-2 bg-timerbot-dark border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                                >
                                    <option value="alarm">Alarm</option>
                                    <option value="bell">Bell</option>
                                    <option value="beep">Beep</option>
                                    <option value="chime">Chime</option>
                                    <option value="ding">Ding</option>
                                    <option value="twang">Twang</option>
                                    <option value="warning">Warning</option>
                                </select>
                            </div>
                            <button type="button" @click="window.previewSound(warning.sound)" class="mt-4 px-3 py-2 rounded-none bg-timerbot-panel border border-gray text-text text-xs uppercase tracking-wider hover:border-timerbot-cyan transition-colors" style="font-family: var(--font-display);">
                                &#9654;
                            </button>
                            <button type="button" @click="removeWarning(index)" class="mt-4 px-3 py-2 rounded-none bg-timerbot-red text-white text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                                Remove
                            </button>
                        </div>
                    </template>
                </div>

                <button type="button" @click="addWarning()" class="mt-4 btn btn-secondary">
                    + Add Warning
                </button>
            </div>

            <div class="mb-6">
                <label for="message" class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Participant Message</label>
                <p class="text-text-muted text-sm mb-4">Displayed to participants on the timer view. HTML is supported.</p>
                <textarea
                    id="message"
                    name="message"
                    rows="4"
                    class="w-full p-3 bg-timerbot-panel border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                >{{ old('message', $timer->message) }}</textarea>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update Timer
                </button>
                <a href="{{ route('timers.show', $timer) }}" class="btn bg-timerbot-panel text-text hover:bg-gray no-underline">
                    Cancel
                </a>
                @if($timer->canRun(auth()->user()))
                    <a href="{{ route('timers.run', $timer) }}" class="btn bg-timerbot-orange text-timerbot-black hover:bg-timerbot-peach no-underline">
                        Run Timer
                    </a>
                @endif
            </div>
        </form>
    </div>
    <script>
    (function () {
        let ctx;
        function c() { return ctx || (ctx = new (window.AudioContext || window.webkitAudioContext)()); }
        function tone(type, freq, dur) {
            const x = c(), o = x.createOscillator(), g = x.createGain();
            o.type = type; o.frequency.value = freq;
            g.gain.setValueAtTime(1, x.currentTime);
            g.gain.exponentialRampToValueAtTime(0.01, x.currentTime + dur);
            o.connect(g).connect(x.destination);
            o.start(x.currentTime); o.stop(x.currentTime + dur);
        }
        const sounds = {
            beep()  { tone('sine', 880, 0.8); },
            ding()  { tone('square', 220, 0.8); },
            bell()  { tone('sine', 1046, 1.0); },
            twang() { tone('sawtooth', 150, 1.2); },
            chime() {
                const x = c();
                [523.25,659.25,783.99,523.25,659.25,783.99].forEach((f, i) => {
                    const o = x.createOscillator(), g = x.createGain();
                    o.type = 'sine'; o.frequency.value = f;
                    const t = x.currentTime + i * 0.2;
                    g.gain.setValueAtTime(1, t);
                    g.gain.exponentialRampToValueAtTime(0.1, t + 0.3);
                    o.connect(g).connect(x.destination); o.start(t); o.stop(t + 0.3);
                });
            },
            alarm() {
                const x = c(), o = x.createOscillator(), g = x.createGain(), now = x.currentTime;
                o.type = 'square'; o.connect(g); g.connect(x.destination);
                for (let i = 0; i < 8; i++) {
                    const t = now + i * 0.15;
                    o.frequency.setValueAtTime(1000, t);
                    o.frequency.setValueAtTime(1200, t + 0.1);
                    g.gain.setValueAtTime(1, t);
                    g.gain.setValueAtTime(0, t + 0.1);
                }
                o.start(now); o.stop(now + 1.8);
            },
            warning() {
                const x = c(), now = x.currentTime;
                [523.25,659.25,783.99,1046.50].forEach((f, i) => {
                    const o = x.createOscillator(), g = x.createGain(), t = now + i * 0.12;
                    o.type = 'triangle'; o.frequency.value = f;
                    o.connect(g); g.connect(x.destination);
                    g.gain.setValueAtTime(0, t);
                    g.gain.linearRampToValueAtTime(1, t + 0.05);
                    g.gain.exponentialRampToValueAtTime(0.0001, t + 0.5);
                    o.start(t); o.stop(t + 0.5);
                });
            }
        };
        window.previewSound = function (name) { const fn = sounds[name]; if (fn) fn(); };
    })();
    </script>
</x-layouts.app>

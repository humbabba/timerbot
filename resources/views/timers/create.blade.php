<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-timerbot-orange rounded-none"></div>
            <h1>Create Timer</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('timers.store') }}" data-ajax-save class="bg-timerbot-panel-light rounded-sm p-6"
              x-data="{
                  warnings: {{ json_encode(old('warnings', [
                      ['seconds_before' => 60, 'sound' => 'beep'],
                      ['seconds_before' => 30, 'sound' => 'chime'],
                      ['seconds_before' => 10, 'sound' => 'horn'],
                  ])) }},
                  addWarning() {
                      this.warnings.push({ seconds_before: 30, sound: 'beep' });
                  },
                  removeWarning(index) {
                      this.warnings.splice(index, 1);
                  }
              }">
            @csrf

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    autofocus
                    class="w-full p-3 bg-timerbot-panel border border-gray rounded-sm text-text focus:border-timerbot-cyan"
                >
            </div>

            <div class="mb-6">
                <label for="end_time" class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">End Time</label>
                <input
                    type="time"
                    id="end_time"
                    name="end_time"
                    value="{{ old('end_time') }}"
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
                    value="{{ old('participant_count', 4) }}"
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
                                <label class="block text-xs text-text-muted mb-1">Seconds before</label>
                                <input
                                    type="number"
                                    :name="'warnings[' + index + '][seconds_before]'"
                                    x-model.number="warning.seconds_before"
                                    min="1"
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
                                    <option value="beep">Beep</option>
                                    <option value="buzzer">Buzzer</option>
                                    <option value="chime">Chime</option>
                                    <option value="bell">Bell</option>
                                    <option value="horn">Horn</option>
                                </select>
                            </div>
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

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Create Timer
                </button>
                <a href="{{ route('timers.index') }}" class="btn bg-timerbot-panel text-text hover:bg-gray no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-layouts.app>

<x-layouts.app>
    <div class="p-8">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <h1>Settings</h1>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-timerbot-green/20 border border-timerbot-green/50 text-timerbot-green rounded-sm">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" data-ajax-save>
            @csrf
            @method('PUT')

            @foreach($settings as $group => $groupSettings)
                <div class="mb-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-3 h-8 bg-timerbot-orange rounded-none"></div>
                        <h2 class="capitalize">{{ $group }} Settings</h2>
                    </div>

                    <div class="bg-timerbot-panel-light rounded-sm p-6 space-y-6">
                        @foreach($groupSettings as $setting)
                            <div class="flex flex-col md:flex-row md:items-start gap-4">
                                <label for="settings_{{ $setting->key }}" class="font-semibold text-timerbot-lavender md:w-64 uppercase text-sm tracking-wider md:pt-2" style="font-family: var(--font-display);">
                                    {{ str_replace('_', ' ', ucwords($setting->key, '_')) }}
                                </label>
                                <div class="flex-1">
                                    @if($setting->type === 'boolean')
                                        <label class="flex items-center gap-3 cursor-pointer">
                                            <input type="hidden" name="settings[{{ $setting->key }}]" value="0">
                                            <div class="relative">
                                                <input
                                                    type="checkbox"
                                                    name="settings[{{ $setting->key }}]"
                                                    id="settings_{{ $setting->key }}"
                                                    value="1"
                                                    {{ $setting->casted_value ? 'checked' : '' }}
                                                    class="sr-only peer"
                                                >
                                                <div class="w-12 h-6 bg-timerbot-panel rounded-none peer-checked:bg-timerbot-green transition-colors"></div>
                                                <div class="absolute left-1 top-1 w-4 h-4 bg-text rounded-none peer-checked:translate-x-6 transition-transform"></div>
                                            </div>
                                            <span class="text-text-muted text-sm">{{ $setting->description }}</span>
                                        </label>
                                    @elseif($setting->type === 'integer')
                                        <div>
                                            <input
                                                type="number"
                                                name="settings[{{ $setting->key }}]"
                                                id="settings_{{ $setting->key }}"
                                                value="{{ $setting->value }}"
                                                class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text w-32"
                                            >
                                            @if($setting->description)
                                                <p class="text-text-muted text-sm mt-2">{{ $setting->description }}</p>
                                            @endif
                                        </div>
                                    @elseif($setting->type === 'richtext')
                                        <div class="w-full">
                                            <input
                                                type="hidden"
                                                name="settings[{{ $setting->key }}]"
                                                id="settings_{{ $setting->key }}"
                                                value="{{ $setting->value }}"
                                            >
                                            <trix-editor
                                                input="settings_{{ $setting->key }}"
                                                class="trix-content"
                                            ></trix-editor>
                                            @if($setting->description)
                                                <p class="text-text-muted text-sm mt-2">{{ $setting->description }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            <input
                                                type="text"
                                                name="settings[{{ $setting->key }}]"
                                                id="settings_{{ $setting->key }}"
                                                value="{{ $setting->value }}"
                                                class="bg-timerbot-panel border border-gray rounded-sm px-4 py-2 text-text w-full max-w-md"
                                            >
                                            @if($setting->description)
                                                <p class="text-text-muted text-sm mt-2">{{ $setting->description }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>

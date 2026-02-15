<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex items-center gap-4 mb-8">
            <div class="w-3 h-10 bg-cortex-cyan rounded-full"></div>
            <h1>Edit Node</h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('nodes.update', $node) }}" id="node-form" data-ajax-save class="bg-cortex-panel-light rounded-xl p-6">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label for="name" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $node->name) }}"
                    required
                    autofocus
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Inputs</label>
                <p class="text-text-muted text-sm mb-4">Define the HTML inputs that users will fill out for this node.</p>

                <div id="inputs-container" class="space-y-4">
                    @php
                        $inputs = old('inputs', $node->inputs ?? []);
                    @endphp
                    @foreach($inputs as $index => $input)
                        @php
                        $hasOptions = in_array($input['type'] ?? '', ['select', 'checkbox', 'radio']);
                        $isFile = ($input['type'] ?? '') === 'file';
                    @endphp
                        <div class="input-row p-4 bg-cortex-panel rounded-lg border border-gray">
                            <div class="flex gap-4">
                                <div class="flex flex-col gap-1 justify-center">
                                    <button type="button" onclick="moveInputUp(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move up">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </button>
                                    <button type="button" onclick="moveInputDown(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move down">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                                <div class="flex-1">
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-cortex-blue">Field name</label>
                                            <input type="text" name="inputs[{{ $index }}][name]" value="{{ $input['name'] ?? '' }}" required class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="e.g., company_name">
                                        </div>
                                        <div>
                                            <label class="block mb-1 text-sm font-medium text-cortex-blue">Label</label>
                                            <input type="text" name="inputs[{{ $index }}][label]" value="{{ $input['label'] ?? '' }}" required class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="e.g., Company Name">
                                        </div>
                                    </div>
                                    <div class="flex gap-4 items-end mb-3">
                                        <div class="w-40">
                                            <label class="block mb-1 text-sm font-medium text-cortex-blue">Type</label>
                                            <select name="inputs[{{ $index }}][type]" onchange="toggleOptionsField(this)" required class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan">
                                                <option value="text" {{ ($input['type'] ?? '') == 'text' ? 'selected' : '' }}>Text</option>
                                                <option value="textarea" {{ ($input['type'] ?? '') == 'textarea' ? 'selected' : '' }}>Textarea</option>
                                                <option value="number" {{ ($input['type'] ?? '') == 'number' ? 'selected' : '' }}>Number</option>
                                                <option value="email" {{ ($input['type'] ?? '') == 'email' ? 'selected' : '' }}>Email</option>
                                                <option value="select" {{ ($input['type'] ?? '') == 'select' ? 'selected' : '' }}>Select</option>
                                                <option value="checkbox" {{ ($input['type'] ?? '') == 'checkbox' ? 'selected' : '' }}>Checkbox</option>
                                                <option value="radio" {{ ($input['type'] ?? '') == 'radio' ? 'selected' : '' }}>Radio</option>
                                                <option value="file" {{ ($input['type'] ?? '') == 'file' ? 'selected' : '' }}>File (Image Upload)</option>
                                            </select>
                                        </div>
                                        <div class="options-field flex-1 {{ $hasOptions ? '' : 'hidden' }}">
                                            <label class="block mb-1 text-sm font-medium text-cortex-blue">Options</label>
                                            <input type="text" name="inputs[{{ $index }}][options]" value="{{ $input['options'] ?? '' }}" class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="Comma-separated">
                                        </div>
                                        <label class="flex items-center gap-2 pb-2">
                                            <input type="checkbox" name="inputs[{{ $index }}][required]" value="1" {{ !empty($input['required']) ? 'checked' : '' }} class="w-5 h-5 rounded border-gray bg-cortex-dark text-cortex-orange">
                                            <span class="text-sm">Required</span>
                                        </label>
                                        <button type="button" onclick="this.closest('.input-row').remove()" class="px-3 py-1.5 rounded-full bg-cortex-red text-white text-xs uppercase tracking-wider" style="font-family: var(--font-display);">Remove</button>
                                    </div>
                                    <div class="default-field {{ $isFile ? 'hidden' : '' }}">
                                        <label class="block mb-1 text-sm font-medium text-cortex-blue">Default Value</label>
                                        <input type="text" name="inputs[{{ $index }}][default]" value="{{ $input['default'] ?? '' }}" class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="Optional default value">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <button type="button" onclick="addInput()" class="mt-4 btn btn-secondary">
                    + Add Input
                </button>
            </div>

            <div class="mb-6">
                <label for="voice_profile_id" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Voice Profile</label>
                <select
                    id="voice_profile_id"
                    name="voice_profile_id"
                    class="w-full max-w-xs p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >
                    <option value="">-- None --</option>
                    @foreach($voiceProfiles as $profile)
                        <option value="{{ $profile->id }}" {{ old('voice_profile_id', $node->voice_profile_id) == $profile->id ? 'selected' : '' }}>
                            {{ $profile->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-text-muted text-sm mt-2">Optionally prepend a voice/style profile to the system prompt.</p>
            </div>

            <div class="mb-6">
                <label for="system_text" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">System Text</label>
                <textarea
                    id="system_text"
                    name="system_text"
                    rows="6"
                    class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >{{ old('system_text', $node->system_text) }}</textarea>
            </div>

            <div class="mb-6">
                <label for="max_tokens" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Max Tokens</label>
                <input
                    type="number"
                    id="max_tokens"
                    name="max_tokens"
                    value="{{ old('max_tokens', $node->max_tokens ?? 8192) }}"
                    required
                    min="1"
                    max="65536"
                    class="w-full max-w-xs p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                >
                <p class="text-text-muted text-sm mt-2">Maximum number of tokens in the response (1-65536)</p>
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="style_check"
                        value="1"
                        {{ old('style_check', $node->style_check) ? 'checked' : '' }}
                        class="w-5 h-5 rounded border-gray bg-cortex-dark text-cortex-orange"
                    >
                    <span class="font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Enable Style Check</span>
                </label>
                <p class="text-text-muted text-sm mt-2 ml-8">Run the "Kim's voice" style checker on this node's output during wave execution.</p>
            </div>

            @php
                $hasUrlFeature = $node->url_source_field && $node->url_target_field;
            @endphp

            <div class="mb-8">
                <button
                    type="button"
                    id="toggle-url-feature"
                    onclick="document.getElementById('url-feature-config').classList.remove('hidden'); this.classList.add('hidden');"
                    class="{{ $hasUrlFeature ? 'hidden' : '' }} btn btn-secondary"
                >
                    + Add Pull from URL Feature
                </button>

                <div id="url-feature-config" class="{{ $hasUrlFeature ? '' : 'hidden' }} p-4 bg-cortex-panel rounded-lg border border-gray mt-4">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <label class="block font-semibold text-cortex-peach uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Pull Data from URL</label>
                            <p class="text-text-muted text-sm">Configure a button to fetch content from a URL and populate a field.</p>
                        </div>
                        <button
                            type="button"
                            onclick="removeUrlFeature()"
                            class="px-3 py-1.5 rounded-full bg-cortex-red text-white text-xs uppercase tracking-wider"
                            style="font-family: var(--font-display);"
                        >
                            Remove
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="url_source_field" class="block mb-1 text-sm font-medium text-cortex-blue">URL source field</label>
                            <select
                                id="url_source_field"
                                name="url_source_field"
                                class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan"
                            >
                                <option value="">-- None --</option>
                                @foreach($node->inputs ?? [] as $input)
                                    <option value="{{ $input['name'] }}" {{ old('url_source_field', $node->url_source_field) == $input['name'] ? 'selected' : '' }}>
                                        {{ $input['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-text-muted text-xs mt-1">Field containing the URL to fetch</p>
                        </div>
                        <div>
                            <label for="url_target_field" class="block mb-1 text-sm font-medium text-cortex-blue">Target field</label>
                            <select
                                id="url_target_field"
                                name="url_target_field"
                                class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan"
                            >
                                <option value="">-- None --</option>
                                @foreach($node->inputs ?? [] as $input)
                                    <option value="{{ $input['name'] }}" {{ old('url_target_field', $node->url_target_field) == $input['name'] ? 'selected' : '' }}>
                                        {{ $input['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-text-muted text-xs mt-1">Field to populate with fetched content</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn-primary">
                    Update Node
                </button>
                <a href="{{ route('nodes.index') }}" class="btn bg-cortex-panel text-text hover:bg-gray no-underline">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <script>
        function getNextInputIndex() {
            const container = document.getElementById('inputs-container');
            const inputs = container.querySelectorAll('input[name^="inputs["][name$="][name]"]');
            let maxIndex = -1;
            inputs.forEach(input => {
                const match = input.name.match(/inputs\[(\d+)\]/);
                if (match) {
                    maxIndex = Math.max(maxIndex, parseInt(match[1], 10));
                }
            });
            return maxIndex + 1;
        }

        function removeUrlFeature() {
            document.getElementById('url_source_field').value = '';
            document.getElementById('url_target_field').value = '';
            document.getElementById('url-feature-config').classList.add('hidden');
            document.getElementById('toggle-url-feature')?.classList.remove('hidden');
        }

        function moveInputUp(btn) {
            const row = btn.closest('.input-row');
            const prev = row.previousElementSibling;
            if (prev && prev.classList.contains('input-row')) {
                row.parentNode.insertBefore(row, prev);
            }
        }

        function moveInputDown(btn) {
            const row = btn.closest('.input-row');
            const next = row.nextElementSibling;
            if (next && next.classList.contains('input-row')) {
                row.parentNode.insertBefore(next, row);
            }
        }

        function toggleOptionsField(select) {
            const row = select.closest('.input-row');
            const optionsField = row.querySelector('.options-field');
            const defaultField = row.querySelector('.default-field');
            const typesWithOptions = ['select', 'checkbox', 'radio'];
            if (typesWithOptions.includes(select.value)) {
                optionsField.classList.remove('hidden');
            } else {
                optionsField.classList.add('hidden');
            }
            if (defaultField) {
                if (select.value === 'file') {
                    defaultField.classList.add('hidden');
                } else {
                    defaultField.classList.remove('hidden');
                }
            }
        }

        function addInput() {
            const container = document.getElementById('inputs-container');
            const inputIndex = getNextInputIndex();
            const html = `
                <div class="input-row p-4 bg-cortex-panel rounded-lg border border-gray">
                    <div class="flex gap-4">
                        <div class="flex flex-col gap-1 justify-center">
                            <button type="button" onclick="moveInputUp(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move up">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                            </button>
                            <button type="button" onclick="moveInputDown(this)" class="p-1 rounded bg-cortex-panel-light hover:bg-cortex-blue hover:text-cortex-black transition-colors" title="Move down">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </div>
                        <div class="flex-1">
                            <div class="grid grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-cortex-blue">Field name</label>
                                    <input type="text" name="inputs[${inputIndex}][name]" required class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="e.g., company_name">
                                </div>
                                <div>
                                    <label class="block mb-1 text-sm font-medium text-cortex-blue">Label</label>
                                    <input type="text" name="inputs[${inputIndex}][label]" required class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="e.g., Company Name">
                                </div>
                            </div>
                            <div class="flex gap-4 items-end mb-3">
                                <div class="w-40">
                                    <label class="block mb-1 text-sm font-medium text-cortex-blue">Type</label>
                                    <select name="inputs[${inputIndex}][type]" onchange="toggleOptionsField(this)" required class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan">
                                        <option value="text">Text</option>
                                        <option value="textarea">Textarea</option>
                                        <option value="number">Number</option>
                                        <option value="email">Email</option>
                                        <option value="select">Select</option>
                                        <option value="checkbox">Checkbox</option>
                                        <option value="radio">Radio</option>
                                        <option value="file">File (Image Upload)</option>
                                    </select>
                                </div>
                                <div class="options-field flex-1 hidden">
                                    <label class="block mb-1 text-sm font-medium text-cortex-blue">Options</label>
                                    <input type="text" name="inputs[${inputIndex}][options]" class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="Comma-separated">
                                </div>
                                <label class="flex items-center gap-2 pb-2">
                                    <input type="checkbox" name="inputs[${inputIndex}][required]" value="1" class="w-5 h-5 rounded border-gray bg-cortex-dark text-cortex-orange">
                                    <span class="text-sm">Required</span>
                                </label>
                                <button type="button" onclick="this.closest('.input-row').remove()" class="px-3 py-1.5 rounded-full bg-cortex-red text-white text-xs uppercase tracking-wider" style="font-family: var(--font-display);">Remove</button>
                            </div>
                            <div class="default-field">
                                <label class="block mb-1 text-sm font-medium text-cortex-blue">Default Value</label>
                                <input type="text" name="inputs[${inputIndex}][default]" class="w-full p-2 bg-cortex-dark border border-gray rounded-lg text-text focus:border-cortex-cyan" placeholder="Optional default value">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
</x-layouts.app>

<x-layouts.app>
    @vite(['resources/js/node.js'])

    <div class="p-8 max-w-4xl">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <div class="flex items-center gap-4">
                <div class="w-3 h-10 bg-cortex-orange rounded-full"></div>
                <h1>{{ $node->name }}</h1>
            </div>
            <div class="flex gap-2 shrink-0">
                @if(auth()->user()->hasPermission('nodes.edit'))
                    <a href="{{ route('nodes.edit', $node) }}" class="btn bg-cortex-cyan text-cortex-black hover:bg-cortex-cyan/80 no-underline whitespace-nowrap">
                        Edit Node
                    </a>
                @endif
                <a href="{{ route('nodes.index') }}" class="btn bg-cortex-panel-light text-text hover:bg-gray no-underline whitespace-nowrap">
                    Back to Nodes
                </a>
            </div>
        </div>

        <div id="error-container" class="hidden mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg"></div>

        <div id="result-container" class="hidden mb-6 bg-cortex-panel-light rounded-xl overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-cortex-green via-cortex-cyan to-cortex-blue"></div>
            <div class="p-5">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-4">
                        <h2 class="text-cortex-green">Result</h2>
                        <span id="word-count" class="text-text-muted text-sm"></span>
                    </div>
                    <div class="flex gap-2">
                        <button id="download-button" onclick="downloadImage()" class="hidden px-4 py-2 rounded-full bg-cortex-panel text-cortex-green hover:bg-cortex-green hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                            Download
                        </button>
                        <button id="copy-button" onclick="copyResult(this)" class="px-4 py-2 rounded-full bg-cortex-panel text-cortex-cyan hover:bg-cortex-cyan hover:text-cortex-black transition-all text-xs uppercase tracking-wider" style="font-family: var(--font-display);">
                            Copy
                        </button>
                    </div>
                </div>
                <iframe id="result-output" class="w-full bg-white rounded-lg" style="min-height: 300px; border: none;"></iframe>
            </div>
        </div>

        <div id="loading-container" class="hidden mb-6 bg-cortex-panel-light rounded-xl p-8 flex flex-col items-center justify-center gap-4">
            <div class="w-12 h-12 border-4 border-cortex-panel border-t-cortex-orange rounded-full animate-spin"></div>
            <p class="text-cortex-orange uppercase tracking-wider" style="font-family: var(--font-display);">Processing Request...</p>
        </div>

        @if($node->inputs && count($node->inputs) > 0)
            <form id="wave-form" method="POST" action="{{ route('nodes.run', $node) }}" data-generate-image-url="{{ route('nodes.generate-image', $node) }}" enctype="multipart/form-data" class="bg-cortex-panel-light rounded-xl p-6">
                @csrf

                <div class="space-y-6 mb-8">
                    @foreach($node->inputs as $input)
                        <div>
                            <label for="input_{{ $input['name'] }}" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                                {{ $input['label'] }}
                                @if(!empty($input['required']))
                                    <span class="text-cortex-red">*</span>
                                @endif
                            </label>

                            @switch($input['type'])
                                @case('textarea')
                                    <textarea
                                        id="input_{{ $input['name'] }}"
                                        name="inputs[{{ $input['name'] }}]"
                                        rows="4"
                                        {{ !empty($input['required']) ? 'required' : '' }}
                                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                                    >{{ old('inputs.' . $input['name'], $previousInputs[$input['name']] ?? $input['default'] ?? '') }}</textarea>
                                    @break

                                @case('select')
                                    <select
                                        id="input_{{ $input['name'] }}"
                                        name="inputs[{{ $input['name'] }}]"
                                        {{ !empty($input['required']) ? 'required' : '' }}
                                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                                    >
                                        <option value="">Select an option</option>
                                        @foreach(explode(',', $input['options'] ?? '') as $option)
                                            @php $option = trim($option); @endphp
                                            @if($option)
                                                <option value="{{ $option }}" {{ old('inputs.' . $input['name'], $previousInputs[$input['name']] ?? $input['default'] ?? '') == $option ? 'selected' : '' }}>
                                                    {{ $option }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @break

                                @case('checkbox')
                                    @php
                                        $checkboxDefault = isset($input['default']) ? explode(',', $input['default']) : [];
                                        $checkboxDefault = array_map('trim', $checkboxDefault);
                                    @endphp
                                    <div class="space-y-2">
                                        @foreach(explode(',', $input['options'] ?? '') as $option)
                                            @php $option = trim($option); @endphp
                                            @if($option)
                                                <label class="flex items-center gap-3 p-3 bg-cortex-panel rounded-lg cursor-pointer hover:bg-gray transition-colors">
                                                    <input
                                                        type="checkbox"
                                                        name="inputs[{{ $input['name'] }}][]"
                                                        value="{{ $option }}"
                                                        {{ in_array($option, (array)(old('inputs.' . $input['name'], $previousInputs[$input['name']] ?? $checkboxDefault))) ? 'checked' : '' }}
                                                        class="w-5 h-5 rounded border-gray bg-cortex-dark text-cortex-orange"
                                                    >
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                    @break

                                @case('radio')
                                    <div class="space-y-2">
                                        @foreach(explode(',', $input['options'] ?? '') as $option)
                                            @php $option = trim($option); @endphp
                                            @if($option)
                                                <label class="flex items-center gap-3 p-3 bg-cortex-panel rounded-lg cursor-pointer hover:bg-gray transition-colors">
                                                    <input
                                                        type="radio"
                                                        name="inputs[{{ $input['name'] }}]"
                                                        value="{{ $option }}"
                                                        {{ old('inputs.' . $input['name'], $previousInputs[$input['name']] ?? $input['default'] ?? '') == $option ? 'checked' : '' }}
                                                        {{ !empty($input['required']) ? 'required' : '' }}
                                                        class="w-5 h-5 border-gray bg-cortex-dark text-cortex-orange"
                                                    >
                                                    <span>{{ $option }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                    @break

                                @case('file')
                                    <input
                                        type="file"
                                        id="input_{{ $input['name'] }}"
                                        name="inputs[{{ $input['name'] }}]"
                                        accept="image/*"
                                        {{ !empty($input['required']) ? 'required' : '' }}
                                        onchange="previewFileInput(this)"
                                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-cortex-orange file:text-cortex-black hover:file:bg-cortex-orange/80"
                                    >
                                    <div id="preview_{{ $input['name'] }}" class="hidden mt-3">
                                        <img src="" alt="Preview" class="max-h-48 rounded-lg border border-gray">
                                    </div>
                                    @break

                                @default
                                    <input
                                        type="{{ $input['type'] }}"
                                        id="input_{{ $input['name'] }}"
                                        name="inputs[{{ $input['name'] }}]"
                                        value="{{ old('inputs.' . $input['name'], $previousInputs[$input['name']] ?? $input['default'] ?? '') }}"
                                        {{ !empty($input['required']) ? 'required' : '' }}
                                        class="w-full p-3 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan"
                                    >
                            @endswitch

                            @if($node->url_source_field && $node->url_target_field && $input['name'] === $node->url_source_field)
                                <button
                                    type="button"
                                    id="fetch-url-button"
                                    onclick="fetchUrlContent()"
                                    data-source-field="input_{{ $node->url_source_field }}"
                                    data-target-field="input_{{ $node->url_target_field }}"
                                    data-fetch-url="{{ route('nodes.fetch-url', $node) }}"
                                    class="mt-2 btn btn-secondary"
                                >
                                    Pull Data from URL
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-4 items-center">
                    @if(auth()->user()->hasPermission('nodes.run'))
                        <button type="submit" id="run-button" class="btn btn-primary">
                            Run Node
                        </button>
                    @endif
                </div>
            </form>
        @else
            <div class="p-8 bg-cortex-panel-light rounded-xl text-center text-text-muted">
                <p style="font-family: var(--font-display);">This node has no inputs defined.</p>
            </div>
        @endif
    </div>
</x-layouts.app>

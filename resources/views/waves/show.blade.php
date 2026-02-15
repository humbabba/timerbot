<x-layouts.app>
    <div class="p-8 max-w-4xl">
        <div class="flex flex-col gap-4 md:flex-row md:justify-between md:items-center mb-8">
            <div class="flex items-center gap-4">
                <div class="w-3 h-10 bg-cortex-orange rounded-full"></div>
                <h1>{{ $wave->name }}</h1>
            </div>
            <div class="flex gap-2 shrink-0">
                @if(auth()->user()->hasPermission('waves.run'))
                    <a href="{{ route('waves.run', $wave) }}" class="btn btn-primary whitespace-nowrap">
                        Run Wave
                    </a>
                @endif
                <a href="{{ route('waves.index') }}" class="btn bg-cortex-panel-light text-text hover:bg-gray no-underline whitespace-nowrap">
                    Back to Waves
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if($wave->description)
            <div class="mb-8 p-4 bg-cortex-panel-light rounded-xl">
                <h2 class="text-cortex-lavender text-sm uppercase tracking-wider mb-2" style="font-family: var(--font-display);">Description</h2>
                <p class="text-text">{{ $wave->description }}</p>
            </div>
        @endif

        <div class="bg-cortex-panel-light rounded-xl overflow-hidden">
            <div class="h-1 bg-gradient-to-r from-cortex-orange via-cortex-peach to-cortex-lavender"></div>
            <div class="p-6">
                <h2 class="text-cortex-lavender text-sm uppercase tracking-wider mb-4" style="font-family: var(--font-display);">Node Chain</h2>

                @if($wave->nodes->count())
                    <div class="space-y-4">
                        @foreach($wave->nodes as $index => $node)
                            @php
                                $mappings = json_decode($node->pivot->mappings ?? '{}', true) ?: [];
                            @endphp
                            <div class="p-4 bg-cortex-panel rounded-lg border border-gray">
                                <div class="flex items-start gap-4">
                                    <span class="w-8 h-8 flex items-center justify-center rounded-full bg-cortex-orange text-cortex-black font-bold" style="font-family: var(--font-display);">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="flex-1">
                                        <h3 class="text-cortex-cyan font-semibold mb-2">{{ $node->name }}</h3>

                                        @if($node->inputs && count($node->inputs))
                                            <div class="mb-3">
                                                <span class="text-text-muted text-xs uppercase tracking-wider">Inputs:</span>
                                                <div class="flex flex-wrap gap-1 mt-1">
                                                    @foreach($node->inputs as $input)
                                                        <span class="badge badge-peach text-xs">{{ $input['label'] }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        @if($index > 0 && !empty($mappings))
                                            <div class="mt-3 pt-3 border-t border-gray/50">
                                                <span class="text-text-muted text-xs uppercase tracking-wider">Mappings:</span>
                                                <div class="mt-2 space-y-1">
                                                    @foreach($mappings as $targetField => $mapping)
                                                        @if(!empty($mapping['type']))
                                                            <div class="text-sm text-text">
                                                                <span class="text-cortex-lavender">{{ $targetField }}</span>
                                                                <span class="text-text-muted">←</span>
                                                                @if($mapping['type'] === 'output')
                                                                    <span class="text-cortex-green">Step {{ ($mapping['source_position'] ?? 0) + 1 }} output</span>
                                                                @else
                                                                    <span class="text-cortex-blue">Step {{ ($mapping['source_position'] ?? 0) + 1 }} input: {{ $mapping['source_field'] ?? 'unknown' }}</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(!$loop->last)
                                <div class="flex justify-center">
                                    <svg class="w-6 h-6 text-cortex-orange" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                    </svg>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-text-muted text-center py-8">No nodes configured for this wave.</p>
                @endif
            </div>
        </div>

        <div class="mt-6 flex gap-4">
            @if(auth()->user()->hasPermission('waves.edit'))
                <a href="{{ route('waves.edit', $wave) }}" class="btn btn-secondary">
                    Edit Wave
                </a>
            @endif
        </div>
    </div>
</x-layouts.app>

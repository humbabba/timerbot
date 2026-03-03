@props(['column', 'label', 'sort', 'direction'])

@php
    $isActive = $sort === $column;
    $nextDirection = $isActive && $direction === 'asc' ? 'desc' : 'asc';
    $params = array_merge(request()->except(['page']), ['sort' => $column, 'direction' => $nextDirection]);
@endphp

<th class="p-4 text-left border-b border-divider">
    <a href="{{ request()->url() . '?' . http_build_query($params) }}"
       class="inline-flex items-center gap-1 no-underline {{ $isActive ? 'text-timerbot-teal' : 'text-text hover:text-timerbot-teal' }} transition-colors">
        {{ $label }}
        <span class="inline-flex flex-col leading-none">
            <svg class="w-3 h-3 {{ $isActive && $direction === 'asc' ? 'text-timerbot-teal' : 'text-text-muted/40' }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 6l-5 5h10l-5-5z"/>
            </svg>
            <svg class="w-3 h-3 -mt-0.5 {{ $isActive && $direction === 'desc' ? 'text-timerbot-teal' : 'text-text-muted/40' }}" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 14l5-5H5l5 5z"/>
            </svg>
        </span>
    </a>
</th>

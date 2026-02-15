@props(['user', 'size' => 8])

@php
    $sizeClasses = [
        6 => 'w-6 h-6 text-xs',
        8 => 'w-8 h-8 text-sm',
        10 => 'w-10 h-10 text-base',
        12 => 'w-12 h-12 text-lg',
    ];
    $pixelSize = $size * 4;
    $classes = $sizeClasses[$size] ?? 'w-8 h-8 text-sm';
    $gravatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?d=404&s=' . ($pixelSize * 2);
    $initial = substr($user->name, 0, 1);
@endphp

<div
    x-data="{ imgError: false }"
    class="{{ $classes }} rounded-full overflow-hidden flex items-center justify-center bg-cortex-lavender"
>
    <img
        x-show="!imgError"
        x-on:error="imgError = true"
        src="{{ $gravatarUrl }}"
        alt="{{ $user->name }}"
        class="w-full h-full object-cover"
    >
    <span
        x-show="imgError"
        x-cloak
        class="text-cortex-black font-bold"
    >{{ $initial }}</span>
</div>

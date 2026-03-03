<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">

    <script>
        (function() {
            @auth
                var dbTheme = @json(auth()->user()->theme);
            @endauth
            var theme = (typeof dbTheme !== 'undefined' && dbTheme) ? dbTheme : (localStorage.getItem('theme') || 'light');
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('theme', theme);
        })();
    </script>

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-timerbot-black text-text">
    <div class="flex flex-col h-full">
        <x-nav />
        <div class="timerbot-bar"></div>

        <main class="flex-1 m-2 md:m-4 overflow-auto timerbot-panel">
            {{ $slot }}
        </main>

        <div class="timerbot-bar"></div>
        <x-footer />
    </div>
    <x-confirm-modal />

    {{-- AJAX Save Indicator --}}
    <div x-data="ajaxSave()"
         x-show="formExists && (isDirty || justSaved || isSaving)"
         x-cloak
         class="save-indicator"
         :class="{
             'save-indicator--unsaved': isDirty && !isSaving,
             'save-indicator--saved': justSaved,
             'save-indicator--saving': isSaving
         }"
         @click="save()"
         @keydown.window.ctrl.s.prevent="save()"
         @keydown.window.meta.s.prevent="save()">
        <span x-text="statusText"></span>
    </div>
</body>
</html>

<footer class="bg-timerbot-dark border-t border-divider">
    <div class="flex items-center justify-between gap-4 min-h-10 px-4 md:px-6 py-2 text-xs text-text-muted" style="font-family: var(--font-display);">
        <div class="flex items-center gap-6">
            <span class="text-timerbot-green">v{{ config('app.version', '1.00.00') }}</span>
            <a href="{{ route('manual') }}" class="text-timerbot-teal hover:text-timerbot-lime" target="_blank">User Manual</a>
        </div>
        <div class="flex items-center gap-6">
            <span>&copy; {{ date('Y') }} <a href="https://sublogicalendeavors.com/" target="_blank" class="text-timerbot-teal hover:text-timerbot-lime">Sublogical Endeavors</a></span>
        </div>
    </div>
</footer>

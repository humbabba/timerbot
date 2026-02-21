<footer class="bg-timerbot-dark border-t border-dark-green">
    <div class="flex items-center justify-between gap-4 min-h-10 px-4 md:px-6 py-2 text-xs text-text-muted" style="font-family: var(--font-display);">
        <div class="flex items-center gap-6">
            <span class="text-timerbot-neon">v{{ config('app.version', '47.3.1') }}</span>
            <a href="{{ route('manual') }}" class="text-timerbot-mint hover:text-timerbot-lime">User Manual</a>
        </div>
        <div class="flex items-center gap-6">
            <span>&copy; {{ date('Y') }} <a href="https://sublogicalendeavors.com/" target="_blank" class="text-timerbot-mint hover:text-timerbot-lime">Sublogical Endeavors</a></span>
        </div>
    </div>
</footer>

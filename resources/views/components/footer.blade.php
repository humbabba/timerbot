<footer class="bg-timerbot-dark border-t border-gray">
    <div class="flex items-center justify-between gap-4 min-h-10 px-4 md:px-6 py-2 text-xs text-text-muted" style="font-family: var(--font-display);">
        <div class="flex items-center gap-6">
            <span class="text-timerbot-orange">v{{ config('app.version', '47.3.1') }}</span>
        </div>
        <div class="flex items-center gap-6">
            <span>&copy; {{ date('Y') }} <a href="https://sublogicalendeavors.com/" target="_blank" class="text-timerbot-cyan hover:text-timerbot-blue">Sublogical Endeavors</a></span>
        </div>
    </div>
</footer>

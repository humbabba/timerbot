<script>window.__auth = @json(auth()->check());</script>
<nav class="bg-timerbot-dark border-b border-divider" x-data="{ mobileMenuOpen: false }">
    <div class="flex items-center h-16 px-4 md:px-6">
        <!-- Logo / Brand -->
        <a href="/?home" class="flex items-center gap-3 text-timerbot-green hover:text-timerbot-lime no-underline relative shrink-0">
            <span class="text-xl md:text-2xl font-semibold tracking-wider" style="font-family: var(--font-display);">
                {{ config('app.name', 'WestStar') }}
            </span>
            @auth
                @php
                    $newsUpdatedAt = \App\Models\AppSetting::where('key', 'news')->value('updated_at');
                    $newsViewedAt = auth()->user()->news_viewed_at;
                    $hasUnreadNews = $newsUpdatedAt && (!$newsViewedAt || \Carbon\Carbon::parse($newsUpdatedAt)->gt($newsViewedAt));
                @endphp
                @if($hasUnreadNews)
                    <span class="absolute -top-1 -right-3 w-3 h-3 rounded-none bg-timerbot-lime animate-pulse"></span>
                @endif
            @endauth
        </a>

        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex items-center gap-1 ml-10" style="font-family: var(--font-display);">

            <a href="{{ route('timers.index') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-none bg-timerbot-panel text-timerbot-green hover:bg-timerbot-green hover:text-white transition-all duration-200 no-underline uppercase text-sm tracking-wider">
                Timers
            </a>

            @auth
                @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view') || auth()->user()->hasPermission('trash.view') || auth()->user()->hasPermission('settings.manage') || auth()->user()->hasPermission('activity-logs.view'))
                    <div class="relative group">
                        <button class="flex items-center gap-2 px-4 py-2 rounded-none bg-timerbot-panel text-timerbot-lime hover:bg-timerbot-lime hover:text-white transition-all duration-200 uppercase text-sm tracking-wider">
                            Utils
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block min-w-[180px] z-50">
                            <div class="bg-timerbot-panel border border-divider rounded-sm overflow-hidden">
                                @if(auth()->user()->hasPermission('users.view'))
                                    <a href="{{ route('users.index') }}" class="block px-4 py-3 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm">Users</a>
                                @endif
                                @if(auth()->user()->hasPermission('roles.view'))
                                    <a href="{{ route('roles.index') }}" class="block px-4 py-3 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm">Roles</a>
                                @endif
                                @if(auth()->user()->hasPermission('trash.view'))
                                    <a href="{{ route('trash.index') }}" class="block px-4 py-3 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm">Trash</a>
                                @endif
                                @if(auth()->user()->hasPermission('settings.manage'))
                                    <a href="{{ route('settings.index') }}" class="block px-4 py-3 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm">Settings</a>
                                @endif
                                @if(auth()->user()->hasPermission('activity-logs.view'))
                                    <a href="{{ route('activity-logs.index') }}" class="block px-4 py-3 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm">Activity Log</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endauth
        </div>

        <!-- Desktop User Section -->
        <div class="hidden md:flex ml-auto items-center gap-4">
            <!-- Theme Toggle -->
            <button
                x-data="{ dark: document.documentElement.getAttribute('data-theme') === 'dark' }"
                @click="dark = !dark; document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light'); localStorage.setItem('theme', dark ? 'dark' : 'light'); if (window.__auth) fetch('/user/theme', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ theme: dark ? 'dark' : 'light' }) })"
                class="p-2 rounded-none text-text-muted hover:text-text transition-colors"
                title="Toggle theme"
            >
                {{-- Sun icon (shown in dark mode → click to go light) --}}
                <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                {{-- Moon icon (shown in light mode → click to go dark) --}}
                <svg x-show="!dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            @auth
                <a href="{{ route('users.show', Auth::user()) }}" class="flex items-center gap-3 no-underline hover:opacity-80 transition-opacity">
                    <x-avatar :user="Auth::user()" :size="8" />
                    <span class="text-text-muted text-sm">{{ Auth::user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-none bg-timerbot-panel text-timerbot-lime hover:bg-timerbot-lime hover:text-white transition-all duration-200 uppercase text-xs tracking-wider" style="font-family: var(--font-display);">
                        Log out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-none bg-timerbot-green text-white hover:shadow-lg hover:shadow-timerbot-green/30 transition-all duration-200 no-underline uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                    Log in
                </a>
                <a href="{{ route('register') }}" class="px-4 py-2 rounded-none bg-timerbot-panel hover:shadow-lg transition-all duration-200 no-underline uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                    Register
                </a>
            @endauth
        </div>

        <!-- Mobile Theme Toggle + Hamburger -->
        <div class="md:hidden ml-auto flex items-center gap-1">
            <button
                x-data="{ dark: document.documentElement.getAttribute('data-theme') === 'dark' }"
                @click="dark = !dark; document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light'); localStorage.setItem('theme', dark ? 'dark' : 'light'); if (window.__auth) fetch('/user/theme', { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }, body: JSON.stringify({ theme: dark ? 'dark' : 'light' }) })"
                class="p-2 text-text-muted hover:text-text transition-colors"
                title="Toggle theme"
            >
                <svg x-show="dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <svg x-show="!dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 text-timerbot-green hover:text-timerbot-lime transition-colors" style="font-family: var(--font-display);">
                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div x-show="mobileMenuOpen" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.outside="mobileMenuOpen = false"
         class="md:hidden border-t border-divider bg-timerbot-dark px-4 pb-4"
         style="font-family: var(--font-display);">

        {{-- Timers Section --}}
        <div class="py-2 border-b border-divider/50">
            <div class="text-timerbot-green uppercase text-xs tracking-wider mb-2 px-2">Timers</div>
            <a href="{{ route('timers.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-timerbot-green hover:text-white transition-colors no-underline text-sm rounded-sm">All Timers</a>
            @if(auth()->user()?->hasPermission('timers.create'))
                <a href="{{ route('timers.create') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-timerbot-green hover:text-white transition-colors no-underline text-sm rounded-sm">New Timer</a>
            @endif
        </div>

        @auth
            {{-- Utils Section --}}
            @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view') || auth()->user()->hasPermission('trash.view') || auth()->user()->hasPermission('settings.manage') || auth()->user()->hasPermission('activity-logs.view'))
                <div class="py-2 border-b border-divider/50">
                    <div class="text-timerbot-lime uppercase text-xs tracking-wider mb-2 px-2">Utils</div>
                    @if(auth()->user()->hasPermission('users.view'))
                        <a href="{{ route('users.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm rounded-sm">Users</a>
                    @endif
                    @if(auth()->user()->hasPermission('roles.view'))
                        <a href="{{ route('roles.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm rounded-sm">Roles</a>
                    @endif
                    @if(auth()->user()->hasPermission('trash.view'))
                        <a href="{{ route('trash.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm rounded-sm">Trash</a>
                    @endif
                    @if(auth()->user()->hasPermission('settings.manage'))
                        <a href="{{ route('settings.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm rounded-sm">Settings</a>
                    @endif
                    @if(auth()->user()->hasPermission('activity-logs.view'))
                        <a href="{{ route('activity-logs.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-timerbot-lime hover:text-white transition-colors no-underline text-sm rounded-sm">Activity Log</a>
                    @endif
                </div>
            @endif

            {{-- User Section --}}
            <div class="pt-3 flex items-center justify-between">
                <a href="{{ route('users.show', Auth::user()) }}" @click="mobileMenuOpen = false" class="flex items-center gap-3 no-underline hover:opacity-80 transition-opacity">
                    <x-avatar :user="Auth::user()" :size="8" />
                    <span class="text-text-muted text-sm">{{ Auth::user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-none bg-timerbot-panel text-timerbot-lime hover:bg-timerbot-lime hover:text-white transition-all duration-200 uppercase text-xs tracking-wider">
                        Log Out
                    </button>
                </form>
            </div>
        @else
            <div class="py-3">
                <a href="{{ route('login') }}" class="block text-center px-4 py-2 rounded-none bg-timerbot-green text-white hover:shadow-lg hover:shadow-timerbot-green/30 transition-all duration-200 no-underline uppercase text-sm tracking-wider">
                    Log In
                </a>
            </div>
        @endauth
    </div>
</nav>

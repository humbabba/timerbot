<nav class="bg-cortex-dark border-b border-gray" x-data="{ mobileMenuOpen: false }">
    <div class="flex items-center h-16 px-4 md:px-6">
        <!-- Logo / Brand -->
        <a href="/?home" class="flex items-center gap-3 text-cortex-orange hover:text-cortex-peach no-underline relative shrink-0">
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
                    <span class="absolute -top-1 -right-3 w-3 h-3 rounded-full bg-cortex-peach animate-pulse"></span>
                @endif
            @endauth
        </a>

        <!-- Desktop Navigation Links -->
        <div class="hidden md:flex items-center gap-1 ml-10" style="font-family: var(--font-display);">

            @auth
                @if(auth()->user()->hasPermission('waves.view'))
                    <div class="relative group">
                        <a href="{{ route('waves.index') }}"
                           class="flex items-center gap-2 px-4 py-2 rounded-full bg-cortex-panel text-cortex-orange hover:bg-cortex-orange hover:text-cortex-black transition-all duration-200 no-underline uppercase text-sm tracking-wider">
                            Waves
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block min-w-[180px] z-50">
                            <div class="bg-cortex-panel border border-gray rounded-lg overflow-hidden">
                                @if(auth()->user()->hasPermission('waves.view'))
                                    <a href="{{ route('waves.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm">All Waves</a>
                                    <a href="{{ route('waves.favorites') }}" class="block px-4 py-3 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm">My favorites</a>
                                @endif
                                @if(auth()->user()->hasPermission('waves.create'))
                                    <a href="{{ route('waves.create') }}" class="block px-4 py-3 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm">New Wave</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endauth

            @auth
                @if(auth()->user()->hasPermission('nodes.view'))
                    <div class="relative group">
                        <a href="{{ route('nodes.index') }}"
                           class="flex items-center gap-2 px-4 py-2 rounded-full bg-cortex-panel text-cortex-lavender hover:bg-cortex-lavender hover:text-cortex-black transition-all duration-200 no-underline uppercase text-sm tracking-wider">
                            Nodes
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </a>
                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block min-w-[180px] z-50">
                            <div class="bg-cortex-panel border border-gray rounded-lg overflow-hidden">
                                @if(auth()->user()->hasPermission('nodes.view'))
                                    <a href="{{ route('nodes.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm">All Nodes</a>
                                @endif
                                @if(auth()->user()->hasPermission('nodes.create'))
                                    <a href="{{ route('nodes.create') }}" class="block px-4 py-3 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm">New Node</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view') || auth()->user()->hasPermission('trash.view') || auth()->user()->hasPermission('settings.manage') || auth()->user()->hasPermission('activity-logs.view'))
                    <div class="relative group">
                        <button class="flex items-center gap-2 px-4 py-2 rounded-full bg-cortex-panel text-cortex-blue hover:bg-cortex-blue hover:text-cortex-black transition-all duration-200 uppercase text-sm tracking-wider">
                            Utils
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute left-0 top-full pt-1 hidden group-hover:block min-w-[180px] z-50">
                            <div class="bg-cortex-panel border border-gray rounded-lg overflow-hidden">
                                @if(auth()->user()->hasPermission('users.view'))
                                    <a href="{{ route('users.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm">Users</a>
                                @endif
                                @if(auth()->user()->hasPermission('roles.view'))
                                    <a href="{{ route('roles.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm">Roles</a>
                                @endif
                                @if(auth()->user()->hasPermission('trash.view'))
                                    <a href="{{ route('trash.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm">Trash</a>
                                @endif
                                @if(auth()->user()->hasPermission('settings.manage'))
                                    <a href="{{ route('settings.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm">Settings</a>
                                @endif
                                @if(auth()->user()->hasPermission('voice-profiles.view'))
                                    <a href="{{ route('voice-profiles.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm border-t border-gray/50">Voice Profiles</a>
                                @endif
                                @if(auth()->user()->hasPermission('activity-logs.view'))
                                    <a href="{{ route('activity-logs.index') }}" class="block px-4 py-3 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm">Activity Log</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endauth
        </div>

        <!-- Desktop User Section -->
        <div class="hidden md:flex ml-auto items-center gap-4">
            @auth
                <a href="{{ route('users.show', Auth::user()) }}" class="flex items-center gap-3 no-underline hover:opacity-80 transition-opacity">
                    <x-avatar :user="Auth::user()" :size="8" />
                    <span class="text-text-muted text-sm">{{ Auth::user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-full bg-cortex-panel text-cortex-peach hover:bg-cortex-peach hover:text-cortex-black transition-all duration-200 uppercase text-xs tracking-wider" style="font-family: var(--font-display);">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 rounded-full bg-cortex-orange text-cortex-black hover:shadow-lg hover:shadow-cortex-orange/30 transition-all duration-200 no-underline uppercase text-sm tracking-wider" style="font-family: var(--font-display);">
                    Log In
                </a>
            @endauth
        </div>

        <!-- Mobile Hamburger Button -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden ml-auto p-2 text-cortex-orange hover:text-cortex-peach transition-colors" style="font-family: var(--font-display);">
            <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
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
         class="md:hidden border-t border-gray bg-cortex-dark px-4 pb-4"
         style="font-family: var(--font-display);">

        @auth
            {{-- Waves Section --}}
            @if(auth()->user()->hasPermission('waves.view'))
                <div class="py-2 border-b border-gray/50">
                    <div class="text-cortex-orange uppercase text-xs tracking-wider mb-2 px-2">Waves</div>
                    <a href="{{ route('waves.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">All Waves</a>
                    <a href="{{ route('waves.favorites') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">My favorites</a>
                    @if(auth()->user()->hasPermission('waves.create'))
                        <a href="{{ route('waves.create') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">New Wave</a>
                    @endif
                </div>
            @endif

            {{-- Nodes Section --}}
            @if(auth()->user()->hasPermission('nodes.view'))
                <div class="py-2 border-b border-gray/50">
                    <div class="text-cortex-lavender uppercase text-xs tracking-wider mb-2 px-2">Nodes</div>
                    <a href="{{ route('nodes.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-lavender hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">All Nodes</a>
                    @if(auth()->user()->hasPermission('nodes.create'))
                        <a href="{{ route('nodes.create') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-lavender hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">New Node</a>
                    @endif
                </div>
            @endif

            {{-- Utils Section --}}
            @if(auth()->user()->hasPermission('users.view') || auth()->user()->hasPermission('roles.view') || auth()->user()->hasPermission('trash.view') || auth()->user()->hasPermission('settings.manage') || auth()->user()->hasPermission('activity-logs.view'))
                <div class="py-2 border-b border-gray/50">
                    <div class="text-cortex-blue uppercase text-xs tracking-wider mb-2 px-2">Utils</div>
                    @if(auth()->user()->hasPermission('users.view'))
                        <a href="{{ route('users.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">Users</a>
                    @endif
                    @if(auth()->user()->hasPermission('roles.view'))
                        <a href="{{ route('roles.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">Roles</a>
                    @endif
                    @if(auth()->user()->hasPermission('trash.view'))
                        <a href="{{ route('trash.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">Trash</a>
                    @endif
                    @if(auth()->user()->hasPermission('settings.manage'))
                        <a href="{{ route('settings.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">Settings</a>
                    @endif
                    @if(auth()->user()->hasPermission('voice-profiles.view'))
                        <a href="{{ route('voice-profiles.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-orange hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">Voice Profiles</a>
                    @endif
                    @if(auth()->user()->hasPermission('activity-logs.view'))
                        <a href="{{ route('activity-logs.index') }}" @click="mobileMenuOpen = false" class="block px-3 py-2 text-text hover:bg-cortex-blue hover:text-cortex-black transition-colors no-underline text-sm rounded-lg">Activity Log</a>
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
                    <button type="submit" class="px-4 py-2 rounded-full bg-cortex-panel text-cortex-peach hover:bg-cortex-peach hover:text-cortex-black transition-all duration-200 uppercase text-xs tracking-wider">
                        Log Out
                    </button>
                </form>
            </div>
        @else
            <div class="py-3">
                <a href="{{ route('login') }}" class="block text-center px-4 py-2 rounded-full bg-cortex-orange text-cortex-black hover:shadow-lg hover:shadow-cortex-orange/30 transition-all duration-200 no-underline uppercase text-sm tracking-wider">
                    Log In
                </a>
            </div>
        @endauth
    </div>
</nav>

<x-layouts.app>
    <div class="flex items-center justify-center h-full">
        <div class="w-full max-w-md">
            <div class="bg-timerbot-panel-light rounded-sm overflow-hidden">
                <!-- Header bar -->
                <div class="h-2 bg-timerbot-green"></div>

                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-timerbot-orange">Login</h1>
                    </div>

                    @if (session('status'))
                        <div class="mb-6 p-4 bg-timerbot-green/20 border border-timerbot-green/50 text-timerbot-green rounded-sm">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('magic_link'))
                        <div class="mb-6 p-4 bg-timerbot-cyan/20 border border-timerbot-cyan/50 rounded-sm">
                            <p class="text-timerbot-cyan font-semibold mb-2" style="font-family: var(--font-display);">Local Dev Magic Link</p>
                            <a href="{{ session('magic_link') }}" class="text-timerbot-blue hover:text-timerbot-cyan break-all">
                                Click here to login
                            </a>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.send') }}">
                        @csrf
                        <div class="mb-6">
                            <label for="email" class="block mb-2 font-semibold text-timerbot-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full p-4 bg-timerbot-panel border border-gray rounded-sm text-text focus:border-timerbot-cyan focus:ring-2 focus:ring-timerbot-cyan/20"
                                placeholder=""
                            >
                        </div>

                        <button type="submit" class="w-full py-4 rounded-none bg-timerbot-green text-timerbot-black font-bold uppercase tracking-wider transition-all hover:shadow-lg hover:shadow-timerbot-orange/30" style="font-family: var(--font-display);">
                            Send Magic Link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

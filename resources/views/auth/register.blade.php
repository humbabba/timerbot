<x-layouts.app>
    <div class="flex items-center justify-center h-full">
        <div class="w-full max-w-md">
            <div class="bg-timerbot-panel-light rounded-sm overflow-hidden">
                <!-- Header bar -->
                <div class="h-2 bg-timerbot-green"></div>

                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-timerbot-neon">Register</h1>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-timerbot-red/20 border border-timerbot-red/50 text-timerbot-red rounded-sm">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('register.store') }}">
                        @csrf
                        <div class="mb-6">
                            <label for="name" class="block mb-2 font-semibold text-timerbot-mint uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Name</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                class="w-full p-4 bg-timerbot-panel border border-dark-green rounded-sm text-text focus:border-timerbot-mint focus:ring-2 focus:ring-timerbot-mint/20"
                            >
                        </div>

                        <div class="mb-6">
                            <label for="email" class="block mb-2 font-semibold text-timerbot-mint uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                class="w-full p-4 bg-timerbot-panel border border-dark-green rounded-sm text-text focus:border-timerbot-mint focus:ring-2 focus:ring-timerbot-mint/20"
                            >
                        </div>

                        <button type="submit" class="w-full py-4 rounded-none bg-timerbot-green text-timerbot-black font-bold uppercase tracking-wider transition-all hover:shadow-lg hover:shadow-timerbot-neon/30" style="font-family: var(--font-display);">
                            Create Account
                        </button>
                    </form>

                    <p class="mt-6 text-center text-text-muted text-sm">
                        Already have an account?
                        <a href="{{ route('login') }}" class="text-timerbot-mint hover:text-timerbot-lime">Log in</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

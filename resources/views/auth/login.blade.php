<x-layouts.app>
    <div class="flex items-center justify-center h-full">
        <div class="w-full max-w-md">
            <div class="bg-cortex-panel-light rounded-2xl overflow-hidden">
                <!-- Header bar -->
                <div class="h-2 bg-gradient-to-r from-cortex-orange via-cortex-lavender to-cortex-blue"></div>

                <div class="p-8">
                    <div class="text-center mb-8">
                        <h1 class="text-cortex-orange">Login</h1>
                    </div>

                    @if (session('status'))
                        <div class="mb-6 p-4 bg-cortex-green/20 border border-cortex-green/50 text-cortex-green rounded-lg">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if (session('magic_link'))
                        <div class="mb-6 p-4 bg-cortex-cyan/20 border border-cortex-cyan/50 rounded-lg">
                            <p class="text-cortex-cyan font-semibold mb-2" style="font-family: var(--font-display);">Local Dev Magic Link</p>
                            <a href="{{ session('magic_link') }}" class="text-cortex-blue hover:text-cortex-cyan break-all">
                                Click here to login
                            </a>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-cortex-red/20 border border-cortex-red/50 text-cortex-red rounded-lg">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.send') }}">
                        @csrf
                        <div class="mb-6">
                            <label for="email" class="block mb-2 font-semibold text-cortex-lavender uppercase text-sm tracking-wider" style="font-family: var(--font-display);">Email Address</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                class="w-full p-4 bg-cortex-panel border border-gray rounded-lg text-text focus:border-cortex-cyan focus:ring-2 focus:ring-cortex-cyan/20"
                                placeholder=""
                            >
                        </div>

                        <button type="submit" class="w-full py-4 rounded-full bg-gradient-to-r from-cortex-orange to-cortex-peach text-cortex-black font-bold uppercase tracking-wider transition-all hover:shadow-lg hover:shadow-cortex-orange/30" style="font-family: var(--font-display);">
                            Send Magic Link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

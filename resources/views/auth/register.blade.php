<x-layouts.app>
    <div class="flex items-center justify-center h-full">
        <div class="w-full max-w-md p-8">
            <h1 class="mb-6 text-center">Register</h1>

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-800 rounded">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}">
                @csrf
                <div class="mb-4">
                    <label for="name" class="block mb-2 font-medium">Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-primary"
                    >
                </div>

                <div class="mb-4">
                    <label for="email" class="block mb-2 font-medium">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="w-full p-3 border border-gray-300 rounded focus:outline-none focus:border-primary"
                    >
                </div>

                <button type="submit" class="w-full p-3 bg-primary text-white font-bold rounded hover:bg-primary-dark">
                    Create Account
                </button>
            </form>

            <p class="mt-6 text-center text-gray-600">
                Already have an account? <a href="{{ route('login') }}" class="text-primary hover:underline">Log in</a>
            </p>
        </div>
    </div>
</x-layouts.app>

@php use App\Models\AppSetting; @endphp
<x-layouts.app>
    <div class="flex items-center justify-center w-full h-full">
        <div class="text-center">
            <div class="mb-8 text-left">
                {!! AppSetting::get('news') ?? '<h1 class="text-4xl mb-4">Welcome to Cortex</h1><p>All is well.</p>' !!}
            </div>
        </div>
    </div>
</x-layouts.app>

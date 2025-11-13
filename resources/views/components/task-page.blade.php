@props([
    'title',
    'subtitle' => null,
])

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <h2 class="text-xl sm:text-2xl font-semibold text-white tracking-tight">
                {{ $title }}
            </h2>

            @isset($subtitle)
                <p class="text-sm text-gray-300 max-w-3xl">{{ $subtitle }}</p>
            @endisset
        </div>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <main class="space-y-6">
                @if (session('success'))
                    <div class="bg-green-900/30 border border-green-700 text-green-100 px-4 py-2 rounded-md">
                        {{ session('success') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</x-app-layout>

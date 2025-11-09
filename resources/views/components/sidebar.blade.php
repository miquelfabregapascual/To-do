{{-- resources/views/components/sidebar.blade.php --}}
@php
    $navigationItems = collect(config('navigation.primary', []))
        ->filter(fn ($item) => isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
        ->values();
@endphp

@if ($navigationItems->isNotEmpty())
    <aside class="bg-gray-800/80 border border-gray-700 rounded-lg p-3">
        <nav class="space-y-1">
            @foreach ($navigationItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                    $isAccent = $item['accent'] ?? false;
                    $activeClass = $isAccent
                        ? 'bg-blue-600/80 text-white'
                        : 'bg-gray-700/80 text-white';
                    $inactiveClass = 'text-gray-200 hover:bg-gray-700/60';
                @endphp

                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-2 px-3 py-2 rounded transition {{ $isActive ? $activeClass : $inactiveClass }}">
                    @if (! empty($item['icon']))
                        <span aria-hidden="true">{{ $item['icon'] }}</span>
                    @endif
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>
@endif

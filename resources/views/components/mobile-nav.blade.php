@php
    $navigationItems = collect(config('navigation.primary', []))
        ->filter(fn ($item) => isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
        ->values();
    $isAuthenticated = auth()->check();
@endphp

@if ($isAuthenticated && $navigationItems->isNotEmpty())
    <nav
        aria-label="Navegación principal"
        class="sm:hidden fixed inset-x-0 bottom-0 z-40 border-t border-gray-800/80 bg-gray-950/90 backdrop-blur supports-[backdrop-filter]:bg-gray-950/75"
        style="padding-bottom: env(safe-area-inset-bottom)"
    >
        <ul class="flex items-stretch gap-1 overflow-x-auto px-3 py-2 no-scrollbar">
            @foreach ($navigationItems as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                    $isAccent = $item['accent'] ?? false;
                    $activeClass = $isAccent
                        ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/40'
                        : 'bg-gray-800/80 text-white shadow-inner shadow-black/20';
                    $inactiveClass = 'text-gray-300 hover:text-white hover:bg-gray-800/70';
                @endphp

                <li class="flex-1 min-w-[88px]">
                    <a
                        href="{{ route($item['route']) }}"
                        class="group flex h-full flex-col items-center justify-center gap-1 rounded-xl px-3 py-2 text-[11px] font-medium transition"
                        @class([
                            $isActive ? $activeClass : $inactiveClass,
                        ])
                        @if ($isActive)
                            aria-current="page"
                        @endif
                    >
                        @if (! empty($item['icon']))
                            <span aria-hidden="true" class="text-base leading-none transition group-hover:scale-110">
                                {{ $item['icon'] }}
                            </span>
                        @endif
                        <span class="leading-tight text-center">{{ $item['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif

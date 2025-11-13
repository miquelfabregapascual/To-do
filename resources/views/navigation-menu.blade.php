<nav x-data="{ open: false }" class="text-gray-100 lg:flex lg:min-h-screen lg:w-64 lg:flex-col lg:border-r lg:border-gray-800/70 lg:bg-gray-950">
    @php
        $primaryNavigation = collect(config('navigation.primary', []))
            ->filter(fn ($item) => isset($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']))
            ->values();
    @endphp

    <!-- Mobile top bar -->
    <div class="flex h-16 items-center justify-between border-b border-gray-800/70 bg-gray-950 px-4 lg:hidden">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <x-application-mark class="block h-9 w-auto text-white" />
            <span class="text-sm font-semibold text-gray-100">{{ config('app.name', 'Planner') }}</span>
        </a>

        <button
            type="button"
            @click="open = ! open"
            class="inline-flex items-center justify-center rounded-md p-2 text-gray-300 transition hover:bg-gray-800/70 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            aria-controls="mobile-primary-nav"
            :aria-expanded="open"
        >
            <span class="sr-only">Abrir menú principal</span>
            <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ 'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ 'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile flyout -->
    <div
        x-cloak
        x-show="open"
        x-transition.origin.top
        id="mobile-primary-nav"
        class="lg:hidden"
    >
        <div class="space-y-1 border-b border-gray-800/60 bg-gray-950/95 px-2 pb-3 pt-2">
            @foreach ($primaryNavigation as $item)
                @php
                    $isActive = request()->routeIs($item['route']);
                    $isAccent = $item['accent'] ?? false;
                    $activeClass = $isAccent
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-800/70 text-white';
                    $inactiveClass = 'text-gray-300 hover:text-white hover:bg-gray-800/70';
                @endphp

                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'flex items-center gap-2 rounded-md px-3 py-2 text-base font-medium transition',
                        $isActive ? $activeClass : $inactiveClass,
                    ])
                    @if ($isActive)
                        aria-current="page"
                    @endif
                >
                    @if (! empty($item['icon']))
                        <span aria-hidden="true">{{ $item['icon'] }}</span>
                    @endif
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="border-b border-gray-800/60 bg-gray-950/95 pb-3 pt-4">
            <div class="flex items-center gap-3 px-4">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                @endif
                <div>
                    <p class="text-base font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="text-sm text-gray-400">{{ Auth::user()->email }}</p>
                </div>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                <div class="mt-3 px-4">
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Manage Team') }}</p>
                    <div class="mt-2 space-y-1">
                        <x-responsive-nav-link href="{{ route('teams.show', Auth::user()->currentTeam->id) }}">
                            {{ __('Team Settings') }}
                        </x-responsive-nav-link>
                        @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                            <x-responsive-nav-link href="{{ route('teams.create') }}">
                                {{ __('Create New Team') }}
                            </x-responsive-nav-link>
                        @endcan
                    </div>

                    @if (Auth::user()->allTeams()->count() > 1)
                        <p class="mt-3 text-xs uppercase tracking-wide text-gray-500">{{ __('Switch Teams') }}</p>
                        <div class="mt-2 space-y-1">
                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team :team="$team" component="x-responsive-nav-link" />
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-4 space-y-1 px-4">
                <x-responsive-nav-link href="{{ route('profile.show') }}" :active="request()->routeIs('profile.show')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                    <x-responsive-nav-link href="{{ route('api-tokens.index') }}" :active="request()->routeIs('api-tokens.index')">
                        {{ __('API Tokens') }}
                    </x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf

                    <x-responsive-nav-link href="{{ route('logout') }}"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>

    <!-- Desktop sidebar -->
    <div class="hidden h-full flex-1 flex-col lg:flex">
        <div class="flex items-center gap-3 border-b border-gray-800/70 px-6 py-8">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                <x-application-mark class="block h-10 w-auto text-white" />
                <span class="text-lg font-semibold tracking-tight text-white">{{ config('app.name', 'Planner') }}</span>
            </a>
        </div>

        <div class="flex-1 overflow-y-auto px-4 pb-6 pt-4">
            <ul class="space-y-1">
                @foreach ($primaryNavigation as $item)
                    @php
                        $isActive = request()->routeIs($item['route']);
                        $isAccent = $item['accent'] ?? false;
                        $baseClasses = 'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition';
                        $activeClasses = $isAccent
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/30'
                            : 'bg-gray-800/80 text-white';
                        $inactiveClasses = 'text-gray-400 hover:text-white hover:bg-gray-800/60';
                    @endphp
                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                $baseClasses,
                                $isActive ? $activeClasses : $inactiveClasses,
                            ])
                            @if ($isActive)
                                aria-current="page"
                            @endif
                        >
                            @if (! empty($item['icon']))
                                <span aria-hidden="true" class="text-base">{{ $item['icon'] }}</span>
                            @endif
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="border-t border-gray-800/70 px-6 py-6">
            <div class="flex items-center gap-3">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <img class="size-10 rounded-full object-cover" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                @endif
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="truncate text-xs text-gray-400">{{ Auth::user()->email }}</p>
                </div>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                <div class="mt-5 space-y-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Teams') }}</p>
                    <a href="{{ route('teams.show', Auth::user()->currentTeam->id) }}" class="block rounded-md bg-gray-900/70 px-3 py-2 text-xs font-medium text-gray-300 transition hover:bg-gray-800/70 hover:text-white">
                        {{ __('Team Settings') }}
                    </a>
                    @can('create', Laravel\Jetstream\Jetstream::newTeamModel())
                        <a href="{{ route('teams.create') }}" class="block rounded-md px-3 py-2 text-xs font-medium text-gray-300 transition hover:bg-gray-800/70 hover:text-white">
                            {{ __('Create New Team') }}
                        </a>
                    @endcan

                    @if (Auth::user()->allTeams()->count() > 1)
                        <div class="space-y-1">
                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team :team="$team" component="x-responsive-nav-link" />
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <button type="submit" class="w-full rounded-md bg-gray-800/80 px-3 py-2 text-sm font-semibold text-gray-200 transition hover:bg-gray-700 hover:text-white">
                    {{ __('Log Out') }}
                </button>
            </form>
        </div>
    </div>
</nav>

{{-- resources/views/components/sidebar.blade.php --}}
@php use Illuminate\Support\Facades\Route as RouteFacade; @endphp

<aside class="bg-gray-800/80 border border-gray-700 rounded-lg p-3">
    <nav class="space-y-1">
        <a href="{{ route('inbox') }}"
           class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700/60
                  {{ request()->routeIs('inbox') ? 'bg-gray-700/80 text-white' : 'text-gray-200' }}">
            📥 <span>Inbox</span>
        </a>

        <a href="{{ route('today') }}"
           class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700/60
                  {{ request()->routeIs('today') ? 'bg-blue-600/80 text-white' : 'text-gray-200' }}">
            ☀️ <span>Hoy</span>
        </a>

        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700/60
                  {{ request()->routeIs('dashboard') ? 'bg-gray-700/80 text-white' : 'text-gray-200' }}">
            📅 <span>Próximos 7 días</span>
        </a>

        <a href="{{ route('completed') }}"
           class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700/60
                  {{ request()->routeIs('completed') ? 'bg-gray-700/80 text-white' : 'text-gray-200' }}">
            ✅ <span>Completadas</span>
        </a>    

        <a href="{{ route('all') }}"
           class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700/60
                  {{ request()->routeIs('all') ? 'bg-gray-700/80 text-white' : 'text-gray-200' }}">
            📚 <span>Todas</span>
        </a>

        @if (RouteFacade::has('settings'))
            <a href="{{ route('settings') }}"
               class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-700/60
                      {{ request()->routeIs('settings') ? 'bg-gray-700/80 text-white' : 'text-gray-200' }}">
                ⚙️ <span>Ajustes</span>
            </a>
        @endif
    </nav>
</aside>

{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl sm:text-2xl font-semibold text-white tracking-tight">
            Mis Tareas
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            
            {{-- ===== Two-column layout: sidebar + main ===== --}}
            <div class="grid grid-cols-12 gap-6">
                
                {{-- LEFT MENU --}}
                <aside class="hidden md:block md:col-span-3">
                    <x-sidebar />
                </aside>

                {{-- MAIN CONTENT --}}
                <main class="col-span-12 md:col-span-9 space-y-6">

                    {{-- Flash message --}}
                    @if (session('success'))
                        <div class="bg-green-900/30 border border-green-700 text-green-100 px-4 py-2 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- === Quick Add Form === --}}
                    <div class="bg-gray-800/90 border border-gray-700 rounded-lg p-4">
                        <form method="POST" action="{{ route('tasks.store') }}" 
                              class="grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                            @csrf

                            <div class="sm:col-span-3">
                                <label for="title" class="block text-xs font-medium text-gray-300">Título</label>
                                <input id="title" name="title" type="text" required
                                       class="w-full mt-1 px-3 py-2 rounded-md bg-gray-800 border border-gray-600 
                                              text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Ej. Estudiar para el examen" value="{{ old('title') }}" />
                                @error('title')
                                    <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="due_date" class="block text-xs font-medium text-gray-300">Fecha</label>
                                <input id="due_date" name="due_date" type="date" required
                                       class="w-full mt-1 px-3 py-2 rounded-md bg-gray-800 border border-gray-600 
                                              text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[13rem]"
                                       value="{{ old('due_date', now()->toDateString()) }}"
                                       min="{{ now()->toDateString() }}">
                                @error('due_date')
                                    <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-4">
                                <label for="description" class="block text-xs font-medium text-gray-300">Descripción</label>
                                <textarea id="description" name="description" rows="1"
                                          class="w-full mt-1 px-3 py-2 rounded-md bg-gray-800 border border-gray-600 text-gray-100
                                                 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Detalles…">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="text-red-300 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-1 flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-md bg-blue-600 hover:bg-blue-700 
                                               px-4 py-2.5 text-white font-medium">
                                    Añadir
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- === Calendar Navigation === --}}
                    @php $today = now()->startOfDay(); @endphp
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('dashboard', ['week' => ($weekOffset ?? 0) - 1]) }}"
                               class="px-3 py-1.5 rounded-md bg-gray-800 border border-gray-700 hover:bg-gray-700" 
                               title="Semana anterior">←</a>

                            <a href="{{ route('dashboard', ['week' => 0]) }}"
                               class="px-3 py-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium"
                               title="Volver a hoy">Hoy</a>

                            <a href="{{ route('dashboard', ['week' => ($weekOffset ?? 0) + 1]) }}"
                               class="px-3 py-1.5 rounded-md bg-gray-800 border border-gray-700 hover:bg-gray-700"
                               title="Siguiente semana">→</a>
                        </div>

                        <div class="text-sm text-gray-300">
                            {{ ($weekStart ?? now())->translatedFormat('d M Y') }} – 
                            {{ ($weekEnd ?? now())->translatedFormat('d M Y') }}
                        </div>
                    </div>

                    {{-- === Tasks grouped by day === --}}
                    @php
                        if (!isset($tasksByDate)) {
                            $tasksByDate = ($tasks ?? collect())->groupBy(function ($t) use ($hasDueDate) {
                                return ($hasDueDate ?? false)
                                    ? optional($t->due_date)->toDateString()
                                    : \Carbon\Carbon::parse($t->created_at)->toDateString();
                            });
                        }
                    @endphp

                    <div class="space-y-4">
                        @foreach ($days as $day)
                            @php
                                $isToday = $day->isSameDay($today);
                                $dayKey = $day->toDateString();
                                $dayTasks = ($tasksByDate ?? collect())->get($dayKey, collect());
                                $count = $dayTasks->count();
                            @endphp

                            <section class="bg-gray-800/90 border border-gray-700 rounded-lg p-4 {{ $isToday ? 'ring-2 ring-blue-400' : '' }}">
                                {{-- Day header --}}
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="uppercase text-xs tracking-wider text-gray-300">
                                            {{ $day->translatedFormat('l') }}
                                        </span>
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full
                                                {{ $isToday ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-200' }}">
                                            {{ $day->translatedFormat('d') }}
                                        </span>
                                        <span class="text-xs text-gray-400">{{ $day->translatedFormat('M Y') }}</span>
                                    </div>

                                    <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-2 text-xs rounded-full
                                            {{ $count ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300' }}">
                                        {{ $count }}
                                    </span>
                                </div>

                                {{-- Tasks list --}}
                                <ol class="space-y-2">
                                    @forelse ($dayTasks as $task)
                                        <li class="group bg-gray-700/70 border border-gray-600 rounded-md p-3 overflow-hidden">
                                            <div class="flex flex-col gap-2 min-w-0">
                                                <div class="flex items-start gap-2 min-w-0">
                                                    <span class="mt-1 inline-block w-2 h-2 rounded-full {{ $task->completed ? 'bg-gray-500' : 'bg-blue-500' }}"></span>
                                                    <div class="min-w-0 flex-1">

                                                        {{-- Task Title --}}
                                                        <div class="text-sm truncate {{ $task->completed ? 'line-through text-gray-400' : 'text-gray-100' }}">
                                                            {{ $task->title }}
                                                        </div>

                                                        {{-- Description preview --}}
                                                       {{-- Description preview --}}
@php
    $desc = trim((string) $task->description);
    $descLen = mb_strlen($desc);

    // Decide when to clamp — only for long descriptions
    // Tune this value; 180–200 characters is good for about 3-4 lines
    $shouldClamp = $descLen > 200;

    $modalId = "task-desc-{$task->id}";
@endphp

@if ($descLen)
    <p class="mt-1 text-xs text-gray-300 break-words {{ $shouldClamp ? 'whitespace-pre-wrap clamp-resp' : 'whitespace-pre-wrap' }}">
        {{ $desc }}
    </p>

    @if ($shouldClamp)
        <a href="#{{ $modalId }}" class="text-blue-400 text-xs mt-1 inline-block underline">
            Leer más
        </a>

        {{-- Modal (CSS-only via :target) --}}
        <div id="{{ $modalId }}" class="modal">
            <a href="#" class="absolute inset-0" aria-label="Cerrar"></a>
            <div class="modal-card">
                <a href="#" class="modal-close" aria-label="Cerrar">×</a>
                <h5 class="text-base font-semibold mb-2">{{ $task->title }}</h5>
                <div class="text-sm whitespace-pre-wrap break-words">
                    {{ $desc }}
                </div>
                <div class="mt-4 text-right">
                    <a href="#" class="inline-block px-3 py-1.5 rounded bg-gray-700 hover:bg-gray-600 text-gray-100">
                        Cerrar
                    </a>
                </div>
            </div>
        </div>
    @endif
@endif

                                                {{-- Actions --}}
                                                <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-600/60">
                                                    <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="text-xs underline hover:no-underline hover:text-blue-300 whitespace-nowrap">
                                                            {{ $task->completed ? 'Desmarcar' : 'Completar' }}
                                                        </button>
                                                    </form>

                                                    <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                                                          onsubmit="return confirm('¿Eliminar tarea?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-300 underline hover:no-underline hover:text-red-200 whitespace-nowrap">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                    @empty
                                        <li class="text-xs text-gray-400 py-2 text-center">Sin tareas</li>
                                    @endforelse
                                </ol>
                            </section>
                        @endforeach
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>

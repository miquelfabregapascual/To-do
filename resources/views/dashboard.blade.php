{{-- resources/views/dashboard.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl sm:text-2xl font-semibold text-white tracking-tight">
            Planificador semanal
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
<div class="layout-main mx-auto px-4 sm:px-6">

    {{-- Wrapper for drag & drop (includes backlog + calendar) --}}
    <div
        class="space-y-6"
        data-planner-board
        data-schedule-url="{{ route('planner.schedule') }}">

        {{-- TOP ROW: Backlog (left) + Quick add (right) --}}
        <div class="flex flex-col gap-6 lg:grid lg:grid-cols-[minmax(260px,300px)_1fr] lg:items-stretch">
            {{-- LEFT COLUMN: Backlog --}}
            <aside class="space-y-6 lg:space-y-0 lg:flex lg:flex-col lg:gap-6 lg:self-stretch">
                <div>
                    <div class="bg-gray-800/90 border border-gray-700 rounded-lg p-4 h-full flex flex-col">
                        <header class="flex items-center justify-between mb-3">
                            <div>
                                <h3 class="text-base font-semibold text-gray-100">Backlog</h3>
                                <p class="text-xs text-gray-400">
                                    Arrastra desde aquí al calendario para programar o suelta tareas programadas para devolverlas.
                                </p>
                            </div>
                            <span class="inline-flex items-center justify-center min-w-[28px] h-6 px-2 text-xs rounded-full bg-gray-700 text-gray-300">
                                {{ $backlog->count() }}
                            </span>
                        </header>

                        <div class="space-y-4 flex-1 lg:overflow-y-auto lg:pr-1 lg:no-scrollbar" data-drop-backlog="true" data-backlog-list>
                            @forelse ($backlog as $task)
                            <article
                                class="bg-gray-700/70 border border-gray-600 rounded-lg p-4 cursor-default md:cursor-grab focus-within:ring-2 focus-within:ring-blue-500"
                                draggable="true"
                                data-task-draggable="{{ $task->id }}"
                                aria-grabbed="false">
                                <div class="flex flex-col gap-2 min-w-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-semibold text-gray-100 truncate">{{ $task->title }}</h4>
                                            <x-task-description
                                                :text="$task->description"
                                                :title="$task->title"
                                                :task-id="$task->id"
                                                paragraph-class="mt-1 text-xs text-gray-300 leading-5" />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between gap-2 pt-3 border-t border-gray-600/60">
                                        <button
                                            type="button"
                                            class="text-[11px] text-blue-200 underline decoration-dotted hover:text-blue-100"
                                            data-task-detail-trigger="{{ $task->id }}">
                                            Detalle
                                        </button>

                                        <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-[11px] underline hover:no-underline hover:text-blue-300">
                                                Completar
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('¿Eliminar tarea?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-[11px] text-red-300 underline hover:no-underline hover:text-red-200">
                                                Eliminar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </article>
                            @empty
                            <div class="bg-gray-800/60 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-10 rounded-lg">
                                Backlog vacío. Guarda tareas sin fecha y arrástralas al plan semanal.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </aside>

            {{-- RIGHT COLUMN: Flash + Quick add --}}
            <main class="flex-1 min-w-0 space-y-6 lg:flex lg:flex-col lg:min-h-full">
                {{-- Flash message --}}
                @if (session('success'))
                <div class="bg-green-900/30 border border-green-700 text-green-100 px-4 py-2 rounded-md">
                    {{ session('success') }}
                </div>
                @endif

                {{-- === Quick Add Form === --}}
                <section class="bg-gray-800/90 border border-gray-700 rounded-lg p-4">
                    <header class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-gray-100">Añadir tarea</h3>
                            <p class="text-xs text-gray-400">Completa la fecha para agendarla o déjala vacía para guardar en el backlog.</p>
                        </div>
                    </header>

                    <form method="POST" action="{{ route('tasks.store') }}" class="grid grid-cols-1 sm:grid-cols-6 gap-3 items-end">
                        @csrf
                        <div class="sm:col-span-3">
                            <label for="title" class="block text-xs font-medium text-gray-300">Título</label>
                            <input id="title" name="title" type="text" required
                                class="w-full mt-1 px-3 py-2 rounded-md bg-gray-800 border border-gray-600 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ej. Preparar informe semanal" value="{{ old('title') }}" />
                            @error('title') <p class="text-red-300 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="due_date" class="block text-xs font-medium text-gray-300">Fecha (opcional)</label>
                            <input id="due_date" name="due_date" type="date"
                                class="w-full mt-1 px-3 py-2 rounded-md bg-gray-800 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                value="{{ old('due_date', ($weekStart ?? now())->toDateString()) }}"
                                min="{{ now()->toDateString() }}">
                            @error('due_date') <p class="text-red-300 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-6">
                            <label for="description" class="block text-xs font-medium text-gray-300">Descripción</label>
                            <textarea id="description" name="description" rows="2"
                                class="w-full mt-1 px-3 py-2 rounded-md bg-gray-800 border border-gray-600 text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Detalles, notas o contexto">{{ old('description') }}</textarea>
                            @error('description') <p class="text-red-300 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-1 flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-blue-600 hover:bg-blue-700 px-4 py-2.5 text-white text-sm font-medium">
                                Guardar
                            </button>
                        </div>
                    </form>
                </section>
            </main>
        </div>

        {{-- BOTTOM: calendar uses FULL width (under backlog + quick add) --}}
        @php $today = now()->startOfDay(); @endphp

        {{-- Calendar navigation --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2">
                <a href="{{ route('dashboard', ['week' => ($weekOffset ?? 0) - 1]) }}"
                    class="px-3 py-1.5 rounded-md bg-gray-800 border border-gray-700 hover:bg-gray-700" title="Semana anterior">←</a>
                <a href="{{ route('dashboard', ['week' => 0]) }}"
                    class="px-3 py-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium" title="Volver a la semana actual">Hoy</a>
                <a href="{{ route('dashboard', ['week' => ($weekOffset ?? 0) + 1]) }}"
                    class="px-3 py-1.5 rounded-md bg-gray-800 border border-gray-700 hover:bg-gray-700" title="Siguiente semana">→</a>
            </div>

            <div class="text-sm text-gray-300">
                {{ ($weekStart ?? now())->translatedFormat('d M Y') }} –
                {{ ($weekEnd ?? now())->translatedFormat('d M Y') }}
            </div>
        </div>

        {{-- Week grid (full width, scrolls if needed) --}}
        <div class="overflow-x-auto">
            <div class="planner-week min-h-full">
                @foreach ($days as $day)
                @php
                    $isToday = $day->isSameDay($today);
                    $dayKey = $day->toDateString();
                    $dayTasks = ($tasksByDate ?? collect())->get($dayKey, collect());
                    $count = $dayTasks->count();
                @endphp

                <section class="planner-day bg-gray-800/90 border border-gray-700 rounded-xl p-3 space-y-2 transition-all"
                    data-drop-date="{{ $dayKey }}">
                    <header class="flex items-center justify-between gap-3">
                        <div class="flex flex-col gap-1">
                            <span class="text-[11px] font-semibold tracking-[0.14em] text-gray-400 uppercase">{{ $day->translatedFormat('l') }}</span>
                            <div class="inline-flex items-center gap-2">
                                <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold leading-tight {{ $isToday ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-100' }}">
                                    {{ $day->translatedFormat('d M') }}
                                </span>
                                @if ($isToday)
                                <span class="text-[11px] font-medium text-blue-300">Hoy</span>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center justify-center min-w-[26px] h-6 px-2 text-[11px] font-semibold rounded-full {{ $count ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300' }}">
                            {{ $count }}
                        </span>
                    </header>

                    <ol class="space-y-2 min-h-[192px]">
                        @forelse ($dayTasks as $task)
                        <li class="group bg-gray-700/70 border border-gray-600 rounded-lg p-2.5 overflow-hidden cursor-default md:cursor-grab"
                            draggable="true" data-task-draggable="{{ $task->id }}" aria-grabbed="false">
                            <div class="flex flex-col gap-2 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="text-[13px] font-semibold text-gray-100 leading-5 truncate">
                                            {{ $task->title }}
                                        </div>
                                        <x-task-description
                                            :text="$task->description"
                                            :title="$task->title"
                                            :task-id="$task->id"
                                            paragraph-class="mt-1 text-xs text-gray-300 leading-5" />
                                    </div>
                                    <span class="mt-0.5 inline-flex items-center justify-center w-2 h-2 rounded-full bg-blue-400"></span>
                                </div>

                                <div class="flex flex-wrap items-center justify-start gap-2 pt-3 border-t border-gray-600/60 md:justify-between">
                                    <button
                                        type="button"
                                        class="text-[11px] text-blue-200 underline decoration-dotted hover:text-blue-100"
                                        data-task-detail-trigger="{{ $task->id }}">
                                        Detalle
                                    </button>

                                    @if (! $task->is_anchor)
                                    <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-[11px] underline hover:no-underline hover:text-blue-300">Completar</button>
                                    </form>
                                    @endif

                                    @if (! $task->is_anchor)
                                    <form method="POST" action="{{ route('planner.schedule') }}">
                                        @csrf
                                        <input type="hidden" name="task_id" value="{{ $task->id }}">
                                        <input type="hidden" name="due_date" value="">
                                        <button type="submit" class="text-[11px] text-amber-200 underline hover:no-underline hover:text-amber-100">
                                            Volver a backlog
                                        </button>
                                    </form>
                                    @endif

                                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('¿Eliminar tarea?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[11px] text-red-300 underline hover:no-underline hover:text-red-200">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                        @empty
                        <li class="flex items-center justify-center rounded-md border border-dashed border-gray-600 bg-gray-800/40 text-[11px] text-gray-400 py-8 text-center">
                            Arrastra tareas desde el backlog o crea nuevas para este día.
                        </li>
                        @endforelse
                    </ol>
                </section>
                @endforeach
            </div>
        </div>
    </div>
</div>





    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const board = document.querySelector('[data-planner-board]');
            if (!board) {
                return;
            }

            const scheduleUrl = board.dataset.scheduleUrl;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            let draggingTaskId = null;

            const highlightClasses = ['ring-2', 'ring-blue-500', 'ring-offset-1', 'ring-offset-gray-900'];

            function clearDragState(element) {
                if (!element) {
                    return;
                }

                highlightClasses.forEach((cls) => element.classList.remove(cls));
            }

            function submitSchedule(taskId, dateValue) {
                if (!taskId) {
                    return;
                }

                const payload = {
                    task_id: taskId,
                    due_date: dateValue ?? null,
                };

                fetch(scheduleUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Request failed');
                        }
                        return response.json();
                    })
                    .then(() => {
                        window.location.reload();
                    })
                    .catch(() => {
                        alert('No se pudo reprogramar la tarea. Intenta nuevamente.');
                    })
                    .finally(() => {
                        draggingTaskId = null;
                    });
            }

            board.querySelectorAll('[data-task-draggable]').forEach((el) => {
                el.addEventListener('dragstart', () => {
                    draggingTaskId = el.dataset.taskDraggable;
                    el.classList.add('opacity-60');
                    el.setAttribute('aria-grabbed', 'true');
                });

                el.addEventListener('dragend', () => {
                    draggingTaskId = null;
                    el.classList.remove('opacity-60');
                    el.setAttribute('aria-grabbed', 'false');
                });
            });

            const dropZones = board.querySelectorAll('[data-drop-date], [data-drop-backlog]');
            dropZones.forEach((zone) => {
                zone.addEventListener('dragover', (event) => {
                    if (!draggingTaskId) {
                        return;
                    }
                    event.preventDefault();
                    highlightClasses.forEach((cls) => zone.classList.add(cls));
                });

                zone.addEventListener('dragleave', () => {
                    clearDragState(zone);
                });

                zone.addEventListener('drop', (event) => {
                    event.preventDefault();
                    const isBacklog = zone.hasAttribute('data-drop-backlog');
                    const targetDate = isBacklog ? null : zone.dataset.dropDate;
                    clearDragState(zone);
                    submitSchedule(draggingTaskId, targetDate || null);
                });
            });
        });
    </script>
</x-app-layout>
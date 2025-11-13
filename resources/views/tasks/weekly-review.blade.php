<x-task-page
    title="Revisión semanal"
    subtitle="Revisa en un vistazo cómo cerró tu semana, qué quedó pendiente y qué lograste."
>
    @php
        $weekLabel = sprintf(
            'Semana del %s al %s',
            $weekStart->translatedFormat('d M'),
            $weekEnd->translatedFormat('d M')
        );
        $completionRateValue = $completionRate ?? 0;
        $completionRateBar = max(min($completionRateValue, 100), 0);
    @endphp

    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-sm font-semibold text-gray-100">{{ $weekLabel }}</p>
            <p class="text-xs text-gray-400">Comparativa entre lo planificado, lo completado y los arrastres.</p>
        </div>

        <div class="flex flex-wrap gap-2 text-sm">
            <a href="{{ route('weekly-review', ['week' => $weekOffset - 1]) }}"
               class="inline-flex items-center gap-1 rounded-md border border-gray-600 px-3 py-1.5 text-gray-200 hover:border-gray-500 hover:text-white">
                ← Semana anterior
            </a>

            @if ($canNavigateForward)
                <a href="{{ route('weekly-review', ['week' => $weekOffset + 1]) }}"
                   class="inline-flex items-center gap-1 rounded-md border border-gray-600 px-3 py-1.5 text-gray-200 hover:border-gray-500 hover:text-white">
                    Semana siguiente →
                </a>
            @else
                <span class="inline-flex items-center gap-1 rounded-md border border-gray-700 px-3 py-1.5 text-gray-500">
                    Semana siguiente →
                </span>
            @endif

            <a href="{{ route('weekly-review', ['week' => 0]) }}"
               class="inline-flex items-center gap-1 rounded-md border border-blue-600/70 bg-blue-600/20 px-3 py-1.5 text-blue-200 hover:bg-blue-600/30">
                Semana actual
            </a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <section class="rounded-lg border border-gray-700 bg-gray-800/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-400">Planificadas</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $plannedCount }}</p>
            <p class="mt-2 text-xs text-gray-400">Tareas programadas con fecha durante la semana.</p>
        </section>

        <section class="rounded-lg border border-gray-700 bg-gray-800/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-400">Completadas</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $completedCount }}</p>
            <div class="mt-3 space-y-1">
                <div class="h-2 rounded-full bg-gray-700">
                    <div
                        class="h-2 rounded-full bg-emerald-500"
                        style="width: {{ $completionRateBar }}%"
                    ></div>
                </div>
                <p class="text-xs text-gray-400">
                    {{ $completionRate !== null ? $completionRate . '% de lo planificado' : 'Sin tareas planificadas' }}
                </p>
            </div>
        </section>

        <section class="rounded-lg border border-gray-700 bg-gray-800/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-400">Carry-overs</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $carryOverCount }}</p>
            <p class="mt-2 text-xs text-gray-400">Tareas que necesitan reprogramarse o retomarse.</p>
        </section>

        <section class="rounded-lg border border-gray-700 bg-gray-800/80 p-4 shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-400">Nuevas esta semana</p>
            <p class="mt-2 text-3xl font-semibold text-white">{{ $createdDuringWeekCount }}</p>
            <p class="mt-2 text-xs text-gray-400">Tareas creadas durante este rango.</p>
        </section>
    </div>

    <section class="rounded-lg border border-gray-700 bg-gray-800/80 p-5">
        <header class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-base font-semibold text-gray-100">Progreso diario</h3>
                <p class="text-xs text-gray-400">Visualiza cuántas tareas se completaron cada día.</p>
            </div>
        </header>

        <div class="mt-4 space-y-4">
            @foreach ($dailyStats as $dayStat)
                @php
                    $dayCompletion = max(min($dayStat['completionRate'] ?? 0, 100), 0);
                @endphp
                <article class="flex flex-col gap-2 rounded-lg border border-gray-700/60 bg-gray-900/50 p-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-100">
                            {{ $dayStat['date']->translatedFormat('l d M') }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $dayStat['planned'] }} planificadas · {{ $dayStat['completed'] }} completadas · {{ $dayStat['carryOver'] }} pendientes
                        </p>
                    </div>

                    <div class="w-full sm:max-w-xs">
                        <div class="h-2 rounded-full bg-gray-700">
                            <div
                                class="h-2 rounded-full bg-blue-500"
                                style="width: {{ $dayCompletion }}%"
                            ></div>
                        </div>
                        <p class="mt-1 text-right text-xs text-gray-400">
                            {{ $dayStat['completionRate'] !== null ? $dayStat['completionRate'] . '% completadas' : 'Sin tareas asignadas' }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-100">Pendientes para arrastrar</h3>
                    <p class="text-xs text-gray-400">Considera reagendar estas tareas en tu próxima planificación.</p>
                </div>
                <span class="text-xs text-gray-400">{{ $carryOverCount }} tareas</span>
            </header>

            @forelse ($carryOverTasks as $task)
                <x-task-card :task="$task" :actions="false" />
            @empty
                <div class="rounded-lg border border-dashed border-gray-600 bg-gray-800/70 py-6 text-center text-sm text-gray-300">
                    ¡Nada que arrastrar! Todo quedó al día.
                </div>
            @endforelse
        </section>

        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-100">Logros de la semana</h3>
                    <p class="text-xs text-gray-400">Tareas completadas dentro del periodo.</p>
                </div>
                <span class="text-xs text-gray-400">{{ $completedDuringWeek->count() }} tareas</span>
            </header>

            @forelse ($completedDuringWeek as $task)
                <x-task-card :task="$task" :actions="false" :show-completed-at="true" />
            @empty
                <div class="rounded-lg border border-dashed border-gray-600 bg-gray-800/70 py-6 text-center text-sm text-gray-300">
                    Aún no hay logros registrados para esta semana.
                </div>
            @endforelse
        </section>
    </div>
</x-task-page>

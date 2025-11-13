<x-task-page
    title="Planifica tu próxima semana"
    subtitle="Sigue los pasos para revisar lo que pasó, fijar el enfoque y preparar el calendario siguiente."
>
    @php
        $steps = [
            ['key' => 'review', 'label' => '1. Revisar la semana'],
            ['key' => 'goals', 'label' => '2. Definir metas'],
            ['key' => 'anchors', 'label' => '3. Añadir anclas'],
            ['key' => 'schedule', 'label' => '4. Programar tareas'],
            ['key' => 'summary', 'label' => '5. Crear semana'],
        ];
        $dayNames = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
    @endphp

    @if (session('error'))
        <div class="rounded-md border border-red-700 bg-red-900/40 px-4 py-2 text-sm text-red-100">
            {{ session('error') }}
        </div>
    @endif

    <nav class="flex flex-wrap gap-2 text-xs font-medium">
        @foreach ($steps as $wizardStep)
            @php
                $isActive = $wizardStep['key'] === $step;
                $stepIndex = array_search($wizardStep['key'], array_column($steps, 'key'), true);
            @endphp
            <a
                href="{{ route('plan-next-week', ['step' => $wizardStep['key']]) }}"
                class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 transition {{
                    $isActive
                        ? 'border-blue-600/70 bg-blue-600/20 text-blue-200'
                        : 'border-gray-700 bg-gray-800/80 text-gray-300 hover:border-gray-600 hover:text-white'
                }}"
            >
                <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border {{
                    $isActive ? 'border-blue-400 text-blue-200' : 'border-gray-500 text-gray-300'
                }}">
                    {{ $stepIndex + 1 }}
                </span>
                {{ $wizardStep['label'] }}
            </a>
        @endforeach
    </nav>

    @switch($step)
        @case('review')
            @php
                $plannedCount = $lastWeekPlanned->count();
                $completedCount = $lastWeekCompleted->count();
                $carryOverCount = $lastWeekCarryOver->count();
                $completionRate = $plannedCount > 0 ? round(($completedCount / $plannedCount) * 100, 1) : null;
                $completionBar = $completionRate !== null ? max(min($completionRate, 100), 0) : 0;
            @endphp

            <section class="space-y-6">
                <header class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-100">Revisión rápida de la semana anterior</h3>
                    <p class="text-sm text-gray-400">
                        Semana del {{ $lastWeekStart->translatedFormat('d M') }} al {{ $lastWeekEnd->translatedFormat('d M') }}.
                        Revisa lo planificado, completado y lo que queda por arrastrar.
                    </p>
                </header>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg border border-gray-700 bg-gray-800/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Planificadas</p>
                        <p class="mt-2 text-3xl font-semibold text-white">{{ $plannedCount }}</p>
                        <p class="mt-2 text-xs text-gray-400">Tareas que tenían fecha durante la semana.</p>
                    </div>

                    <div class="rounded-lg border border-gray-700 bg-gray-800/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Completadas</p>
                        <p class="mt-2 text-3xl font-semibold text-white">{{ $completedCount }}</p>
                        <div class="mt-3 space-y-1">
                            <div class="h-2 rounded-full bg-gray-700">
                                <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $completionBar }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400">
                                {{ $completionRate !== null ? $completionRate . '% de lo planificado' : 'Sin tareas planificadas' }}
                            </p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-gray-700 bg-gray-800/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Carry-overs</p>
                        <p class="mt-2 text-3xl font-semibold text-white">{{ $carryOverCount }}</p>
                        <p class="mt-2 text-xs text-gray-400">Considera reagendar estas tareas.</p>
                    </div>

                    <div class="rounded-lg border border-gray-700 bg-gray-800/70 p-4">
                        <p class="text-xs uppercase tracking-wide text-gray-400">Próximo paso</p>
                        <p class="mt-2 text-lg font-semibold text-white">Define tus metas</p>
                        <p class="mt-2 text-xs text-gray-400">Haz clic abajo para continuar con la planificación.</p>
                    </div>
                </div>

                <section class="rounded-lg border border-gray-700 bg-gray-800/70 p-4">
                    <header class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-200">Progreso diario</h4>
                        <p class="text-xs text-gray-400">Balance de tareas planificadas vs. completadas cada día.</p>
                    </header>

                    <div class="space-y-3">
                        @foreach ($lastWeekDailyStats as $dayStat)
                            @php
                                $dayCompletion = $dayStat['completionRate'] !== null
                                    ? max(min($dayStat['completionRate'], 100), 0)
                                    : 0;
                            @endphp
                            <article class="flex flex-col gap-2 rounded-md border border-gray-700/70 bg-gray-900/50 p-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-100">
                                        {{ $dayStat['date']->translatedFormat('l d M') }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $dayStat['planned'] }} planificadas · {{ $dayStat['completed'] }} completadas · {{ $dayStat['carryOver'] }} pendientes
                                    </p>
                                </div>
                                <div class="w-full sm:max-w-xs">
                                    <div class="h-2 rounded-full bg-gray-700">
                                        <div class="h-2 rounded-full bg-blue-500" style="width: {{ $dayCompletion }}%"></div>
                                    </div>
                                    <p class="mt-1 text-right text-xs text-gray-400">
                                        {{ $dayStat['completionRate'] !== null ? $dayStat['completionRate'] . '% completadas' : 'Sin tareas asignadas' }}
                                    </p>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>

                <div class="flex justify-end">
                    <a
                        href="{{ route('plan-next-week', ['step' => 'goals']) }}"
                        class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
                    >
                        Continuar →
                    </a>
                </div>
            </section>
        @break

        @case('goals')
            <section class="space-y-6">
                <header class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-100">Enfoque y metas de la próxima semana</h3>
                    <p class="text-sm text-gray-400">
                        Identifica entre tres y cinco metas que te gustaría lograr. Usa frases cortas y accionables.
                    </p>
                </header>

                <form method="POST" action="{{ route('plan-next-week.goals') }}" class="space-y-4">
                    @csrf
                    <div class="space-y-3">
                        @for ($i = 0; $i < $goalSlots; $i++)
                            @php $existing = $goals[$i] ?? null; @endphp
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    Meta {{ $i + 1 }}
                                </label>
                                <input
                                    type="text"
                                    name="goals[]"
                                    value="{{ old('goals.' . $i, $existing?->title) }}"
                                    placeholder="Ej. Avanzar el borrador del informe Q2"
                                    class="mt-1 w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                                />
                            </div>
                        @endfor
                    </div>

                    <div class="flex items-center justify-between">
                        <a
                            href="{{ route('plan-next-week', ['step' => 'review']) }}"
                            class="text-sm text-gray-400 underline-offset-2 hover:text-gray-200 hover:underline"
                        >
                            ← Volver a la revisión
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
                        >
                            Guardar metas y seguir →
                        </button>
                    </div>
                </form>
            </section>
        @break

        @case('anchors')
            <section class="space-y-6">
                <header class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-100">Refuerza tus anclas semanales</h3>
                    <p class="text-sm text-gray-400">
                        Confirma las rutinas que quieres mantener. Añade nuevas anclas si necesitas reservar bloques fijos.
                    </p>
                </header>

                @if (! $anchorsSchemaReady)
                    <div class="rounded-md border border-amber-600/60 bg-amber-900/30 px-4 py-3 text-sm text-amber-100">
                        Para usar anclas recurrentes primero ejecuta las migraciones correspondientes.
                    </div>
                @elseif (! $anchorsEnabled)
                    <div class="rounded-md border border-amber-600/60 bg-amber-900/30 px-4 py-3 text-sm text-amber-100">
                        Las anclas recurrentes están desactivadas. Habilítalas con la variable <code class="font-mono">PLANNER_ANCHORS=true</code>.
                    </div>
                @else
                    <div class="space-y-3">
                        @forelse ($anchors as $anchor)
                            <article class="rounded-md border border-gray-700 bg-gray-900/60 p-3">
                                <header class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-100">{{ $anchor->title }}</h4>
                                        <p class="text-xs text-gray-400">{{ $anchor->description ?? 'Sin descripción' }}</p>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ $dayNames[$anchor->day_of_week] ?? 'Día' }} · {{ optional($anchor->start_time)->format('H:i') }}–{{ optional($anchor->end_time)->format('H:i') }}
                                    </span>
                                </header>
                            </article>
                        @empty
                            <div class="rounded-md border border-dashed border-gray-600 bg-gray-800/60 px-4 py-6 text-center text-sm text-gray-300">
                                Aún no tienes anclas. Úsalas para reservar tus bloques más importantes.
                            </div>
                        @endforelse
                    </div>

                    <form method="POST" action="{{ route('plan-next-week.anchors') }}" class="rounded-lg border border-gray-700 bg-gray-900/50 p-4 space-y-4">
                        @csrf
                        <h4 class="text-sm font-semibold text-gray-100">Crear nueva ancla</h4>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="anchor-title" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Título</label>
                                <input
                                    id="anchor-title"
                                    name="title"
                                    type="text"
                                    required
                                    value="{{ old('title') }}"
                                    class="mt-1 w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                                />
                            </div>

                            <div class="sm:col-span-2">
                                <label for="anchor-description" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Descripción</label>
                                <textarea
                                    id="anchor-description"
                                    name="description"
                                    rows="2"
                                    class="mt-1 w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                                >{{ old('description') }}</textarea>
                            </div>

                            <div>
                                <label for="anchor-day" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Día</label>
                                <select
                                    id="anchor-day"
                                    name="day_of_week"
                                    class="mt-1 w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                                    required
                                >
                                    <option value="">Selecciona un día</option>
                                    @foreach ($dayNames as $index => $label)
                                        <option value="{{ $index }}" @selected(old('day_of_week') == $index)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="anchor-start" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Inicio</label>
                                <input
                                    id="anchor-start"
                                    name="start_time"
                                    type="time"
                                    value="{{ old('start_time') }}"
                                    required
                                    class="mt-1 w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                                />
                            </div>

                            <div>
                                <label for="anchor-end" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Fin</label>
                                <input
                                    id="anchor-end"
                                    name="end_time"
                                    type="time"
                                    value="{{ old('end_time') }}"
                                    required
                                    class="mt-1 w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                                />
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <a
                                href="{{ route('plan-next-week', ['step' => 'goals']) }}"
                                class="text-sm text-gray-400 underline-offset-2 hover:text-gray-200 hover:underline"
                            >
                                ← Volver a metas
                            </a>

                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
                            >
                                Guardar ancla
                            </button>
                        </div>
                    </form>

                    <div class="flex justify-end">
                        <a
                            href="{{ route('plan-next-week', ['step' => 'schedule']) }}"
                            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
                        >
                            Continuar a programación →
                        </a>
                    </div>
                @endif
            </section>
        @break

        @case('schedule')
            <section class="space-y-6">
                <header class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-100">Programa tus tareas clave</h3>
                    <p class="text-sm text-gray-400">
                        Selecciona las tareas del backlog que sí o sí quieres mover a la próxima semana.
                    </p>
                </header>

                <form method="POST" action="{{ route('plan-next-week.schedule') }}" class="space-y-4">
                    @csrf

                    <div class="space-y-3">
                        @forelse ($backlog as $index => $task)
                            <article class="rounded-md border border-gray-700 bg-gray-900/60 p-4">
                                <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-100">{{ $task->title }}</h4>
                                        <p class="text-xs text-gray-400">{{ $task->description ?: 'Sin descripción adicional.' }}</p>
                                    </div>

                                    <div class="sm:text-right">
                                        <label class="text-xs font-semibold uppercase tracking-wide text-gray-400">Día objetivo</label>
                                        <input type="hidden" name="assignments[{{ $index }}][task_id]" value="{{ $task->id }}">
                                        <select
                                            name="assignments[{{ $index }}][day]"
                                            class="mt-1 w-full rounded-md border border-gray-600 bg-gray-900 px-3 py-1.5 text-sm text-gray-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/70"
                                        >
                                            <option value="">Sin programar</option>
                                            @foreach ($nextWeekDays as $offset => $date)
                                                <option value="{{ $offset }}">
                                                    {{ $date->translatedFormat('l d M') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </header>
                            </article>
                        @empty
                            <div class="rounded-md border border-dashed border-gray-600 bg-gray-800/60 px-4 py-6 text-center text-sm text-gray-300">
                                Tu backlog está vacío o todo ya tiene fecha. ¡Excelente!
                            </div>
                        @endforelse
                    </div>

                    <div class="flex items-center justify-between">
                        <a
                            href="{{ route('plan-next-week', ['step' => 'anchors']) }}"
                            class="text-sm text-gray-400 underline-offset-2 hover:text-gray-200 hover:underline"
                        >
                            ← Volver a anclas
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700"
                        >
                            Guardar programación →
                        </button>
                    </div>
                </form>
            </section>
        @break

        @case('summary')
            <section class="space-y-6">
                <header class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-100">Resumen final antes de crear la semana</h3>
                    <p class="text-sm text-gray-400">
                        Repasa las metas, los bloques fijos y las tareas ya programadas para la próxima semana.
                    </p>
                </header>

                <div class="grid gap-5 lg:grid-cols-3">
                    <section class="space-y-3 rounded-lg border border-gray-700 bg-gray-900/60 p-4 lg:col-span-1">
                        <h4 class="text-sm font-semibold text-gray-100">Metas seleccionadas</h4>
                        <ul class="space-y-2 text-sm text-gray-200">
                            @forelse ($goals as $goal)
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-blue-400"></span>
                                    <span>{{ $goal->title }}</span>
                                </li>
                            @empty
                                <li class="rounded-md border border-dashed border-gray-600 bg-gray-800/60 px-3 py-3 text-center text-xs text-gray-400">
                                    Aún no definiste metas. Puedes volver atrás para registrarlas.
                                </li>
                            @endforelse
                        </ul>
                    </section>

                    <section class="space-y-3 rounded-lg border border-gray-700 bg-gray-900/60 p-4 lg:col-span-1">
                        <h4 class="text-sm font-semibold text-gray-100">Anclas activas</h4>
                        <ul class="space-y-2 text-sm text-gray-200">
                            @if ($anchorsEnabled && $anchors->isNotEmpty())
                                @foreach ($anchors as $anchor)
                                    <li class="flex items-start gap-2">
                                        <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                                        <span>
                                            {{ $anchor->title }} · {{ $dayNames[$anchor->day_of_week] ?? 'Día' }} {{ optional($anchor->start_time)->format('H:i') }}-{{ optional($anchor->end_time)->format('H:i') }}
                                        </span>
                                    </li>
                                @endforeach
                            @elseif ($anchorsSchemaReady)
                                <li class="rounded-md border border-dashed border-gray-600 bg-gray-800/60 px-3 py-3 text-center text-xs text-gray-400">
                                    Sin anclas configuradas todavía.
                                </li>
                            @else
                                <li class="rounded-md border border-dashed border-gray-600 bg-gray-800/60 px-3 py-3 text-center text-xs text-amber-200">
                                    Las anclas estarán disponibles una vez que ejecutes las migraciones.
                                </li>
                            @endif
                        </ul>
                    </section>

                    <section class="space-y-3 rounded-lg border border-gray-700 bg-gray-900/60 p-4 lg:col-span-1">
                        <h4 class="text-sm font-semibold text-gray-100">Tareas ya asignadas</h4>
                        <ul class="space-y-2 text-sm text-gray-200">
                            @forelse ($nextWeekAssignments as $task)
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 inline-flex h-2 w-2 rounded-full bg-purple-400"></span>
                                    <span>
                                        {{ $task->title }} · {{ optional($task->due_date)->translatedFormat('l d M') }}
                                    </span>
                                </li>
                            @empty
                                <li class="rounded-md border border-dashed border-gray-600 bg-gray-800/60 px-3 py-3 text-center text-xs text-gray-400">
                                    Aún no moviste tareas al calendario de la próxima semana.
                                </li>
                            @endforelse
                        </ul>
                    </section>
                </div>

                <form method="POST" action="{{ route('plan-next-week.finalize') }}" class="flex items-center justify-between">
                    @csrf
                    <a
                        href="{{ route('plan-next-week', ['step' => 'schedule']) }}"
                        class="text-sm text-gray-400 underline-offset-2 hover:text-gray-200 hover:underline"
                    >
                        ← Volver a programación
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700"
                    >
                        Materializar semana →
                    </button>
                </form>
            </section>
        @break
    @endswitch
</x-task-page>

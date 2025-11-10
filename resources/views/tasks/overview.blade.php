<x-task-page title="Resumen" subtitle="Una vista panorámica de lo que viene, lo que falta planificar y los logros recientes.">
    <div class="grid gap-6 lg:grid-cols-2">
        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-100">Retrasadas</h3>
                <span class="text-xs text-gray-400">{{ $overdue->count() }} tareas</span>
            </header>

            @forelse ($overdue as $task)
                <x-task-card :task="$task" />
            @empty
                <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-6 rounded-lg">
                    Sin pendientes atrasados.
                </div>
            @endforelse
        </section>

        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-gray-100">Próximos 7 días</h3>
                <span class="text-xs text-gray-400">{{ $upcoming->count() }} tareas</span>
            </header>

            @forelse ($upcoming as $task)
                <x-task-card :task="$task" />
            @empty
                <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-6 rounded-lg">
                    Aún no hay tareas planificadas para la próxima semana.
                </div>
            @endforelse
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-100">Sin fecha</h3>
                    <p class="text-xs text-gray-400">Ideal para planificar desde el Inbox.</p>
                </div>
                <span class="text-xs text-gray-400">{{ $unscheduled->count() }} tareas</span>
            </header>

            @forelse ($unscheduled as $task)
                <x-task-card :task="$task" :show-due-date="false" />
            @empty
                <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-6 rounded-lg">
                    Todo tiene fecha asignada.
                </div>
            @endforelse
        </section>

        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-100">Completadas recientemente</h3>
                    <p class="text-xs text-gray-400">Últimas {{ $recentlyCompleted->count() }} tareas marcadas como listas.</p>
                </div>
            </header>

            @forelse ($recentlyCompleted as $task)
                <x-task-card :task="$task" :actions="false" :show-meta-slot="true" :show-completed-at="true">
                    <x-slot name="meta">
                        <p class="text-xs text-gray-400">
                            {{ optional($task->updated_at)->diffForHumans() }}
                        </p>
                    </x-slot>
                </x-task-card>
            @empty
                <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-6 rounded-lg">
                    Aún no hay tareas completadas recientemente.
                </div>
            @endforelse
        </section>
    </div>
</x-task-page>

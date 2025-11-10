<x-task-page title="Hoy" subtitle="Concéntrate en los compromisos críticos de hoy y recupera lo que quedó pendiente.">
    <section class="space-y-3">
        <header class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-100">Retrasadas</h3>
            <span class="text-xs text-gray-400">{{ $overdue->count() }} tareas</span>
        </header>

        @forelse ($overdue as $task)
            <x-task-card :task="$task" />
        @empty
            <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-6 rounded-lg">
                Sin tareas atrasadas. ¡Bien hecho!
            </div>
        @endforelse
    </section>

    <section class="space-y-3">
        <header class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-100">Para hoy</h3>
            <span class="text-xs text-gray-400">{{ $today->count() }} tareas</span>
        </header>

        @forelse ($today as $task)
            <x-task-card :task="$task" />
        @empty
            <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-6 rounded-lg">
                No hay nada planificado para hoy. Puedes asignar tareas desde el Inbox o la vista semanal.
            </div>
        @endforelse
    </section>
</x-task-page>

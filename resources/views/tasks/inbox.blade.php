<x-task-page title="Inbox" subtitle="Tareas que aún no tienen día asignado. Úsalas como bandeja de entrada antes de planificarlas.">
    <section class="space-y-3">
        <header class="flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-100">Sin fecha programada</h3>
            <span class="text-xs text-gray-400">{{ $unscheduled->count() }} tareas</span>
        </header>

        @forelse ($unscheduled as $task)
            <x-task-card :task="$task" :show-due-date="false" />
        @empty
            <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-10 rounded-lg">
                Todo organizado. ¡Asigna nuevas tareas desde cualquier vista!
            </div>
        @endforelse
    </section>

    @if ($scheduledSoon->isNotEmpty())
        <section class="space-y-3">
            <header class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-gray-100">Programadas recientemente</h3>
                    <p class="text-xs text-gray-400">Un vistazo rápido a los próximos pasos que salieron del Inbox.</p>
                </div>
            </header>

            @foreach ($scheduledSoon as $task)
                <x-task-card :task="$task" />
            @endforeach
        </section>
    @endif
</x-task-page>

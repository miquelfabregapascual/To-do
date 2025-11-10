<x-task-page title="Completadas" subtitle="Revisa los entregables terminados y recupera contexto de cuándo sucedieron.">
    <section class="space-y-4">
        @forelse ($completedGroups as $weekLabel => $tasks)
            <div class="space-y-3">
                <header class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-100">{{ $weekLabel }}</h3>
                    <span class="text-xs text-gray-400">{{ $tasks->count() }} tareas</span>
                </header>

                @foreach ($tasks as $task)
                    <x-task-card :task="$task" :show-meta-slot="true" :show-completed-at="true">
                        <x-slot name="meta">
                            <p class="text-xs text-gray-400">
                                <span class="font-medium">Marcada como completa:</span>
                                {{ optional($task->updated_at)->translatedFormat('d M Y H:i') }}
                            </p>
                        </x-slot>
                    </x-task-card>
                @endforeach
            </div>
        @empty
            <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-10 rounded-lg">
                Todavía no has completado tareas. Marca las de hoy cuando las finalices.
            </div>
        @endforelse
    </section>
</x-task-page>

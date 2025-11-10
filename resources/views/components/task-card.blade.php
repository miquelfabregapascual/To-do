@props([
    'task',
    'showDueDate' => true,
    'showCompletedAt' => false,
    'showMetaSlot' => false,
    'actions' => true,
])

<article class="bg-gray-800/90 border border-gray-700 rounded-lg p-4">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-2">
            <header class="flex items-center gap-2">
                <span class="text-sm font-medium {{ $task->completed ? 'line-through text-gray-400' : 'text-gray-100' }}">
                    {{ $task->title }}
                </span>

                @if ($task->completed)
                    <span class="inline-flex items-center rounded-full bg-emerald-500/15 text-emerald-200 text-[11px] px-2 py-0.5">
                        Listo
                    </span>
                @endif
            </header>

            <div class="space-y-1">
                @if ($showDueDate)
                    <p class="text-xs text-gray-400">
                        @if ($task->due_date)
                            <span class="font-medium">Vence:</span>
                            {{ optional($task->due_date)->translatedFormat('d M Y') }}
                        @else
                            <span class="font-medium">Sin fecha programada</span>
                        @endif
                    </p>
                @endif

                @if ($showCompletedAt)
                    <p class="text-xs text-gray-400">
                        <span class="font-medium">Actualizado:</span>
                        {{ optional($task->updated_at)->diffForHumans() }}
                    </p>
                @endif

                @if ($showMetaSlot)
                    {{ $meta ?? '' }}
                @endif
            </div>

            <x-task-description
                :text="$task->description"
                :title="$task->title"
                :task-id="$task->id"
                paragraph-class="text-sm text-gray-200"
            />
        </div>

        @if ($actions)
            <div class="flex flex-col items-end gap-2 shrink-0">
                <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                    @csrf
                    @method('PATCH')
                    <button class="text-[11px] underline hover:no-underline hover:text-blue-300">
                        {{ $task->completed ? 'Desmarcar' : 'Completar' }}
                    </button>
                </form>

                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('¿Eliminar tarea?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-[11px] text-red-300 underline hover:no-underline hover:text-red-200">
                        Eliminar
                    </button>
                </form>
            </div>
        @endif
    </div>
</article>

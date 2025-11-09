<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl sm:text-2xl font-semibold text-white tracking-tight">{{ $title }}</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-4xl mx-auto px-4 space-y-3">
            @forelse ($tasks as $task)
                <div class="bg-gray-800/90 border border-gray-700 rounded-lg p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm {{ $task->completed ? 'line-through text-gray-400' : 'text-gray-100' }}">
                                {{ $task->title }}
                            </div>
                            @if($task->due_date)
                                <div class="text-xs text-gray-400 mt-1">
                                    Vence: {{ \Carbon\Carbon::parse($task->due_date)->translatedFormat('d M Y') }}
                                </div>
                            @endif

                            @if ($task->description)
                                <p class="mt-2 text-xs text-gray-300 clamp-resp break-words whitespace-pre-wrap">
                                    {{ $task->description }}
                                </p>
                            @endif>
                        </div>

                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <form method="POST" action="{{ route('tasks.toggle', $task) }}">
                                @csrf @method('PATCH')
                                <button class="text-[11px] underline hover:no-underline hover:text-blue-300">
                                    {{ $task->completed ? 'Desmarcar' : 'Completar' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('¿Eliminar tarea?')">
                                @csrf @method('DELETE')
                                <button class="text-[11px] text-red-300 underline hover:no-underline hover:text-red-200">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-sm text-gray-400 text-center py-10">Sin tareas</div>
            @endforelse
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl sm:text-2xl font-semibold text-white tracking-tight">{{ $title }}</h2>
    </x-slot>

    <div class="py-8 bg-gray-900 min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-12 gap-6">
                <aside class="hidden md:block md:col-span-3">
                    <x-sidebar />
                </aside>

                <main class="col-span-12 md:col-span-9 space-y-4">
                    @if (session('success'))
                        <div class="bg-green-900/30 border border-green-700 text-green-100 px-4 py-2 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif

                    @forelse ($tasks as $task)
                        <article class="bg-gray-800/90 border border-gray-700 rounded-lg p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="text-sm {{ $task->completed ? 'line-through text-gray-400' : 'text-gray-100' }}">
                                        {{ $task->title }}
                                    </div>

                                    @if ($task->due_date)
                                        <div class="text-xs text-gray-400 mt-1">
                                            Vence: {{ \Carbon\Carbon::parse($task->due_date)->translatedFormat('d M Y') }}
                                        </div>
                                    @endif

                                    <x-task-description
                                        :text="$task->description"
                                        :title="$task->title"
                                        :task-id="$task->id"
                                        paragraph-class="mt-2"
                                    />
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
                        </article>
                    @empty
                        <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-10 rounded-lg">
                            Sin tareas
                        </div>
                    @endforelse
                </main>
            </div>
        </div>
    </div>
</x-app-layout>

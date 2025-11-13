<x-task-page
    title="Backlog"
    subtitle="Revisa tus ideas y decide qué pasa a tu semana. Usa las acciones rápidas para priorizar, etiquetar y enviar a Inbox o al archivo.">
    <div
        x-data="backlogTriage({
            tasks: @js($tasks->map(fn ($task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'labels' => $task->labels ?? [],
                'created_at' => optional($task->created_at)->diffForHumans(),
            ])->values()),
            scheduleOptions: @js($scheduleOptions),
            routes: {
                schedule: '{{ route('planner.schedule') }}',
                triage: '{{ route('tasks.triage', ['task' => '__ID__']) }}',
            },
            csrf: '{{ csrf_token() }}',
        })"
        x-cloak
        class="space-y-5"
    >
        <template x-if="tasks.length === 0">
            <div class="bg-gray-800/70 border border-dashed border-gray-600 text-sm text-gray-300 text-center py-12 rounded-lg">
                No hay tareas en backlog. Usa la bandeja de entrada para capturar y envía aquí lo que quieras revisar más tarde.
            </div>
        </template>

        <template x-for="task in tasks" :key="task.id">
            <article class="bg-gray-800/90 border border-gray-700 rounded-lg p-4 space-y-4">
                <header class="flex items-start justify-between gap-3">
                    <div class="space-y-1 min-w-0">
                        <h3 class="text-sm font-semibold text-gray-100" x-text="task.title"></h3>
                        <p class="text-xs text-gray-400" x-show="task.created_at">
                            Capturada <span x-text="task.created_at"></span>
                        </p>
                        <p class="text-sm text-gray-200" x-show="task.description" x-text="task.description"></p>
                    </div>

                    <div class="flex flex-col items-end gap-2">
                        <button
                            type="button"
                            class="text-[11px] text-blue-200 underline decoration-dotted hover:text-blue-100"
                            x-bind:data-task-detail-trigger="task.id"
                        >
                            Detalle
                        </button>

                        <div class="flex flex-wrap gap-2 justify-end">
                            <template x-for="label in task.labels" :key="label">
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-100" x-text="label"></span>
                            </template>
                        </div>
                    </div>
                </header>

                <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="space-y-1">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Prioridad</h4>
                        <select
                            class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="updatePriority(task, $event.target.value)"
                            :value="task.priority ?? ''"
                        >
                            <option value="">Sin prioridad</option>
                            <option value="1">P1 — Crítica</option>
                            <option value="2">P2 — Importante</option>
                            <option value="3">P3 — Media</option>
                            <option value="4">P4 — Baja</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Etiquetas</h4>
                        <input
                            type="text"
                            class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :value="task.labels.join(', ')"
                            @change="updateLabels(task, $event.target.value)"
                            placeholder="work, ideas, ..."
                        />
                        <p class="text-[11px] text-gray-500">Separa con comas.</p>
                    </div>

                    <div class="space-y-1">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Agendar</h4>
                        <select
                            class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            @change="scheduleTask(task, $event.target.value)"
                        >
                            <option value="">Elige día…</option>
                            <template x-for="option in scheduleOptions" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                        <p class="text-[11px] text-gray-500">Se moverá a Inbox.</p>
                    </div>

                    <div class="space-y-1">
                        <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-400">Mover a</h4>
                        <div class="flex flex-col gap-2">
                            <button
                                type="button"
                                class="text-sm bg-blue-600/80 hover:bg-blue-600 text-white rounded px-3 py-2"
                                @click="moveTask(task, 'inbox')"
                            >Inbox</button>
                            <button
                                type="button"
                                class="text-sm bg-gray-700/70 hover:bg-gray-700 text-gray-100 rounded px-3 py-2"
                                @click="moveTask(task, 'archived')"
                            >Archivo</button>
                        </div>
                    </div>
                </section>
            </article>
        </template>
    </div>
</x-task-page>

@once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('backlogTriage', (config) => ({
                tasks: config.tasks || [],
                scheduleOptions: config.scheduleOptions || [],
                routes: config.routes,
                csrf: config.csrf,
                async scheduleTask(task, dueDate) {
                    if (!dueDate) {
                        return;
                    }

                    await this.postJson(this.routes.schedule, {
                        task_id: task.id,
                        due_date: dueDate,
                    });
                    this.removeTask(task.id);
                },
                async moveTask(task, stage) {
                    await this.patchTask(task.id, { stage });
                    this.removeTask(task.id);
                },
                async updatePriority(task, priority) {
                    const value = priority ? Number(priority) : null;
                    const response = await this.patchTask(task.id, { priority: value });
                    task.priority = response.task.priority;
                },
                async updateLabels(task, labelsInput) {
                    const labels = labelsInput
                        .split(',')
                        .map((label) => label.trim())
                        .filter((label) => label.length > 0);
                    const response = await this.patchTask(task.id, { labels });
                    task.labels = response.task.labels || [];
                },
                async patchTask(taskId, payload) {
                    const url = this.routes.triage.replace('__ID__', taskId);
                    return this.postJson(url, payload, 'PATCH');
                },
                async postJson(url, payload, method = 'POST') {
                    const response = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!response.ok) {
                        throw new Error('No se pudo guardar.');
                    }

                    return response.json();
                },
                removeTask(taskId) {
                    this.tasks = this.tasks.filter((item) => item.id !== taskId);
                },
            }));
        });
    </script>
@endonce

@props(['routes' => []])

@php
    $showRoute = $routes['show'] ?? '';
    $updateRoute = $routes['update'] ?? '';
@endphp

<div
    data-task-detail-root
    data-show-route="{{ $showRoute }}"
    data-update-route="{{ $updateRoute }}"
    data-csrf-token="{{ csrf_token() }}"
    class="fixed inset-0 z-40 hidden"
    aria-hidden="true">
    <div
        data-task-detail-overlay
        class="absolute inset-0 bg-black/60 opacity-0 transition-opacity"
    ></div>

    <section
        data-task-detail-panel
        class="relative ml-auto flex h-full w-full max-w-lg flex-col bg-gray-900 text-gray-100 shadow-2xl transition-transform duration-200 ease-out translate-x-full"
    >
        <header class="flex items-start justify-between gap-3 border-b border-gray-800 px-6 py-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-500">Detalle de la tarea</p>
                <h2 class="text-lg font-semibold text-white" data-task-detail-heading>—</h2>
            </div>
            <button
                type="button"
                data-task-detail-close
                class="rounded-full p-2 text-gray-400 transition hover:bg-gray-800 hover:text-white"
                aria-label="Cerrar panel"
            >
                ✕
            </button>
        </header>

        <div class="flex-1 overflow-y-auto px-6 py-5">
            <div data-task-detail-loading class="text-sm text-gray-400">Cargando información…</div>

            <div data-task-detail-form-wrapper class="hidden space-y-5">
                <div data-task-detail-error class="hidden rounded-md border border-red-500/40 bg-red-900/30 px-3 py-2 text-sm text-red-200"></div>
                <div data-task-detail-success class="hidden rounded-md border border-emerald-500/40 bg-emerald-900/20 px-3 py-2 text-sm text-emerald-200"></div>

                <form data-task-detail-form class="space-y-5">
                    <div class="space-y-2">
                        <label for="task-detail-title" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Título</label>
                        <input
                            id="task-detail-title"
                            name="title"
                            type="text"
                            class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            placeholder="Describe brevemente la tarea"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="task-detail-description" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Notas (Markdown)</label>
                        <textarea
                            id="task-detail-description"
                            name="description"
                            rows="4"
                            class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            placeholder="Agrega contexto, checklist o enlaces. Puedes usar Markdown."
                        ></textarea>
                        <p class="text-[11px] text-gray-500">Usa **negritas**, listas o enlaces en Markdown para documentar mejor.</p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label for="task-detail-priority" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Prioridad</label>
                            <select
                                id="task-detail-priority"
                                name="priority"
                                class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Sin prioridad</option>
                                <option value="1">P1 — Crítica</option>
                                <option value="2">P2 — Importante</option>
                                <option value="3">P3 — Media</option>
                                <option value="4">P4 — Baja</option>
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label for="task-detail-estimate" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Estimación (min)</label>
                            <input
                                id="task-detail-estimate"
                                name="estimate_minutes"
                                type="number"
                                min="5"
                                step="5"
                                class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                placeholder="Ej. 45"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="task-detail-labels" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Etiquetas</label>
                        <input
                            id="task-detail-labels"
                            name="labels"
                            type="text"
                            class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            placeholder="work, personal, prioridades"
                        />
                        <p class="text-[11px] text-gray-500">Separa las etiquetas con comas.</p>
                    </div>

                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <label for="task-detail-due-date" class="text-xs font-semibold uppercase tracking-wide text-gray-400">Fecha programada</label>
                            <div class="flex gap-2 text-[11px] text-gray-400">
                                <button type="button" data-task-detail-clear-date class="underline decoration-dotted decoration-gray-500 hover:text-gray-200">Quitar fecha</button>
                                <button type="button" data-task-detail-schedule-today class="underline decoration-dotted decoration-gray-500 hover:text-gray-200">Hoy</button>
                            </div>
                        </div>
                        <input
                            id="task-detail-due-date"
                            name="due_date"
                            type="date"
                            class="w-full rounded-md border border-gray-700 bg-gray-900 px-3 py-2 text-sm text-gray-100 focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        />
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Subtareas</p>
                            <button type="button" data-add-subtask class="text-[11px] font-semibold text-blue-300 hover:text-blue-200">+ Añadir</button>
                        </div>
                        <div data-subtasks-list class="space-y-3"></div>
                        <p class="text-[11px] text-gray-500">Divide la tarea en pasos manejables y marca su avance sin salir del panel.</p>
                    </div>
                </form>

                <div class="flex flex-col gap-3 border-t border-gray-800 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <button
                        type="button"
                        data-task-detail-save
                        class="inline-flex w-full items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-500 sm:w-auto"
                    >
                        Guardar cambios
                    </button>

                    <button
                        type="button"
                        data-task-detail-schedule
                        class="inline-flex w-full items-center justify-center rounded-md border border-blue-400/60 px-4 py-2 text-sm font-semibold text-blue-200 transition hover:bg-blue-500/10 sm:w-auto"
                    >
                        Guardar y agendar
                    </button>
                </div>
            </div>
        </div>
    </section>

    <template data-subtask-template>
        <div data-subtask-row class="flex items-center gap-2 rounded-md border border-gray-700 bg-gray-900/60 px-3 py-2">
            <input type="checkbox" data-subtask-completed class="h-4 w-4 rounded border-gray-600 text-blue-500 focus:ring-blue-500" />
            <input
                type="text"
                data-subtask-title
                class="flex-1 border-0 bg-transparent text-sm text-gray-100 focus:border-0 focus:ring-0"
                placeholder="Ej. Redactar borrador"
            />
            <button type="button" data-remove-subtask class="text-xs text-gray-500 transition hover:text-red-300">✕</button>
        </div>
    </template>
</div>

import './bootstrap';
import '../css/app.css';

const autoResizeDescription = (event) => {
    if (!event.target.matches('#description')) {
        return;
    }

    event.target.style.height = 'auto';
    event.target.style.height = `${event.target.scrollHeight}px`;
};

document.addEventListener('input', autoResizeDescription);

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-read-more-toggle]');
    if (!toggle) {
        return;
    }

    const targetId = toggle.getAttribute('data-target');
    if (!targetId) {
        return;
    }

    const wrapper = document.getElementById(targetId);
    const text = wrapper?.querySelector('[data-description-text]');
    if (!wrapper || !text) {
        return;
    }

    const expanded = wrapper.getAttribute('data-expanded') === 'true';
    const nextExpanded = !expanded;

    wrapper.setAttribute('data-expanded', String(nextExpanded));
    toggle.setAttribute('aria-expanded', String(nextExpanded));

    const labelTarget = toggle.querySelector('[data-toggle-label]') || toggle;
    const moreLabel = toggle.getAttribute('data-label-more') || 'Leer más';
    const lessLabel = toggle.getAttribute('data-label-less') || 'Leer menos';

    labelTarget.textContent = nextExpanded ? lessLabel : moreLabel;
});

function setupTaskDetailTriggers() {
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-task-detail-trigger]');
        if (!trigger) {
            return;
        }

        const taskId = trigger.getAttribute('data-task-detail-trigger');
        if (!taskId || typeof window.openTaskDetailDrawer !== 'function') {
            return;
        }

        event.preventDefault();
        window.openTaskDetailDrawer(taskId);
    });
}

function setupTaskDetailDrawer() {
    const root = document.querySelector('[data-task-detail-root]');
    if (!root) {
        return;
    }

    const overlay = root.querySelector('[data-task-detail-overlay]');
    const panel = root.querySelector('[data-task-detail-panel]');
    const heading = root.querySelector('[data-task-detail-heading]');
    const loadingState = root.querySelector('[data-task-detail-loading]');
    const formWrapper = root.querySelector('[data-task-detail-form-wrapper]');
    const form = root.querySelector('[data-task-detail-form]');
    const errorBox = root.querySelector('[data-task-detail-error]');
    const successBox = root.querySelector('[data-task-detail-success]');
    const subtasksList = root.querySelector('[data-subtasks-list]');
    const addSubtaskButton = root.querySelector('[data-add-subtask]');
    const subtaskTemplate = root.querySelector('[data-subtask-template]');
    const saveButton = root.querySelector('[data-task-detail-save]');
    const scheduleButton = root.querySelector('[data-task-detail-schedule]');
    const clearDateButton = root.querySelector('[data-task-detail-clear-date]');
    const scheduleTodayButton = root.querySelector('[data-task-detail-schedule-today]');
    const closeButtons = root.querySelectorAll('[data-task-detail-close]');

    let successTimeout = null;
    const state = {
        currentTaskId: null,
        subtasks: [],
    };

    const showRouteTemplate = root.getAttribute('data-show-route');
    const updateRouteTemplate = root.getAttribute('data-update-route');
    const csrfToken = root.getAttribute('data-csrf-token');

    const setLoading = (isLoading) => {
        if (loadingState) {
            loadingState.classList.toggle('hidden', !isLoading);
        }
        if (formWrapper) {
            formWrapper.classList.toggle('hidden', isLoading);
        }
    };

    const setError = (message) => {
        if (!errorBox) {
            return;
        }
        if (!message) {
            errorBox.classList.add('hidden');
            errorBox.textContent = '';
            return;
        }
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    };

    const setSuccess = (message) => {
        if (!successBox) {
            return;
        }
        if (successTimeout) {
            window.clearTimeout(successTimeout);
            successTimeout = null;
        }
        if (!message) {
            successBox.classList.add('hidden');
            successBox.textContent = '';
            return;
        }
        successBox.textContent = message;
        successBox.classList.remove('hidden');
        successTimeout = window.setTimeout(() => {
            successBox?.classList.add('hidden');
        }, 2500);
    };

    const getRouteForTask = (template, taskId) => {
        return template ? template.replace('__TASK__', taskId) : '';
    };

    const renderSubtasks = () => {
        if (!subtasksList) {
            return;
        }
        subtasksList.innerHTML = '';

        if (!state.subtasks.length) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-gray-400';
            empty.textContent = 'Sin subtareas por ahora.';
            subtasksList.appendChild(empty);
            return;
        }

        state.subtasks.forEach((subtask, index) => {
            const row = subtaskTemplate?.content?.firstElementChild?.cloneNode(true);
            if (!row) {
                return;
            }
            row.dataset.index = String(index);
            const checkbox = row.querySelector('[data-subtask-completed]');
            const input = row.querySelector('[data-subtask-title]');
            if (checkbox) {
                checkbox.checked = Boolean(subtask.completed);
            }
            if (input) {
                input.value = subtask.title || '';
            }
            subtasksList.appendChild(row);
        });
    };

    const fillForm = (task) => {
        if (!form) {
            return;
        }
        if (heading) {
            heading.textContent = task.title || 'Tarea';
        }
        form.elements.title.value = task.title || '';
        form.elements.description.value = task.description || '';
        form.elements.priority.value = task.priority ?? '';
        form.elements.labels.value = task.labels_text || (task.labels || []).join(', ');
        form.elements.estimate_minutes.value = task.estimate_minutes ?? '';
        form.elements.due_date.value = task.due_date ?? '';
        state.subtasks = Array.isArray(task.subtasks) ? [...task.subtasks] : [];
        renderSubtasks();
    };

    const fetchTask = async (taskId) => {
        setLoading(true);
        try {
            const response = await fetch(getRouteForTask(showRouteTemplate, taskId), {
                headers: {
                    Accept: 'application/json',
                },
            });
            if (!response.ok) {
                throw new Error('No se pudo cargar la tarea.');
            }
            const data = await response.json();
            fillForm(data.task);
            setLoading(false);
        } catch (error) {
            setLoading(false);
            setError(error.message || 'Error al cargar la tarea.');
        }
    };

    const collectPayload = () => {
        if (!form) {
            return {};
        }
        const labelsInput = form.elements.labels.value || '';
        const labels = labelsInput
            .split(',')
            .map((label) => label.trim())
            .filter((label) => label.length > 0);

        const subtasks = state.subtasks
            .map((subtask) => ({
                title: subtask.title || '',
                completed: Boolean(subtask.completed),
            }))
            .filter((subtask) => subtask.title.trim().length > 0);

        return {
            title: form.elements.title.value.trim(),
            description: form.elements.description.value.trim(),
            priority: form.elements.priority.value ? Number(form.elements.priority.value) : null,
            labels,
            estimate_minutes: form.elements.estimate_minutes.value
                ? Number(form.elements.estimate_minutes.value)
                : null,
            due_date: form.elements.due_date.value || null,
            subtasks,
        };
    };

    const toggleSaving = (isSaving) => {
        [saveButton, scheduleButton].forEach((button) => {
            if (!button) {
                return;
            }
            button.classList.toggle('opacity-60', isSaving);
            button.toggleAttribute('disabled', isSaving);
        });
    };

    const submitChanges = async ({ requireDueDate = false } = {}) => {
        if (!state.currentTaskId || !form) {
            return;
        }
        if (requireDueDate && !form.elements.due_date.value) {
            setError('Selecciona una fecha para agendar esta tarea.');
            return;
        }

        setError(null);
        setSuccess(null);
        toggleSaving(true);

        try {
            const response = await fetch(getRouteForTask(updateRouteTemplate, state.currentTaskId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify(collectPayload()),
            });

            if (!response.ok) {
                throw new Error('No se pudo guardar la tarea.');
            }

            const data = await response.json();
            fillForm(data.task);
            setSuccess('Cambios guardados.');
        } catch (error) {
            setError(error.message || 'Error al guardar los cambios.');
        } finally {
            toggleSaving(false);
        }
    };

    const openDrawer = (taskId) => {
        state.currentTaskId = taskId;
        root.classList.remove('hidden');
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        window.requestAnimationFrame(() => {
            overlay?.classList.add('opacity-100');
            panel?.classList.remove('translate-x-full');
        });
        setError(null);
        setSuccess(null);
        fetchTask(taskId);
    };

    const closeDrawer = () => {
        panel?.classList.add('translate-x-full');
        overlay?.classList.remove('opacity-100');
        root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        window.setTimeout(() => {
            if (root.getAttribute('aria-hidden') === 'true') {
                root.classList.add('hidden');
            }
        }, 200);
    };

    addSubtaskButton?.addEventListener('click', () => {
        state.subtasks.push({ title: '', completed: false });
        renderSubtasks();
    });

    subtasksList?.addEventListener('input', (event) => {
        const row = event.target.closest('[data-subtask-row]');
        if (!row) {
            return;
        }
        const index = Number(row.dataset.index);
        if (Number.isNaN(index) || !state.subtasks[index]) {
            return;
        }
        if (event.target.matches('[data-subtask-title]')) {
            state.subtasks[index].title = event.target.value;
        }
    });

    subtasksList?.addEventListener('change', (event) => {
        const row = event.target.closest('[data-subtask-row]');
        if (!row) {
            return;
        }
        const index = Number(row.dataset.index);
        if (Number.isNaN(index) || !state.subtasks[index]) {
            return;
        }
        if (event.target.matches('[data-subtask-completed]')) {
            state.subtasks[index].completed = event.target.checked;
        }
    });

    subtasksList?.addEventListener('click', (event) => {
        if (!event.target.matches('[data-remove-subtask]')) {
            return;
        }
        const row = event.target.closest('[data-subtask-row]');
        if (!row) {
            return;
        }
        const index = Number(row.dataset.index);
        if (Number.isNaN(index)) {
            return;
        }
        state.subtasks.splice(index, 1);
        renderSubtasks();
    });

    form?.addEventListener('submit', (event) => {
        event.preventDefault();
        submitChanges();
    });

    saveButton?.addEventListener('click', (event) => {
        event.preventDefault();
        submitChanges();
    });

    scheduleButton?.addEventListener('click', (event) => {
        event.preventDefault();
        submitChanges({ requireDueDate: true });
    });

    clearDateButton?.addEventListener('click', (event) => {
        event.preventDefault();
        if (form) {
            form.elements.due_date.value = '';
        }
    });

    scheduleTodayButton?.addEventListener('click', (event) => {
        event.preventDefault();
        if (form) {
            const today = new Date();
            form.elements.due_date.value = today.toISOString().slice(0, 10);
        }
    });

    overlay?.addEventListener('click', closeDrawer);
    closeButtons.forEach((button) => button.addEventListener('click', closeDrawer));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !root.classList.contains('hidden')) {
            closeDrawer();
        }
    });

    window.openTaskDetailDrawer = (taskId) => {
        openDrawer(taskId);
    };
}

window.addEventListener('DOMContentLoaded', () => {
    setupTaskDetailDrawer();
    setupTaskDetailTriggers();
});

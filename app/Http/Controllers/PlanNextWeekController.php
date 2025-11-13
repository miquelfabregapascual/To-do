<?php

namespace App\Http\Controllers;

use App\Models\RecurringAnchor;
use App\Models\Task;
use App\Models\WeeklyGoal;
use App\Services\RecurringAnchorService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PlanNextWeekController extends Controller
{
    public function __construct(private readonly RecurringAnchorService $recurringAnchorService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user, 403);

        $step = $request->query('step', 'review');
        $step = in_array($step, ['review', 'goals', 'anchors', 'schedule', 'summary'], true)
            ? $step
            : 'review';

        $now = Carbon::now();
        $currentWeekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $lastWeekStart = $currentWeekStart->copy()->subWeek();
        $lastWeekEnd = $lastWeekStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
        $nextWeekStart = $currentWeekStart->copy()->addWeek();
        $nextWeekEnd = $nextWeekStart->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();
        $anchorsEnabled = $anchorsSchemaReady && config('planner.anchors.enabled');

        $lastWeekPlanned = Task::query()
            ->where('user_id', $user->id)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [
                $lastWeekStart->toDateString(),
                $lastWeekEnd->toDateString(),
            ])
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->orderBy('due_date')
            ->get();

        $lastWeekCompleted = $lastWeekPlanned->where('completed', true);
        $lastWeekCarryOver = $lastWeekPlanned->where('completed', false)->values();

        $lastWeekDailyStats = collect(range(0, 6))->map(function (int $offset) use ($lastWeekStart, $lastWeekPlanned) {
            $day = $lastWeekStart->copy()->addDays($offset);
            $tasks = $lastWeekPlanned->filter(fn (Task $task) => optional($task->due_date)?->isSameDay($day));

            $planned = $tasks->count();
            $completed = $tasks->where('completed', true)->count();

            return [
                'date' => $day,
                'planned' => $planned,
                'completed' => $completed,
                'carryOver' => $tasks->where('completed', false)->count(),
                'completionRate' => $planned > 0 ? round(($completed / $planned) * 100, 1) : null,
            ];
        });

        $goals = WeeklyGoal::query()
            ->where('user_id', $user->id)
            ->whereDate('week_start_date', $nextWeekStart->toDateString())
            ->orderBy('position')
            ->get();

        $goalSlots = max(3, $goals->count() + 1);

        $anchors = $anchorsEnabled
            ? RecurringAnchor::query()
                ->where('user_id', $user->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
            : collect();

        $backlog = Task::query()
            ->where('user_id', $user->id)
            ->whereNull('due_date')
            ->where('completed', false)
            ->where('stage', Task::STAGE_INBOX)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->orderBy('created_at')
            ->get();

        $nextWeekAssignments = Task::query()
            ->where('user_id', $user->id)
            ->whereBetween('due_date', [
                $nextWeekStart->toDateString(),
                $nextWeekEnd->toDateString(),
            ])
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->orderBy('due_date')
            ->get();

        $nextWeekDays = collect(range(0, 6))->map(fn (int $offset) => $nextWeekStart->copy()->addDays($offset));

        return view('tasks.plan-next-week', [
            'step' => $step,
            'lastWeekStart' => $lastWeekStart,
            'lastWeekEnd' => $lastWeekEnd,
            'lastWeekPlanned' => $lastWeekPlanned,
            'lastWeekCompleted' => $lastWeekCompleted,
            'lastWeekCarryOver' => $lastWeekCarryOver,
            'lastWeekDailyStats' => $lastWeekDailyStats,
            'goals' => $goals,
            'goalSlots' => $goalSlots,
            'nextWeekStart' => $nextWeekStart,
            'nextWeekEnd' => $nextWeekEnd,
            'anchorsEnabled' => $anchorsEnabled,
            'anchorsSchemaReady' => $anchorsSchemaReady,
            'anchors' => $anchors,
            'backlog' => $backlog,
            'nextWeekAssignments' => $nextWeekAssignments,
            'nextWeekDays' => $nextWeekDays,
        ]);
    }

    public function storeGoals(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $validated = $request->validate([
            'goals' => ['array'],
            'goals.*' => ['nullable', 'string', 'max:255'],
        ]);

        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $nextWeekStart = $currentWeekStart->copy()->addWeek();

        $submittedGoals = collect($validated['goals'] ?? [])
            ->map(fn (?string $goal) => is_string($goal) ? trim($goal) : null)
            ->filter(fn (?string $goal) => $goal !== null && $goal !== '')
            ->values();

        WeeklyGoal::query()
            ->where('user_id', $user->id)
            ->whereDate('week_start_date', $nextWeekStart->toDateString())
            ->delete();

        foreach ($submittedGoals as $index => $goalTitle) {
            WeeklyGoal::create([
                'user_id' => $user->id,
                'week_start_date' => $nextWeekStart->toDateString(),
                'position' => $index + 1,
                'title' => $goalTitle,
            ]);
        }

        return Redirect::route('plan-next-week', ['step' => 'anchors'])
            ->with('success', 'Metas para la próxima semana guardadas.');
    }

    public function storeAnchor(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        if (! $this->recurringAnchorService->canUseAnchors() || ! config('planner.anchors.enabled')) {
            return Redirect::route('plan-next-week', ['step' => 'anchors'])
                ->with('error', 'Los anclajes recurrentes no están disponibles.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'day_of_week' => ['required', 'integer', 'between:0,6'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        RecurringAnchor::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'day_of_week' => $validated['day_of_week'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'timezone' => $user->timezone ?? 'UTC',
            'is_active' => true,
        ]);

        return Redirect::route('plan-next-week', ['step' => 'anchors'])
            ->with('success', 'Nuevo ancla semanal creada.');
    }

    public function scheduleTasks(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $validated = $request->validate([
            'assignments' => ['array'],
            'assignments.*.task_id' => ['required', 'integer', 'distinct'],
            'assignments.*.day' => ['nullable', 'integer', 'between:0,6'],
        ]);

        $assignments = collect($validated['assignments'] ?? [])
            ->filter(fn (array $assignment) => Arr::has($assignment, ['task_id', 'day']))
            ->map(fn (array $assignment) => [
                'task_id' => (int) $assignment['task_id'],
                'day' => $assignment['day'] !== null ? (int) $assignment['day'] : null,
            ]);

        if ($assignments->isEmpty()) {
            return Redirect::route('plan-next-week', ['step' => 'summary'])
                ->with('success', 'Sin cambios en la programación.');
        }

        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $nextWeekStart = $currentWeekStart->copy()->addWeek();

        $taskIds = $assignments->pluck('task_id')->all();
        $tasks = Task::query()
            ->where('user_id', $user->id)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->whereIn('id', $taskIds)
            ->get()
            ->keyBy('id');

        foreach ($assignments as $assignment) {
            $task = $tasks->get($assignment['task_id']);

            if (! $task || $task->completed || $task->is_anchor || $assignment['day'] === null) {
                continue;
            }

            $task->due_date = $nextWeekStart->copy()->addDays($assignment['day'])->toDateString();
            $task->stage = Task::STAGE_INBOX;
            $task->save();
        }

        return Redirect::route('plan-next-week', ['step' => 'summary'])
            ->with('success', 'Tareas clave programadas para la próxima semana.');
    }

    public function finalize(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);

        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $nextWeekStart = CarbonImmutable::make($currentWeekStart)->addWeek();
        $nextWeekEnd = $nextWeekStart->endOfWeek(Carbon::SUNDAY);

        $this->recurringAnchorService->materializeWeek(
            $user,
            CarbonPeriod::create($nextWeekStart, '1 day', $nextWeekEnd)
        );

        return Redirect::route('dashboard', ['week' => 1])
            ->with('success', 'Tu próxima semana está lista. ¡Planificación completada!');
    }
}

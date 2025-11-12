<?php

namespace App\Http\Controllers;

use App\Models\AnchorException;
use App\Models\Task;
use App\Models\User;
use App\Services\RecurringAnchorService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    public function __construct(private readonly RecurringAnchorService $recurringAnchorService)
    {
    }

    public function index(Request $request)
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Week navigation (?week=0 this week, -1 prev, +1 next)
        $weekOffset = (int) $request->query('week', 0);
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($weekOffset)->startOfDay();
        $weekEnd = (clone $weekStart)->endOfWeek(Carbon::SUNDAY)->endOfDay();

        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();
        $anchorsEnabled = $anchorsSchemaReady && config('planner.anchors.enabled');

        if ($anchorsEnabled) {
            $this->recurringAnchorService->materializeWeek(
                $user,
                CarbonPeriod::create((clone $weekStart), '1 day', (clone $weekEnd))
            );
        }

        // Mon..Sun collection
        $days = collect();
        for ($i = 0; $i < 7; $i++) {
            $days->push((clone $weekStart)->addDays($i));
        }

        $tasksQuery = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ])
            ->where('completed', false)
            ->where('stage', '!=', Task::STAGE_ARCHIVED);

        if (! $anchorsEnabled && $anchorsSchemaReady) {
            $tasksQuery->where('is_anchor', false);
        }

        $tasks = $tasksQuery
            ->orderBy('due_date')
            ->orderBy('created_at')
            ->get();

        // Group tasks by day key YYYY-MM-DD so Blade can display correctly
        $tasksByDate = $tasks->groupBy(function (Task $t) {
            return optional($t->due_date)->toDateString();
        });

        $backlog = Task::where('user_id', $user->id)
            ->whereNull('due_date')
            ->where('completed', false)
            ->where('stage', Task::STAGE_INBOX)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->latest('created_at')
            ->get();

        return view('dashboard', [
            'tasks' => $tasks,
            'tasksByDate' => $tasksByDate,
            'days' => $days,
            'weekOffset' => $weekOffset,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'backlog' => $backlog,
        ]);
    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $stage = $request->filled('due_date')
            ? Task::STAGE_INBOX
            : Task::STAGE_BACKLOG;

        $user->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->filled('due_date')
                ? Carbon::parse($request->due_date)->toDateString()
                : null,
            'completed' => false,
            'stage' => $stage,
        ]);

        return redirect()->route('dashboard')->with('success', 'Task created successfully!');
    }

    public function toggle(Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);
        abort_if($task->is_anchor, 422, 'Anchors cannot be toggled.');

        $task->completed = ! $task->completed;
        $task->save();

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);

        if (
            $this->recurringAnchorService->canUseAnchors()
            && $task->is_anchor
            && $task->recurring_anchor_id
            && $task->due_date
        ) {
            AnchorException::firstOrCreate(
                [
                    'recurring_anchor_id' => $task->recurring_anchor_id,
                    'anchor_date' => $task->due_date->toDateString(),
                ],
                [
                    'action' => AnchorException::ACTION_SKIP,
                ]
            );
        }

        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }

    public function schedule(Request $request)
    {
        $validated = $request->validate([
            'task_id' => ['required', 'integer', 'exists:tasks,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        /** @var User|null $user */
        $user = Auth::user();
        abort_unless($user, 403);

        $task = Task::where('id', $validated['task_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        abort_if($task->is_anchor, 422, 'Anchors cannot be rescheduled.');

        $task->due_date = $validated['due_date']
            ? Carbon::parse($validated['due_date'])->toDateString()
            : null;
        if ($task->due_date) {
            $task->stage = Task::STAGE_INBOX;
        }
        $task->save();

        if ($request->wantsJson()) {
            $task->refresh();

            return response()->json([
                'status' => 'ok',
                'task' => $task,
            ]);
        }

        return back()->with('success', 'Task rescheduled.');
    }

    public function backlog()
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();

        $tasks = Task::where('user_id', $user->id)
            ->where('completed', false)
            ->where('stage', Task::STAGE_BACKLOG)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->orderByDesc('priority')
            ->orderBy('created_at')
            ->get();

        $startOfWeek = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $scheduleOptions = collect(range(0, 13))->map(function (int $offset) use ($startOfWeek) {
            $date = (clone $startOfWeek)->addDays($offset);

            return [
                'value' => $date->toDateString(),
                'label' => $date->translatedFormat('D d M'),
            ];
        });

        return view('tasks.backlog', [
            'tasks' => $tasks,
            'scheduleOptions' => $scheduleOptions,
        ]);
    }

    public function triageUpdate(Request $request, Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);
        abort_if($task->is_anchor, 422, 'Anchors cannot be triaged.');

        $validated = $request->validate([
            'stage' => ['sometimes', 'string', Rule::in([
                Task::STAGE_BACKLOG,
                Task::STAGE_INBOX,
                Task::STAGE_ARCHIVED,
            ])],
            'priority' => ['sometimes', 'nullable', 'integer', 'between:1,4'],
            'labels' => ['sometimes', 'nullable', 'array'],
            'labels.*' => ['string', 'max:40'],
        ]);

        if (array_key_exists('stage', $validated)) {
            $task->stage = $validated['stage'] ?? Task::STAGE_INBOX;
        }

        if (array_key_exists('priority', $validated)) {
            $task->priority = $validated['priority'];
        }

        if (array_key_exists('labels', $validated)) {
            $task->labels = $validated['labels'];
        }

        $task->save();

        return response()->json([
            'status' => 'ok',
            'task' => $task->fresh(),
        ]);
    }

    public function inbox()
    {
        $user = auth()->user();
        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();

        $unscheduled = Task::where('user_id', $user->id)
            ->whereNull('due_date')
            ->where('completed', false)
            ->where('stage', Task::STAGE_INBOX)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->latest('created_at')
            ->get();

        $scheduledSoon = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        return view('tasks.inbox', [
            'unscheduled' => $unscheduled,
            'scheduledSoon' => $scheduledSoon,
        ]);
    }

    public function today()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();

        $overdue = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->get();

        $todayTasks = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->whereDate('due_date', $today)
            ->orderBy('created_at')
            ->get();

        return view('tasks.today', [
            'overdue' => $overdue,
            'today' => $todayTasks,
        ]);
    }

    public function completed()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();

        $completedTasks = Task::where('user_id', $user->id)
            ->where('completed', true)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->orderByDesc('updated_at')
            ->get();

        $completedGroups = $completedTasks->groupBy(function (Task $task) use ($today) {
            $updatedAt = $task->updated_at ?? $today;
            $startOfWeek = (clone $updatedAt)->startOfWeek(Carbon::MONDAY);
            $endOfWeek = (clone $updatedAt)->endOfWeek(Carbon::SUNDAY);

            return sprintf(
                'Semana del %s al %s',
                $startOfWeek->translatedFormat('d M'),
                $endOfWeek->translatedFormat('d M')
            );
        });

        return view('tasks.completed', [
            'completedGroups' => $completedGroups,
        ]);
    }

    public function weeklyReview(Request $request)
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();

        $weekOffset = (int) $request->query('week', -1);
        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->addWeeks($weekOffset)->startOfDay();
        $weekEnd = (clone $weekStart)->endOfWeek(Carbon::SUNDAY)->endOfDay();
        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();

        $plannedTasks = Task::where('user_id', $user->id)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [
                $weekStart->toDateString(),
                $weekEnd->toDateString(),
            ])
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->orderBy('due_date')
            ->orderBy('created_at')
            ->get();

        $plannedCount = $plannedTasks->count();
        $completedTasks = $plannedTasks->where('completed', true);
        $completedCount = $completedTasks->count();
        $carryOverTasks = $plannedTasks->where('completed', false)->values();
        $carryOverCount = $carryOverTasks->count();
        $completionRate = $plannedCount > 0
            ? round(($completedCount / $plannedCount) * 100, 1)
            : null;

        $dailyStats = collect(range(0, 6))->map(function (int $offset) use ($weekStart, $plannedTasks) {
            $day = (clone $weekStart)->addDays($offset);
            $tasksForDay = $plannedTasks->filter(
                fn (Task $task) => optional($task->due_date)?->isSameDay($day)
            );

            $planned = $tasksForDay->count();
            $completed = $tasksForDay->where('completed', true)->count();
            $carryOver = $tasksForDay->where('completed', false)->count();

            return [
                'date' => $day,
                'planned' => $planned,
                'completed' => $completed,
                'carryOver' => $carryOver,
                'completionRate' => $planned > 0
                    ? round(($completed / $planned) * 100, 1)
                    : null,
            ];
        });

        $completedDuringWeek = Task::where('user_id', $user->id)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->where('completed', true)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->whereBetween('updated_at', [$weekStart, $weekEnd])
            ->orderBy('updated_at')
            ->get();

        $createdDuringWeekCount = Task::where('user_id', $user->id)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->count();

        $canNavigateForward = $weekStart->lt($currentWeekStart);

        return view('tasks.weekly-review', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'weekOffset' => $weekOffset,
            'plannedCount' => $plannedCount,
            'completedCount' => $completedCount,
            'carryOverCount' => $carryOverCount,
            'completionRate' => $completionRate,
            'dailyStats' => $dailyStats,
            'carryOverTasks' => $carryOverTasks,
            'completedDuringWeek' => $completedDuringWeek,
            'createdDuringWeekCount' => $createdDuringWeekCount,
            'canNavigateForward' => $canNavigateForward,
        ]);
    }

    public function all()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $anchorsSchemaReady = $this->recurringAnchorService->canUseAnchors();

        $overdue = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->get();

        $upcoming = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->whereBetween('due_date', [$today, (clone $today)->addDays(7)])
            ->orderBy('due_date')
            ->get();

        $unscheduled = Task::where('user_id', $user->id)
            ->whereNull('due_date')
            ->where('completed', false)
            ->where('stage', Task::STAGE_INBOX)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->latest('created_at')
            ->get();

        $recentlyCompleted = Task::where('user_id', $user->id)
            ->where('completed', true)
            ->where('stage', '!=', Task::STAGE_ARCHIVED)
            ->when($anchorsSchemaReady, fn ($query) => $query->where('is_anchor', false))
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        return view('tasks.overview', [
            'overdue' => $overdue,
            'upcoming' => $upcoming,
            'unscheduled' => $unscheduled,
            'recentlyCompleted' => $recentlyCompleted,
        ]);
    }
}

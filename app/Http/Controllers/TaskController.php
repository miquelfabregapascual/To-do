<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
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

        // Mon..Sun collection
        $days = collect();
        for ($i = 0; $i < 7; $i++) {
            $days->push((clone $weekStart)->addDays($i));
        }

        $tasks = Task::where('user_id', $user->id)
            ->whereDate('due_date', '>=', $weekStart->toDateString())
            ->whereDate('due_date', '<=', $weekEnd->toDateString())
            ->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->get();

        // Group tasks by day key YYYY-MM-DD so Blade can display correctly
        $tasksByDate = $tasks->groupBy(function (Task $t) {
            return optional($t->due_date)->toDateString()
                ?? Carbon::parse($t->created_at)->toDateString();
        });

        return view('dashboard', [
            'tasks' => $tasks,
            'tasksByDate' => $tasksByDate,
            'days' => $days,
            'weekOffset' => $weekOffset,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
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
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        /** @var User $user */
        $user = Auth::user();

        $user->tasks()->create([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => Carbon::parse($request->due_date)->toDateString(),
            'completed' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Task created successfully!');
    }

    public function toggle(Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);

        $task->completed = ! $task->completed;
        $task->save();

        return back()->with('success', 'Task updated.');
    }

    public function destroy(Task $task)
    {
        abort_unless($task->user_id === Auth::id(), 403);

        $task->delete();

        return back()->with('success', 'Task deleted successfully.');
    }

    public function inbox()
    {
        $user = auth()->user();

        $unscheduled = Task::where('user_id', $user->id)
            ->whereNull('due_date')
            ->where('completed', false)
            ->latest('created_at')
            ->get();

        $scheduledSoon = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
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

        $overdue = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->get();

        $todayTasks = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
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

        $completedTasks = Task::where('user_id', $user->id)
            ->where('completed', true)
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

    public function all()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $overdue = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->whereDate('due_date', '<', $today)
            ->orderBy('due_date')
            ->get();

        $upcoming = Task::where('user_id', $user->id)
            ->whereNotNull('due_date')
            ->where('completed', false)
            ->whereBetween('due_date', [$today, (clone $today)->addDays(7)])
            ->orderBy('due_date')
            ->get();

        $unscheduled = Task::where('user_id', $user->id)
            ->whereNull('due_date')
            ->where('completed', false)
            ->latest('created_at')
            ->get();

        $recentlyCompleted = Task::where('user_id', $user->id)
            ->where('completed', true)
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

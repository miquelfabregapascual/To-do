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

        $tasks = Task::where('user_id', $user->id)
            ->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tasks.simple-list', [
            'title' => 'Inbox',
            'tasks' => $tasks,
        ]);
    }

    public function today()
    {
        $user = auth()->user();
        $today = Carbon::today();

        $tasks = Task::where('user_id', $user->id)
            ->whereDate('due_date', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tasks.simple-list', [
            'title' => 'Hoy',
            'tasks' => $tasks,
        ]);
    }

    public function completed()
    {
        $user = auth()->user();

        $tasks = Task::where('user_id', $user->id)
            ->where('completed', true)
            ->orderBy('due_date')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('tasks.simple-list', [
            'title' => 'Completadas',
            'tasks' => $tasks,
        ]);
    }

    public function all()
    {
        $user = auth()->user();

        $tasks = Task::where('user_id', $user->id)
            ->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('tasks.simple-list', [
            'title' => 'Todas',
            'tasks' => $tasks,
        ]);
    }
}

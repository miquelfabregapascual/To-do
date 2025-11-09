<?php

namespace App\Http\Controllers;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    
    public function toggle(Task $task)
{
    // Ensure the task belongs to the logged-in user
    if ($task->user_id !== Auth::id()) {
        abort(403);
    }

    $task->update([
        'completed' => !$task->completed
    ]);

    return redirect()->route('dashboard');
}

}

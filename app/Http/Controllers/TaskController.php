<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
  
    public function index()
{
    /** @var \App\Models\User $user */
    $user = Auth::user();

    $tasks = $user->tasks()->latest()->get();
    return view('dashboard', compact('tasks'));
}
public function create()
{
    return view('tasks.create');
}

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
    ]);

    /** @var \App\Models\User $user */
    $user = Auth::user();

    $user->tasks()->create([
        'title' => $request->title,
    ]);

    return redirect()->route('dashboard')->with('success', 'Task created successfully!');
}


}

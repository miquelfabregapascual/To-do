<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlanNextWeekController;
use App\Http\Controllers\TaskController;

Route::middleware(['auth', 'verified'])->group(function () {
    // Existing weekly view
    Route::get('/dashboard', [TaskController::class, 'index'])->name('dashboard');

    // New simple sections
    Route::get('/inbox',     [TaskController::class, 'inbox'])->name('inbox');
    Route::get('/today',     [TaskController::class, 'today'])->name('today');
    Route::get('/completed', [TaskController::class, 'completed'])->name('completed');
    Route::get('/all',       [TaskController::class, 'all'])->name('all');
    Route::get('/weekly-review', [TaskController::class, 'weeklyReview'])->name('weekly-review');
    Route::get('/plan-next-week', [PlanNextWeekController::class, 'index'])->name('plan-next-week');
    Route::post('/plan-next-week/goals', [PlanNextWeekController::class, 'storeGoals'])->name('plan-next-week.goals');
    Route::post('/plan-next-week/anchors', [PlanNextWeekController::class, 'storeAnchor'])->name('plan-next-week.anchors');
    Route::post('/plan-next-week/schedule', [PlanNextWeekController::class, 'scheduleTasks'])->name('plan-next-week.schedule');
    Route::post('/plan-next-week/finalize', [PlanNextWeekController::class, 'finalize'])->name('plan-next-week.finalize');

    // Optional settings page (simple placeholder view)
    Route::view('/settings', 'settings')->name('settings');

    // Task actions you already have:
    Route::post('/tasks',                [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}/toggle', [TaskController::class, 'toggle'])->name('tasks.toggle');
    Route::delete('/tasks/{task}',       [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::post('/planner/schedule',     [TaskController::class, 'schedule'])->name('planner.schedule');
});

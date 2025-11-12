<?php

namespace App\Services;

use App\Models\AnchorException;
use App\Models\RecurringAnchor;
use App\Models\Task;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class RecurringAnchorService
{
    /**
     * Materialize recurring anchors into planner tasks for a given week.
     *
     * @param User $user
     * @param CarbonPeriod $weekRange Inclusive week range (start/end at midnight)
     * @return Collection<int, Task>
     */
    public function materializeWeek(User $user, CarbonPeriod $weekRange): Collection
    {
        if (! $this->canUseAnchors()) {
            return collect();
        }

        $start = CarbonImmutable::make($weekRange->getStartDate())->startOfDay();
        $end = CarbonImmutable::make($weekRange->getEndDate())->endOfDay();
        $period = CarbonPeriod::create($start, '1 day', $end);

        $datesByWeekday = [];
        foreach ($period as $date) {
            $immutable = CarbonImmutable::make($date)->startOfDay();
            $datesByWeekday[$immutable->dayOfWeek] = $immutable;
        }

        if ($datesByWeekday === []) {
            return collect();
        }

        $anchors = RecurringAnchor::query()
            ->where('user_id', $user->id)
            ->active()
            ->get();

        if ($anchors->isEmpty()) {
            return collect();
        }

        $anchorIds = $anchors->pluck('id');
        $dateRange = [$start->toDateString(), $end->toDateString()];

        $exceptions = AnchorException::query()
            ->whereIn('recurring_anchor_id', $anchorIds)
            ->whereBetween('anchor_date', $dateRange)
            ->get()
            ->keyBy(fn (AnchorException $exception) => $exception->recurring_anchor_id.'|'.$exception->anchor_date->toDateString());

        $existingTasks = Task::query()
            ->where('user_id', $user->id)
            ->whereIn('recurring_anchor_id', $anchorIds)
            ->whereBetween('due_date', $dateRange)
            ->get()
            ->keyBy(fn (Task $task) => $task->recurring_anchor_id.'|'.$task->due_date?->toDateString());

        $materialized = collect();

        foreach ($anchors as $anchor) {
            if (! isset($datesByWeekday[$anchor->day_of_week])) {
                continue;
            }

            $occursOn = $datesByWeekday[$anchor->day_of_week];
            $key = $anchor->id.'|'.$occursOn->toDateString();

            if ($exceptions->has($key)) {
                if ($existingTasks->has($key)) {
                    $existingTasks[$key]->delete();
                    $existingTasks->forget($key);
                }

                continue;
            }

            if ($existingTasks->has($key)) {
                $materialized->push($existingTasks[$key]);
                continue;
            }

            $created = $user->tasks()->create([
                'title' => $anchor->title,
                'description' => $anchor->description,
                'due_date' => $occursOn->toDateString(),
                'completed' => false,
                'is_anchor' => true,
                'recurring_anchor_id' => $anchor->id,
                'anchor_start_time' => optional($anchor->start_time)->format('H:i:s'),
                'anchor_end_time' => optional($anchor->end_time)->format('H:i:s'),
            ]);

            $existingTasks->put($key, $created);
            $materialized->push($created);
        }

        return $materialized;
    }

    public function canUseAnchors(): bool
    {
        static $schemaReady;

        if ($schemaReady !== null) {
            return $schemaReady;
        }

        if (! Schema::hasTable('recurring_anchors') || ! Schema::hasTable('anchor_exceptions')) {
            return $schemaReady = false;
        }

        foreach (['is_anchor', 'recurring_anchor_id', 'anchor_start_time', 'anchor_end_time'] as $column) {
            if (! Schema::hasColumn('tasks', $column)) {
                return $schemaReady = false;
            }
        }

        return $schemaReady = true;
    }
}

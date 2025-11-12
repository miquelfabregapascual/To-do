<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    public const STAGE_BACKLOG = 'backlog';
    public const STAGE_INBOX = 'inbox';
    public const STAGE_ARCHIVED = 'archived';

    public const PRIORITY_P1 = 1;
    public const PRIORITY_P2 = 2;
    public const PRIORITY_P3 = 3;
    public const PRIORITY_P4 = 4;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'due_date',
        'completed',
        'stage',
        'priority',
        'labels',
        'is_anchor',
        'recurring_anchor_id',
        'anchor_start_time',
        'anchor_end_time',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'completed' => false,
        'stage' => self::STAGE_BACKLOG,
        'is_anchor' => false,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed' => 'boolean',
        'due_date' => 'date',
        'priority' => 'integer',
        'is_anchor' => 'boolean',
        'labels' => 'array',
        'anchor_start_time' => 'datetime:H:i:s',
        'anchor_end_time' => 'datetime:H:i:s',
    ];

    /**
     * Get the user that owns the task.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Recurring anchor that produced this task, if any.
     *
     * @return BelongsTo<RecurringAnchor, self>
     */
    public function recurringAnchor(): BelongsTo
    {
        return $this->belongsTo(RecurringAnchor::class);
    }
}

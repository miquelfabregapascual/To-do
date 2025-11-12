<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

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
        'is_anchor' => 'boolean',
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnchorException extends Model
{
    use HasFactory;

    public const ACTION_SKIP = 'skip';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'recurring_anchor_id',
        'anchor_date',
        'action',
        'metadata',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'anchor_date' => 'date',
        'metadata' => 'array',
    ];

    /**
     * @return BelongsTo<RecurringAnchor, self>
     */
    public function recurringAnchor(): BelongsTo
    {
        return $this->belongsTo(RecurringAnchor::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $all)
 */
class TaskReviews extends Model
{
    use HasFactory;

    protected $fillable = [
        "task_id",
        "rating",
        "review",
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Tasks::class);
    }
}
 
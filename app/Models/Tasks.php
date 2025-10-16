<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $all)
 * @method static where(string $string, $date)
 */
class Tasks extends Model
{
    use HasFactory;

    protected $fillable = [
        "task_title",
        "category_id",
        "vendor_id",
        "apartment_id",
        "stage",
        "frequency",
        "picture",
        "time_duration",
        "date_duration",
        "completion_time",
        "status",
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategories::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendors::class);
    }

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartments::class);
    }
}
 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $all)
 * @method static where(string $string, mixed $id)
 */
class Notifications extends Model
{
    use HasFactory;

    protected $fillable = [
      'user_id',
      'notification_detail',
      'system_or_webhook',
      'event',
      'property_id',
      'is_checked'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
 
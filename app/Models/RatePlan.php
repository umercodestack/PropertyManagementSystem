<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, mixed $listing_id)
 */
class RatePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listing_id',
        'ch_rate_plan_id',
        'mr_rate_plan_id',
        'property_id',
        'room_type_id',
        'title',
        'occupancy',
        'is_primary',
        'rate',
    ];
}
 
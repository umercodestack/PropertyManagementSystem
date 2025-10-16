<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, mixed $listing_id)
 */
class RoomType extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listing_id',
        'property_id',
        'ch_room_type_id',
        'mr_room_type_id',
        'title',
        'count_of_rooms',
        'occ_adults',
        'occ_children',
        'occ_infants',
    ];
}
 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calender extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'availability',
        'max_stay',
        'min_stay_through',
        'rate',
        'base_price',
        'is_no_booking_trigger',
        'calender_date',
        'block_reason',
    ];
}

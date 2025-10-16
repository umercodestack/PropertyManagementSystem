<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cleaning extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'booking_id',
        'checkout_date',
        'cleaning_date',
        'key_code',
        'cleaning_time',
        'status',
        'cleaner_id',
        'checkin_time',
        'checkout_time',
        'cleaner_assign_datetime'
    ];
}

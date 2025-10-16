<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'type',
        'reason',
        'sub_reason',
        'message_to_guest',
        'message_to_airbnb',
        'cancel_by'
    ];
}

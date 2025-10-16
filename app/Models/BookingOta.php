<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingOta extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_json',
        'event'
    ];
}
 
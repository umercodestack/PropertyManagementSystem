<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingInquiry extends Model
{
    use HasFactory;
    protected $table = 'booking_inquiry';

    protected $fillable = [
        'ch_inquiry_id',
        'property_id',
        'status',
        'comment',
        'message_thread_id',
        'type',
        'total_price',
        'booking_details',
        'event_type',
    ];
}
 
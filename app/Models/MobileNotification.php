<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MobileNotification extends Model
{
    use HasFactory;
    protected $fillable = [
        'listing_id',
        'booking_id',
        'ota_type',
        'type',
        'review_id',
        'price',
        'notification_label',
        'status',
        'booking_dates',
        'listing_name',
                // 'created_at'

    ];
}

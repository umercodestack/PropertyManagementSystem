<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookingReference extends Model
{
    use HasFactory;

    protected $table = 'booking_references';

    protected $fillable = [
        'booking_id',
        'image',
        'booking_type',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
        'amount_received' => 'decimal:2',
    ];

    /**
     * Relationship with Booking
     */
    public function booking()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }

    /**
     * Relationship with Image (optional, if image table exists)
     */
    public function image()
    {
        return $this->belongsTo(BookingImages::class, 'image_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 * @method static where(string $string, $id)
 */
class Bookings extends Model
{
    use HasFactory;

    protected $fillable = [
        "reservation_code",
        "booking_lead_id",
        "name",
        "surname",
        "email",
        "phone",
        "dob",
        "gender",
        "country",
        "city",
        "rating",
        "purpose_of_call",
        "reason",
        "booking_notes",
        "cnic_passport",
        "adult",
        "children",
        "rooms",
        "custom_discount",
        "payment_method",
        "guest_id",
        "host_id",
        "listing_id",
        "booking_sources",
        "booking_date_start",
        "booking_date_end",
        "service_fee",
        "cleaning_fee",
        "include_cleaning",
        "per_night_price",
        "ota_commission",
        "booking_type",
        "booking_status",
        "created_by",
        "updated_by",
        "total_price",
        "amount_received",
        'forex_adjustement',
        'reference_numbers',
        'payment_status',
        'booking_type',
    ];

    public function apartment()
    {
        return $this->belongsTo(Apartments::class);
    }

    public function payment_reconciliation()
    {
        return $this->hasOne(PaymentReconciliation::class, 'booking_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(BookingImages::class, 'booking_id', 'id');
    }

    public function be_review()
    {
        return $this->hasOne(BeReview::class, 'booking_id', 'id');
    }

    public function references()
    {
        return $this->hasMany(BookingReference::class, 'booking_id', 'id');
    }
    public function guest()
    {
        return $this->belongsTo(\App\Models\Guests::class, 'guest_id');
    }

    public function listing()
    {
        return $this->belongsTo(\App\Models\Listing::class, 'listing_id');
    }

}

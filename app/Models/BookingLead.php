<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingLead extends Model
{
    use HasFactory;
    
    protected $table = 'booking_leads';

    protected $fillable = [
        "invoice_id",
        "payment_status",
        "reservation_code",
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
        "per_night_price",
        "ota_commission",
        "booking_status",
        "created_by",
        "updated_by",
        "total_price",
    ];

}
 
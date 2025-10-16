<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingOtasDetails extends Model
{
    use HasFactory;
    protected $table = 'booking_otas_details';

    protected $fillable = [
        'listing_id',
        'property_id',
        'arrival_date',
        'departure_date',
        'promotion',
        'discount',
        'ota_commission',
        'amount',
        'cleaning_fee',
        'unique_id',
        'booking_id',
        'channel_id',
        'booking_otas_json_details',
        'status',
        'proof_of_payment',
        'system_status',
        'ch_thread_id',
        'is_updated',
        'ota_name',
        'guest_name',
        'guest_phone',
        'guest_email',
        'amount_received',
        'forex_adjustement',
        'reference_numbers',
        'payment_status',
    ];

    public function payment_reconciliation()
    {
        return $this->hasOne(PaymentReconciliation::class, 'ota_booking_id', 'id');
    }
}

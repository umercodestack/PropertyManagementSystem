<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReconciliation extends Model
{
    use HasFactory;

   protected $fillable = [
        'booking_id',
        'ota_booking_id',
        'ibft_screenshot',
        'payment_recevied_ota',
        'payment_received_date',
        'bank_charges',
        'bank_statement',
        'remarks',
    ];
    
    protected $table = "payment_reconciliations";


    public function booking(): BelongsTo
    {
        return $this->belongsTo(Bookings::class, 'id', 'booking_id');
    }
    
    public function ota_booking(): BelongsTo
    {
        return $this->belongsTo(BookingOtasDetails::class, 'id', 'ota_booking_id');
    }

}

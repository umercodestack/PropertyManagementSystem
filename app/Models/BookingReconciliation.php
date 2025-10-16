<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingReconciliation extends Model
{
    protected $table = 'v_booking_reconciliation';
    public $timestamps = false; // View doesn’t manage timestamps
}

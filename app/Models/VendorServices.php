<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create()
 */
class VendorServices extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'service_id',
        'amount',
        'currency',
    ];
}
 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $chargeType)
 */
class HostChargeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_type_id',
        'charge_type',
    ];
}
 
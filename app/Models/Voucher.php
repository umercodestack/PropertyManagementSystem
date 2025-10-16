<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static create(array $all)
 * @method static where(string $string, mixed $email)
 */
class Voucher extends Authenticatable
{
    protected $guarded = [];
    
    // protected $fillable = [
    //     'listing_ids',
    //     'voucher_code',
    //     'discount',
    //     'discount_type',
    //     'max_discount_amount',
    //     'voucher_usage_limit',
    //     'is_enabled',
    //     'created_by'
    // ];
}

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
class VoucherRedeemed extends Authenticatable
{
    protected $table = 'voucher_redeemed';
    
    protected $fillable = [
        'user_id',
        'listing_id',
        'voucher_code',
        'is_redeemed'
    ];
}

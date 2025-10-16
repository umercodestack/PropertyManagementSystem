<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static create(array $all)
 */
class Guests extends Authenticatable
{
    use HasFactory;
    use HasApiTokens;

    protected $fillable = [
        'google_user_id',
        'social_type',
        'name',
        'surname',
        'email',
        'password',
        'guest_type',
        'phone_code',
        'phone',
        'country',
        'city',
        'dp',
    ];
}
 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'rate_plan_id',
        'listing_currency',
        'instant_booking',
        'default_daily_price',
        'guests_included',
        'weekend_price',
        'price_per_extra_person',
        'weekly_price_factor',
        'monthly_price_factor',
        'pass_through_linen_fee',
        'pass_through_security_deposit',
        'pass_through_resort_fee',
        'pass_through_community_fee',
        'pass_through_pet_fee',
        'pass_through_cleaning_fee',
        'pass_through_short_term_cleaning_fee',
        'cleaning_fee',
    ];
}
 
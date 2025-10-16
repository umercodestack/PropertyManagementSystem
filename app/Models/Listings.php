<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listings extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'commission_type',
        'listing_json',
        'status',
        'is_sync',
        'commission_value',
        'google_map',
        'apartment_num',
        'is_churned',
        'exp_managers',
          'cleaning_fee_direct_booking',
        'ota_fee_direct_booking',
        'is_cleaning_fee',
        'cleaning_fee_per_cycle',
        'pre_discount',
        'is_co_host',
        'listing_id',
        'property_about',
        'be_listing_name',
        'bedrooms',
        'beds',
        'bathrooms',
        'city_name',
        'district',
        'street',
        'property_type',
        'is_allow_pets',
        'is_self_check_in',
        'cleaning_fee',
        'discounts',
        'tax',
        'google_map',
        'activation_id',
         'living_room',
        'laundry_area',
        'corridor',
        'outdoor_area',
        'kitchen',
        'is_manual',
        'longitude',
        'latitude',
        'amenities',
        'checkin_time',
        'checkout_time',
        'cancellation_policy',
        'minimum_days_stay',
        'is_long_term'
    ];

    public function listingRelation()
    {
        return $this->hasMany(ListingRelation::class,'listing_id_airbnb');
    }
}

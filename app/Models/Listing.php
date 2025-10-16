<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'listing_id',
        'channel_id',
        'is_sync',
        'listing_json',
        'status',
        'apartment_num',
        'is_churned',
        'exp_managers',
          'cleaning_fee_direct_booking',
        'ota_fee_direct_booking',
        'is_cleaning_fee',
        'cleaning_fee_per_cycle_value',
        'cleaning_fee_per_cycle',
        'mr_ota_price_id',
    ];

    public function setting()
    {
        return $this->hasOne(ListingSetting::class, 'listing_id', 'listing_id');
    }
    
    public function channel()
    {
        return $this->belongsTo(Channels::class);
    }

    public function property()
    {
        return $this->belongsTo(Properties::class, 'property_id');
    }

    public function channexSetting()
    {
        return $this->hasOne(ListingSetting::class, 'listing_id', 'listing_id');
    }

    public function calendars()
    {
        return $this->hasMany(Calender::class, 'listing_id', 'listing_id');
    }

    public function channexProperty()
    {
        return $this->belongsToMany(Properties::class, 'rate_plans', 'listing_id', 'property_id', 'listing_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(Bookings::class, 'listing_id', 'id');
    }

    public function bookingsOTAS()
    {
        return $this->hasMany(BookingOtasDetails::class, 'listing_id', 'listing_id');
    }
    
    public function trigger()
    {
        return $this->hasOne(TriggersPrice::class, 'listing_id', 'listing_id');
    }
    
    public function getListingNameAttribute()
    {
        $detail = json_decode($this->listing_json, true);
        return $detail['title'] ?? null;
    }
    
    public function airbnbImages()
    {
        return $this->hasOne(AirbnbImage::class, 'listing_id', 'listing_id');
    }
    
    public function amenities()
    {
        return $this->hasOne(ListingAmenity::class, 'listing_id', 'listing_id');
    }
}
 
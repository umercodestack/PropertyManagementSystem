<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static create(array $all)
 */
class Apartments extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exp_managers',
        'commission_type',
        'commission_value',
        'apartment_type',
        'rental_type',
        'description',
        'title',
        'apartment_num',
        'is_churned',
        'google_map',
        'district',
        'street',
        'city_name',
        'address_line',
        'longitude',
        'latitude',
        'postal',
        'be_listing_name',
        'property_about',
        'max_guests',
        'bedrooms',
        'beds',
        'bathrooms',
        'amenities',
        'is_allow_pets',
        'is_self_check_in',
        'living_room',
        'laundry_area',
        'corridor',
        'outdoor_area',
        'kitchen',
        'cleaning_fee',
        'discounts',
        'tax',
        'price',
        'any_of_these',
        'unique_attr',
        'js_id',
        'host_type_id',
        'created_by',
        'door_lock',
        'created_at',
        'updated_at',
        'checkin_time',
        'checkout_time',
        'cancellation_policy',
        'minimum_days_stay',
        'is_long_term'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function images(): HasMany
    {
        return $this->hasMany(ApartmentImages::class, 'apartment_id');
    }

    public function address(): HasOne
    {
        return $this->hasOne(ApartmentAddress::class, 'apartment_id');
    }
}
 
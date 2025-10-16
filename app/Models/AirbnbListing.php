<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirbnbListing extends Model
{
    use HasFactory;
    public $guarded = ['id'];

    public function listing()
    {
        return $this->hasOne(Listing::class, 'listing_id', 'listing_id');
    }

    public function images()
    {
        return $this->hasMany(AirbnbImage::class, 'listing_id', 'listing_id');
    }

    public function rooms()
    {
        return $this->hasMany(AirbnbRoom::class, 'listing_id', 'listing_id');
    }

}

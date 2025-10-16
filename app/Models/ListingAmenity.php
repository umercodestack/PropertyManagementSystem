<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingAmenity extends Model
{
    use HasFactory;

    protected $table = "listing_amenities";
    
    protected $fillable = [
        'listing_id',
        'amenities_json'
    ];
}

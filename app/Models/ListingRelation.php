<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingRelation extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id_airbnb',
        'listing_id_other_ota',
        'listing_type',
    ];

    public function listing()
    {
        return $this->belongsTo(Listings::class);
    }
}

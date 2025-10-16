<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListingIcalLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id',
        'url',
        'active',
        'token'
    ];
    protected $casts = [
        'active' => 'boolean',
    ];
    public function listing()
    {
        return $this->belongsTo(Listing::class, 'listing_id');
    }
}

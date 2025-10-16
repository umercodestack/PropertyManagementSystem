<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    use HasFactory;
    
    public $guarded = ['id'];

    public function listing()
    {
        return $this->hasOne(Listing::class, 'listing_id', 'listing_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AirbnbImage extends Model
{
    use HasFactory;

    public $guarded = ['id'];
    
    protected $fillable = [
        'listing_id',
        'url',
        'category'
    ];
}

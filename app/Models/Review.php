<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'uId',
        'booking_id',
        'ota_name',
        'overall_score',
        'review_json',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeReview extends Model
{
    use HasFactory;
    
    protected $table = 'be_reviews';

    protected $fillable = [
        'booking_id',
        'user_id',
        'house_rules_star',
        'house_rules_review',
        'communication_star',
        'communication_review',
        'cleanliness_star',
        'cleanliness_review',
        'review',
        'private_review'
    ];
    
    public function booking()
    {
        return $this->belongsTo(Bookings::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

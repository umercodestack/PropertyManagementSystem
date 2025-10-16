<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Linkrepository extends Model
{
    // use HasFactory;

    protected $table = 'linkrepository';
    
    protected $fillable = [
        'user_id',
        'host_id',
        'listing_id',
        'ota_channel',
        'airbnb',
        'gathern',
        'booking',
        'vrbo',
        'status',
        'created_at',
        'updated_at',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hostdetail(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id', 'id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'id');
    }

    public function userdetail()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

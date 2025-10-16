<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockDateRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'host_id',
        'listing_id',
        'start_date',
        'end_date',
        'availability',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function listing()
    {
        return $this->hasOne(Listing::class, 'id', 'listing_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingShifting extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_id_one',
        'title_one',
        'listing_id_two',
        'title_two',
        'created_by',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

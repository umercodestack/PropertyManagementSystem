<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static where(string $string, $id)
 */
class ApartmentPrices extends Model
{
    use HasFactory;

    protected $fillable = [
        "apartment_id",
        "discount_id",
        "price",
        "start_date",
        "end_date",
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartments::class);
    }
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discounts::class);
    }
}
 
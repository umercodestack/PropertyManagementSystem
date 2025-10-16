<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static create(array $all)
 */
class Vendors extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "location",
        "occupation",
        "availability",
        "last_hired",
        "time_duration",
        "picture",
        "is_active",
        "phone",
        "service_id",
        "country_code",
        "country_short_name"
    ];

    public function vendorServices(): hasOne
    {
        return $this->hasOne(VendorServices::class, 'vendor_id');
    }
    
    public function service(): BelongsTo
    {
        return $this->belongsTo(Services::class, 'service_id');
    }
}
 
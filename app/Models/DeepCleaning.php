<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeepCleaning extends Model
{
    use HasFactory;

    protected $fillable = [
        'listing_title',
        'listing_id',
        'host_id',
        'poc',
        'audit_id',
        'assignToPropertyManager',
        'assignToVendor',
        'start_date',
        'host_name',
        'host_phone',
        'poc_name',
        'end_date',
        'cleaning_date',
        'location',
        'key_code',
        'status',
        'remarks',
        'host_activation_id',
        'type',
        'unit_type',
        'floor',
        'unit_number',
               
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignToPropMan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignToPropertyManager');
    }
    public function assignToVen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignToVendor');
    }

    public function pocUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'poc');
    }
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class, 'listing_id', 'id');
    }
     public function hostactivation(): BelongsTo
    {
        return $this->belongsTo(Hostaboard::class, 'host_activation_id','id');
    }
}

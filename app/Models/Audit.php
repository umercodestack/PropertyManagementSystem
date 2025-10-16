<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    use HasFactory;

   protected $fillable = [
        'listing_title',
        'listing_id',
        'host_id',
        'poc',
        'assignTo',
        'host_name',
        'host_phone',
        'poc_name',
        'start_date',
        'end_date',
        'audit_date',
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

    public function assignToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignTo');
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

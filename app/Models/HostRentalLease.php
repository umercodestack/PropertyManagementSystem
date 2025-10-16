<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostRentalLease extends Model
{
    use HasFactory;

    protected $table = 'host_rental_lease';

    protected $fillable = [
        'hostaboard_id',
        'status',
        'remarks',
        'updated_by',
        'visible'
    ];

    public function hostaboard()
    {
        return $this->belongsTo(Hostaboard::class, 'hostaboard_id');
    }
    
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

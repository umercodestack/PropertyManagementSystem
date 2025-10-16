<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class auditlistingmapping extends Model
{
    use HasFactory;

    protected $table = 'auditlistingmapping';

     protected $fillable = [
    'hostaboard_id',
    'audit_id',
    'auditlisting_id',

    'airbnb',
    'airbnb_status',

    'booking_com',
    'booking_status',

    'vrbo',
    'vrbo_status',

    'al_mosafer',
    'al_mosafer_status',

    'agoda',
    'agoda_status',

    'golden_host',
    'golden_host_status',

    'aqar',
    'aqar_status',

    'bayut',
    'bayut_status',

    'google_hotels',
    'google_hotels_status',

    'gathen',
    'gathen_status',

    'darent',
    'darent_status',

    'status',
    'remarks',
    'updated_by'
    ];




    public function audit()
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function auditlisting()
    {
        return $this->belongsTo(AuditListing::class, 'auditlisting_id');
    }

    public function hostaboard()
    {
        return $this->belongsTo(Hostaboard::class, 'hostaboard_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    } 
}

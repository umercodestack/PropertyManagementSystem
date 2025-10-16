<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotographyStatusLog extends Model
{
    use HasFactory;
    protected $table = 'photography_status_log';

     protected $fillable = [
        'revenue_activation_audit_id',
        'hostaboard_id',
        'user_id',
        'status',
    ];

    public $timestamps = false;

    protected $casts = [
        'timestamp' => 'datetime',
    ];
}

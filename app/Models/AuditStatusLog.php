<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditStatusLog extends Model
{
    use HasFactory;

    protected $table = 'audit_status_log';

    protected $fillable = [
        'audit_id',
        'user_id',
        'status',
    ];

    public $timestamps = false;

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function Audit()
    {
        return $this->belongsTo(Audit::class, 'Audit_id');
    }
}

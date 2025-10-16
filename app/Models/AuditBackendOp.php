<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditBackendOp extends Model
{
    use HasFactory;

    protected $table = 'audit_backend_ops';

    protected $fillable = [
        'hostaboard_id',
        'audit_id',
        'status',
        'remarks',
        'updated_by',
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class, 'audit_id');
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

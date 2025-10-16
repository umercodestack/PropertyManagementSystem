<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesActivationAudit extends Model
{
    use HasFactory;
    protected $table = 'sales_activation_audit';

    protected $fillable = [
        'hostaboard_id',
        'audit_id',
        'status',
        'remarks',
        'is_required',
        'minor_major',
        'amount',
        'task_remarks',
        'task_type',
        'updated_by'
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

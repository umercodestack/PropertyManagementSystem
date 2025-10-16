<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevenueActivationAudit extends Model
{
    use HasFactory;

    protected $table = 'revenue_activation_audit';

    protected $fillable = [
        'hostaboard_id',
        'status',
        'remarks',
        'task_status',
        'task_remarks',
        'url',
        'updated_by',
    ];

    // Relations
    public function hostaboard()
    {
        return $this->belongsTo(Hostaboard::class, 'hostaboard_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

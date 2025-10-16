<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTask extends Model
{
    
    protected $table = 'audit_tasks';

    // Primary key
    protected $primaryKey = 'id';

    // Timestamps
    public $timestamps = true;

    // Fillable fields
    protected $fillable = [
        'audit_id',
        'host_activation_id',
        'audit_checklist_detailed_id',
        'value',
    ];

    // If you're using casts for data types
    protected $casts = [
        'audit_id' => 'integer',
        'host_activation_id' => 'integer',
        'audit_checklist_detailed_id' => 'integer',
        'value' => 'string',
    ];
}

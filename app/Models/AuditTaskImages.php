<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTaskImages extends Model
{
    //use HasFactory;
    protected $table = 'audit_task_images';

    // Primary key (optional if using 'id')
    protected $primaryKey = 'id';

    // Auto-incrementing ID
    public $incrementing = true;

    // Timestamps
    public $timestamps = true;

    // Mass assignable fields
    protected $fillable = [
        'audit_id',
        'host_activation_id',
        'audit_checklist_detailed_id',
        'file_path',
    ];
}

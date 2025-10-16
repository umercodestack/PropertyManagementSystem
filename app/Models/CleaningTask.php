<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningTask extends Model
{
    protected $table = 'cleaning_tasks';
    
    protected $fillable = [
        'property_checklist_id',
        'listing_id',
        'cleaning_id',
        'is_completed',
        'created_at',
        'updated_at'
    ];
}

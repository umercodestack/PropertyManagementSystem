<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CleaningStatusLog extends Model
{
    use HasFactory;

    protected $table = 'cleaning_status_log';

    protected $fillable = [
        'cleaning_id',
        'user_id',
        'status',
    ];

    public $timestamps = false;

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    public function cleaning()
    {
        return $this->belongsTo(Cleaning::class, 'cleaning_id');
    }
}

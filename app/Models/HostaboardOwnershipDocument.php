<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostaboardOwnershipDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'hostaboard_id',
        'document_type',
        'document_path',
    ];

    // Optionally, define the relationship to the Hostaboard model
    public function hostaboard()
    {
        return $this->belongsTo(Hostaboard::class);
    }
}
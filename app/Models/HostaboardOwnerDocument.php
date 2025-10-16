<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HostaboardOwnerDocument extends Model
{
    use HasFactory;

    protected $table = 'hostaboard_owner_documents';

    protected $fillable = [
        'hostaboard_id',
        'document_type',
        'document_path'
    ];

    public function hostaboard()
    {
        return $this->belongsTo(Hostaboard::class, 'hostaboard_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoaDetail extends Model
{
    use HasFactory;
    protected $table = 'soa_details';

    protected $fillable = [
        'soa_id',
        'type',
        'alpa',
        'head_type',
        'comment',
        'value',
    ];
}

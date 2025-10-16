<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class HostType extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_name',
        'description',
        'amount_type',
        'amount',
    ];
}
 
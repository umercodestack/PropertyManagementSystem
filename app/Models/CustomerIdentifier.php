<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class CustomerIdentifier extends Model
{
    use HasFactory;

    protected $fillable = [
        "customer_identifier",
        "status",
    ];

   
}

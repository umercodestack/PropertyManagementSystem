<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, mixed $id)
 */
class ApartmentImages extends Model
{
    use HasFactory;

    protected $fillable = [
        "apartment_id",
        "apartment_image",
    ];
}
 
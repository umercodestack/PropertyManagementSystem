<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, mixed $id)
 */
class ApartmentAddress extends Model
{
    use HasFactory;
    protected $table = 'apartment_address';


    protected $fillable = [
        "apartment_id",
        "latitude",
        "longitude",
        "country",
        "address_line",
        "city",
        "province",
        "postal",
    ];
}
 
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class Discounts extends Model
{
    use HasFactory;

    protected $fillable = [
        "discount_title",
        "discount_type",
        "discount_amount"
    ];
}
 
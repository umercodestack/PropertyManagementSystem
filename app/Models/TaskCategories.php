<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class TaskCategories extends Model
{
    use HasFactory;

    protected $fillable = [
        "category_title",
    ];
}
 
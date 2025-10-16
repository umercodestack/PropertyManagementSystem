<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class ServiceCategories extends Model
{
    use HasFactory;
    protected $table = 'services_categories';

    protected $fillable = [
        'category_name',
        'tags',
    ];
}
 
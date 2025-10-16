<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $all)
 */
class Services extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_category_id',
        'service_name',
        'title',
        'description'
    ];


    /**
     * @return BelongsTo
     */
    public function ServiceCategories(): BelongsTo
    {
        return $this->belongsTo(ServiceCategories::class, 'service_category_id');
    }
}
 
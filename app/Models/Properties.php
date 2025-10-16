<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $data)
 * @method static where(string $string, mixed $id)
 */
class Properties extends Model
{
    use HasFactory;

    protected $fillable = [
      'user_id',
      'title',
      'currency',
      'email',
      'phone',
      'zip_code',
      'country',
      'state',
      'city',
      'address',
      'longitude',
      'latitude',
      'timezone',
      'property_type',
      'group_id',
      'ch_group_id',
      'ch_property_id',
      'mr_property_id'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
 
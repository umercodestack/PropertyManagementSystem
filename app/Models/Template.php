<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    public $guarded = ['id'];

    public function listings()
    {
        return $this->belongsToMany(Listing::class, 'listing_template', 'template_id', 'listing_id', 'id', 'listing_id');
    }

}

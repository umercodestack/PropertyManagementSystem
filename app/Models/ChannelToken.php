<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChannelToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'channel_id',
        'token_json',
    ];
}

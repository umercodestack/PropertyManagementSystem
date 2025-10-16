<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEmegencyContact extends Model
{
    use HasFactory;
    protected $table = 'user_emergency_contact';
    protected $fillable = [
      'user_id',
      'first_name',
      'last_name',
      'country_id',
      'city_id',
      'phone',
      'email',
    ];
}
 
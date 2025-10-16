<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyContact extends Model
{
    protected $table = 'company_contacts';

    protected $fillable = [
        'company_name',
        'name',
        'email_address',
    ];
}

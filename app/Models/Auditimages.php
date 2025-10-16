<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Auditimages extends Model
{
  
    protected $table = 'auditimages';

   
    protected $primaryKey = 'id';

   
    public $incrementing = true;

    
    public $timestamps = true;

  
    protected $fillable = [
        'audit_id',
        'file_path',
    ];
}

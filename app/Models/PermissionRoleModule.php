<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionRoleModule extends Model
{
    use HasFactory;

    protected $table = 'permission_role_module';

    protected $fillable = [
        'user_id',
        'role_id',
        'module_id',
        'access_level'
    ];
}
 
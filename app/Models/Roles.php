<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class Roles extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'role_name',
    ];
    
    
    public function modules()
    {
        return $this->belongsToMany(
            PermissionModule::class,
            'permission_role_module', 
            'role_id',                
            'module_id'
                 
        )
        ->where('permission_module.is_parent', 1)
        ->orderBy('permission_module.position', 'asc');
    }    

    public function permissions()
    {
        return $this->belongsToMany(
            PermissionModule::class,
            'permission_role_module', 
            'role_id',                
            'module_id',
            
              
        )->withPivot('access_level')->orderBy('permission_module.position', 'asc');
    }
    
    public function hasPermission($permission)
    {
        return $this->permissions()->where('module_route', $permission)->exists();
    }
}
 
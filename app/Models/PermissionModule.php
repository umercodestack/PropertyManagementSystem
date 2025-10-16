<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $all)
 */
class PermissionModule extends Model
{
    use HasFactory;
    protected $table = 'permission_module';
    protected $fillable = [
        'permission',
        'module_name',
        'module_icon',
        'module_route',
        'position',
        'parent_module_id',
        'is_for_menu'
    ];

    public function childModules()
    {
        return $this->hasMany(PermissionModule::class, 'parent_module_id');
    }
    public function permissionModules()
    {
        return $this->belongsToMany(Roles::class, 'permission_role_module', 'module_id', 'role_id');
    }
    
    public function childModulesForUser()
    {
        $user = auth()->user();
        
        if ($user->role->role_name == 'Super Admin' || $user->role->role_name == 'Admin') {
            return $this->hasMany(PermissionModule::class, 'parent_module_id');
        }

        return $this->hasMany(PermissionModule::class, 'parent_module_id')
                    ->whereHas('permissionModules', function ($query) use ($user) {
                        $query->where('role_id', $user->role->id);
                    })->where('permission_module.is_for_menu', 1);
    }


}
 
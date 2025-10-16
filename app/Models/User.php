<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @method static create(array $all)
 * @method static where(string $string, mixed $email)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'host_key',
        'fcm_token',
        'parent_user_id',
        'role_id',
        'host_type_id',
        'name',
        'surname',
        'email',
        'email_verification_code',
        'email_verified',
        'phone',
        'phone_verification_code',
        'phone_verified',
        'dob',
        'gender',
        'country',
        'city',
        'password',
        'plan_verified',
        'host_activation_id',
        'is_block',
        'able_to_block_calender',
        'cleaning_per_cycle'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Roles::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategories::class);
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(UserEmegencyContact::class, 'id', 'user_id');
    }


    public function hostType(): BelongsTo
    {
        return $this->belongsTo(HostType::class, 'host_type_id', 'id');
    }


    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channels::class);
    }

    public function modules()
    {
        return $this->role->modules()
            ->where('permission_module.is_parent', 1)
            ->orderBy('permission_module.position', 'asc');
    }

    public function permissions()
    {
        return $this->role->permissions()
            ->orderBy('permission_module.position', 'asc');
    }

    public function hasPermission($permission)
    {
        return $this->permissions()->where('module_route', $permission)->exists();
    }

    public function fcmTokens()
    {
        return $this->hasMany(UserToken::class, 'user_id');
    }
}

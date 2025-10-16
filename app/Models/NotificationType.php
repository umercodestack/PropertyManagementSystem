<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    protected $table = 'notification_types';  // Specify the table name
    protected $primaryKey = 'id';  // Primary key

    protected $fillable = [
        'type_name',
        'description',
        'role_ids',
    ];

    protected $casts = [
        'role_ids' => 'array',  // Cast the role_ids as an array
    ];

    // Define the relationship to notifications
    public function notifications()
    {
        return $this->hasMany(NotificationM::class, 'notification_type_id');
    }
}

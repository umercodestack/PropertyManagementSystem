<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationUserSeen extends Model
{
    protected $table = 'notification_user_seen';  // Specify the table name
    protected $primaryKey = 'id';  // Primary key

    protected $fillable = [
        'notification_id',
        'user_id',
        'seen_at',
    ];

    // Define the relationship to the notification
    public function notification()
    {
        return $this->belongsTo(NotificationM::class, 'notification_id');
    }

    // Define the relationship to the user (assuming you have a 'User' model)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

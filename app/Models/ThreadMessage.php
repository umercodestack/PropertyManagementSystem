<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreadMessage extends Model
{
    use HasFactory;
    protected $table = 'threads_messages';

    protected $fillable = [
        'thread_id',
        'message_uid',
        'sender',
        'message_content',
        'message_date',
        'message_type',
        'created_at',
        'attachment_id',
        'attachment_type',
        'attachment_url',
        'user_id'
    ];
}

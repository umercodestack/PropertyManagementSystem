<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'ch_thread_id',
        'listing_id',
        'name',
        'live_feed_event_id',
        'last_message',
        'thread_type',
        'message_date',
        'booking_info_json',
        'status',
        'thread_type',
        'is_read',
        'is_starred',
        'is_archived',
        'is_mute',
        'action_taken_at',
        'intercom_contact_id',
        'intercom_conversation_id'
    ];
    
    public function listing()
    {
        return $this->hasOne(Listing::class, 'listing_id', 'listing_id');
    }
    
    public function messages()
    {
        return $this->hasMany(ThreadMessage::class, 'thread_id');
    }
    
    public function bookingOtasDetails()
    {
        return $this->hasManyThrough(
            BookingOtasDetails::class, 
            Listing::class,            
            'listing_id',              
            'listing_id',              
            'listing_id',              
            'listing_id'               
        );
    }
}

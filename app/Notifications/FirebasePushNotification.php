<?php

namespace App\Notifications;

use App\Channels\FirebaseChannel;
use Illuminate\Notifications\Notification;

class FirebasePushNotification extends Notification
{
    protected $title;
    protected $body;
    protected $deepLink;

    public function __construct($title, $body, $deepLink = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->deepLink = $deepLink;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Firebase channel ko use karen
        return [FirebaseChannel::class];
    }

    /**
     * Convert the notification to a Firebase-compatible array.
     *
     * @param  mixed  $notifiable
     * @return object
     */
    public function toFirebase($notifiable)
    {
        // if ($this->deepLink) {
        //     $data['deep_link'] = $this->deepLink;
        // }
        
        $data = [
            'title' => $this->title,
            'body' => $this->body,
        ];
        
        $data['deep_link'] = ['item' => 10, 'otaName' => 'livedin'];
        
        return (object) [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $data
        ];
    }
}

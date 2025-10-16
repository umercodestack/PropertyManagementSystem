<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Hostaboard; 
use App\Models\HostRentalLease;

class HostRentalLeaseReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $activation;  
    public $lease;   
 
    public function __construct(Hostaboard $activation, HostRentalLease $lease)
    {
        $this->activation = $activation;  
        $this->lease = $lease;
    }

   
    public function build()
    {
        return $this->subject("Lease Reminder: Your lease is expiring soon")
                    ->view('mail.lease_reminder');
    }

   
}

<?php

namespace App\Mail;

use App\Models\CompanyContact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $contact;

    public function __construct(CompanyContact $contact)
    {
        $this->contact = $contact;
    }

    public function build()
    {
        return $this->subject("You're Invited: Discover Flexible Stays with Livedin!")
                    ->view('mail.company_contact');
    }
}

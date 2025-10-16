<?php

namespace App\Jobs;

use App\Models\CompanyContact;
use App\Mail\CompanyContactMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class SendCompanyContactEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $contact;
    

    public function __construct(CompanyContact $contact)
    {
        $this->contact = $contact;
    }

    public function handle()
    {
        Log::info('Sending email to: ' . $this->contact->email_address);

    try {
        Mail::to($this->contact->email_address)
            ->send(new CompanyContactMail($this->contact));

        Log::info('✅ Email sent to: ' . $this->contact->email_address);
      } catch (\Exception $e) {
        Log::error('❌ Email failed: ' . $e->getMessage());
      }
    }
}


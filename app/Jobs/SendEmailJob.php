<?php

namespace App\Jobs;

use App\Mail\EmailVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;

    /**
     * @param $details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        $data = $this->details;
        $mailData = [
            'title' => 'Mail from LivedIn',
            'body' => route('verfiyEmailAddress', $data['id'])
        ];
        Mail::to($data['email'])->send(new EmailVerification($mailData));
    }
}
 
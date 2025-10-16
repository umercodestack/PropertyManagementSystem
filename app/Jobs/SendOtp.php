<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendOtp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;

    /**
     * Create a new job instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->details;
        $response = Http::post('https://control.msg91.com/api/v5/otp', [
            'template_id' => env('TEMPLATE_ID'),
            'mobile' => $user->phone,
            'authkey' => env('AUTH_KEY'),
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $user->update([
                'phone_verification_code' => $data
            ]);
        } else {
            // Request failed, handle error
            $error = $response->body();
            // Handle error
        }
    }
}
 
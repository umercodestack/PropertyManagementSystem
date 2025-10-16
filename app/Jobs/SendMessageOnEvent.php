<?php

namespace App\Jobs;

use App\Models\Listings;
use App\Models\Thread;
use App\Models\ThreadMessage;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendMessageOnEvent implements ShouldQueue
{
    public $event;
    public $event_data;
    public $booking_id;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct($event, $event_data, $booking_id)
    {
        $this->event = $event;
        $this->event_data = $event_data;
        $this->booking_id = $booking_id;
    }

    public function sendEventMessage($thread, $message)
    {
        // dd($thread,$message);
        if (!empty($message)) {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/message_threads/$thread->ch_thread_id/messages", [
                        "message" => [
                            "message" => $message,
                        ]
                    ]);

            if ($response->successful()) {
                $responseData = $response->json();
                ThreadMessage::create([
                    'thread_id' => $thread->id,
                    'sender' => "property",
                    'message_content' => $message,
                    'message_date' => Carbon::now(),
                ]);
            } else {
                // dd($response->body());
                return response()->json(['error' => $response->body()], 500);
            }
        }
    }
    public function fetchBookingMessage()
    {
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$this->booking_id/messages");

        if ($response->successful()) {
            $responseData = $response->json();
            return $responseData['data'][0]['relationships']['message_thread']['data']['id'];
            // dd($responseData['data'][0]['relationships']['message_thread']['data']['id']);

        } else {
            // dd($response->body());
            return response()->json(['error' => $response->body()], 500);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->event == 'inquiry') {
            $message = "Welcome to LivedIn Apartments!
                        We are grateful for your choice and promise to provide you with the best services. Our goal is to always ensure a comfortable and exceptional stay for you. If you have any inquiries or need assistance, please do not hesitate to contact us.
                        Best regards,
                        LivedIn Team";
            $this->sendEventMessage($this->event_data, $message);

        }
        // dd($this->fetchBookingMessage($event_data['booking_id']));
        if ($this->event == 'booking_new') {
            $booking_json = json_decode($this->event_data['booking_otas_json_details']);
            $raw_message = json_decode($booking_json->attributes->raw_message);
            $listing = Listings::where('listing_id', $this->event_data['listing_id'])->first();
            $listing_json = json_decode($listing->listing_json);
            $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
            $checkin_date = $this->event_data['arrival_date'];
            $departure_date = $this->event_data['departure_date'];
            $price = $this->event_data['amount'];
            $apart_num = $listing->apartment_num;
            $apart_map = $listing->google_map;
            $cust_name = $booking_json->attributes->customer->name . ' ' . $booking_json->attributes->customer->surname;
            // dd($thread_id);
            $thread = Thread::where('ch_thread_id', $this->event_data['thread_id'])->first();
            // dd($thread);
            // dd($event_data,$listing,$raw_message,$cust_name);
            $message = <<<EOD
                Dear $cust_name,  
                Your booking at $listing_name is confirmed from $checkin_date to $departure_date. We're excited to host you and ensure a wonderful stay!  
                Stay Details:  
                Check-In: 4:00 PM, $checkin_date  
                Check-Out: 12:00 PM, $departure_date  
                Total Charges: $price 

                Apartment Number :  $apart_num

                Location: $apart_map

                Apartment Guide Link :   

                For any other requests, feel free to reach us via WhatsApp or call at +966115115798.  
                Looking forward to make your stay exceptional
                EOD;
            // dd( $message);
            // $message = '.';
            $this->sendEventMessage($thread, $message);
        }
    }
}

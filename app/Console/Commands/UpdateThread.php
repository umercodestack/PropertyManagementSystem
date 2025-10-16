<?php

namespace App\Console\Commands;

use App\Jobs\SendMessageOnEvent;
use App\Models\BookingOtasDetails;
use App\Models\NotificationM;
use App\Models\Thread;
use App\Models\ThreadMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class UpdateThread extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updatethread:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Log::channel('thread')->info("Update Start " . now());
        $bookings = BookingOtasDetails::where('status', 'New')
            ->whereNull('ch_thread_id')
            ->orderBy('id', 'desc') // Or 'updated_at' if more appropriate
            ->take(10)
            ->get();
        // $bookings = BookingOtasDetails::where('id', 2735)
        //     ->get();
        // dd($bookings);
        foreach ($bookings as $booking) {
            $ch_thread_id = $this->fetchBookingMessage($booking->booking_id);
            if ($ch_thread_id) {
                $thread = Thread::where('ch_thread_id', $ch_thread_id)->first();
                if ($thread) {

                    $booking->update(['ch_thread_id' => $thread->ch_thread_id]);
                    $values = array(
                        'listing_id' => $booking->listing_id,
                        'property_id' => $booking->property_id,
                        'channel_id' => $booking->channel_id,
                        'arrival_date' => $booking->arrival_date,
                        'departure_date' => $booking->departure_date,
                        'booking_id' => $booking->booking_id,
                        'booking_otas_json_details' => $booking->booking_otas_json_details,
                        'thread_id' => $thread->ch_thread_id,

                    );
                    SendMessageOnEvent::dispatch('booking_new', $values, $booking->booking_id);


                    ThreadMessage::create([
                        'thread_id' => $thread->id,
                        'sender' => 'channel',
                        'message_content' => $thread->name . " has confirmed the booking",
                        'message_date' => now(),
                        'message_type' => 'booking_confirm'
                    ]);

                    $thread->update(['status' => 'booking confirm', 'action_taken_at' => now()]);

                    try {
                        NotificationM::create([
                            'notification_type_id' => 2,
                            'module' => "Chat Module",
                            'module_id' => $thread->id,
                            'title' => "Booking Confirmation",
                            'message' => $thread->name . " has confirmed the booking",
                            'url' => url("/communication-management"),
                            'is_seen_by_all' => false
                        ]);
                    } catch (\Exception $e) {

                    }

                }
            }
        }
        // Log::channel('thread')->info("Update End " . now());

    }

    public function fetchBookingMessage($booking_id)
    {
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id/messages");

        if ($response->successful()) {
            $responseData = $response->json();
            return $responseData['data'][0]['relationships']['message_thread']['data']['id'];
            // dd($responseData['data'][0]['relationships']['message_thread']['data']['id']);

        } else {
            //dd($response->body());
            return response()->json(['error' => $response->body()], 500);
        }
    }
}

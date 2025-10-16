<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\BookingOta;
use App\Models\Notifications;
use App\Models\Thread;
use App\Models\ThreadMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FallbackController extends Controller
{

    public function handle(Request $request)
    {
        date_default_timezone_set('Asia/Baku');

        if ($request->isMethod('post')) {
            $channex_json = $request->getContent();
            $channex_json_decoded = json_decode($channex_json, true);

            if (($channex_json_decoded === null && json_last_error() !== JSON_ERROR_NONE) || !isset($channex_json_decoded["event"])) {
                return response()->json(['error' => 'Unprocessable Entity'], 422);
            }

            // Define the directory path to store webhook events
            $directory = public_path('webhook_events');

            // Create the directory if it doesn't exist
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Generate a unique filename based on timestamp
            $filename = 'event_' . time() . '.json';

            // Save the webhook event data into a file
            file_put_contents($directory . '/' . $filename, $channex_json);

            switch ($channex_json_decoded["event"]) {
                case "booking":
                    $this->logger($channex_json, $channex_json_decoded["event"]);
                    Notifications::create(
                        [
                            'user_id' => 22,
                            'notification_detail' => $channex_json_decoded,
                            'system_or_webhook' => 'webhook',
                            'event' => $channex_json_decoded["event"],
                            'property_id' => $channex_json_decoded['property_id']
                        ]
                    );
                    BookingOta::create(
                        [
                            'booking_json' => $channex_json_decoded,
                            'event' => $channex_json_decoded["event"],
                        ]
                    );
                    break;
                case "booking_new":
//                    $this->logger($channex_json, $channex_json_decoded["event"]);
//                    Notifications::create(
//                        [
//                            'user_id' => 22,
//                            'notification_detail' => json_encode($channex_json_decoded),
//                            'system_or_webhook' => 'webhook',
//                            'event' => $channex_json_decoded["event"],
//                            'property_id' => $channex_json_decoded['property_id']
//                        ]
//                    );

                    $booking_ota = BookingOta::create(
                        [
                            'booking_json' => json_encode($channex_json_decoded),
                            'event' => $channex_json_decoded["event"],
                        ]
                    );
//                    dd($channex_json_decoded['payload']['channel_id']);

                    $booking_id = $channex_json_decoded['payload']['booking_id'];
                    $revision_id = $channex_json_decoded['payload']['booking_revision_id'];


                    $acknoweldge = Http::withHeaders([
                        'user-api-key' =>  env('CHANNEX_API_KEY'),
                    ])->post(env('CHANNEX_URL')."/api/v1/booking_revisions/$revision_id/ack");
                    if ($acknoweldge->successful()) {
                        $acknoweldge = $acknoweldge->json();
//                        return response()->json($acknoweldge);
                    }




                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL')."/api/v1/bookings/$booking_id");

                    if ($response->successful()) {
                        $response = $response->json();
                        $raw_mesasge = json_decode($response['data']['attributes']['raw_message']);
                        $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 12345;
//                        dd($response['data']);
                        $booking_detail_json = json_encode($response['data']);
//                        $listing = $response['data']['listing'];
//                dd($listing['pricing_settings']['default_daily_price']);

                    } else {
                        $error = $response->body();
//                        dd($error);
                    }
                    $property_id = $channex_json_decoded['property_id'];
                    $channel_id = $channex_json_decoded['payload']['channel_id'];

//                    dd($booking_ota->id, $channel_id,$property_id,$listing_id, $booking_detail_json);
                    $values = array(
                        'booking_otas_id' => $booking_ota->id,
                        'listing_id' => $listing_id,
                        'property_id' => $property_id,
                        'channel_id' => $channel_id,
                        'booking_id' => $channex_json_decoded['payload']['booking_id'],
                        'booking_otas_json_details' => $booking_detail_json,
                    );
//                    dd($channex_json_decoded['payload']['booking_revision_id']);

//                    dd($values);
                    $data =  DB::table('booking_otas_details')->insert($values);
                    break;
                case "alteration_request":
                    $this->logger($channex_json, $channex_json_decoded["event"]);
                    Notifications::create(
                        [
                            'user_id' => 22,
                            'notification_detail' => json_encode($channex_json_decoded),
                            'system_or_webhook' => 'webhook',
                            'event' => $channex_json_decoded["event"],
                            'property_id' => $channex_json_decoded['property_id']
                        ]
                    );
                    break;
                case "inquiry":
                    $this->logger($channex_json, $channex_json_decoded["event"]);
//                        dd($channex_json_decoded['payload']['message_thread_id']);
                    $messageThreadId = $channex_json_decoded['payload']['message_thread_id'];
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL')."/api/v1/message_threads/$messageThreadId");
                    if ($response->successful()) {
                        $availability = $response->json();
                        $listing_id = $availability['data']['attributes']['meta']['listing_id'];
                        $name = $availability['data']['attributes']['title'];
                        $last_message = $availability['data']['attributes']['last_message']['message'];
                        $last_message_sender = $availability['data']['attributes']['last_message']['sender'];
                        $last_message_inserted = $availability['data']['attributes']['last_message']['inserted_at'];
//                            dd($listing_id,$name,$last_message,$last_message_sender,$last_message_inserted);
                        $threadInDb = Thread::where('listing_id', $listing_id)->where('name', $name)->first();
//                            dd($threadInDb);
                        if ($threadInDb) {
                            $threadMessage = ThreadMessage::create([
                                'thread_id' => $threadInDb->id,
                                'sender' => $last_message_sender,
                                'message_content' => $last_message,
                                'message_date' => $last_message_inserted,
                            ]);
                        }
                        else {
                            $thread = Thread::create([
                                'listing_id' => $listing_id,
                                'ch_thread_id' => $messageThreadId,
                                'name' => $name,
                                'last_message' => $last_message,
                                'message_date' => $last_message_inserted,
                            ]);

                            $threadMessage = ThreadMessage::create([
                                'thread_id' => $thread->id,
                                'sender' => $last_message_sender,
                                'message_content' => $thread->last_message,
                                'message_date' => $last_message_inserted,
                            ]);
                        }

                    } else {
                        $error = $response->body();
                        dd($error);
                    }
                    break;
                case "booking_unmapped_rate":
                    $this->logger($channex_json, $channex_json_decoded["event"]);
                    Notifications::create(
                        [
                            'user_id' => 22,
                            'notification_detail' => json_encode($channex_json_decoded),
                            'system_or_webhook' => 'webhook',
                            'event' => $channex_json_decoded["event"],
                            'property_id' => $channex_json_decoded['property_id']
                        ]
                    );
                    break;
                case "message":
                    $this->logger($channex_json, $channex_json_decoded["event"]);
                    $this->logger($channex_json, $channex_json_decoded["event"]);
//                        dd($channex_json_decoded['payload']['message_thread_id']);
                    $messageThreadId = $channex_json_decoded['payload']['message_thread_id'];
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL')."/api/v1/message_threads/$messageThreadId");
                    if ($response->successful()) {
                        $availability = $response->json();
                        $listing_id = $availability['data']['attributes']['meta']['listing_id'];
                        $name = $availability['data']['attributes']['title'];
                        $last_message = $availability['data']['attributes']['last_message']['message'];
                        $last_message_sender = $availability['data']['attributes']['last_message']['sender'];
                        $last_message_inserted = $availability['data']['attributes']['last_message']['inserted_at'];
//                            dd($listing_id,$name,$last_message,$last_message_sender,$last_message_inserted);
                        $threadInDb = Thread::where('listing_id', $listing_id)->where('name', $name)->first();
//                            dd($threadInDb);
                        if ($threadInDb) {
                            $threadMessage = ThreadMessage::create([
                                'thread_id' => $threadInDb->id,
                                'sender' => $last_message_sender,
                                'message_content' => $last_message,
                                'message_date' => $last_message_inserted,
                            ]);
                        }
                        else {
                            $thread = Thread::create([
                                'ch_thread_id' => $messageThreadId,
                                'listing_id' => $listing_id,
                                'name' => $name,
                                'last_message' => $last_message,
                                'message_date' => $last_message_inserted,
                            ]);
                            $threadMessage = ThreadMessage::create([
                                'thread_id' => $thread->id,
                                'sender' => $last_message_sender,
                                'message_content' => $thread->last_message,
                                'message_date' => $last_message_inserted,
                            ]);
                        }

                    } else {
                        $error = $response->body();
                    }
                    Notifications::create(
                        [
                            'user_id' => 22,
                            'notification_detail' => json_encode($channex_json_decoded),
                            'system_or_webhook' => 'webhook',
                            'event' => $channex_json_decoded["event"],
                            'property_id' => $channex_json_decoded['property_id']
                        ]
                    );
                    break;
                case "reservation_request":
                    $this->logger($channex_json, $channex_json_decoded["event"]);
                    Notifications::create(
                        [
                            'user_id' => 22,
                            'notification_detail' => json_encode($channex_json_decoded),
                            'system_or_webhook' => 'webhook',
                            'event' => $channex_json_decoded["event"],
                            'property_id' => $channex_json_decoded['property_id']
                        ]
                    );
                    break;
                case "ari":
                    $this->logger($channex_json, $channex_json_decoded["event"]);
//                    $data = [];
////                    dd($channex_json_decoded['payload']);
//                    foreach ($channex_json_decoded['payload'] as $key=>$item) {
////                        dd($item);
//                        $ariArray[$key]['date'] = $item['date'];
//                        $ariArray[$key]['property_id'] = $channex_json_decoded["property_id"];
//                        $ariArray[$key]['room_type_id'] = $item["room_type_id"];
//                        $ariArray[$key]['availability'] = $item["availability"];
////                        $ariArray =
//                    }


                    Notifications::create(
                        [
                            'user_id' => 22,
                            'notification_detail' => json_encode($channex_json_decoded),
                            'system_or_webhook' => 'webhook',
                            'event' => $channex_json_decoded["event"],
                            'property_id' => $channex_json_decoded['property_id']
                        ]
                    );

//                    $response = Http::withHeaders([
//                        'user-api-key' => env('CHANNEX_API_KEY'),
//                    ])->post(env('CHANNEX_URL')."/api/v1/availability", [
////                ])->post(env('CHANNEX_URL')."/api/v1/restrictions", [
//                        "values" =>  $ariArray
//                    ]);
//
//                    if ($response->successful()) {
//                        $calender['response_avail'] = $response->json();
//                    } else {
//                        $error = $response->body();
//                    }
                    break;
                default:
                    $this->logger($channex_json, $channex_json_decoded["event"]);
                    return response()->json(['error' => 'No valid events received'], 422);
            }
        }
    }

    private function logger($payload, $event_type)
    {
        $formatted_time = date('d/m/Y H:i:s', time());
        $data = "-------------------" . PHP_EOL;
        $data .= "EVENT NAME: " . $event_type . PHP_EOL;
        $data .= "RECEIVED AT: " . $formatted_time . PHP_EOL;
        $data .= "CHANNEX PAYLOAD: " . PHP_EOL . PHP_EOL;

        $data .= $payload;

        $data .= PHP_EOL . "-------------------";

        Log::info($data);
    }

}
 
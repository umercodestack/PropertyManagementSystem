<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SendMessageOnEvent;
use App\Models\BookingOta;
use App\Models\BookingOtasDetails;
use App\Models\Calender;
use App\Models\Notifications;
use App\Models\Review;
use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\BookingRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\FirebaseService;
use App\Models\Listing;
use App\Models\User;
use App\Services\StoreProcedureService;
use App\Models\Vendors;
use Twilio\Rest\Client;
use App\Models\MobileNotification;
use App\Models\RoomType;
use App\Models\Properties;
use App\Models\Listings;
use App\Models\ListingRelation;
use App\Models\Channels;
use Illuminate\Support\Str;
use App\Models\NotificationM;
use App\Http\Controllers\Admin\MagaRental\MagaRentalController;

class WebhookController extends Controller
{
    protected $firebaseService;
    protected $storeProcedureService = false;

    public function __construct(FirebaseService $firebaseService, StoreProcedureService $storeProcedureService)
    {
        $this->firebaseService = $firebaseService;
        $this->storeProcedureService = $storeProcedureService;

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->client = new Client($sid, $token);
    }

    public function test()
    {
        echo 'webhook is working';
    }


    public function sendMessage($to, $message, $type)
    {

        $to = $this->formatPhoneNumber($to);

        $from = $type === 'whatsapp'
            ? env('TWILIO_WHATSAPP_FROM')
            : env('TWILIO_PHONE_NUMBER');


        if ($type === 'whatsapp') {
            $to = "whatsapp:$to";
        }


        try {

            return $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatPhoneNumber($to)
    {

        $to = preg_replace('/[\s()-]+/', '', $to);


        if (substr($to, 0, 1) === '0') {
            $to = substr($to, 1);
        }


        $to = '+' . $to;

        return $to;
    }

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

            $MagaRentalController = new MagaRentalController();

            switch ($channex_json_decoded["event"]) {
                case "booking":
                    $booking_id = $channex_json_decoded['payload']['booking_id'];
                    $revision_id = $channex_json_decoded['payload']['booking_revision_id'];
                    $bookingDb = BookingOtasDetails::where('booking_id', $booking_id)->first();
                    if ($bookingDb) {
                        return false;
                    }

                    $acknoweldge = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->post(env('CHANNEX_URL') . "/api/v1/booking_revisions/$revision_id/ack");
                    if ($acknoweldge->successful()) {
                        $acknoweldge = $acknoweldge->json();
                        //                        return response()->json($acknoweldge);
                    }
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id");
                    $arrival_date = $departure_date = '';
                    $listing_id = null;
                    if ($response->successful()) {
                        $response = $response->json();
                        $raw_mesasge = json_decode($response['data']['attributes']['raw_message']);
                        $promotions = 0;
                        $discounts = 0;
                        $cleanings = 0;
                        $unique_id = $response['data']['attributes']['unique_id'];
                        $guest_name = $response['data']['attributes']['customer']['name'] . ' ' . $response['data']['attributes']['customer']['surname'];
                        $guest_phone = $response['data']['attributes']['customer']['phone'];
                        $guest_email = $response['data']['attributes']['customer']['mail'];
                        $ota_name = $response['data']['attributes']['ota_name'];
                        // dd($guest_name, $guest_phone, $guest_email);
                        $ota_commision = isset($response['data']['attributes']['ota_commission']) ? (float) $response['data']['attributes']['ota_commission'] : 0;
                        $amount = isset($response['data']['attributes']['amount']) ? (float) $response['data']['attributes']['amount'] : 0;

                        if (isset($raw_mesasge->reservation->promotion_details) && $raw_mesasge->reservation->promotion_details) {
                            foreach ($raw_mesasge->reservation->promotion_details as $promotion) {
                                $promotions += isset($promotion->amount) ? abs($promotion->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->pricing_rule_details) && $raw_mesasge->reservation->pricing_rule_details) {
                            foreach ($raw_mesasge->reservation->pricing_rule_details as $discount) {
                                $discounts += isset($discount->amount) ? abs($discount->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->standard_fees_details) && $raw_mesasge->reservation->standard_fees_details) {
                            foreach ($raw_mesasge->reservation->standard_fees_details as $cleaning) {
                                $cleanings += isset($cleaning->amount) ? abs($cleaning->amount) : 0;
                            }
                        }
                        $cleaning_short_term = $cleanings;
                        $amount = $amount + $promotions + $discounts + $ota_commision;
                        // dd($amount, $response['data']['attributes'], $ota_commision, $raw_mesasge, $promotions, $discounts, $cleanings);
                        if ($response['data']['attributes']['ota_name'] == 'BookingCom') {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : $response['data']['attributes']['rooms'][0]['meta']['room_type_code'];
                            $bComListing = Listing::where('listing_id', $listing_id)->first();
                            if ($bComListing) {
                                $listingRelation = ListingRelation::where('listing_id_other_ota', $bComListing->id)->first();
                                if ($listingRelation) {
                                    $listingAirbnb = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
                                    if ($listingAirbnb) {
                                        $cleanings = $listingAirbnb->cleaning_fee;
                                        $amount -= $cleanings;
                                    }
                                }
                            }
                        } else {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 0;
                            $listing = Listing::where('listing_id', $listing_id)->first();
                            if ($listing) {
                                $host_cleaning = isset($listing->cleaning_fee) && $listing->cleaning_fee == 0 ? 0 : abs($listing->cleaning_fee - $cleanings);
                                $cleanings = $cleanings + $host_cleaning;
                                $amount = $amount - $cleanings;
                            }
                        }
                        $arrival_date = $response['data']['attributes']['arrival_date'];
                        $departure_date = $response['data']['attributes']['departure_date'];
                        $booking_detail_json = json_encode($response['data']);
                    } else {
                        $error = $response->body();
                    }

                    $property_id = $channex_json_decoded['property_id'];
                    $channel_id = $channex_json_decoded['payload']['channel_id'];

                    //                    dd($booking_ota->id, $channel_id,$property_id,$listing_id, $booking_detail_json);
                    $values = array(
                        'listing_id' => $listing_id,
                        'property_id' => $property_id,
                        'channel_id' => $channel_id,
                        'unique_id' => $unique_id,
                        'arrival_date' => $arrival_date,
                        'departure_date' => $departure_date,
                        'promotion' => $promotions,
                        'discount' => $discounts,
                        'cleaning_fee' => $cleanings,
                        'short_term_cleaning' => $cleaning_short_term,
                        'ota_commission' => $ota_commision,
                        'amount' => $amount,
                        'guest_name' => $guest_name,
                        'guest_phone' => $guest_phone,
                        'guest_email' => $guest_email,
                        'ota_name' => $ota_name,
                        'booking_id' => $channex_json_decoded['payload']['booking_id'],
                        'booking_otas_json_details' => $booking_detail_json,
                        'status' => 'New'
                    );
                    //                    dd($channex_json_decoded['payload']['booking_revision_id']);

                    //                    dd($values);
                    $date = Carbon::parse($departure_date);
                    $previousDay = $date->subDay();
                    $previousDay = $previousDay->toDateString();
                    $listing_id = (int) $listing_id;
                    $affectedRows = Calender::where('listing_id', $listing_id)->whereBetween('calender_date', [$arrival_date, $previousDay])
                        ->update(
                            ['availability' => 0]
                        );
                    DB::table('calenders')
                        ->where('listing_id', $listing_id)
                        ->whereBetween('calender_date', [$arrival_date, $previousDay])
                        ->update(['availability' => 0]);
                    $this->blockAvailability($response['data']['attributes']['ota_name'], $listing_id, $arrival_date, $previousDay, 0);
                    $data = DB::table('booking_otas_details')->insertGetId($values);

                    $ch_thread_id = $this->fetchBookingMessage($booking_id);
                    // dd($ch_thread_id);
                    if ($ch_thread_id) {

                        logger("channex thread id : " . $ch_thread_id);
                        $otaDetail = BookingOtasDetails::where('booking_id', $booking_id)->first();
                        if ($otaDetail) {
                            $otaDetail->update(['ch_thread_id' => $ch_thread_id]);

                            logger("channex thread id update : " . $ch_thread_id);
                        }

                        $thread = Thread::where('ch_thread_id', $ch_thread_id)->first();
                        if ($thread) {

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
                    // dd('asda');
                    // FIREBASE WORK
                    $listing = Listing::where('listing_id', $listing_id)->first();
                    $listing_json = json_decode($listing->listing_json);
                    $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
                    MobileNotification::create([
                        'listing_id' => $listing_id,
                        'booking_id' => $data,
                        'ota_type' => 'ota',
                        'type' => 'booking',
                        'price' => $amount,
                        'notification_label' => $response['data']['attributes']['customer']['name'] . ' ' . $response['data']['attributes']['customer']['surname'] . ' has booked ' . $listing_name,
                        'status' => 'unread',
                        'booking_dates' => $arrival_date . ' to ' . $departure_date,
                        'listing_name' => $listing_name,
                    ]);



                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];

                    $customer_name = $channex_json_decoded['payload']['customer_name'];

                    logger("************************* Booking Notification Start *************************");

                    if (!empty($user_ids_arr)) {

                        $listing_name_arr = !empty($listing_name) ? explode(" ", $listing_name) : "";
                        $list_name_expld = !empty($listing_name_arr[0]) ? $listing_name_arr[0] : "";

                        $deviceToken = '';
                        $title = "Booking Confirmed";
                        $body = "Booking confirmed! " . $customer_name . " is staying at " . $list_name_expld . " from " . Carbon::parse($arrival_date)->format('j M Y') . " to " . Carbon::parse($departure_date)->format('j M Y') . ".";


                        $bodyForLite = "";
                        if (!empty($response['data']['attributes']['occupancy']['adults']) && !empty($response['data']['attributes']['occupancy']['children']) && !empty($response['data']['attributes']['occupancy']['infants'])) {
                            $adults = $response['data']['attributes']['occupancy']['adults'];
                            $children = $response['data']['attributes']['occupancy']['children'];
                            $infants = $response['data']['attributes']['occupancy']['infants'];

                            // Calculate the total number of guests
                            $numberOfGuests = $adults + $children + $infants;
                            $bodyForLite = "Booking Confirmed! " . $customer_name . " has booked your space from " . Carbon::parse($arrival_date)->format('j M Y') . " to " . Carbon::parse($departure_date)->format('j M Y') . " for " . $numberOfGuests . " guests.";
                        }

                        $ntrg_bk_id = !empty($data) ? $data : 0;

                        $gtthrdid = getThreadIDbyBookingOtaFDt($ntrg_bk_id);

                        foreach ($user_ids_arr as $user_id) {

                            $user = User::find($user_id);

                            if (!is_null($user) && !empty($user->fcmTokens)) {

                                if ($user->host_type_id == 2) {
                                    foreach ($user->fcmTokens as $token) {
                                        if (!empty($token->fcm_token)) {
                                            try {

                                                $notificationData = [
                                                    'id' => $ntrg_bk_id,
                                                    'otaName' => 'airbnb',
                                                    'type' => 'booking_detail',
                                                ];

                                                $send = $this->firebaseService->sendPushNotification($token->fcm_token, $title, $body, "BookingTrigger", $notificationData);
                                            } catch (\Exception $ex) {
                                                logger("Notification Error: " . $ex->getMessage());
                                            }
                                        }
                                    }
                                }

                                if (!empty($bodyForLite) && $user->host_type_id == 1) { // for lite user only
                                    sendLiteNotification($user->id, "Instant Book Confirmations", $bodyForLite, $gtthrdid);
                                }
                            }
                        }

                        logger("************************* Booking Notification End *************************");

                        try {
                            $no_bk_calendars = Calender::where(['listing_id' => $listing_id, 'is_no_booking_trigger' => 1])->get();
                            if (!empty($no_bk_calendars)) {

                                logger("************************* No Booking Trigger Called *************************");

                                foreach ($no_bk_calendars as $nbc) {

                                    $nbc->is_no_booking_trigger = 0;
                                    $nbc->rate = $nbc->base_price;
                                    $nbc->base_price = 0;
                                    $nbc->save();

                                }
                            }
                        } catch (\Exception $e) {
                            logger("************************* No Booking Trigger Error *************************");
                        }





                        logger("************************* OTA PROCEDURE REACHED *************************" . $listing_id . $booking_id);
                        try {

                            $procedurparameter = [
                                'p_listing_id' => $listing_id,
                                'P_Booking_Id' => $booking_id
                            ];


                            $result = $this->storeProcedureService
                                ->name('sp_check_triggers_and_bookings_ota_V3')
                                ->InParameters([
                                    'p_listing_id',
                                    'P_Booking_Id'
                                ])
                                ->OutParameters(['return_value', 'return_message', 'return_host_id', 'return_vendor_id'])
                                ->data($procedurparameter)
                                ->execute();

                            $procresponse = $this->storeProcedureService->response();

                            if ($procresponse['response']['return_host_id'] > 0) {
                                $host_id = $procresponse['response']['return_host_id'];
                                $vendor_id = $procresponse['response']['return_vendor_id'];

                                $vendor = Vendors::find($vendor_id);
                                $userDB = User::find($host_id);


                                if (!empty($vendor->phone) && $vendor->phone != '0') {

                                    $vendorPhone = $vendor->country_code . $vendor->phone;
                                    $this->sendMessage($vendorPhone, "New Task Created", "sms");

                                } else {

                                    logger("Message not sent to vendor: Invalid phone number for vendor ID {$vendor->id}");
                                }


                                if (!empty($userDB->phone) && $userDB->phone != '0') {


                                    $this->sendMessage($userDB->phone, "New Task Created", "whatsapp");

                                } else {

                                    logger("Message not sent to user: Invalid phone number for user ID {$vendor->id}");
                                }



                            }

                            logger("************************* OTA Automated Task Executed *************************");

                        } catch (\Exception $e) {

                            logger("************************* OTA Automated Task Error *************************");
                        }


                    }
                    break;
                case "booking_modification":
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
                    $booking_id = $channex_json_decoded['payload']['booking_id'];

                    // dd($booking_id);
                    $booking = BookingOtasDetails::where('booking_id', $booking_id)->first();
                    // dd($old_listing_id,$old_arrival_date, $old_departure_date);

                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id");

                    if ($response->successful()) {
                        $response = $response->json();
                        $raw_mesasge = json_decode($response['data']['attributes']['raw_message']);
                        $promotions = 0;
                        $discounts = 0;
                        $cleanings = 0;
                        $unique_id = $response['data']['attributes']['unique_id'];
                        $guest_name = $response['data']['attributes']['customer']['name'] . ' ' . $response['data']['attributes']['customer']['surname'];
                        $guest_phone = $response['data']['attributes']['customer']['phone'];
                        $guest_email = $response['data']['attributes']['customer']['mail'];
                        $ota_name = $response['data']['attributes']['ota_name'];
                        // dd($guest_name, $guest_phone, $guest_email);
                        $ota_commision = isset($response['data']['attributes']['ota_commission']) ? (float) $response['data']['attributes']['ota_commission'] : 0;
                        $amount = isset($response['data']['attributes']['amount']) ? (float) $response['data']['attributes']['amount'] : 0;
                        if (isset($raw_mesasge->reservation->promotion_details) && $raw_mesasge->reservation->promotion_details) {
                            foreach ($raw_mesasge->reservation->promotion_details as $promotion) {
                                $promotions += isset($promotion->amount) ? abs($promotion->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->pricing_rule_details) && $raw_mesasge->reservation->pricing_rule_details) {
                            foreach ($raw_mesasge->reservation->pricing_rule_details as $discount) {
                                $discounts += isset($discount->amount) ? abs($discount->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->standard_fees_details) && $raw_mesasge->reservation->standard_fees_details) {
                            foreach ($raw_mesasge->reservation->standard_fees_details as $cleaning) {
                                $cleanings += isset($cleaning->amount) ? abs($cleaning->amount) : 0;
                            }
                        }
                        
                        $get_airbnb_listing_id = 0;
                        
                        $cleaning_short_term = $cleanings;
                        $amount = $amount + $promotions + $discounts + $ota_commision;
                        if ($response['data']['attributes']['ota_name'] == 'BookingCom') {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : $response['data']['attributes']['rooms'][0]['meta']['room_type_code'];
                            $bComListing = Listing::where('listing_id', $listing_id)->first();
                            if ($bComListing) {
                                $listingRelation = ListingRelation::where('listing_id_other_ota', $bComListing->id)->first();
                                if ($listingRelation) {
                                    $listingAirbnb = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
                                    if ($listingAirbnb) {
                                        
                                        $get_airbnb_listing_id = $listingAirbnb->listing_id;
                                        
                                        $cleanings = $listingAirbnb->cleaning_fee;
                                        $amount -= $cleanings;
                                    }
                                }
                            }
                        } else {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 0;
                            $listing = Listing::where('listing_id', $listing_id)->first();
                            if ($listing) {
                                $host_cleaning = isset($listing->cleaning_fee) && $listing->cleaning_fee == 0 ? 0 : abs($listing->cleaning_fee - $cleanings);
                                $cleanings = $cleanings + $host_cleaning;
                                $amount = $amount - $cleanings;
                            }
                            
                            $get_airbnb_listing_id = $listing_id;
                        }
                        //                        dd($response['data']);
                        $arrival_date = $response['data']['attributes']['arrival_date'];
                        $departure_date = $response['data']['attributes']['departure_date'];
                        $response['data']['attributes']['raw_message'] = json_encode($raw_mesasge);

                        $booking_detail_json = json_encode($response['data']);
                        // dd($response['data']['attributes']);

                        $data = DB::table('booking_otas_details')->where('booking_id', $booking_id)->update([
                            'arrival_date' => $arrival_date,
                            'departure_date' => $departure_date,
                            'promotion' => $promotions,
                            'discount' => $discounts,
                            'cleaning_fee' => $cleanings,
                            'short_term_cleaning' => $cleaning_short_term,
                            'ota_commission' => $ota_commision,
                            'amount' => $amount,
                            'booking_otas_json_details' => $booking_detail_json,
                            'status' => $response['data']['attributes']['status']
                        ]);
                        $date = Carbon::parse($departure_date);
                        $previousDay = $date->subDay();
                        $previousDay = $previousDay->toDateString();
                        // dd($booking);
                        $old_arrival_date = $booking->arrival_date;
                        $old_departure_date = $booking->departure_date;
                        $old_listing_id = $booking->listing_id;
                        $old_departure = Carbon::parse($old_departure_date);
                        $previousDepartureBooking = $old_departure->subDay();

                        if ($old_listing_id != $listing_id) {

                            Calender::where('listing_id', $old_listing_id)->whereBetween('calender_date', [$old_arrival_date, $previousDepartureBooking->toDateString()])
                                ->update(
                                    ['availability' => 1]
                                );
                                
                            // Almosafer Calendar Update
                            $almsfr_respn = $MagaRentalController->almosafer_block_calendar($get_airbnb_listing_id, $old_arrival_date, $previousDepartureBooking->toDateString(), 1);
                        }

                        if ($previousDepartureBooking->toDateString() != $previousDay || $arrival_date != $old_arrival_date) {

                            Calender::where('listing_id', $old_listing_id)->whereBetween('calender_date', [$old_arrival_date, $previousDepartureBooking->toDateString()])
                                ->update(
                                    ['availability' => 1]
                                );
                                
                            // Almosafer Calendar Update
                            $almsfr_respn = $MagaRentalController->almosafer_block_calendar($get_airbnb_listing_id, $old_arrival_date, $previousDepartureBooking->toDateString(), 1);
                        }
                        Calender::where('listing_id', $listing_id)->whereBetween('calender_date', [$arrival_date, $previousDay])
                            ->update(
                                ['availability' => 0]
                            );
                            
                        // Almosafer Calendar Update
                        $almsfr_respn = $MagaRentalController->almosafer_block_calendar($get_airbnb_listing_id, $arrival_date, $previousDay, 0);


                        $gtthrdid = getThreadIDbyBookingOtaFDt($booking->id);


                        // Send Notification for Booking Notification
                        $this->sendNotification($listing_id, $response['data']['attributes'], "booking_modification", $gtthrdid);

                        //                        $listing = $response['data']['listing'];
//                dd($listing['pricing_settings']['default_daily_price']);

                    } else {
                        $error = $response->body();
                        // dd($error);
                    }

                    break;
                case "booking_new":
                    $booking_id = $channex_json_decoded['payload']['booking_id'];
                    $revision_id = $channex_json_decoded['payload']['booking_revision_id'];
                    $bookingDb = BookingOtasDetails::where('booking_id', $booking_id)->first();
                    if ($bookingDb) {
                        return false;
                    }

                    $get_airbnb_listing_id = 0;

                    $acknoweldge = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->post(env('CHANNEX_URL') . "/api/v1/booking_revisions/$revision_id/ack");
                    if ($acknoweldge->successful()) {
                        $acknoweldge = $acknoweldge->json();
                        //                        return response()->json($acknoweldge);
                    }
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id");
                    $arrival_date = $departure_date = '';
                    $listing_id = null;
                    if ($response->successful()) {
                        $response = $response->json();
                        $raw_mesasge = json_decode($response['data']['attributes']['raw_message']);
                        $promotions = 0;
                        $discounts = 0;
                        $cleanings = 0;
                        $unique_id = $response['data']['attributes']['unique_id'];
                        $guest_name = $response['data']['attributes']['customer']['name'] . ' ' . $response['data']['attributes']['customer']['surname'];
                        $guest_phone = $response['data']['attributes']['customer']['phone'];
                        $guest_email = $response['data']['attributes']['customer']['mail'];
                        $ota_name = $response['data']['attributes']['ota_name'];
                        // dd($guest_name, $guest_phone, $guest_email);
                        $ota_commision = isset($response['data']['attributes']['ota_commission']) ? (float) $response['data']['attributes']['ota_commission'] : 0;
                        $amount = isset($response['data']['attributes']['amount']) ? (float) $response['data']['attributes']['amount'] : 0;

                        if (isset($raw_mesasge->reservation->promotion_details) && $raw_mesasge->reservation->promotion_details) {
                            foreach ($raw_mesasge->reservation->promotion_details as $promotion) {
                                $promotions += isset($promotion->amount) ? abs($promotion->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->pricing_rule_details) && $raw_mesasge->reservation->pricing_rule_details) {
                            foreach ($raw_mesasge->reservation->pricing_rule_details as $discount) {
                                $discounts += isset($discount->amount) ? abs($discount->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->standard_fees_details) && $raw_mesasge->reservation->standard_fees_details) {
                            foreach ($raw_mesasge->reservation->standard_fees_details as $cleaning) {
                                $cleanings += isset($cleaning->amount) ? abs($cleaning->amount) : 0;
                            }
                        }
                      
                        $cleaning_short_term = $cleanings;
                        $amount = $amount + $promotions + $discounts + $ota_commision;

                        // dd($amount, $response['data']['attributes'], $ota_commision, $raw_mesasge, $promotions, $discounts, $cleanings);
                        if ($response['data']['attributes']['ota_name'] == 'BookingCom') {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : $response['data']['attributes']['rooms'][0]['meta']['room_type_code'];
                            $bComListing = Listing::where('listing_id', $listing_id)->first();
                            if ($bComListing) {
                                $listingRelation = ListingRelation::where('listing_id_other_ota', $bComListing->id)->first();
                                if ($listingRelation) {
                                    $listingAirbnb = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
                                    if ($listingAirbnb) {

                                        $get_airbnb_listing_id = $listingAirbnb->listing_id;

                                        $cleanings = $listingAirbnb->cleaning_fee;
                                        $amount -= $cleanings;
                                    }
                                }
                            }
                        } else {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 0;
                            $listing = Listing::where('listing_id', $listing_id)->first();
                            if ($listing) {
                                $host_cleaning = isset($listing->cleaning_fee) && $listing->cleaning_fee == 0 ? 0 : abs($listing->cleaning_fee - $cleanings);
                                $cleanings = $cleanings + $host_cleaning;
                                $amount = $amount - $cleanings;
                            }
                            $get_airbnb_listing_id = $listing_id;
                        }
                        $arrival_date = $response['data']['attributes']['arrival_date'];
                        $departure_date = $response['data']['attributes']['departure_date'];
                        $booking_detail_json = json_encode($response['data']);
                    } else {
                        $error = $response->body();
                    }

                    $property_id = $channex_json_decoded['property_id'];
                    $channel_id = $channex_json_decoded['payload']['channel_id'];

                    //                    dd($booking_ota->id, $channel_id,$property_id,$listing_id, $booking_detail_json);
                    $values = array(
                        'listing_id' => $listing_id,
                        'property_id' => $property_id,
                        'channel_id' => $channel_id,
                        'unique_id' => $unique_id,
                        'arrival_date' => $arrival_date,
                        'departure_date' => $departure_date,
                        'promotion' => $promotions,
                        'discount' => $discounts,
                        'cleaning_fee' => $cleanings,
                        'short_term_cleaning' => $cleaning_short_term,
                        'ota_commission' => $ota_commision,
                        'amount' => $amount,
                        'guest_name' => $guest_name,
                        'guest_phone' => $guest_phone,
                        'guest_email' => $guest_email,
                        'ota_name' => $ota_name,
                        'booking_id' => $channex_json_decoded['payload']['booking_id'],
                        'booking_otas_json_details' => $booking_detail_json,
                        'status' => 'New'
                    );
                    //                    dd($channex_json_decoded['payload']['booking_revision_id']);

                    //                    dd($values);
                    $date = Carbon::parse($departure_date);
                    $previousDay = $date->subDay();
                    $previousDay = $previousDay->toDateString();
                    $listing_id = (int) $listing_id;
                    $affectedRows = Calender::where('listing_id', $listing_id)->whereBetween('calender_date', [$arrival_date, $previousDay])
                        ->update(
                            ['availability' => 0]
                        );
                    DB::table('calenders')
                        ->where('listing_id', $listing_id)
                        ->whereBetween('calender_date', [$arrival_date, $previousDay])
                        ->update(['availability' => 0]);
                    $this->blockAvailability($response['data']['attributes']['ota_name'], $listing_id, $arrival_date, $previousDay, 0);
                    $data = DB::table('booking_otas_details')->insertGetId($values);

                    // Almosafer Calendar Update
                    $almsfr_respn = $MagaRentalController->almosafer_block_calendar($get_airbnb_listing_id, $arrival_date, $previousDay, 0);

                    $ch_thread_id = $this->fetchBookingMessage($booking_id);
                    // dd($ch_thread_id);
                    if ($ch_thread_id) {

                        logger("channex thread id : " . $ch_thread_id);
                        $otaDetail = BookingOtasDetails::where('booking_id', $booking_id)->first();
                        if ($otaDetail) {
                            $otaDetail->update(['ch_thread_id' => $ch_thread_id]);

                            logger("channex thread id update : " . $ch_thread_id);
                        }

                        $thread = Thread::where('ch_thread_id', $ch_thread_id)->first();
                        if ($thread) {

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
                    // dd('asda');
                    // FIREBASE WORK
                    $listing = Listing::where('listing_id', $listing_id)->first();
                    $listing_json = json_decode($listing->listing_json);
                    $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
                    MobileNotification::create([
                        'listing_id' => $listing_id,
                        'booking_id' => $data,
                        'ota_type' => 'ota',
                        'type' => 'booking',
                        'price' => $amount,
                        'notification_label' => $response['data']['attributes']['customer']['name'] . ' ' . $response['data']['attributes']['customer']['surname'] . ' has booked ' . $listing_name,
                        'status' => 'unread',
                        'booking_dates' => $arrival_date . ' to ' . $departure_date,
                        'listing_name' => $listing_name,
                    ]);



                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];

                    $customer_name = $channex_json_decoded['payload']['customer_name'];

                    logger("************************* Booking Notification Start *************************");

                    if (!empty($user_ids_arr)) {

                        $listing_name_arr = !empty($listing_name) ? explode(" ", $listing_name) : "";
                        $list_name_expld = !empty($listing_name_arr[0]) ? $listing_name_arr[0] : "";

                        $deviceToken = '';
                        $title = "Booking Confirmed";
                        $body = "Booking confirmed! " . $customer_name . " is staying at " . $list_name_expld . " from " . Carbon::parse($arrival_date)->format('j M Y') . " to " . Carbon::parse($departure_date)->format('j M Y') . ".";


                        $bodyForLite = "";
                        if (!empty($response['data']['attributes']['occupancy']['adults']) && !empty($response['data']['attributes']['occupancy']['children']) && !empty($response['data']['attributes']['occupancy']['infants'])) {
                            $adults = $response['data']['attributes']['occupancy']['adults'];
                            $children = $response['data']['attributes']['occupancy']['children'];
                            $infants = $response['data']['attributes']['occupancy']['infants'];

                            // Calculate the total number of guests
                            $numberOfGuests = $adults + $children + $infants;
                            $bodyForLite = "Booking Confirmed! " . $customer_name . " has booked your space from " . Carbon::parse($arrival_date)->format('j M Y') . " to " . Carbon::parse($departure_date)->format('j M Y') . " for " . $numberOfGuests . " guests.";
                        }

                        $ntrg_bk_id = !empty($data) ? $data : 0;

                        $gtthrdid = getThreadIDbyBookingOtaFDt($ntrg_bk_id);

                        foreach ($user_ids_arr as $user_id) {

                            $user = User::find($user_id);

                            if (!is_null($user) && !empty($user->fcmTokens)) {

                                if ($user->host_type_id == 2) {
                                    foreach ($user->fcmTokens as $token) {
                                        if (!empty($token->fcm_token)) {
                                            try {

                                                $notificationData = [
                                                    'id' => $ntrg_bk_id,
                                                    'otaName' => 'airbnb',
                                                    'type' => 'booking_detail',
                                                ];

                                                $send = $this->firebaseService->sendPushNotification($token->fcm_token, $title, $body, "BookingTrigger", $notificationData);
                                            } catch (\Exception $ex) {
                                                logger("Notification Error: " . $ex->getMessage());
                                            }
                                        }
                                    }
                                }

                                if (!empty($bodyForLite) && $user->host_type_id == 1) { // for lite user only
                                    sendLiteNotification($user->id, "Instant Book Confirmations", $bodyForLite, $gtthrdid);
                                }
                            }
                        }

                        logger("************************* Booking Notification End *************************");

                        try {
                            $no_bk_calendars = Calender::where(['listing_id' => $listing_id, 'is_no_booking_trigger' => 1])->get();
                            if (!empty($no_bk_calendars)) {

                                logger("************************* No Booking Trigger Called *************************");

                                foreach ($no_bk_calendars as $nbc) {

                                    $nbc->is_no_booking_trigger = 0;
                                    $nbc->rate = $nbc->base_price;
                                    $nbc->base_price = 0;
                                    $nbc->save();

                                }
                            }
                        } catch (\Exception $e) {
                            logger("************************* No Booking Trigger Error *************************");
                        }





                        logger("************************* OTA PROCEDURE REACHED *************************" . $listing_id . $booking_id);
                        try {

                            $procedurparameter = [
                                'p_listing_id' => $listing_id,
                                'P_Booking_Id' => $booking_id
                            ];


                            $result = $this->storeProcedureService
                                ->name('sp_check_triggers_and_bookings_ota_V3')
                                ->InParameters([
                                    'p_listing_id',
                                    'P_Booking_Id'
                                ])
                                ->OutParameters(['return_value', 'return_message', 'return_host_id', 'return_vendor_id'])
                                ->data($procedurparameter)
                                ->execute();

                            $procresponse = $this->storeProcedureService->response();

                            if ($procresponse['response']['return_host_id'] > 0) {
                                $host_id = $procresponse['response']['return_host_id'];
                                $vendor_id = $procresponse['response']['return_vendor_id'];

                                $vendor = Vendors::find($vendor_id);
                                $userDB = User::find($host_id);


                                if (!empty($vendor->phone) && $vendor->phone != '0') {

                                    $vendorPhone = $vendor->country_code . $vendor->phone;
                                    $this->sendMessage($vendorPhone, "New Task Created", "sms");

                                } else {

                                    logger("Message not sent to vendor: Invalid phone number for vendor ID {$vendor->id}");
                                }


                                if (!empty($userDB->phone) && $userDB->phone != '0') {


                                    $this->sendMessage($userDB->phone, "New Task Created", "whatsapp");

                                } else {

                                    logger("Message not sent to user: Invalid phone number for user ID {$vendor->id}");
                                }



                            }

                            logger("************************* OTA Automated Task Executed *************************");

                        } catch (\Exception $e) {

                            logger("************************* OTA Automated Task Error *************************");
                        }


                    }

                    break;
                case "alteration_request":
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
                    $booking_id = $channex_json_decoded['payload']['bms']['booking_id'];

                    $booking = BookingOtasDetails::where('booking_id', $booking_id)->first();

                    $old_arrival_date = $booking->arrival_date;
                    $old_departure_date = $booking->departure_date;
                    $old_listing_id = $booking->listing_id;
                    // dd($old_listing_id,$old_arrival_date, $old_departure_date);


                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id");

                    if ($response->successful()) {
                        $response = $response->json();
                        // dd($response);
                        $raw_mesasge = json_decode($response['data']['attributes']['raw_message']);
                        $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 12345;
                        //                        dd($response['data']);
                        $arrival_date = $response['data']['attributes']['arrival_date'];
                        $departure_date = $response['data']['attributes']['departure_date'];
                        $booking_detail_json = json_encode($response['data']);
                        // dd($response['data']['attributes']);
                        $data = DB::table('booking_otas_details')->where('booking_id', $booking_id)->update([
                            'booking_otas_json_details' => $booking_detail_json,
                            'listing_id' => $listing_id,
                            'arrival_date' => $arrival_date,
                            'departure_date' => $departure_date,
                            'status' => $response['data']['attributes']['status']
                        ]);
                        $old_departure = Carbon::parse($old_departure_date);
                        $previousDepartureBooking = $old_departure->subDay();

                        Calender::where('listing_id', $old_listing_id)->whereBetween('calender_date', [$old_arrival_date, $previousDepartureBooking->toDateString()])
                            ->update(
                                ['availability' => 1]
                            );
                        $date = Carbon::parse($departure_date);
                        $previousDay = $date->subDay();
                        $previousDay = $previousDay->toDateString();
                        Calender::where('listing_id', $listing_id)->whereBetween('calender_date', [$arrival_date, $previousDay])
                            ->update(
                                ['availability' => 0]
                            );
                        //                        $listing = $response['data']['listing'];
//                dd($listing['pricing_settings']['default_daily_price']);

                    } else {
                        $error = $response->body();
                        // dd($error);
                    }
                    break;
                case "booking_cancellation":
                    // $this->logger($channex_json, $channex_json_decoded["event"]);
                    $booking_id = $channex_json_decoded['payload']['booking_id'];

                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id");

                    if ($response->successful()) {
                        $response = $response->json();
                        $raw_mesasge = json_decode($response['data']['attributes']['raw_message']);
                        $promotions = 0;
                        $discounts = 0;
                        $cleanings = 0;
                        $unique_id = $response['data']['attributes']['unique_id'];
                        $guest_name = $response['data']['attributes']['customer']['name'] . ' ' . $response['data']['attributes']['customer']['surname'];
                        $guest_phone = $response['data']['attributes']['customer']['phone'];
                        $guest_email = $response['data']['attributes']['customer']['mail'];
                        $ota_name = $response['data']['attributes']['ota_name'];
                        // dd($guest_name, $guest_phone, $guest_email);
                        $ota_commision = isset($response['data']['attributes']['ota_commission']) ? (float) $response['data']['attributes']['ota_commission'] : 0;
                        $amount = isset($response['data']['attributes']['amount']) ? (float) $response['data']['attributes']['amount'] : 0;
                        if (isset($raw_mesasge->reservation->promotion_details) && $raw_mesasge->reservation->promotion_details) {
                            foreach ($raw_mesasge->reservation->promotion_details as $promotion) {
                                $promotions += isset($promotion->amount) ? abs($promotion->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->pricing_rule_details) && $raw_mesasge->reservation->pricing_rule_details) {
                            foreach ($raw_mesasge->reservation->pricing_rule_details as $discount) {
                                $discounts += isset($discount->amount) ? abs($discount->amount) : 0;
                            }
                        }
                        if (isset($raw_mesasge->reservation->standard_fees_details) && $raw_mesasge->reservation->standard_fees_details) {
                            foreach ($raw_mesasge->reservation->standard_fees_details as $cleaning) {
                                $cleanings += isset($cleaning->amount) ? abs($cleaning->amount) : 0;
                            }
                        }

                        $get_airbnb_listing_id = 0;
                        // dd($amount, $response['data']['attributes'], $ota_commision, $raw_mesasge, $promotions, $discounts, $cleanings);
                        if ($response['data']['attributes']['ota_name'] == 'BookingCom') {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : $response['data']['attributes']['rooms'][0]['meta']['room_type_code'];
                            $bComListing = Listing::where('listing_id', $listing_id)->first();
                            if ($bComListing) {
                                $listingRelation = ListingRelation::where('listing_id_other_ota', $bComListing->id)->first();
                                if ($listingRelation) {
                                    $listingAirbnb = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
                                    if ($listingAirbnb) {

                                        $get_airbnb_listing_id = $listingAirbnb->listing_id;

                                        $cleanings = $listingAirbnb->cleaning_fee;
                                        $amount -= $cleanings;
                                    }
                                }
                            }
                        } else {
                            $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 0;
                            $listing = Listing::where('listing_id', $listing_id)->first();
                            if ($listing) {
                                $host_cleaning = isset($listing->cleaning_fee) && $listing->cleaning_fee == 0 ? 0 : abs($listing->cleaning_fee - $cleanings);
                                $cleanings = $cleanings + $host_cleaning;
                                $amount = $amount - $cleanings;
                            }

                            $get_airbnb_listing_id = $listing_id;
                        }
                        // $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 12345;
                        //                        dd($response['data']);
                        $arrival_date = $response['data']['attributes']['arrival_date'];
                        $departure_date = $response['data']['attributes']['departure_date'];
                        $booking_detail_json = json_encode($response['data']);
                        // dd($response['data']['attributes']);
                        $data = DB::table('booking_otas_details')->where('booking_id', $booking_id)->update([
                            'booking_otas_json_details' => $booking_detail_json,
                            'listing_id' => $listing_id,
                            'arrival_date' => $arrival_date,
                            'departure_date' => $departure_date,
                            'promotion' => $promotions,
                            'discount' => $discounts,
                            'cleaning_fee' => $cleanings,
                            'ota_commission' => $ota_commision,
                            'amount' => $amount,
                            'status' => $response['data']['attributes']['status']
                        ]);

                        $date = Carbon::parse($departure_date);
                        $previousDay = $date->subDay();
                        $previousDay = $previousDay->toDateString();
                        Calender::where('listing_id', $listing_id)->whereBetween('calender_date', [$arrival_date, $previousDay])
                            ->update(
                                ['availability' => 1]
                            );
                        $this->blockAvailability($response['data']['attributes']['ota_name'], $listing_id, $arrival_date, $previousDay, 1);
                        $db_otabooking = DB::table('booking_otas_details')->where('booking_id', $booking_id)->first();
                        $db_listing = DB::table('listings')->where('listing_id', $listing_id)->first();

                        // Almosafer Calendar Update
                        $almsfr_respn = $MagaRentalController->almosafer_block_calendar($get_airbnb_listing_id, $arrival_date, $previousDay, 1);


                        if ($db_otabooking && $db_listing) {

                            logger("************************* OTA TASK DELETE EXECUTION *************************");

                            DB::table('tasks')
                                ->where('listing_id', $db_listing->id)
                                ->where('booking_id', $db_otabooking->id)
                                ->where('booking_Type', 'OTA')
                                ->delete();
                        } else {

                            logger("************************* OTA TASK Record not found for deletion *************************");
                        }

                        //                        $listing = $response['data']['listing'];
                        //                dd($listing['pricing_settings']['default_daily_price']);


                        $gtthrdid = getThreadIDbyBookingOtaFDt($db_otabooking->id);

                        // Send Notification for Booking Cancellation
                        if (!empty($response['data']['attributes']['customer']['name'])) {
                            $this->sendNotification($listing_id, [
                                "guest_name" => $response['data']['attributes']['customer']['name'],
                                "arrival_date" => $arrival_date
                            ], "booking_cancellation", $gtthrdid);
                        }

                    } else {
                        $error = $response->body();
                        // dd($error);
                    }
                    break;
                case "inquiry":
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
                    //                        dd($channex_json_decoded['payload']['message_thread_id']);
                    $messageThreadId = $channex_json_decoded['payload']['message_thread_id'];
                    // dd( $channex_json_decoded['payload']['booking_details']);
                    $live_feed_event_id = $channex_json_decoded['payload']['live_feed_event_id'];
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/message_threads/$messageThreadId");
                    if ($response->successful()) {
                        $availability = $response->json();

                        $provider = $availability['data']['attributes']['provider'];

                        $provider = Str::studly(Str::lower($provider));

                        $listing_id = $availability['data']['attributes']['meta']['listing_id'];
                        $name = $channex_json_decoded['payload']['booking_details']['guest_name'];
                        $last_message = $availability['data']['attributes']['last_message']['message'];
                        $last_message_sender = $availability['data']['attributes']['last_message']['sender'];
                        $last_message_inserted = $availability['data']['attributes']['last_message']['inserted_at'];
                        //                            dd($listing_id,$name,$last_message,$last_message_sender,$last_message_inserted);
                        $threadInDb = Thread::where('listing_id', $listing_id)->where('name', $name)->first();
                        //                            dd($threadInDb);

                        // ThreadMessage::create([
                        //     'thread_id' => $threadInDb->id,
                        //     'sender' => $last_message_sender,
                        //     'message_content' => $last_message,
                        //     'message_date' => $last_message_inserted,
                        // ]);
                        $intercomcontactId = $conversationId = "";


                        try {

                            $apiUrl = "https://api.intercom.io/contacts";
                            $bearerToken = env('INTERCOM_TOKEN');

                            $apiUrlconversation = "https://api.intercom.io/conversations";




                            $timestamp = now()->timestamp;
                            $data = [
                                "role" => "user",
                                "external_id" => $messageThreadId,
                                "email" => "",
                                "phone" => "",
                                'name' => $name . ' (' . $provider . ')',
                                "avatar" => "https://example.org/128Wash.jpg",
                                "last_seen_at" => $timestamp,
                                "signed_up_at" => $timestamp,
                                "owner_id" => 8008643,
                                "unsubscribed_from_emails" => false
                            ];


                            $intercomresponse = Http::withHeaders([
                                "Authorization" => "Bearer $bearerToken",
                                "Accept" => "application/json",
                                "Content-Type" => "application/json"
                            ])->post($apiUrl, $data);



                            $intercomcontactId = $intercomresponse['id'] ?? null;


                            $data = [
                                "from" => [
                                    "type" => "user",
                                    "id" => $intercomcontactId,
                                ],
                                "body" => $last_message
                            ];

                            $intercomResponseconversation = Http::withHeaders([
                                "Authorization" => "Bearer $bearerToken",
                                "Accept" => "application/json",
                                "Content-Type" => "application/json"
                            ])->post($apiUrlconversation, $data);


                            $conversationId = $intercomResponseconversation['conversation_id'] ?? null;

                        } catch (\Exception $ex) {
                            logger("Intercom" . $ex->getMessage());
                        }

                        $thread = Thread::create([
                            'ch_thread_id' => $messageThreadId,
                            'listing_id' => $listing_id,
                            'live_feed_event_id' => $live_feed_event_id,
                            'name' => $name,
                            'last_message' => $last_message,
                            'thread_type' => 'inquiry',
                            'status' => 'inquiry',
                            'booking_info_json' => json_encode($channex_json_decoded['payload']['booking_details']),
                            'message_date' => $last_message_inserted,
                            'intercom_contact_id' => $intercomcontactId,
                            'intercom_conversation_id' => $conversationId
                        ]);
                        $this->sendMessageOnEvent('inquiry', $thread);
                        // dd($thread);

                        try {

                            $listing = Listing::where('listing_id', $listing_id)->first();
                            $listing_json = json_decode($listing->listing_json);
                            $listing_name = !empty($listing_json->title) ? $listing_json->title : '';

                            NotificationM::create([
                                'notification_type_id' => 2,
                                'module' => "Chat Module",
                                'module_id' => $thread->id,
                                'title' => "Inquiry Generated",
                                'message' => $listing_name,
                                'url' => url("/communication-management"),
                                'is_seen_by_all' => false
                            ]);
                        } catch (\Exception $e) {

                        }



                        // $threadMessage = ThreadMessage::create([
                        //     'thread_id' => $thread->id,
                        //     'sender' => $last_message_sender,
                        //     'message_content' => $last_message,
                        //     'message_date' => $last_message_inserted,
                        // ]);

                        $notifyThreadId = !empty($thread->id) ? $thread->id : 0;

                        // Send Notification for Inquiry
                        if (!empty($channex_json_decoded['payload']['booking_details'])) {
                            $this->sendNotification($listing_id, [
                                "guest_name" => $name,
                                "listing_name" => $channex_json_decoded['payload']['booking_details']['listing_name'],
                                "number_of_guests" => $channex_json_decoded['payload']['booking_details']['number_of_guests'],
                                "checkin_date" => $channex_json_decoded['payload']['booking_details']['checkin_date'],
                                "checkout_date" => $channex_json_decoded['payload']['booking_details']['checkout_date']
                            ], "inquiry", $notifyThreadId);
                        }


                    } else {
                        $error = $response->body();
                        dd($error);
                    }
                    break;
                case "booking_unmapped_rate":
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
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
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
                    //                        dd($channex_json_decoded['payload']['message_thread_id']);
                    $messageThreadId = $channex_json_decoded['payload']['message_thread_id'];
                    $live_feed_event_id = $channex_json_decoded['payload']['live_feed_event_id'];
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/message_threads/$messageThreadId");
                    if ($response->successful()) {
                        $availability = $response->json();

                        // dd($channex_json_decoded['payload']['message']);
                        // dd($availability['data']);
                        if (isset($availability['data']['attributes']['provider']) && $availability['data']['attributes']['provider'] == 'BookingCom') {
                            $channel_id = $availability['data']['relationships']['channel']['data']['id'];
                            $channel = Channels::where('ch_channel_id', $channel_id)->first();
                            $listing = Listing::where('channel_id', $channel->id)->first();
                            $listing_id = $listing->listing_id;

                            // dd($channel_id,$channel, $listing);
                        } else {
                            $listing_id = $availability['data']['attributes']['meta']['listing_id'];
                        }
                        $provider = $availability['data']['attributes']['provider'];
                        $provider = Str::studly(Str::lower($provider));
                        $name = $availability['data']['attributes']['title'];
                        $last_message = $channex_json_decoded['payload']['message'];
                        $last_message_sender = $availability['data']['attributes']['last_message']['sender'];
                        $last_message_inserted = $availability['data']['attributes']['last_message']['inserted_at'];
                        //                            dd($listing_id,$name,$last_message,$last_message_sender,$last_message_inserted);


                        // Get Attachment Work
                        $attachment_type = "";
                        $have_attachment = !empty($channex_json_decoded['payload']['have_attachment']) ? true : false;
                        if ($have_attachment && !empty($channex_json_decoded['payload']['attachments'][0])) {
                            $get_attachment_url = $channex_json_decoded['payload']['attachments'][0];

                            $gaurl = explode('.', $get_attachment_url);
                            $extension = !empty($gaurl[count($gaurl) - 1]) ? $gaurl[count($gaurl) - 1] : '';

                            $attachment_type = 'image/' . $extension;

                            $get_attachment_url = "http://app.channex.io/api/v1/" . $get_attachment_url;

                            $last_message = $get_attachment_url;
                        }

                        $notifyThreadId = 0;

                        $threadInDb = Thread::where('ch_thread_id', $messageThreadId)->first();
                        if ($threadInDb) {

                            $notifyThreadId = $threadInDb->id;

                            $thread_msg_arr = [
                                'thread_id' => $threadInDb->id,
                                'message_uid' => $channex_json_decoded['payload']['id'],
                                'sender' => $last_message_sender,
                                'message_content' => $last_message,
                                'message_date' => $last_message_inserted,
                            ];


                            if (!empty($attachment_type) && !is_null($last_message)) {
                                $thread_msg_arr['message_type'] = 'attachment';
                                $thread_msg_arr['attachment_type'] = $attachment_type;
                                $thread_msg_arr['attachment_url'] = $get_attachment_url;
                            }

                            ThreadMessage::create($thread_msg_arr);
                            $threadInDb->unread_count = ($threadInDb->unread_count ?? 0) + 1;
                            $threadInDb->is_read = 0;
                            $threadInDb->save();

                            $conversationId = $threadInDb->intercom_conversation_id;
                            $intercomcontactId = $threadInDb->intercom_contact_id;


                            $apiUrl = "https://api.intercom.io/conversations/{$conversationId}/reply";
                            $bearerToken = env('INTERCOM_TOKEN');

                            $data = [
                                "message_type" => "comment",
                                "type" => "user",
                                "intercom_user_id" => $intercomcontactId,
                                "body" => !empty($last_message) ? $last_message : ""
                            ];

                            if ($last_message_sender == 'property') {

                                $data = [
                                    "message_type" => "note",
                                    "type" => "admin",
                                    "admin_id" => '8008643',
                                    "body" => !empty($last_message) ? $last_message : ""
                                ];
                            }


                            $intercom_response = Http::withHeaders([
                                "Authorization" => "Bearer $bearerToken",
                                "Accept" => "application/json",
                                "Content-Type" => "application/json"
                            ])->post($apiUrl, $data);

                            try {
                                NotificationM::create([
                                    'notification_type_id' => 2,
                                    'module' => "Chat Module",
                                    'module_id' => $threadInDb->id,
                                    'title' => "New Message",
                                    'message' => Str::limit($last_message, 100),
                                    'url' => url("/communication-management"),
                                    'is_seen_by_all' => false
                                ]);
                            } catch (\Exception $e) {

                            }

                        } else {

                            $intercomcontactId = $conversationId = "";

                            try {

                                $apiUrl = "https://api.intercom.io/contacts";
                                $bearerToken = env('INTERCOM_TOKEN');

                                $apiUrlconversation = "https://api.intercom.io/conversations";


                                $timestamp = now()->timestamp;

                                $data = [
                                    "role" => "user",
                                    "external_id" => $messageThreadId,
                                    "email" => "",
                                    "phone" => "",
                                    'name' => $name . ' (' . $provider . ')',
                                    "avatar" => "https://example.org/128Wash.jpg",
                                    "last_seen_at" => $timestamp,
                                    "signed_up_at" => $timestamp,
                                    "owner_id" => 8008643,
                                    "unsubscribed_from_emails" => false
                                ];


                                $intercomresponse = Http::withHeaders([
                                    "Authorization" => "Bearer $bearerToken",
                                    "Accept" => "application/json",
                                    "Content-Type" => "application/json"
                                ])->post($apiUrl, $data);



                                $intercomcontactId = $intercomresponse['id'] ?? null;


                                $data = [
                                    "from" => [
                                        "type" => "user",
                                        "id" => $intercomcontactId,
                                    ],
                                    "body" => !empty($last_message) ? $last_message : ""
                                ];

                                $intercomResponseconversation = Http::withHeaders([
                                    "Authorization" => "Bearer $bearerToken",
                                    "Accept" => "application/json",
                                    "Content-Type" => "application/json"
                                ])->post($apiUrlconversation, $data);


                                $conversationId = $intercomResponseconversation['conversation_id'] ?? null;
                            } catch (\Exception $ex) {
                                logger("Intercom" . $ex->getMessage());
                            }

                            $thread = Thread::create([
                                'ch_thread_id' => $messageThreadId,
                                'listing_id' => $listing_id,
                                'live_feed_event_id' => $live_feed_event_id,
                                'name' => $name,
                                'last_message' => $last_message,
                                'thread_type' => 'message',
                                'status' => 'inquiry',
                                'message_date' => $last_message_inserted,
                                'unread_count' => 1,
                                'is_read' => 0,
                                'intercom_contact_id' => $intercomcontactId,
                                'intercom_conversation_id' => $conversationId
                            ]);

                            $notifyThreadId = $thread->id;

                            $thread_arr = [
                                'thread_id' => $thread->id,
                                'message_uid' => $channex_json_decoded['payload']['id'],
                                'sender' => $last_message_sender,
                                'message_content' => $last_message,
                                'message_date' => $last_message_inserted,
                            ];

                            if (!empty($attachment_type) && !is_null($last_message)) {
                                $thread_arr['message_type'] = 'attachment';
                                $thread_arr['attachment_type'] = $attachment_type;
                                $thread_arr['attachment_url'] = $get_attachment_url;
                            }

                            ThreadMessage::create($thread_arr);
                        }
                        //                            dd($threadInDb);
                        //                            dd($threadInDb);
                        // dd($channex_json_decoded['payload']['message);


                        // Send Notification for New Message

                        if ($last_message_sender == "guest") {
                            $this->sendNotification($listing_id, [
                                "guest_name" => $name, //$last_message_sender == "guest" ? $name : "Property",
                                "last_message" => $last_message,
                            ], "new_message", $notifyThreadId);
                        }

                    } else {
                        $error = $response->body();
                    }


                    // Notifications::create(
                    //     [
                    //         'user_id' => 22,
                    //         'notification_detail' => json_encode($channex_json_decoded),
                    //         'system_or_webhook' => 'webhook',
                    //         'event' => $channex_json_decoded["event"],
                    //         'property_id' => $channex_json_decoded['property_id']
                    //     ]
                    // );





                    break;
                case "reservation_request":
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
                    // Notifications::create(
                    //     [
                    //         'user_id' => 22,
                    //         'notification_detail' => json_encode($channex_json_decoded),
                    //         'system_or_webhook' => 'webhook',
                    //         'event' => $channex_json_decoded["event"],
                    //         'property_id' => $channex_json_decoded['property_id']
                    //     ]
                    // );

                    $data = $channex_json_decoded;
                    $bookingRequest = BookingRequest::where('live_feed_event_id', $data['payload']['live_feed_event_id'] ?? null)->first();

                    if (!$bookingRequest) {
                        $bookingRequest = BookingRequest::create([
                            "listing_id" => $data['payload']['bms']['meta']['listing_id'] ?? null,
                            "message_thread_id" => $data['payload']['message_thread_id'] ?? null,
                            "live_feed_event_id" => $data['payload']['live_feed_event_id'] ?? null,
                            "amount" => $data['payload']['bms']['amount'] ?? null,
                            "is_guest_verified" => $data['payload']['bms']['raw_message']['reservation']['is_guest_verified'] ?? null,
                            "guest_name" => $data['payload']['bms']['customer']['name'] ?? null,
                            "check_in_datetime" => $data['payload']['bms']['raw_message']['reservation']['check_in_datetime'] ?? null,
                            "check_out_datetime" => $data['payload']['bms']['raw_message']['reservation']['check_out_datetime'] ?? null,
                            "status" => 'pending',
                            "booking_json" => json_encode($data) ?? null,
                        ]);
                    }
                    $thread = Thread::where('live_feed_event_id', $bookingRequest->live_feed_event_id)->first();
                    if ($thread) {
                        $threadMessage = ThreadMessage::create([
                            'thread_id' => $thread->id,
                            'sender' => 'channel',
                            'message_content' => $thread->name . " requested for booking",
                            'message_date' => Carbon::now()->subHour(),
                            'message_type' => 'booking_request'
                        ]);
                    } else {
                        $thread = Thread::create([
                            'ch_thread_id' => $data['payload']['message_thread_id'] ?? null,
                            'listing_id' => $data['payload']['bms']['meta']['listing_id'] ?? null,
                            'live_feed_event_id' => $data['payload']['live_feed_event_id'],
                            'name' => $data['payload']['bms']['customer']['name'] ?? null,
                            'last_message' => $data['payload']['bms']['customer']['name'] . " requested for booking",
                            'message_date' => Carbon::now()->subHour(),
                        ]);
                        $threadMessage = ThreadMessage::create([
                            'thread_id' => $thread->id,
                            'sender' => 'channel',
                            'message_content' => $thread->name . " requested for booking",
                            'message_date' => Carbon::now()->subHour(),
                            'message_type' => 'booking_request'
                        ]);
                    }

                    try {
                        $this->sendMessageOnEvent('inquiry', $thread);
                    } catch (\Exception $e) {

                    }


                    try {
                        NotificationM::create([
                            'notification_type_id' => 2,
                            'module' => "Chat Module",
                            'module_id' => $thread->id,
                            'title' => "Reservation Request",
                            'message' => $thread->name . " requested for booking",
                            'url' => url("/communication-management"),
                            'is_seen_by_all' => false
                        ]);

                        Thread::where('id', $thread->id)->update([
                            'status' => 'requested for booking',
                            'action_taken_at' => now()
                        ]);

                    } catch (\Exception $e) {

                    }

                    $notifyThreadId = !empty($thread->id) ? $thread->id : 0;

                    // Send Notification for Booking Request
                    if (!empty($data['payload']['bms']['meta']['listing_id'])) {
                        $this->sendNotification($data['payload']['bms']['meta']['listing_id'], $data, "reservation_request", $notifyThreadId);
                    }

                    break;
                case "review":
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
                    $review_id = $channex_json_decoded['payload']['id'];
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->get(env('CHANNEX_URL') . "/api/v1/reviews/$review_id");
                    if ($response->successful()) {
                        $data = $response->json();

                        logger("Review Response: " . json_encode($data));

                        // dd($data['data']['attributes']['ota']);
                        $rev = Review::create([
                            'uId' => $data['data']['attributes']['id'],
                            'booking_id' => $data['data']['relationships']['booking']['data']['id'],
                            'ota_name' => $data['data']['attributes']['ota'],
                            'overall_score' => $data['data']['attributes']['overall_score'],
                            'review_json' => json_encode($data['data']['attributes']),
                        ]);

                        // FIREBASE WORK
                        $listing_id = !empty($data['data']['attributes']['meta']['listing_id']) ? $data['data']['attributes']['meta']['listing_id'] : '';
                        $star = $data['data']['attributes']['overall_score'] ?? 0;
                        $customer_name = $data['data']['attributes']['guest_name'] ?? '';
                        if (!empty($listing_id)) {
                            $listing = Listing::where('listing_id', $listing_id)->first();
                            $listing_json = json_decode($listing->listing_json);
                            $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
                            if (round($star / 2) > 3) {
                                MobileNotification::create([
                                    'listing_id' => $listing_id,
                                    'booking_id' => null,
                                    'ota_type' => 'ota',
                                    'type' => 'review',
                                    'review_id' => $rev->id,
                                    'price' => null,
                                    'notification_label' => $customer_name . ' left ' . round($star / 2) . ' star review',
                                    'status' => 'unread',
                                    'booking_dates' => null,
                                    'listing_name' => $listing_name,
                                ]);
                            }

                        }
                        logger("************************* Review Notification Start *************************");

                        if (!empty($listing_id) && !empty($star) && !empty($customer_name)) {
                            $listing = Listing::where('listing_id', $listing_id)->first();
                            $listing_json = json_decode($listing->listing_json);
                            $listing_name = !empty($listing_json->title) ? $listing_json->title : '';

                            $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];

                            if (!empty($user_ids_arr)) {

                                $listing_name_arr = !empty($listing_name) ? explode(" ", $listing_name) : "";
                                $list_name_expld = !empty($listing_name_arr[0]) ? $listing_name_arr[0] : "";

                                $star = $star / 2;

                                $deviceToken = '';
                                $title = "New Review Received";
                                $body = "New review! $customer_name has left a $star star review for their stay at $list_name_expld. Check it out!";

                                try {
                                    logger("Notification Body: " . json_encode($body));

                                    foreach ($user_ids_arr as $user_id) {

                                        $user = User::find($user_id);

                                        if (!is_null($user) && !empty($user->fcmTokens) && $user->host_type_id == 2) { // for pro user only
                                            foreach ($user->fcmTokens as $token) {
                                                if (!empty($token->fcm_token)) {
                                                    try {

                                                        $notificationData = [
                                                            'id' => $rev->id,
                                                            'otaName' => '',
                                                            'type' => 'review_recieved',
                                                        ];

                                                        $send = $this->firebaseService->sendPushNotification($token->fcm_token, $title, $body, "ReviewTrigger", $notificationData);

                                                        logger("Notification Response: " . json_encode($send));
                                                    } catch (\Exception $ex) {
                                                        logger("Notification Error: " . $ex->getMessage());
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } catch (\Exception $ex) {
                                    logger("Notification Error: " . $ex->getMessage());
                                }
                            }
                        }

                        logger("************************* Review Notification End *************************");

                    } else {
                        $error = $response->body();
                    }

                    break;
                case "ari":
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
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


                    //                    Notifications::create(
//                        [
//                            'user_id' => 22,
//                            'notification_detail' => json_encode($channex_json_decoded),
//                            'system_or_webhook' => 'webhook',
//                            'event' => $channex_json_decoded["event"],
//                            'property_id' => $channex_json_decoded['property_id']
//                        ]
//                    );

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
                    // // $this->logger($channex_json, $channex_json_decoded["event"]);
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


    private function sendNotification($listingId, $data, $case, $thread_id = 0)
    {
        if (!empty($data)) {
            $listing = Listing::where('listing_id', $listingId)->first();
            $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
            if ($case == 'reservation_request') {
                $guestName = $data['payload']['bms']['guests'][0]['name'] ?? 'Guest';
                $listingName = $data['payload']['raw_message']['reservation']['listing_id'] ?? 'a listing';
                $checkinDate = $data['payload']['bms']['checkin_date'] ?? 'unknown date';
                $checkoutDate = $data['payload']['bms']['checkout_date'] ?? 'unknown date';
                $numberOfGuests = $data['payload']['bms']['occ_adults'] ?? 0;
                $numberOfChildren = $data['payload']['bms']['occ_children'] ?? 0;
                $numberOfInfants = $data['payload']['bms']['occ_infants'] ?? 0;

                $totalGuests = $numberOfGuests + $numberOfChildren + $numberOfInfants;

                $chckindt = $checkinDate != 'unknown date' ? date('d-m-Y', strtotime($checkinDate)) : 'unknown date';

                $chckoutdt = $checkoutDate != 'unknown date' ? date('d-m-Y', strtotime($checkoutDate)) : 'unknown date';

                $message = sprintf(
                    '%s wants to book %s from %s to %s for %d guests. You have 24 hours to respond!',
                    $guestName,
                    $listingName,
                    $chckindt,
                    $chckoutdt,
                    $totalGuests
                );

                foreach ($user_ids_arr as $user_id) {
                    if (!empty($user_id)) {
                        sendLiteNotification($user_id, "New Booking Requests", $message, $thread_id);
                    }
                }
            }

            if ($case == 'inquiry') {

                $message = "{$data['guest_name']} is interested in your space {$data['listing_name']} for {$data['number_of_guests']} guests from {$data['checkin_date']} to {$data['checkout_date']}. Respond now!";

                foreach ($user_ids_arr as $user_id) {
                    if (!empty($user_id)) {
                        sendLiteNotification($user_id, "Pre-Booking Inquiries", $message, $thread_id);
                    }
                }
            }

            if ($case == "booking_modification") {

                $numberOfGuests = 0;
                if (!empty($data['occupancy']['adults']) && !empty($data['occupancy']['children']) && !empty($data['occupancy']['infants'])) {
                    $adults = $data['occupancy']['adults'];
                    $children = $data['occupancy']['children'];
                    $infants = $data['occupancy']['infants'];

                    $numberOfGuests = $adults + $children + $infants;
                }

                $message = "{$data['customer']['name']} has made changes to their booking: the new dates are {$data['arrival_date']} to {$data['departure_date']} with {$numberOfGuests} guests. Review the updated details!";

                foreach ($user_ids_arr as $user_id) {
                    if (!empty($user_id)) {
                        sendLiteNotification($user_id, "Booking Modifications", $message, $thread_id);
                    }
                }
            }

            if ($case == "new_message") {

                $listingName = '';
                if (!is_null($listing)) {
                    $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                    $listingName = !empty($listing_json->title) ? $listing_json->title : '';
                }

                $message = "{$data['guest_name']} has sent you a message about their stay at {$listingName}: \"{$data['last_message']}\". Reply now!";

                foreach ($user_ids_arr as $user_id) {
                    if (!empty($user_id)) {
                        sendLiteNotification($user_id, "New Messages", $message, $thread_id);
                    }
                }
            }

            if ($case == 'booking_cancellation') {
                $message = "{$data['guest_name']} has requested to cancel their reservation for {$data['arrival_date']}. Review the request and respond promptly to ensure a smooth resolution.";

                foreach ($user_ids_arr as $user_id) {
                    if (!empty($user_id)) {
                        sendLiteNotification($user_id, "Cancellation Requests", $message, $thread_id);
                    }
                }
            }


        }
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

    public function sendMessageOnEvent($event, $event_data)
    {
        // dd($event_data['live_feed_event_id']);
        if ($event == 'inquiry') {
            $message = "Welcome to LivedIn Apartments!
                        We are grateful for your choice and promise to provide you with the best services. Our goal is to always ensure a comfortable and exceptional stay for you. If you have any inquiries or need assistance, please do not hesitate to contact us.
                        Best regards,
                        LivedIn Team";
            $this->sendEventMessage($event_data, $message);

        }
        // dd($this->fetchBookingMessage($event_data['booking_id']));
        if ($event == 'booking_new') {
            $booking_json = json_decode($event_data['booking_otas_json_details']);
            $raw_message = json_decode($booking_json->attributes->raw_message);
            $listing = Listings::where('listing_id', $event_data['listing_id'])->first();
            $listing_json = json_decode($listing->listing_json);
            $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
            $checkin_date = $event_data['arrival_date'];
            $departure_date = $event_data['departure_date'];
            $price = $event_data['amount'];
            $apart_num = $listing->apartment_num;
            $apart_map = $listing->google_map;
            $cust_name = $booking_json->attributes->customer->name . ' ' . $booking_json->attributes->customer->surname;
            $thread_id = $this->fetchBookingMessage($event_data['booking_id']);
            $thread = Thread::where('ch_thread_id', $thread_id)->first();
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
            $this->sendEventMessage($thread, $message);
        }
        // dd($event, $event_data);
    }

    public function blockAvailability($channel_type, $listing_id, $startDate, $endDate, $set_availability)
    {
        if ($channel_type != 'AirBNB') {
            // dd($channel_type,$listing_id, $startDate, $endDate);
            $listing = Listings::where('listing_id', $listing_id)->first();
            $listingOther = ListingRelation::where('listing_id_other_ota', $listing->id)->first();
            $listing_id_airbnb = Listings::where('id', $listingOther->listing_id_airbnb)->first();
            $matchinListing = ListingRelation::where('listing_id_airbnb', $listingOther->listing_id_airbnb)->get();
            // dd($matchinListing);
            foreach ($matchinListing as $item) {
                // dd($item->listing_id_airbnb);
                $listing = Listings::where('id', $item->listing_id_other_ota)->first();
                $endDate = Carbon::parse($endDate)->format('Y-m-d');
                $room_type = RoomType::where('listing_id', $listing->listing_id)->first();
                $property = Properties::where('id', $room_type->property_id)->first();
                $response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                            "values" => [
                                [
                                    //                        'date' => '2024-11-21',
                                    "date_from" => $startDate,
                                    "date_to" => $endDate,
                                    "property_id" => $property->ch_property_id,
                                    "room_type_id" => $room_type->ch_room_type_id,
                                    "availability" => $set_availability,
                                ],
                            ]
                        ]);

                if ($response->successful()) {
                    $availability = $response->json();

                    $this->blockAirbnb($item->listing_id_airbnb, $startDate, $endDate, $set_availability);

                    Calender::where('listing_id', $listing_id_airbnb->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                        ->update(
                            ['availability' => $set_availability]
                        );
                } else {
                    $error = $response->body();
                }
            }

        } else {
            return true;
        }
    }

    public function blockAirbnb($listing_id, $startDate, $endDate, $set_availability)
    {
        $listing = Listings::where('id', $listing_id)->first();
        $endDate = Carbon::parse($endDate)->format('Y-m-d');
        $room_type = RoomType::where('listing_id', $listing->listing_id)->first();
        $property = Properties::where('id', $room_type->property_id)->first();
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    "values" => [
                        [
                            "date_from" => $startDate,
                            "date_to" => $endDate,
                            "property_id" => $property->ch_property_id,
                            "room_type_id" => $room_type->ch_room_type_id,
                            "availability" => $set_availability,
                        ],
                    ]
                ]);
        if ($response->successful()) {
            $availability = $response->json();
            Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                ->update(
                    ['availability' => $set_availability]
                );
        } else {
            $error = $response->body();
        }
    }



    public function updateBookingThreadsmanual()
    {
        $records = BookingOtasDetail::all();

        foreach ($records as $record) {
            $booking_id = $record->booking_id;

            try {
                $response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id/messages");

                if ($response->successful()) {
                    $responseData = $response->json();
                    $ch_thread_id = $responseData['data'][0]['relationships']['message_thread']['data']['id'] ?? null;

                    if ($ch_thread_id) {
                        $thread = Thread::where('ch_thread_id', $ch_thread_id)
                            ->where(function ($query) {
                                $query->whereNull('status')->orWhere('status', '');
                            })
                            ->first();

                        if ($thread) {
                            $thread->update([
                                'status' => 'booking confirm',
                                'action_taken_at' => now(),
                            ]);
                        }
                    }
                } else {
                    logger("❌ API failed for booking_id $booking_id: " . $response->body());
                }

            } catch (\Exception $e) {
                logger("❌ Exception for booking_id $booking_id: " . $e->getMessage());
            }
        }

        // After loop: update all threads with NULL or empty status to 'inquiry'
        Thread::where(function ($query) {
            $query->whereNull('status')->orWhere('status', '');
        })->update([
                    'status' => 'inquiry',
                    'action_taken_at' => now(),
                ]);

        return response()->json(['message' => 'Threads updated successfully.']);
    }


}

<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SendMessageOnEvent;
use App\Models\BookingOtasDetails;
use App\Models\Calender;
use App\Models\NotificationM;
use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\BookingRequest;
use App\Models\Listing;
use App\Models\User;
use App\Models\Vendors;
use App\Models\MobileNotification;
use App\Models\RoomType;
use App\Models\Properties;
use App\Models\ListingRelation;
use App\Models\Channels;
use App\Models\Review;
use App\Services\FirebaseService;
use App\Services\StoreProcedureService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Twilio\Rest\Client;

class BackupWebhookController extends Controller
{
    protected $firebaseService;
    protected $storeProcedureService;
    protected $client;

    public function __construct(FirebaseService $firebaseService, StoreProcedureService $storeProcedureService)
    {
        $this->firebaseService = $firebaseService;
        $this->storeProcedureService = $storeProcedureService;

        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_AUTH_TOKEN'));
    }

    public function test()
    {
        return response()->json(['message' => 'Webhook is working']);
    }

    public function sendMessage($to, $message, $type)
    {
        $to = $this->formatPhoneNumber($to);
        $from = $type === 'whatsapp' ? env('TWILIO_WHATSAPP_FROM') : env('TWILIO_PHONE_NUMBER');
        if ($type === 'whatsapp') {
            $to = "whatsapp:$to";
        }

        try {
            return $this->client->messages->create($to, [
                'from' => $from,
                'body' => $message,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send message: {$e->getMessage()}");
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function formatPhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/[\s()-]+/', '', $phoneNumber);
        if (substr($phoneNumber, 0, 1) === '0') {
            $phoneNumber = substr($phoneNumber, 1);
        }
        return '+' . $phoneNumber;
    }

    public function handle(Request $request)
    {
        date_default_timezone_set('Asia/Baku');

        if (!$request->isMethod('post')) {
            return response()->json(['error' => 'Method Not Allowed'], 405);
        }

        $payload = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE || !isset($payload['event'])) {
            return response()->json(['error' => 'Unprocessable Entity'], 422);
        }

        $this->storeWebhookEvent($request->getContent());

        return $this->processWebhookEvent($payload);
    }

    private function storeWebhookEvent($content)
    {
        $directory = public_path('webhook_events');
        if (!file_exists($directory)) {
            mkdir($directory, 0755, true);
        }
        file_put_contents($directory . '/event_' . time() . '.json', $content);
    }

    private function processWebhookEvent(array $payload)
    {
        $event = $payload['event'];
        $this->logWebhookEvent(json_encode($payload), $event);

        switch ($event) {
            case 'booking':
            case 'booking_new':
                return $this->handleBookingEvent($payload, $event);
            case 'booking_modification':
                return $this->handleBookingModification($payload);
            case 'booking_cancellation':
                return $this->handleBookingCancellation($payload);
            case 'alteration_request':
                return $this->handleAlterationRequest($payload);
            case 'inquiry':
                return $this->handleInquiry($payload);
            case 'message':
                return $this->handleMessage($payload);
            case 'reservation_request':
                return $this->handleReservationRequest($payload);
            case 'review':
                return $this->handleReview($payload);
            case 'booking_unmapped_rate':
                return $this->handleUnmappedRate($payload);
            case 'ari':
                return $this->handleAri($payload);
            default:
                return response()->json(['error' => 'No valid events received'], 422);
        }
    }

    private function handleBookingEvent(array $payload, string $event)
    {
        $bookingId = $payload['payload']['booking_id'];
        $bookingDb = BookingOtasDetails::where('booking_id', $bookingId)->first();
        if ($bookingDb) {
            return response()->json(['message' => 'Booking already exists'], 200);
        }

        if ($event === 'booking_new') {
            $this->acknowledgeBookingRevision($payload['payload']['booking_revision_id']);
        }

        $bookingData = $this->fetchBookingData($bookingId);
        if (!$bookingData) {
            return response()->json(['error' => 'Failed to fetch booking data'], 500);
        }

        $attributes = $this->processBookingAttributes($bookingData['data']['attributes']);
        $values = $this->prepareBookingValues($payload, $bookingData, $attributes);

        $this->updateCalendarAvailability($attributes['listing_id'], $attributes['arrival_date'], $attributes['departure_date'], 0);
        $this->blockAvailability($attributes['ota_name'], $attributes['listing_id'], $attributes['arrival_date'], $attributes['departure_date'], 0);

        $bookingOtaId = BookingOtasDetails::insertGetId($values);
        $this->handleBookingThread($bookingId, $bookingOtaId, $attributes);

        $this->sendBookingNotifications($attributes['listing_id'], $bookingData['data']['attributes'], $bookingOtaId);

        return response()->json(['message' => 'Booking processed successfully']);
    }

    private function acknowledgeBookingRevision($revisionId)
    {
        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->post(env('CHANNEX_URL') . "/api/v1/booking_revisions/$revisionId/ack");

        if (!$response->successful()) {
            Log::error("Failed to acknowledge booking revision: {$response->body()}");
        }
    }

    private function fetchBookingData($bookingId)
    {
        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->get(env('CHANNEX_URL') . "/api/v1/bookings/$bookingId");

        if (!$response->successful()) {
            Log::error("Failed to fetch booking data: {$response->body()}");
            return null;
        }

        return $response->json();
    }

    private function processBookingAttributes(array $attributes)
    {
        $rawMessage = json_decode($attributes['raw_message']);
        $otaName = $attributes['ota_name'];
        $listingId = $this->determineListingId($rawMessage, $otaName, $attributes);
        $amount = (float) ($attributes['amount'] ?? 0);
        $cleaningFee = $this->calculateCleaningFee($listingId, $otaName, $rawMessage);
        $amount -= $cleaningFee;

        return [
            'listing_id' => $listingId,
            'ota_name' => $otaName,
            'arrival_date' => $attributes['arrival_date'],
            'departure_date' => $attributes['departure_date'],
            'unique_id' => $attributes['unique_id'],
            'guest_name' => $attributes['customer']['name'] . ' ' . $attributes['customer']['surname'],
            'guest_phone' => $attributes['customer']['phone'],
            'guest_email' => $attributes['customer']['mail'],
            'ota_commission' => (float) ($attributes['ota_commission'] ?? 0),
            'amount' => $amount,
            'cleaning_fee' => $cleaningFee,
            'promotions' => $this->calculatePromotions($rawMessage),
            'discounts' => $this->calculateDiscounts($rawMessage),
            'channel_id' => $attributes['channel_id'],
        ];
    }

    private function determineListingId($rawMessage, $otaName, $attributes)
    {
        if ($otaName === 'BookingCom') {
            return $rawMessage->reservation->listing_id ?? $attributes['rooms'][0]['meta']['room_type_code'];
        }
        return $rawMessage->reservation->listing_id ?? 0;
    }

    private function calculateCleaningFee($listingId, $otaName, $rawMessage)
    {
        $cleaningFee = 0;
        if (isset($rawMessage->reservation->standard_fees_details)) {
            foreach ($rawMessage->reservation->standard_fees_details as $fee) {
                $cleaningFee += abs($fee->amount ?? 0);
            }
        }

        $listing = Listing::where('listing_id', $listingId)->first();
        if ($otaName === 'BookingCom') {
            $bComListing = $listing;
            if ($bComListing) {
                $listingRelation = ListingRelation::where('listing_id_other_ota', $bComListing->id)->first();
                if ($listingRelation) {
                    $listingAirbnb = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
                    return $listingAirbnb ? $listingAirbnb->cleaning_fee : $cleaningFee;
                }
            }
        } elseif ($listing) {
            $cleaningFee += $listing->cleaning_fee;
        }

        return $cleaningFee;
    }

    private function calculatePromotions($rawMessage)
    {
        $promotions = 0;
        if (isset($rawMessage->reservation->promotion_details)) {
            foreach ($rawMessage->reservation->promotion_details as $promotion) {
                $promotions += abs($promotion->amount ?? 0);
            }
        }
        return $promotions;
    }

    private function calculateDiscounts($rawMessage)
    {
        $discounts = 0;
        if (isset($rawMessage->reservation->pricing_rule_details)) {
            foreach ($rawMessage->reservation->pricing_rule_details as $discount) {
                $discounts += abs($discount->amount ?? 0);
            }
        }
        return $discounts;
    }

    private function prepareBookingValues($payload, $bookingData, $attributes)
    {
        return [
            'listing_id' => $attributes['listing_id'],
            'property_id' => $payload['property_id'],
            'channel_id' => $attributes['channel_id'],
            'unique_id' => $attributes['unique_id'],
            'arrival_date' => $attributes['arrival_date'],
            'departure_date' => $attributes['departure_date'],
            'promotion' => $attributes['promotions'],
            'discount' => $attributes['discounts'],
            'cleaning_fee' => $attributes['cleaning_fee'],
            'ota_commission' => $attributes['ota_commission'],
            'amount' => $attributes['amount'],
            'guest_name' => $attributes['guest_name'],
            'guest_phone' => $attributes['guest_phone'],
            'guest_email' => $attributes['guest_email'],
            'ota_name' => $attributes['ota_name'],
            'booking_id' => $payload['payload']['booking_id'],
            'booking_otas_json_details' => json_encode($bookingData['data']),
            'status' => 'New',
        ];
    }

    private function updateCalendarAvailability($listingId, $startDate, $endDate, $availability)
    {
        $endDate = Carbon::parse($endDate)->subDay()->toDateString();
        $affectedRows = Calender::where('listing_id', (int) $listingId)
            ->whereBetween('calender_date', [$startDate, $endDate])
            ->update(['availability' => $availability]);

        DB::table('calenders')
            ->where('listing_id', (int) $listingId)
            ->whereBetween('calender_date', [$startDate, $endDate])
            ->update(['availability' => $availability]);

        Log::info("Updated calendar availability: Listing ID {$listingId}, Dates [{$startDate}, {$endDate}], Rows affected: {$affectedRows}");
    }

    private function handleBookingThread($bookingId, $bookingOtaId, $attributes)
    {
        $chThreadId = $this->fetchBookingMessage($bookingId);
        if (!$chThreadId) {
            return;
        }

        BookingOtasDetails::where('id', $bookingOtaId)->update(['ch_thread_id' => $chThreadId]);
        $thread = Thread::where('ch_thread_id', $chThreadId)->first();

        if ($thread) {
            ThreadMessage::create([
                'thread_id' => $thread->id,
                'sender' => 'channel',
                'message_content' => "{$thread->name} has confirmed the booking",
                'message_date' => now(),
                'message_type' => 'booking_confirm',
            ]);

            $thread->update(['status' => 'booking confirm', 'action_taken_at' => now()]);

            NotificationM::create([
                'notification_type_id' => 2,
                'module' => 'Chat Module',
                'module_id' => $thread->id,
                'title' => 'Booking Confirmation',
                'message' => "{$thread->name} has confirmed the booking",
                'url' => url('/communication-management'),
                'is_seen_by_all' => false,
            ]);
        }
    }

    private function sendBookingNotifications($listingId, $attributes, $bookingOtaId)
    {
        $listing = Listing::where('listing_id', $listingId)->first();
        if (!$listing) {
            return;
        }

        $listingJson = json_decode($listing->listing_json);
        $listingName = $listingJson->title ?? '';
        $customerName = $attributes['customer']['name'] . ' ' . $attributes['customer']['surname'];

        MobileNotification::create([
            'listing_id' => $listingId,
            'booking_id' => $bookingOtaId,
            'ota_type' => 'ota',
            'type' => 'booking',
            'price' => $attributes['amount'],
            'notification_label' => "{$customerName} has booked {$listingName}",
            'status' => 'unread',
            'booking_dates' => "{$attributes['arrival_date']} to {$attributes['departure_date']}",
            'listing_name' => $listingName,
        ]);

        $userIds = json_decode($listing->user_id, true) ?? [];
        $this->sendPushNotifications($userIds, $listingName, $customerName, $attributes, $bookingOtaId);
        $this->executeStoredProcedure($listingId, $attributes['booking_id']);
    }

    private function sendPushNotifications(array $userIds, $listingName, $customerName, $attributes, $bookingOtaId)
    {
        $title = 'Booking Confirmed';
        $listingNameArr = explode(' ', $listingName);
        $shortListingName = $listingNameArr[0] ?? '';
        $body = "Booking confirmed! {$customerName} is staying at {$shortListingName} from " .
            Carbon::parse($attributes['arrival_date'])->format('j M Y') .
            " to " . Carbon::parse($attributes['departure_date'])->format('j M Y') . ".";

        $bodyForLite = $this->generateLiteNotificationBody($attributes, $customerName);
        $threadId = $this->getThreadIdByBookingOtaId($bookingOtaId);

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user || empty($user->fcmTokens)) {
                continue;
            }

            if ($user->host_type_id == 2) {
                foreach ($user->fcmTokens as $token) {
                    if (!empty($token->fcm_token)) {
                        $notificationData = [
                            'id' => $bookingOtaId,
                            'otaName' => 'airbnb',
                            'type' => 'booking_detail',
                        ];
                        $this->firebaseService->sendPushNotification($token->fcm_token, $title, $body, 'BookingTrigger', $notificationData);
                    }
                }
            } elseif ($user->host_type_id == 1 && $bodyForLite) {
                sendLiteNotification($user->id, 'Instant Book Confirmations', $bodyForLite, $threadId);
            }
        }
    }

    private function generateLiteNotificationBody($attributes, $customerName)
    {
        if (isset($attributes['occupancy']['adults'], $attributes['occupancy']['children'], $attributes['occupancy']['infants'])) {
            $numberOfGuests = $attributes['occupancy']['adults'] + $attributes['occupancy']['children'] + $attributes['occupancy']['infants'];
            return "Booking Confirmed! {$customerName} has booked your space from " .
                Carbon::parse($attributes['arrival_date'])->format('j M Y') .
                " to " . Carbon::parse($attributes['departure_date'])->format('j M Y') .
                " for {$numberOfGuests} guests.";
        }
        return '';
    }

    private function executeStoredProcedure($listingId, $bookingId)
    {
        try {
            $result = $this->storeProcedureService
                ->name('sp_check_triggers_and_bookings_ota_V3')
                ->InParameters(['p_listing_id', 'P_Booking_Id'])
                ->OutParameters(['return_value', 'return_message', 'return_host_id', 'return_vendor_id'])
                ->data(['p_listing_id' => $listingId, 'P_Booking_Id' => $bookingId])
                ->execute();

            $response = $this->storeProcedureService->response();
            if ($response['response']['return_host_id'] > 0) {
                $this->notifyHostAndVendor($response['response']['return_host_id'], $response['response']['return_vendor_id']);
            }
        } catch (\Exception $e) {
            Log::error("Stored procedure error: {$e->getMessage()}");
        }
    }

    private function notifyHostAndVendor($hostId, $vendorId)
    {
        $vendor = Vendors::find($vendorId);
        $user = User::find($hostId);

        if ($vendor && $vendor->phone && $vendor->phone !== '0') {
            $this->sendMessage($vendor->country_code . $vendor->phone, 'New Task Created', 'sms');
        } else {
            Log::error("Invalid vendor phone number for vendor ID {$vendorId}");
        }

        if ($user && $user->phone && $user->phone !== '0') {
            $this->sendMessage($user->phone, 'New Task Created', 'whatsapp');
        } else {
            Log::error("Invalid user phone number for user ID {$hostId}");
        }
    }

    private function handleBookingModification(array $payload)
    {
        $bookingId = $payload['payload']['booking_id'];
        $booking = BookingOtasDetails::where('booking_id', $bookingId)->first();
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $bookingData = $this->fetchBookingData($bookingId);
        if (!$bookingData) {
            return response()->json(['error' => 'Failed to fetch booking data'], 500);
        }

        $attributes = $this->processBookingAttributes($bookingData['data']['attributes']);
        $this->updateBookingDetails($bookingId, $attributes, $bookingData);

        $this->updateCalendarForModification($booking, $attributes);
        $this->sendNotification($attributes['listing_id'], $bookingData['data']['attributes'], 'booking_modification', $this->getThreadIdByBookingOtaId($booking->id));

        return response()->json(['message' => 'Booking modification processed successfully']);
    }

    private function updateBookingDetails($bookingId, $attributes, $bookingData)
    {
        BookingOtasDetails::where('booking_id', $bookingId)->update([
            'arrival_date' => $attributes['arrival_date'],
            'departure_date' => $attributes['departure_date'],
            'promotion' => $attributes['promotions'],
            'discount' => $attributes['discounts'],
            'cleaning_fee' => $attributes['cleaning_fee'],
            'ota_commission' => $attributes['ota_commission'],
            'amount' => $attributes['amount'],
            'booking_otas_json_details' => json_encode($bookingData['data']),
            'status' => $bookingData['data']['attributes']['status'],
        ]);
    }

    private function updateCalendarForModification($booking, $attributes)
    {
        $oldDeparture = Carbon::parse($booking->departure_date)->subDay()->toDateString();
        if (
            $booking->listing_id != $attributes['listing_id'] ||
            $booking->arrival_date != $attributes['arrival_date'] ||
            $oldDeparture != Carbon::parse($attributes['departure_date'])->subDay()->toDateString()
        ) {
            $this->updateCalendarAvailability($booking->listing_id, $booking->arrival_date, $booking->departure_date, 1);
        }
        $this->updateCalendarAvailability($attributes['listing_id'], $attributes['arrival_date'], $attributes['departure_date'], 0);
    }

    private function handleBookingCancellation(array $payload)
    {
        $bookingId = $payload['payload']['booking_id'];
        $bookingData = $this->fetchBookingData($bookingId);
        if (!$bookingData) {
            return response()->json(['error' => 'Failed to fetch booking data'], 500);
        }

        $attributes = $this->processBookingAttributes($bookingData['data']['attributes']);
        $this->updateBookingDetails($bookingId, $attributes, $bookingData);
        $this->updateCalendarAvailability($attributes['listing_id'], $attributes['arrival_date'], $attributes['departure_date'], 1);
        $this->blockAvailability($attributes['ota_name'], $attributes['listing_id'], $attributes['arrival_date'], $attributes['departure_date'], 1);

        $bookingOta = BookingOtasDetails::where('booking_id', $bookingId)->first();
        $listing = Listing::where('listing_id', $attributes['listing_id'])->first();
        if ($bookingOta && $listing) {
            DB::table('tasks')
                ->where('listing_id', $listing->id)
                ->where('booking_id', $bookingOta->id)
                ->where('booking_Type', 'OTA')
                ->delete();
        }

        $this->sendNotification($attributes['listing_id'], [
            'guest_name' => $attributes['guest_name'],
            'arrival_date' => $attributes['arrival_date'],
        ], 'booking_cancellation', $this->getThreadIdByBookingOtaId($bookingOta->id));

        return response()->json(['message' => 'Booking cancellation processed successfully']);
    }

    private function handleAlterationRequest(array $payload)
    {
        $bookingId = $payload['payload']['bms']['booking_id'];
        $booking = BookingOtasDetails::where('booking_id', $bookingId)->first();
        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $bookingData = $this->fetchBookingData($bookingId);
        if (!$bookingData) {
            return response()->json(['error' => 'Failed to fetch booking data'], 500);
        }

        $rawMessage = json_decode($bookingData['data']['attributes']['raw_message']);
        $listingId = $rawMessage->reservation->listing_id ?? 12345;
        $attributes = [
            'listing_id' => $listingId,
            'arrival_date' => $bookingData['data']['attributes']['arrival_date'],
            'departure_date' => $bookingData['data']['attributes']['departure_date'],
            'status' => $bookingData['data']['attributes']['status'],
        ];

        BookingOtasDetails::where('booking_id', $bookingId)->update([
            'booking_otas_json_details' => json_encode($bookingData['data']),
            'listing_id' => $listingId,
            'arrival_date' => $attributes['arrival_date'],
            'departure_date' => $attributes['departure_date'],
            'status' => $attributes['status'],
        ]);

        $this->updateCalendarAvailability($booking->listing_id, $booking->arrival_date, $booking->departure_date, 1);
        $this->updateCalendarAvailability($listingId, $attributes['arrival_date'], $attributes['departure_date'], 0);

        return response()->json(['message' => 'Alteration request processed successfully']);
    }

    private function handleInquiry(array $payload)
    {
        $messageThreadId = $payload['payload']['message_thread_id'];
        $liveFeedEventId = $payload['payload']['live_feed_event_id'];
        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->get(env('CHANNEX_URL') . "/api/v1/message_threads/$messageThreadId");

        if (!$response->successful()) {
            Log::error("Failed to fetch message thread: {$response->body()}");
            return response()->json(['error' => $response->body()], 500);
        }

        $data = $response->json();
        $provider = Str::studly(Str::lower($data['data']['attributes']['provider']));
        $listingId = $data['data']['attributes']['meta']['listing_id'];
        $name = $payload['payload']['booking_details']['guest_name'];
        $lastMessage = $data['data']['attributes']['last_message']['message'];
        $lastMessageSender = $data['data']['attributes']['last_message']['sender'];
        $lastMessageInserted = $data['data']['attributes']['last_message']['inserted_at'];

        $threadInDb = Thread::where('listing_id', $listingId)->where('name', $name)->first();
        $intercomIds = $this->createIntercomContactAndConversation($messageThreadId, $name, $provider, $lastMessage);

        $thread = Thread::create([
            'ch_thread_id' => $messageThreadId,
            'listing_id' => $listingId,
            'live_feed_event_id' => $liveFeedEventId,
            'name' => $name,
            'last_message' => $lastMessage,
            'thread_type' => 'inquiry',
            'status' => 'inquiry',
            'booking_info_json' => json_encode($payload['payload']['booking_details']),
            'message_date' => $lastMessageInserted,
            'intercom_contact_id' => $intercomIds['contact_id'],
            'intercom_conversation_id' => $intercomIds['conversation_id'],
        ]);

        $this->sendMessageOnEvent('inquiry', $thread);

        NotificationM::create([
            'notification_type_id' => 2,
            'module' => 'Chat Module',
            'module_id' => $thread->id,
            'title' => 'Inquiry Generated',
            'message' => $this->getListingName($listingId),
            'url' => url('/communication-management'),
            'is_seen_by_all' => false,
        ]);

        $this->sendNotification($listingId, $payload['payload']['booking_details'], 'inquiry', $thread->id);

        return response()->json(['message' => 'Inquiry processed successfully']);
    }

    private function createIntercomContactAndConversation($messageThreadId, $name, $provider, $lastMessage)
    {
        $bearerToken = env('INTERCOM_TOKEN');
        $timestamp = now()->timestamp;

        $contactData = [
            'role' => 'user',
            'external_id' => $messageThreadId,
            'email' => '',
            'phone' => '',
            'name' => "{$name} ({$provider})",
            'avatar' => 'https://example.org/128Wash.jpg',
            'last_seen_at' => $timestamp,
            'signed_up_at' => $timestamp,
            'owner_id' => 8008643,
            'unsubscribed_from_emails' => false,
        ];

        $contactResponse = Http::withHeaders([
            'Authorization' => "Bearer $bearerToken",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.intercom.io/contacts', $contactData);

        $contactId = $contactResponse['id'] ?? null;

        $conversationData = [
            'from' => ['type' => 'user', 'id' => $contactId],
            'body' => $lastMessage,
        ];

        $conversationResponse = Http::withHeaders([
            'Authorization' => "Bearer $bearerToken",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://api.intercom.io/conversations', $conversationData);

        return [
            'contact_id' => $contactId,
            'conversation_id' => $conversationResponse['conversation_id'] ?? null,
        ];
    }

    private function handleMessage(array $payload)
    {
        $messageThreadId = $payload['payload']['message_thread_id'];
        $liveFeedEventId = $payload['payload']['live_feed_event_id'];
        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->get(env('CHANNEX_URL') . "/api/v1/message_threads/$messageThreadId");

        if (!$response->successful()) {
            Log::error("Failed to fetch message thread: {$response->body()}");
            return response()->json(['error' => $response->body()], 500);
        }

        $data = $response->json();
        $listingId = $this->determineMessageListingId($data['data']['attributes'], $data['data']['relationships']);
        $provider = Str::studly(Str::lower($data['data']['attributes']['provider']));
        $name = $data['data']['attributes']['title'];
        $lastMessage = $payload['payload']['message'];
        $lastMessageSender = $data['data']['attributes']['last_message']['sender'];
        $lastMessageInserted = $data['data']['attributes']['last_message']['inserted_at'];

        $attachment = $this->processAttachment($payload['payload']);
        $threadInDb = Thread::where('ch_thread_id', $messageThreadId)->first();

        if ($threadInDb) {
            $this->updateExistingThread($threadInDb, $payload, $lastMessage, $lastMessageSender, $lastMessageInserted, $attachment);
        } else {
            $this->createNewThread($messageThreadId, $listingId, $liveFeedEventId, $name, $lastMessage, $lastMessageSender, $lastMessageInserted, $attachment, $provider);
        }

        if ($lastMessageSender === 'guest') {
            $this->sendNotification($listingId, [
                'guest_name' => $name,
                'last_message' => $lastMessage,
            ], 'new_message', $threadInDb ? $threadInDb->id : Thread::where('ch_thread_id', $messageThreadId)->first()->id);
        }

        return response()->json(['message' => 'Message processed successfully']);
    }

    private function determineMessageListingId($attributes, $relationships)
    {
        if ($attributes['provider'] === 'BookingCom') {
            $channelId = $relationships['channel']['data']['id'];
            $channel = Channels::where('ch_channel_id', $channelId)->first();
            $listing = Listing::where('channel_id', $channel->id)->first();
            return $listing->listing_id;
        }
        return $attributes['meta']['listing_id'];
    }

    private function processAttachment($payload)
    {
        $attachment = ['type' => '', 'url' => ''];
        if (!empty($payload['have_attachment']) && !empty($payload['attachments'][0])) {
            $url = $payload['attachments'][0];
            $extension = pathinfo($url, PATHINFO_EXTENSION);
            $attachment['type'] = "image/{$extension}";
            $attachment['url'] = "http://app.channex.io/api/v1/{$url}";
        }
        return $attachment;
    }

    private function updateExistingThread($thread, $payload, $lastMessage, $lastMessageSender, $lastMessageInserted, $attachment)
    {
        $messageData = [
            'thread_id' => $thread->id,
            'message_uid' => $payload['payload']['id'],
            'sender' => $lastMessageSender,
            'message_content' => $lastMessage,
            'message_date' => $lastMessageInserted,
        ];

        if ($attachment['type'] && $attachment['url']) {
            $messageData['message_type'] = 'attachment';
            $messageData['attachment_type'] = $attachment['type'];
            $messageData['attachment_url'] = $attachment['url'];
        }

        ThreadMessage::create($messageData);
        $thread->update([
            'unread_count' => ($thread->unread_count ?? 0) + 1,
            'is_read' => 0,
        ]);

        $this->sendIntercomReply($thread->intercom_conversation_id, $thread->intercom_contact_id, $lastMessage, $lastMessageSender);

        NotificationM::create([
            'notification_type_id' => 2,
            'module' => 'Chat Module',
            'module_id' => $thread->id,
            'title' => 'New Message',
            'message' => Str::limit($lastMessage, 100),
            'url' => url('/communication-management'),
            'is_seen_by_all' => false,
        ]);
    }

    private function sendIntercomReply($conversationId, $contactId, $message, $sender)
    {
        $bearerToken = env('INTERCOM_TOKEN');
        $data = [
            'message_type' => $sender === 'property' ? 'note' : 'comment',
            'type' => $sender === 'property' ? 'admin' : 'user',
            $sender === 'property' ? 'admin_id' : 'intercom_user_id' => $sender === 'property' ? '8008643' : $contactId,
            'body' => $message,
        ];

        Http::withHeaders([
            'Authorization' => "Bearer $bearerToken",
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post("https://api.intercom.io/conversations/{$conversationId}/reply", $data);
    }

    private function createNewThread($messageThreadId, $listingId, $liveFeedEventId, $name, $lastMessage, $lastMessageSender, $lastMessageInserted, $attachment, $provider)
    {
        $intercomIds = $this->createIntercomContactAndConversation($messageThreadId, $name, $provider, $lastMessage);

        $thread = Thread::create([
            'ch_thread_id' => $messageThreadId,
            'listing_id' => $listingId,
            'live_feed_event_id' => $liveFeedEventId,
            'name' => $name,
            'last_message' => $lastMessage,
            'thread_type' => 'message',
            'status' => 'inquiry',
            'message_date' => $lastMessageInserted,
            'unread_count' => 1,
            'is_read' => 0,
            'intercom_contact_id' => $intercomIds['contact_id'],
            'intercom_conversation_id' => $intercomIds['conversation_id'],
        ]);

        $messageData = [
            'thread_id' => $thread->id,
            'message_uid' => $payload['payload']['id'],
            'sender' => $lastMessageSender,
            'message_content' => $lastMessage,
            'message_date' => $lastMessageInserted,
        ];

        if ($attachment['type'] && $attachment['url']) {
            $messageData['message_type'] = 'attachment';
            $messageData['attachment_type'] = $attachment['type'];
            $messageData['attachment_url'] = $attachment['url'];
        }

        ThreadMessage::create($messageData);
    }

    private function handleReservationRequest(array $payload)
    {
        $bookingRequest = BookingRequest::where('live_feed_event_id', $payload['payload']['live_feed_event_id'])->first();
        if (!$bookingRequest) {
            $bookingRequest = BookingRequest::create([
                'listing_id' => $payload['payload']['bms']['meta']['listing_id'] ?? null,
                'message_thread_id' => $payload['payload']['message_thread_id'] ?? null,
                'live_feed_event_id' => $payload['payload']['live_feed_event_id'] ?? null,
                'amount' => $payload['payload']['bms']['amount'] ?? null,
                'is_guest_verified' => $payload['payload']['bms']['raw_message']['reservation']['is_guest_verified'] ?? null,
                'guest_name' => $payload['payload']['bms']['customer']['name'] ?? null,
                'check_in_datetime' => $payload['payload']['bms']['raw_message']['reservation']['check_in_datetime'] ?? null,
                'check_out_datetime' => $payload['payload']['bms']['raw_message']['reservation']['check_out_datetime'] ?? null,
                'status' => 'pending',
                'booking_json' => json_encode($payload),
            ]);
        }

        $thread = Thread::where('live_feed_event_id', $bookingRequest->live_feed_event_id)->first();
        if (!$thread) {
            $thread = Thread::create([
                'ch_thread_id' => $payload['payload']['message_thread_id'] ?? null,
                'listing_id' => $payload['payload']['bms']['meta']['listing_id'] ?? null,
                'live_feed_event_id' => $payload['payload']['live_feed_event_id'],
                'name' => $payload['payload']['bms']['customer']['name'] ?? null,
                'last_message' => "{$payload['payload']['bms']['customer']['name']} requested for booking",
                'message_date' => Carbon::now()->subHour(),
            ]);
        }

        ThreadMessage::create([
            'thread_id' => $thread->id,
            'sender' => 'channel',
            'message_content' => "{$thread->name} requested for booking",
            'message_date' => Carbon::now()->subHour(),
            'message_type' => 'booking_request',
        ]);

        $this->sendMessageOnEvent('inquiry', $thread);

        NotificationM::create([
            'notification_type_id' => 2,
            'module' => 'Chat Module',
            'module_id' => $thread->id,
            'title' => 'Reservation Request',
            'message' => "{$thread->name} requested for booking",
            'url' => url('/communication-management'),
            'is_seen_by_all' => false,
        ]);

        Thread::where('id', $thread->id)->update([
            'status' => 'requested for booking',
            'action_taken_at' => now(),
        ]);

        $this->sendNotification($payload['payload']['bms']['meta']['listing_id'], $payload, 'reservation_request', $thread->id);

        return response()->json(['message' => 'Reservation request processed successfully']);
    }

    private function handleReview(array $payload)
    {
        $reviewId = $payload['payload']['id'];
        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->get(env('CHANNEX_URL') . "/api/v1/reviews/$reviewId");

        if (!$response->successful()) {
            Log::error("Failed to fetch review: {$response->body()}");
            return response()->json(['error' => $response->body()], 500);
        }

        $data = $response->json();
        $review = Review::create([
            'uId' => $data['data']['attributes']['id'],
            'booking_id' => $data['data']['relationships']['booking']['data']['id'],
            'ota_name' => $data['data']['attributes']['ota'],
            'overall_score' => $data['data']['attributes']['overall_score'],
            'review_json' => json_encode($data['data']['attributes']),
        ]);

        $listingId = $data['data']['attributes']['meta']['listing_id'] ?? '';
        $star = $data['data']['attributes']['overall_score'] ?? 0;
        $customerName = $data['data']['attributes']['guest_name'] ?? '';

        if ($listingId && round($star / 2) > 3) {
            $listing = Listing::where('listing_id', $listingId)->first();
            $listingName = json_decode($listing->listing_json)->title ?? '';
            MobileNotification::create([
                'listing_id' => $listingId,
                'booking_id' => null,
                'ota_type' => 'ota',
                'type' => 'review',
                'review_id' => $review->id,
                'price' => null,
                'notification_label' => "{$customerName} left " . round($star / 2) . " star review",
                'status' => 'unread',
                'booking_dates' => null,
                'listing_name' => $listingName,
            ]);

            $this->sendReviewNotifications($listingId, $listingName, $customerName, $star, $review->id);
        }

        return response()->json(['message' => 'Review processed successfully']);
    }

    private function sendReviewNotifications($listingId, $listingName, $customerName, $star, $reviewId)
    {
        $listing = Listing::where('listing_id', $listingId)->first();
        $userIds = json_decode($listing->user_id, true) ?? [];
        $shortListingName = explode(' ', $listingName)[0] ?? '';
        $star = $star / 2;
        $title = 'New Review Received';
        $body = "New review! {$customerName} has left a {$star} star review for their stay at {$shortListingName}. Check it out!";

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->host_type_id == 2 && !empty($user->fcmTokens)) {
                foreach ($user->fcmTokens as $token) {
                    if (!empty($token->fcm_token)) {
                        $notificationData = [
                            'id' => $reviewId,
                            'otaName' => '',
                            'type' => 'review_recieved',
                        ];
                        $this->firebaseService->sendPushNotification($token->fcm_token, $title, $body, 'ReviewTrigger', $notificationData);
                    }
                }
            }
        }
    }

    private function handleUnmappedRate(array $payload)
    {
        NotificationM::create([
            'notification_type_id' => 2,
            'module' => 'Webhook',
            'module_id' => 0,
            'title' => 'Unmapped Rate',
            'message' => json_encode($payload),
            'url' => '',
            'is_seen_by_all' => false,
        ]);

        return response()->json(['message' => 'Unmapped rate notification created']);
    }

    private function handleAri(array $payload)
    {
        // Placeholder for ARI handling logic
        return response()->json(['message' => 'ARI event processed']);
    }

    private function logWebhookEvent($payload, $eventType)
    {
        $data = "-------------------\n" .
            "EVENT NAME: {$eventType}\n" .
            "RECEIVED AT: " . date('d/m/Y H:i:s') . "\n" .
            "CHANNEX PAYLOAD:\n\n{$payload}\n" .
            "-------------------";
        Log::info($data);
    }

    private function getListingName($listingId)
    {
        $listing = Listing::where('listing_id', $listingId)->first();
        return $listing ? (json_decode($listing->listing_json)->title ?? '') : '';
    }

    private function getThreadIdByBookingOtaId($bookingOtaId)
    {
        // Implement the getThreadIDbyBookingOtaFDt function logic here
        return 0; // Placeholder
    }

    public function sendEventMessage($thread, $message)
    {
        if (empty($message)) {
            return response()->json(['error' => 'Message cannot be empty'], 400);
        }

        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->post(env('CHANNEX_URL') . "/api/v1/message_threads/{$thread->ch_thread_id}/messages", [
                'message' => ['message' => $message],
            ]);

        if ($response->successful()) {
            ThreadMessage::create([
                'thread_id' => $thread->id,
                'sender' => 'property',
                'message_content' => $message,
                'message_date' => Carbon::now(),
            ]);
            return response()->json(['message' => 'Message sent successfully']);
        }

        Log::error("Failed to send event message: {$response->body()}");
        return response()->json(['error' => $response->body()], 500);
    }

    public function fetchBookingMessage($bookingId)
    {
        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->get(env('CHANNEX_URL') . "/api/v1/bookings/{$bookingId}/messages");

        if ($response->successful()) {
            $data = $response->json();
            return $data['data'][0]['relationships']['message_thread']['data']['id'] ?? null;
        }

        Log::error("Failed to fetch booking message: {$response->body()}");
        return null;
    }

    public function sendMessageOnEvent($event, $eventData)
    {
        if ($event === 'inquiry') {
            $message = "Welcome to LivedIn Apartments!\n" .
                "We are grateful for your choice and promise to provide you with the best services. " .
                "Our goal is to always ensure a comfortable and exceptional stay for you. " .
                "If you have any inquiries or need assistance, please do not hesitate to contact us.\n" .
                "Best regards,\nLivedIn Team";
            $this->sendEventMessage($eventData, $message);
        } elseif ($event === 'booking_new') {
            $bookingJson = json_decode($eventData['booking_otas_json_details']);
            $listing = Listings::where('listing_id', $eventData['listing_id'])->first();
            $listingJson = json_decode($listing->listing_json);
            $message = $this->generateBookingConfirmationMessage($bookingJson, $listing, $eventData);
            $threadId = $this->fetchBookingMessage($eventData['booking_id']);
            $thread = Thread::where('ch_thread_id', $threadId)->first();
            if ($thread) {
                $this->sendEventMessage($thread, $message);
            }
        }
    }

    private function generateBookingConfirmationMessage($bookingJson, $listing, $eventData)
    {
        $listingName = $listingJson->title ?? '';
        $custName = $bookingJson->attributes->customer->name . ' ' . $bookingJson->attributes->customer->surname;
        $checkinDate = $eventData['arrival_date'];
        $departureDate = $eventData['departure_date'];
        $price = $eventData['amount'];
        $apartNum = $listing->apartment_num;
        $apartMap = $listing->google_map;

        return <<<EOD
Dear {$custName},  
Your booking at {$listingName} is confirmed from {$checkinDate} to {$departureDate}. We're excited to host you and ensure a wonderful stay!  
Stay Details:  
Check-In: 4:00 PM, {$checkinDate}  
Check-Out: 12:00 PM, {$departureDate}  
Total Charges: {$price} 
Apartment Number: {$apartNum}
Location: {$apartMap}
Apartment Guide Link: 
For any other requests, feel free to reach us via WhatsApp or call at +966115115798.  
Looking forward to make your stay exceptional
EOD;
    }

    public function blockAvailability($channelType, $listingId, $startDate, $endDate, $setAvailability)
    {
        if ($channelType === 'AirBNB') {
            return true;
        }

        $listing = Listings::where('listing_id', $listingId)->first();
        $listingOther = ListingRelation::where('listing_id_other_ota', $listing->id)->first();
        $listingAirbnb = Listings::where('id', $listingOther->listing_id_airbnb)->first();
        $matchingListings = ListingRelation::where('listing_id_airbnb', $listingOther->listing_id_airbnb)->get();

        foreach ($matchingListings as $item) {
            $listing = Listings::where('id', $item->listing_id_other_ota)->first();
            $roomType = RoomType::where('listing_id', $listing->listing_id)->first();
            $property = Properties::where('id', $roomType->property_id)->first();

            $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
                ->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    'values' => [
                        [
                            'date_from' => $startDate,
                            'date_to' => Carbon::parse($endDate)->format('Y-m-d'),
                            'property_id' => $property->ch_property_id,
                            'room_type_id' => $roomType->ch_room_type_id,
                            'availability' => $setAvailability,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                $this->blockAirbnb($item->listing_id_airbnb, $startDate, $endDate, $setAvailability);
                Calender::where('listing_id', $listingAirbnb->listing_id)
                    ->whereBetween('calender_date', [$startDate, $endDate])
                    ->update(['availability' => $setAvailability]);
            } else {
                Log::error("Failed to block availability: {$response->body()}");
            }
        }

        return true;
    }

    public function blockAirbnb($listingId, $startDate, $endDate, $setAvailability)
    {
        $listing = Listings::where('id', $listingId)->first();
        $roomType = RoomType::where('listing_id', $listing->listing_id)->first();
        $property = Properties::where('id', $roomType->property_id)->first();

        $response = Http::withHeaders(['user-api-key' => env('CHANNEX_API_KEY')])
            ->post(env('CHANNEX_URL') . "/api/v1/availability", [
                'values' => [
                    [
                        'date_from' => $startDate,
                        'date_to' => Carbon::parse($endDate)->format('Y-m-d'),
                        'property_id' => $property->ch_property_id,
                        'room_type_id' => $roomType->ch_room_type_id,
                        'availability' => $setAvailability,
                    ],
                ],
            ]);

        if ($response->successful()) {
            Calender::where('listing_id', $listing->listing_id)
                ->whereBetween('calender_date', [$startDate, $endDate])
                ->update(['availability' => $setAvailability]);
        } else {
            Log::error("Failed to block Airbnb availability: {$response->body()}");
        }
    }

    public function updateBookingThreadsManual()
    {
        $records = BookingOtasDetails::all();

        foreach ($records as $record) {
            $bookingId = $record->booking_id;
            try {
                $chThreadId = $this->fetchBookingMessage($bookingId);
                if ($chThreadId) {
                    $thread = Thread::where('ch_thread_id', $chThreadId)
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
            } catch (\Exception $e) {
                Log::error("Exception for booking_id {$bookingId}: {$e->getMessage()}");
            }
        }

        Thread::where(function ($query) {
            $query->whereNull('status')->orWhere('status', '');
        })->update([
                    'status' => 'inquiry',
                    'action_taken_at' => now(),
                ]);

        return response()->json(['message' => 'Threads updated successfully']);
    }
}
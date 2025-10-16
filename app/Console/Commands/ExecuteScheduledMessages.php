<?php

namespace App\Console\Commands;

use App\Models\BookingOtasDetails;
use App\Models\Listing;
use App\Models\ScheduledMessageLog;
use App\Models\Template;
use App\Models\Thread;
use App\Models\ThreadMessage;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ExecuteScheduledMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'execute-scheduled-messages';

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
        // logger("SCHEDULE MESSAGES...". now(). '...');

        $templates = Template::with('listings')->get();
        // $currentTime = now()->setTimezone('Asia/Riyadh')->startOfMinute();
        $currentTime = now()->setTimezone('Asia/Riyadh')->startOfMinute();

        foreach ($templates as $template) {
            $listingIds = $template->listings->pluck('listing_id')->toArray();
            $bookings = BookingOtasDetails::where('status', 'new')->whereIn('listing_id', $listingIds)->get();


            if ($bookings->count() > 0) {
                // If action is booking confirmation
                if ($template->action == "booking_confirmed") {
                    foreach ($bookings as $booking) {
                        $standardTime = Carbon::parse($booking->created_at)->format('H:i');
                        $today = now()->toDateString();

                        if ($booking->created_at->toDateString() !== $today) {
                            continue;
                        }

                        if ($template->when_to_send === 'immediately_after') {
                            $timeValue = 0;
                            $timeUnit = 'minutes';
                        } else {
                            preg_match('/(\d+)_([a-z]+)_after/', $template->when_to_send, $matches);
                            $timeValue = (int) $matches[1];
                            $timeUnit = $matches[2];
                        }

                        // $targetCheckInTime = Carbon::parse("$today $standardTime", 'Asia/Riyadh')

                        $targetCheckInTime = Carbon::parse($booking->created_at)
                            ->add($timeUnit, $timeValue)
                            ->subHour()
                            ->startOfMinute();

                        // logger($currentTime);
                        // logger($targetCheckInTime);

                        if (
                            $currentTime->hour === $targetCheckInTime->hour &&
                            $currentTime->minute === $targetCheckInTime->minute
                        ) {
                            // $finalBooking = $bookings->where('arrival_date', $today);
                            $finalBooking[0] = $booking;
                            $this->sendMessage($finalBooking, $template);
                        } else {
                            // logger("Current time does not match {$timeValue} {$timeUnit} after check-in time.");
                        }
                    }
                }

                // Logic for check in and check out
                if ($bookings->count() > 0 && ($template->action == 'check_in' || $template->action == 'check_out')) {
                    $today = now()->toDateString();
                    $targetDate = Carbon::parse($today);
                    if ($template->day != 'day_of') {
                        preg_match('/(\d+)_days_([a-z]+)/', $template->day, $matches);
                        $dayOffset = $matches ? (int) $matches[1] : 0;
                        $dayDirection = $matches[2];

                        if ($dayDirection === 'before') {
                            $targetDate->addDays($dayOffset);
                        } elseif ($dayDirection === 'after') {
                            $targetDate->subDays($dayOffset);
                        }
                    }

                    $checkInHour = (int) $template->time;
                    $targetCheckInTime = $targetDate->setTime($checkInHour, 0)->startOfMinute();
                    $targetCheckInTime = $targetCheckInTime->subHours(3);


                    if ($template->action == 'check_in') {
                        $filteredBookings = $bookings->where('arrival_date', $targetDate->toDateString());
                        // logger($targetDate->toDateString());
                    } elseif ($template->action == 'check_out') {
                        $filteredBookings = $bookings->where('departure_date', $targetDate->toDateString());
                    }

                    // logger($currentTime->hour);
                    // logger($targetCheckInTime->hour);

                    if (
                        $filteredBookings->isNotEmpty() &&
                        $currentTime->hour === $targetCheckInTime->hour
                        && $currentTime->minute === $targetCheckInTime->minute
                    ) {
                        $this->sendMessage($filteredBookings, $template);
                    } else {
                        // logger("Current time does not match target check-in time for {$dayOffset} days {$dayDirection}.");
                    }
                }
            }

            if ($template->action == 'booking_inquiry') {
                $threads = Thread::where('thread_type', 'inquiry')->whereIn('listing_id', $listingIds)->get();
                if ($threads->count() > 0) {
                    foreach ($threads as $thread) {
                        $standardTime = Carbon::parse($thread->created_at)->format('H:i');
                        $today = now()->toDateString();

                        if ($thread->created_at->toDateString() !== $today) {
                            continue;
                        }


                        if ($template->when_to_send === 'immediately_after') {
                            $timeValue = 0;
                            $timeUnit = 'minutes';
                        } else {
                            preg_match('/(\d+)_([a-z]+)_after/', $template->when_to_send, $matches);
                            $timeValue = (int) $matches[1];
                            $timeUnit = $matches[2];
                        }

                        // $targetCheckInTime = Carbon::parse("$today $standardTime", 'Asia/Riyadh')

                        $targetCheckInTime = Carbon::parse($thread->created_at)
                            ->add($timeUnit, $timeValue)
                            ->subHour()
                            ->startOfMinute();

                        // logger($thread->id);
                        // logger($currentTime);
                        // logger($targetCheckInTime);

                        if (
                            $currentTime->hour === $targetCheckInTime->hour &&
                            $currentTime->minute === $targetCheckInTime->minute
                        ) {
                            $finalBooking[0] = $thread;
                            $this->sendMessage($finalBooking, $template, $type = 'inquiry');
                        } else {
                            // logger("Current time does not match {$timeValue} {$timeUnit} after check-in time.");
                        }
                    }
                }
            }
        }
    }

    protected function sendMessage($bookings, $template, $type = null)
    {
        // logger("Sending Message");
        $message = $template->message;
        foreach ($bookings as $booking) {
            if ($type == 'inquiry') {
                $listing = Listing::where('listing_id', $booking->listing_id)->first();
                $thread = $booking;
                if (!empty($thread)) {
                    $threadId = $thread->ch_thread_id;
                    // $threadId = "fdcdba56-6aee-4eaf-9d0c-3ec5f049acf5";
                    $details = json_decode($thread->booking_info_json, true);

                    $shortnotes = [
                        '[LISTING_NAME]',
                        '[GUEST_NAME]',
                        '[CHECK_IN_DATE]',
                        '[CHECK_OUT_DATE]',
                        '[WIFI_NAME]',
                        '[WIFI_PASSWORD]',
                    ];

                    foreach ($shortnotes as $shortnote) {
                        if (strpos($message, $shortnote) !== false) {
                            switch ($shortnote) {
                                case '[LISTING_NAME]':
                                    if ($listing) {
                                        $listingJson = json_decode($listing->listing_json, true);
                                        $listingName = $listingJson["title"];
                                        $message = str_replace('[LISTING_NAME]', $listingName, $message);
                                    } else {
                                        return;
                                    }
                                    break;

                                case '[GUEST_NAME]':
                                    if (!empty($details['guest_name'])) {
                                        $customerName = $details['guest_name'];
                                        $message = str_replace('[GUEST_NAME]', $customerName, $message);
                                    } else {
                                        return;
                                    }
                                    break;

                                case '[CHECK_IN_DATE]':
                                    $arrivalDate = $details['checkin_date'];
                                    if (!empty($arrivalDate)) {
                                        $arrivalDate = Carbon::parse($arrivalDate)->format('F j, Y');
                                        $message = str_replace('[CHECK_IN_DATE]', $arrivalDate, $message);
                                    } else {
                                        return;
                                    }
                                    break;

                                case '[CHECK_OUT_DATE]':
                                    $departureDate = $details['checkout_date'];
                                    $departureDate = Carbon::parse($departureDate)->format('F j, Y');
                                    if (!empty($departureDate)) {
                                        $message = str_replace('[CHECK_OUT_DATE]', $departureDate, $message);
                                    } else {
                                        return;
                                    }
                                    break;

                                // case '[CHECK_IN_TIME]':
                                //     $checkInTime = Carbon::createFromTime($template['standard_check_in_time'], 0)->format('h:i A');
                                //     if (!empty($checkInTime)) {
                                //         $message = str_replace('[CHECK_IN_TIME]', $checkInTime, $message);
                                //     } else {
                                //         return;
                                //     }
                                //     break;

                                // case '[CHECK_OUT_TIME]':
                                //     $checkOutTime = Carbon::createFromTime($template['standard_check_out_time'], 0)->format('h:i A');
                                //     if (!empty($checkOutTime)) {
                                //         $message = str_replace('[CHECK_OUT_TIME]', $checkOutTime, $message);
                                //     } else {
                                //         return;
                                //     }
                                //     break;

                                // case '[TOTAL_AMOUNT]':
                                //     $detailJson = $booking['booking_otas_json_details'];
                                //     $decodedDetails = json_decode($detailJson, true);
                                //     if (!empty($decodedDetails['attributes']['amount']) && !empty($decodedDetails['attributes']['currency'])) {
                                //         $amount = $decodedDetails['attributes']['amount'];
                                //         $currency = $decodedDetails['attributes']['currency'];
                                //         $finalAmount = $amount . ' ' . $currency;
                                //         $message = str_replace('[TOTAL_AMOUNT]', $finalAmount, $message);
                                //     } else {
                                //         return;
                                //     }
                                //     break;

                                // case '[ADDRESS]':
                                //     if (!empty($listing->address)) {
                                //         $message = str_replace('[ADDRESS]', $listing->address, $message);
                                //     } else {
                                //         return;
                                //     }
                                //     break;

                                case '[WIFI_NAME]':
                                    if (!empty($listing->wifi_name)) {
                                        $message = str_replace('[WIFI_NAME]', $listing->wifi_name, $message);
                                    } else {
                                        return;
                                    }
                                    break;

                                case '[WIFI_PASSWORD]':
                                    if (!empty($listing->wifi_password)) {
                                        $message = str_replace('[WIFI_PASSWORD]', $listing->wifi_password, $message);
                                    } else {
                                        return;
                                    }
                                    break;
                            }
                        }
                    }

                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->post(env('CHANNEX_URL') . "/api/v1/message_threads/$threadId/messages", [
                                "message" => [
                                    "message" => "$message",
                                ],
                            ]);
                    if ($response->successful()) {
                        $response = $response->json();
                        $threadMessage = ThreadMessage::create([
                            'thread_id' => $thread->id,
                            'sender' => "property",
                            'message_content' => $message,
                            'message_date' => $response['data']['attributes']['inserted_at'],
                        ]);
                    }
                }
            } else if (!empty($booking->bookingOta)) {
                $ota = $booking->bookingOta;
                $details = $ota['booking_json'];
                $listing = Listing::where('listing_id', $booking->listing_id)->first();

                $details = json_decode($details, true);

                if (is_array($details) && isset($details['payload']['live_feed_event_id'])) {
                    $liveFeedEventId = $details['payload']['live_feed_event_id'];
                    // logger("Live Feed Event ID: " . $liveFeedEventId);
                    $thread = Thread::where('live_feed_event_id', $liveFeedEventId)->first();
                    if (!empty($thread)) {
                        $threadId = $thread->ch_thread_id;
                        // $threadId = "fdcdba56-6aee-4eaf-9d0c-3ec5f049acf5";

                        $shortnotes = [
                            '[LISTING_NAME]',
                            '[GUEST_NAME]',
                            '[CHECK_IN_DATE]',
                            '[CHECK_OUT_DATE]',
                            '[CHECK_IN_TIME]',
                            '[CHECK_OUT_TIME]',
                            '[TOTAL_AMOUNT]',
                            '[ADDRESS]',
                            '[WIFI_NAME]',
                            '[WIFI_PASSWORD]',
                        ];

                        foreach ($shortnotes as $shortnote) {
                            if (strpos($message, $shortnote) !== false) {
                                switch ($shortnote) {
                                    case '[LISTING_NAME]':
                                        if ($listing) {
                                            $listingJson = json_decode($listing->listing_json, true);
                                            $listingName = $listingJson["title"];
                                            $message = str_replace('[LISTING_NAME]', $listingName, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[GUEST_NAME]':
                                        $detailJson = $booking['booking_otas_json_details'];
                                        $decodedDetails = json_decode($detailJson, true);
                                        if (!empty($decodedDetails['attributes']['customer']['name'])) {
                                            $customerName = $decodedDetails['attributes']['customer']['name'];
                                            $message = str_replace('[GUEST_NAME]', $customerName, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[CHECK_IN_DATE]':
                                        $arrivalDate = $booking['arrival_date'];
                                        if (!empty($arrivalDate)) {
                                            $arrivalDate = Carbon::parse($arrivalDate)->format('F j, Y');
                                            $message = str_replace('[CHECK_IN_DATE]', $arrivalDate, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[CHECK_OUT_DATE]':
                                        $departureDate = $booking['departure_date'];
                                        $departureDate = Carbon::parse($departureDate)->format('F j, Y');
                                        if (!empty($departureDate)) {
                                            $message = str_replace('[CHECK_OUT_DATE]', $departureDate, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[CHECK_IN_TIME]':
                                        $checkInTime = Carbon::createFromTime($template['standard_check_in_time'], 0)->format('h:i A');
                                        if (!empty($checkInTime)) {
                                            $message = str_replace('[CHECK_IN_TIME]', $checkInTime, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[CHECK_OUT_TIME]':
                                        $checkOutTime = Carbon::createFromTime($template['standard_check_out_time'], 0)->format('h:i A');
                                        if (!empty($checkOutTime)) {
                                            $message = str_replace('[CHECK_OUT_TIME]', $checkOutTime, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[TOTAL_AMOUNT]':
                                        $detailJson = $booking['booking_otas_json_details'];
                                        $decodedDetails = json_decode($detailJson, true);
                                        if (!empty($decodedDetails['attributes']['amount']) && !empty($decodedDetails['attributes']['currency'])) {
                                            $amount = $decodedDetails['attributes']['amount'];
                                            $currency = $decodedDetails['attributes']['currency'];
                                            $finalAmount = $amount . ' ' . $currency;
                                            $message = str_replace('[TOTAL_AMOUNT]', $finalAmount, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[ADDRESS]':
                                        if (!empty($listing->address)) {
                                            $message = str_replace('[ADDRESS]', $listing->address, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[WIFI_NAME]':
                                        if (!empty($listing->wifi_name)) {
                                            $message = str_replace('[WIFI_NAME]', $listing->wifi_name, $message);
                                        } else {
                                            return;
                                        }
                                        break;

                                    case '[WIFI_PASSWORD]':
                                        if (!empty($listing->wifi_password)) {
                                            $message = str_replace('[WIFI_PASSWORD]', $listing->wifi_password, $message);
                                        } else {
                                            return;
                                        }
                                        break;
                                }
                            }
                        }

                        $response = Http::withHeaders([
                            'user-api-key' => env('CHANNEX_API_KEY'),
                        ])->post(env('CHANNEX_URL') . "/api/v1/message_threads/$threadId/messages", [
                            "message" => [
                                "message" => "$message",
                            ],
                        ]);
                        if ($response->successful()) {
                            $response = $response->json();
                            $threadMessage = ThreadMessage::create([
                                'thread_id' => $thread->id,
                                'sender' => "property",
                                'message_content' => $message,
                                'message_date' => $response['data']['attributes']['inserted_at'],
                            ]);
                        }
                    }
                }

                ScheduledMessageLog::create([
                    'template_id' => $template->id,
                    'listing_id' => $listing->listing_id,
                    'booking_id' => $booking->id,
                    'execution_count' => 1,
                    'message' => $message ?? null,
                    'status' => 'SUCCESS',
                ]);

            }

            // logger("Message Sent!");
        }
    }

}

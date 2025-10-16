<?php

namespace App\Http\Controllers\Admin\CalenderManagement;

use App\Http\Controllers\Controller;
use App\Jobs\SaveCalenderData;
use App\Mail\BlockDateRequestEmail;
use App\Models\BlockDateRequest;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Calender;
use App\Models\Channels;
use App\Models\Listing;
use App\Models\ListingIcalLink;
use App\Models\NotificationM;
use App\Models\Properties;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Models\User;
use App\Models\MrBranch;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Admin\BookingManagement\BookingManagementController;
use App\Models\Listings;
use App\Models\ListingSetting;
use App\Services\MixpanelService;
use App\Utilities\UserUtility;
use App\Models\ListingRelation;
use Illuminate\Support\Facades\Mail;
use om\IcalParser;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Mail\BlockDateEmail;
use Illuminate\Support\Facades\{
    DB
};


class CalenderManagementController extends Controller
{

    private $mixpanelService;
    public function __construct(MixpanelService $mixpanelService)
    {
        //  $this->middleware('permission');
        $this->mixpanelService = $mixpanelService;
    }

    public function index(Request $request)
    {

        // dd(isset($request->listing_id));
        // if (isset($request->listing_id)) {
        //     $bookings = BookingOtasDetails::where('created_at', '>', Carbon::parse('2025-07-15'))->get();


        //     foreach ($bookings as $booking) {
        //         // if ($booking->is_updated == 1) {
        //         //     continue;
        //         // }
        //         $listing = Listing::where('listing_id', $booking->listing_id)->first();
        //         // dd($listing);
        //         if (!$listing) {
        //             continue;
        //             return response()->json(['error' => 'Listing not found for listing_id: ' . $request->listing_id], 404);
        //         }
        //         $booking_json = json_decode($booking->booking_otas_json_details);
        //         $raw_message = json_decode($booking_json->attributes->raw_message ?? '{}');

        //         $promotions = 0;
        //         $discounts = 0;
        //         $cleanings = 0;

        //         $unique_id = $booking_json->attributes->unique_id ?? '';
        //         $guest_name = ($booking_json->attributes->customer->name ?? '') . ' ' . ($booking_json->attributes->customer->surname ?? '');
        //         $guest_phone = $booking_json->attributes->customer->phone ?? '';
        //         $guest_email = $booking_json->attributes->customer->mail ?? '';
        //         $ota_name = $booking_json->attributes->ota_name ?? '';
        //         $ota_commision = isset($booking_json->attributes->ota_commission) ? (float) $booking_json->attributes->ota_commission : 0;
        //         $amount = isset($booking_json->attributes->amount) ? (float) $booking_json->attributes->amount : 0;
        //         // dd($raw_message->reservation);
        //         // if(!isset($raw_message->reservation)){
        //         //     dd($booking_json);
        //         // }

        //         // Promotions
        //         if (!empty($raw_message->reservation->promotion_details)) {
        //             foreach ($raw_message->reservation->promotion_details as $promotion) {
        //                 $promotions += isset($promotion->amount) ? abs($promotion->amount) : 0;
        //             }
        //         }

        //         // Discounts
        //         if (!empty($raw_message->reservation->pricing_rule_details)) {
        //             foreach ($raw_message->reservation->pricing_rule_details as $discount) {
        //                 $discounts += isset($discount->amount) ? abs($discount->amount) : 0;
        //             }
        //         }

        //         // Cleaning Fees
        //         if (!empty($raw_message->reservation->standard_fees_details)) {
        //             foreach ($raw_message->reservation->standard_fees_details as $cleaning) {
        //                 $cleanings += isset($cleaning->amount) ? abs($cleaning->amount) : 0;
        //             }
        //         }
        //         // $amount= 0;

        //         // $amount = $amount + $promotions + $discounts + $ota_commision;

        //         // dd($amount);

        //         $host_cleaning = isset($listing->cleaning_fee) && $listing->cleaning_fee == 0 ? 0 : abs($listing->cleaning_fee - $cleanings);


        //         $cleanings = $cleanings + $host_cleaning;
        //         if ($booking->id == 3151) {
        //             dd($amount, $promotions, $discounts, $ota_commision, $cleanings, $host_cleaning);
        //         }
        //         $amount = $amount - $cleanings;

        //         $values = [
        //             'unique_id' => $unique_id,
        //             'promotion' => $promotions,
        //             'discount' => $discounts,
        //             'cleaning_fee' => $cleanings,
        //             'ota_commission' => $ota_commision,
        //             'amount' => $amount,
        //             'guest_name' => $guest_name,
        //             'guest_phone' => $guest_phone,
        //             'guest_email' => $guest_email,
        //             'is_updated' => 1,
        //             'ota_name' => $ota_name,
        //         ];

        //         $booking->update($values);
        //     }
        // }


        // $url = 'https://gathern.co/ical/990228LVX8oVeR.ics';
        // // $url = 'https://gathern.co/ical/188360nHBgbnnO.ics';

        // try {
        //     // Step 1: Fetch the ICS file
        //     $response = Http::get($url);
        //     if (!$response->successful()) {
        //         return response()->json(['error' => 'Failed to fetch calendar data'], 500);
        //     }

        //     // Step 2: Parse the ICS content
        //     $parser = new IcalParser();
        //     $parser->parseString($response->body());

        //     $eventsList = $parser->getSortedEvents(); // This is an EventsList object

        //     // Step 3: Iterate manually to build array
        //     $formattedEvents = [];

        //     foreach ($eventsList as $event) {
        //         $start = $event['DTSTART'] ?? null;
        //         $end = $event['DTEND'] ?? null;

        //         $startFormatted = null;
        //         $endFormatted = null;

        //         if ($start instanceof \DateTime) {
        //             $startFormatted = $start->format('Y-m-d H:i:s');
        //         } elseif (is_string($start)) {
        //             $startFormatted = date('Y-m-d H:i:s', strtotime($start));
        //         }

        //         if ($end instanceof \DateTime) {
        //             $endFormatted = $end->format('Y-m-d H:i:s');
        //         } elseif (is_string($end)) {
        //             $endFormatted = date('Y-m-d H:i:s', strtotime($end));
        //         }
        //         $formattedEvents[] = [
        //             'summary' => $event['SUMMARY'] ?? null,
        //             'start' => $startFormatted,
        //             'end' => $endFormatted,
        //             'description' => $event['DESCRIPTION'] ?? null,
        //             'location' => $event['LOCATION'] ?? null,
        //         ];
        //     }


        //     return response()->json($formattedEvents);

        // } catch (\Exception $e) {
        //     return response()->json(['error' => $e->getMessage()], 500);
        // }
        //           $response = Http::withHeaders([
        //        'user-api-key' => env('CHANNEX_API_KEY'),
        //    ])->get(env('CHANNEX_URL')."/api/v1/properties/05acf5e9-d39c-4182-8204-f2c248b92d69");
        //    if ($response->successful()) {
        //        $availability = $response->json();
        //        dd($availability);
        //        foreach($availability['data'] as $items) {
        //         // $items['attributes']['occ_adults'] = 1;
        //         // $items['attributes']['count_of_rooms'] = 1;
        //         // $items['attributes']['default_occupancy'] = 1;
        //         // dd($items);
        //         $id = $items['id'];
        //         $response = Http::withHeaders([
        //             'user-api-key' => env('CHANNEX_API_KEY'),
        //         ])->put(env('CHANNEX_URL')."/api/v1/room_types/$id",[
        //             'room_type' => [
        //                 "count_of_rooms" => 1,
        //                 "occ_adults" => 1,
        //                 "default_occupancy" => 1,
        //                 "occ_children" => 0,
        //                 "occ_infants" => 0
        //             ]
        //         ]);
        //         if ($response->successful()) {
        //             $availabilit = $response->json();
        //             // dd($availabilit );
        //             // foreach($availability['data'] as $items) {
        //             //  dd($items);
        //             // }
        //             // dd($availability);
        //         } else {
        //             $error = $response->body();
        //             dd($error);
        //         }
        //        }

        //    } else {
        //        $error = $response->body();
        //        dd($error);
        //    }

        //        $response = Http::withHeaders([
//            'user-api-key' => env('CHANNEX_API_KEY'),
//        ])->post(env('CHANNEX_URL')."/api/v1/channels/b06c4629-6cf0-4116-b7a0-9d3744e67d81/execute/load_listing_price_settings",[
//            "listing_id"=> 1150503108547634485
//        ]);
//        if ($response->successful()) {
//            $availability = $response->json();
//            dd($availability);
//        } else {
//            $error = $response->body();
//            dd($error);
//        }


        //        $response = Http::withHeaders([
//            'user-api-key' => env('CHANNEX_API_KEY'),
//        ])->get(env('CHANNEX_URL')."/api/v1/message_threads/bd443307-d640-4a3b-abfc-107e1dedd5e1");
//        if ($response->successful()) {
//            $availability = $response->json();
//            dd($availability);
//        } else {
//            $error = $response->body();
//            dd($error);
//        }




        $listings = Listing::where('is_sync', 'sync_all')->get();
        if ($request->get('listing_id')) {
            // $bookings = BookingOtasDetails::where('listing_id', $request->get('listing_id'))->whereBetween('arrival_date', ['2024-08-01', '2024-08-31'])->get();
            // $bookings = Bookings::where('listing_id', $request->get('listing_id'))->whereBetween('arrival_date', ['2024-08-01', '2024-08-31'])->get();
            // dd($bookings);
            $rate_plan = RatePlan::where('listing_id', $request->get('listing_id'))->get();
            $calenderData = Calender::where('listing_id', $request->get('listing_id'))->get();
            $calender = array();
            foreach ($calenderData as $item) {
                $calArr = $item->toArray();
                // dd($calArr);
                $user = User::where('id', $calArr['updated_by'])->first();

                $calender[$calArr['calender_date']] = [
                    'availability' => $calArr['availability'],
                    'max_stay' => $calArr['max_stay'],
                    'min_stay_through' => $calArr['min_stay_through'],
                    'rate' => $calArr['rate'],
                    'is_lock' => $calArr['is_lock'],
                    'block_reason' => $calArr['block_reason'] ?? '',
                    'updated_by' => isset($user) && $user !== null ? $user->name . ' ' . $user->surname : ''
                ];
            }

            // ->where('booking_sources', '!=', 'host_booking')

            $listing = Listing::where('listing_id', $request->get('listing_id'))->first();
            $listingIdArr = [$listing->listing_id];
            // $listingRelation = ListingRelation::where('listing_id_airbnb', $listing->id)->first();
            $listingRelation = ListingRelation::where('listing_id_airbnb', $listing->id)->get();
            if ($listingRelation) {
                foreach ($listingRelation as $it) {
                    $listing_Bcom = Listing::where('id', $it->listing_id_other_ota)->first();

                    if ($it->listing_type == 'Almosafer') {
                        $listingIdArr[] = $listing_Bcom->id;
                    } else {
                        $listingIdArr[] = $listing_Bcom->listing_id;
                    }
                }

            }
            // if($listingRelation) {
            $livedInBookingData = Bookings::where('listing_id', $listing->id)
                ->select('id', 'name', 'surname', 'booking_date_start', 'booking_date_end', 'booking_status', 'booking_sources', 'ota_name', 'created_at')
                ->get();
            $otaBookingsData = BookingOtasDetails::whereIn('listing_id', $listingIdArr)
                ->get();
            // }else {
            //     $livedInBookingData = Bookings::where('listing_id', $listing->id)
            //     ->select('id', 'name', 'surname', 'booking_date_start', 'booking_date_end', 'booking_status')
            //     ->get();
            //     $otaBookingsData = BookingOtasDetails::where('listing_id', $request->get('listing_id'))
            //     ->get();
            // }
            // dd($listingRelation);

            $livedInBooking = array();
            foreach ($livedInBookingData as $key => $item) {
                $livedInBooking[$key]['id'] = $item->id;
                $livedInBooking[$key]['name'] = $item->name;
                $livedInBooking[$key]['surname'] = $item->surname;
                $livedInBooking[$key]['booking_date_start'] = $item->booking_date_start;
                $livedInBooking[$key]['booking_date_end'] = $item->booking_date_end;
                $livedInBooking[$key]['type'] = 'livedIn_booking';
                $livedInBooking[$key]['status'] = $item->booking_status;
                $livedInBooking[$key]['ota_name'] = 'livedin_booking'; //!empty($item->ota_name) ? strtolower($item->ota_name) : '';
                $livedInBooking[$key]['booking_sources'] = !empty($item->booking_sources) ? strtolower($item->booking_sources) : '';
                $livedInBooking[$key]['reason'] = $item->reason ?? '';
                $livedInBooking[$key]['created_at'] = date('d-M-Y', strtotime($item->created_at));
            }

            $otaBookings = array();
            foreach ($otaBookingsData as $key => $item) {
                // $b = json_decode($item['booking_otas_json_details']);
                $otaBookings[$key]['id'] = $item->id;
                $otaBookings[$key]['name'] = $item->guest_name; //$b->attributes->customer->name ?? null;
                $otaBookings[$key]['booking_date_start'] = $item->arrival_date;
                $otaBookings[$key]['booking_date_end'] = $item->departure_date;
                $otaBookings[$key]['type'] = 'ota_booking';
                $otaBookings[$key]['status'] = $item->status;
                $otaBookings[$key]['ota_name'] = !empty($item->ota_name) ? strtolower($item->ota_name) : '';
                $otaBookings[$key]['booking_sources'] = '';
                $otaBookings[$key]['reason'] = $item->reason ?? '';
                $otaBookings[$key]['created_at'] = date('d-M-Y', strtotime($item->created_at));
            }
            $bookings = array_merge($livedInBooking, $otaBookings);
            //    Update Calender logic
            // foreach ($bookings as $book) {
            //     // dd($book);
            //     if ($book['status'] != 'cancelled') {
            //         $date = Carbon::parse($book['booking_date_end']);
            //         $previousDay = $date->subDay();
            //         $previousDay = $previousDay->toDateString();
            //         Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$book['booking_date_start'], $previousDay])
            //             ->update(
            //                 ['availability' => 0, 'is_lock' => 0]
            //             );
            //     }
            // }
            //    Update Calender logic

            // if(isset($_GET['testing'])){
            //     print_r($bookings);die;
            // }

            return view('Admin.calender-management.index', ['listings' => $listings, 'rate_plan' => $rate_plan, 'calender' => $calender, 'bookings' => $bookings]);
        }
        return view('Admin.calender-management.index', ['listings' => $listings]);
    }

    public function syncGathern($listing_id)
    {
        $listing = ListingIcalLink::where('listing_id', $listing_id)->first();
        if (!$listing) {
            return redirect()->back()->with('error', 'Listing not found');
        }
        // dd($listing);
        // $url = $listing->url;
        // dd($url);
        $url = 'https://admin.livedin.co/ical/6IAlWf07joUaF5W.ics';

        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch calendar data'], 500);
            }
            $parser = new IcalParser();
            $parser->parseString($response->body());
            $eventsList = $parser->getSortedEvents();
            $formattedEvents = [];
            foreach ($eventsList as $event) {
                $start = $event['DTSTART'] ?? null;
                $end = $event['DTEND'] ?? null;
                $formatDate = fn($date) => $date instanceof \DateTime
                    ? $date->format('Y-m-d')
                    : (is_string($date) ? date('Y-m-d', strtotime($date)) : null);
                $formattedEvents[] = [
                    'summary' => $event['SUMMARY'] ?? null,
                    'start' => $formatDate($start),
                    'end' => $formatDate($end),
                    'description' => $event['DESCRIPTION'] ?? null,
                    'location' => $event['LOCATION'] ?? null,
                ];
            }
            // dd($formattedEvents);

            foreach ($formattedEvents as $event) {
                // dd($event['description']);
                Calender::where('listing_id', $listing_id)->whereBetween('calender_date', [$event['start'], $event['end']])
                    ->update(
                        ['availability' => 0, 'block_reason' => 'Gathern ' . $event['description']]
                    );
            }
            // Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$block_date_request->start_date, $block_date_request->end_date])
            //     ->update(
            //         ['availability' => (int) $block_date_request->availability, 'is_lock' => isset($block_date_request->availability) && $block_date_request->availability == 0 ? 1 : 0, 'updated_by' => $user_id, 'block_reason' => 'host request for block']
            //     );
            return redirect()->back()->with('success', 'Calendar synced successfully');
            // return response()->json($formattedEvents);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function blockDateRequest()
    {
        $block_date_request = BlockDateRequest::orderBy('id', 'desc')->with('user', 'listing')->get();
        // dd($block_date_request);
        return view('Admin.calender-management.block-date-request', ['block_date_request' => $block_date_request]);
    }

    public function blockDateRequestAccept($id)
    {
        $user_id = Auth::user()->id;
        $block_date_request = BlockDateRequest::where('id', $id)->first();
        // dd($block_date_request);
        $listing = Listing::where('id', $block_date_request->listing_id)->first();
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
        $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
        // dd( $room_type);

        $ch_room_type_id = $room_type->ch_room_type_id;
        $rate_plan_ch_id = $rate_plan->ch_rate_plan_id;
        $property = Properties::where('id', $rate_plan->property_id)->first();
        $property_id = $property->ch_property_id;

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    "values" => [
                        [
                            //                        'date' => '2024-11-21',
                            "date_from" => $block_date_request->start_date,
                            "date_to" => $block_date_request->end_date,
                            "property_id" => "$property_id",
                            "room_type_id" => "$ch_room_type_id",
                            "availability" => (int) $block_date_request->availability,
                        ],
                    ]
                ]);
        if ($response->successful()) {
            $availability = $response->json();
            Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$block_date_request->start_date, $block_date_request->end_date])
                ->update(
                    ['availability' => (int) $block_date_request->availability, 'is_lock' => isset($block_date_request->availability) && $block_date_request->availability == 0 ? 1 : 0, 'updated_by' => $user_id, 'block_reason' => 'host request for block']
                );
            $block_date_request->update(['status' => 'completed']);
            return redirect()->back();
        } else {
            $error = $response->body();
        }

    }
    public function blockDateRequestDecline($id)
    {
        $block_date_request = BlockDateRequest::where('id', $id)->first();
        $block_date_request->update(['status' => 'declined']);
        return redirect()->back();
    }

    public function slideBarUpdateAvailability(Request $request)
    {

        if (!empty($request->daterange)) {
            $dateRange = $request->input('daterange');
            [$startDate, $endDate] = explode(' - ', $dateRange);
            // Parse the dates and format them as YYYY-MM-DD
            $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        }

        if (empty($startDate) || empty($endDate)) {
            return redirect()->back()->with('error', 'Start or End date is null');
        }

        // $last_date_calender = Calender::where('listing_id', $request->listing_id)->orderBy('calender_date', 'desc')->limit(1)->first();
        // $last_date = Carbon::parse($last_date_calender->calender_date);
        // $user_id = Auth::user()->id;
        // Add one day
        // $last_date->addDay();
        // $period = CarbonPeriod::create($last_date, $endDate);
        // if ($endDate > $last_date->toDateString()) {

        //     foreach ($period as $date) {
        //         Calender::create([
        //             'listing_id' => $request->listing_id,
        //             'availability' => 1,
        //             'max_stay' => $last_date_calender->max_stay,
        //             'min_stay_through' => $last_date_calender->min_stay_through,
        //             'rate' => $request->price,
        //             'calender_date' => $date->toDateString()
        //         ]);
        //     }
        // }
        $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
        $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
        $ch_room_type_id = $room_type->ch_room_type_id;
        $rate_plan_ch_id = $rate_plan->ch_rate_plan_id;
        $property = Properties::where('id', $rate_plan->property_id)->first();
        $property_id = $property->ch_property_id;
        $user_id = Auth::user()->id;

        if ($request->has('availability')) {

            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])
                ->timeout(0)
                ->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    "values" => [
                        [
                            "date_from" => $startDate,
                            "date_to" => $endDate,
                            "property_id" => "$property_id",
                            "room_type_id" => "$ch_room_type_id",
                            "availability" => (int) $request->availability,
                        ],
                    ]
                ]);

            if ($response->successful()) {
                $availability = $response->json();
                $block = null;
                if ($request->has('block_reason')) {
                    $block = $request->block_reason;
                }

                Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                    ->update(
                        ['availability' => (int) $request->availability, 'is_lock' => isset($request->availability) && $request->availability == 0 ? 1 : 0, 'updated_by' => $user_id, 'block_reason' => $block]
                    );

                // if($request->has('bcom_rm')) {
                // dd($restrictionDataBcom);
                $listing_airbnb = Listing::where('listing_id', $request->listing_id)->first();
                $listingRelations = ListingRelation::where('listing_id_airbnb', $listing_airbnb->id)->get();
                if ($listingRelations) {
                    foreach ($listingRelations as $listingRelation) {
                        $listing_Bcom = Listing::where('id', $listingRelation->listing_id_other_ota)->first();
                        if (is_null($listing_Bcom)) {
                            continue;
                        }

                        // For Maga Rental
                        if ($listingRelation->listing_type == 'Almosafer') {

                            $rate_plan_almosafer = RatePlan::where('listing_id', 'mr_' . $listing_Bcom->id)->first();
                            if (is_null($rate_plan_almosafer)) {
                                continue;
                            }

                            $room_type_almosafer = RoomType::where('listing_id', 'mr_' . $listing_Bcom->id)->first();
                            if (is_null($room_type_almosafer)) {
                                continue;
                            }

                            $avl = (int) $request->availability;

                            $ota_price_url = config('magarental.base_url') . '/api/v1/ota_prices';

                            if (!empty($listing_Bcom->mr_ota_price_id)) {
                                $ota_price_url .= '/' . $listing_Bcom->mr_ota_price_id;
                            }

                            $ota_prices = Http::withHeaders([
                                'Authorization' => config('magarental.api_key'),
                            ])
                                ->timeout(0)
                                ->put(
                                    $ota_price_url,
                                    [
                                        'PropertyId' => $listing_Bcom->listing_id ?? null,
                                        'Room_typeid' => $room_type_almosafer->mr_room_type_id ?? null,
                                        'OTA_inventory' => [
                                            [
                                                'date_from' => $startDate,
                                                'date_to' => $endDate,
                                                'max_sell' => $avl == 1 ? 1 : 0,
                                            ]
                                        ],
                                        "Created_date" => date('Y-m-d h:i:s')
                                    ]
                                );

                            if ($ota_prices->successful()) {
                                $ota_prices_json = $ota_prices->json();
                                $ota_price_id = $ota_prices_json['id'] ?? null;

                                if (!empty($ota_price_id)) {
                                    $listing_Bcom->mr_ota_price_id = $ota_price_id;
                                    $listing_Bcom->save();
                                }
                            }
                            continue;
                        }

                        $room_typeBcom = RoomType::where('listing_id', $listing_Bcom->listing_id)->first();
                        if (is_null($room_typeBcom)) {
                            continue;
                        }

                        $propertyBcom = Properties::where('id', $room_typeBcom->property_id)->first();
                        if (is_null($propertyBcom)) {
                            continue;
                        }

                        $rate_planBcom = RatePlan::where('listing_id', $listing_Bcom->listing_id)->first();
                        if (is_null($rate_planBcom)) {
                            continue;
                        }

                        $response = Http::withHeaders([
                            'user-api-key' => env('CHANNEX_API_KEY'),
                        ])
                            ->timeout(0)
                            ->post(env('CHANNEX_URL') . "/api/v1/availability", [
                                "values" => [
                                    [
                                        "date_from" => $startDate,
                                        "date_to" => $endDate,
                                        "property_id" => "$propertyBcom->ch_property_id",
                                        "room_type_id" => "$room_typeBcom->ch_room_type_id",
                                        "availability" => (int) $request->availability,
                                    ],
                                ]
                            ]);

                        if ($response->successful()) {
                            $availability = $response->json();
                            // dd($availability );
                            $block = null;
                            if ($request->has('block_reason')) {
                                $block = $request->block_reason;
                            }

                            Calender::where('listing_id', $listing_Bcom->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                                ->update(
                                    ['availability' => (int) $request->availability, 'is_lock' => isset($request->availability) && $request->availability == 0 ? 1 : 0, 'updated_by' => $user_id, 'block_reason' => $block]
                                );

                            Log::info('availability resp', ['response' => $availability]);
                        } else {
                            $error = $response->body();
                        }

                    }
                }
                // }

                Log::info('availability resp', ['response' => $availability]);

                return redirect()->back()->with('success', 'Availability has been updated');

            } else {
                $error = $response->body();

                return redirect()->back()->with('error', json_encode($error));
            }
        }
    }

    public function slideBarUpdatePrice(Request $request)
    {
        // print_r($request->all());die;

        if (!empty($request->daterange)) {
            $dateRange = $request->input('daterange');
            [$startDate, $endDate] = explode(' - ', $dateRange);
            // Parse the dates and format them as YYYY-MM-DD
            $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        }

        if (empty($startDate) || empty($endDate)) {
            return redirect()->back()->with('error', 'Start or End date is null');
        }

        $last_date_calender = Calender::where('listing_id', $request->listing_id)->orderBy('calender_date', 'desc')->limit(1)->first();
        $last_date = Carbon::parse($last_date_calender->calender_date);
        $user_id = Auth::user()->id;
        // Add one day
        $last_date->addDay();
        $period = CarbonPeriod::create($last_date, $endDate);
        if ($endDate > $last_date->toDateString()) {

            foreach ($period as $date) {
                Calender::create([
                    'listing_id' => $request->listing_id,
                    'availability' => 1,
                    'max_stay' => $last_date_calender->max_stay,
                    'min_stay_through' => $last_date_calender->min_stay_through,
                    'rate' => $request->price,
                    'calender_date' => $date->toDateString()
                ]);
            }
        }


        // Price Update
        if (!empty($request->price)) {

            $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
            $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
            $ch_room_type_id = $room_type->ch_room_type_id;
            $rate_plan_ch_id = $rate_plan->ch_rate_plan_id;
            $property = Properties::where('id', $rate_plan->property_id)->first();
            $property_id = $property->ch_property_id;

            $restrictionData = [
                "date_from" => $startDate,
                "date_to" => $endDate,
                "property_id" => "$property_id",
                "rate_plan_id" => "$rate_plan_ch_id", /*twin best rate*/
                'updated_by' => $user_id
            ];
            $restrictionDataDB = [
            ];
            if ($request->has('max_stay')) {
                $restrictionData["max_stay"] = (int) $request->max_stay;
                $restrictionDataDB["max_stay"] = (int) $request->max_stay;
            }
            if ($request->has('min_stay')) {
                $restrictionData["min_stay"] = (int) $request->min_stay;
                $restrictionDataDB["min_stay_through"] = (int) $request->min_stay;
            }
            if ($request->has('price')) {
                $restrictionData["rate"] = (int) $request->price * 100;
                $restrictionDataDB["rate"] = (int) $request->price;
            }

            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])
                ->timeout(0)
                ->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                    "values" => [
                        $restrictionData
                    ]
                ]);

            if ($response->successful()) {
                $restrictions = $response->json();
                Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                    ->update($restrictionDataDB);

                // Booking.com
                if (!empty($request->bcom_rm) && is_numeric($request->bcom_rm) && !empty($restrictionData['rate'])) {
                    $restrictionDataBcom = $restrictionData;
                    // dd($restrictionDataBcom);
                    $listing_airbnb = Listing::where('listing_id', $request->listing_id)->first();
                    if (is_null($listing_airbnb)) {
                        return redirect()->back()->with('error', 'BookingCom: Listing not found');
                    }

                    $listingRelation = ListingRelation::where('listing_id_airbnb', $listing_airbnb->id)->first();
                    if (is_null($listingRelation)) {
                        return redirect()->back()->with('error', 'BookingCom: Listing Relation not found');
                    }

                    $listing_Bcom = Listing::where('id', $listingRelation->listing_id_other_ota)->first();
                    if (is_null($listing_Bcom)) {
                        return redirect()->back()->with('error', 'BookingCom Listing not found');
                    }

                    $rate_planBcom = RatePlan::where('listing_id', $listing_Bcom->listing_id)->first();
                    if (is_null($rate_planBcom)) {
                        return redirect()->back()->with('error', 'BookingCom: Rate Plan not found');
                    }

                    $propertyBcom = Properties::where('id', $rate_planBcom->property_id)->first();
                    if (is_null($propertyBcom)) {
                        return redirect()->back()->with('error', 'BookingCom: Property not found');
                    }

                    $restrictionDataBcom['property_id'] = $propertyBcom->ch_property_id;
                    $restrictionDataBcom['rate_plan_id'] = $rate_planBcom->ch_rate_plan_id;
                    $restrictionDataBcom['rate'] = ($restrictionDataBcom['rate'] * ($request->bcom_rm / 100)) + $restrictionDataBcom['rate'];
                    $restrictionDataBcomDB['rate'] = ($restrictionDataBcom['rate'] * ($request->bcom_rm / 100)) + $restrictionDataBcom['rate'];
                    // dd($property,$rate_planBcom,$listing_Bcom,$listingRelation,$restrictionData,$request->bcom_rm,$listingRelation);
                    // dd($restrictionDataDB);
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])
                        ->timeout(0)
                        ->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                            "values" => [
                                $restrictionDataBcom
                            ]
                        ]);

                    if ($response->successful()) {
                        $restrictions = $response->json();

                        Calender::where('listing_id', $listing_Bcom->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                            ->update($restrictionDataBcomDB);
                        Log::info('rest resp', ['response' => $restrictions]);
                    } else {
                        $error = $response->body();
                        dd($error);
                    }
                }

                // Almosafer
                if (!empty($request->almosafer_rm) && is_numeric($request->almosafer_rm) && !empty($restrictionData['rate'])) {

                    $almosafer_restrict_data = $restrictionData;

                    $listing_airbnb = Listing::where('listing_id', $request->listing_id)->first();
                    if (is_null($listing_airbnb)) {
                        return redirect()->back()->with('error', 'Almosafer: Listing airbnb not found');
                    }

                    $listingRelation = ListingRelation::where('listing_id_airbnb', $listing_airbnb->id)
                        ->where('listing_type', 'Almosafer')
                        ->first();
                    if (is_null($listingRelation)) {
                        return redirect()->back()->with('error', 'Almosafer: Listing Relation not found');
                    }

                    $almosafer_listing = Listing::where('id', $listingRelation->listing_id_other_ota)->first();
                    if (is_null($almosafer_listing)) {
                        return redirect()->back()->with('error', 'Almosafer: Almosafer Listing not found');
                    }

                    $rate_plan_almosafer = RatePlan::where('listing_id', 'mr_' . $almosafer_listing->id)->first();
                    if (is_null($rate_plan_almosafer)) {
                        return redirect()->back()->with('error', 'Almosafer: Rate Plan not found');
                    }

                    $room_type_almosafer = RoomType::where('listing_id', 'mr_' . $almosafer_listing->id)->first();
                    if (is_null($room_type_almosafer)) {
                        return redirect()->back()->with('error', 'Almosafer: Room Type not found');
                    }

                    $almosafer_rate = ($almosafer_restrict_data['rate'] * ($request->almosafer_rm / 100)) + $almosafer_restrict_data['rate'];

                    $restrict = Http::withHeaders([
                        'Authorization' => config('magarental.api_key'),
                    ])
                        ->timeout(0)
                        ->post(config('magarental.base_url') . '/api/v1/restrictions', [
                            'values' => [
                                [
                                    'property_id' => $almosafer_listing->listing_id,
                                    'rate_plan_id' => $rate_plan_almosafer->mr_rate_plan_id,
                                    'date_from' => $startDate,
                                    'date_to' => $endDate,
                                    'rate' => round($almosafer_rate),
                                    'min_stay' => 1,
                                    'max_stay' => 730,
                                    'stopsell' => 0
                                ]
                            ]
                        ]);

                    if ($restrict->successful()) {
                        //
                    } else {
                        return redirect()->back()->with('error', 'Almosafer: Price not updated, Something went wrong');
                    }

                    // if($restrict->successful()){

                    //     $ota_prices_arr = DB::table('calenders')
                    //     ->selectRaw('calender_date as date_from, calender_date as date_to, IF(availability = 1, 1, 0) as max_sell')
                    //     ->where('listing_id', $listing_airbnb->listing_id)
                    //     ->whereBetween('calender_date', [$startDate, $endDate])
                    //     ->get()
                    //     ->toArray();

                    //     if(!empty($ota_prices_arr)){
                    //         $ota_price_url = config('magarental.base_url') . '/api/v1/ota_prices';

                    //         if(!empty($almosafer_listing->mr_ota_price_id)){
                    //             $ota_price_url .= '/'.$almosafer_listing->mr_ota_price_id;
                    //         }

                    //         $ota_prices = Http::withHeaders([
                    //             'Authorization' => config('magarental.api_key'),
                    //         ])->put($ota_price_url,
                    //             [
                    //                 'PropertyId' => $almosafer_listing->listing_id ?? null,
                    //                 'Room_typeid' => $room_type_almosafer->mr_room_type_id ?? null,
                    //                 'OTA_inventory' => $ota_prices_arr,
                    //                 "Created_date" => date('Y-m-d h:i:s')
                    //             ]
                    //         );

                    //         if ($ota_prices->successful()){
                    //             $ota_prices_json = $ota_prices->json();
                    //             $ota_price_id = $ota_prices_json['id'] ?? null;

                    //             if(!empty($ota_price_id)){
                    //                 $almosafer_listing->mr_ota_price_id = $ota_price_id;
                    //                 $almosafer_listing->save();
                    //             }
                    //         }
                    //     }
                    // }
                }
            } else {
                $error = $response->body();
                dd($error);
            }
        }

        return redirect()->back()->with('success', 'Price has been updated');
    }

    public function slideBarUpdateCustomPrice(Request $request)
    {
        // print_r($request->all());die;

        // (0=Monday, 1=Tuesday, 2=Wednesday, 3=Thursday, 4=Friday, 5=Saturday, 6=Sunday)

        if (empty($request->bcom_rm) && empty($request->weekday_pricing) && empty($request->weekend_pricing)) {
            return redirect()->back()->with('error', 'Please add atleast 1 field');
        }

        $today = Carbon::today()->toDateString();

        $restrictionData = [];

        $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
        $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
        $ch_room_type_id = $room_type->ch_room_type_id;
        $rate_plan_ch_id = $rate_plan->ch_rate_plan_id;
        $property = Properties::where('id', $rate_plan->property_id)->first();
        $property_id = $property->ch_property_id;
        $user_id = Auth::user()->id;

        $booking_com_flag = $almosafer_com_flag = false;
        $listingRelation = $listing_Bcom = $rate_planBcom = $propertyBcom = '';

        $listing_airbnb = Listing::where('listing_id', $request->listing_id)->first();

        // Booking.com
        if (!empty($request->bcom_rm) && is_numeric($request->bcom_rm)) {

            $listingRelation = ListingRelation::where('listing_id_airbnb', $listing_airbnb->id)
                ->where('listing_type', 'BookingCom')
                ->first();
            if (is_null($listingRelation)) {
                return redirect()->back()->with('error', 'BCom - Listing relation not found');
            }

            $listing_Bcom = Listing::where('id', $listingRelation->listing_id_other_ota)
                ->first();
            if (is_null($listing_Bcom)) {
                return redirect()->back()->with('error', 'BCom - Listing not found');
            }

            $rate_planBcom = RatePlan::where('listing_id', $listing_Bcom->listing_id)->first();
            if (is_null($rate_planBcom)) {
                return redirect()->back()->with('error', 'BCom - Rate plan not found');
            }

            $propertyBcom = Properties::where('id', $rate_planBcom->property_id)->first();
            if (is_null($propertyBcom)) {
                return redirect()->back()->with('error', 'BCom - Property not found');
            }

            $booking_com_flag = true;
        }

        // Almosafer
        if (!empty($request->almosafer_rm) && is_numeric($request->almosafer_rm)) {

            $listingRelation = ListingRelation::where('listing_id_airbnb', $listing_airbnb->id)
                ->where('listing_type', 'Almosafer')
                ->first();
            if (is_null($listingRelation)) {
                return redirect()->back()->with('error', 'Almosafer - Listing relation not found');
            }

            $almosafer_listing = Listing::where('id', $listingRelation->listing_id_other_ota)
                ->first();
            if (is_null($almosafer_listing)) {
                return redirect()->back()->with('error', 'Almosafer - Listing not found');
            }

            $almosafer_rate_plan = RatePlan::where('listing_id', 'mr_' . $almosafer_listing->id)->first();
            if (is_null($almosafer_rate_plan)) {
                return redirect()->back()->with('error', 'Almosafer - Rate plan not found');
            }

            $almosafer_room_type = RoomType::where('listing_id', 'mr_' . $almosafer_listing->id)->first();
            if (is_null($almosafer_room_type)) {
                return redirect()->back()->with('error', 'Almosafer - Room type not found');
            }

            $almosafer_property = Properties::where('id', $almosafer_rate_plan->property_id)->first();
            if (is_null($almosafer_property)) {
                return redirect()->back()->with('error', 'Almosafer - Property not found');
            }

            $almosafer_com_flag = true;
        }

        // WEEKDAY PRICING
        if (!empty($request->weekday_pricing)) {

            $weekday_arr = Calender::where('listing_id', $request->listing_id)
                ->whereDate('calender_date', '>=', $today)
                ->where(function ($query) use ($listing_airbnb) {

                    // 1 - Thursday, Friday & Saturday
                    if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                        $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                            ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                            ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                            // ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                            ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                    }

                    // 2 - Thursday & Friday
                    if ($listing_airbnb->ksa_weekend == 2) {
                        $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                            ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                            ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                            ->orWhereRaw('WEEKDAY(calender_date) = 5') // Saturday
                            ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                    }

                    // 3 - Saturday & Sunday
                    if ($listing_airbnb->ksa_weekend == 3) {
                        $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                            ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                            ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                            ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                            ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                    }

                })
                ->selectRaw('
                calender_date as date_from,
                calender_date as date_to,
                ? as property_id,
                ? as rate_plan_id,
                ? as rate,
                ? as updated_by
            ', ["$property_id", "$rate_plan_ch_id", (int) $request->weekday_pricing * 100, $user_id])
                ->orderBy('calender_date', 'ASC')
                ->get()
                ->toArray();

            if (!empty($weekday_arr)) {

                $weekday_response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])
                    ->timeout(0)
                    ->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                        "values" => $weekday_arr
                    ]);

                if ($weekday_response->successful()) {

                    Calender::where('listing_id', $request->listing_id)
                        ->whereDate('calender_date', '>=', $today)
                        ->where(function ($query) use ($listing_airbnb) {

                            // 1 - Thursday, Friday & Saturday
                            if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                    // ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                            }

                            // 2 - Thursday & Friday
                            if ($listing_airbnb->ksa_weekend == 2) {
                                $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 5') // Saturday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                            }

                            // 3 - Saturday & Sunday
                            if ($listing_airbnb->ksa_weekend == 3) {
                                $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                            }
                        })
                        ->update([
                            'rate' => (int) $request->weekday_pricing,
                            'updated_by' => $user_id,
                        ]);

                    // Booking.com Weekday Price Update Start
                    if (!empty($request->bcom_rm) && is_numeric($request->bcom_rm) && $booking_com_flag) {

                        $airbnb_price = (int) $request->weekday_pricing * 100;

                        $booking_com_price = ($airbnb_price * ($request->bcom_rm / 100)) + $airbnb_price;

                        $booking_com_weekday_arr = Calender::where('listing_id', $listing_Bcom->listing_id)
                            ->whereDate('calender_date', '>=', $today)
                            ->where(function ($query) use ($listing_airbnb) {

                                // 1 - Thursday, Friday & Saturday
                                if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                        // ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                }

                                // 2 - Thursday & Friday
                                if ($listing_airbnb->ksa_weekend == 2) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 5') // Saturday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                }

                                // 3 - Saturday & Sunday
                                if ($listing_airbnb->ksa_weekend == 3) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                                }
                            })
                            ->selectRaw('
                            calender_date as date_from,
                            calender_date as date_to,
                            ? as property_id,
                            ? as rate_plan_id,
                            ? as rate,
                            ? as updated_by
                        ', ["$propertyBcom->ch_property_id", "$rate_planBcom->ch_rate_plan_id", $booking_com_price, $user_id])
                            ->orderBy('calender_date', 'ASC')
                            ->get()
                            ->toArray();

                        if (!empty($booking_com_weekday_arr)) {

                            $booking_com_weekday_response = Http::withHeaders([
                                'user-api-key' => env('CHANNEX_API_KEY'),
                            ])
                                ->timeout(0)
                                ->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                                    "values" => $booking_com_weekday_arr
                                ]);

                            if ($booking_com_weekday_response->successful()) {

                                Calender::where('listing_id', $listing_Bcom->listing_id)
                                    ->whereDate('calender_date', '>=', $today)
                                    ->where(function ($query) use ($listing_airbnb) {

                                        // 1 - Thursday, Friday & Saturday
                                        if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                            $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                                // ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                        }

                                        // 2 - Thursday & Friday
                                        if ($listing_airbnb->ksa_weekend == 2) {
                                            $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 5') // Saturday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                        }

                                        // 3 - Saturday & Sunday
                                        if ($listing_airbnb->ksa_weekend == 3) {
                                            $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                                        }
                                    })
                                    ->update([
                                        'rate' => $booking_com_price,
                                        'updated_by' => $user_id,
                                    ]);
                            } else {
                                return redirect()->back()->with('error', 'Booking.com Weekday price has not been updated');
                            }
                        }
                    }
                    // Booking.com Weekday Price Update End

                    // Almosafer Weekday Price Update Start
                    if (!empty($request->almosafer_rm) && is_numeric($request->almosafer_rm) && $almosafer_com_flag) {

                        $branch = MrBranch::where('name', 'Riyadh')->first();

                        $airbnb_price = (int) $request->weekday_pricing * 100;

                        $almosafer_price = ($airbnb_price * ($request->almosafer_rm / 100)) + $airbnb_price;

                        $base_query = Calender::where('listing_id', $listing_airbnb->listing_id)
                            ->whereDate('calender_date', '>=', $today)
                            ->where(function ($query) use ($listing_airbnb) {

                                // 1 - Thursday, Friday & Saturday
                                if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                        // ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                }

                                // 2 - Thursday & Friday
                                if ($listing_airbnb->ksa_weekend == 2) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 5') // Saturday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                }

                                // 3 - Saturday & Sunday
                                if ($listing_airbnb->ksa_weekend == 3) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 0') // Monday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 1') // Tuesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 2') // Wednesday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                                }
                            });

                        $almosafer_weekday_arr = (clone $base_query)
                            ->selectRaw('? as property_id, ? as rate_plan_id, ? as rate, calender_date as date_from, calender_date as date_to, 1 as min_stay, 730 as max_stay, 0 as stopsell', [
                                $branch->property_id,
                                $almosafer_rate_plan->mr_rate_plan_id,
                                $almosafer_price
                            ])
                            ->orderBy('calender_date', 'ASC')
                            ->get()
                            ->toArray();

                        if (!empty($almosafer_weekday_arr)) {

                            $almosafer_restrict = Http::withHeaders([
                                'Authorization' => config('magarental.api_key'),
                            ])
                                ->timeout(0)
                                ->post(config('magarental.base_url') . '/api/v1/restrictions', [
                                    'values' => $almosafer_weekday_arr
                                ]);

                            $ota_prices_weekday_arr = (clone $base_query)
                                ->selectRaw('calender_date as date_from, calender_date as date_to, IF(availability = 1, 1, 0) as max_sell')
                                ->orderBy('calender_date', 'ASC')
                                ->get()
                                ->toArray();

                            if (!empty($ota_prices_weekday_arr)) {

                                $ota_price_url = config('magarental.base_url') . '/api/v1/ota_prices';

                                if (!empty($almosafer_listing->mr_ota_price_id)) {
                                    $ota_price_url .= '/' . $almosafer_listing->mr_ota_price_id;
                                }

                                $ota_prices = Http::withHeaders([
                                    'Authorization' => config('magarental.api_key'),
                                ])
                                    ->timeout(0)
                                    ->put(
                                        $ota_price_url,
                                        [
                                            'PropertyId' => $branch->property_id,
                                            'Room_typeid' => $almosafer_room_type->mr_room_type_id,
                                            'OTA_inventory' => $ota_prices_weekday_arr,
                                            "Created_date" => date('Y-m-d h:i:s')
                                        ]
                                    );

                                if ($ota_prices->successful()) {
                                    $ota_prices_json = $ota_prices->json();
                                    $ota_price_id = $ota_prices_json['id'] ?? null;

                                    if (!empty($ota_price_id)) {
                                        $almosafer_listing->mr_ota_price_id = $ota_price_id;
                                        $almosafer_listing->save();
                                    }
                                }
                            }
                        }
                    }
                    // Almosafer Weekday Price Update End

                } else {
                    return redirect()->back()->with('error', 'Airbnb Weekday price has not been updated');
                }
            }

            // print_r($weekday_arr);die;
        }

        // WEEKEND PRICING
        if (!empty($request->weekend_pricing)) {

            $weekend_arr = Calender::where('listing_id', $request->listing_id)
                ->whereDate('calender_date', '>=', $today)
                ->where(function ($query) use ($listing_airbnb) {

                    // 1 - Thursday, Friday & Saturday
                    if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                        $query->whereRaw('WEEKDAY(calender_date) = 4')  // Friday
                            ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                            ->orWhereRaw('WEEKDAY(calender_date) = 5'); // Saturday
                    }

                    // 2 - Thursday & Friday
                    if ($listing_airbnb->ksa_weekend == 2) {
                        $query->whereRaw('WEEKDAY(calender_date) = 3')  // Thursday
                            ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                    }

                    // 3 - Saturday & Sunday
                    if ($listing_airbnb->ksa_weekend == 3) {
                        $query->whereRaw('WEEKDAY(calender_date) = 5')  // Saturday
                            ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                    }
                })
                ->selectRaw('
                calender_date as date_from,
                calender_date as date_to,
                ? as property_id,
                ? as rate_plan_id,
                ? as rate,
                ? as updated_by
            ', ["$property_id", "$rate_plan_ch_id", (int) $request->weekend_pricing * 100, $user_id])
                ->orderBy('calender_date', 'ASC')
                ->get()
                ->toArray();

            if (!empty($weekend_arr)) {

                $weekend_response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])
                    ->timeout(0)
                    ->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                        "values" => $weekend_arr
                    ]);

                if ($weekend_response->successful()) {
                    Calender::where('listing_id', $request->listing_id)
                        ->whereDate('calender_date', '>=', $today)
                        ->where(function ($query) use ($listing_airbnb) {

                            // 1 - Thursday, Friday & Saturday
                            if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                $query->whereRaw('WEEKDAY(calender_date) = 4')  // Friday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 5'); // Saturday
                            }

                            // 2 - Thursday & Friday
                            if ($listing_airbnb->ksa_weekend == 2) {
                                $query->whereRaw('WEEKDAY(calender_date) = 3')  // Thursday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                            }

                            // 3 - Saturday & Sunday
                            if ($listing_airbnb->ksa_weekend == 3) {
                                $query->whereRaw('WEEKDAY(calender_date) = 5')  // Saturday
                                    ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                            }
                        })
                        ->update([
                            'rate' => (int) $request->weekend_pricing,
                            'updated_by' => $user_id,
                        ]);

                    // Booking.com Weekend Price Update Start
                    if (!empty($request->bcom_rm) && is_numeric($request->bcom_rm) && $booking_com_flag) {

                        $airbnb_price = (int) $request->weekend_pricing * 100;

                        $booking_com_price = ($airbnb_price * ($request->bcom_rm / 100)) + $airbnb_price;

                        $booking_com_weekend_arr = Calender::where('listing_id', $listing_Bcom->listing_id)
                            ->whereDate('calender_date', '>=', $today)
                            ->where(function ($query) use ($listing_airbnb) {

                                // 1 - Thursday, Friday & Saturday
                                if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 4')  // Friday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 5'); // Saturday
                                }

                                // 2 - Thursday & Friday
                                if ($listing_airbnb->ksa_weekend == 2) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 3')  // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                                }

                                // 3 - Saturday & Sunday
                                if ($listing_airbnb->ksa_weekend == 3) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 5')  // Saturday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                }
                            })
                            ->selectRaw('
                            calender_date as date_from,
                            calender_date as date_to,
                            ? as property_id,
                            ? as rate_plan_id,
                            ? as rate,
                            ? as updated_by
                        ', ["$propertyBcom->ch_property_id", "$rate_planBcom->ch_rate_plan_id", $booking_com_price, $user_id])
                            ->orderBy('calender_date', 'ASC')
                            ->get()
                            ->toArray();

                        if (!empty($booking_com_weekend_arr)) {

                            $booking_com_weekend_response = Http::withHeaders([
                                'user-api-key' => env('CHANNEX_API_KEY'),
                            ])
                                ->timeout(0)
                                ->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                                    "values" => $booking_com_weekend_arr
                                ]);

                            if ($booking_com_weekend_response->successful()) {

                                Calender::where('listing_id', $listing_Bcom->listing_id)
                                    ->whereDate('calender_date', '>=', $today)
                                    ->where(function ($query) use ($listing_airbnb) {

                                        // 1 - Thursday, Friday & Saturday
                                        if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                            $query->whereRaw('WEEKDAY(calender_date) = 4')  // Friday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 5'); // Saturday
                                        }

                                        // 2 - Thursday & Friday
                                        if ($listing_airbnb->ksa_weekend == 2) {
                                            $query->whereRaw('WEEKDAY(calender_date) = 3')  // Thursday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                                        }

                                        // 3 - Saturday & Sunday
                                        if ($listing_airbnb->ksa_weekend == 3) {
                                            $query->whereRaw('WEEKDAY(calender_date) = 5')  // Saturday
                                                ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                        }
                                    })
                                    ->update([
                                        'rate' => $booking_com_price,
                                        'updated_by' => $user_id,
                                    ]);
                            } else {
                                return redirect()->back()->with('error', 'Booking.com Weekend price has not been updated');
                            }
                        }
                    }
                    // Booking.com Weekend Price Update End

                    // Almosafer Weekend Price Update Start
                    if (!empty($request->almosafer_rm) && is_numeric($request->almosafer_rm) && $almosafer_com_flag) {

                        $branch = MrBranch::where('name', 'Riyadh')->first();

                        $airbnb_price = (int) $request->weekend_pricing * 100;

                        $almosafer_price = ($airbnb_price * ($request->almosafer_rm / 100)) + $airbnb_price;

                        $base_query = Calender::where('listing_id', $listing_airbnb->listing_id)
                            ->whereDate('calender_date', '>=', $today)
                            ->where(function ($query) use ($listing_airbnb) {

                                // 1 - Thursday, Friday & Saturday
                                if (empty($listing_airbnb->ksa_weekend) || $listing_airbnb->ksa_weekend == 1) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 4')  // Friday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 3') // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 5'); // Saturday
                                }

                                // 2 - Thursday & Friday
                                if ($listing_airbnb->ksa_weekend == 2) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 3')  // Thursday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 4'); // Friday
                                }

                                // 3 - Saturday & Sunday
                                if ($listing_airbnb->ksa_weekend == 3) {
                                    $query->whereRaw('WEEKDAY(calender_date) = 5')  // Saturday
                                        ->orWhereRaw('WEEKDAY(calender_date) = 6'); // Sunday
                                }
                            });

                        $almosafer_weekend_arr = (clone $base_query)
                            ->selectRaw('? as property_id, ? as rate_plan_id, ? as rate, calender_date as date_from, calender_date as date_to, 1 as min_stay, 730 as max_stay, 0 as stopsell', [
                                $branch->property_id,
                                $almosafer_rate_plan->mr_rate_plan_id,
                                $almosafer_price
                            ])
                            ->orderBy('calender_date', 'ASC')
                            ->get()
                            ->toArray();

                        if (!empty($almosafer_weekend_arr)) {

                            $almosafer_restrict = Http::withHeaders([
                                'Authorization' => config('magarental.api_key'),
                            ])
                                ->timeout(0)
                                ->post(config('magarental.base_url') . '/api/v1/restrictions', [
                                    'values' => $almosafer_weekend_arr
                                ]);

                            $ota_prices_weekend_arr = (clone $base_query)
                                ->selectRaw('calender_date as date_from, calender_date as date_to, IF(availability = 1, 1, 0) as max_sell')
                                ->orderBy('calender_date', 'ASC')
                                ->get()
                                ->toArray();

                            if (!empty($ota_prices_weekend_arr)) {

                                $ota_price_url = config('magarental.base_url') . '/api/v1/ota_prices';

                                if (!empty($almosafer_listing->mr_ota_price_id)) {
                                    $ota_price_url .= '/' . $almosafer_listing->mr_ota_price_id;
                                }

                                $ota_prices = Http::withHeaders([
                                    'Authorization' => config('magarental.api_key'),
                                ])
                                    ->timeout(0)
                                    ->put(
                                        $ota_price_url,
                                        [
                                            'PropertyId' => $branch->property_id,
                                            'Room_typeid' => $almosafer_room_type->mr_room_type_id,
                                            'OTA_inventory' => $ota_prices_weekend_arr,
                                            "Created_date" => date('Y-m-d h:i:s')
                                        ]
                                    );

                                if ($ota_prices->successful()) {
                                    $ota_prices_json = $ota_prices->json();
                                    $ota_price_id = $ota_prices_json['id'] ?? null;

                                    if (!empty($ota_price_id)) {
                                        $almosafer_listing->mr_ota_price_id = $ota_price_id;
                                        $almosafer_listing->save();
                                    }
                                }
                            }
                        }
                    }
                    // Almosafer Weekend Price Update End

                } else {
                    return redirect()->back()->with('error', 'Airbnb Weekend price has not been updated');
                }
            }

            // print_r($weekend_arr);die;
        }

        return redirect()->back()->with('success', 'Price has been updated');
    }

    public function getGbvDetails(Request $request)
    {
        // Validate required fields
        if (empty($request->listing_id) || empty($request->start_date) || empty($request->end_date)) {
            return response()->json([
                'error' => 'Fields are mandatory'
            ], 400);
        }

        // Find listing
        $listing = Listings::where('listing_id', $request->listing_id)->first();
        if (is_null($listing)) {
            return response()->json([
                'error' => 'Listing not found'
            ], 404);
        }

        // DEBUG: Log the inputs
        \Log::info('Debug GBV Details', [
            'listing_id' => $request->listing_id,
            'listing_db_id' => $listing->id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);

        // Get base price
        $listingSetting = ListingSetting::where('listing_id', $listing->listing_id)->first();
        $base_price = !is_null($listingSetting) ? $listingSetting->default_daily_price : 0;

        // Initialize totals
        $total_gbv_amount = 0;
        $total_nbv_amount = 0;
        $total_nights = 0;
        $total_discounts = 0;

        // DEBUG: Check what bookings exist for this listing
        $all_bookings = Bookings::where('listing_id', $listing->id)
            ->select('id', 'booking_date_start', 'booking_date_end', 'total_price', 'ota_commission', 'cleaning_fee', 'custom_discount', 'booking_status')
            ->get();

        \Log::info('All bookings for listing', ['bookings' => $all_bookings->toArray()]);

        // Simplified date filtering - try different approaches

        // Method 1: Simple date range (your original approach)
        $gbv_livedin_simple = Bookings::where('listing_id', $listing->id)
            ->whereDate('booking_date_start', '>=', $request->start_date)
            ->whereDate('booking_date_end', '<=', $request->end_date)
            ->selectRaw('
            SUM(DATEDIFF(booking_date_end, booking_date_start)) as total_nights,
            SUM(total_price + COALESCE(ota_commission, 0) + COALESCE(cleaning_fee, 0) + COALESCE(custom_discount, 0)) as gbv_total,
            SUM(total_price + COALESCE(cleaning_fee, 0)) as nbv_total,
            SUM(COALESCE(custom_discount, 0)) as total_discounts
        ')
            ->first();

        \Log::info('Simple date filter result', ['result' => $gbv_livedin_simple ? $gbv_livedin_simple->toArray() : 'null']);

        // Method 2: Overlapping date ranges
        $gbv_livedin_overlap = Bookings::where('listing_id', $listing->id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    // Booking starts within range
                    $q->whereDate('booking_date_start', '>=', $request->start_date)
                        ->whereDate('booking_date_start', '<=', $request->end_date);
                })
                    ->orWhere(function ($q) use ($request) {
                        // Booking ends within range
                        $q->whereDate('booking_date_end', '>=', $request->start_date)
                            ->whereDate('booking_date_end', '<=', $request->end_date);
                    })
                    ->orWhere(function ($q) use ($request) {
                        // Booking spans the entire range
                        $q->whereDate('booking_date_start', '<=', $request->start_date)
                            ->whereDate('booking_date_end', '>=', $request->end_date);
                    });
            })
            ->selectRaw('
            SUM(DATEDIFF(booking_date_end, booking_date_start)) as total_nights,
            SUM(total_price + COALESCE(ota_commission, 0) + COALESCE(cleaning_fee, 0) + COALESCE(custom_discount, 0)) as gbv_total,
            SUM(total_price + COALESCE(cleaning_fee, 0)) as nbv_total,
            SUM(COALESCE(custom_discount, 0)) as total_discounts
        ')
            ->first();

        \Log::info('Overlap date filter result', ['result' => $gbv_livedin_overlap ? $gbv_livedin_overlap->toArray() : 'null']);

        // Use overlap method for main calculation
        $gbv_livedin = $gbv_livedin_overlap;

        // Add LivedIn data to totals
        if ($gbv_livedin) {
            $total_gbv_amount += $gbv_livedin->gbv_total ?? 0;
            $total_nbv_amount += $gbv_livedin->nbv_total ?? 0;
            $total_nights += $gbv_livedin->total_nights ?? 0;
            $total_discounts += $gbv_livedin->total_discounts ?? 0;
        }

        // DEBUG: Check OTA bookings
        $all_ota_bookings = BookingOtasDetails::where('listing_id', $listing->listing_id)
            ->select('id', 'arrival_date', 'departure_date', 'amount', 'ota_commission', 'cleaning_fee', 'discount', 'status')
            ->get();

        \Log::info('All OTA bookings for listing', ['ota_bookings' => $all_ota_bookings->toArray()]);

        // DEBUG: Check OTA bookings in detail
        $ota_bookings_raw = BookingOtasDetails::where('listing_id', $listing->listing_id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereDate('arrival_date', '>=', $request->start_date)
                        ->whereDate('arrival_date', '<=', $request->end_date);
                })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('departure_date', '>=', $request->start_date)
                            ->whereDate('departure_date', '<=', $request->end_date);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('arrival_date', '<=', $request->start_date)
                            ->whereDate('departure_date', '>=', $request->end_date);
                    });
            })
            ->get(['arrival_date', 'departure_date', 'amount', 'ota_commission', 'cleaning_fee', 'discount']);

        \Log::info('OTA bookings raw data', [
            'count' => $ota_bookings_raw->count(),
            'bookings' => $ota_bookings_raw->map(function ($booking) {
                return [
                    'arrival_date' => $booking->arrival_date,
                    'departure_date' => $booking->departure_date,
                    'nights' => Carbon::parse($booking->arrival_date)->diffInDays(Carbon::parse($booking->departure_date)),
                    'amount' => $booking->amount,
                    'ota_commission' => $booking->ota_commission,
                    'cleaning_fee' => $booking->cleaning_fee,
                    'discount' => $booking->discount,
                    'gbv_per_booking' => $booking->amount + ($booking->ota_commission ?? 0) + ($booking->cleaning_fee ?? 0) + ($booking->discount ?? 0)
                ];
            })->toArray()
        ]);

        // Get OTA bookings data with overlapping date logic
        $gbv_ota = BookingOtasDetails::where('listing_id', $listing->listing_id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    // Booking starts within range
                    $q->whereDate('arrival_date', '>=', $request->start_date)
                        ->whereDate('arrival_date', '<=', $request->end_date);
                })
                    ->orWhere(function ($q) use ($request) {
                        // Booking ends within range
                        $q->whereDate('departure_date', '>=', $request->start_date)
                            ->whereDate('departure_date', '<=', $request->end_date);
                    })
                    ->orWhere(function ($q) use ($request) {
                        // Booking spans the entire range
                        $q->whereDate('arrival_date', '<=', $request->start_date)
                            ->whereDate('departure_date', '>=', $request->end_date);
                    });
            })
            ->selectRaw('
            SUM(DATEDIFF(departure_date, arrival_date)) as total_nights,
            SUM(amount) as gbv_total,
            SUM(amount + COALESCE(cleaning_fee, 0)) as nbv_total,
            SUM(COALESCE(discount, 0)) as total_discounts
        ')
            ->first();

        \Log::info('OTA query result', ['result' => $gbv_ota ? $gbv_ota->toArray() : 'null']);

        // Add OTA data to totals
        if ($gbv_ota) {
            $total_gbv_amount += $gbv_ota->gbv_total ?? 0;
            $total_nbv_amount += $gbv_ota->nbv_total ?? 0;
            $total_nights += $gbv_ota->total_nights ?? 0;
            $total_discounts += $gbv_ota->total_discounts ?? 0;
        }

        // Calculate final averages per night
        $final_gbv = $total_gbv_amount;
        $final_nbv = $total_nbv_amount;

        // Calculate ADR (Average Daily Rate)
        $adr = 0;
        $rates = Calender::where('listing_id', $listing->listing_id)
            ->whereBetween('calender_date', [$request->start_date, $request->end_date])
            ->pluck('rate');

        if ($rates->count() > 0) {
            $adr = round($rates->avg(), 2);
        }

        // Calculate total cancelled bookings
        $livedinCancelledBookingsCount = Bookings::where('listing_id', $listing->id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereDate('booking_date_start', '>=', $request->start_date)
                        ->whereDate('booking_date_start', '<=', $request->end_date);
                })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('booking_date_end', '>=', $request->start_date)
                            ->whereDate('booking_date_end', '<=', $request->end_date);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('booking_date_start', '<=', $request->start_date)
                            ->whereDate('booking_date_end', '>=', $request->end_date);
                    });
            })
            ->where('booking_status', 'cancelled')
            ->count();

        $otaCancelledBookingsCount = BookingOtasDetails::where('listing_id', $listing->listing_id)
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereDate('arrival_date', '>=', $request->start_date)
                        ->whereDate('arrival_date', '<=', $request->end_date);
                })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('departure_date', '>=', $request->start_date)
                            ->whereDate('departure_date', '<=', $request->end_date);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('arrival_date', '<=', $request->start_date)
                            ->whereDate('departure_date', '>=', $request->end_date);
                    });
            })
            ->where('status', 'cancelled')
            ->count();

        $total_cancellations = $livedinCancelledBookingsCount + $otaCancelledBookingsCount;

        // Calculate occupancy more accurately using unique dates
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $total_days = $start->diffInDays($end) + 1; // Days in range (inclusive)

        // DEBUG: Log date calculation
        \Log::info('Date range calculation', [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'calculated_days' => $total_days,
            'diff_in_days' => $start->diffInDays($end)
        ]);

        // Get all occupied dates from LivedIn bookings
        $livedin_dates = Bookings::where('listing_id', $listing->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereDate('booking_date_start', '>=', $request->start_date)
                        ->whereDate('booking_date_start', '<=', $request->end_date);
                })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('booking_date_end', '>=', $request->start_date)
                            ->whereDate('booking_date_end', '<=', $request->end_date);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('booking_date_start', '<=', $request->start_date)
                            ->whereDate('booking_date_end', '>=', $request->end_date);
                    });
            })
            ->get(['booking_date_start', 'booking_date_end']);

        // Get all occupied dates from OTA bookings
        $ota_dates = BookingOtasDetails::where('listing_id', $listing->listing_id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->whereDate('arrival_date', '>=', $request->start_date)
                        ->whereDate('arrival_date', '<=', $request->end_date);
                })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('departure_date', '>=', $request->start_date)
                            ->whereDate('departure_date', '<=', $request->end_date);
                    })
                    ->orWhere(function ($q) use ($request) {
                        $q->whereDate('arrival_date', '<=', $request->start_date)
                            ->whereDate('departure_date', '>=', $request->end_date);
                    });
            })
            ->get(['arrival_date', 'departure_date']);

        // Count unique occupied dates within the selected range
        $occupied_dates = collect();

        // Add LivedIn occupied dates (exclude checkout day)
        foreach ($livedin_dates as $booking) {
            $current = Carbon::parse($booking->booking_date_start);
            $booking_end = Carbon::parse($booking->booking_date_end);

            // Only include dates that fall within our selected range
            // and exclude the checkout date
            while ($current->lt($booking_end) && $current->gte($start) && $current->lte($end)) {
                $occupied_dates->push($current->format('Y-m-d'));
                $current->addDay();
            }
        }

        // Add OTA occupied dates (exclude checkout day)  
        foreach ($ota_dates as $booking) {
            $current = Carbon::parse($booking->arrival_date);
            $booking_end = Carbon::parse($booking->departure_date);

            // Only include dates that fall within our selected range
            // and exclude the checkout date
            while ($current->lt($booking_end) && $current->gte($start) && $current->lte($end)) {
                $occupied_dates->push($current->format('Y-m-d'));
                $current->addDay();
            }
        }

        // Get unique occupied dates count
        $unique_occupied_days = $occupied_dates->unique()->count();
        $occupancy = $total_days > 0 ? round(($unique_occupied_days / $total_days) * 100, 2) : 0;

        // Ensure occupancy never exceeds 100%
        $occupancy = min($occupancy, 100);

        // DEBUG: Log occupancy calculation
        \Log::info('Occupancy calculation', [
            'date_range' => $request->start_date . ' to ' . $request->end_date,
            'total_days_in_range' => $total_days,
            'unique_occupied_days' => $unique_occupied_days,
            'occupied_dates_list' => $occupied_dates->unique()->sort()->values()->toArray(),
            'total_nights_from_bookings' => $total_nights,
            'calculated_occupancy' => $occupancy,
            'livedin_bookings_count' => $livedin_dates->count(),
            'ota_bookings_count' => $ota_dates->count()
        ]);
        \Log::info('Final calculations', [
            'total_gbv_amount' => $total_gbv_amount,
            'total_nbv_amount' => $total_nbv_amount,
            'total_nights' => $total_nights,
            'total_days' => $total_days,
            'final_gbv' => $final_gbv,
            'final_nbv' => $final_nbv,
            'occupancy' => $occupancy
        ]);

        return response()->json([
            'base_price' => $base_price,
            'gbv' => $final_gbv,
            'adr' => $adr,
            'nbv' => $final_nbv,
            'occupancy' => $occupancy,
            'cancellations' => $total_cancellations,
            'discounts' => round($total_discounts, 2),
        ]);
    }

    public function updateRateAndRestriction(Request $request)
    {
        if (isset($request->dateRangeBulk)) {
            $dateRange = $request->input('dateRangeBulk');
            [$startDate, $endDate] = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        } else {
            $dateRange = $request->input('daterange');
            [$startDate, $endDate] = explode(' - ', $dateRange);
            // Parse the dates and format them as YYYY-MM-DD
            $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        }

        $last_date_calender = Calender::where('listing_id', $request->listing_id)->orderBy('calender_date', 'desc')->limit(1)->first();
        $last_date = Carbon::parse($last_date_calender->calender_date);
        $user_id = Auth::user()->id;
        // Add one day
        $last_date->addDay();
        $period = CarbonPeriod::create($last_date, $endDate);
        if ($endDate > $last_date->toDateString()) {

            foreach ($period as $date) {
                Calender::create([
                    'listing_id' => $request->listing_id,
                    'availability' => 1,
                    'max_stay' => $last_date_calender->max_stay,
                    'min_stay_through' => $last_date_calender->min_stay_through,
                    'rate' => $request->price,
                    'calender_date' => $date->toDateString()
                ]);
            }
        }
        $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
        $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
        $ch_room_type_id = $room_type->ch_room_type_id;
        $rate_plan_ch_id = $rate_plan->ch_rate_plan_id;
        $property = Properties::where('id', $rate_plan->property_id)->first();
        $property_id = $property->ch_property_id;
        if ($request->has('availability')) {

            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                        "values" => [
                            [
                                //                        'date' => '2024-11-21',
                                "date_from" => $startDate,
                                "date_to" => $endDate,
                                "property_id" => "$property_id",
                                "room_type_id" => "$ch_room_type_id",
                                "availability" => (int) $request->availability,
                            ],
                        ]
                    ]);
            if ($response->successful()) {
                $availability = $response->json();
                $block = null;
                if ($request->has('block_reason')) {
                    $block = $request->block_reason;
                }

                Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                    ->update(
                        ['availability' => (int) $request->availability, 'is_lock' => isset($request->availability) && $request->availability == 0 ? 1 : 0, 'updated_by' => $user_id, 'block_reason' => $block]
                    );

                // if($request->has('bcom_rm')) {
                // dd($restrictionDataBcom);
                $listing_airbnb = Listing::where('listing_id', $request->listing_id)->first();
                $listingRelations = ListingRelation::where('listing_id_airbnb', $listing_airbnb->id)->get();
                if ($listingRelations) {
                    foreach ($listingRelations as $listingRelation) {
                        $listing_Bcom = Listing::where('id', $listingRelation->listing_id_other_ota)->first();
                        $room_typeBcom = RoomType::where('listing_id', $listing_Bcom->listing_id)->first();

                        if (is_null($room_typeBcom)) {
                            continue;
                        }

                        $propertyBcom = Properties::where('id', $room_typeBcom->property_id)->first();
                        $rate_planBcom = RatePlan::where('listing_id', $listing_Bcom->listing_id)->first();

                        // For Maga Rental
                        if ($listingRelation->listing_type == 'Almosafer') {

                            $rate_plan_almosafer = RatePlan::where('listing_id', 'mr_' . $listing_Bcom->id)->first();
                            if (is_null($rate_plan_almosafer)) {
                                continue;
                            }

                            $room_type_almosafer = RoomType::where('listing_id', 'mr_' . $listing_Bcom->id)->first();
                            if (is_null($room_type_almosafer)) {
                                continue;
                            }

                            $avl = (int) $request->availability;

                            $restrict = Http::withHeaders([
                                'Authorization' => config('magarental.api_key'),
                            ])->post(config('magarental.base_url') . '/api/v1/restrictions', [
                                        'values' => [
                                            [
                                                'property_id' => $listing_Bcom->listing_id,
                                                'rate_plan_id' => $rate_plan_almosafer->mr_rate_plan_id,
                                                'date_from' => $startDate,
                                                'date_to' => $endDate,
                                                'rate' => round($request->price) * 100,
                                                'min_stay' => 1,
                                                'max_stay' => 730,
                                                'stopsell' => 0
                                            ]
                                        ]
                                    ]);

                            // echo 'Result: ' . $restrict->successful();

                            // print_r($restrict->json());

                            if ($restrict->successful()) {

                                $ota_price_url = config('magarental.base_url') . '/api/v1/ota_prices';

                                if (!empty($listing_Bcom->mr_ota_price_id)) {
                                    $ota_price_url .= '/' . $listing_Bcom->mr_ota_price_id;
                                }

                                $ota_prices = Http::withHeaders([
                                    'Authorization' => config('magarental.api_key'),
                                ])->put(
                                        $ota_price_url,
                                        [
                                            'PropertyId' => $listing_Bcom->listing_id ?? null,
                                            'Room_typeid' => $room_type_almosafer->mr_room_type_id ?? null,
                                            'OTA_inventory' => [
                                                [
                                                    'date_from' => $startDate,
                                                    'date_to' => $endDate,
                                                    'max_sell' => $avl == 1 ? 1 : 0,
                                                ]
                                            ],
                                            "Created_date" => date('Y-m-d h:i:s')
                                        ]
                                    );

                                if ($ota_prices->successful()) {
                                    $ota_prices_json = $ota_prices->json();
                                    $ota_price_id = $ota_prices_json['id'] ?? null;

                                    if (!empty($ota_price_id)) {
                                        $listing_Bcom->mr_ota_price_id = $ota_price_id;
                                        $listing_Bcom->save();
                                    }
                                }
                            }
                            continue;
                        }

                        // dd($property,$rate_planBcom,$listing_Bcom,$listingRelation,$restrictionData,$request->bcom_rm,$listingRelation);
                        // dd($restrictionDataDB);
                        $response = Http::withHeaders([
                            'user-api-key' => env('CHANNEX_API_KEY'),
                        ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                                    "values" => [
                                        [
                                            "date_from" => $startDate,
                                            "date_to" => $endDate,
                                            "property_id" => "$propertyBcom->ch_property_id",
                                            "room_type_id" => "$room_typeBcom->ch_room_type_id",
                                            "availability" => (int) $request->availability,
                                        ],
                                    ]
                                ]);
                        if ($response->successful()) {
                            $availability = $response->json();
                            // dd($availability );
                            $block = null;
                            if ($request->has('block_reason')) {
                                $block = $request->block_reason;
                            }

                            Calender::where('listing_id', $listing_Bcom->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                                ->update(
                                    ['availability' => (int) $request->availability, 'is_lock' => isset($request->availability) && $request->availability == 0 ? 1 : 0, 'updated_by' => $user_id, 'block_reason' => $block]
                                );

                            Log::info('availability resp', ['response' => $availability]);
                        } else {
                            $error = $response->body();
                        }

                    }
                }


                // }

                Log::info('availability resp', ['response' => $availability]);
            } else {
                $error = $response->body();
            }
        }

        $restrictionData = [
            "date_from" => $startDate,
            "date_to" => $endDate,
            "property_id" => "$property_id",
            "rate_plan_id" => "$rate_plan_ch_id", /*twin best rate*/
            'updated_by' => $user_id
        ];
        $restrictionDataDB = [
        ];
        if ($request->has('max_stay')) {
            $restrictionData["max_stay"] = (int) $request->max_stay;
            $restrictionDataDB["max_stay"] = (int) $request->max_stay;
        }
        if ($request->has('min_stay')) {
            $restrictionData["min_stay"] = (int) $request->min_stay;
            $restrictionDataDB["min_stay_through"] = (int) $request->min_stay;
        }
        if ($request->has('price')) {
            $restrictionData["rate"] = (int) $request->price * 100;
            $restrictionDataDB["rate"] = (int) $request->price;
        }


        // dd('stop');
        if (isset($request->price) && $request->price || isset($request->min_stay) && $request->min_stay || isset($request->max_stay) && $request->max_stay) {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                        "values" => [
                            $restrictionData
                        ]
                    ]);

            if ($response->successful()) {
                $restrictions = $response->json();
                Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                    ->update($restrictionDataDB);

                if (!empty($request->bcom_rm)) {
                    $restrictionDataBcom = $restrictionData;
                    // dd($restrictionDataBcom);
                    $listing_airbnb = Listing::where('listing_id', $request->listing_id)->first();
                    $listingRelation = ListingRelation::where('listing_id_airbnb', $listing_airbnb->id)->first();
                    $listing_Bcom = Listing::where('id', $listingRelation->listing_id_other_ota)->first();
                    $rate_planBcom = RatePlan::where('listing_id', $listing_Bcom->listing_id)->first();
                    $propertyBcom = Properties::where('id', $rate_planBcom->property_id)->first();
                    $restrictionDataBcom['property_id'] = $propertyBcom->ch_property_id;
                    $restrictionDataBcom['rate_plan_id'] = $rate_planBcom->ch_rate_plan_id;
                    $restrictionDataBcom['rate'] = ($restrictionDataBcom['rate'] * ($request->bcom_rm / 100)) + $restrictionDataBcom['rate'];
                    $restrictionDataBcomDB['rate'] = ($restrictionDataBcom['rate'] * ($request->bcom_rm / 100)) + $restrictionDataBcom['rate'];
                    // dd($property,$rate_planBcom,$listing_Bcom,$listingRelation,$restrictionData,$request->bcom_rm,$listingRelation);
                    // dd($restrictionDataDB);
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                                "values" => [
                                    $restrictionDataBcom
                                ]
                            ]);

                    if ($response->successful()) {
                        $restrictions = $response->json();

                        Calender::where('listing_id', $listing_Bcom->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                            ->update($restrictionDataBcomDB);
                        Log::info('rest resp', ['response' => $restrictions]);
                    } else {
                        $error = $response->body();
                        dd($error);
                    }
                }
                Log::info('rest resp', ['response' => $restrictions]);
            } else {
                $error = $response->body();
                dd($error);
            }
        }
        return redirect()->back();
    }
    public function updateListingCalender(Request $request)
    {
        $apiKey = env('CHANNEX_API_KEY');
        $channelId = $request->channel_id;
        $listingId = $request->listing_id;
        $eventDate = $request->eventDate;
        $availability = $request->availability;

        // Build the data for the POST request
        $data = [
            'request' => [
                'endpoint' => '/pricing_and_availability/standard/calendar_operations_requests',
                'method' => 'post',
                'payload' => [
                    'listing_id' => $listingId,
                    'operations' => [
                        [
                            'dates' => [$eventDate],
                            'availability' => $availability,
                        ],
                    ],
                ],
            ],
        ];
        $response = Http::withHeaders([
            'user-api-key' => $apiKey,
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channelId/action/api_proxy", $data);


        // Check for errors and handle appropriately
        if ($response->failed()) {
            // Log or report the error
            error_log('Error response: ' . $response->body());

            return response()->json([
                'error' => 'Request failed',
                'details' => $response->json(),
            ], 400); // 400 Bad Request
        }

        // Return a success response
        return response()->json([
            'message' => 'Request successful',
            'data' => $response->json(),
        ], 200); // 200 OK
    }
    public function updateListingCalenderBulk()
    {

    }

    public function get_calendar(Request $request)
    {

        $data['calendars'] = [];

        $calendars = new Calender();

        if (empty($request->property_uids)) {
            return $data;
        }

        // Get Previous 6 & Next 18 months date or filter specific dates
        $start_date = Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
        $end_date = Carbon::now()->addMonths(18)->endOfMonth()->toDateString();
        if ($request->filled(['start_date', 'end_date'])) {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }
        $calendars->whereBetween('calender_date', [$start_date, $end_date]);

        if ($request->filled('offset')) {
            $calendars->skip($request->offset);
        }

        if ($request->filled('limit')) {
            $calendars->limit($request->limit);
        }

        foreach ($request->property_uids as $property_uid) {
            $calender_data = $calendars->where('listing_id', $property_uid)->orderBy('calender_date', 'ASC')->get();

            if (!empty($calender_data)) {
                $total = 0;
                $entries_arr = [];
                $unique_date_arr = [];
                foreach ($calender_data as $calendar) {

                    if (in_array($calendar->calender_date, $unique_date_arr)) {
                        continue;
                    }

                    ++$total;
                    $entries_arr['entries'][] = [
                        'date' => $calendar->calender_date,
                        'availability' => $calendar->availability,
                        'pricing' => $calendar->rate,
                        'max_stay' => $calendar->max_stay,
                        'day' => !empty($calendar->calender_date) ? Carbon::parse($calendar->calender_date)->format('D') : '',
                    ];

                    $unique_date_arr[] = $calendar->calender_date;
                }

                if (!empty($entries_arr['entries'])) {
                    $entries_arr['propertyUid'] = $property_uid;
                    $entries_arr['total_calendar_data'] = $total;
                    $data['calendars'][] = $entries_arr;
                }
            }
        }
        return $data;
    }

    public function getBookingCalendar(Request $request)
    {
        $startDate = $request->filled('start_date') ? $request->start_date : Carbon::now()->subMonths(6)->startOfMonth()->toDateString();
        $endDate = $request->filled('end_date') ? $request->end_date : Carbon::now()->addMonths(18)->endOfMonth()->toDateString();

        $listing = Listing::findOrFail($request->listing_id);
        $listingId = $listing->listing_id;

        $source = strtolower($request->search_ota);
        $listingIdArr = [$listingId];
        // $source = $source === 'direct' ? 'host_booking' : $source;
        $listingRelation = ListingRelation::where('listing_id_airbnb', $listing->id)->get();
        if ($listingRelation) {
            foreach ($listingRelation as $it) {
                $listing_Bcom = Listing::where('id', $it->listing_id_other_ota)->first();

                if ($it->listing_type == 'Almosafer') {
                    $listingIdArr[] = $listing_Bcom->id;
                } else {
                    $listingIdArr[] = $listing_Bcom->listing_id;
                }
            }
        }
        // dd($listingIdArr);
        // Define booking queries
        $bookingsQuery = BookingOtasDetails::query()
            ->whereIn('listing_id', $listingIdArr)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('arrival_date', [$startDate, $endDate])
                    ->orWhereBetween('departure_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('arrival_date', '<=', $startDate)
                            ->where('departure_date', '>=', $endDate);
                    });
            });

        $bookingLivedInQuery = Bookings::query()
            ->where('listing_id', $listing->id)
            ->where('booking_status', '!=', 'cancelled')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('booking_date_start', [$startDate, $endDate])
                    ->orWhereBetween('booking_date_end', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('booking_date_start', '<=', $startDate)
                            ->where('booking_date_end', '>=', $endDate);
                    });
            });

        // Add booking counts
        $bookingCounts = [
            'livedin' => $bookingLivedInQuery->clone()->whereNotIn('booking_sources', ['host_booking', 'gathern'])
                ->count(),
            'airbnb' => $bookingsQuery->clone()->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                ['airbnb']
            )->count(),
            'booking_com' => $bookingsQuery->clone()->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                ['bookingcom']
            )->count(),
            'almosafer' => $bookingsQuery->clone()->where('ota_name', 'Almosafer')->count(),
            'vrbo' => $bookingsQuery->clone()->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                ['vrbo']
            )->count(),
            'direct' => $bookingLivedInQuery->clone()->where('booking_sources', 'host_booking')->count(),
            'gathern' => $bookingLivedInQuery->clone()->where('booking_sources', 'gathern')->count(),
        ];

        if (!empty($source) && $source !== 'all') {
            if (isset($source) && $source == 'booking_com') {
                $source = 'bookingcom';
            }

            if (!empty($source) && strtolower($source) == 'almosafer') {
                $source = 'almosafer';
            }

            if ($source == 'gathern') {
                $bookingLivedInQuery->where('booking_sources', 'gathern');
            } else if ($source == 'direct' || $source == 'host_booking') {
                $bookingLivedInQuery->where('booking_sources', 'host_booking');
            } else if ($source == 'livedin') {
                $excludedSources = ['host_booking', 'gathern'];
                $bookingLivedInQuery->whereNotIn('booking_sources', $excludedSources)
                    ->where('ota_name', $source);
            } else {
                $bookingLivedInQuery->where('ota_name', $source);
            }
            // else {
            //     $bookingLivedInQuery->where('booking_sources', $source);
            // }

            if ($source == 'almosafer') {
                $bookingsQuery->where('ota_name', 'Almosafer');
            } else {
                $bookingsQuery->whereRaw(
                    'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                    [$source === 'booking_com' ? 'booking.com' : $source]
                );
            }
        }

        // Fetch booking data
        $bookingLivedIn = $bookingLivedInQuery->get();
        $bookings = $bookingsQuery->get();

        // Map bookings
        $allBookings = array_merge(
            $bookingLivedIn->map(function ($item) use ($listing, $source) {
                return [
                    'listing_id' => $listing->id,
                    'booking_id' => $item->id,
                    'ota_name' => $item->booking_sources == 'host_booking' ? 'direct' : ($item->booking_sources == 'gathern' ? 'gathern' : ('livedin')),
                    // 'ota_name' => 'livedin',
                    'type' => 'livedin',
                    'arrival_date' => $item->booking_date_start,
                    'departure_date' => Carbon::parse($item->booking_date_end)->subDay()->toDateString(),
                ];
            })->toArray(),
            $bookings->map(function ($item) {

                if (strtolower($item->ota_name) == 'almosafer') {
                    $otaSource = strtolower($item->ota_name);
                } else {
                    $bookingJson = json_decode($item->booking_otas_json_details, true);
                    $otaSource = strtolower($bookingJson['attributes']['ota_name'] ?? '');
                }

                return [
                    'listing_id' => $item->listing_id,
                    'booking_id' => $item->id,
                    'ota_name' => $otaSource,
                    'arrival_date' => $item->arrival_date,
                    'type' => 'ota',
                    'departure_date' => Carbon::parse($item->departure_date)->subDay()->toDateString(),
                ];
            })->toArray()
        );

        // Fetch calendar data with pagination
        $calendars = Calender::query()
            ->where('listing_id', $listingId)
            ->whereBetween('calender_date', [$startDate, $endDate])
            ->orderBy('calender_date', 'ASC');

        $calenderData = $calendars->get();

        $calendarsWithBookings = $calenderData->map(function ($calendar) use ($allBookings) {
            $currentDate = $calendar->calender_date;
            $matchingBooking = collect($allBookings)->first(function ($booking) use ($currentDate) {
                return $currentDate >= $booking['arrival_date'] && $currentDate <= $booking['departure_date'];
            });

            return [
                'date' => $calendar->calender_date,
                'availability' => $calendar->availability,
                'is_blocked' => $calendar->is_blocked,
                'pricing' => $calendar->rate,
                'max_stay' => $calendar->max_stay,
                'day' => Carbon::parse($calendar->calender_date)->format('D'),
                'booking' => $matchingBooking ?? null,
            ];
        });

        $listingJson = json_decode($listing->listing_json, true);

        $cleaningFee = 0;
        $listingSetting = ListingSetting::where('listing_id', $listingId)->first();
        if (!empty($listingSetting['cleaning_fee'])) {
            $cleaningFee = $listingSetting['cleaning_fee'] ?? 0;
        }

        $data = [
            'id' => $listing->id,
            'listing_id' => $listingJson['id'] ?? $listingId,
            'name' => $listingJson['title'] ?? null,
            'cleaning_fee' => $cleaningFee,
            'calendars' => $calendarsWithBookings->isNotEmpty() ? $calendarsWithBookings : [],
            'bookings' => count($allBookings),
            'booking_counts' => $bookingCounts,
        ];

        $user = auth()->user();
        if (!empty($user->role_id) && $user->role_id === 2) {

            try {

                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();
                $listingJson = json_decode($listing->listing_json, true);

                $this->mixpanelService->trackEvent('Property Calendar Opened', [
                    'distinct_id' => $user->id,
                    'first_name' => $user->name,
                    'last_name' => $user->surname,
                    'email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city,
                    'host_type' => $user->hostType->module_name,
                    'LP_NAME' => $listingJson['title'] ?? 'Unnamed Listing'

                ]);

                $this->mixpanelService->setPeopleProperties($user->id, [
                    '$first_name' => $user->name,
                    '$last_name' => $user->surname,
                    '$email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city,
                    'host_type' => $user->hostType->module_name,
                    'LP_NAME' => $listingJson['title'] ?? 'Unnamed Listing'

                ]);


            } catch (\Exception $e) {


            }
        }

        return $data;
    }

    public function getMonthlyBookingCalendar(Request $request)
    {
        $user = auth()->user();


        // Define the start and end dates for the current month
        $startDate = Carbon::now()->startOfMonth()->toDateString();
        $endDate = Carbon::now()->endOfMonth()->toDateString();

        // Check if a specific listing ID is provided; otherwise, fetch data for all listings
        $search = $request->input('search_listing');
        $listings = Listing::when($search, function ($query, $search) {
            $query->where('listing_json->title', 'like', "%{$search}%");
        })->whereJsonContains('user_id', strval($user->id))->where('is_sync', 'sync_all');

        $listings = $listings->offset($request->offset ?? 0);
        $listings = $listings->limit($request->limit ?? 10);

        $listings = $listings->get();

        $allListingsData = [];
        $totalBookingCounts = [
            'livedin' => 0,
            'airbnb' => 0,
            'booking_com' => 0,
            'vrbo' => 0,
            'direct' => 0,
            'gathern' => 0,
            'almosafer' => 0
        ];

        $index = 0;
        foreach ($listings as $listing) {

            // $user_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
            // if (!in_array(auth()->user()->id, $user_arr)){
            //     continue;
            // }

            $listingId = $listing->listing_id;

            $source = strtolower($request->search_ota);
            $source = $source === 'direct' ? 'host_booking' : $source;
            $listingIdArr = [$listingId];
            // $source = $source === 'direct' ? 'host_booking' : $source;
            $listingRelation = ListingRelation::where('listing_id_airbnb', $listing->id)->get();
            if ($listingRelation) {
                foreach ($listingRelation as $it) {
                    $listing_Bcom = Listing::where('id', $it->listing_id_other_ota)->first();

                    if ($it->listing_type == 'Almosafer') {
                        $listingIdArr[] = $listing_Bcom->id;
                    } else {
                        $listingIdArr[] = $listing_Bcom->listing_id;
                    }

                }
            }
            // Define booking queries
            $bookingsQuery = BookingOtasDetails::query()
                ->whereIn('listing_id', $listingIdArr)
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('arrival_date', [$startDate, $endDate])
                        ->orWhereBetween('departure_date', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('arrival_date', '<=', $startDate)
                                ->where('departure_date', '>=', $endDate);
                        });
                });

            $bookingLivedInQuery = Bookings::query()
                ->where('listing_id', $listing->id)
                ->where('booking_status', '!=', 'cancelled')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('booking_date_start', [$startDate, $endDate])
                        ->orWhereBetween('booking_date_end', [$startDate, $endDate])
                        ->orWhere(function ($query) use ($startDate, $endDate) {
                            $query->where('booking_date_start', '<=', $startDate)
                                ->where('booking_date_end', '>=', $endDate);
                        });
                });

            // Add booking counts for the current listing
            $currentCounts = [
                'livedin' => $bookingLivedInQuery->clone()->whereNotIn('booking_sources', ['host_booking', 'gathern'])
                    ->count(),
                'airbnb' => $bookingsQuery->clone()->whereRaw(
                    'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                    ['airbnb']
                )->count(),
                'booking_com' => $bookingsQuery->clone()->whereRaw(
                    'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                    ['bookingcom']
                )->count(),
                'almosafer' => $bookingsQuery->clone()->where('ota_name', 'Almosafer')->count(),
                'vrbo' => $bookingsQuery->clone()->whereRaw(
                    'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                    ['vrbo']
                )->count(),
                'direct' => $bookingLivedInQuery->clone()->where('booking_sources', 'host_booking')->count(),
                'gathern' => $bookingLivedInQuery->clone()->where('booking_sources', 'gathern')->count(),
            ];

            if (!empty($source) && $source !== 'all') {
                if (isset($source) && $source == 'booking_com') {
                    $source = 'bookingcom';
                }

                if (!empty($source) && strtolower($source) == 'almosafer') {
                    $source = 'almosafer';
                }

                if ($source == 'gathern') {
                    $bookingLivedInQuery->where('booking_sources', 'gathern');
                } else if ($source == 'direct' || $source == 'host_booking') {
                    $bookingLivedInQuery->where('booking_sources', 'host_booking');
                } else if ($source == 'livedin') {
                    $excludedSources = ['host_booking', 'gathern'];
                    $bookingLivedInQuery->whereNotIn('booking_sources', $excludedSources)
                        ->where('ota_name', $source);
                } else {
                    $bookingLivedInQuery->where('ota_name', $source);
                }
                // else {
                //     $bookingLivedInQuery->where('booking_sources', $source);
                // }

                if ($source == 'almosafer') {
                    $bookingsQuery->where('ota_name', 'Almosafer');
                } else {
                    $bookingsQuery->whereRaw(
                        'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                        [$source === 'booking_com' ? 'booking.com' : $source]
                    );
                }


                if ($index === 0) {
                    if (!empty($user->role_id) && $user->role_id === 2) {

                        try {

                            $userUtility = new UserUtility();
                            $location = $userUtility->getUserGeolocation();

                            $this->mixpanelService->trackEvent('Calendar Filters Used', [
                                'distinct_id' => $user->id,
                                'first_name' => $user->name,
                                'last_name' => $user->surname,
                                'email' => $user->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $user->country,
                                'db_city' => $user->city,
                                'host_type' => $user->hostType->module_name

                            ]);

                            $this->mixpanelService->setPeopleProperties($user->id, [
                                '$first_name' => $user->name,
                                '$last_name' => $user->surname,
                                '$email' => $user->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $user->country,
                                'db_city' => $user->city,
                                'host_type' => $user->hostType->module_name

                            ]);


                        } catch (\Exception $e) {


                        }
                    }
                }
            }

            // Aggregate counts into totalBookingCounts
            foreach ($currentCounts as $key => $value) {
                $totalBookingCounts[$key] += $value;
            }

            // Fetch calendar and booking data
            $bookingLivedIn = $bookingLivedInQuery->get();
            $bookings = $bookingsQuery->get();

            $allBookings = array_merge(
                $bookingLivedIn->map(function ($item) use ($listing, $source) {
                    return [
                        'listing_id' => $listing->id,
                        'booking_id' => $item->id,
                        'ota_name' => $item->booking_sources == 'host_booking' ? 'direct' : ($item->booking_sources == 'gathern' ? 'gathern' : ('livedin')),
                        'type' => 'livedin',
                        'arrival_date' => $item->booking_date_start,
                        'departure_date' => Carbon::parse($item->booking_date_end)->subDay()->toDateString(),
                    ];
                })->toArray(),
                $bookings->map(function ($item) {

                    if (strtolower($item->ota_name) == 'almosafer') {
                        $otaSource = strtolower($item->ota_name);
                    } else {
                        $bookingJson = json_decode($item->booking_otas_json_details, true);
                        $otaSource = strtolower($bookingJson['attributes']['ota_name'] ?? '');
                    }

                    return [
                        'listing_id' => $item->listing_id,
                        'booking_id' => $item->id,
                        'ota_name' => $otaSource,
                        'arrival_date' => $item->arrival_date,
                        'type' => 'ota',
                        'departure_date' => Carbon::parse($item->departure_date)->subDay()->toDateString(),
                    ];
                })->toArray()
            );

            $calenderData = Calender::query()
                ->where('listing_id', $listingId)
                ->whereBetween('calender_date', [$startDate, $endDate])
                ->orderBy('calender_date', 'ASC');

            $calendarsWithBookings = $calenderData->get()->map(function ($calendar) use ($allBookings) {
                $currentDate = $calendar->calender_date;
                $matchingBooking = collect($allBookings)->first(function ($booking) use ($currentDate) {
                    return $currentDate >= $booking['arrival_date'] && $currentDate <= $booking['departure_date'];
                });

                return [
                    'date' => $calendar->calender_date,
                    'availability' => $calendar->availability,
                    'is_blocked' => $calendar->is_blocked,
                    'pricing' => $calendar->rate,
                    'max_stay' => $calendar->max_stay,
                    'day' => Carbon::parse($calendar->calender_date)->format('D'),
                    'booking' => $matchingBooking ?? null,
                ];
            });
            $listingJson = json_decode($listing->listing_json, true);
            $listing_id = $listingJson["id"] ?? $listing->listing_id;
            $allListingsData[] = [
                "id" => $listing->id,
                "name" => $listingJson['title'] ?? null,
                'listing_id' => $listing_id,
                'calendars' => $calendarsWithBookings->isNotEmpty() ? $calendarsWithBookings : [],
                // 'bookings' => $allBookings,
            ];

            $index++;
        }

        if (!empty($user->role_id) && $user->role_id === 2) {

            try {

                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Calendar Module Opened', [
                    'distinct_id' => $user->id,
                    'first_name' => $user->name,
                    'last_name' => $user->surname,
                    'email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city

                ]);

                $this->mixpanelService->setPeopleProperties($user->id, [
                    '$first_name' => $user->name,
                    '$last_name' => $user->surname,
                    '$email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city

                ]);

            } catch (\Exception $e) {


            }
        }

        return [
            'total_booking_counts' => $totalBookingCounts,
            'listings' => $allListingsData,
        ];
    }

    private function getUserOS()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];


        $osArray = [
            'Windows' => 'Windows',
            'Macintosh' => 'Mac OS',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
            'Android' => 'Android',
            'Linux' => 'Linux',
            'PostmanRuntime' => 'Postman',
            'okhttp' => 'Android',
        ];


        if (stripos($userAgent, 'LivedInMobileApp') !== false && stripos($userAgent, 'CFNetwork') !== false && stripos($userAgent, 'Darwin') !== false) {

            return 'iOS';
        }


        foreach ($osArray as $key => $os) {
            if (stripos($userAgent, $key) !== false) {
                return $os;
            }
        }


        return 'Unknown';
    }
    function get_snippet($str, $wordCount = 10)
    {
        return implode(
            '',
            array_slice(
                preg_split(
                    '/([\s,\.;\?\!]+)/',
                    $str,
                    $wordCount * 2 + 1,
                    PREG_SPLIT_DELIM_CAPTURE
                ),
                0,
                $wordCount * 2 - 1
            )
        );
    }

    public function blockCalendar(Request $request)
    {
        $validatedData = $request->validate([
            'dates' => 'required|array|min:1',
            'listing_id' => 'required',
            'availability' => 'required|in:0,1',
        ]);
        $listing = Listings::where('id', $request->listing_id)->first();
        if (is_null($listing)) {
            return response()->json(['error' => 'Listing not found'], 500);
        }

        $dates = $request['dates'];
        $firstDate = reset($dates);
        $lastDate = end($dates);
        $listing_json = json_decode($listing->listing_json);
        // dd($listing_json);
        $emailData['host_name'] = auth()->user()->name . ' ' . auth()->user()->surname;
        // dd($this->get_snippet($listing_json->title, 2));
        $emailData['listing_title'] = $listing_json->title;
        $emailData['booking_date_start'] = $firstDate;
        $emailData['booking_date_end'] = $lastDate;
        $emailData['status'] = 'block';

        // dd($request->availability);

        $request->availability == 0 ? $emailData['status'] = 'block' : $emailData['status'] = 'unblock';
        $emailData['status'] == 'block' ? $subjectStatus = 'blocked' : $subjectStatus = 'unblocked';
        $emailData['subject'] = $this->get_snippet($listing_json->title, 2) . ' | ' . $subjectStatus . ' | ' . $emailData['booking_date_start'] . ' to ' . $emailData['booking_date_end'];
        // dd(auth()->user());
        // dd($request);
        if (auth()->user()->host_type_id == 2 && auth()->user()->able_to_block_calender == 1) {
            $emailData['status'] == 'block' ? $subjectStatus = 'block' : $subjectStatus = 'unblock';
            $emailData['subject'] = auth()->user()->name . ' ' . auth()->user()->surname . ' request to' . ' ' . $subjectStatus . ' ' . $this->get_snippet($listing_json->title, 2) . ' from ' . $emailData['booking_date_start'] . ' to ' . $emailData['booking_date_end'];

            // dd($emailData);
            BlockDateRequest::create([
                'host_id' => auth()->user()->id,
                'listing_id' => $request->listing_id,
                'start_date' => $firstDate,
                'end_date' => $lastDate,
                'availability' => (int) $request->availability,
                'status' => 'pending',
            ]);
            try {
                $notification = NotificationM::create([
                    'notification_type_id' => 3, // Block Dates
                    'module' => "Block Dates",
                    'module_id' => $request->listing_id,
                    'title' => $listing_json->title,
                    'message' => "Block date request has been generated from " . $firstDate . ' to ' . $lastDate,
                    'url' => route('block.date.request'),
                    'is_seen_by_all' => false
                ]);
            } catch (\Exception $e) {
                // You can log the error or handle it as needed
                Log::error('Failed to create notification: ' . $e->getMessage());
            }
            Mail::to(['adnan.anwar@livedin.co', 'support@livedin.co'])->send(new BlockDateRequestEmail($emailData));

            return response()->json(["message" => "success"], 200);
            // Mail::to(['adnan.anwar@livedin.co', 'support@livedin.co'])->send(new BlockDateRequestEmail($emailData));
        }
        if (auth()->user()->host_type_id == 2) {
            return response()->json(["message" => "success"], 200);
        }




        $room_type = RoomType::where('listing_id', $listing->listing_id)->first();
        if (is_null($room_type)) {
            return response()->json(['error' => 'Room type not found'], 500);
        }

        $property = Properties::where('id', $room_type->property_id)->first();
        if (is_null($property)) {
            return response()->json(['error' => 'Property not found'], 500);
        }

        $dates = [];
        foreach ($request->dates as $date) {
            array_push($dates, [
                "date_from" => $date,
                "date_to" => $date,
                "property_id" => $property->ch_property_id,
                "room_type_id" => $room_type->ch_room_type_id,
                "availability" => (int) $request->availability,
            ]);
        }

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    "values" => $dates
                ]);
        if ($response->successful()) {
            $availability = $response->json();
            Calender::where('listing_id', $listing->listing_id)
                ->whereIn('calender_date', $request->dates)
                ->update(
                    [
                        // 'availability' => $request->availability,
                        'is_blocked' => $request->availability == 1 ? 0 : 1,
                        'is_lock' => $request->availability == 1 ? 0 : 1,
                        'block_reason' => 'Host block the date'
                    ]
                );

            // dd($emailData);
            Mail::to(['adnan.anwar@livedin.co', 'support@livedin.co'])->send(new BlockDateEmail($emailData));

            $user = auth()->user();
            if (!empty($user->role_id) && $user->role_id === 2) {

                try {

                    $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation();
                    $listingJson = json_decode($listing->listing_json, true);

                    $this->mixpanelService->trackEvent('Dates Blocked', [
                        'distinct_id' => $user->id,
                        'first_name' => $user->name,
                        'last_name' => $user->surname,
                        'email' => $user->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $user->country,
                        'db_city' => $user->city,
                        'host_type' => $user->hostType->module_name,
                        'LP_NAME' => $listingJson['title'] ?? 'Unnamed Listing'

                    ]);

                    $this->mixpanelService->setPeopleProperties($user->id, [
                        '$first_name' => $user->name,
                        '$last_name' => $user->surname,
                        '$email' => $user->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $user->country,
                        'db_city' => $user->city,
                        'host_type' => $user->hostType->module_name,
                        'LP_NAME' => $listingJson['title'] ?? 'Unnamed Listing'

                    ]);


                } catch (\Exception $e) {


                }
            }


            return response()->json(["message" => "success"], 200);
        }




        return response()->json(["error" => "Something went wrong!"], 500);
    }

    public function multi_calendar()
    {
        return view('Admin.calender-management.multi_calendar');
    }

    public function update_price(Request $request)
    {
        $request->validate([
            'property_uids' => 'required|array|min:1',
            'from' => 'required|string',
            'to' => 'required|string',
            // 'price' => 'required|numeric|min:1',
            'availability' => 'required|boolean',
            'min_stay' => 'required|numeric|min:1',
            'max_stay' => 'required|numeric|min:1'
        ]);

        $pricing_arr = [];
        $listing_id_arr = [];
        $availability_arr = [];
        foreach ($request->property_uids as $key => $property_uid) {

            $rate_plan = RatePlan::where('listing_id', $property_uid)->first();
            if (is_null($rate_plan)) {
                return response()->json(['error' => 'Rate Plan not found'], 500);
            }

            $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
            if (is_null($room_type)) {
                return response()->json(['error' => 'Room Type not found'], 500);
            }

            $property = Properties::where('id', $room_type->property_id)->first();
            if (is_null($property)) {
                return response()->json(['error' => 'Property not found'], 500);
            }

            $property_id = $property->ch_property_id;
            $listing_id_arr[] = $property_uid;

            // Availability Update
            $availability_arr = [
                "date_from" => $request->from,
                "date_to" => $request->to,
                "property_id" => "$property_id",
                "room_type_id" => "$room_type->ch_room_type_id",
                "availability" => (int) $request->availability
            ];

            // Pricing Update
            if (isset($request->price) && $request->price != "") {
                $pricing_arr[] = [
                    "date_from" => $request->from,
                    "date_to" => $request->to,
                    "property_id" => "$property_id",
                    "rate_plan_id" => "$rate_plan->ch_rate_plan_id",
                    // 'min_stay' => (int) $request->min_stay,
                    'min_stay_through' => (int) $request->min_stay,
                    'max_stay' => (int) $request->max_stay,
                    'rate' => $request->price * 100,
                    'updated_by' => Auth::check() ? (int) Auth::user()->id : null
                ];
            }
        }

        // Availability Post Request
        $calendar_update_arr = [];
        $availability_flag = $pricing_flag = false;
        if (!empty($availability_arr)) {

            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                        "values" => [
                            $availability_arr
                        ]
                    ]);

            // Check if a warning exists, then return the warning
            $availability_response = $response->json();
            if (!empty($availability_response['meta']['warnings'][0]['warning'])) {
                Log::info('Availability Warning Error:', ['response' => $response->body()]);
                return response()->json(['error' => $response->body()], 400);
            }

            if ($response->successful()) {

                $calendar_update_arr = [
                    'availability' => !empty($request->availability) ? 1 : 0,
                    'is_lock' => !empty($request->is_lock) ? 1 : 0,
                    'block_reason' => $request->filled('block_reason') ? $request->block_reason : null,
                    'max_stay' => $request->max_stay,
                    'min_stay_through' => $request->min_stay,
                    'updated_by' => Auth::check() ? Auth::user()->id : null
                ];

                $availability_flag = true;
                Log::info('Availability Response:', ['response' => $response->json()]);
            } else {
                Log::info('Availability Error:', ['response' => $response->body()]);
                return response()->json(['error' => $response->body()], 400);
            }
        }

        // Pricing Post Request
        if (!empty($pricing_arr)) {

            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                        "values" =>
                            $pricing_arr
                    ]);

            // Check if a warning exists, then return the warning
            $pricing_response = $response->json();
            if (!empty($pricing_response['meta']['warnings'][0]['warning'])) {
                Log::info('Price Update Warning Error:', ['response' => $response->body()]);
                return response()->json(['error' => $response->body()], 400);
            }

            if ($response->successful()) {

                $calendar_update_arr["rate"] = $request->price;

                $pricing_flag = true;
                Log::info('Price Update Response:', ['response' => $response->json()]);
            } else {
                Log::info('Price Update Error:', ['response' => $response->body()]);
                return response()->json(['error' => $response->body()], 400);
            }
        }

        // Calendar Update
        if (!empty($calendar_update_arr) && !empty($listing_id_arr)) {

            Calender::whereIn('listing_id', $listing_id_arr)
                ->whereBetween('calender_date', [$request->from, $request->to])
                ->update($calendar_update_arr);

            $result = [
                'message' => 'Availability & Price successfully updated',
                'availability_success' => $availability_flag,
                'pricing_success' => $pricing_flag
            ];

            Log::info('Availability & Price successfully updated: ', ['response' => json_encode($result)]);

            return response()->json($result, 200);
        }

        return response()->json(['error' => 'Failed to update price. Please try again.'], 500);
    }
}

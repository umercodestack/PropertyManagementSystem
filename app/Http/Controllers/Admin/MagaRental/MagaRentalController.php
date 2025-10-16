<?php

namespace App\Http\Controllers\Admin\MagaRental;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Auth;

use Illuminate\Support\Facades\{
    Http,
    Log,
    DB
};

use App\Models\{
    Listing,
    Bookings,
    ChurnedProperty,
    BookingOtasDetails,
    Properties,
    RatePlan,
    RoomType,
    Calender,
    Listings,
    ListingSetting,
    ListingRelation,
    MrBranch
};
use App\Http\Controllers\Admin\BookingManagement\BookingManagementController;
use App\Services\StoreProcedureService;

class MagaRentalController extends Controller
{
    
    // public function __construct()
    // {
    //     $this->middleware('permission');
    // }
    
    public function get_property_by_id(Request $request)
    {
        die;

        try {

            if(empty($request->property_id) || empty($request->listing_id_airbnb)){
                return redirect()->back()->with('error', 'Property id or listing id airbnb is required');
            }

            echo $request->property_id;die;

            // $airbnb_listing = Listing::find($request->listing_id_airbnb);
            $airbnb_user_id = 0; //json_encode($airbnb_listing->user_id) ?? 0;
            
            $mr_res = Http::withHeaders([
                'Authorization' => config('magarental.api_key'),
            ])->get(config('magarental.base_url') . "/api/v1/properties/{$request->property_id}");

            if($mr_res->successful()){

                // print_r($mr_res->json()); die;

                $mr_json = $mr_res->json();

                print_r($mr_json);die;

                $property_response = $mr_json['data']['attributes'] ?? null;

                // $response['content']['photos'][0]['room_type_id'];

                if(empty($response['id'])){
                    return redirect()->back()->with('error', 'Property ID not found');
                }

                if(empty($response['is_active'])){
                    return redirect()->back()->with('error', 'Property is not active from maga rental');
                }

                $property_exist = Properties::where('ch_property_id', $request->property_id)->exists();
                if($property_exist){
                    return redirect()->back()->with('error', 'Property already exists');
                }

                $listing = Listing::create([
                    'user_id' => $airbnb_user_id,
                    'listing_id' => $response['id'],
                    'listing_json' => json_encode(['data'=>'almosafer']),
                    'channel_id' => 1,
                ]);

                if(empty($listing->id)){
                    return redirect()->back()->with('error', 'Listing can not be create');
                }

                $property = Properties::create([
                    'title' => $response['title'] ?? null,
                    'currency' => $response['currency'] ?? 'USD',
                    'email' => $response['email'] ?? null,
                    'country' => $response['country'] ?? null,
                    'city' => $response['city'] ?? null,
                    'user_id' => $airbnb_user_id,
                    'ch_property_id' => $response['id'],
                    'ch_group_id' => 1,
                    'group_id' => 1,
                ]);

                $room_type = RoomType::create([
                    'user_id' => $airbnb_user_id,
                    'listing_id' => $listing->listing_id,
                    'property_id' => $property->id,
                    'title' => $response['title'] ?? null,
                    'count_of_rooms' => 1,
                    'occ_adults' => 5,
                    'occ_children' => 0,
                    'occ_infants' => 0,
                    'ch_room_type_id' => 1
                ]);

                $rate_plan = RatePlan::create([
                    'user_id' => $airbnb_user_id,
                    'listing_id' => $listing->listing_id,
                    'property_id' => $property->id,
                    'room_type_id' => $room_type->id,
                    'title' => $response['title'] ?? null,
                    'occupancy' => 5,
                    'is_primary' => true,
                    'rate' => 1,
                    'ch_rate_plan_id' => 1
                ]);

                ListingSetting::create([
                    'listing_id' => $listing->id,
                    // 'rate_plan_id' => $ch_rate_plan_id,
                    'listing_currency' => 'SAR',
                    'instant_booking' => 'everyone',
                    'default_daily_price' => 1,
                ]);

                ListingRelation::create([
                    'listing_id_airbnb' => $request->listing_id_airbnb,
                    'listing_id_other_ota' => $listing->id,
                    'listing_type' => 'Almosafer',
                ]);


                // Disabled - Calendar sync
                // $start_date = Carbon::today()->toDateString();
                // $end_date = Carbon::today()->addDays(500)->toDateString();

                // $restriction = Http::withHeaders([
                //     'Authorization' => config('magarental.api_key'),
                // ])->get(config('magarental.base_url') . '/api/v1/restrictions', [
                //     'filter' => [
                //         'property_id' => $request->property_id,
                //         'date' => [
                //             'gte' => $start_date,
                //             'lte' => $end_date,
                //         ],
                //         'restrictions' => 'rate',
                //     ]
                // ]);

                // if($restriction->successful()){
                
                //     print_r($restriction->successful());

                //     print_r($restriction->json());

                //     $restriction_json = $restriction->json();

                //     if(!empty($restriction_json['data'])){
                //         // return response()->json(['error' => 'Restriction data not found']);

                //         // $restriction_response = $restriction_json['data']['attributes'] ?? null;

                //         $rate_plan_id = array_key_first($restriction_json['data']);

                //         if(!empty($rate_plan_id) && !empty($restriction_json['data'][$rate_plan_id])){

                //             $dates_rates = $restriction_json['data'][$rate_plan_id];

                //             $values = [];
                //             foreach ($dates_rates as $date => $details) {
                //                 if(!empty($date) && !empty($details['rate'])){

                //                     $values[] = [
                //                         'listing_id' => (int) $listing->listing_id,
                //                         'availability' => 1,
                //                         'max_stay' => 730,
                //                         'min_stay_through' => 1,
                //                         'rate' => $details['rate'],
                //                         'calender_date' => $date,
                //                         'created_at' => now(),
                //                         'updated_at' => now()
                //                     ];
                //                 }
                //             }

                //             if(!empty($values)){
                //                 Calender::insert($values);
                //             }
                //         }
                //     }
                // }

                // echo 'Property has been imported'; die;

                return redirect()->back()->with('success', 'Property has been imported');

            } else {
                $error = $mr_res->body();

                print_r($error); die;
            }
        
        } catch (\Exception $ex) {
            print_r($ex->getMessage());
        }
    }

    public function sync_booking(Request $request){

        // print_r($request->all());die;

        try {

            $this->almosaferLog("*************** ALMOSAFER BOOKING START ***************");

            $this->almosaferLog("Booking JSON: " . json_encode($request->all()));

            $whitelistedIps = [
                '176.9.0.238', // Maga Rental ip
                '158.69.67.198', // Maga Rental ip
                '110.93.226.46' // Livedin ip
            ];

            $clientIp = $request->ip();

            if (!in_array($clientIp, $whitelistedIps)) {

                $this->almosaferLog("*************** MR Unauthorized Access ***************");
                $this->almosaferLog("Client IP: " . $clientIp);

                $this->almosaferLog("*************** ALMOSAFER BOOKING END ***************");

                return response()->json(['success' => false, 'error' => 'Unauthorized Access', 'ip' => $clientIp], 403);
            }

            $bookingController = new BookingManagementController(app(StoreProcedureService::class));

            $riyadhDateTime = Carbon::now('Asia/Riyadh')->format('Y-m-d H:i:s');

            if(empty($request['attributes'])){
                return response()->json([
                    'success' => false,
                    'message' => 'Attributes key not found from maga rental',
                    'datetime' => $riyadhDateTime
                ]);
            }

            $record = $request['attributes'];

            if(empty($record['booking_id']) || empty($record['property_id'])){
                return response()->json([
                    'success' => false,
                    'message' => 'Booking id or Property id not found from maga rental',
                    'datetime' => $riyadhDateTime
                ]);
            }

            $name = $record['customer']['name'] ?? '';
            $name .= ' ' . $record['customer']['surname'] ?? '';

            $arrival_date = $record['arrival_date'] ?? null;
            $departure_date = $record['departure_date'] ?? null;

            $listing_id = 0;
            if(!empty($record['rooms'][0]['room_type_id'])){
                $mr_room_type_id = $record['rooms'][0]['room_type_id'];
                $room_type = RoomType::where('mr_room_type_id', $mr_room_type_id)->first();
                $listing_id = !is_null($room_type) ? str_replace('mr_', '', $room_type->listing_id) : 0;
            }

            // Booking Cancelled
            if(strtolower($record['status']) == 'cancelled'){

                // UnBlock Channex Calendar
                if(!empty($listing_id)){

                    $almosafer_listing_relation = ListingRelation::where('listing_id_other_ota', $listing_id)
                    ->where('listing_type', 'Almosafer')
                    ->first();

                    if(!is_null($almosafer_listing_relation)){

                        $endDate = Carbon::parse($departure_date);
                        $endDate = $endDate->subDay();
                        $endDate = $endDate->toDateString();
                        
                        $bookingController->blockAvailability($almosafer_listing_relation->listing_id_airbnb, $arrival_date, $endDate, 1);

                        // Block Almosafer Calendar
                        $get_listing_airbnb = Listing::find($almosafer_listing_relation->listing_id_airbnb);
                        if(!is_null($get_listing_airbnb)){
                            $almosafer_resp = $this->almosafer_block_calendar($get_listing_airbnb->listing_id, $arrival_date, $endDate, 1);
                        }
                    }
                }
            }

            $get_booking = BookingOtasDetails::where('booking_id', 'MR-'.$record['booking_id'])->first();
            if(!is_null($get_booking)){
                $get_booking->guest_name = $name;
                $get_booking->amount = $record['amount'] ?? $get_booking->amount;
                $get_booking->ota_commission = $record['ota_commission'] ?? $get_booking->ota_commission;
                $get_booking->status = ucfirst($record['status']) ?? $get_booking->status;
                $get_booking->adults = $record['occupancy']['adults'] ?? $get_booking->adults;
                $get_booking->children = $record['occupancy']['children'] ?? $get_booking->children;
                $get_booking->save();

                $this->almosaferLog("*************** ALMOSAFER BOOKING END ***************");

                return response()->json([
                    'success' => true,
                    'message' => 'Bookings updated successfully.',
                    'datetime' => $riyadhDateTime
                ]);
            }

            $adults = $record['occupancy']['adults'] ?? 0;
            $children = $record['occupancy']['children'] ?? 0;
            $rooms = 1;

            $notes = $record['notes'] ?? '';
            if(!empty($record['VCCnotes'])){
                $notes .= ' VCC Notes: '. $record['VCCnotes'] ?? '';
            }

            $booking = [
                'channel_id'      => 1,
                'listing_id'      => $listing_id,
                'booking_id'      => !empty($record['booking_id']) ? 'MR-'.$record['booking_id'] : null,
                'unique_id'       => $record['ota_reservation_code'] ?? null,
                'property_id'     => $record['property_id'] ?? null,
                'ota_name'        => $record['ota_name'] ?? null,
                'status'          => ucfirst($record['status']) ?? null,
                'arrival_date'    => $arrival_date,
                'departure_date'  => $departure_date,
                'adults'          => $adults,
                'children'        => $children,
                'rooms'           => $rooms,
                'amount'          => $record['amount'] ?? 0,
                'discount'        =>  0,
                'promotion'       =>  0,
                'cleaning_fee'    =>  0,
                'ota_commission'  => $record['ota_commission'] ?? 0,
                'guest_name'      => $name,
                'guest_email'     => $record['customer']['mail'] ?? null,
                'guest_phone'     => $record['customer']['phone'] ?? null,
                'booking_notes'   => $notes,
                'booking_otas_json_details' => json_encode(['name'=>'almosafer']),
            ];

            // print_r($booking);die;

            BookingOtasDetails::insert($booking);

            // Block Channex Calendar
            if(!empty($listing_id)){

                $almosafer_listing_relation = ListingRelation::where('listing_id_other_ota', $listing_id)
                ->where('listing_type', 'Almosafer')
                ->first();

                if(!is_null($almosafer_listing_relation)){

                    $endDate = Carbon::parse($departure_date);
                    $endDate = $endDate->subDay();
                    $endDate = $endDate->toDateString();

                    $bookingController->blockAvailability($almosafer_listing_relation->listing_id_airbnb, $arrival_date, $endDate);

                    // Block Almosafer Calendar
                    $get_listing_airbnb = Listing::find($almosafer_listing_relation->listing_id_airbnb);
                    if(!is_null($get_listing_airbnb)){
                        $almosafer_resp = $this->almosafer_block_calendar($get_listing_airbnb->listing_id, $arrival_date, $endDate, 0);
                    }
                }
            }

            $this->almosaferLog("*************** ALMOSAFER BOOKING END ***************");

            return response()->json([
                'success' => true,
                'message' => 'Bookings created successfully.',
                'datetime' => $riyadhDateTime
            ]);

        } catch (\Exception $ex) {

            $this->almosaferLog("MR ERROR: " . $ex->getMessage());

            $this->almosaferLog("*************** ALMOSAFER BOOKING END ***************");

            return response()->json([
                'success' => false,
                'message' => json_encode($ex->getMessage()),
                'datetime' => $riyadhDateTime
            ]);

            // print_r($ex->getMessage());
        }
    }

    function almosafer_block_calendar($airbnb_listing_id, $start_date, $end_date, $availability){
        try{

            $get_listing_airbnb = Listing::where('listing_id', $airbnb_listing_id)->first();
            $almosafer_listing_relation = ListingRelation::where('listing_id_airbnb', $get_listing_airbnb->id)
            ->where('listing_type', 'Almosafer')
            ->first();

            if(is_null($almosafer_listing_relation)){
                return false;
            }

            $almosafer_listing = Listing::where('id', $almosafer_listing_relation->listing_id_other_ota)->first();
            if(is_null($almosafer_listing)){
                return false;
            }

            $almosafer_rate_plan = RatePlan::where('listing_id', 'mr_'.$almosafer_listing->id)->first();
            if(is_null($almosafer_rate_plan)){
                return false;
            }

            $almosafer_room_type = RoomType::where('listing_id', 'mr_'.$almosafer_listing->id)->first();
            if(is_null($almosafer_room_type)){
                return false;
            }

            $ota_price_url = config('magarental.base_url') . '/api/v1/ota_prices';

            if(!empty($almosafer_listing->mr_ota_price_id)){
                $ota_price_url .= '/'.$almosafer_listing->mr_ota_price_id;
            }

            $ota_prices = Http::withHeaders([
                'Authorization' => config('magarental.api_key'),
            ])->put($ota_price_url,
                [
                    'PropertyId' => $almosafer_listing->listing_id ?? null,
                    'Room_typeid' => $almosafer_room_type->mr_room_type_id ?? null,
                    'OTA_inventory' => [
                        [
                            'date_from' => $start_date,
                            'date_to' => $end_date,
                            'max_sell' => $availability,
                        ]
                    ],
                    "Created_date" => date('Y-m-d h:i:s')
                ]
            );

            if ($ota_prices->successful()){
                $ota_prices_json = $ota_prices->json();
                $ota_price_id = $ota_prices_json['id'] ?? null;

                if(!empty($ota_price_id)){
                    $almosafer_listing->mr_ota_price_id = $ota_price_id;
                    $almosafer_listing->save();
                }

                $this->almosaferLog("******** Almosafer Calendar Blocked START ********");
                $this->almosaferLog("Airbnb Listing ID: " . $get_listing_airbnb->id);
                $this->almosaferLog("Almosafer Listing ID: " . $almosafer_listing->id);
                $this->almosaferLog("Start Date: $start_date");
                $this->almosaferLog("End Date: $end_date");
                $this->almosaferLog("******** Almosafer Calendar Blocked END ********");
            }

        } catch (\Exception $ex) {

            $this->almosaferLog("******** Almosafer Calendar Blocked ERROR START ********");
            $this->almosaferLog("Almosafer ERROR: " . $ex->getMessage());
            $this->almosaferLog("******** Almosafer Calendar Blocked ERROR END ********");

            return false;
        }
    }

    // public function fetch_bookings(){

    //     try {

    //         $mr_property = Http::withHeaders([
    //             'Authorization' => config('magarental.api_key'),
    //         ])->get(config('magarental.base_url') . "/api/v1/booking_revisions/feed");

    //         if($mr_property->successful()){

    //             // print_r($mr_property->json()); die;

    //             $mr_json = $mr_property->json();

    //             $data = $mr_json['data'] ?? null;

    //             if(empty($data)){
    //                 return response()->json(['error' => 'Booking not found from maga rental']);
    //             }

    //             $this->almosaferLog("*************** MR BOOKING START ***************");

    //             $all_bookings = [];
    //             foreach($data as $d){
    //                 if (empty($d['attributes'])) {
    //                     return response()->json(['error' => 'Attribute key not found']);
    //                 }

    //                 $record = $d['attributes'];

    //                 if(empty($record['booking_id']) || empty($record['property_id'])){
    //                     return response()->json(['error' => 'Booking or Property not found']);
    //                 }

    //                 $name = $record['customer']['name'] ?? '';
    //                 $name .= ' ' . $record['customer']['surname'] ?? '';

    //                 $booking = [
    //                     'booking_id'      => $record['booking_id'] ?? null,
    //                     'unique_id'       => $record['ota_reservation_code'] ?? null,
    //                     'property_id'     => $record['property_id'] ?? null,
    //                     'ota_name'        => $record['ota_name'] ?? null,
    //                     'status'          => $record['status'] ?? null,
    //                     'arrival_date'    => $record['arrival_date'] ?? null,
    //                     'departure_date'  => $record['departure_date'] ?? null,
    //                     'amount'          => $record['amount'] ?? null,
    //                     'ota_commission'  => $record['ota_commission'] ?? null,
    //                     'guest_name'      => $name,
    //                     'guest_email'     => $record['customer']['mail'] ?? null,
    //                     'guest_phone'     => $record['customer']['phone'] ?? null,
    //                     'created_at'      => Carbon::now()->toDateString(),
    //                     'updated_at'      => Carbon::now()->toDateString(),
    //                 ];

    //                 $all_bookings[] = $booking;
    //             }

    //             print_r($all_bookings);die;

    //             if(!empty($all_bookings)){

    //                 $this->almosaferLog("Booking JSON: " . json_encode($all_bookings));

    //                 BookingOtasDetails::insert($all_bookings);

    //                 foreach($all_bookings as $abk){

    //                     $booking_id = $abk['booking_id'];

    //                     $mr_ack = Http::withHeaders([
    //                         'Authorization' => config('magarental.api_key'),
    //                     ])->post(config('magarental.base_url') . "/api/v1/booking_revisions/{$booking_id}/ack");

    //                     // if($mr_ack->successful()){
    //                     //     print_r($mr_ack->json()); die;
    //                     // }
    //                 }

    //                 $this->almosaferLog("*************** MR BOOKING END ***************");

    //                 return response()->json(['message' => 'Bookings created successfully.'], 201);
    //             }
    //         }

    //         return response()->json(['error' => 'Booking not found from maga rental']);

    //     } catch (\Exception $ex) {

    //         $this->almosaferLog("MR ERROR: " . $ex->getMessage());
    //         $this->almosaferLog("*************** MR BOOKING END ***************");

    //         print_r($ex->getMessage());
    //     }

    // }

    public function createAlmosaferProperty($id)
    {
        $listing   = Listing::findOrFail($id);
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();

        if(is_null($rate_plan)){
            return redirect()->back()->with('error', 'Rate plan not found from database');
        }

        $property  = Properties::findOrFail($rate_plan->property_id);

        $branch = MrBranch::where('name', 'Riyadh')->first();

        $data  = json_decode($listing->listing_json, true);
        $title = $data['title'];

        $airbnb_user_id = 0;
        

        // dd($listing, $rate_plan, $property, $title, $property->currency);

        // if(empty($listing->id)){
        //     return redirect()->back()->with('error', 'Listing can not be create');
        // }
        // dd($response,$response['title'],$response['currency'],$response['email'],$response['country'],$response['city']);

        $roomType = Http::withHeaders([
            'Authorization' => config('magarental.api_key'),
        ])->post(config('magarental.base_url') . "/api/v1/room_types", [
            "room_type" => [
                "property_id"       => $branch->property_id,
                "title"             => $title ?? 'Master Room',
                "count_of_rooms"    => 1,
                "occ_adults"        => 4,
                "occ_children"      => 0,
                "occ_infants"       => 0,
                "default_occupancy" => 1,
                "facilities"        => [],
                "room_kind"         => "room",
                "capacity"          => null,
                'content'           => [
                    'description' => "",
                    'photos'      => [
                        [
                            'author'      => 'Author Name',
                            'description' => 'Room View',
                            'kind'        => 'photo',
                            'position'    => 1,
                            'url'         => 'https://www.zaaer.co/storage/units/7Si2I5rAaFDWAAbdz6cKycPcR.jpeg',
                        ],
                        [
                            'author'      => 'Author Name',
                            'description' => 'Room View',
                            'kind'        => 'photo',
                            'position'    => 2,
                            'url'         => 'https://www.zaaer.co/storage/units/lbARlPDAZOELKsohQkhc9GJbe.jpeg',
                        ],
                        [
                            'author'      => 'Author Name',
                            'description' => 'Room View',
                            'kind'        => 'photo',
                            'position'    => 3,
                            'url'         => 'https://www.zaaer.co/storage/units/7NHeEKhOSa7wRnBL04ggeBcnb.jpeg',
                        ],
                    ],
                ],
            ],
        ]);

        if ($roomType->successful()) {

            $new_listing    = Listing::create([
                'user_id'      => 0,
                'listing_id'   => $branch->property_id,
                'listing_json' => json_encode(['listing'=>$title, 'data' => 'almosafer']),
                'channel_id'   => 1,
            ]);

            $new_property = Properties::create([
                'title'          => $title ?? null,
                'currency'       => 'SAR', //$property->currency ?? 'USD',
                'email'          => $property->email ?? null,
                'country'        => $property->country ?? null,
                'city'           => $property->city ?? null,
                'user_id'        => $airbnb_user_id,
                'ch_property_id' => 1,
                'mr_property_id' => $branch->property_id,
                'ch_group_id'    => 1,
                'group_id'       => 1,
            ]);

            $roomType_json = $roomType->json();
            $roomType_id   = $roomType_json['data']['attributes']['id'] ?? null;
            // dd($roomType_id);
            // $property_id   = $roomType_json['data']['relationships']['property']['data']['id'];
            // print_r($roomType_json);
            if (empty($roomType_id)) {
                return redirect()->back()->with('error', 'Case-2 room type id not found from MagaRental');
            }

            $new_room_type = RoomType::create([
                'user_id'         => $airbnb_user_id,
                'listing_id'      => 'mr_'.$new_listing->id,
                'property_id'     => $new_property->id,
                'title'           => $title ?? null,
                'count_of_rooms'  => 1,
                'occ_adults'      => 5,
                'occ_children'    => 0,
                'occ_infants'     => 0,
                'ch_room_type_id' => 1,
                'mr_room_type_id' => $roomType_id,
            ]);

            $ratePlan = Http::withHeaders([
                'Authorization' => config('magarental.api_key'),
            ])->post(config('magarental.base_url') . "/api/v1/rate_plans", [
                "rate_plan" => [
                    "title"                       => $title ?? 'Master Room',
                    "property_id"                 => $branch->property_id,
                    "room_type_id"                => $roomType_id,
                    "parent_rate_plan_id"         => null,
                    "children_fee"                => "0.00",
                    "infant_fee"                  => "0.00",
                    "options"                     => [
                        [
                            "occupancy"  => 1,
                            "is_primary" => true,
                        ],
                    ],
                    "currency"                    => "SAR",
                    "sell_mode"                   => "per_room",
                    "rate_mode"                   => "manual",
                    "inherit_rate"                => false,
                    "inherit_closed_to_arrival"   => false,
                    "inherit_closed_to_departure" => false,
                    "inherit_stop_sell"           => false,
                    "inherit_min_stay_arrival"    => false,
                    "inherit_min_stay_through"    => false,
                    "inherit_max_stay"            => false,
                    "inherit_max_sell"            => false,
                    "inherit_max_availability"    => false,
                    "inherit_availability_offset" => false,
                    "auto_rate_settings"          => null,
                ],
            ]);

            if ($ratePlan->successful()) {

                $ratePlan_json = $ratePlan->json();

                $ratePlan_id = $ratePlan_json['data']['attributes']['id'] ?? null;

                if(empty($ratePlan_id)){
                    return redirect()->back()->with('error', 'Case-3 rate plan id not found from MagaRental');
                }

                $rate_plan = RatePlan::create([
                    'user_id'         => $airbnb_user_id,
                    'listing_id'      => 'mr_'.$new_listing->id,
                    'property_id'     => $new_property->id,
                    'room_type_id'    => $new_room_type->id,
                    'title'           => $title ?? null,
                    'occupancy'       => 5,
                    'is_primary'      => true,
                    'rate'            => 1,
                    'ch_rate_plan_id' => 1,
                    'mr_rate_plan_id' => $ratePlan_id,
                ]);

                $listing->update(['status' => 1]);

                ListingSetting::create([
                    'listing_id'          => $new_listing->id,
                    'rate_plan_id'        => $ratePlan_id,
                    'listing_currency'    => 'SAR',
                    'instant_booking'     => 'everyone',
                    'default_daily_price' => 1,
                ]);

                ListingRelation::create([
                    'listing_id_airbnb'    => $listing->id,
                    'listing_id_other_ota' => $new_listing->id,
                    'listing_type'         => 'Almosafer',
                ]);

                // Calendar update
                $restriction_arr = DB::table('calenders')
                ->selectRaw('? as property_id, ? as rate_plan_id, calender_date as date_from, calender_date as date_to, ROUND(rate) * 100 as rate, 1 as min_stay, 365 as max_stay, 0 as stopsell', [
                    $branch->property_id,
                    $ratePlan_id
                ])
                ->where('listing_id', $listing->listing_id)
                ->where('calender_date', '>=', date('Y-m-d'))
                ->get()
                ->toArray();

                if(!empty($restriction_arr)){
                    $restrict = Http::withHeaders([
                        'Authorization' => config('magarental.api_key'),
                    ])
                    ->timeout(0)
                    ->post(config('magarental.base_url') . '/api/v1/restrictions', [
                        'values' => $restriction_arr
                    ]);
                }

                $ota_prices_arr = DB::table('calenders')
                ->selectRaw('calender_date as date_from, calender_date as date_to, IF(availability = 1, 1, 0) as max_sell')
                ->where('listing_id', $listing->listing_id)
                ->where('calender_date', '>=', date('Y-m-d'))
                ->get()
                ->toArray();

                if(!empty($ota_prices_arr)){
                    $ota_prices = Http::withHeaders([
                        'Authorization' => config('magarental.api_key'),
                    ])
                    ->timeout(0)
                    ->post(config('magarental.base_url') . '/api/v1/ota_prices',
                        [
                            'PropertyId' => $branch->property_id,
                            'Room_typeid' => $roomType_id,
                            'OTA_inventory' => $ota_prices_arr,
                            "Created_date" => date('Y-m-d h:i:s') //"2024-03-12 14:25:19"
                        ]
                    );

                    if ($ota_prices->successful()){
                        $ota_prices_json = $ota_prices->json();
                        $ota_price_id = $ota_prices_json['id'] ?? null;

                        if(!empty($ota_price_id)){
                            $new_listing->mr_ota_price_id = $ota_price_id;
                            $new_listing->save();
                        }
                    }
                }

                return redirect()->back()->with('success', 'Property Created Successfully!');

            } else {
                $error = $ratePlan->body();

                return redirect()->back()->with('error', $error);
            }

        } else {
            $error = $roomType->body();

            return redirect()->back()->with('error', $error);
        }

    }

    public function createAlmosaferBranch()
    {
        die;

        $name = 'Riyadh';

        $branch = MrBranch::where('name', $name)->first();
        if (! empty($branch)) {
            return redirect()->back()->with('error', 'Branch Already Exist!');
        }

        $branch = Http::withHeaders([
            'Authorization' => config('magarental.api_key'),
        ])->post(config('magarental.base_url') . "/api/v1/properties", [
            "property" => [
                "title"         => $name,
                "currency"      => 'SAR',
                "email"         => '',
                "phone"         => '41796034724',
                "zip_code"      => '12271',
                "country"       => 'Saudia',
                "state"         => 'Riyadh',
                "city"          => 'Riyadh',
                "address"       => 'Riyadh city',
                "longitude"     => '46.716667',
                "latitude"      => '24.633333',
                "timezone"      => 'AST',
                "facilities"    => [],
                "property_type" => 'Hotel',
                "company_rooms" => 1,
                // "group_id"      => $property->group_id,
                'settings'      => [
                    'allow_availability_autoupdate_on_confirmation' => true,
                    'allow_availability_autoupdate_on_modification' => true,
                    'allow_availability_autoupdate_on_cancellation' => true,
                    'min_stay_type'                                 => 'both',
                    'min_price'                                     => null,
                    'max_price'                                     => null,
                    'state_length'                                  => 500,
                    'cut_off_time'                                  => '00:00:00',
                    'cut_off_days'                                  => 0,
                ],
                'content'       => [
                    'description'           => 'Riyadh Branch Description',
                    'photos'                => [
                        [
                            'url'         => 'https://zaaer.co/storage/units/gAPJ5r1236qbBHVzxd6JQsoJ1.jpeg',
                            'position'    => 1,
                            'author'      => 'Author Name',
                            'kind'        => 'photo',
                            'description' => 'Room View',
                        ],
                        [
                            'url'         => 'https://zaaer.co/storage/units/ZDnzr6EmXF6cpKsxUd6erUhec.jpeg',
                            'position'    => 2,
                            'author'      => 'Author Name',
                            'kind'        => 'photo',
                            'description' => 'Room View',
                        ],
                        [
                            'url'         => 'https://zaaer.co/storage/units/ZDnzr6EmXF6cpKsxUd6erUhec.jpeg',
                            'position'    => 3,
                            'author'      => 'Author Name',
                            'kind'        => 'photo',
                            'description' => 'Room View',
                        ],
                    ],
                    'important_information' => 'Riyadh Branch',
                ],
                // 'logo_url'      => 'https://hotel.domain/logo.png',
                // 'website'       => 'https://some-hotel-website.com',
            ],
        ]);

        if ($branch->successful()) {

            $branch_json = $branch->json();

            $response       = $branch_json['data']['attributes'] ?? null;
            $mg_property_id = $response['id'] ?? null;

            $new_branch              = new MrBranch();
            $new_branch->name        = $name;
            $new_branch->property_id = $mg_property_id;
            $new_branch->save();

        } else {
            $error = $branch->body();
            print_r($error);
        }
    }

    public function deletePropertyRecord()
    {
        die;

        $ratePlanId = ''; //'6fca8b04-6b93-4f3e-8645-5014a2e46056';
        $roomTypeId = ''; //'e2e519bc-856c-44b9-a9cc-9aa89ef6e37c';

        $ratePlan = Http::withHeaders([
                'Authorization' => config('magarental.api_key'),
            ])->delete(config('magarental.base_url') . "/api/v1/rate_plans/".$ratePlanId);
        
        if ($ratePlan->successful()) {

            $ratePlan_json = $ratePlan->json();
            print_r($ratePlan_json);
            
        } else {
            $error = $ratePlan->body();
            print_r($error);
        }
        
        
        $roomType = Http::withHeaders([
                'Authorization' => config('magarental.api_key'),
            ])->delete(config('magarental.base_url') . "/api/v1/room_types/".$roomTypeId);
    
        if ($roomType->successful()) {

            $roomType_json = $roomType->json();
            print_r($roomType_json);
            
        } else {
            $error = $roomType->body();
            print_r($error);
        }

    }

    private function almosaferLog($message, $context = [])
    {
        Log::channel('almosafer')->info($message, $context);
    }

    public function updateCalendarAirbnbToAlmosafer(){
        try{

            $airbnb_listing_id = '';

            $get_listing_airbnb = Listing::where('listing_id', $airbnb_listing_id)->first();
            if(is_null($get_listing_airbnb)){
                return response()->json(['error' => 'Listing not found']);
            }

            $almosafer_listing_relation = ListingRelation::where('listing_id_airbnb', $get_listing_airbnb->id)
            ->where('listing_type', 'Almosafer')
            ->first();

            if(is_null($almosafer_listing_relation)){
                return response()->json(['error' => 'Listing Relation not found']);
            }

            $almosafer_listing = Listing::where('id', $almosafer_listing_relation->listing_id_other_ota)->first();
            if(is_null($almosafer_listing)){
                return response()->json(['error' => 'Almosafer Listing not found']);
            }

            $almosafer_rate_plan = RatePlan::where('listing_id', 'mr_'.$almosafer_listing->id)->first();
            if(is_null($almosafer_rate_plan)){
                return response()->json(['error' => 'Almosafer rate plan not found']);
            }

            $almosafer_room_type = RoomType::where('listing_id', 'mr_'.$almosafer_listing->id)->first();
            if(is_null($almosafer_room_type)){
                return response()->json(['error' => 'Almosafer room type not found']);
            }

            $branch = MrBranch::where('name', 'Riyadh')->first();

            // Calendar update
            $restriction_arr = DB::table('calenders')
            ->selectRaw('? as property_id, ? as rate_plan_id, calender_date as date_from, calender_date as date_to, ROUND(rate) * 100 as rate, 1 as min_stay, 365 as max_stay, 0 as stopsell', [
                $branch->property_id,
                $almosafer_rate_plan->mr_rate_plan_id
            ])
            ->where('listing_id', $get_listing_airbnb->listing_id)
            ->where('calender_date', '>=', date('Y-m-d'))
            ->get()
            ->toArray();

            $restrict_flag = $ota_prices_flag = false;

            if(!empty($restriction_arr)){
                $restrict = Http::withHeaders([
                    'Authorization' => config('magarental.api_key'),
                ])
                ->timeout(0)
                ->post(config('magarental.base_url') . '/api/v1/restrictions', [
                    'values' => $restriction_arr
                ]);

                if($restrict->successful()){
                    $restrict_flag = true;
                }
            }

            $ota_prices_arr = DB::table('calenders')
            ->selectRaw('calender_date as date_from, calender_date as date_to, IF(availability = 1, 1, 0) as max_sell')
            ->where('listing_id', $get_listing_airbnb->listing_id)
            ->where('calender_date', '>=', date('Y-m-d'))
            ->get()
            ->toArray();

            if(!empty($ota_prices_arr)){

                $ota_price_url = config('magarental.base_url') . '/api/v1/ota_prices';

                if(!empty($almosafer_listing->mr_ota_price_id)){
                    $ota_price_url .= '/'.$almosafer_listing->mr_ota_price_id;
                }

                $ota_prices = Http::withHeaders([
                    'Authorization' => config('magarental.api_key'),
                ])
                ->timeout(0)
                ->put($ota_price_url,
                    [
                        'PropertyId' => $branch->property_id,
                        'Room_typeid' => $almosafer_room_type->mr_room_type_id,
                        'OTA_inventory' => $ota_prices_arr,
                        "Created_date" => date('Y-m-d h:i:s') //"2024-03-12 14:25:19"
                    ]
                );

                if ($ota_prices->successful()){

                    $ota_prices_flag = true;

                    $ota_prices_json = $ota_prices->json();
                    $ota_price_id = $ota_prices_json['id'] ?? null;

                    if(!empty($ota_price_id)){
                        $almosafer_listing->mr_ota_price_id = $ota_price_id;
                        $almosafer_listing->save();
                    }
                }
            }

            if($restrict_flag && $ota_prices_flag){
                return response()->json(['success' => 'Price has been updated on Almosafer']);
            }

            $error = 'Something went wrong!';
            if(!$restrict_flag){
                $error .= ' Found Restrict api error |';
            }

            if(!$ota_prices_flag){
                $error .= ' Found Ota Prices api error';
            }

            return response()->json(['error' => $error]);

        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()]);
        }
    }
}
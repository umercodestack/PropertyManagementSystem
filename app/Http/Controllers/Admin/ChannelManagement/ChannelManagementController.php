<?php

namespace App\Http\Controllers\Admin\ChannelManagement;

use App\Http\Controllers\Controller;
use App\Jobs\SaveCalenderData;
use App\Models\BookingOtasDetails;
use App\Models\Channels;
use App\Models\Group;
use App\Models\Listing;
use App\Models\ListingRelation;
use App\Models\ListingSetting;
use App\Models\Properties;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Calender;
use App\Models\ChannelToken;

use Illuminate\Support\Facades\Http;

class ChannelManagementController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission');
    }

    public function index()
    {
        $channels = Channels::with('user')
            ->whereHas('user', function ($query) {
                $query->whereNull('parent_user_id');
            })
            ->get();
        // dd($channels);
        return view('Admin.channels-management.index', ['channels' => $channels]);
    }

    public function create()
    {
        $users = User::all();
        return view('Admin.channels-management.create', ['users' => $users]);
    }

    public function getExchangeRateToUSD()
    {
        $url = 'https://v6.exchangerate-api.com/v6/ef39bbc4c09e807d2acf103e/latest/USD';
        $response = Http::get($url);
        if ($response->successful()) {
            $data = $response->json();
            return $data['conversion_rates']['SAR'];
        } else {
            return response()->json(['success' => false, 'error' => 'Unable to fetch data.'], $response->status());
        }
    }
    /**
     * @param $listing_id
     * @param $property
     * @param $user
     * @param $listing
     * @return string
     */
    public function createRoomType($listing_id, $property_id, $user, $listing)
    {
        $property = Properties::where('ch_property_id', $property_id)->first();
        // dd($property);
        $listing_json = json_decode($listing->listing_json);
        // dd(($listing_json));
        // dd($listing_id, $property_id, $user );
        $roomTypeCh = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/room_types', [
                    "room_type" => [
                        "property_id" => $property_id,
                        'title' => $listing_json->title . $listing_id,
                        'count_of_rooms' => 1,

                        'occ_adults' => 1,
                        'occ_children' => 0,
                        'occ_infants' => 0,
                        "default_occupancy" => 1,
                    ]
                ]);
        if ($roomTypeCh->successful()) {
            $roomTypeCh = $roomTypeCh->json();
            $roomTypeDB = RoomType::create([
                'user_id' => $user,
                'listing_id' => $listing_id,
                'property_id' => $property->id,
                'title' => $listing_json->title,
                'count_of_rooms' => 1,
                'occ_adults' => 1,
                'occ_children' => 0,
                'occ_infants' => 0,
                'ch_room_type_id' => $roomTypeCh['data']['id'],
            ]);
            return $roomTypeDB;
        } else {
            return $roomTypeCh->body();
            //                    dd($error);
        }
    }

    public function saveBookingInDB($channel_id, $listing_id, $property_id)
    {
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/bookings/?filter[channel_id]=$channel_id");
        if ($response->successful()) {
            $response = $response->json();
            foreach ($response['data'] as $item) {
                //            dd($item['attributes']['id']);
                $checkForBookingInDB = BookingOtasDetails::where('booking_id', $item['attributes']['id'])->first();
                if ($checkForBookingInDB) {
                    return $checkForBookingInDB;
                } else {
                    BookingOtasDetails::create([
                        'listing_id' => $listing_id,
                        'property_id' => $property_id,
                        'booking_id' => $item['attributes']['id'],
                        'channel_id' => $channel_id,
                        'booking_otas_json_details' => json_encode($item),
                    ]);
                }
            }
        } else {
            return $response->body();
        }
    }

    // public function syncCalenders($rate_multiplier,$channel, $listing)
    // {
    //     // dd($rate_multiplier,$channel, $fa, $listing);
    //     $listing_relation = ListingRelation::where('listing_id_other_ota', $listing->id)->first();
    //     $airbnb_listing = Listing::where('id', $listing_relation->listing_id_airbnb)->first();
    //     // dd($listing_relation,$airbnb_listing);
    //     $today_date = Carbon::now()->toDateString();
    //     // dd($today_date);
    //     $calender_airbnb = Calender::where('listing_id', $airbnb_listing->listing_id)->where('calender_date', '>=', $today_date)->orderBy('id', 'DESC')->get();
    //     $room_plan = RoomType::where('listing_id', $listing->listing_id)->first();
    //     // dd(vars: $room_plan);
    //     $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
    //     $property = Properties::where('id', $rate_plan->property_id)->first();
    //     // dd($rate_plan, $property);
    //     // // $property
    //     // dd($calender_airbnb);
    //     $rate_data = [];
    //     $room_data = [];
    //     foreach($calender_airbnb as $key=>$item) {
    //         // dd($item);
    //         $rate_data[$key]['date_from'] = $item->calender_date;
    //         $rate_data[$key]['date_to'] = $item->calender_date;
    //         $rate_data[$key]['property_id'] = $property->ch_property_id;
    //         $rate_data[$key]['rate_plan_id'] = $rate_plan->ch_rate_plan_id;
    //         $rate_data[$key]['rate'] = (int)((($rate_multiplier / 100) * $item->rate) + $item->rate)* 100;

    //         $room_data[$key]['date_from'] = $item->calender_date;
    //         $room_data[$key]['date_to'] = $item->calender_date;
    //         $room_data[$key]['property_id'] = $property->ch_property_id;
    //         $room_data[$key]['room_type_id'] = $room_plan->ch_room_type_id;
    //         $room_data[$key]['availability'] = $item->availability;
    //     }
    //     // dd($rate_data);
    //     $response = Http::withHeaders([
    //         'user-api-key' => env('CHANNEX_API_KEY'),
    //         //            ])->post(env('CHANNEX_URL')."/api/v1/availability", [
    //     ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
    //                 "values" => $rate_data
    //             ]);

    //     if ($response->successful()) {
    //         $restrictions = $response->json();
    //         // dd($restrictions);
    //         // Log::info('rest sync', ['response' => $restrictions]);
    //         // //Save Calender Data
    //         // $this->saveCalenderData($listing_id, $property->ch_property_id, $startDate, $endDate);
    //     } else {
    //         $error = $response->body();
    //                     //    dd($error);
    //     }

    //     $response = Http::withHeaders([
    //         'user-api-key' => env('CHANNEX_API_KEY'),
    //     ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
    //                 "values" => $room_data
    //             ]);
    //     if ($response->successful()) {
    //         $availability = $response->json();
    //         // dd($availability );

    //     } else {
    //         $error = $response->body();
    //         // dd($error);
    //     }
    // }
    public function syncCalenders($channel_type, $listing, $listingOtherOta)
    {
        // dd($listing);
        // dd($rate_multiplier,$channel, $fa, $listing);
        $listing_relation = ListingRelation::where('listing_id_other_ota', $listingOtherOta->id)
            ->where('listing_type', 'BookingCom')
            ->first();
        // dd($listing_relation);
        $listing_Bcom = Listing::where('id', $listingOtherOta->id)->first();
        // dd($listing_Bcom);
        $airbnb_listing = Listing::where('id', $listing_relation->listing_id_airbnb)->first();

        // dd($listing_relation,$airbnb_listing);
        $today_date = Carbon::now()->toDateString();
        // dd($today_date);
        $calender_airbnb = Calender::where('listing_id', $airbnb_listing->listing_id)->where('calender_date', '>=', $today_date)->orderBy('id', 'DESC')->get();
        $room_plan = RoomType::where('listing_id', $listing->listing_id)->first();
        // dd(vars: $room_plan);
        $rate_plan = RatePlan::where('listing_id', $listing_Bcom->listing_id)->first();
        $property = Properties::where('id', $rate_plan->property_id)->first();
        // dd($rate_plan, $room_plan);
        // // $property
        // dd($calender_airbnb);
        $rate_data = [];
        $room_data = [];


        $exchange = $this->getExchangeRateToUSD();

        foreach ($calender_airbnb as $key => $item) {
            // dd($item);
            $rate_data[$key]['date_from'] = $item->calender_date;
            $rate_data[$key]['date_to'] = $item->calender_date;
            $rate_data[$key]['property_id'] = $property->ch_property_id;
            $rate_data[$key]['rate_plan_id'] = $rate_plan->ch_rate_plan_id;
            $rate_data[$key]['rate'] = $item->rate * 100;

            $room_data[$key]['date_from'] = $item->calender_date;
            $room_data[$key]['date_to'] = $item->calender_date;
            $room_data[$key]['property_id'] = $property->ch_property_id;
            $room_data[$key]['room_type_id'] = $room_plan->ch_room_type_id;
            $room_data[$key]['availability'] = $item->availability;
        }
        // dd($rate_data);
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
            //            ])->post(env('CHANNEX_URL')."/api/v1/availability", [
        ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                    "values" => $rate_data
                ]);

        if ($response->successful()) {
            $restrictions = $response->json();
            // dd($restrictions);
            // Log::info('rest sync', ['response' => $restrictions]);
            // //Save Calender Data
            // $this->saveCalenderData($listing_id, $property->ch_property_id, $startDate, $endDate);
        } else {
            $error = $response->body();
            // dd($error);
        }

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    "values" => $room_data
                ]);
        if ($response->successful()) {
            $availability = $response->json();
            // dd($availability);

        } else {
            $error = $response->body();
            // dd($error);
        }
    }
    public function pullFutureReservation(Request $request): void
    {
        // dd($request);
        $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
        $property = Properties::where('id', $rate_plan->property_id)->first();
        $pullFutureReservation = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$request->channel_id/action/load_future_reservations", [
                    "listing_id" => $request->listing_id
                ]);
        if ($pullFutureReservation->successful()) {
            $pullFutureReservation->json();
            $this->saveBookingInDB($request->channel_id, $request->listing_id, $property->ch_property_id);
        } else {
            $error = $pullFutureReservation->body();
        }
    }

    public function saveCalenderData($listing_id, $property_id, $startDate, $endDate)
    {
        //            dd($property);
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/restrictions?filter[property_id]=$property_id&filter[date][gte]=$startDate&filter[date][lte]=$endDate&filter[restrictions]=rate,availability,max_stay,min_stay_through");

        if ($response->successful()) {
            $response = $response->json();
            $ari = $response['data'];
        } else {
            $error = $response->body();
        }
        $rate_plan = RatePlan::where('listing_id', $listing_id)->first();
        // dd($rate_plan);
        if (isset($ari[$rate_plan->ch_rate_plan_id])) {
            // Get all values for the specific key
            // dd($ari[$rate_plan->ch_rate_plan_id]);
            $calender = $ari[$rate_plan->ch_rate_plan_id];
            //                return response()->json($values);
        }
        SaveCalenderData::dispatch($calender, $listing_id);
    }

    /**
     * @param $listing_id
     * @param $property
     * @param $user
     * @param $listing
     * @param $room_type
     * @return string
     */
    //     public function createRatePlan($rate_multiplier,$airbnb_listing_id,$rate,$listing_id,$channel_id, $property_id, $user, $listing, $room_type)
//     {
//         $airbnb_listing = Listing::whereId($airbnb_listing_id)->first();
//         $listing_setting_airbnb = ListingSetting::where('listing_id',$airbnb_listing->listing_id)->first();
//         // dd($listing_setting_airbnb->default_daily_price,(($rate_multiplier / 100) * $listing_setting_airbnb->default_daily_price) * 100);
//         // dd($listing_setting_airbnb->default_daily_price * ($rate_multiplier / 100) + $listing_setting_airbnb->default_daily_price);
// // dd(($rate_multiplier / 100) * $listing_setting_airbnb->default_daily_price * 100);
//         $property = Properties::where('ch_property_id', $property_id)->first();
//         $listing_json = json_decode($listing->listing_json);
//                 $ratePlanCh = Http::withHeaders([
//             'user-api-key' => env('CHANNEX_API_KEY'),
//         ])->post(env('CHANNEX_URL') . '/api/v1/rate_plans', [
//                     "rate_plan" => [
//                         "property_id" => $property->ch_property_id,
//                         'room_type_id' => $room_type['ch_room_type_id'],
//                         'title' => $listing_json->title . $listing_id,
//                         'options' => [
//                             [
//                                 'occupancy' => 1,
//                                 'is_primary' => true,
//                                 'rate' => round((($rate_multiplier / 100) * $listing_setting_airbnb->default_daily_price) * 100)
//                             ]
//                         ]
//                     ]
//                 ]);
//         if ($ratePlanCh->successful()) {

    //             $rate_plan = RatePlan::create([
//                 'user_id' => $user,
//                 'listing_id' => $listing_id,
//                 'property_id' => $property->id,
//                 'room_type_id' => $room_type['id'],
//                 'title' => $listing_json->title . $listing_id,
//                 'occupancy' => 1,
//                 'is_primary' => true,
//                 'rate' => ($rate_multiplier / 100) * $listing_setting_airbnb->default_daily_price * 100
//             ]);

    //             $ratePlanCh = $ratePlanCh->json();
//             $ratePlanDB = RatePlan::where('id', $rate_plan->id)->first();
//             $ratePlanDB->update(['ch_rate_plan_id' => $ratePlanCh['data']['attributes']['id']]);
//             $ch_room_type_id = $room_type['ch_room_type_id'];
//             $ch_rate_plan_id = $ratePlanCh['data']['attributes']['id'];

    //             $startDate = Carbon::today()->toDateString();
//             $endDate = Carbon::today()->addDays(500)->toDateString();
//             $response = Http::withHeaders([
//                 'user-api-key' => env('CHANNEX_API_KEY'),
//             ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
//                         //                ])->post(env('CHANNEX_URL')."/api/v1/restrictions", [
//                         "values" => [
//                             [
//                                 //                        'date' => '2024-11-21',
//                                 "date_from" => $startDate,
//                                 "date_to" => $endDate,
//                                 "property_id" => "$property->ch_property_id",
//                                 "room_type_id" => "$ch_room_type_id",
//                                 "availability" => 1,
//                             ]
//                         ]
//                     ]);

    //             if ($response->successful()) {
//                 $availability = $response->json();
//                 \Log::info('avail sync', ['response' => $availability]);
//             } else {
//                 $error = $response->body();
//                               dd($error);
//             }
//             $response = Http::withHeaders([
//                 'user-api-key' => env('CHANNEX_API_KEY'),
//                 //            ])->post(env('CHANNEX_URL')."/api/v1/availability", [
//             ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
//                         "values" => [
//                             [
//                                 //                        'date' => '2024-11-21',
//                                 "date_from" => $startDate,
//                                 "date_to" => $endDate,
//                                 "property_id" => "$property->ch_property_id",
//                                 "rate_plan_id" => "$ch_rate_plan_id",
//                                 "availability" => 1,
//                                 "rate" => ($rate_multiplier / 100) * $listing_setting_airbnb->default_daily_price * 100,
//                             ]
//                         ]
//                     ]);

    //             if ($response->successful()) {
//                 $restrictions = $response->json();
//                 \Log::info('rest sync', ['response' => $restrictions]);
//                 //Save Calender Data
//                 $this->saveCalenderData($listing_id, $property->ch_property_id, $startDate, $endDate);
//                 // $this->pullFutureReservation($channel_id, $listing_id, $property_id);
//             } else {
//                 $error = $response->body();
//                               dd($error);
//             }

    //             return $ratePlanDB;
//         } else {
//             $error = $ratePlanCh->body();
//                               dd($error);
//         }
//     }

    public function createRatePlan($channel_type, $rate, $property, $user_id, $listing, $room_type)
    {
        $exchange = $this->getExchangeRateToUSD();
        if ($channel_type == 'VRBO') {
            $rate = floor((((1 / 100) * $rate) / $exchange) * 100);
        } else {
            $rate = $rate * 100;
        }
        $listing_json = json_decode($listing->listing_json);
        $ratePlanCh = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/rate_plans', [
                    "rate_plan" => [
                        "property_id" => $property->ch_property_id,
                        'room_type_id' => $room_type->ch_room_type_id,
                        "currency" => "SAR",
                        'title' => $listing_json->title . $listing->listing_id . ' ' . $channel_type,
                        'options' => [
                            [
                                'occupancy' => 1,
                                'is_primary' => true,
                                'rate' => $rate
                            ]
                        ]
                    ]
                ]);
        if ($ratePlanCh->successful()) {
            $ratePlanCh = $ratePlanCh->json();
            $rate_plan = RatePlan::create([
                'user_id' => $user_id,
                'listing_id' => $listing->listing_id,
                'property_id' => $property->id,
                'room_type_id' => $room_type->id,
                'title' => $listing_json->title . $listing->listing_id,
                'occupancy' => 1,
                'is_primary' => true,
                'rate' => $rate / 100,
                'ch_rate_plan_id' => $ratePlanCh['data']['attributes']['id']
            ]);
            $ch_room_type_id = $room_type->ch_room_type_id;
            $ch_rate_plan_id = $ratePlanCh['data']['attributes']['id'];

            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->addDays(500)->toDateString();
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                        //                ])->post(env('CHANNEX_URL')."/api/v1/restrictions", [
                        "values" => [
                            [
                                //                        'date' => '2024-11-21',
                                "date_from" => $startDate,
                                "date_to" => $endDate,
                                "property_id" => "$property->ch_property_id",
                                "room_type_id" => "$ch_room_type_id",
                                "availability" => 1,
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $availability = $response->json();
                \Log::info('avail sync', ['response' => $availability]);
            } else {
                $error = $response->body();
                dd($error);
            }
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
                //            ])->post(env('CHANNEX_URL')."/api/v1/availability", [
            ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                        "values" => [
                            [
                                //                        'date' => '2024-11-21',
                                "date_from" => $startDate,
                                "date_to" => $endDate,
                                "property_id" => "$property->ch_property_id",
                                "rate_plan_id" => "$ch_rate_plan_id",
                                "availability" => 1,
                                "rate" => $rate,
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $restrictions = $response->json();
                // \Log::info('rest sync', ['response' => $restrictions]);
                //Save Calender Data
                // $this->saveCalenderData($listing_id, $property->ch_property_id, $startDate, $endDate);
                // $this->pullFutureReservation($channel_id, $listing_id, $property_id);
            } else {
                $error = $response->body();
                dd($error);
            }

            return $rate_plan;
        } else {
            $error = $ratePlanCh->body();
            dd($error);
        }
    }

    public function createProperty($group, $listing)
    {
        $listing = $listing->toArray();
        $listing_json = json_decode($listing['listing_json']);
        $user = User::where('id', $group->user_id)->first();
        // dd($group,$listing_json->title,$user);
        // $listing_json = json_decode($listing->listing_json);
        // $listing_title = $listing_json
        // dd($listing_json, $group);
        $property = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/properties', [
                    "property" => [
                        "title" => strtolower($listing_json->title) . $listing['id'],
                        "currency" => 'USD',
                        "timezone" => 'Asia/Riyadh',
                        "email" => $user->email,
                        "country" => substr($user->country, -2),
                        "city" => $user->city,
                        "group_id" => $group->ch_group_id,
                        "settings" => [
                            "min_stay_type" => 'through'
                        ]
                    ]
                ]);
        if ($property->successful()) {
            $property = $property->json();
            $data['title'] = strtolower($listing_json->title) . $listing['id'];
            $data['currency'] = 'USD';
            $data['email'] = $user->email;
            $data['country'] = substr($user->country, -2);
            $data['city'] = $user->city;
            $data['user_id'] = $user->id;
            $data['ch_property_id'] = $property['data']['id'];
            $data['ch_group_id'] = $group->ch_group_id;
            $data['group_id'] = $group->id;
            $prop = Properties::create($data);

            $webhook = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . '/api/v1/webhooks', [
                        "webhook" => [
                            "property_id" => $data['ch_property_id'],
                            "callback_url" => 'https://admin.livedin.co/api/webhook',
                            "event_mask" => "*",
                            "is_active" => true,
                            "send_data" => true,
                        ]
                    ]);
            if ($webhook->successful()) {
                $webhook = $webhook->json();
                return $prop;
                //                dd($webhook);
            } else {
                $error = $property->body();
                //                dd($error);
            }
        } else {
            $error = $property->body();
            //            dd($error);
        }
    }

    public function getChannelInfo($channel_id)
    {
        // dd($channel_id,$listing_id);
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel_id");
        if ($response->successful()) {
            $response = $response->json();
            // dd($response['data']['attributes']['settings']);
            return $response['data'];
        } else {
            return $response->body();
        }
    }

    public function updateChannel($channel_id, $property)
    {
        $channelInfo = $this->getChannelInfo($channel_id);
        // 96127960-2678-4725-a951-1ddbd11b00cc
        array_push($channelInfo['attributes']['properties'], $property->ch_property_id);
        // dd($channelInfo['attributes']['id'],$property_id);
        $channel_id = $channelInfo['attributes']['id'];
        $channel = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->put(env('CHANNEX_URL') . "/api/v1/channels/$channel_id", [
                    "channel" => $channelInfo['attributes']
                ]);
        if ($channel->successful()) {
            $channel = $channel->json();
            // dd($channel);
        } else {
            $error = $channel->body();
            // dd($error);
        }
    }
    public function mapBookingListing(array $request)
    {
        // dd($request);

        $airbnb_listing_id = $request['airbnb_listing_id'];
        $rate_multiplier = $request['rate_multiplier'];
        $listing = Listing::whereId($request['listing_id'])->first();
        // dd($listing);
        $channel = Channels::whereId($listing->channel_id)->first();

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id");
        if ($response->successful()) {
            $data = $response->json();
            // dd($data['data']['attributes']['channel']);
            // dd($data['data']['attributes']['channel']);
            // $hotel_id = $data['data']['attributes']['settings']['hotel_id'];
            // dd($data['data']['attributes']['settings']['hotel_id']);
            $group_id = $data['data']['relationships']['group']['data']['id'];
            $group = Group::where('ch_group_id', $group_id)->first();
            if ($data['data']['attributes']['channel'] == 'BookingCom') {
                $property_id = $data['data']['relationships']['properties']['data'][0]['id'];
            } else {
                $prop = $this->createProperty($group, $listing);
                $property_id = $prop->ch_property_id;
                $this->updateChannel($channel->ch_channel_id, $prop);
            }

            ListingRelation::create([
                'listing_id_airbnb' => $airbnb_listing_id,
                'listing_id_other_ota' => $listing->id,
                'listing_type' => $data['data']['attributes']['channel'],
            ]);

            $room_type = $this->createRoomType($listing->listing_id, $property_id, $channel->user_id, $listing);
            // dd($room_type);
            $rate_plan = $this->createRatePlan($data['data']['attributes']['channel'], $rate_multiplier, $airbnb_listing_id, 200, $listing->listing_id, $channel->ch_channel_id, $property_id, $channel->user_id, $listing, $room_type);


            $this->syncCalenders($data['data']['attributes']['channel'], $rate_multiplier, $channel, $listing);

            // $map_listing =
            // $this->saveCalenderData($listing_id, $property_id, $startDate, $endDate);
        }
        return redirect()->back();

        // $response = Http::withHeaders([
        //     'user-api-key' => env('CHANNEX_API_KEY'),
        // ])->post(env('CHANNEX_URL') . "/api/v1/channels",[
        //     "channel" => [
        //         "channel"=> "BookingCom",
        //         "group_id"=> "902ab74c-de1d-4881-aa07-9a56a95297df",
        //         "is_active"=> true,
        //         "title"=> "Livedin_BookingCom",
        //         "known_mappings_list" => [],
        //         "properties"=> ["b9e2d21e-196e-46e8-a410-0d29e0e1988a"],
        //         "rate_plans"=> [
        //             [
        //                 "rate_plan_id"=> "707f6f07-12d6-474b-ae90-0ecf15cb13d7",
        //                 "settings" => [
        //                     "occ_changed"=> false,
        //                     "occupancy"=> 1,
        //                     "pricing_type"=> "RLO",
        //                     "primary_occ"=> false,
        //                     "rate_plan_code"=> 47253826,
        //                     "readonly"=> false,
        //                     "room_type_code"=> 1257384801
        //                 ]
        //             ]
        //         ],
        //         "settings"=> [
        //             "hotel_id" => "12573848"
        //         ]
        //     ]
        // ]);
        // if ($response->successful()) {
        //     dd($response->json());
        // }
        // else {
        //     $error = $response->body();
        //     dd($error);
        // }
    }
    // public function mapBookingListing(Request $request){
    //     $airbnb_listing_id = $request->airbnb_listing_id;
    //     $rate_multiplier = $request->rate_multiplier;
    //     $listing = Listing::whereId($request->listing_id)->first();
    //     // dd($listing);
    //     $channel = Channels::whereId($listing->channel_id)->first();

    //     $response = Http::withHeaders([
    //         'user-api-key' => env('CHANNEX_API_KEY'),
    //     ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id");
    //     if ($response->successful()) {
    //         $data = $response->json();
    //         // dd($data );
    //         $hotel_id = $data['data']['attributes']['settings']['hotel_id'];
    //         // dd($data['data']['attributes']['settings']['hotel_id']);
    //         $group_id = $data['data']['relationships']['group']['data']['id'];
    //         $property_id = $data['data']['relationships']['properties']['data'][0]['id'];
    //         $room_type = $this->createRoomType($listing->listing_id, $property_id, $channel->user_id, $listing);
    //         // dd($room_type);
    //         $rate_plan = $this->createRatePlan($rate_multiplier,$airbnb_listing_id,$request->rate,$listing->listing_id, $channel->ch_channel_id,$property_id, $channel->user_id, $listing, $room_type);

    //         ListingRelation::create([
    //             'listing_id_airbnb' => $airbnb_listing_id,
    //             'listing_id_other_ota' => $listing->id,
    //             'listing_type' => 'other_ota',
    //         ]);
    //         $this->syncCalenders($rate_multiplier,$channel, $listing);

    //         // $map_listing =
    //         // $this->saveCalenderData($listing_id, $property_id, $startDate, $endDate);
    //     }
    //     return redirect()->back();

    //     // $response = Http::withHeaders([
    //     //     'user-api-key' => env('CHANNEX_API_KEY'),
    //     // ])->post(env('CHANNEX_URL') . "/api/v1/channels",[
    //     //     "channel" => [
    //     //         "channel"=> "BookingCom",
    //     //         "group_id"=> "902ab74c-de1d-4881-aa07-9a56a95297df",
    //     //         "is_active"=> true,
    //     //         "title"=> "Livedin_BookingCom",
    //     //         "known_mappings_list" => [],
    //     //         "properties"=> ["b9e2d21e-196e-46e8-a410-0d29e0e1988a"],
    //     //         "rate_plans"=> [
    //     //             [
    //     //                 "rate_plan_id"=> "707f6f07-12d6-474b-ae90-0ecf15cb13d7",
    //     //                 "settings" => [
    //     //                     "occ_changed"=> false,
    //     //                     "occupancy"=> 1,
    //     //                     "pricing_type"=> "RLO",
    //     //                     "primary_occ"=> false,
    //     //                     "rate_plan_code"=> 47253826,
    //     //                     "readonly"=> false,
    //     //                     "room_type_code"=> 1257384801
    //     //                 ]
    //     //             ]
    //     //         ],
    //     //         "settings"=> [
    //     //             "hotel_id" => "12573848"
    //     //         ]
    //     //     ]
    //     // ]);
    //     // if ($response->successful()) {
    //     //     dd($response->json());
    //     // }
    //     // else {
    //     //     $error = $response->body();
    //     //     dd($error);
    //     // }
    // }

    public function createBookingComView()
    {
        $listings = Listing::select([
            'id',
            'listing_id',
            'created_at',
            \DB::raw("JSON_UNQUOTE(JSON_EXTRACT(listing_json, '$.title')) AS title"),
        ])
            ->where('is_sync', 'sync_all')->get();
        return view('Admin.listings-management.create_booking_com', ['listings' => $listings]);
    }

    public function testBookingComConnection(Request $request)
    {
        $request->validate([
            'hotel_id' => 'required',
        ]);

        $payload = [
            "channel" => "BookingCom",
            "settings" => [
                "id" => null,
                "email" => "",
                "hotel_id" => $request->hotel_id, // from request
                "send_email_notifications" => false,
                "mappingSettings" => new \stdClass(), // empty object
            ],
        ];

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/test_connection", $payload);

        if ($response->successful()) {
            $response = $response->json();

            return response()->json([
                'success' => $response['data']['success'] == true,
                'message' => $response['data']['success'] == true ? 'Connection successful' : 'Connection failed',
                'data' => $response,
            ]);
        } else {
            $error = $response->body();
            return response()->json([
                'success' => false,
                'message' => 'Connection failed',
                'data' => $error,
            ]);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'listing_id' => 'required',
            'hotel_id' => 'required',
            // 'room_id' => 'required',
        ]);
        $listings = [];
        // Fetch related models
        $listing = Listing::where('id', $request->listing_id)->first();
        $listing_setting = ListingSetting::where('listing_id', $listing->listing_id)->first();

        $channel = Channels::where('id', $listing->channel_id)->first();
        // dd($channel);
        $room_type = RoomType::where('listing_id', $listing->listing_id)->first();
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
        $property = Properties::where('id', $room_type->property_id)->first();

        // $response = Http::withHeaders([
        //     'user-api-key' => env('CHANNEX_API_KEY'),
        // ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details", [
        //             "channel" => "BookingCom",
        //             "settings" => [
        //                 "hotel_id" => $request->hotel_id
        //             ],
        //         ]);

        // if ($response->successful()) {
        //     $responseData = $response->json();
        //     dd($responseData);
        // }
        // dd('die');

        // ✅ Payload structure
        $payload = [
            "channel" => [
                "group_id" => $property->ch_group_id, // from DB
                "properties" => [$property->ch_property_id], // array of property IDs
                "known_mappings_list" => [],
                "channel" => "BookingCom",
                "currency" => "SAR",
                "title" => $request->title,
                "settings" => [
                    "email" => "",
                    "hotel_id" => $request->hotel_id,
                    "send_email_notifications" => false,
                    "mappingSettings" => new \stdClass(), // empty object {}
                    "derived_option" => new \stdClass(),  // empty object {}
                ],
                "rate_plans" => [], // can be filled from $rate_plan if needed
                "is_active" => false,
            ]
        ];
        // $payload['channel']['rate_plans'][] = [
        //     'rate_plan_id' => 'asdsadsa',
        //     'settings' => [
        //         'occ_changed' => false,
        //         'occupancy' => 1,
        //         'pricing_type' => 'RLO',
        //         'primary_occ' => true,
        //         'rate_plan_code' => 123,
        //         'readonly' => false,
        //         'room_type_code' => 123
        //     ]
        // ];
        // dd($payload['channel']);

        // ✅ Send POST request
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post('https://app.channex.io/api/v1/channels', $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            // dd($responseData['data']['attributes']['id']);

            $channel = Channels::create([
                'user_id' => $channel->user_id,
                'connection_type' => 'BCom',
                'ch_channel_id' => $responseData['data']['attributes']['id'],
            ]);
            // Fetch Listings
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details", [
                        "channel" => "BookingCom",
                        "settings" => [
                            "hotel_id" => $request->hotel_id
                        ],
                    ]);

            if ($response->successful()) {
                $response = $response->json();
                // dd($response);
                foreach ($response['data']['rooms'] as $item) {
                    $listings[] = [
                        "id" => $item['id'],
                        "type" => "apartment",
                        "title" => $item['title'],
                        "occupancies" => $item['rates'][0]['occupancies'],
                        "rooms" => $item
                    ];
                }

                foreach ($listings as $item) {
                    // dd($item['rooms']['id']);
                    $listingBcom = Listing::create([
                        'user_id' => json_encode(["$channel->user_id"]),
                        'listing_id' => $item['id'],
                        'listing_json' => json_encode($item),
                        'channel_id' => $channel->id,
                    ]);
                    ListingRelation::create([
                        'listing_id_airbnb' => $listing->id,
                        'listing_id_other_ota' => $listingBcom->id,
                        'listing_type' => 'BookingCom',
                    ]);


                    // dd(count($item['rooms']['rates'][0]['derived_rate_plan_ids']), $item);
                    $rate_plan = $this->createRatePlan('Bcom', $listing_setting->default_daily_price, $property, $channel->user_id, $listingBcom, $room_type);

                    $payload['channel']['rate_plans'][] = [
                        'rate_plan_id' => $rate_plan->ch_rate_plan_id,
                        'settings' => [
                            'occ_changed' => false,
                            'occupancy' => $item['rooms']['rates'][0]['max_persons'],
                            'pricing_type' => 'RLO',
                            'primary_occ' => true,
                            'rate_plan_code' => $item['rooms']['rates'][0]['id'],
                            'readonly' => false,
                            'room_type_code' => $item['rooms']['id']
                        ]
                    ];
                    // $payload['channel']['settings'] = [
                    //     "hotel_id" => $request->hotel_id,
                    // ];
                    // dd($payload);
                    //     "settings" => [
                    //     "email" => "",
                    //     "hotel_id" => $request->hotel_id,
                    //     "send_email_notifications" => false,
                    //     "mappingSettings" => new \stdClass(), // empty object {}
                    //     "derived_option" => new \stdClass(),  // empty object {}
                    // ],
                    // dd($payload);
                    $ch_channel_id = $responseData['data']['attributes']['id'];
                    // dd($payload);
                    $response = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->put("https://app.channex.io/api/v1/channels/$ch_channel_id", $payload);
                    if ($response->successful()) {
                        $response = $response->json();
                        $this->syncCalenders('Bcom', $listing, $listingBcom);
                        $response = Http::withHeaders([
                            'user-api-key' => env('CHANNEX_API_KEY'),
                        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/activate");
                        if ($response->successful()) {
                            $response = $response->json();
                            // dd($response);
                        } else {
                            $error = $response->body();
                            // dd($error);
                        }
                        // dd($response);
                    } else {
                        $error = $response->body();
                        // dd($error);
                    }
                }
            } else {
                $error = $response->body();
                // dd($error);
            }
            // Fetch Listings
            return redirect()->route('channel-management.index');
            // return response()->json([
            //     'success' => true,
            //     'data' => $responseData
            // ]);
        } else {
            $error = $response->json();
            \Log::error('Channex API request failed', ['error' => $error]);

            return response()->json([
                'success' => false,
                'error' => $error,
            ], 500);
        }
    }


    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'title' => 'required',
    //         'listing_id' => 'required',
    //         'hotel_id' => 'required',
    //         'room_id' => 'required',
    //     ]);

    //     $listing = Listing::where('id', $request->listing_id)->first();
    //     // dd($listing);
    //     $room_type = RoomType::where('listing_id', $listing->listing_id)->first();
    //     $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
    //     $property = Properties::where('id', $room_type->property_id)->first();
    //     dd($property, $rate_plan);
    //     // Send the POST request to the Channex API
    //     $response = Http::withHeaders([
    //         'user-api-key' => env('CHANNEX_API_KEY'),
    //     ])->post('https://app.channex.io/api/v1/channels', [
    //                 "channel" => [
    //                     "channel" => "BookingCom",
    //                     "group_id" => $property->ch_group_id,
    //                     "is_active" => false,
    //                     "title" => $request->title,
    //                     "known_mappings_list" => [],
    //                     "properties" => ["$property->ch_property_id"],
    //                     "rate_plans" => [],
    //                     "settings" => [
    //                         "hotel_id" => $request->hotel_id
    //                     ]
    //                 ]
    //             ]);

    //     if ($response->successful()) {
    //         $responseData = $response->json();
    //         // Handle the successful response (e.g., return or process the response data)
    //         return $responseData;
    //     } else {
    //         // Handle the error response
    //         $error = $response->json();
    //         \Log::error('Channex API request failed', ['error' => $error]);
    //         return response()->json(['error' => 'Failed to create channel'], 500);
    //     }
    //     // Validate the incoming request (as per your provided validation rules)

    //     https://app.channex.io/api/v1/channels



    //     dd('die');
    //     $response = Http::withHeaders([
    //         'user-api-key' => env('CHANNEX_API_KEY'),
    //     ])->get(env('CHANNEX_URL') . "/api/v1/channels/$request->ch_channel_id");
    //     if ($response->successful()) {
    //         $data = $response->json();
    //         // dd();
    //         if ($data['data']['attributes']['channel'] == 'VRBO') {
    //             // dd($data, $request->all());
    //             return view('Admin.channels-management.create_vrbo', ['request' => $request->all()]);
    //         }
    //         $hotel_id = $data['data']['attributes']['settings']['hotel_id'];
    //         // dd($data['data']['attributes']['settings']['hotel_id']);
    //         $channel = Channels::create($request->all());
    //         $group_id = $data['data']['relationships']['group']['data']['id'];
    //         $property_id = $data['data']['relationships']['properties']['data'][0]['id'];


    //         $response = Http::withHeaders([
    //             'user-api-key' => env('CHANNEX_API_KEY'),
    //         ])->get(env('CHANNEX_URL') . "/api/v1/groups/$group_id");
    //         if ($response->successful()) {
    //             // Channels::create($request->all());
    //             $data = $response->json();
    //             $grData['group_name'] = $data['data']['attributes']['title'];
    //             $grData['user_id'] = $request->user_id;
    //             $grData['ch_group_id'] = $data['data']['attributes']['id'];
    //             // $groupCreated = Group::create($grData);
    //         }

    //         $response = Http::withHeaders([
    //             'user-api-key' => env('CHANNEX_API_KEY'),
    //         ])->get(env('CHANNEX_URL') . "/api/v1/properties/$property_id");
    //         if ($response->successful()) {
    //             // Channels::create($request->all());
    //             $data = $response->json();
    //             // dd($data['data']['attributes']);
    //             $data['title'] = $data['data']['attributes']['title'];
    //             $data['currency'] = $data['data']['attributes']['currency'];
    //             $data['email'] = $data['data']['attributes']['email'];
    //             $data['country'] = '';
    //             $data['city'] = '';
    //             $data['user_id'] = $request->user_id;
    //             $data['ch_property_id'] = $data['data']['attributes']['id'];
    //             $data['ch_group_id'] = $grData['ch_group_id'];
    //             // $data['group_id'] = $groupCreated->id;
    //             // Properties::create($data);

    //             $webhook = Http::withHeaders([
    //                 'user-api-key' => env('CHANNEX_API_KEY'),
    //             ])->post(env('CHANNEX_URL') . '/api/v1/webhooks', [
    //                         "webhook" => [
    //                             "property_id" => $property_id,
    //                             "callback_url" => 'https://admin.livedin.co/api/webhook',
    //                             "event_mask" => "*",
    //                             "is_active" => true,
    //                             "send_data" => true,
    //                         ]
    //                     ]);
    //             if ($webhook->successful()) {
    //                 $webhook = $webhook->json();
    //                 //                dd($webhook);
    //             }

    //             $listings = array();

    //             $response = Http::withHeaders([
    //                 'user-api-key' => env('CHANNEX_API_KEY'),
    //             ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details", [
    //                         "channel" => "BookingCom",
    //                         "settings" => [
    //                             "hotel_id" => $hotel_id
    //                         ],
    //                     ]);

    //             if ($response->successful()) {
    //                 $response = $response->json();
    //                 //    dd($response['data']['rooms']);
    //                 foreach ($response['data']['rooms'] as $item) {
    //                     $listingData = [
    //                         "id" => $item['id'],
    //                         "type" => "apartment",
    //                         "title" => $item['title'],
    //                         "occupancies" => $item['rates'][0]['occupancies'],
    //                         "rooms" => $item
    //                     ];
    //                     array_push($listings, $listingData);
    //                 }

    //                 //    dd($listings);

    //                 foreach ($listings as $item) {
    //                     Listing::create([
    //                         'user_id' => json_encode(["$request->user_id"]),
    //                         'listing_id' => $item['id'],
    //                         'listing_json' => json_encode($item),
    //                         'channel_id' => $channel->id,
    //                     ]);
    //                 }

    //             } else {
    //                 $error = $response->body();
    //                 dd($error);
    //             }
    //             // dd($listings);

    //         }
    //         // dd($data['data']['relationships'], $property_id);
    //     } else {

    //     }

    //     return redirect()->route('channel-management.index')->with('success', 'Channel Created Successfully');
    // }

    public function saveVrboListing($data, $datas)
    {
        // dd($data['data']['id'], $datas);
        $channel = Channels::where('ch_channel_id', $data['data']['id'])->first();
        // dd($channel->user_id);
        $listings = array();
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details", [
                    "channel" => "VRBO",
                    "settings" => [
                        "username" => $datas['data']['token']['username'],
                        "password" => $datas['data']['token']['password'],
                        "token" => $datas['data']['token']
                    ],
                ]);

        if ($response->successful()) {
            $response = $response->json();
            //    dd($response['data']['property_id_dictionary']['values']);
            foreach ($response['data']['property_id_dictionary']['values'] as $item) {
                $parts = explode('.', $item['id']);
                // dd($parts);
                if (count($parts) > 2) {
                    // Reconstruct the number up to the second decimal point
                    $correctedValue = $parts[0] . $parts[1] . $parts[2];
                    // dd($correctedValue);
                    $result = (float) $correctedValue; // Convert to float
                }
                // dd($result);
                $listingData = [
                    "id" => $item['id'],
                    "type" => "apartment",
                    "title" => $item['title'],
                    "occupancies" => 1,
                    "rooms" => $item
                ];
                array_push($listings, $listingData);
            }

            //    dd($listings);

            foreach ($listings as $item) {
                Listing::create([
                    'user_id' => json_encode(["$channel->user_id"]),
                    'listing_id' => $item['id'],
                    'listing_json' => json_encode($item),
                    'channel_id' => $channel->id,
                ]);
            }

        } else {
            $error = $response->body();
            // dd( $error);
        }
    }

    public function authenticateVrbo(Request $request)
    {
        $channelDb = Channels::where('ch_channel_id', $request->ch_channel_id)->first();
        // dd($request,$channelDb);
        // dd($channelDb->ch_channel_id);

        // $response = Http::withHeaders([
        //     'user-api-key' => env('CHANNEX_API_KEY'),
        // ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details",[
        //     "channel"=> "VRBO",
        //     "settings"=> [
        //         "username" => "operations@livedin.co",
        //         "password" => "Livedin2025$",
        //         "token" => [
        //                 "status" => "verified",
        //                 "password" => "Livedin2025$",
        //                 "session_id" => "d8e73453-af0b-4f69-9e38-e9c3963ba6d6",
        //                 "username" => "operations@livedin.co",
        //                 "device_id" => "ea6d29b3347932b8",
        //                 "visitor_id" => "fa5cedf6-579a-4f31-ab7c-e301eff18bb8",
        //                 "ha_session" => "39cdbab7-7d5a-4496-83ad-77e77746a97b",
        //                 "hatgc_lotc" => "tgt-099371b5-5835-421f-b0bf-f5fe99bb79cb-production.homeaway.com-aws",
        //                 "challenge_ticket" => null,
        //             ]
        //     ],
        // ]);

        // if ($response->successful()) {
        //     $response = $response->json();
        //                dd($response['data']);
        //                foreach ($response['data']['rooms'] as $item) {
        //                 $listingData = [
        //                     "id" => $item['id'],
        //                     "type" => "apartment",
        //                     "title" => $item['title'],
        //                     "occupancies" => $item['rates'][0]['occupancies'],
        //                     "rooms" => $item
        //                ];
        //                 array_push($listings, $listingData);
        //             }

        //             //    dd($listings);

        //                foreach ($listings as $item) {
        //                 Listing::create([
        //                     'user_id' => json_encode(["1"]),
        //                     'listing_id' => $item['id'],
        //                     'listing_json' => json_encode($item),
        //                     'channel_id' => 1,
        //                 ]);
        //             }

        // } else {
        //     $error = $response->body();
        //     dd( $error);
        // }
        // dd($request->all());
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$request->ch_channel_id");
        if ($response->successful()) {
            $data = $response->json();


            // Verbo authentication
            $responses = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/meta/vrbo/authenticate", [
                        "username" => $request->username,
                        "password" => $request->password,
                    ]);

            if ($responses->successful()) {

                $datas = $responses->json();
                // dd($datas['data']);
                if (is_null($datas['data']['additional_data'])) {
                    // dd($datas['data']);
                    ChannelToken::create([
                        'type' => 'Vrbo',
                        'channel_id' => $channelDb->id,
                        'token_json' => json_encode($datas['data']['token'])
                    ]);
                    $this->saveVrboListing($data, $datas);
                    return redirect()->back();
                } else {
                    $responseMfa = Http::withHeaders([
                        'user-api-key' => env('CHANNEX_API_KEY'),
                    ])->post(env('CHANNEX_URL') . "/api/v1/meta/vrbo/request_mfa_code", [
                                "token" => $datas['data']['token'],
                                "phone" => $datas['data']['additional_data']['phones'][0]
                            ]);

                    if ($responseMfa->successful()) {
                        ChannelToken::create([
                            'type' => 'Vrbo',
                            'channel_id' => $channelDb->id,
                            'token_json' => json_encode($datas['data']['token'])
                        ]);
                        $this->saveVrboListing($data, $datas);
                        $datas = $responseMfa->json();
                        return view('Admin.channels-management.create_vrbo_otp', ['datas' => $datas, 'request' => $request]);
                    } else {
                        $errors = $responseMfa->body();
                        // dd( $errors);
                    }
                }

            } else {
                $errors = $responses->body();
                // dd( $errors);
            }
            // dd($data['data']['relationships'], $property_id);
        } else {

        }
    }

    public function authenticateVrboVerifyCode(Request $request)
    {

        $responses = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/meta/vrbo/verify_mfa_code", [
                    "token" => $request->token,
                    "code" => $request->code
                ]);

        if ($responses->successful()) {
            $datas = $responses->json();
            // $channel = Channels::create($request->all());

            $responsesChannelListing = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details", [
                        "channel" => "VRBO",
                        "settings" => [
                            "username" => "operations@livedin.co",
                            "password" => "Livedin2024$",
                            "token" => [
                                "status" => "verified",
                                "password" => "Livedin2024$",
                                "session_id" => "e0c64839-5186-4b1d-bc49-4c5e50df32ff",
                                "username" => "operations@livedin.co",
                                "device_id" => "563d253968eb82e4",
                                "visitor_id" => "11155fec-ba68-4e34-a1b8-69b6ff6b57a5",
                                "ha_session" => "97cb29d4-04b0-4211-a37d-21c4f46b8d69",
                                "hatgc_lotc" => "tgt-c6b72d4a-7dfc-4812-870f-f27b0b51cdd0-production.homeaway.com-aws",
                                "challenge_ticket" => "cc7333a8-a4a6-468b-a352-1c3368c3e83f",
                            ]
                        ],
                    ]);


            if ($responsesChannelListing->successful()) {
                $datas = $responsesChannelListing->json();
                dd($datas);
            } else {
                $errors = $responsesChannelListing->body();
                dd($errors);
            }

        } else {
            $errors = $responses->body();
            dd($errors);
        }
    }

    // public function store(Request $request)
    // {
    //     // $request->validate([
    //     //     'user_id' => 'required',
    //     //     'ch_channel_id' => 'required',
    //     //     'connection_type' => 'required',
    //     // ]);
    //     $response = Http::withHeaders([
    //         'user-api-key' => env('CHANNEX_API_KEY'),
    //     ])->get(env('CHANNEX_URL') . "/api/v1/channels/$request->ch_channel_id");
    //     if ($response->successful()) {
    //         $data = $response->json();
    //         $hotel_id = $data['data']['attributes']['settings']['hotel_id'];
    //         // dd($data['data']['attributes']['settings']['hotel_id']);
    //         $channel = Channels::create($request->all());
    //         $group_id = $data['data']['relationships']['group']['data']['id'];
    //         $property_id = $data['data']['relationships']['properties']['data'][0]['id'];


    //         $response = Http::withHeaders([
    //             'user-api-key' => env('CHANNEX_API_KEY'),
    //         ])->get(env('CHANNEX_URL') . "/api/v1/groups/$group_id");
    //         if ($response->successful()) {
    //             // Channels::create($request->all());
    //             $data = $response->json();
    //             $grData['group_name'] = $data['data']['attributes']['title'];
    //             $grData['user_id'] = $request->user_id;
    //             $grData['ch_group_id'] = $data['data']['attributes']['id'];
    //             $groupCreated = Group::create($grData);
    //         }

    //         $response = Http::withHeaders([
    //             'user-api-key' => env('CHANNEX_API_KEY'),
    //         ])->get(env('CHANNEX_URL') . "/api/v1/properties/$property_id");
    //         if ($response->successful()) {
    //             // Channels::create($request->all());
    //             $data = $response->json();
    //             // dd($data['data']['attributes']);
    //             $data['title'] = $data['data']['attributes']['title'];
    //             $data['currency'] = $data['data']['attributes']['currency'];
    //             $data['email'] = $data['data']['attributes']['email'];
    //             $data['country'] = '';
    //             $data['city'] = '';
    //             $data['user_id'] = $request->user_id;
    //             $data['ch_property_id'] = $data['data']['attributes']['id'];
    //             $data['ch_group_id'] = $grData['ch_group_id'];
    //             $data['group_id'] = $groupCreated->id;
    //             Properties::create($data);

    //             $webhook = Http::withHeaders([
    //                 'user-api-key' => env('CHANNEX_API_KEY'),
    //             ])->post(env('CHANNEX_URL') . '/api/v1/webhooks', [
    //                         "webhook" => [
    //                             "property_id" => $property_id,
    //                             "callback_url" => 'https://admin.livedin.co/api/webhook',
    //                             "event_mask" => "*",
    //                             "is_active" => true,
    //                             "send_data" => true,
    //                         ]
    //                     ]);
    //             if ($webhook->successful()) {
    //                 $webhook = $webhook->json();
    //                 //                dd($webhook);
    //             }

    //             $listings = array();

    //     $response = Http::withHeaders([
    //         'user-api-key' => env('CHANNEX_API_KEY'),
    //     ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details",[
    //         "channel"=> "BookingCom",
    //         "settings"=> [
    //             "hotel_id" => $hotel_id
    //         ],
    //     ]);

    //     if ($response->successful()) {
    //         $response = $response->json();
    //                 //    dd($response['data']['rooms']);
    //                   foreach ($response['data']['rooms'] as $item) {
    //                     $listingData = [
    //                         "id" => $item['id'],
    //                         "type" => "apartment",
    //                         "title" => $item['title'],
    //                         "occupancies" => $item['rates'][0]['occupancies'],
    //                         "rooms" => $item
    //                   ];
    //                     array_push($listings, $listingData);
    //                 }

    //                 //    dd($listings);

    //                   foreach ($listings as $item) {
    //                     Listing::create([
    //                         'user_id' => json_encode(["$request->user_id"]),
    //                         'listing_id' => $item['id'],
    //                         'listing_json' => json_encode($item),
    //                         'channel_id' => $channel->id,
    //                     ]);
    //                 }

    //     } else {
    //         $error = $response->body();
    //         dd( $error);
    //     }
    //     // dd($listings);

    //         }
    //         // dd($data['data']['relationships'], $property_id);
    //     } else {

    //     }

    //     return redirect()->route('channel-management.index')->with('success', 'Channel Created Successfully');
    // }

    public function activateChannel($id)
    {
        $channel = Channels::findOrFail($id);
        $userApiKey = env('CHANNEX_API_KEY');
        $response = Http::withHeaders([
            'user-api-key' => $userApiKey,
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/activate");

        if ($response->successful()) {
            $response = $response->json();
            //            dd($response);
            return redirect()->route('channel-management.index');
        } else {
            $error = $response->body();
            dd($error);
            return redirect()->route('channel-management.index')->with('error', $error);
        }
    }
}

<?php

namespace App\Http\Controllers\Api\Listings;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChannelResource;
use App\Jobs\SaveCalenderData;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Channels;
use App\Models\HostType;
use App\Models\Listing;
use App\Models\Listings;
use App\Models\ListingSetting;
use App\Models\ListingRelation;
use App\Models\Properties;
use App\Models\RatePlan;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Linkrepository;
use Carbon\Carbon;
use App\Models\Group;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Services\MixpanelService;
use App\Services\ChannexProxyService;
use App\Utilities\UserUtility;

class ListingManagementController extends Controller
{
    private $channelId;
    private $apiKey;
    private $mixpanelService;

    public function __construct(MixpanelService $mixpanelService)
    {
        $this->channelId = config('services.channex.channel_id');
        $this->apiKey = config('services.channex.api_key');
        $this->mixpanelService = $mixpanelService;
    }

    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {

    }
    //    public function index()
//    {
//        $listings_response = Http::withHeaders([
//            'user-api-key' => $this->apiKey,
//        ])->get(env('CHANNEX_URL')."/api/v1/channels/{$this->channelId}/action/listings");
//
//        if ($listings_response->successful()) {
//            $listings = $listings_response->json('data.listing_id_dictionary.values');
//            foreach ($listings as $listing) {
//                $listing_id = $listing['id'];
//                $redis_Key = "listing:$listing_id";
//                if (Redis::exists($redis_Key)) {
//                    $redis_data = Redis::get($redis_Key);
//                    $decoded_data = json_decode($redis_data, true);
//                    return $decoded_data;
//                } else {
//                    $listing_detail_response = Http::withHeaders([
//                        'user-api-key' => $this->apiKey,
//                    ])->get(env('CHANNEX_URL')."/api/v1/channels/{$this->channelId}/action/listing_details?listing_id={$listing_id}");
//
//                    if ($listing_detail_response->successful()) {
//                        $listing_detail_data = $listing_detail_response->json('data.listing');
//                        Redis::setex($redis_Key, 300, json_encode($listing_detail_data));
//                        return $listing_detail_data;
//                    }
//                }
//            }
//        }
//    }

    public function fetchListingsByUserId(Request $request, User $user)
    {
        $listingResponse = array();
        $channel = Channels::where('ch_channel_id', $request->channel_id)->first();
        if (isset($request->is_sync) && $request->is_sync) {
            // $listings = Listing::where('channel_id', $channel->id)->where('is_sync', $request->is_sync)->get();
            $listings = Listing::where('is_sync', $request->is_sync)->get();
            foreach ($listings as $item) {
                $listing_details = $item->toArray();
                $user_arr = json_decode($listing_details['user_id']);
                if (in_array($user->id, $user_arr)) {
                    $listing = json_decode($item->listing_json);
                    $listing->is_sync = $item->is_sync;
                    $linkRepositoryData = LinkRepository::where('listing_id', $item->id)->where('status', 'Published')->get();
                    $listing->link_repository = $linkRepositoryData;
                    array_push($listingResponse, $listing);
                }
            }
        } else {
            // $listings = Listing::where('channel_id', $channel->id)->get();
            $listings = Listing::all();
            foreach ($listings as $item) {
                $listing_details = $item->toArray();
                $user_arr = json_decode($listing_details['user_id']);
                if (in_array($user->id, $user_arr)) {
                    $listing = json_decode($item->listing_json);

                    $listing->is_sync = $item->is_sync;
                    $linkRepositoryData = LinkRepository::where('listing_id', $item->id)->where('status', 'Published')->get();
                    $listing->link_repository = $linkRepositoryData;
                    array_push($listingResponse, $listing);
                }

            }
            //            $listings = Listing::where('user_id', $user->id)->get();
        }
        //        $listings = Listing::where('channel_id', $channel->id)->get();
//        foreach ($listings as $item) {
//            $listing = json_decode($item->listing_json);
//
//            $listing->is_sync =  $item->is_sync;
//
//            array_push($listingResponse, $listing);
//        }

        if (!empty($user->role_id) && $user->role_id === 2) {

            try {

                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Listing Module Opened', [
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

        return response()->json($listingResponse);
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

    //     public function pricingSetting($id)
//     {
//         $listing = Listings::where('listing_id',$id)->first();
//         $channel = Channels::whereId($listing->channel_id)->first();
//         $users = User::all();
//         $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
//         if($channel->connection_type != null) {
//             return view('Admin.listings-management.edit', ['listing' => $listing, 'users' => $users, 'rate_plan' => $rate_plan->ch_rate_plan_id]);
//         }

    //         // dd( $channel);


    //         $listing_settings = ListingSetting::where('listing_id', $listing->listing_id)->first();
//         $listing_settings = $listing_settings->toArray();

    //       $response = Http::withHeaders([
//           'user-api-key' => env('CHANNEX_API_KEY'),
//       ])->post(env('CHANNEX_URL')."/api/v1/channels/$channel->ch_channel_id/execute/load_listing_price_settings", [
//           "listing_id" => $listing->listing_id
//       ]);

    //       if ($response->successful()) {
//           $response = $response->json();
//         //   dd($response['data']);
//           $listing_settings['ch_pricing_settings'] = $response['data'];
// //                dd($ari);
//       } else {
//           $error = $response->body();
//         //    dd($error);
//       }

    //       return response()->json($listing_settings);
//     }

    public function pricingSetting($listing_id)
    {
        $listing = Listing::where('listing_id', $listing_id)->first();
        $channel = Channels::whereId($listing->channel_id)->first();
        $users = User::all();
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();

        if ($channel->connection_type != null) {
            return response()->json(["success" => false, "message" => "Connect type is incorrect"], 500);
        }

        $listing_settings = ListingSetting::where('listing_id', $listing->listing_id)->first();
        $listing_settings = $listing_settings?->toArray();

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/execute/load_listing_price_settings", [
                    "listing_id" => $listing->listing_id
                ]);

        if ($response->successful()) {
            $response = $response->json();
            $data = $response['data'];

            $standardFees = [];
            foreach ($data['standard_fees'] as $fee) {
                $key = strtolower(str_replace('', '', $fee['fee_type']));
                $standardFees[$key] = $fee['amount'];
            }
            $data['standard_fees'] = $standardFees;


            $listing_settings['ch_pricing_settings'] = $data;

            $listing_settings['airbnb_discounts'] = $this->getDiscounts($channel->ch_channel_id, $listing->listing_id);

            return $listing_settings;
        } else {
            $error = $response->body();
        }
    }

    protected function getDiscounts($channelId, $listingId)
    {
        $channexProxyService = new ChannexProxyService();
        $data = [
            'request' => [
                'endpoint' => "/pricing_and_availability/standard/pricing_settings/" . $listingId,
                'method' => 'get',
            ],
        ];

        $response = $channexProxyService->postToProxy($channelId, $data);

        if (!empty($response['data']['pricing_setting']['default_pricing_rules'])) {
            $responseData = $response['data']['pricing_setting']['default_pricing_rules'];
            $result = [];
            if ($responseData && count($responseData) > 0) {
                foreach ($responseData as $data) {
                    if ($data['rule_type'] == 'BOOKED_WITHIN_AT_MOST_X_DAYS') {
                        $result['last_minute'] = [
                            "threshold_one" => $data['threshold_one' ?? 0],
                            "price_change" => $data['price_change'] ?? 0
                        ];
                    }
                    if ($data['rule_type'] == 'BOOKED_BEYOND_AT_LEAST_X_DAYS') {
                        $result['early_bird'] = [
                            "threshold_one" => $data['threshold_one' ?? 0],
                            "price_change" => $data['price_change'] ?? 0
                        ];
                    }
                }

                return $result;
            }
        }

    }

    public function updatePricingSetting(Request $request, $listing_id)
    {
        $request->validate([
            'default_daily_price' => 'numeric|min:1',
            'early_bird_price_change' => 'nullable|numeric',
            'early_bird_threshold_one' => 'nullable|numeric',
            'last_minute_price_change' => 'nullable|numeric',
            'last_minute_threshold_one' => 'nullable|numeric',
            'guests_included' => 'numeric|min:1',
            'price_per_extra_person' => 'nullable|numeric',
            'security_deposit' => 'nullable|numeric',
            'pass_through_resort_fee' => 'nullable|numeric',
            'weekend_price' => 'nullable|numeric',
            'weekly_price_factor' => 'nullable|numeric',
            'pass_through_community_fee' => 'nullable|numeric',
            'pass_through_linen_fee' => 'nullable|numeric',
            'pass_through_cleaning_fee' => 'nullable|numeric',
            'pass_through_short_term_cleaning_fee' => 'nullable|numeric',
        ]);

        $data = $request->all();
        $listing = Listing::where('listing_id', $listing_id)->first();
        $listing_setting = ListingSetting::where('listing_id', $listing_id)->first();
        $channel = Channels::where('id', $listing->channel_id)->first();
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
        $property = Properties::where('id', $rate_plan->property_id)->first();

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id");

        if ($response->failed()) {
            return response()->json(['message' => $response->body()], 500);
        }

        $response = $response->json();
        $channex_channel_rate_plan = $response['data']['attributes']['rate_plans'];

        foreach ($channex_channel_rate_plan as $item) {
            if ($item['rate_plan_id'] == $rate_plan['ch_rate_plan_id']) {
                $channel_rate_plan = $item;
                break;
            }
        }

        if (isset($data['instant_booking']) && $data['instant_booking']) {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->put(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/execute/update_booking_setting", [
                        "channel_rate_plan_id" => $channel_rate_plan['id'],
                        "data" => [
                            "check_in_time_end" => $channel_rate_plan['settings']['booking_setting']['check_in_time_end'],
                            "check_in_time_start" => $channel_rate_plan['settings']['booking_setting']['check_in_time_start'],
                            "check_out_time" => $channel_rate_plan['settings']['booking_setting']['check_out_time'],
                            "instant_booking_allowed_category" => $data['instant_booking'],
                        ]
                    ]);

            if ($response->failed()) {
                return response()->json(['message' => $response->body()], 500);
            }

            $response = $response->json();
            $listing_setting->update(['instant_booking' => $data['instant_booking']]);
        }

        $user_api_key = env('CHANNEX_API_KEY');
        $channel_id = $channel->ch_channel_id;

        $standard_fees = [];

        $fees_mapping = [
            'pass_through_community_fee' => 'PASS_THROUGH_COMMUNITY_FEE',
            'pass_through_linen_fee' => 'PASS_THROUGH_LINEN_FEE',
            'pass_through_resort_fee' => 'PASS_THROUGH_RESORT_FEE',
            'pass_through_cleaning_fee' => 'PASS_THROUGH_CLEANING_FEE',
            'pass_through_short_term_cleaning_fee' => 'PASS_THROUGH_SHORT_TERM_CLEANING_FEE',
            'pass_through_long_term_cleaning_fee' => 'PASS_THROUGH_LONG_TERM_CLEANING_FEE',
        ];

        foreach ($fees_mapping as $key => $fee_type) {
            if (!empty($data[$key])) {
                $standard_fees[] = [
                    "amount" => $data[$key],
                    "amount_type" => "flat",
                    "charge_period" => "PER_BOOKING",
                    "charge_type" => "PER_GROUP",
                    "fee_type" => $fee_type,
                    "offline" => false,
                ];
            }
        }

        $final_data = [
            "channel_rate_plan_id" => $channel_rate_plan['id'],
            "data" => [
                "listing_currency" => "SAR",
                "default_daily_price" => $request->default_daily_price,
                "guests_included" => $request->guests_included ?? 0,
                "monthly_price_factor" => $request->monthly_price_factor ?? 0,
                "price_per_extra_person" => $request->price_per_extra_person ?? 0,
                "security_deposit" => $request->security_deposit ?? 0,
                "weekend_price" => $request->weekend_price ?? 0,
                "weekly_price_factor" => $request->weekly_price_factor ?? 0,
                "standard_fees" => $standard_fees

            ]
        ];
        // return $final_data;
        $json_data = json_encode($final_data);
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://app.channex.io/api/v1/channels/' . $channel_id . '/execute/update_pricing_setting',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $json_data,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'user-api-key: ' . $user_api_key
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response === false) {
            return response()->json(['message' => 'Failed to update pricing setting'], 500);
        }

        $decodedResponse = json_decode($response);

        if (isset($decodedResponse->errors->details->pricing_setting)) {
            $pricingSetting = $decodedResponse->errors->details->pricing_setting;

            // Check if it's an array and fetch the first element, otherwise use as is.
            $message = is_array($pricingSetting) ? $pricingSetting[0] : $pricingSetting;

            return response()->json([
                "success" => false,
                "message" => $message
            ], 500);
        }


        $listing_setting->update($final_data['data']);

        // Add Airbnb Discounts
        $channexProxyService = new ChannexProxyService();
        $rules = [];

        if ($request->early_bird_price_change < 0) {
            $rules[] = [
                "rule_type" => "BOOKED_BEYOND_AT_LEAST_X_DAYS",
                "price_change" => $request->early_bird_price_change,
                "price_change_type" => "PERCENT",
                "threshold_one" => $request->early_bird_threshold_one
            ];
        }

        if ($request->last_minute_price_change < 0) {
            $rules[] = [
                "rule_type" => "BOOKED_WITHIN_AT_MOST_X_DAYS",
                "price_change" => $request->last_minute_price_change,
                "price_change_type" => "PERCENT",
                "threshold_one" => $request->last_minute_threshold_one
            ];
        }

        if (is_numeric($request->weekly_price_factor)) {
            $rules[] = [
                "rule_type" => "STAYED_AT_LEAST_X_DAYS",
                "price_change" => -($request->weekly_price_factor),
                "price_change_type" => "PERCENT",
                "threshold_one" => 7
            ];
        }

        if (is_numeric($request->monthly_price_factor)) {
            $rules[] = [
                "rule_type" => "STAYED_AT_LEAST_X_DAYS",
                "price_change" => -($request->monthly_price_factor),
                "price_change_type" => "PERCENT",
                "threshold_one" => 28
            ];
        }

        $data = [
            'request' => [
                'endpoint' => "/pricing_and_availability/standard/pricing_settings/" . $request->listing_id,
                'method' => 'put',
                'payload' => [
                    "default_pricing_rules" => $rules
                ],
            ],
        ];

        $response = $channexProxyService->postToProxy($channel_id, $data);

        return response()->json(['message' => 'Pricing setting updated successfully']);
    }


    public function fetchListingDetailsByListingID(Request $request)
    {
        $response = Http::withHeaders([
            'user-api-key' => $this->apiKey,
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$request->channel_id/action/listing_details?listing_id=$request->listing_id");

        if ($response->successful()) {
            $response = $response->json();
            $listing = $response['data']['listing'];
            return response()->json($listing);
        } else {
            $error = $response->body();
            dd($error);
        }
    }

    public function getUserChannel(User $user)
    {
        $channel = Channels::where('user_id', $user->id)->first();
        if ($channel === null) {
            return response()->json(['error', "Channel Not Available"]);
        } else {
            return new ChannelResource($channel);
        }
    }

    /**
     * @param $listing_id
     * @param $channel_id
     * @return mixed|string
     */
    public function fetchListingDetails($listing_id, $channel_id): mixed
    {
        $response = Http::withHeaders([
            'user-api-key' => $this->apiKey,
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/{$channel_id}/action/listing_details?listing_id=$listing_id");
        if ($response->successful()) {
            $response = $response->json();
            return $response['data']['listing'];
        } else {
            return $response->body();
        }
    }

    /**
     * @param $channel_id
     * @param $listing_id
     * @param $property_id
     * @return void
     */
    public function pullFutureReservation($channel_id, $listing_id, $property_id): void
    {
        $pullFutureReservation = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel_id/action/load_future_reservations", [
                    "listing_id" => $listing_id
                ]);
        if ($pullFutureReservation->successful()) {
            $pullFutureReservation->json();
            $this->saveBookingInDB($channel_id, $listing_id, $property_id);

        } else {
            $error = $pullFutureReservation->body();
        }
    }

    /**
     * @param $channel_id
     * @param $listing_id
     * @param $rate_plan_id
     * @return string|void
     */
    public function mapListingByRatePlan($channel_id, $listing_id, $rate_plan_id)
    {
        $mapping = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel_id/mappings", [
                    "mapping" => [
                        "rate_plan_id" => $rate_plan_id,
                        'settings' => [
                            'listing_id' => $listing_id,
                        ]
                    ]
                ]);
        if ($mapping->successful()) {
            //            $listing = Listing::where('listing_id', $listing_id)->first();
//            $listing->update([
//                'is_sync' => 'sync_all'
//            ]);
            DB::select("UPDATE listings
                    SET is_sync = 'sync_all'
                    WHERE listing_id = $listing_id");
        } else {

            // Log::info('Mapping Respone Error: ', ['listing_id' => json_encode($mapping->body())]);

            return $mapping->body();


            //                    dd($error);
        }
    }

    /**
     * @param $listing_id
     * @param $property
     * @param $user
     * @param $listing
     * @return string
     */
    public function createRoomType($listing_id, $property, $user, $listing)
    {
        $room_type = RoomType::create([
            'user_id' => $user->id,
            'listing_id' => $listing_id,
            'property_id' => $property->id,
            'title' => $listing['name'],
            'count_of_rooms' => 1,
            'occ_adults' => $listing['person_capacity'],
            'occ_children' => 0,
            'occ_infants' => 0,
        ]);
        $roomTypeCh = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/room_types', [
                    "room_type" => [
                        "property_id" => $property->ch_property_id,
                        'title' => $listing['name'] . $listing_id,
                        'count_of_rooms' => 1,
                        'occ_adults' => 1,
                        'occ_children' => 0,
                        'occ_infants' => 0,
                    ]
                ]);

        if ($roomTypeCh->successful()) {
            $roomTypeCh = $roomTypeCh->json();

            $messageApp = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/applications/install", [
                        'application_installation' => [
                            'property_id' => $property->ch_property_id,
                            'application_id' => 'd5c07f16-52f7-4afb-a884-dfe2d1cd7103'
                        ]
                    ]);
            if ($messageApp->successful()) {
            } else {
                $error = $messageApp->body();
            }
            // Log::info('Success Room Type Response:', ['response' => $roomTypeCh]);

            //                    dd($roomTypeCh);
            $roomTypeDB = RoomType::where('id', $room_type->id)->first();
            $roomTypeDB->update(['ch_room_type_id' => $roomTypeCh['data']['attributes']['id']]);
            return $roomTypeDB;
        } else {

            // Log::info('Error Room Type Response:', ['response' => $roomTypeCh->body()]);

            return $roomTypeCh->body();
            //                    dd($error);
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
        if (isset($ari[$rate_plan->ch_rate_plan_id])) {
            // Get all values for the specific key
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
    public function createRatePlan($listing_id, $property, $user, $listing, $room_type)
    {
        $rate_plan = RatePlan::create([
            'user_id' => $user->id,
            'listing_id' => $listing_id,
            'property_id' => $property->id,
            'room_type_id' => $room_type['id'],
            'title' => $listing['name'] . $listing_id,
            'occupancy' => count($listing['rooms']),
            'is_primary' => true,
            'rate' => $listing['pricing_settings']['default_daily_price'] * 100
        ]);
        //                dd($property->ch_property_id,$roomTypeDB->ch_room_type_id);
        $ratePlanCh = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/rate_plans', [
                    "rate_plan" => [
                        "property_id" => $property->ch_property_id,
                        'room_type_id' => $room_type['ch_room_type_id'],
                        'title' => $listing['name'] . $listing_id,
                        'options' => [
                            [
                                'occupancy' => $listing['person_capacity'],
                                'is_primary' => true,
                                'rate' => $listing['pricing_settings']['default_daily_price'] * 100
                            ]
                        ]
                    ]
                ]);
        if ($ratePlanCh->successful()) {
            $ratePlanCh = $ratePlanCh->json();
            $ratePlanDB = RatePlan::where('id', $rate_plan->id)->first();
            $ratePlanDB->update(['ch_rate_plan_id' => $ratePlanCh['data']['attributes']['id']]);
            $ch_room_type_id = $room_type['ch_room_type_id'];
            $ch_rate_plan_id = $ratePlanCh['data']['attributes']['id'];

            ListingSetting::create(
                [
                    'listing_id' => $listing_id,
                    'rate_plan_id' => $ch_rate_plan_id,
                    'listing_currency' => 'SAR',
                    'instant_booking' => 'everyone',
                    'default_daily_price' => $listing['pricing_settings']['default_daily_price'],
                ]
            );


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
                Log::info('avail sync', ['response' => $availability]);
            } else {
                $error = $response->body();
                //                dd($error);
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
                                "availability" => count($listing['rooms']),
                                "max_stay" => $listing['availability_rules']['default_max_nights'],
                                "min_stay" => $listing['availability_rules']['default_min_nights'],
                                "rate" => $listing['pricing_settings']['default_daily_price'] * 100,
                            ]
                        ]
                    ]);

            if ($response->successful()) {
                $restrictions = $response->json();
                Log::info('rest sync', ['response' => $restrictions]);
                //Save Calender Data
                $this->saveCalenderData($listing_id, $property->ch_property_id, $startDate, $endDate);
            } else {
                $error = $response->body();
                //                dd($error);
            }

            return $ratePlanDB;
        } else {
            return $ratePlanCh->body();
        }
    }

    /**
     * @param $channel_id
     * @return JsonResponse|string
     */
    public function activateChannel($channel_id): JsonResponse|string
    {
        $channel_activation = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel_id/activate");
        if ($channel_activation->successful()) {
            //            $channel_activation = $channel_activation->json();
            return response()->json(['success', "Your Listing Has Been Mapped Successfully"]);
        } else {
            return $channel_activation->body();
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

    public function getChannelInfo($channel_id)
    {
        // dd($channel_id,$listing_id);
        $response = Http::withHeaders([
            'user-api-key' => $this->apiKey,
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel_id");
        if ($response->successful()) {
            $response = $response->json();
            // dd($response['data']['attributes']['settings']);
            return $response['data'];
        } else {
            return $response->body();
        }
    }
    public function createProperty($group, $user, $listing)
    {
        // dd($listing['name']);
        // $listing_json = json_decode($listing->listing_json);
        // $listing_title = $listing_json
        // dd($listing_json, $group);
        $property = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/properties', [
                    "property" => [
                        "title" => strtolower($listing['name']) . $listing['id'],
                        "currency" => 'USD',
                        "timezone" => 'Asia/Riyadh',
                        "email" => $user->email,
                        "country" => substr($user->country, -2),
                        "city" => $user->city,
                        "property_type" => "apartment",
                        "group_id" => $group->ch_group_id,
                        "settings" => [
                            "min_stay_type" => 'through'
                        ]
                    ]
                ]);
        if ($property->successful()) {
            $property = $property->json();
            $data['title'] = strtolower($listing['name']) . $listing['id'];
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
    public function updateChannel($channel_id, $property)
    {
        $channelInfo = $this->getChannelInfo($channel_id);

        $channelInfo['attributes']['settings']['derived_option'] = null;
        $propertyId = $property->ch_property_id;

        // Check if property ID exists in the array
        if (in_array($propertyId, $channelInfo['attributes']['properties'])) {
            // Remove it
            $channelInfo['attributes']['properties'] = array_filter(
                $channelInfo['attributes']['properties'],
                fn($id) => $id !== $propertyId
            );
        } else {
            // Add it
            $channelInfo['attributes']['properties'][] = $propertyId;
        }
        // dd($channelInfo['attributes']['properties'], $propertyId);
        $channel_id = $channelInfo['attributes']['id'];

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->put(env('CHANNEX_URL') . "/api/v1/channels/$channel_id", [
                    "channel" => $channelInfo['attributes']
                ]);

        if ($response->successful()) {
            $channel = $response->json();
            // Optional: log or return success
        } else {
            $error = $response->body();
            // Optional: log or handle error
        }
    }

    public function getMappingInfo($channel_id, $listing_id)
    {
        $channelInfo = $this->getChannelInfo($channel_id);
        $channelInfo = $channelInfo['attributes']['settings']['tokens'];
        // dd($channelInfo['user_id']);
        $response = Http::withHeaders([
            'user-api-key' => $this->apiKey,
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details", [
                    "channel" => "AirBNB",
                    "settings" => [
                        "tokens" => [
                            "access_token" => $channelInfo['access_token'],
                            "user_id" => $channelInfo['user_id']
                        ]
                    ]
                ]);
        if ($response->successful()) {
            $response = $response->json();
            dd($response);
            foreach ($response['data']['listing_id_dictionary']['values'] as $item) {
                $item['id'] = (int) $item['id'];
                if ($item['id'] == $listing_id) {
                    dd($item);
                }
                dd($item['id']);
            }
            return $response['data']['attributes']['settings'];
        } else {
            $error = $response->body();
            dd($error);
        }
    }

    public function removeMapping($channel_id, $listing_id)
    {
        $channelInfo = $this->getChannelInfo($channel_id);
        // dd($channelInfo['attributes']['rate_plans']);
        foreach ($channelInfo['attributes']['rate_plans'] as $item) {
            $item['settings']['listing_id'] = (int) $item['settings']['listing_id'];
            if ($item['settings']['listing_id'] == $listing_id) {
                // dd($item['id']);
                $mapping_id = $item['id'];
                $response = Http::withHeaders([
                    'user-api-key' => $this->apiKey,
                ])->delete(env('CHANNEX_URL') . "/api/v1/channels/$channel_id/mappings/$mapping_id");
                if ($response->successful()) {
                    $response = $response->json();
                    return $response;
                } else {
                    $error = $response->body();
                    dd($error);
                }
            }
            // dd($item['settings']['listing_id']);
        }

    }
    public function mapListing(Request $request, User $user): JsonResponse
    {
        $group = Group::where('user_id', $user->id)->first();
        $listing = Listing::where('listing_id', $request->listing_id)->first();
        $channel = Channels::where('id', $listing->channel_id)->first();
        $request->channel_id = $channel->ch_channel_id;
        // Log::info('Map Listing ID: ', ['listing_id' => $request->listing_id]);

        if ($listing->is_sync == 'sync_all') {
            $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
            $property = Properties::where('id', $rate_plan->property_id)->first();
            $this->updateChannel($request->channel_id, $property);
            $this->removeMapping($request->channel_id, $listing->listing_id);
            $listing->update(['is_sync' => '']);
            return response()->json([
                'code' => 100,
                'message' => 'listing unmapped successfully'
            ]);
        } else {
            $listing = $this->fetchListingDetails($request->listing_id, $request->channel_id);
            $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
            if ($rate_plan) {
                $property = Properties::where('id', $rate_plan->property_id)->first();
                $this->updateChannel($request->channel_id, $property);
                $this->mapListingByRatePlan($request->channel_id, $request->listing_id, $rate_plan->ch_rate_plan_id);
                $this->activateChannel($request->channel_id);
                $this->pullFutureReservation($request->channel_id, $request->listing_id, $property->ch_property_id);
                DB::update("UPDATE listings
            SET is_sync = 'sync_all'
            WHERE listing_id = ?", [$request->listing_id]);
                return response()->json([
                    'code' => 100,
                    'message' => 'listing mapped successfully'
                ]);
            } else {
                $property = $this->createProperty($group, $user, $listing);
                $this->updateChannel($request->channel_id, $property);
                $room_type = $this->createRoomType($request->listing_id, $property, $user, $listing);
                $rate_plan = $this->createRatePlan($request->listing_id, $property, $user, $listing, $room_type);
                $this->mapListingByRatePlan($request->channel_id, $request->listing_id, $rate_plan->ch_rate_plan_id);
                $this->activateChannel($request->channel_id);
                $this->pullFutureReservation($request->channel_id, $request->listing_id, $property->ch_property_id);
                return response()->json([
                    'code' => 100,
                    'message' => 'listing mapped successfully'
                ]);
            }
        }
    }
    public function getPerformanceData(Request $request)
    {
        $channel_id = request('channel_id');
        $channel = Channels::where('ch_channel_id', $channel_id)->first();

        $listings = Listing::where('channel_id', $channel->id)->get();

        $listings = Listing::all();
        $listing_arr = array();
        $booking_arr = array();
        $user_id = Auth::user()->id;
        $userDB = User::whereId($user_id)->first();
        $hostType = HostType::where('id', $userDB->host_type_id)->first();
        foreach ($listings as $item) {
            $users = json_decode($item['user_id']);
            if (in_array($user_id, $users)) {
                array_push($listing_arr, $item);
            }
        }
        // dd($listing_arr[0]->commission_value);
        $stayGreaterThanOne = 0;
        $stayGreaterThanTwo = 0;
        $stayGreaterThanThree = 0;
        $stayGreaterThanSeven = 0;
        $stayGreaterThanThirty = 0;

        $listing_ids = array();
        $booking_listing_ids = array();
        $booking_listing_idsSys = array();


        $total_amount_system = 0;
        $occupancy_system = 0;
        $revenue_system = 0;
        $nights_system = 0;
        $earnings_system = 0;
        $daysCountBookingRev_system = 0;
        $ota_commitionSys = 0;
        $adr_system = 0;
        $ota_discount_system = 0;

        foreach ($listing_arr as $item) {
            // dd($item->commission_value);
            array_push($listing_ids, $item->listing_id);

            if ($request->start_date !== null && $request->end_date !== null) {
                $startDateSystem = Carbon::parse($request->start_date)->toDateString();
                $endDateSystem = Carbon::parse($request->end_date)->toDateString();
                // dd($startDateSystem, $endDateSystem);
                $daysCount_system = Carbon::parse($startDateSystem)->diffInDays(Carbon::parse($endDateSystem));
                $daysCount_system === 0 ? $daysCount_system = 1 : $daysCount_system = Carbon::parse($startDateSystem)->diffInDays(Carbon::parse($endDateSystem));
                $system_booking = Bookings::where('listing_id', $item->id)
                    // ->whereBetween('booking_date_start', [$startDateSystem, $endDateSystem])
                    ->get();

            } else {

                $currentDate1Sys = Carbon::now();
                $dateBefore7DaysSys = $currentDate1Sys->copy()->subDays(6);
                $startDateSystem = Carbon::parse($dateBefore7DaysSys)->toDateString();
                $endDateSystem = Carbon::parse($currentDate1Sys)->toDateString();
                // dd($startDateSystem, $endDateSystem);
                $daysCount_system = Carbon::parse($startDateSystem)->diffInDays(Carbon::parse($endDateSystem));
                $daysCount_system === 0 ? $daysCount_system = 1 : $daysCount_system = Carbon::parse($startDateSystem)->diffInDays(Carbon::parse($endDateSystem));
                //                $startDateSystem = Carbon::parse($request->start_date)->toDateString();
//                $endDateSystem = Carbon::parse($request->end_date)->toDateString();
////            dd($startDate,$endDate);
//                $daysCount_system = Carbon::parse($startDateSystem)->diffInDays(Carbon::parse($endDateSystem));
//                $daysCount_system === 0 ? $daysCount_system =1 : $daysCount_system = Carbon::parse($startDateSystem)->diffInDays(Carbon::parse($endDateSystem));
                $system_booking = Bookings::where('listing_id', $item->id)
                    // ->whereBetween('booking_date_start', [$startDateSystem, $endDateSystem])
                    ->get();
                // dd($system_booking);
            }
            foreach ($system_booking as $key => $bookings) {

                $startDateDB = Carbon::parse($bookings->booking_date_start)->toDateString();
                $endDateDB = Carbon::parse($bookings->booking_date_end)->toDateString();
                // Create a CarbonPeriod instance
                // dd($startDateSystem);
                $periodRequest = CarbonPeriod::create(isset($request->start_date) ? $request->start_date : $startDateSystem, isset($request->end_date) ? $request->end_date : $endDateSystem);
                $periodRequestDB = CarbonPeriod::create($startDateDB, $endDateDB);

                // Initialize an array to hold the dates
                $dateArrayRequest = [];
                $dateArrayDB = [];

                // Loop through the period and add each date to the array
                foreach ($periodRequest as $dateRequest) {
                    // if (!isset($request->start_date)) {
                    //     if ($key === 0) {
                    //         continue;
                    //     }
                    // }
                    $dateArrayRequest[] = $dateRequest->toDateString();

                }
                foreach ($periodRequestDB as $key => $dateDB) {
                    if ($key === count($periodRequestDB) - 1) {
                        continue;
                    }
                    $dateArrayDB[] = $dateDB->toDateString();
                }
                // dd($dateArrayRequest, $dateArrayDB);
                $commonValues = array_intersect($dateArrayRequest, $dateArrayDB);
                // Count the number of common values
                $countCommonValues = count($commonValues);
                // dd($countCommonValues);
                // if (isset($request->start_date)) {
                //     $countCommonValues = $countCommonValues - 1;
                // }
                // dd($countCommonValues);

                // if ($countCommonValues === 0) {
                //     $countCommonValues = 1;
                // }

                $daysCountBookingSys = Carbon::parse($startDateDB)->diffInDays(Carbon::parse($endDateDB));
                // $daysCountBookingSys === 0 ? $daysCountBookingSys = 1 : $daysCountBookingSys = Carbon::parse($startDateDB)->diffInDays(Carbon::parse($endDateDB));
                // dd($daysCountBookingSys);
                $daysCountBookingRev_system += $daysCountBookingSys;
                // dd($countCommonValues);
                $nights_system += $countCommonValues;


                if ($nights_system === 1) {
                    $stayGreaterThanOne += 1;
                } else if ($nights_system === 2) {
                    $stayGreaterThanTwo += 1;
                } elseif ($nights_system >= 3 && $nights_system <= 6) {
                    // dd($nights_system);
                    $stayGreaterThanThree += 1;
                } else if ($nights_system >= 7 && $nights_system <= 29) {
                    $stayGreaterThanSeven += 1;
                } else if ($nights_system > 30) {
                    $stayGreaterThanThirty += 1;
                }


                $total_amount_system += $bookings->total_price / $daysCountBookingSys * $countCommonValues;

                $ota_discount_system += $bookings->custom_discount;
                if (in_array($item->listing_id, $listing_ids) === true) {
                    if (!in_array($item->listing_id, $booking_listing_idsSys)) {
                        $booking_listing_idsSys[$key] = $item->listing_id;
                    }
                    //                $booking_listing_ids[$key] = $item->listing_id;
                }
                $ota_commitionSys += $bookings->ota_commission;

                //                    $total_amount_system += $bookings->total_price;
            }


            //                $system_booking = Bookings::where('listing_id', $item->id)->get();

        }
        // dd($nights_system, $daysCount_system);
        $ota_commitionSys = isset($nights_system) && $nights_system !== 0 ? ($ota_commitionSys / $daysCountBookingRev_system) * $nights_system : 0;
        $ota_discount_system = isset($nights_system) && $nights_system !== 0 ? ($ota_discount_system / $daysCountBookingRev_system) * $nights_system : 0;
        $livedInDisSys = ($item->commission_value / 100 * ($total_amount_system));

        //        dd($livedInDis);
//        $livedInDis = ($livedInDis / $nights) * $daysCountBookingRev;
//        dd($livedInDis);
//        dd($ota_commition);
//        dd($nights);
        // dd($ota_discount_system, $total_amount_system, $ota_commitionSys, $livedInDisSys);
        $earningsSys = $total_amount_system - $ota_commitionSys - $livedInDisSys - $ota_discount_system;
        // dd($earningsSys);

        $adrSys = isset($nights_system) && $nights_system !== 0 ? $total_amount_system / $nights_system : 0;
        //        dd($earningsSys,$adrSys,$total_amount_system);
        $no_of_nights_per_sys = isset($nights_system) && $nights_system !== 0 ? (count($system_booking)) / $nights_system * 100 : 0;
        //        dd($no_of_nights_per);
        // dd($nights_system, $daysCount_system, count($listing_arr));
        $occupancy_system = isset($nights_system) && $nights_system ? $nights_system / (($daysCount_system > 1 ? $daysCount_system + 1 : $daysCount_system) * count($listing_arr)) * 100 : 0;
        // dd($occupancy_system);
        // dd($occupancy_system);
        if ($occupancy_system > 100) {
            $occupancy_system = 100;
        }
        //        dd($nights_system);
//        $occupancy = $nights / ($daysCount * count($listing_arr)) * 100;
        // $stayGreaterThanOne = 0;
        // $stayGreaterThanTwo = 0;
        // $stayGreaterThanThree = 0;
        // $stayGreaterThanSeven = 0;
        // $stayGreaterThanThirty = 0;

        if ($request->start_date !== null && $request->end_date !== null) {

            $startDate = Carbon::parse($request->start_date)->toDateString();
            $endDate = Carbon::parse($request->end_date)->toDateString();
            //            dd($startDate,$endDate);
            $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
            $daysCount === 0 ? $daysCount = 1 : $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));

            foreach ($listing_arr as $item) {
                $bookings = BookingOtasDetails::where('listing_id', $item->listing_id)
                    ->whereBetween('arrival_date', [$startDate, $endDate])
                    //                ->whereBetween('created_at', [$startDate, $endDate])
                    ->get();
                if ($bookings) {
                    foreach ($bookings as $booking) {
                        array_push($booking_arr, $booking);
                    }
                }
            }
        } else {
            $currentDate1 = Carbon::now();
            $dateBefore7Days = $currentDate1->copy()->subDays(7);
            $startDate = Carbon::parse($dateBefore7Days->toDateString())->startOfDay();
            $endDate = Carbon::parse($currentDate1->toDateString())->endOfDay();
            $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
            $daysCount === 0 ? $daysCount = 1 : $daysCount = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
            //            dd($today);

            foreach ($listing_arr as $item) {

                $bookings = BookingOtasDetails::where('listing_id', $item->listing_id)
                    ->where('arrival_date', $currentDate1->toDateString())
                    // ->whereBetween('arrival_date', [$startDate, $endDate])
                    ->get();
                //                dd($item);

                if ($bookings) {
                    foreach ($bookings as $booking) {
                        array_push($booking_arr, $booking);
                    }
                }
            }

        }

        $total_amount = 0;
        $occupancy = 0;
        $revenue = 0;
        $nights = 0;
        $earnings = 0;
        $daysCountBookingRev = 0;
        $ota_commition = 0;
        $adr = 0;
        $discountLived = 0;
        foreach ($booking_arr as $key => $item) {
            $booking_details = json_decode($item['booking_otas_json_details']);



            $nights_arr = (array) $booking_details->attributes->rooms[0]->days;
            $raw_message = json_decode($booking_details->attributes->raw_message);
            $discount = isset($raw_message->reservation->promotion_details[0]->amount_native) ? (int) $raw_message->reservation->promotion_details[0]->amount_native : 0;
            $discount = abs($discount);
            $discountLived += $discount;
            $raw_message->reservation->listing_base_price_accurate = $raw_message->reservation->listing_base_price_accurate + $discount;
            $nights_sum = 0;
            foreach ($nights_arr as $nights_a) {
                $nights_sum = $nights_sum + $nights_a;
            }

            $startDateDB = Carbon::parse($item->arrival_date)->toDateString();
            $endDateDB = Carbon::parse($item->departure_date)->toDateString();

            // Create a CarbonPeriod instance
            $periodRequest = CarbonPeriod::create($startDate, $endDate);
            $periodRequestDB = CarbonPeriod::create($startDateDB, $endDateDB);

            // Initialize an array to hold the dates
            $dateArrayRequest = [];
            $dateArrayDB = [];

            // Loop through the period and add each date to the array
            foreach ($periodRequest as $dateRequest) {
                $dateArrayRequest[] = $dateRequest->toDateString();
            }
            foreach ($periodRequestDB as $dateDB) {
                // if ($key === count($periodRequestDB) - 1) {
                //     continue;
                // }
                $dateArrayDB[] = $dateDB->toDateString();

            }
            // dd($dateArrayDB);
            $commonValues = array_intersect($dateArrayRequest, $dateArrayDB);
            // Count the number of common values
            $countCommonValues = count($commonValues);
            //            $countCommonValues = $countCommonValues-1;
            if ($countCommonValues === 0) {
                $countCommonValues = 1;
            }

            if ($countCommonValues > count($nights_arr)) {
                $countCommonValues = $countCommonValues - 1;
            }

            $daysCountBooking = Carbon::parse($startDateDB)->diffInDays(Carbon::parse($endDateDB));
            $daysCountBooking === 0 ? $daysCountBooking = 1 : $daysCountBooking = Carbon::parse($startDateDB)->diffInDays(Carbon::parse($endDateDB));
            $daysCountBookingRev += $daysCountBooking;
            $nights += $countCommonValues;

            if ($nights === 1) {
                $stayGreaterThanOne += 1;
            } else if ($nights === 2) {
                $stayGreaterThanTwo += 1;
            } elseif ($nights >= 3 && $nights <= 6) {
                // dd($nights_system);
                $stayGreaterThanThree += 1;
            } else if ($nights >= 7 && $nights <= 29) {
                $stayGreaterThanSeven += 1;
            } else if ($nights > 30) {
                $stayGreaterThanThirty += 1;
            }

            //            dd($daysCountBooking);
            //            dd($booking_details->attributes->amount+$booking_details->attributes->ota_commission);
            $total_amount += $raw_message->reservation->listing_base_price_accurate / $daysCountBooking * $countCommonValues;

            if (in_array($item->listing_id, $listing_ids) === true) {
                $occupancy = $occupancy + 1;
                if (!in_array($item->listing_id, $booking_listing_ids)) {
                    $booking_listing_ids[$key] = $item->listing_id;
                }
                //                $booking_listing_ids[$key] = $item->listing_id;
            }
            //            dd($total_amount,$booking_details->attributes->ota_commission,(20 / 100 * $total_amount));
            $ota_commition += $booking_details->attributes->ota_commission;

        }

        //        dd(($ota_commition));
        $ota_commition = (isset($daysCountBookingRev) && $daysCountBookingRev != 0 ? $ota_commition / $daysCountBookingRev : 0) * $nights;
        // dd($ota_commition);
        //        dd($ota_commition);
        $livedInHostComm = ($listing_arr[0]->commission_value / 100 * ($total_amount));
        $livedInDis = (isset($daysCountBookingRev) && $daysCountBookingRev != 0 ? $discountLived / $daysCountBookingRev : 0) * $nights;
        // dd($livedInDis);
        //        dd($livedInDis);
//        $livedInDis = ($livedInDis / $nights) * $daysCountBookingRev;
//        dd($livedInDis);
//        dd($ota_commition);
//        dd($nights);
        // dd($total_amount, $ota_commition, $livedInDis);
        $earnings = $total_amount - $ota_commition - $livedInDis - ($listing_arr[0]->commission_value / 100 * $total_amount);

        $adr = isset($nights) && $nights != 0 ? $total_amount / $nights : 0;
        $no_of_nights_per = isset($nights) && $nights != 0 ? (count($bookings)) / $nights * 100 : 0;
        //        dd($no_of_nights_per);
//        dd($nights, $daysCount);
        $occupancy = isset($nights) && $nights != 0 ? $nights / ($daysCount * count($listing_arr)) * 100 : 0;
        if ($occupancy > 100) {
            $occupancy = 100;
        }
        //         dd(
//             $stayGreaterThanOne
//             ,
//             $stayGreaterThanTwo
//             ,
//             $stayGreaterThanThree
//             ,
//             $stayGreaterThanSeven
//             ,
//             $stayGreaterThanThirty
//         );
// dd($occupancy + $occupancy_system);
        // dd($livedInHostComm, $livedInDisSys);
        //        $earningsSys
//$adrSys
//$total_amount_system
//$occupancy_system
        // dd(($occupancy), $total_amount, $adr, $earnings);
        $total_bookings = count($booking_arr) + count($system_booking);
        // dd($total_bookings);
        // dd($total_bookings - 1);
        // $stayGreaterThanThree = $stayGreaterThanThree / $total_bookings * 100;
        // dd($stayGreaterThanThree);
        if (isset($request->start_date) && $request->start_date == '2024-01-01') {
            $adr_total = $adr;
        } else {
            $adr_total = $adr + $adrSys;
        }

        // $rounded_number = sprintf("%.2f", $number);
        // dd($rounded_number);
        return response()->json([
            'occupancy' => round($occupancy + $occupancy_system) > 100 ? 100 : round($occupancy + $occupancy_system, 2),
            'revenue' => round($total_amount + $total_amount_system, 2),
            'adr' => round($adr_total),
            'earnings' => round($earnings + $earningsSys),
            'total_amount' => round($total_amount + $total_amount_system, 2),
            'my_earning' => round($earnings + $earningsSys, 2),
            'my_earning_per' => 100 - $listing_arr[0]->commission_value - 15,
            'livedIn_per' => $listing_arr[0]->commission_value,
            'livedIn' => round($livedInHostComm + $livedInDisSys, 2),
            'Airbnb_per' => 15,
            'Airbnb' => round($ota_commition + $ota_commitionSys, 2),
            'discount' => round($ota_discount_system + $livedInDis, 2),
            'stayGreaterThanOne' => round($stayGreaterThanOne / $total_bookings * 100, 2),
            'stayGreaterThanTwo' => round($stayGreaterThanTwo / $total_bookings * 100, 2),
            'stayGreaterThanThree' => round($stayGreaterThanThree / $total_bookings * 100, 2),
            'stayGreaterThanSeven' => round($stayGreaterThanSeven / $total_bookings * 100, 2),
            'stayGreaterThanThirty' => round($stayGreaterThanThirty / $total_bookings * 100, 2),
        ]);

        //        dd($adr);
//        $occupancy =
//        $user_id = request('user_id');
//        $channel_info = Http::withHeaders([
//            'user-api-key' =>  env('CHANNEX_API_KEY'),
//        ])->get(env('CHANNEX_URL')."/api/v1/channels/$channel_id");
//        // Check if the response is successful
//        if ($channel_info->successful()) {
//            // Parse the JSON response
//            $data = $channel_info->json();  // Returns the response body as an array
////            dd($data['data']['attributes']['settings']['tokens']['user_id']);
//            $host_id = $data['data']['attributes']['settings']['tokens']['user_id'];
//        } else {
//            // Handle error
//            $error = $channel_info->body();
//            dd($error);
//        }
//
//        $reservation = Http::withHeaders([
//            'user-api-key' =>  env('CHANNEX_API_KEY'),
//        ])->post(env('CHANNEX_URL')."/api/v1//channels/$channel_id/action/api_proxy", [
//            "request" =>  [
//                "endpoint" => "/reservations",
//                "method" => "get",
//                "params" => [
//                    "host_id" => $host_id
//                ]
//            ]
//        ]);
//        if ($reservation->successful()) {
//            $reservation = $reservation->json();
//            $reservations = $reservation['data']['reservations'];
//        } else {
//            $error = $reservation->body();
//            dd($error);
//        }
//
//
//        $listings = Http::withHeaders([
//            'user-api-key' => $this->apiKey,
//        ])->get(env('CHANNEX_URL')."/api/v1/channels/$channel_id/action/listings");
//
//        if ($listings->successful()) {
//            $listings = $listings->json();
//            $listings = $listings['data']['listing_id_dictionary'];
////            dd($listings);
////                dd($listing['pricing_settings']['default_daily_price']);
//        } else {
//
//
//
//            $error = $listings->body();
//            dd($error);
//        }
//        $revenue = 0;
//        foreach ($reservations as $item) {
//            $revenue = $revenue + $item['expected_payout_amount_accurate'];
//        }
//        $occupancy = count($reservations) / count($listings['values']) * 100;
//        $adr = $revenue / count($listings['values']);
////        dd($adr);
//        return response()->json([
//            'occupancy' => $occupancy,
//            'revenue' => $revenue,
//            'adr' => $adr,
//            'earnings' => (int)$revenue - 15 / 100,
//        ]);
    }
    public function printSoa($request): array
    {
        // dd($request);
        if (gettype($request['listings']) == 'string') {
            $request['listings'] = json_decode($request['listings']);
        }
        // $request = $request->all();
        $host = User::whereId($request['user_id'])->first();
        // $soa = ReportFinanceSoa::where('user_id', $request['user_id'])->where('booking_dates', $request['daterange'])->where('listings', json_encode($request['listings']))->first();
        // if ($soa) {
        //     $soaDetails = SoaDetail::where('soa_id', $soa->id)->get();
        //     $soaDetails = $soaDetails->toArray();
        // } else {
        //     $soaDetails = null;
        // }
        $listing_arr = Listing::whereIn('id', $request['listings'])->get();
        $bookingsCod = [];
        $bookings = [];
        $dateRange = $request['daterange'];
        [$startDate, $endDate] = explode(' - ', $dateRange);
        // dd($startDate);
        // $startDate = Carbon::createFromFormat('m-d-Y', trim($startDate))->format('Y-m-d');
        // $endDate = Carbon::createFromFormat('m-d-Y', trim($endDate))->format('Y-m-d');
        $period = CarbonPeriod::create($startDate, $endDate)->excludeEndDate();
        $periodDatesArray = $period->toArray();
        $periodDateStrings = array_map(fn($date) => $date->toDateString(), $periodDatesArray);
        $dates = [];
        $bookingOtaDatas = [];
        $bookingOtaliveds = [];
        foreach ($listing_arr as $key => $item) {
            foreach ($period as $date) {
                $listingIdArr = [$item->listing_id];
                $bookingLivedIn = Bookings::where('listing_id', $item->id)
                    ->whereDate('booking_date_end', '>', $date->format('Y-m-d'))
                    ->whereDate('booking_date_start', '<=', $date->format('Y-m-d'))
                    ->get();
                $listingRelation = ListingRelation::where('listing_id_airbnb', $item->id)->get();
                if ($listingRelation) {
                    foreach ($listingRelation as $it) {
                        $listing_Bcom = Listing::where('id', $it->listing_id_other_ota)->first();
                        $listingIdArr[] = $listing_Bcom->listing_id;
                    }
                }
                $bookingOta = BookingOtasDetails::whereIn('listing_id', $listingIdArr)
                    ->whereDate('departure_date', '>', $date->format('Y-m-d'))
                    ->whereDate('arrival_date', '<=', $date->format('Y-m-d'))
                    ->get();
                foreach ($bookingOta as $booking) {
                    if (in_array($booking->id, $bookingOtaDatas)) {
                        continue;
                    }
                    $start_date = $booking->arrival_date;
                    $end_date = $booking->departure_date;
                    $bookingOtaDatas[] = $booking->id;
                    $amount = $booking->amount;
                    $discount = $booking->discount;
                    $promotion = $booking->promotion;
                    $discount = $discount + $promotion;
                    $total_cleaning = $booking->cleaning_fee;
                    $apartment = Listing::where('listing_id', $item->listing_id)->first();
                    if ($apartment->is_cleaning_fee == 0 || $apartment->is_cleaning_fee == null) {
                        $cleaning_fee = $booking->cleaning_fee;
                        $amount = $amount + $cleaning_fee;
                    }
                    $start_date = $booking->arrival_date;
                    $end_date = $booking->departure_date;
                    $ota_commission = $booking->ota_commission;
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $datesArray = $bookingPeriod->toArray();
                    $dateStrings = array_map(fn($date) => $date->toDateString(), $datesArray);
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $bookingPeriodDatesArray = $bookingPeriod->toArray();
                    $bookingPeriodDateStrings = array_map(fn($date) => $date->toDateString(), $bookingPeriodDatesArray);
                    $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);
                    $nights = count($matchingValues);
                    if ($apartment->pre_discount == 1) {
                        $livedin_commission = ($item->commission_value / 100 * ($amount - $discount)) * 1.15;
                        // if ($booking->id == 3054) {
                        //     dd($item->commission_value, $livedin_commission, $amount, $discount);
                        // }
                    } else {
                        $livedin_commission = ($item->commission_value / 100 * $amount) * 1.15;
                    }

                    $host_commission = $amount - $discount - $ota_commission - $livedin_commission;
                    $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                    $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                    if ($apartment->is_co_host == 1) {
                        array_push($bookingsCod, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->status == 'cancelled' ? $booking->status : $booking_status,
                            'payment_status' => 'OTA',
                            'type' => 'AirBnb',
                            'booking_id' => 'O' . $booking->id,
                            'name' => $booking->guest_name ?? '',
                            'night_rate' => round($amount / $totalnights) > 0 ? round($amount / $totalnights) : 0,
                            'total' => round($amount / $totalnights * $nights) > 0 ? round($amount / $totalnights * $nights) : 0,
                            'discount' => round($discount / $totalnights * $nights),
                            'post_discount_booking_amount' => round($amount / $totalnights * $nights) - round($discount / $totalnights * $nights),
                            'ota_commission' => round($ota_commission / $totalnights * $nights),
                            'livedin_commission' => round($livedin_commission / $totalnights * $nights, 1) > 0 ? round($livedin_commission / $totalnights * $nights, 1) : 0,
                            'host_commission' => round($host_commission / $totalnights * $nights) > 0 ? round($host_commission / $totalnights * $nights) : 0,
                            'total_cleaning' => round($total_cleaning / $totalnights * $nights),
                        ]);
                    } else {
                        array_push($bookings, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->status == 'cancelled' ? $booking->status : $booking_status,
                            'payment_status' => 'OTA',
                            'type' => 'AirBnb',
                            'booking_id' => 'O' . $booking->id,
                            'name' => $booking->guest_name ?? '',
                            'night_rate' => round($amount / $totalnights) > 0 ? round($amount / $totalnights) : 0,
                            'total' => round($amount / $totalnights * $nights) > 0 ? round($amount / $totalnights * $nights) : 0,
                            'discount' => round($discount / $totalnights * $nights),
                            'post_discount_booking_amount' => round($amount / $totalnights * $nights) - round($discount / $totalnights * $nights),
                            'ota_commission' => round($ota_commission / $totalnights * $nights),
                            'livedin_commission' => round($livedin_commission / $totalnights * $nights, 1) > 0 ? round($livedin_commission / $totalnights * $nights, 1) : 0,
                            'host_commission' => round($host_commission / $totalnights * $nights) > 0 ? round($host_commission / $totalnights * $nights) : 0,
                            'total_cleaning' => round($total_cleaning / $totalnights * $nights),
                        ]);
                    }
                }
                foreach ($bookingLivedIn as $booking) {
                    if (in_array($booking->id, $bookingOtaliveds)) {
                        continue;
                    }
                    $bookingOtaliveds[] = $booking->id;
                    $total = $booking->total_price;
                    $discount = $booking->custom_discount;
                    $start_date = $booking->booking_date_start;
                    $end_date = $booking->booking_date_end;
                    $ota_commission = $booking->ota_commission;
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $bookingPeriodDatesArray = $bookingPeriod->toArray();
                    $bookingPeriodDateStrings = array_map(fn($date) => $date->toDateString(), $bookingPeriodDatesArray);
                    $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);
                    $nights = count($matchingValues);
                    if ($nights === 0) {
                        $nights = 1;
                    }
                    $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                    $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                    $apartment = Listing::where('id', $item->id)->first();
                    $discount = round($discount / $totalnights * $nights);
                    if ($apartment->pre_discount == 1) {
                        $livedin_commission = ($nights * ($booking->per_night_price - $discount / $nights) * $item->commission_value / 100) * 1.15;
                    } else {
                        $livedin_commission = ($item->commission_value / 100 * $booking->per_night_price * $nights) * 1.15;
                    }
                    $post_discount_booking_amount = ($booking->per_night_price * $nights) - $discount; // Calculate for LivedIn bookings
                    // if ($booking->id == 2724) {
                    //     dd($post_discount_booking_amount, $ota_commission / $totalnights * $nights, $livedin_commission);
                    // }
                    $host_commission = $booking->per_night_price * $nights - $discount - ($ota_commission / $totalnights * $nights) - $livedin_commission;
                    $total_cleaning = $booking->cleaning_fee; // Use cleaning_fee for LivedIn bookings
                    if ($booking->payment_method == 'cod') {
                        array_push($bookingsCod, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->booking_status == 'cancelled' ? $booking->booking_status : $booking_status,
                            'payment_status' => $booking->payment_method,
                            'type' => $booking->booking_sources,
                            'booking_id' => 'L' . $booking->id,
                            'name' => $booking->name . ' ' . $booking->surname,
                            'night_rate' => $booking->booking_status == 'cancelled' ? 0 : round($booking->per_night_price),
                            'total' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->cleaning_fee_direct_booking == 1
                                    ? round($booking->per_night_price * $nights)
                                    : round(($booking->per_night_price + $booking->cleaning_fee) * $nights)),
                            'discount' => $discount,
                            'post_discount_booking_amount' => round($post_discount_booking_amount),
                            'ota_commission' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->ota_fee_direct_booking == 1
                                    ? round($ota_commission / $totalnights * $nights)
                                    : 0),
                            'livedin_commission' => $booking->booking_status == 'cancelled' ? 0 : round($livedin_commission, 1),
                            'host_commission' => $booking->booking_status == 'cancelled' ? 0 : round($host_commission, 2),
                            'total_cleaning' => $booking->booking_status == 'cancelled' ? 0 : round($total_cleaning / $totalnights * $nights),
                        ]);
                    } else {
                        array_push($bookings, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->booking_status == 'cancelled' ? $booking->booking_status : $booking_status,
                            'payment_status' => $booking->payment_method,
                            'type' => $booking->booking_sources,
                            'booking_id' => 'L' . $booking->id,
                            'name' => $booking->name . ' ' . $booking->surname,
                            'night_rate' => $booking->booking_status == 'cancelled' ? 0 : round($booking->per_night_price),
                            'total' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->cleaning_fee_direct_booking == 1
                                    ? round($booking->per_night_price * $nights)
                                    : round(($booking->per_night_price + $booking->cleaning_fee) * $nights)),
                            'discount' => $discount,
                            'post_discount_booking_amount' => round($post_discount_booking_amount),
                            'ota_commission' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->ota_fee_direct_booking == 1
                                    ? round($ota_commission / $totalnights * $nights)
                                    : 0),
                            'livedin_commission' => $booking->booking_status == 'cancelled' ? 0 : round($livedin_commission, 1),
                            'host_commission' => $booking->booking_status == 'cancelled' ? 0 : round($host_commission, 2),
                            'total_cleaning' => $booking->booking_status == 'cancelled' ? 0 : round($total_cleaning / $totalnights * $nights),
                        ]);
                    }
                }
            }
        }
        return ['host' => $host, 'cleaning_per_cycle' => $host->cleaning_per_cycle, 'bookings' => $bookings, 'bookingsCod' => $bookingsCod];
        return view('Admin.reports.finance.printsoa', ['host' => $host, 'cleaning_per_cycle' => $host->cleaning_per_cycle, 'bookings' => $bookings, 'bookingsCod' => $bookingsCod, 'soa' => $soa, 'soaDetails' => $soaDetails]);
    }
    public function getPerformance(Request $request)
    {
        // dd($request);

        $listing_arr = [];
        $bookings = [];
        $user_id = Auth::id();
        $userDB = User::find($user_id);
        $listings = Listing::all();
        foreach ($listings as $listing) {
            $users = json_decode($listing->user_id, true);
            if (is_array($users) && in_array($user_id, $users)) {
                $listing_arr[] = $listing;
            }
        }
        $listing_idsReq = array_map(function ($listing) {
            return (string) $listing->id; // Ensure IDs are strings for JSON format
        }, $listing_arr);
        // dd($listing_idsReq);
        $requestDataSoa['user_id'] = $user_id;
        if (!empty($request->start_date) && !empty($request->end_date)) {
            // If request has both dates
            $requestDataSoa['daterange'] = "$request->start_date - $request->end_date";
            $startDate = Carbon::parse($request->start_date);   
            $endDate = Carbon::parse($request->end_date);
        } else {
            // Default: last 6 months
            $startDate = Carbon::now()->subMonths(6)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            $requestDataSoa['daterange'] = $startDate->toDateString() . ' - ' . $endDate->toDateString();
        }
        $requestDataSoa['listings'] = json_encode($listing_idsReq);
        // dd($requestDataSoa);
        $d = $this->printSoa($requestDataSoa);
        foreach ($listing_arr as $key => $item) {
            $bookingOta = BookingOtasDetails::where('listing_id', $item->listing_id)->where('status', '!=', 'cancelled')->get();
            $bookingLivedIn = Bookings::where('listing_id', $item->id)->where('booking_status', '!=', 'cancelled')->get();
            foreach ($bookingOta as $booking) {
                if ($booking->status == 'cancelled') {
                    continue;
                }
                $promotion = $booking->promotion;
                $discount = $booking->discount + $promotion;

                $total = $booking->amount;
                // dd($booking->cleaning_fee);
                $total = $total - $booking->short_term_cleaning;
                $start_date = $booking->arrival_date;
                $end_date = $booking->departure_date;
                $ota_commission = (float) $booking->ota_commission;
                $nights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                $host_commission = $item->commission_value / 100 * $total;
                array_push($bookings, [
                    'booking_id' => $booking->id,
                    'listing_id' => $item->listing_id,
                    'total' => $total,
                    'discount' => $discount,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'ota_commission' => $ota_commission,
                    'nights' => $nights,
                    'host_commission' => $host_commission,
                ]);
            }
            foreach ($bookingLivedIn as $booking) {
                if ($booking->booking_status == 'cancelled') {
                    continue;
                }

                $total = $booking->total_price;
                $discount = $booking->custom_discount;
                $start_date = $booking->booking_date_start;
                $end_date = $booking->booking_date_end;
                $ota_commission = $booking->ota_commission;
                $nights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                if ($nights === 0) {
                    $nights = 1;
                }
                $host_commission = $item->commission_value / 100 * $total;
                array_push($bookings, [
                    'booking_id' => $booking->id,
                    'listing_id' => $item->listing_id,
                    'total' => $total,
                    'discount' => $discount,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'ota_commission' => $ota_commission,
                    'nights' => $nights,
                    'host_commission' => $host_commission,
                ]);
            }
        }
        // dd($bookings);
        $total_nights = 0;
        $total_host_commission = 0;
        $total_discount = 0;
        $total_ota_commission = 0;
        $listings_occupied = array();
        $total_total_nights_requested = 0;
        $total_amount = 0;
        $total_occupancy = 0;
        $total_amount_test = 0;
        $stayGreaterThanOne = 0;
        $stayGreaterThanTwo = 0;
        $stayGreaterThanThree = 0;
        $stayGreaterThanSeven = 0;
        $stayGreaterThanThirty = 0;

        $lowestStartDate = null;
        $highestEndDate = null;
        foreach ($bookings as $booking) {
            if ($lowestStartDate === null || $booking['start_date'] < $lowestStartDate) {
                $lowestStartDate = $booking['start_date'];
            }
            if ($highestEndDate === null || $booking['end_date'] > $highestEndDate) {
                $highestEndDate = $booking['end_date'];
            }
        }
        // dd($lowestStartDate, $highestEndDate);

        if (isset($request->start_date) && isset($request->end_date)) {
            $startDateRequested = $request->start_date;
            $endDateRequested = $request->end_date;


            if (!empty($userDB->role_id) && $userDB->role_id === 2) {

                try {

                    $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation();

                    $this->mixpanelService->trackEvent('Analytics Dates Opened', [
                        'distinct_id' => $userDB->id,
                        'first_name' => $userDB->name,
                        'last_name' => $userDB->surname,
                        'email' => $userDB->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $userDB->country,
                        'db_city' => $userDB->city,
                        'host_type' => $userDB->hostType->module_name
                    ]);

                    $this->mixpanelService->setPeopleProperties($userDB->id, [
                        '$first_name' => $userDB->name,
                        '$last_name' => $userDB->surname,
                        '$email' => $userDB->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $userDB->country,
                        'db_city' => $userDB->city,
                        'host_type' => $userDB->hostType->module_name

                    ]);

                } catch (\Exception $e) {


                }
            }


        } else {
            $startDateRequested = $lowestStartDate;
            $endDateRequested = $highestEndDate;
        }
        // dd($startDateRequested, $endDateRequested);

        foreach ($bookings as $booking) {
            $total_amount_test += $booking['total'];
            $startDateDB = Carbon::parse($booking['start_date'])->toDateString();
            $endDateDB = Carbon::parse($booking['end_date'])->toDateString();
            $periodRequest = CarbonPeriod::create($startDateRequested, $endDateRequested);
            $periodRequestDB = CarbonPeriod::create($startDateDB, $endDateDB);
            $total_total_nights_requested = count($periodRequest);
            $dateArrayRequest = [];
            $dateArrayDB = [];

            foreach ($periodRequest as $dateRequest) {
                $dateArrayRequest[] = $dateRequest->toDateString();
            }
            foreach ($periodRequestDB as $key => $dateDB) {
                if ($key === count($periodRequestDB) - 1) {
                    continue;
                }
                $dateArrayDB[] = $dateDB->toDateString();

            }
            //  if($booking['nights'] == 0 && $countCommonValues == 0) {
            //     $booking['nights'] = 1;
            //     $countCommonValues = 1;
            // }
            // if($booking['nights'] === 0) {
            //     dd($booking);
            // }
            $commonValues = array_intersect($dateArrayRequest, $dateArrayDB);
            $countCommonValues = count($commonValues);
            $total_amount += $booking['total'] / $booking['nights'] * $countCommonValues;
            $total_host_commission += $booking['host_commission'] / $booking['nights'] * $countCommonValues;
            $total_discount += $booking['discount'] / $booking['nights'] * $countCommonValues;
            $total_ota_commission += $booking['ota_commission'] / $booking['nights'] * $countCommonValues;
            if ($countCommonValues > 0) {
                $total_nights += $countCommonValues;
                if ($countCommonValues === 1) {
                    $stayGreaterThanOne += 1;
                } else if ($countCommonValues === 2) {
                    $stayGreaterThanTwo += 1;
                } elseif ($countCommonValues >= 3 && $countCommonValues <= 6) {
                    // dd($nights_system);
                    $stayGreaterThanThree += 1;
                } else if ($countCommonValues >= 7 && $countCommonValues <= 29) {
                    $stayGreaterThanSeven += 1;
                } else if ($countCommonValues > 30) {
                    $stayGreaterThanThirty += 1;
                }
                if (!in_array($booking['listing_id'], $listings_occupied)) {
                    array_push($listings_occupied, $booking['listing_id']);
                }
            }

        }
        // dd($total_amount);

        $occupancy = isset($total_nights) && $total_nights != 0 ? $total_nights / (count($dateArrayRequest) * count($listing_arr)) * 100 : 0;
        // dd($total_amount , $total_discount , ($total_host_commission * 1.15) , $total_ota_commission);
        $earnings = $total_amount - $total_discount - ($total_host_commission * 1.15) - $total_ota_commission;
        $adr = isset($total_nights) && $total_nights != 0 ? $total_amount / $total_nights : 0;


        if (!empty($userDB->role_id) && $userDB->role_id === 2) {

            try {

                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Analytics Module Opened', [
                    'distinct_id' => $userDB->id,
                    'first_name' => $userDB->name,
                    'last_name' => $userDB->surname,
                    'email' => $userDB->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $userDB->country,
                    'db_city' => $userDB->city
                ]);

                $this->mixpanelService->setPeopleProperties($userDB->id, [
                    '$first_name' => $userDB->name,
                    '$last_name' => $userDB->surname,
                    '$email' => $userDB->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $userDB->country,
                    'db_city' => $userDB->city

                ]);

            } catch (\Exception $e) {


            }
        }
        // dd($d['bookings']);
        $tot = 0;
        $tot_nig = 0;
        $dis = 0;
        $host_com = 0;
        foreach ($d['bookings'] as $book) {
            $tot += $book['total'];
            $tot_nig += $book['nights'];
            $dis += $book['discount'];
            $host_com += $book['host_commission'];
        }

        return response()->json([
            'occupancy' => round($occupancy),
            'revenue' => round($tot, 2),
            'adr' => $tot_nig > 0 ? round($tot / $tot_nig, 2) : 0,
            'my_earning' => round($host_com, 2),
            'discount' => round($dis, 2),
            'my_earning_per' => 100 - $listing_arr[0]->commission_value - 15,
            'livedIn_per' => $listing_arr[0]->commission_value,
            'Airbnb_per' => 15,
            'livedIn' => round($total_host_commission, 2),
            'Airbnb' => round($total_ota_commission, 2),
            'stayGreaterThanOne' => count($bookings) > 0 ? round($stayGreaterThanOne / count($bookings) * 100, 2) : 0,
            'stayGreaterThanTwo' => count($bookings) > 0 ? round($stayGreaterThanTwo / count($bookings) * 100, 2) : 0,
            'stayGreaterThanThree' => count($bookings) > 0 ? round($stayGreaterThanThree / count($bookings) * 100, 2) : 0,
            'stayGreaterThanSeven' => count($bookings) > 0 ? round($stayGreaterThanSeven / count($bookings) * 100, 2) : 0,
            'stayGreaterThanThirty' => count($bookings) > 0 ? round($stayGreaterThanThirty / count($bookings) * 100, 2) : 0,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getAllListings()
    {
        $listings = Listing::all();
        return $listings->map(function ($listing) {
            $listingJson = json_decode($listing->listing_json, true);

            return [
                "id" => $listing->id,
                "listing_id" => $listingJson['id'],
                "name" => $listingJson['title'] ?? 'Unnamed Listing',
            ];
        });
    }


    public function customMapListing(Request $request, User $user): JsonResponse
    {
        echo 'closed';
        die;
        // $request->listing_id = 1299718088831255518;

        print_r($request->all());
        die;

        $group = Group::where('user_id', $user->id)->first();
        $listing = Listing::where('listing_id', $request->listing_id)->first();

        // Log::info('Map Listing ID: ', ['listing_id' => $request->listing_id]);

        // print_r($listing->is_sync);
        // die;

        if ($listing->is_sync == 'sync_all') {
            $this->removeMapping($request->channel_id, $listing->listing_id);
            $listing->update(['is_sync' => '']);
            return response()->json([
                'code' => 100,
                'message' => 'listing unmapped successfully'
            ]);
        } else {
            $listing = $this->fetchListingDetails($request->listing_id, $request->channel_id);
            $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();

            // print_r($rate_plan);
            // die;

            if ($rate_plan) {
                $property = Properties::where('id', $rate_plan->property_id)->first();

                // print_r($property);
                // die;

                // echo $request->channel_id; die;

                $this->mapListingByRatePlan($request->channel_id, $request->listing_id, $rate_plan->ch_rate_plan_id);

                // DB::select("UPDATE listings
                //         SET is_sync = 'sync_all'
                //         WHERE listing_id = ".$request->listing_id);

                // print_r($property);
                // die;

                $this->activateChannel($request->channel_id);
                $this->pullFutureReservation($request->channel_id, $request->listing_id, $property->ch_property_id);
                return response()->json([
                    'code' => 100,
                    'message' => 'listing mapped successfully'
                ]);
            } else {
                $property = $this->createProperty($group, $user, $listing);
                $this->updateChannel($request->channel_id, $property);
                $room_type = $this->createRoomType($request->listing_id, $property, $user, $listing);
                $rate_plan = $this->createRatePlan($request->listing_id, $property, $user, $listing, $room_type);
                $this->mapListingByRatePlan($request->channel_id, $request->listing_id, $rate_plan->ch_rate_plan_id);
                $this->activateChannel($request->channel_id);
                $this->pullFutureReservation($request->channel_id, $request->listing_id, $property->ch_property_id);
                return response()->json([
                    'code' => 100,
                    'message' => 'listing mapped successfully'
                ]);
            }
        }
    }


}

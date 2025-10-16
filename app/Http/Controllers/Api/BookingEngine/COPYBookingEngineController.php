<?php

namespace App\Http\Controllers\Api\BookingEngine;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\BookingManagement\BookingManagementController;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\BookingResource;
use Illuminate\Support\Facades\Storage;

use App\Services\StoreProcedureService;


use App\Models\{
    ChurnedProperty,
    Listing,
    Review,
    Guests,
    Channels,
    Calender,
    Bookings,
    RoomType,
    RatePlan,
    BeOrder,
    BookingLead,
    User,
    BookingCancellation
    // Bookings,
    // BookingOtasDetails,
    // Properties,
    // RoomType,
    // Calender
};

use Illuminate\Support\Facades\{
    DB
};

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

class BookingEngineController extends Controller
{
    // dynamic property_type, guest
    // discounts calculate, total_price get after discount, discount calculate
    
    private $channelId;
    private $apiKey;
    public function __construct()
    {
        $this->channelId = config('services.channex.channel_id');
        $this->apiKey = config('services.channex.api_key');
    }

   /**
     * Summary of authenticate
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse|mixed
     */

    public function authenticate(Request $request): JsonResponse
    {
        // dd($request);
        $data = $request->only(['email', 'password']);

        $validator = Validator::make($data, [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        if (Auth::guard('api')->attempt($data)) {
            $guest = Auth::guard('api')->user();

            $accessToken = $guest->createToken($guest->email)->plainTextToken;
            $expiration = now()->addYear();
            
            $guest->dp = url('/storage').'/'.$guest->dp;
            
            return response()->json([
                'code' => 200,
                'message' => 'Login successful (Guest)',
                'user' => $guest,
                'access_token' => $accessToken,
                'expire_at' => $expiration
            ]);
        }
        return response()->json([
            'code' => 401,
            'message' => 'Invalid credentials',
        ], 401);
    }
    
    public function google_authenticate(Request $request): JsonResponse
    {
        // $data = $request->only(['sub', 'name', 'email', 'phone', 'phone_code', 'country_code', 'email_verified', 'picture']);

        $validator = Validator::make($request->all(), [
            'sub' => ['required'],
            'name' => ['required'],
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        $data = [
            'google_user_id'=>$request->sub,
            'name'=>$request->name,
            'email'=>$request->email,
            'social_type'=>'google',
        ];
        
        if(!empty($request->phone)){
            $data['phone'] = $request->phone;
        }
        
        if(!empty($request->phone_code)){
            $data['phone_code'] = $request->phone_code;
        }
        
        if(!empty($request->country_code)){
            $data['country_code'] = $request->country_code;
        }
        
        if(!empty($request->picture)){
            $data['dp'] = $request->picture;
        }
        
        $guest = Guests::where('email', $request->email)->first();
        if(is_null($guest)){
            $guest = Guests::create($data);
        } else{
            Guests::where('email', $request->email)->update($data);
        }
        
        Auth::guard('api')->login($guest);
        
        $accessToken = $guest->createToken($guest->email)->plainTextToken;
        $expiration = now()->addYear();
        
        return response()->json([
            'code' => 200,
            'message' => 'Login successful (Guest)',
            'user' => $guest,
            'access_token' => $accessToken,
            'expire_at' => $expiration
        ]);
    }

    public function register(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) {
                    if (Guests::where('email', $value)->exists()) {
                        $fail('The email has already been taken.');
                    }
                },
            ],
            'phone' => 'required|numeric',
            'password' => 'required|min:6',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }
        

        $data = $validator->validated();
        $data['password'] = bcrypt($data['password']);
        try {
            $user = Guests::create($data);
            $user['accessToken'] = $user->createToken($user->email)->plainTextToken;
            return response()->json([
                'code' => 200,
                'message' => 'User Created Successfully',
                'guest' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong. Please try again later.'. $e->getMessage()], 500);
        }
    }
    
    
    public function logout(Request $request){
        
        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->id)){
            $guest->currentAccessToken()->delete();
        }
        
        return response()->json('success');
    }

    public function get_5_star_apartments(){
        try{
            
            $data = [];

            $listings = Listing::with('setting', 'airbnbImages')
            ->whereNotNull('be_listing_name')
            ->where(['is_sync' => 'sync_all', 'is_churned' => 0])
            ->orderBy('id', 'desc')
            ->get();

            $guest_listing_ids = [];
            $guest = Auth::guard('guest')->user();
            if($guest && !empty($guest->id)){
                $guest_listing_ids = DB::table('guest_favorites')->where(['user_id' => $guest->id])->pluck('listing_id')->toArray();
            }

            foreach($listings as $listing){
                
                $is_churned = ChurnedProperty::where('listing_id', $listing->listing_id)->exists();
                if($is_churned){
                    continue;
                }
                
                $images = $listing->airbnbImages()->pluck('url')->toArray();
                
                if(empty($images)){
                    continue;
                }
                
                if(is_null($listing->channel) || !is_null($listing->channel->connection_type)){
                    continue;
                }
                
                if(count($data) == 10){
                    break;
                }
                
                $reviews = Review::where('review_json->meta->listing_id', $listing->listing_id)
                ->where('overall_score','>=','8')
                ->orderBy('id', 'Desc')
                ->get();
                
                if(empty($reviews)){
                    continue;
                }
                
                $overall = 0;
                $guest_review = ['guest_name'=>'','review'=>''];
                foreach($reviews as $review){
                    
                    $review_json = !empty($review->review_json) ? json_decode($review->review_json) : '';
                    
                    if(!empty($review_json->guest_name)){
                        $guest_review['guest_name'] = $review_json->guest_name;
                    }
                    
                    if(!empty($review_json->raw_content->public_review)){
                        $guest_review['review'] = $review_json->raw_content->public_review;
                    }
                    
                    $overall += $review->overall_score / 2;
                }

                if(empty($overall)){
                    continue;
                }
                
                $jsn = !empty($listing->listing_json) ? json_decode($listing->listing_json) : null;
                $title = !empty($jsn->title) ? explode('-', $jsn->title) : '';
                
                
                $listing_title = isset($title[0]) ? trim($title[0]) : '';
                
                // $short_name = '';
                
                // if(!empty($title)){
                //     array_shift($title);
                //     $short_name = trim(implode(' ', $title));
                // }
                
                // if(empty($short_name)){
                //     $title = !empty($jsn->title) ? explode('.', $jsn->title) : '';
                //     $short_name = !empty($title[1]) ? $title[1] : '';
                // }
                
                $start_date = $end_date = date('Y-m-d');
                
                $price = $listing->calendars()->whereBetween('calenders.calender_date', [$start_date, $end_date])->sum('rate');
                
                // $price = $listing->setting->default_daily_price ?? 0;
                
                $currency = $this->get_logged_in_user_currency();
                
                $price = $this->calculate_price_with_exchange_rate($price, $currency);

                $data[] = [
                    'listing_id' =>$listing->listing_id,
                    'title' =>$listing->be_listing_name, //$listing_title,
                    'property_type' =>$listing->property_type,
                    // 'short_name' =>'',//$short_name,
                    'guest_review'=>$guest_review,
                    'price' =>$price,
                    'currency' =>$currency,
                    'ratings' => !empty($overall) && !empty($reviews) ? $overall / count($reviews) : 0,
                    'is_favorite' => !empty($guest_listing_ids) && in_array($listing->listing_id, $guest_listing_ids) ? 1 : 0,
                    'images' => $images
                ];

            }

            if(!empty($data)){
                return response()->json(['success'=>1, 'response'=>$data], 200);
            }

            return response()->json(['success'=>0, 'response'=>'data not found'], 400);
        } catch(Exception $ex){
            return response()->json(['success'=>0, 'response'=>$ex->getMessage()], 400);
        }
    }
    
    public function get_all_listings(Request $request){
        try{
            
            $data = [];
            
            // if(!$request->filled('district')){
            //     return response()->json(['success'=>0, 'response'=>'District is required'], 400);
            // }
            
            $listings = Listing::with('setting', 'airbnbImages')
                ->whereNotNull('be_listing_name')
                ->where(['is_sync' => 'sync_all', 'is_churned' => 0]);
            
            $start_date = $end_date = '';
            if($request->filled('start_date') && $request->filled('end_date')){
                
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');

                // $listings->join('calenders', function($join) use ($start_date, $end_date) {
                //     $join->on('calenders.listing_id', '=', 'listings.listing_id')
                //     ->whereBetween('calenders.calender_date', [$start_date, $end_date])
                //     ->where(['calenders.availability'=>1, 'calenders.is_lock'=>0, 'calenders.is_blocked'=>0]);
                // });
            }
            
            if($request->filled('district')){
                if(strtolower($request->district) == "riyadh"){
                    $listings->where('city_name', "riyadh");
                } else{
                    $listings->where('district', $request->district);
                }
            }
            
            if($request->filled('property_type')){
                $property_types = json_decode($request->property_type);
                if(!empty($property_types)){
                    $listings->whereIn('property_type', $property_types);
                }
            }
            
            if($request->filled('bedrooms')){
                $listings->where('bedrooms', $request->bedrooms);
            }
            
            if($request->filled('beds')){
                $listings->where('beds', $request->beds);
            }
            
            if($request->filled('bathrooms')){
                $listings->where('bathrooms', $request->bathrooms);
            }
            
            if(!empty($request->is_allow_pets)){
                $listings->where('is_allow_pets', $request->is_allow_pets);
            }
            
            if(!empty($request->is_self_check_in)){
                $listings->where('is_self_check_in', $request->is_self_check_in);
            }
            
            $records = $listings->get();
            
            $guest_listing_ids = [];
            $guest = Auth::guard('guest')->user();
            if($guest && !empty($guest->id)){
                $guest_listing_ids = DB::table('guest_favorites')->where(['user_id' => $guest->id])->pluck('listing_id')->toArray();
            }
            
            foreach($records as $listing){

                $is_churned = ChurnedProperty::where('listing_id', $listing->listing_id)->exists();
                if($is_churned){
                    // continue;
                }

                $reviews = Review::where('review_json->meta->listing_id', $listing->listing_id)
                ->where('overall_score','>=','8')
                ->orderBy('id', 'Desc')
                ->get();
                
                if(empty($reviews)){
                    // continue;
                }
                
                $images = $listing->airbnbImages()->pluck('url')->toArray();
                
                $insert_images = [];
                
                if(empty($images)){
                    // continue;
                    
                    $channel = Channels::where('id', $listing->channel_id)->first();
                    $response = Http::withHeaders([
                        'user-api-key' => $this->apiKey,
                    ])->get(env('CHANNEX_URL') . "/api/v1/channels/{$channel->ch_channel_id}/action/listing_details?listing_id=".$listing->listing_id);
                    
                    if ($response->successful()) {
                        $response = $response->json();
                        
                        if(!empty($response['data']['listing']["images"])){
                            foreach($response['data']['listing']["images"] as $imggs){
                                $images[] = $imggs['extra_medium_url'];
                                
                                $insert_images[] = [
                                    'listing_id' => $listing->listing_id,
                                    'airbnb_image_id' => !empty($imggs['id']) ? $imggs['id'] : '',
                                    'url' => $imggs['extra_medium_url'],
                                ];
                            }
                        }
                        
                        if(!empty($insert_images)){
                            $listing->airbnbImages()->createMany($insert_images);
                        }
                    }
                    
                    // print_r($images);die;
                }
                
                if(is_null($listing->channel) || !is_null($listing->channel->connection_type)){
                    // continue;
                }

                // if(!empty($request->adults) || !empty($request->child) || !empty($request->infants) || !empty($request->pets)){
                    
                //     $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
                //     if(is_null($rate_plan)){
                //         continue;
                //     }

                //     $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
                //     if(is_null($room_type)){
                //         continue;
                //     }
                    
                //     if(!empty($request->adults) && $request->adults != $room_type->occ_adults){
                //         continue;
                //     }
                    
                //     if(!empty($request->child) && $request->child != $room_type->occ_children){
                //         continue;
                //     }
                    
                //     if(!empty($request->infants) && $request->infants != $room_type->occ_infants){
                //         continue;
                //     }
                    
                //     if(!empty($request->pets) && $request->pets != $listing->is_allow_pets){
                //         continue;
                //     }
                // }
                
                $overall = 0;
                $guest_review = ['guest_name'=>'','review'=>''];
                foreach($reviews as $review){
                    
                    $review_json = !empty($review->review_json) ? json_decode($review->review_json) : '';
                    
                    if(!empty($review_json->guest_name)){
                        $guest_review['guest_name'] = $review_json->guest_name;
                    }
                    
                    if(!empty($review_json->raw_content->public_review)){
                        $guest_review['review'] = $review_json->raw_content->public_review;
                    }

                    $overall += $review->overall_score / 2;
                }
                
                if(empty($overall)){
                    // continue;
                }
                
                $jsn = !empty($listing->listing_json) ? json_decode($listing->listing_json) : null;
                $listing_name = !empty($jsn->title) ? $jsn->title : '';
                
                
                if(empty($start_date) && empty($end_date)){
                    $start_date = $end_date = date('Y-m-d');
                    
                    $updatedBookingDateEnd = $end_date;
                } else {
                    $updatedBookingDateEnd = Carbon::parse($end_date)->subDay();
                }
                
                $total = $listing->calendars()->whereBetween('calenders.calender_date', [$start_date, $updatedBookingDateEnd])->sum('rate');
                
                // $total = empty($total) && !is_null($listing->setting) ? $listing->setting->default_daily_price : $total;
                
                
                if($request->filled('min_price') && $request->filled('max_price')){
                    if($total >= $request->min_price && $total <= $request->max_price){
                        //
                    } else{
                        // continue;
                    }
                }
                
                if($request->filled('amenities')){
                    
                    if(empty($listing->amenities->amenities_json)){
                        continue;
                    }
                    
                    $amenities_not_exists = false;
                    
                    $am_arr = json_decode($request->amenities);
                    $amenities = json_decode($listing->amenities->amenities_json);
                    
                    $amenities_arr = [];
                    foreach($amenities as $aminity_key => $aminity){
                        if(!in_array($aminity_key, $amenities_arr)){
                            $amenities_arr[] = $aminity_key;
                        }
                    }
                    
                    // print_r($amenities_arr);die;
                    
                    foreach($am_arr as $amr){
                        if(!in_array($amr, $amenities_arr)){
                            $amenities_not_exists = true;
                            break;
                        }
                    }
                    
                    if($amenities_not_exists){
                        // continue;
                    }
                }
                
                $start = Carbon::parse($start_date);
                $end = Carbon::parse($end_date);
                
                $total_nights = $start->diffInDays($end);
                
                $total_nights = empty($total_nights) ? 1 : $total_nights;

                $per_night_price = !empty($total) && !empty($total_nights) ? round($total / $total_nights) : 0;
                
                $ratings = !empty($overall) && !empty($reviews) ? round($overall / count($reviews)) : 0;
                
                if($request->filled('ratings')){
                    $ratings_arr = json_decode($request->ratings);
                    
                    if(!empty($ratings_arr) && !in_array($ratings, $ratings_arr)){
                        // continue;
                    }
                }
                
                $currency = $this->get_logged_in_user_currency();
                
                $total = $this->calculate_price_with_exchange_rate($total, $currency);
                
                $per_night_price = $this->calculate_price_with_exchange_rate($per_night_price, $currency);
                
                $data[] = [
                    'listing_id' => $listing->listing_id,
                    'listing_name' => $listing->be_listing_name, //$listing_name,
                    'property_type' => $listing->property_type,
                    'ratings' => $ratings,
                    'ratings_count' => count($reviews),
                    'guest_review'=>$guest_review,
                    'bedrooms' => $listing->bedrooms,
                    'beds' => $listing->beds,
                    'bathrooms' => $listing->bathrooms,
                    'city_name' => $listing->city_name,
                    'district' => $listing->district,
                    'cleaning_fee' => $listing->cleaning_fee,
                    'discounts' => $listing->discounts,
                    'tax' => $listing->tax,
                    'total' => (int) $total,
                    'per_night_price' => (int) $per_night_price,
                    'total_nights' => (int) $total_nights,
                    'currency' => $currency,
                    'is_favorite' => !empty($guest_listing_ids) && in_array($listing->listing_id, $guest_listing_ids) ? 1 : 0,
                    'images' => $images
                ];
            }

            if(!empty($data)){
                return response()->json(['success'=>1, 'response'=>$data], 200);
            }

            return response()->json(['success'=>0, 'response'=>'data not found'], 400);
        } catch(Exception $ex){
            return response()->json(['success'=>0, 'response'=>$ex->getMessage()], 400);
        }
    }

    public function get_all_listings_filters(Request $request){
        try{
            
            $data = [];
            
            // if(!$request->filled('district')){
            //     return response()->json(['success'=>0, 'response'=>'District is required'], 400);
            // }
            
            $listings = Listing::with('setting', 'airbnbImages')
                ->whereNotNull('be_listing_name')
                ->where(['is_sync' => 'sync_all', 'is_churned' => 0]);
                
            if($request->filled('district')){
                if(strtolower($request->district) == "riyadh"){
                    $listings->where('city_name', "riyadh");
                } else{
                    $listings->where('district', $request->district);
                }
            }
            
            $start_date = $end_date = '';
            if($request->filled('start_date') && $request->filled('end_date')){
                
                $start_date = $request->input('start_date');
                $end_date = $request->input('end_date');

                // $listings->join('calenders', function($join) use ($start_date, $end_date) {
                //     $join->on('calenders.listing_id', '=', 'listings.listing_id')
                //     ->whereBetween('calenders.calender_date', [$start_date, $end_date])
                //     ->where(['calenders.availability'=>1, 'calenders.is_lock'=>0, 'calenders.is_blocked'=>0]);
                // });
            }
            
            $records = $listings->get();
            
            $min_max_arr = [];
            $property_type_arr = [];
            $ratings = [];
            
            $bedrooms = [];
            $beds = [];
            $bathrooms = [];
            
            $amenities_arr = [];
            
            foreach($records as $listing){
                
                $reviews = Review::where('review_json->meta->listing_id', $listing->listing_id)
                ->where('overall_score','>=','8')
                ->orderBy('id', 'Desc')
                ->get();
                
                if(empty($reviews)){
                    continue;
                }

                $images = $listing->airbnbImages()->pluck('url')->toArray();
                
                if(empty($images)){
                    continue;
                }
                
                if(is_null($listing->channel) || !is_null($listing->channel->connection_type)){
                    continue;
                }
                
                $overall = 0;
                foreach($reviews as $review){
                    $overall += $review->overall_score / 2;
                }
                
                if(empty($overall)){
                    continue;
                }

                // print_r($listing);die;
                
                $jsn = !empty($listing->listing_json) ? json_decode($listing->listing_json) : null;
                $listing_name = !empty($jsn->title) ? $jsn->title : '';
                
                if(empty($start_date) && empty($end_date)){
                    $start_date = $end_date = date('Y-m-d');
                    
                    $updatedBookingDateEnd = $end_date;
                } else {
                    $updatedBookingDateEnd = Carbon::parse($end_date)->subDay();
                }
                
                // $updatedBookingDateEnd = Carbon::parse($end_date)->subDay();
                
                $total = $listing->calendars()->whereBetween('calenders.calender_date', [$start_date, $updatedBookingDateEnd])->sum('rate');
                
                
                $start = Carbon::parse($start_date);
                $end = Carbon::parse($end_date);
                
                $total_nights = $start->diffInDays($end);
                
                $total_nights = empty($total_nights) ? 1 : $total_nights;
                
                $per_night_price = !empty($total) && !empty($total_nights) ? round($total / $total_nights) : 0;
                
                
                // $total = empty($total) && !is_null($listing->setting) ? $listing->setting->default_daily_price : $total;
                
                $rating = !empty($overall) && !empty($reviews) ? round($overall / count($reviews)) : 0;
                
                if(!in_array($rating, $ratings)){
                    $ratings[] = $rating;
                }
                
                // $min_max_arr[] = $total;
                $min_max_arr[] = $per_night_price;
                
                // print_r($min_max_arr);die;
                
                if(!empty($listing->property_type) && !in_array($listing->property_type, $property_type_arr)){
                    $property_type_arr[] = $listing->property_type;
                }
                
                if(!in_array($listing->bedrooms, $bedrooms)){
                    $bedrooms[] = $listing->bedrooms;
                }
                
                if(!in_array($listing->beds, $beds)){
                    $beds[] = $listing->beds;
                }
                
                if(!in_array($listing->bathrooms, $bathrooms)){
                    $bathrooms[] = $listing->bathrooms;
                }
                
                if(!is_null($listing->amenities) && !empty($listing->amenities->amenities_json)){
                    $amenities = json_decode($listing->amenities->amenities_json);
                    
                    foreach($amenities as $aminity_key => $aminity){
                        if(!in_array($aminity_key, $amenities_arr)){
                            $amenities_arr[] = $aminity_key;
                        }
                    }
                }
            }

            $min_value = !empty($min_max_arr) ? (int) min($min_max_arr) : 0;
            $max_value = !empty($min_max_arr) ? (int) max($min_max_arr) : 0;
            
            $currency = $this->get_logged_in_user_currency();
                
            $min_value = $this->calculate_price_with_exchange_rate($min_value, $currency);
            
            $max_value = $this->calculate_price_with_exchange_rate($max_value, $currency);
            
            $price = ['min'=>$min_value, 'max'=>$max_value];
            
            $rooms_and_beds = [
                // 'bedrooms'=>$bedrooms,
                // 'beds'=>$beds,
                // 'bathrooms'=>$bathrooms,
                'max_bedrooms'=>!empty($bedrooms)?max($bedrooms):0,
                'max_beds'=>!empty($beds)?max($beds):0,
                'max_bathrooms'=>!empty($bathrooms)?max($bathrooms):0
            ];

            return response()->json([
                'success'=>1,
                'price'=>$price,
                'currency' => $currency,
                'property_type'=>$property_type_arr,
                'ratings'=>$ratings,
                'rooms_and_beds'=>$rooms_and_beds,
                'amenities'=>$amenities_arr
            ], 200);

        } catch(Exception $ex){
            return response()->json(['success'=>0, 'response'=>$ex->getMessage()], 400);
        }
    }
    
    public function fetch_update_listings(){
        
        echo 'Not required, closed';die;

        $listings = Listing::where(['is_sync' => 'sync_all', 'is_churned' => 0, 'is_attempted'=>0])
        // ->whereNull('city_name')
        ->orderBy('id', 'desc')
        ->limit(10)
        ->get();
        
        // $listings = Listing::where(['listing_id'=>1309496407373708339])
        // ->get();
        

        foreach($listings as $listing){

            $listing->is_attempted = 1;
            $listing->save();
            
            if(is_null($listing->channel) || !is_null($listing->channel->connection_type)){
                continue;
            }

            $response = Http::withHeaders([
                'user-api-key' => $this->apiKey,
            ])->get(env('CHANNEX_URL') . "/api/v1/channels/".$listing->channel->ch_channel_id."/action/listing_details?listing_id=$listing->listing_id");
            
            // print_r($response->json());die;
            
            // 1309496407373708339
            // Array ( [errors] => Array ( [code] => validation_error [title] => Validation Error [details] => Array ( [action] => Array ( [0] => is not authorized ) ) ) )

            if ($response->successful()) {
                
                $response = $response->json();
                
                print_r($response);die;
                
                $channex_listing = !empty($response['data']['listing']) ? $response['data']['listing'] : '';
                
                $is_allow_pets = !empty($channex_listing['booking_settings']['guest_controls']['allows_pets_as_host']) ? 1 : 0;
                
                $is_self_check_in = false;
                if(!empty($channex_listing['check_in_option']['category']) && $channex_listing['check_in_option']['category'] == "keypad"){
                    $is_self_check_in = true;
                }
                
                $amenities = !empty($channex_listing['amenities']) ? json_encode($channex_listing['amenities']) : [];
                
                if(!empty($amenities)){
                    if (!$listing->amenities) {
                        $listing->amenities()->create([
                            'amenities_json' => $amenities,
                        ]);
                    }
                }
                
                $property_type = $channex_listing['property_type_category'] ?? $listing->property_type;
                
                $listing->is_allow_pets = $is_allow_pets;
                $listing->bedrooms = $channex_listing['bedrooms'] ?? $listing->bedrooms;
                $listing->beds = $channex_listing['beds'] ?? $listing->beds;
                $listing->bathrooms = $channex_listing['bathrooms'] ?? $listing->bathrooms;
                $listing->city_name = $channex_listing['city'] ?? $listing->city_name;
                $listing->property_type = strtolower($property_type);
                $listing->street = $channex_listing['street'] ?? '';
                $listing->is_self_check_in = $is_self_check_in;
                $listing->save();
            }
        }
        
        echo "Executed";
    }
    
    public function fetchListingDetails($listing_id) //: mixed
    {
        
        try
        {
        $guest_listing_ids = [];
        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->id)){
            $guest_listing_ids = DB::table('guest_favorites')->where(['user_id' => $guest->id])->pluck('listing_id')->toArray();
        }
            
        $listing = Listing::where('listing_id', $listing_id)->first();
        
        // print_r($listing);die;
        
        $apartment = [];
        if($listing) {
            $reviews = Review::where('review_json->meta->listing_id', $listing->listing_id)
            ->where('overall_score','>=','8')
                ->orderBy('id', 'Desc')
                ->get();
                $averageScore = round($reviews->avg('overall_score') / 2);
                $reviewCount = count($reviews);
            $channel = Channels::where('id', $listing->channel_id)->first();
            $response = Http::withHeaders([
                'user-api-key' => $this->apiKey,
            ])->get(env('CHANNEX_URL') . "/api/v1/channels/{$channel->ch_channel_id}/action/listing_details?listing_id=$listing_id");
            if ($response->successful()) {
                $response = $response->json();
                 $amenities = $response['data']['listing']["amenities"];

                //$filteredAmenities = array_keys(array_filter($amenities, function ($item) {
                   // return isset($item['is_present']) && $item['is_present'] === true;
                //}));
                
                        
                if (!is_null($listing->amenities) && !empty($listing->amenities->amenities_json)) {
                    $dbamenities = json_decode($listing->amenities->amenities_json, true); 
                }
            
                if (!empty($dbamenities)) {
                    $filteredAmenities = array_keys(array_filter($dbamenities, function ($item) {
                        return isset($item['is_present']) && $item['is_present'] === true;
                    }));
                }
                
                $apartment['name'] = $listing->be_listing_name; //$response['data']['listing']['listing_nickname'];
                $apartment['apartment_description'] = $listing->property_about; //"Al Olaya is the vibrant heart of Riyadh, renowned for its modern skyline, bustling commercial activity, and cosmopolitan atmosphere. As the city's financial and business hub, it features iconic landmarks such as the Kingdom Tower and Al Faisaliah Tower, which define its striking urban landscape. The district offers a seamless blend of luxury and convenience, with upscale shopping malls, high-end hotels, and a diverse array of international restaurants and cafes. Its central location and dynamic energy make Al Olaya a prime destination for both residents and visitors, epitomizing the contemporary charm and ambition of Riyadh.";
                $apartment['images'] = $response['data']['listing']["images"];
                $apartment['amenities'] = !empty($filteredAmenities) ? $filteredAmenities : [];
                $apartment['rooms'] = $response['data']['listing']["rooms"];
                $apartment['reviewCount'] = $reviewCount;
                $apartment['averageScore'] = $averageScore;
                $apartment['reviews'] = $reviews;
                $apartment['city_description'] = $listing->district." is the vibrant heart of Riyadh, renowned for its modern skyline, bustling commercial activity, and cosmopolitan atmosphere. As the city's financial and business hub, it features iconic landmarks such as the Kingdom Tower and Al Faisaliah Tower, which define its striking urban landscape. The district offers a seamless blend of luxury and convenience, with upscale shopping malls, high-end hotels, and a diverse array of international restaurants and cafes. Its central location and dynamic energy make ".$listing->district." a prime destination for both residents and visitors, epitomizing the contemporary charm and ambition of Riyadh.";
                $apartment['map'] = $listing->google_map;
                $apartment['checkin_time'] = $response['data']['listing']['booking_settings']['check_in_time_end'];
                $apartment['checkout_time'] = $response['data']['listing']['booking_settings']['check_out_time'];
                $apartment['cancellation_policy'] = $response['data']['listing']['booking_settings']['cancellation_policy_settings']['cancellation_policy_category'];
                $apartment['self_checkin'] = $listing->is_self_check_in == 1 ? "Yes" : "No"; //$response['data']['listing']['check_in_option']['category'];
                $apartment['district'] = $listing->district;
                $apartment['lat'] = $response['data']['listing']['lat'];
                $apartment['lng'] = $response['data']['listing']['lng'];
                $apartment['beds'] = $listing->beds;
                $apartment['bedrooms'] = $listing->bedrooms;
                $apartment['bathrooms'] = $listing->bathrooms;
                $apartment['cleaning_fee'] = $listing->cleaning_fee;
                $apartment['discounts'] = $listing->discounts;
                $apartment['tax'] = $listing->tax;
                
                $current_year = Carbon::now()->year;
                
                $created_at = $listing->created_at;
                $created_year = Carbon::parse($created_at)->year;
                
                $years_hosting = $current_year - $created_year;
                
                $apartment['years_hosting'] = $years_hosting > 1 ? $years_hosting . " Years Hosting" : "1 Year Hosting";

                $apartment['is_favorite'] = !empty($guest_listing_ids) && in_array($listing->listing_id, $guest_listing_ids) ? 1 : 0;
                
                foreach ($apartment['rooms'] as &$room) {
                    $room['images'] = array_values(array_filter($apartment['images'], function ($image) use ($room) {
                        return isset($image['room_id']) && $image['room_id'] === $room['id'];
                    }));
                }
                return $apartment;
                // return $response['data']['listing'];
            } else {
                return $response->body();
            }
        }
      }
      catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred', 'message' => $e->getMessage()], 500);
        }
    }
    
    public function currency_exchange_rate_update(){

        $url = 'https://v6.exchangerate-api.com/v6/ef39bbc4c09e807d2acf103e/latest/SAR';
        $response = Http::get($url);
        
        if ($response->successful()) {
            $data = $response->json();

            if(!empty($data['conversion_rates']['USD'])){
                
                DB::table('currency_rates')->updateOrInsert(
                    ['currency_text' => 'SAR_TO_USD'],
                    [
                        'exchange_rate' => $data['conversion_rates']['USD'],
                        'last_updated_at' => date('Y-m-d h:i:s')
                    ]
                );
            }
            
            // return $data['conversion_rates']['USD'];
        } else {
            // return response()->json(['success' => false, 'error' => 'Unable to fetch data.'], $response->status());
        }
        
        $url = 'https://v6.exchangerate-api.com/v6/ef39bbc4c09e807d2acf103e/latest/USD';
        $response = Http::get($url);
        
        if ($response->successful()) {
            $data = $response->json();

            if(!empty($data['conversion_rates']['SAR'])){
                
                DB::table('currency_rates')->updateOrInsert(
                    ['currency_text' => 'USD_TO_SAR'],
                    [
                        'exchange_rate' => $data['conversion_rates']['SAR'],
                        'last_updated_at' => date('Y-m-d h:i:s')
                    ]
                );
            }
            
            // return $data['conversion_rates']['SAR'];
        } else {
            // return response()->json(['success' => false, 'error' => 'Unable to fetch data.'], $response->status());
        }
    }

    public function calculate_price_with_exchange_rate($price, $currency, $type="prices"){
        
        if($type == 'get_conversion'){
            $currency_rate = DB::table('currency_rates')->where(['currency_text' => 'USD_TO_SAR'])->first();

            if(is_null($currency_rate)){
                return $price;
            }
            
            return round($price * $currency_rate->exchange_rate);
        }
        
        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->currency)){
            $currency = $guest->currency;
        }

        $currency_rate = DB::table('currency_rates')->where(['currency_text' => 'SAR_TO_'.$currency])->first();

        if(is_null($currency_rate)){
            return $price;
        }
        
        return round($price * $currency_rate->exchange_rate);
    }

    public function get_logged_in_user_currency(){

        $currency = 'SAR';
        if(!empty($request->currency)){
            $currency = $request->currency;
            return $currency;
        }
        
        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->currency)){
            $currency = $guest->currency;
        }

        return $currency;
    }
    
    public function getBlockedDates($listing_id)
    {
       $today = Carbon::now()->toDateString();
        // dd($taday);
        $blockedDates = Calender::where('listing_id', $listing_id)
        ->where('calender_date', '>' ,$today)
        ->where('availability', 0)
        // ->get();
        ->pluck('calender_date')
        ->toArray();
        
        // $blockedDates = $calender->pluck('calender_date')->toArray();
        return response()->json(['blockedDates' => $blockedDates]);
    }

    public function getPrice($listing_id,$start_date, $end_date)
    {
        
        $updatedBookingDateEnd = Carbon::parse($end_date)->subDay();
        
        // dd($listing_id,$start_date, $end_data);
        $rates = Calender::where('listing_id', $listing_id)
        ->whereBetween('calender_date', [$start_date, $updatedBookingDateEnd])
        ->pluck('rate');
        $sum = $rates->sum();
        $average = $rates->avg();

        $currency = $this->get_logged_in_user_currency();

        $sum = $this->calculate_price_with_exchange_rate($sum, $currency);

        $average = $this->calculate_price_with_exchange_rate($average, $currency);
        
        
        $start = Carbon::parse($start_date);
        $end = Carbon::parse($end_date);
        $total_nights = $start->diffInDays($end);

        return ['total' => round($sum), 'adr' => round($average), 'guest_currency' => $currency, 'total_nights'=>$total_nights];
    }

    public function update_guest_currency(Request $request){
        if(!empty($request->user_id) && !empty($request->currency)){
            $guest = DB::table('guests')->where(['id' => $request->user_id])->update(['currency'=>$request->currency]);
            return response()->json(['status'=>1, 'currency'=>$request->currency], 200);
        }
        return response()->json(['status'=>0, 'currency'=>''], 200);
    }
    
    public function guest_favorite(Request $request){
        if(!empty($request->user_id) && !empty($request->listing_id)){
            
            $guest_favorite = DB::table('guest_favorites')->where(
                    [
                        'user_id' => $request->user_id,
                        'listing_id' => $request->listing_id
                    ]
                )->first();
            
            if(is_null($guest_favorite) && $request->is_favorite == 1){ // add favorite
                DB::table('guest_favorites')->insert(
                    [
                        'user_id' => $request->user_id,
                        'listing_id' => $request->listing_id,
                        'start_date' => $request->filled('start_date') ? $request->start_date : null,
                        'end_date' => $request->filled('end_date') ? $request->end_date : null,
                        'total' => $request->filled('total') ? $request->total : null,
                        'per_night' => $request->filled('per_night') ? $request->per_night : null,
                        'total_nights' => $request->filled('total_nights') ? $request->total_nights : null
                    ]
                );
            }
            
            if($request->is_favorite == 0){ // remove favorite
                DB::table('guest_favorites')->where(
                    [
                        'user_id' => $request->user_id,
                        'listing_id' => $request->listing_id
                    ]
                )->delete();
            }
        }
        return true;
    }

    public function checkout(Request $request)
    {
        // dd($request);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'listing_id' => 'required',
            'adult' => 'required',
            'children' => 'required',
            'pets' => 'required',
            'rooms' => 'required',
            'payment_method' => 'required',
            'booking_date_start' => 'required',
            'booking_date_end' => 'required',
            'cleaning_fee' => 'required',
            'service_fee' => 'required',
            'per_night_price' => 'required',
            'total_price' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        
        // logger("Checkout Payload: " . json_encode($request->all()));
        
        $guest = Guests::where('email', $request->email)->first();
        $listing = Listing::where('listing_id', $request->listing_id)->first();
        if(is_null($guest)) {
            $guest = Guests::create(
                [
                    'name' => $request->name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]
            );
        } else{
            $guest->name = $request->name;
            $guest->email = $request->email;
            $guest->phone = $request->phone;
            $guest->save();
        }
        
        // dd( $guest);guest_id
        $request['listing_id'] = $listing->id;
        $request['guest_id'] = $guest->id;
        $request['booking_sources'] = "booking_engine";
        
        $currency = $this->get_logged_in_user_currency();
        if(strtoupper($currency) != "SAR"){
            $request['cleaning_fee'] = $this->calculate_price_with_exchange_rate($request['cleaning_fee'], $currency, 'get_conversion');
            $request['service_fee'] = $this->calculate_price_with_exchange_rate($request['service_fee'], $currency, 'get_conversion');
            $request['per_night_price'] = $this->calculate_price_with_exchange_rate($request['per_night_price'], $currency, 'get_conversion');
            $request['total_price'] = $this->calculate_price_with_exchange_rate($request['total_price'], $currency, 'get_conversion');
        }
        
        // $booking = Bookings::create($request->all());
        
        $booking = BookingLead::create($request->all());
        
        $start = Carbon::parse($booking->booking_date_start);
        $end = Carbon::parse($booking->booking_date_end);
        
        $total_nights = $start->diffInDays($end);
        
        $booking['total_nights'] = $total_nights;

        // $updatedBookingDateEnd = Carbon::parse($request->booking_date_end)->subDay();
        
        // return new BookingResource($booking);
        
        return response()->json($booking);
    }

    public function get_unique_amenities(){

        $unique_amenities = [];
        $amenities = DB::table('listing_amenities')->get();
        foreach($amenities as $amenity){
            if(!empty($amenity->amenities_json)){
                $get_amenities = json_decode($amenity->amenities_json);
                
                foreach($get_amenities as $amnkey => $amnvalue){
                    
                    if(!in_array($amnkey, $unique_amenities)){
                        $unique_amenities[] = $amnkey;
                    }
                }
            }
        }
        sort($unique_amenities);

        return response()->json($unique_amenities, 200);
    }
    
    public function getPaymentStatus(Request $request){

        $beOrder = BeOrder::where('listing_id', $request->listing_id)
        ->where('email', $request->email)->where('start_date', $request->start_date)
        ->where('end_date', $request->end_date)
        // ->where('status', 'unpaid')
        ->orderBy('id', 'DESC')
        ->first();
        
        if(isset($beOrder->status) && strtoupper($beOrder->status) == "SUCCESS"){
            return response()->json([
                'message' => 'Payment paid',
                'status' => 1
            ]);
        }else {
            return response()->json([
                'message' => 'Payment unpaid',
                'status' => 0
            ]);
        }
    }
    
    public function createPaymentStatus(Request $request){
        
        $currency = $this->get_logged_in_user_currency();
        
        $gtotal = !empty($request->total) ? $request->total : 0;
        // if(strtoupper($currency) != "SAR"){
        //     $gtotal = $this->calculate_price_with_exchange_rate($gtotal, $currency, 'get_conversion');
        // }
        
        // if(!empty($request->booking_id)){
            
        //     $fnbooking = Bookings::find($request->booking_id);
        //     if(!is_null($fnbooking)){
                
        //         if(!empty($request->adult)){
        //             $fnbooking->adult = $request->adult;
        //         }
                
        //         if(!empty($request->children)){
        //             $fnbooking->children = $request->children;
        //         }
                
        //         if(!empty($request->start_date)){
        //             $fnbooking->booking_date_start = $request->start_date;
        //         }
                
        //         if(!empty($request->end_date)){
        //             $fnbooking->booking_date_end = $request->end_date;
        //         }
        //         $fnbooking->save();
                
        //         $bookingLead = BookingLead::find($fnbooking->booking_lead_id);
        //         if(!is_null($bookingLead)){
                    
        //             if(!empty($request->adult)){
        //                 $bookingLead->adult = $request->adult;
        //             }
                    
        //             if(!empty($request->children)){
        //                 $bookingLead->children = $request->children;
        //             }
                    
        //             if(!empty($request->start_date)){
        //                 $bookingLead->booking_date_start = $request->start_date;
        //             }
                    
        //             if(!empty($request->end_date)){
        //                 $bookingLead->booking_date_end = $request->end_date;
        //             }
        //             $bookingLead->save();
        //         }
        //     }
        // }

        $beOrder = BeOrder::create([
            'listing_id'=>$request->listing_id,
            'email'=>$request->email,
            'start_date'=>$request->start_date,
            'end_date'=>$request->end_date,
            'status'=>'unpaid',
            'total'=>$gtotal,
            ]);

        return response($beOrder);
    }
    
    public function updatePaymentStatus(Request $request){
        $beOrder = BeOrder::where('listing_id', $request->listing_id)
        ->where('email', $request->email)->where('start_date', $request->start_date)
        ->where('end_date', $request->end_date)
        ->orderBy('id', 'DESC')
        ->first();
        
       $beOrder->update([]);

        return response($beOrder);
    }

    public function add_user_searches(Request $request){
        
        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->id)){
            
            $user_searches = DB::table('user_searches')->where(['user_id' => $guest->id])->first();
            if(!is_null($user_searches) && !empty($request->searches)){
                
                // print_r($request->searches);die;

                $searches = !empty($user_searches->searches) ? json_decode($user_searches->searches) : [];
                if(!empty($searches)){
                    
                    foreach($request->searches as $request_search){
                        
                        // print_r($request_search['count']);die;
                        
                        // print_r($searches);die;
                        
                        $is_found = false;
                        foreach($searches as $search_key => $search){
                            if($request_search['label'] == $search->label){
                                $searches[$search_key]->count += (int) $request_search['count'];
                                $is_found = true;
                                break;
                            }
                        }
                        
                        if(!$is_found){
                            $searches[] = $request_search;
                        }
                    }
                    
                    DB::table('user_searches')->where(['user_id' => $guest->id])->update(['searches'=>json_encode($searches)]);
                    
                    return response()->json([
                        'success' => 1
                    ]);
                }
            }
            
            if($request->filled('searches') && is_null($user_searches)){
                DB::table('user_searches')->insert([
                    'user_id'=>$guest->id,
                    'searches'=>json_encode($request->searches),
                ]);
                
                return response()->json([
                    'success' => 1
                ]);
            }
        }
        
        return response()->json([
            'success' => 0,
            'error' => 'Guest not found'
        ]);
    }
    
    public function get_user_searches(){

        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->id)){

            $user_searches = DB::table('user_searches')->where(['user_id' => $guest->id])->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(searches, '$[0].count')) DESC")->first();
            if(!is_null($user_searches)){
                
                $searches = json_decode($user_searches->searches, true);
                
                usort($searches, function($a, $b) {
                    return $b['count'] - $a['count'];
                });

                return response()->json([
                    'success' => 1,
                    'data' => $searches
                ]);
            }
        }
        
        return response()->json([
            'success' => 0,
            'data' => []
        ]);
    }
    
    public function updateProfile(Request $request){

        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->id)){
            
            if(!empty($request->name)){
                $guest->name = $request->name;
            }
            
            if(!empty($request->email)){
                $guest->email = $request->email;
            }
            
            if(!empty($request->phone_code)){
                $guest->phone_code = $request->phone_code;
            }
            
            if(!empty($request->country_code)){
                $guest->country_code = $request->country_code;
            }
            
            if(!empty($request->phone)){
                $guest->phone = $request->phone;
            }
            
            if(!empty($request->country_name)){
                $guest->country_name = $request->country_name;
            }
            
            if(!empty($request->city_name)){
                $guest->city_name = $request->city_name;
            }
            
            // print_r($guest);die;
            
            $guest->save();
            
            $guest->dp = url('/storage').'/'.$guest->dp;
            
            return response()->json([
                'success' => 1,
                'guest' => $guest
            ]);
        }
        
        return response()->json([
            'success' => 0
        ]);
    }
    
    public function updateProfileDp(Request $request){

        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->id)){
            
            $guest_dp = "";
            if ($request->hasFile('dp')) {
    
                if (!empty($guest->dp)) {
                    Storage::delete('public/' . $guest->dp);
                }

                $file = $request->file('dp');
                $filePath = $file->store('guests_dp', 'public');
                $guest_dp = $filePath;
            }
            
            if(!empty($guest_dp)){
                $guest->dp = $guest_dp;
                $guest->save();
                
                $guest->dp = url('/storage').'/'.$guest_dp;
                return response()->json([
                    'success' => 1,
                    'guest' => $guest
                ]);
            }
        }
        
        return response()->json([
            'success' => 0
        ]);
    }
    
    public function getGuestBookings(Request $request){
        
        $guest = Auth::guard('guest')->user();
        
        if($guest && !empty($guest->id)){
            
            $bookings = Bookings::where('guest_id', $guest->id) //$guest->id | 1548
            ->whereNotNull('created_at');
            
            if($request->filled('status')){
                $bookings->where('booking_status', $request->status);
            }
            
            $bookings = $bookings->orderBy('id', 'DESC')
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('F-Y');
            });
            
            $formattedBookings = [];
            foreach ($bookings as $month => $bookingData) {
                
                foreach($bookingData as $bk_key => $bkd){
                    if(!empty($bkd['listing_id'])){
                        
                        $listing = Listing::find($bkd['listing_id']);
                        
                        $images = $listing->airbnbImages()->pluck('url')->toArray();
                
                        if(!is_null($listing)){
                            $bookingData[$bk_key]['listing_id'] = $listing->listing_id;
                            $bookingData[$bk_key]['property_type'] = $listing->property_type;
                            $bookingData[$bk_key]['be_listing_name'] = $listing->be_listing_name;
                            $bookingData[$bk_key]['beds'] = $listing->beds;
                            $bookingData[$bk_key]['bathrooms'] = $listing->bathrooms;
                            $bookingData[$bk_key]['district'] = $listing->district;
                            $bookingData[$bk_key]['city_name'] = $listing->city_name;
                            $bookingData[$bk_key]['images'] = !empty($images) ? $images[0] : [];
                        }
                    }
                }
                
                $formattedBookings['bookings'][$month] = $bookingData;
            }
            
            if(empty($formattedBookings)){
                return response()->json(['error'=>'Booking not found']);
            }
            
            return $formattedBookings;
        }
        
        return response()->json(['error'=>'Guest not logged in']);
    }
    
    public function getGuestBookingDetail($booking_id){
        
        $data = [];
        
        $guest = Auth::guard('guest')->user();
        if(empty($guest->id)){
            return response()->json(['error'=>'Guest not logged in']);
        }
        
        $booking = Bookings::where('guest_id', $guest->id) //$guest->id | 1548
            ->where('id', $booking_id)
            ->whereNotNull('created_at')
            ->first();

        if(empty($booking)){
            return response()->json(['error'=>'Booking not found']);
        }

        $listing = Listing::find($booking->listing_id);
        if(is_null($listing)){
            return response()->json(['error'=>'Listing not found']);
        }
        
        $start = Carbon::parse($booking->booking_date_start);
        $end = Carbon::parse($booking->booking_date_end);
        
        $total_nights = $start->diffInDays($end);
        
        $reviews = Review::where('review_json->meta->listing_id', $listing->listing_id)
            ->where('overall_score','>=','8')
            ->orderBy('id', 'Desc')
            ->get();
        
        $averageScore = $reviewCount = 0;
        if(!empty($reviews)){
            $averageScore = round($reviews->avg('overall_score') / 2);
            $reviewCount = count($reviews);
        }
        
        $current_year = Carbon::now()->year;
        
        $created_at = $listing->created_at;
        $created_year = Carbon::parse($created_at)->year;
        
        $years_hosting = $current_year - $created_year;
        
        $images = $listing->airbnbImages()->pluck('url')->toArray();
        
        $currency = $this->get_logged_in_user_currency();
        
        $data = [
            'booking_id' => $booking->id,
            'adult' => $booking->adult ?? 0,
            'children' => $booking->children ?? 0,
            'infants' => 0,
            'pets' => 0,
            'booking_date_start' => $booking->booking_date_start,
            'booking_date_end' => $booking->booking_date_end,
            'total_price' => $this->calculate_price_with_exchange_rate($booking->total_price, $currency),
            'per_night_price' => $this->calculate_price_with_exchange_rate($booking->per_night_price, $currency),
            'total_nights' => $total_nights,
            'cleaning_fee' => $this->calculate_price_with_exchange_rate($booking->cleaning_fee, $currency),
            'service_fee' => $this->calculate_price_with_exchange_rate($booking->service_fee, $currency),
            'tax' => $this->calculate_price_with_exchange_rate($listing->tax, $currency),
            'discounts' => $this->calculate_price_with_exchange_rate($listing->discounts, $currency),
            'listing_id' => $listing->listing_id,
            'be_listing_name' => $listing->be_listing_name,
            'property_type' => $listing->property_type,
            'district' => $listing->district,
            'average_score' => $averageScore,
            'review_count' => $reviewCount,
            'years_hosting' => $years_hosting > 1 ? $years_hosting . " Years Hosting" : "1 Year Hosting",
            'google_map' => $listing->google_map,
            'property_about' => $listing->property_about,
            'bedrooms' => $listing->bedrooms,
            'beds' => $listing->beds,
            'bathrooms' => $listing->bathrooms,
            'booking_status' => $booking->booking_status,
            'reservation_code' => $booking->reservation_code,
            'payment_method' => $booking->payment_method,
            'images' => !empty($images) ? $images : [],
            'amenities' => !empty($listing->amenities->amenities_json) ? json_decode($listing->amenities->amenities_json) : [],
        ];
        
        return response()->json($data);
    }
    
    public function getPlaces(){
        
        $data = [];
        
        $districts = Listing::whereNotNull('district')->groupBy('district')->pluck('district')->toArray();
        
        $city_names = Listing::whereNotNull('city_name')->groupBy('city_name')->pluck('city_name')->toArray();
        
        foreach($districts as $district){
            $data[] = ['label'=>$district, 'value'=>$district];
        }
        
        foreach($city_names as $city_name){
            $data[] = ['label'=>$city_name, 'value'=>$city_name];
        }
        
        if(empty($data)){
            return response()->json(['success'=>0, 'data'=>[]]);
        }
        
        return response()->json(['success'=>1, 'data'=>$data]);
    }

    public function getWishList(Request $request){
        
        $listings = [];
        $guest = Auth::guard('guest')->user();
        // echo $guest->id;die;
        
        if($guest && !empty($guest->id)){
            $listings = DB::table('guest_favorites')->where(['user_id' => $guest->id])->get();
            
            $currency = $this->get_logged_in_user_currency();

            foreach($listings as $key => $listing){
            
                $listing = Listing::where('listing_id', $listing->listing_id)->first();
                if(is_null($listing)){
                    $listings[$key]->images = "";
                    continue;
                }
                
                $images = $listing->airbnbImages()->pluck('url')->toArray();
                // print_r($images);die;
                $listings[$key]->images = !empty($images) ? $images[0] : "";
                
                $listings[$key]->listing_id = (string) $listing->listing_id;

                $listings[$key]->listing_name = $listing->be_listing_name;
                $listings[$key]->district = $listing->district;
                $listings[$key]->city_name = $listing->city_name;
                $listings[$key]->bedrooms = $listing->bedrooms;
                $listings[$key]->beds = $listing->beds;
                $listings[$key]->bathrooms = $listing->bathrooms;
                $listings[$key]->property_type = $listing->property_type;

                $listings[$key]->total = !empty($listings[$key]->total) ? $this->calculate_price_with_exchange_rate($listings[$key]->total, $currency) : 0;
                $listings[$key]->per_night = !empty($listings[$key]->per_night) ? $this->calculate_price_with_exchange_rate($listings[$key]->per_night, $currency) : 0;
                $listings[$key]->currency = $currency;

            }
            
            if(empty($listings)){
                return response()->json(['error'=>'Wishlist not found']);
            }
            
            return response()->json($listings);
        }
        
        return response()->json(['error'=>'Guest not logged in']);
    }
    
    public function bookingModified(Request $request){
        
        if(!$request->filled('booking_id')){
            return response()->json(['success'=>0, 'error'=>'Booking_id is required']);
        }
        
        $data = [];
        if($request->filled('start_date') && $request->filled('end_date')){
            $data['booking_date_start'] = $request->start_date;
            $data['booking_date_end'] = $request->end_date;
        }
        
        if($request->filled('service_fee')){
            $data['service_fee'] = $request->service_fee;
        }
        
        if($request->filled('cleaning_fee')){
            $data['cleaning_fee'] = $request->cleaning_fee;
        }
        
        if($request->filled('per_night_price')){
            $data['per_night_price'] = $request->per_night_price;
        }
        
        if($request->filled('total_price')){
            $data['total_price'] = $request->total_price;
        }
        
        if($request->filled('adult')){
            $data['adult'] = $request->adult;
        }
        
        if($request->filled('children')){
            $data['children'] = $request->children;
        }
        
        if(empty($data)){
            return response()->json([
                'success' => 0,
                'msg' => "Data is required",
            ]);
        }
        
        // $booking = Bookings::where('id', $request->booking_id)->update($data);
        
        return response()->json([
            'success' => 1,
            'msg' => "Data updated successfully",
        ]);
    }
    
    public function bookingModifiedPay(Request $request){

        if(!$request->filled('booking_id')){
            return response()->json(['success'=>0, 'error'=>'Booking_id is required']);
        }
        
        if(!$request->filled('total_price')){
            return response()->json(['success'=>0, 'error'=>'Total Price is required']);
        }
        
        if(!$request->filled('start_date')){
            return response()->json(['success'=>0, 'error'=>'Start Date is required']);
        }
        
        if(!$request->filled('end_date')){
            return response()->json(['success'=>0, 'error'=>'End Date is required']);
        }
        
        $refundAmount = $this->calculateRefundAmount($request->booking_id, $request->total_price);
        if(empty($refundAmount)){
            return response()->json(['success'=>0, 'error'=>'The refund amount criteria are not being met, Refund amount is required']);
        }

        // return response()->json([
        //     'success' => 1,
        //     'msg' => 'Refund Processed successfully'
        // ]);
        
        
        $booking = Bookings::find($request->booking_id);
        if(is_null($booking)){
            return response()->json(['success'=>0, 'error'=>'Booking not found']);
        }
        
        $bookingLead = BookingLead::find($booking->booking_lead_id);
        if(is_null($bookingLead)){
            return response()->json(['success'=>0, 'error'=>'Booking lead not found']);
        }
        
        $response = Http::withToken(env('MYFATOORAH_TOKEN'))
        ->post(env('MYFATOORAH_URL') . "MakeRefund", 
            [
                "Key" => $bookingLead->invoice_id,
                "KeyType" => "InvoiceId",
                "ServiceChargeOnCustomer" => false,
                "Amount" => $refundAmount, // 0.3
                "Comment" => "Modified Partial refund to the customer and refund amount is " . $refundAmount,
                // "AmountDeductedFromSupplier" => 0
            ]
        );
        
        if ($response->successful()) {
            
            $data = $response->json();
            
            logger("Modified Pay Response: " . json_encode($data));
            
            $listing = Listing::where('id', $booking->listing_id)->first();

            if(!is_null($listing)){
                
                Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$booking->booking_date_start, $booking->booking_date_end])
                ->update(
                    ['availability' => 1]
                );
                
                Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$request->start_date, $request->end_date])
                ->update(
                    ['availability' => 0]
                );
            }
            
            if(isset($data['IsSuccess']) && $data['IsSuccess'] == true){
                return response()->json([
                    'success' => 1,
                    'msg' => 'Refund Processed successfully'
                ]);
            }
        }
        
        logger("Modified Pay Response Error: " . json_encode($response->body()));
        
        return response()->json([
            'success' => 0,
            'error' => $response->body()
        ]);
    }
    
    public function bookingCancelled(Request $request){
        
        if(!$request->filled('booking_id')){
            return response()->json(['success'=>0, 'error'=>'Booking_id is required']);
        }
        
        if(!$request->filled('reason')){
            return response()->json(['success'=>0, 'error'=>'Reason is required']);
        }
        
        if(!$request->filled('reason_detail')){
            return response()->json(['success'=>0, 'error'=>'Reason Detail is required']);
        }
        
        $refundAmount = $this->calculateRefundAmount($request->booking_id);
        if(empty($refundAmount)){
            return response()->json(['success'=>0, 'error'=>'The refund amount criteria are not being met, Refund amount is required']);
        }

        return response()->json([
            'success' => 1,
            'msg' => 'Refund Processed successfully'
        ]);
        
        
        $fnbooking = Bookings::find($request->booking_id);
        if(is_null($fnbooking)){
            return response()->json(['success'=>0, 'error'=>'Booking not found']);
        }
        
        $bookingLead = BookingLead::find($fnbooking->booking_lead_id);
        if(is_null($bookingLead)){
            return response()->json(['success'=>0, 'error'=>'Booking lead not found']);
        }
        
        $response = Http::withToken(env('MYFATOORAH_TOKEN'))
        ->post(env('MYFATOORAH_URL') . "MakeRefund", 
            [
                "Key" => $bookingLead->invoice_id,
                "KeyType" => "InvoiceId",
                "ServiceChargeOnCustomer" => false,
                "Amount" => $refundAmount,
                "Comment" => "Cancelled Partial refund to the customer and refund amount is " . $refundAmount,
                "AmountDeductedFromSupplier" => 0
            ]
        );
        
        if ($response->successful()) {
            
            $data = $response->json();
            
            $refund = DB::table('booking_refunds')->insert(
                [
                    'booking_id' => $bookingLead->booking_id,
                    'invoice_id' => $bookingLead->invoice_id,
                    'refund_amount' => $refundAmount,
                    'refund_status' => 'Pending',
                    'reason' => $request->reason,
                    'reason_detail' => $request->reason_detail,
                    'refund_json' => json_encode($data),
                ]
            );
            
            if(isset($data['IsSuccess']) && $data['IsSuccess'] == true){
                return response()->json([
                    'success' => 1,
                    'msg' => 'Refund Processed successfully'
                ]);
            }
        }
        
        return response()->json([
            'success' => 0,
            'error' => $response->body()
        ]);
    }
    
    public function refundProcess(){
        
        $refund = DB::table('booking_refunds')
        ->where('refund_status', 'Pending')
        ->orderBy('id', 'DESC')
        ->first();

        if(is_null($refund)){
            return false;
        }
        
        $response = Http::withToken(env('MYFATOORAH_TOKEN'))
        ->post(env('MYFATOORAH_URL') . "GetRefundStatus", 
            [
                "Key" => $refund->invoice_id,
                "KeyType" => "InvoiceId"
            ]
        );
        
        if ($response->successful()){
            
            $data = $response->json();
            
            if(isset($data['IsSuccess']) && $data['IsSuccess'] == true){
                if(!empty($data['Data']['RefundStatusResult'][0]['RefundStatus'])){
                    
                    $refundStatus = strtolower($data['Data']['RefundStatusResult'][0]['RefundStatus']);
                    
                    if($refundStatus == "refunded"){
                        
                        $booking = Bookings::find($refund->booking_id);
                        if(is_null($booking)){
                            return false;
                        }
                        
                        BookingCancellation::create([
                            'booking_id' => $booking->id,
                            'type' => 'bookingengine',
                            'reason' => $refund->reason,
                            'sub_reason' => $refund->reason_detail,
                            'message_to_guest' => '',
                            'message_to_airbnb' => '',
                            'cancel_by' => Auth::user()->id,
                        ]);
                        
                        $listing = Listing::where('id', $booking->listing_id)->first();
                        
                        if(!is_null($listing)){
                            Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$booking->booking_date_start, $booking->booking_date_end])
                            ->update(
                                ['availability' => 1]
                            );
                        }
                        
                        Bookings::where('id', $booking->id)->update([
                            'reason' => $refund->reason . ' - ' . $refund->reason_detail,
                            'booking_status' => 'cancelled'
                        ]);
                        
                        $refund->refund_status = 'Refunded';
                        $refund->save();
                    }
                    
                    if($refundStatus == "pending" || $refundStatus == "canceled"){
                        //
                    }
                    
                }
            }
            
        }
    }
    
    public function calculateRefundAmount($booking_id, $total_price=0) {
        
        $booking = Bookings::where('id', $booking_id)
            ->where('booking_sources', 'booking_engine')
            ->first();

        if(is_null($booking)){
            return 0;
        }
        
        $totalAmount = $booking->total_price;
        
        if(!empty($total_price)){
            $totalAmount = $total_price;
        }

        // $bookingDate = Carbon::parse($bookingDate);
        $now = Carbon::now();
        $checkInDate = Carbon::parse($booking->booking_date_start);
        $checkOutDate = Carbon::parse($booking->booking_date_end);
        
        $total_nights = $checkInDate->diffInDays($checkOutDate);

        $hoursToCheckIn = $now->diffInHours($checkInDate, false);

        // Same Day Cancellation
        if ($hoursToCheckIn <= 0) {
            
            // Same Day Cancellation for Long Stays
            if($total_nights > 1) {
                // Long Stay Cancellation
                $firstDayCharge = $totalAmount / $total_nights;
                $remainingAmount = $totalAmount - $firstDayCharge;
                $chargeForRemainingDays = $remainingAmount * 0.30;
                return $totalAmount - $firstDayCharge - $chargeForRemainingDays;
            } else {
                // Normal Same Day Cancellation
                return $totalAmount * 0.70; // 30% will be deducted
            }
        }

        // Cancellation 48 Hours Before Check-In
        if ($hoursToCheckIn >= 48) {
            return $totalAmount; // Full refund
        }

        // Cancellation within 48 Hours of Arrival
        if ($hoursToCheckIn > 0 && $hoursToCheckIn < 48) {
            return $totalAmount * 0.70; // 30% will be deducted
        }

        return 0;
    }
}









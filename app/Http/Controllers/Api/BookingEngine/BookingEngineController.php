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
    BeReview,
    ChurnedProperty,
    Listing,
    Review,
    Guests,
    Channels,
    AirbnbImage,
    Calender,
    Bookings,
    RoomType,
    RatePlan,
    BeOrder,
    BookingLead,
    User,
    BookingCancellation,
    Voucher,
    VoucherRedeemed
};

use Illuminate\Support\Facades\{
    DB
};

use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

use App\Services\FirebaseService;

class BookingEngineController extends Controller
{
    // dynamic property_type, guest
    // discounts calculate, total_price get after discount, discount calculate
    
    private $channelId;
    private $apiKey;
    private $languages = ['ar'];
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
            
            if(!empty($guest->dp)){
                
                if (strpos($guest->dp, "https://lh3.googleusercontent.com") === false) {
                    $guest->dp = url('/storage').'/'.$guest->dp;
                }
            }
            
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
        
        if(!empty($request->dob)){
            $data['dob'] = $request->dob;
        }
        
        if(!empty($request->picture)){
            $data['dp'] = $request->picture;
        }
        
        $guest_exists = Guests::where('email', $request->email)->exists();
        if($guest_exists){
            Guests::where('email', $request->email)->update($data);
        } else{
            $guest = Guests::create($data);
        }
        
        $guest = Guests::where('email', $request->email)->first();
        
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

    public function get_5_star_apartments(Request $request){
        try{
            
            $data = [];

            $lang = in_array($request->lang, $this->languages) ? $request->lang : '';
            
            $manual_listings = Listing::whereNotNull('be_listing_name')
            ->where(['is_sync' => 'sync_all', 'is_churned' => 0, 'is_manual'=>1])
            ->orderBy('id', 'desc')
            ->get();
            
            $start_date = $end_date = date('Y-m-d');
            
            foreach($manual_listings as $ml){
                
                $images = $ml->airbnbImages()->pluck('url')->toArray();
                
                if(empty($images)){
                    continue;
                }
                
                $mlprice = $ml->calendars()->whereBetween('calenders.calender_date', [$start_date, $end_date])->sum('rate');
                
                $currency = $this->get_logged_in_user_currency();
                
                $mlprice = $this->calculate_price_with_exchange_rate($mlprice, $currency);


                $dlisting_name = $ml->be_listing_name;
                $dproperty_type = $ml->property_type;
                $drating = !empty($overall) && !empty($reviews) ? $overall / count($reviews) : 0;

                if(!empty($lang)){
                    $dlisting_name = $listing->{$lang . '_listing_name'} ?? $dlisting_name;
                    $dproperty_type = $listing->{$lang . '_property_type'} ?? $dproperty_type;
                    $drating = $this->convertToArabicNumber($drating, $lang);
                }
                
                $data[] = [
                    'listing_id' =>$ml->listing_id,
                    'title' =>$dlisting_name,
                    'property_type' =>$dproperty_type,
                    'guest_review'=>['guest_name'=>'Guest','review'=>''],
                    'price' =>$this->convertToArabicNumber($mlprice, $lang),
                    'currency' =>$currency,
                    'ratings' => $drating,
                    'is_favorite' => !empty($guest_listing_ids) && in_array($ml->listing_id, $guest_listing_ids) ? 1 : 0,
                    'images' => $images
                ];
            }
            
            // print_r($data);die;

            $listings = Listing::with('setting', 'airbnbImages')
            ->whereNotNull('be_listing_name')
            ->where(['is_sync' => 'sync_all', 'is_churned' => 0, 'is_manual'=>0])
            ->orderBy('id', 'desc')
            // ->limit(10)
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
                
                // $currency = $this->get_logged_in_user_currency();
                
                $price = $this->calculate_price_with_exchange_rate($price, $currency);

                $dlisting_name = $listing->be_listing_name;
                $dproperty_type = $listing->property_type;
                $drating = !empty($overall) && !empty($reviews) ? $overall / count($reviews) : 0;

                if(!empty($lang)){
                    $dlisting_name = $listing->{$lang . '_listing_name'} ?? $dlisting_name;
                    $dproperty_type = $listing->{$lang . '_property_type'} ?? $dproperty_type;
                    $drating = $this->convertToArabicNumber($drating, $lang);
                }

                $data[] = [
                    'listing_id' =>$listing->listing_id,
                    'title' =>$dlisting_name, //$listing_title,
                    'property_type' =>$dproperty_type,
                    // 'short_name' =>'',//$short_name,
                    'guest_review'=>$guest_review,
                    'price' =>$this->convertToArabicNumber($price, $lang),
                    'currency' =>$currency,
                    'ratings' => $drating,
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

            $lang = in_array($request->lang, $this->languages) ? $request->lang : '';
            
            // if(!$request->filled('district')){
            //     return response()->json(['success'=>0, 'response'=>'District is required'], 400);
            // }
            
            $listings = Listing::with('setting', 'airbnbImages')
                // ->whereNotNull('be_listing_name')
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
                if(strtolower($request->district) == "riyadh" || $request->district == "الرياض"){
                    $listings->where('city_name', "riyadh");
                } else{
                    $listings->where('district', $request->district)->orWhere('ar_district', $request->district);
                }
            }
            
            if($request->filled('property_type')){
                $property_types = json_decode($request->property_type);
                if(!empty($property_types)){
                    $listings->whereIn('property_type', $property_types)->orWhereIn('ar_property_type', $property_types);
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
                    continue;
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
                
                $channel = Channels::where('id', $listing->channel_id)->first();
                if(empty($images) && !empty($channel->ch_channel_id)){
                    // continue;
                    
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
                    $updatedBookingDateEnd = Carbon::parse($end_date)->toDateString();
                }
                
                $total = $listing->calendars()->whereBetween('calenders.calender_date', [$start_date, $updatedBookingDateEnd])->sum('rate');
                
                // $total = empty($total) && !is_null($listing->setting) ? $listing->setting->default_daily_price : $total;
                
                $start = Carbon::parse($start_date);
                $end = Carbon::parse($end_date);
                
                $total_nights = $start->diffInDays($end);
                
                $total_nights = empty($total_nights) ? 1 : $total_nights;

                $per_night_price = !empty($total) && !empty($total_nights) ? round($total / $total_nights) : 0;
                
                if($request->filled('min_price') && $request->filled('max_price')){
                    
                    $min_price = (int) $request->min_price;
                    $max_price = (int) $request->max_price;
                    
                    // echo $per_night_price . ' = ' .$min_price . ' = ' . $max_price;die;
                    
                    if($per_night_price >= $min_price && $per_night_price <= $max_price){
                        //
                    } else{
                        continue;
                    }
                }
                
                if(!empty($request->amenities)){
                    
                    // if(empty($listing->amenities->amenities_json)){
                    //     continue;
                    // }
                    
                    $amenities_not_exists = false;
                    
                    $am_arr = json_decode($request->amenities);
                    
                    $amenities_arr = [];
                    $listing_amenities = DB::table('listing_amenities')->where(['listing_id' => $listing->listing_id])->first();
                    if (!is_null($listing_amenities) && !empty($listing_amenities->amenities_json)) {
                        
                        $amenities = json_decode($listing_amenities->amenities_json);
                        
                        foreach($amenities as $aminity_key => $aminity){
                            if(!in_array($aminity_key, $amenities_arr)){
                                $amenities_arr[] = $aminity_key;
                            }
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
                
                
                
                $ratings = !empty($overall) && !empty($reviews) ? round($overall / count($reviews)) : 0;
                
                if($request->filled('ratings')){
                    $ratings_arr = json_decode($request->ratings);
                    
                    if(!empty($ratings_arr) && !in_array($ratings, $ratings_arr)){
                        continue;
                    }
                }
                
                $currency = $this->get_logged_in_user_currency();
                
                $total = $this->calculate_price_with_exchange_rate($total, $currency);
                
                $per_night_price = $this->calculate_price_with_exchange_rate($per_night_price, $currency);
                
                // $lang

                $dlisting_name = !empty($listing->be_listing_name) ? $listing->be_listing_name : $listing_name;
                $dproperty_type = $listing->property_type;
                $dcity_name = $listing->city_name;
                $ddistrict = $listing->district;

                if(!empty($lang)){
                    $dlisting_name = $listing->{$lang . '_listing_name'} ?? $dlisting_name;
                    $dproperty_type = $listing->{$lang . '_property_type'} ?? $dproperty_type;
                    $dcity_name = $listing->{$lang . '_city_name'} ?? $dcity_name;
                    $ddistrict = $listing->{$lang . '_district'} ?? $ddistrict;
                }

                // working

                $data[] = [
                    'listing_id' => $listing->listing_id,
                    'listing_name' => $dlisting_name,
                    'property_type' => $dproperty_type,
                    'ratings' => $this->convertToArabicNumber($ratings, $lang),
                    'ratings_count' => $this->convertToArabicNumber(count($reviews), $lang),
                    'guest_review'=>$guest_review,
                    'bedrooms' => $this->convertToArabicNumber($listing->bedrooms, $lang),
                    'beds' => $this->convertToArabicNumber($listing->beds, $lang),
                    'bathrooms' => $this->convertToArabicNumber($listing->bathrooms, $lang),
                    'city_name' => $dcity_name,
                    'district' => $ddistrict,
                    'cleaning_fee' => $this->convertToArabicNumber($listing->cleaning_fee, $lang),
                    'discounts' => $this->convertToArabicNumber($listing->discounts, $lang),
                    'tax' => $this->convertToArabicNumber($listing->tax, $lang),
                    'total' => $this->convertToArabicNumber((int) $total, $lang),
                    
                    'min_price' => $this->convertToArabicNumber((int) $request->min_price, $lang),
                    'max_price' => $this->convertToArabicNumber((int) $request->max_price, $lang),
                    
                    'per_night_price' => $this->convertToArabicNumber((int) $per_night_price, $lang),
                    'total_nights' => $this->convertToArabicNumber((int) $total_nights, $lang),
                    'currency' => $currency,
                    'is_favorite' => !empty($guest_listing_ids) && in_array($listing->listing_id, $guest_listing_ids) ? 1 : 0,
                    'images' => $images
                ];
            }

            if(!empty($data)){
                return response()->json(['success'=>1, 'response'=>$data, 'listings_count'=>count($data), 'ar_listings_count'=>$this->convertToArabicNumber(count($data), 'ar')], 200);
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
                    // continue;
                }
                
                if(is_null($listing->channel) || !is_null($listing->channel->connection_type)){
                    // continue;
                }
                
                $overall = 0;
                foreach($reviews as $review){
                    $overall += $review->overall_score / 2;
                }
                
                if(empty($overall)){
                    // continue;
                }

                // print_r($listing);die;
                
                $jsn = !empty($listing->listing_json) ? json_decode($listing->listing_json) : null;
                $listing_name = !empty($jsn->title) ? $jsn->title : '';
                
                if(empty($start_date) && empty($end_date)){
                    $start_date = $end_date = date('Y-m-d');
                    
                    $updatedBookingDateEnd = $end_date;
                } else {
                    $updatedBookingDateEnd = Carbon::parse($end_date)->toDateString();
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
                
                $listing_amenities = DB::table('listing_amenities')->where(['listing_id' => $listing->listing_id])->first();
                if (!is_null($listing_amenities) && !empty($listing_amenities->amenities_json)) {
                    
                    $amenities = json_decode($listing_amenities->amenities_json);
                    
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
            
            $price = ['min'=>$min_value, 'ar_min'=>$this->convertToArabicNumber($min_value, 'ar'), 'max'=>$max_value, 'ar_max'=>$this->convertToArabicNumber($max_value, 'ar')];
            
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
    
    public function fetchListingDetails($listing_id, Request $request) //: mixed
    {
        
        try
        {
            $lang = in_array($request->lang, $this->languages) ? $request->lang : '';

            // print_r($request->lang);die;


        $guest_listing_ids = [];
        $guest = Auth::guard('guest')->user();
        if($guest && !empty($guest->id)){
            $guest_listing_ids = DB::table('guest_favorites')->where(['user_id' => $guest->id])->pluck('listing_id')->toArray();
        }
            
        $listing = Listing::where('listing_id', $listing_id)->first();
        
        // print_r($listing);die;
        
        $listing_amenities = DB::table('listing_amenities')->where(['listing_id' => $listing->listing_id])->first();
        if (!is_null($listing_amenities) && !empty($listing_amenities->amenities_json)) {
            $dbamenities = json_decode($listing_amenities->amenities_json, true); 
        }
        
        
    
        if (!empty($dbamenities)) {
            $filteredAmenities = array_keys(array_filter($dbamenities, function ($item) {
                return isset($item['is_present']) && $item['is_present'] === true;
            }));
        }
        
        $apartment = [];

        $dlisting_name = $listing->be_listing_name;
        $dproperty_about = $listing->property_about;
        $dcity_description = $listing->district." is the vibrant heart of Riyadh, renowned for its modern skyline, bustling commercial activity, and cosmopolitan atmosphere. As the city's financial and business hub, it features iconic landmarks such as the Kingdom Tower and Al Faisaliah Tower, which define its striking urban landscape. The district offers a seamless blend of luxury and convenience, with upscale shopping malls, high-end hotels, and a diverse array of international restaurants and cafes. Its central location and dynamic energy make ".$listing->district." a prime destination for both residents and visitors, epitomizing the contemporary charm and ambition of Riyadh.";

        if(!empty($lang)){
            $dlisting_name = $listing->{$lang . '_listing_name'} ?? $dlisting_name;
            $dproperty_about = $listing->{$lang . '_property_about'} ?? $dproperty_about;

            $dcity_description = $dcity_description; //'Arabic Version';
        }

        $current_year = Carbon::now()->year;
                
        $created_at = $listing->created_at;
        $created_year = Carbon::parse($created_at)->year;
        
        $years_hosting = $current_year - $created_year;

        $ddistrict = $listing->district;

        if($lang == 'ar'){
            $apartment['years_hosting'] = $years_hosting > 1 ?  "استضافة لعدة " . $this->convertToArabicNumber($years_hosting, $lang) : "استضافة لمدة سنة واحدة";

            $apartment['self_checkin'] = $listing->is_self_check_in == 1 ? "نعم" : "لا";

            $ddistrict = $listing->{$lang . '_district'} ?? null;
        }

        if($listing->is_manual == 0) {
            // dd($listing);die;
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
                
                        
                // if (!is_null($listing->amenities) && !empty($listing->amenities->amenities_json)) {
                //     $dbamenities = json_decode($listing->amenities->amenities_json, true); 
                // }
            
                // if (!empty($dbamenities)) {
                //     $filteredAmenities = array_keys(array_filter($dbamenities, function ($item) {
                //         return isset($item['is_present']) && $item['is_present'] === true;
                //     }));
                // }
                

                // $this->convertToArabicNumber((int) $per_night_price, $lang)

                $apartment['name'] = $dlisting_name; //$response['data']['listing']['listing_nickname'];
                $apartment['apartment_description'] = $dproperty_about; //"Al Olaya is the vibrant heart of Riyadh, renowned for its modern skyline, bustling commercial activity, and cosmopolitan atmosphere. As the city's financial and business hub, it features iconic landmarks such as the Kingdom Tower and Al Faisaliah Tower, which define its striking urban landscape. The district offers a seamless blend of luxury and convenience, with upscale shopping malls, high-end hotels, and a diverse array of international restaurants and cafes. Its central location and dynamic energy make Al Olaya a prime destination for both residents and visitors, epitomizing the contemporary charm and ambition of Riyadh.";
                $apartment['images'] = $response['data']['listing']["images"] ?? '';
                $apartment['amenities'] = !empty($filteredAmenities) ? $filteredAmenities : [];
                $apartment['rooms'] = $response['data']['listing']["rooms"] ?? '';
                $apartment['reviewCount'] = $this->convertToArabicNumber($reviewCount, $lang);
                $apartment['averageScore'] = $this->convertToArabicNumber($averageScore, $lang);
                $apartment['reviews'] = $this->convertToArabicNumber($reviews, $lang);
                $apartment['city_description'] = $dcity_description;
                $apartment['map'] = $listing->google_map;
                $apartment['checkin_time'] = $response['data']['listing']['booking_settings']['check_in_time_end'] ?? '';
                $apartment['checkout_time'] = $response['data']['listing']['booking_settings']['check_out_time'] ?? '';
                $apartment['cancellation_policy'] = $response['data']['listing']['booking_settings']['cancellation_policy_settings']['cancellation_policy_category'] ?? '';

                if(empty($apartment['self_checkin'])){
                    $apartment['self_checkin'] = $listing->is_self_check_in == 1 ? "Yes" : "No"; //$response['data']['listing']['check_in_option']['category'];
                }
                
                $apartment['district'] = $ddistrict;
                $apartment['lat'] = $response['data']['listing']['lat'] ?? '';
                $apartment['lng'] = $response['data']['listing']['lng'] ?? '';

                $apartment['beds'] = (int) $listing->beds;
                $apartment['bedrooms'] = (int) $listing->bedrooms;
                $apartment['bathrooms'] = (int) $listing->bathrooms;

                $apartment['ar_beds'] = $this->convertToArabicNumber((int) $listing->beds, $lang);
                $apartment['ar_bedrooms'] = $this->convertToArabicNumber((int) $listing->bedrooms, $lang);
                $apartment['ar_bathrooms'] = $this->convertToArabicNumber((int) $listing->bathrooms, $lang);
                
                $apartment['cleaning_fee'] = (int) $listing->cleaning_fee;
                $apartment['discounts'] = (int) $listing->discounts;
                $apartment['tax'] = (int) $listing->tax;

                $apartment['ar_cleaning_fee'] = $this->convertToArabicNumber((int) $listing->cleaning_fee, 'ar');
                $apartment['ar_discounts'] = $this->convertToArabicNumber((int) $listing->discounts, 'ar');
                $apartment['ar_tax'] = $this->convertToArabicNumber((int) $listing->tax, 'ar');

                $apartment['is_long_term'] = $listing->is_long_term;
                $apartment['minimum_days_stay'] = empty($listing->minimum_days_stay) ? 1 : $listing->minimum_days_stay;
                
                
                
                if(empty($apartment['years_hosting'])){
                    $apartment['years_hosting'] = $years_hosting > 1 ? $years_hosting . " Years Hosting" : "1 Year Hosting";
                }

                $apartment['is_favorite'] = !empty($guest_listing_ids) && in_array($listing->listing_id, $guest_listing_ids) ? 1 : 0;
                
                foreach ($apartment['rooms'] as &$room) {
                    $room['images'] = array_values(array_filter($apartment['images'], function ($image) use ($room) {
                        return isset($image['room_id']) && $image['room_id'] === $room['id'];
                    }));
                }

                $apartment['listing_id'] = $listing_id;

                return $apartment;
                // return $response['data']['listing'];
            } else {
                return $response->body();
            }
        }
        else {
            $images = AirbnbImage::where('listing_id', $listing->listing_id)->get();

// Get one image per distinct category
            $roomimages = AirbnbImage::where('listing_id', $listing->listing_id)->where('category', '!=','other images')->where('category', '!=','cover image')->get();

$distinctImages = $roomimages->unique('category')->values();
            // dd(count($images->toArray()),count($distinctImages));
            $room = [];
            foreach($distinctImages as $key=>$image){
                $room[$key]['beds'] = [];
                 $room[$key]['is_private'] = true;
                 $room[$key]['listing_id'] = $listing->listing_id;
                 $room[$key]['room_number'] = 1;
                 $room[$key]['room_type'] = $image->category;
                 $room[$key]['images'][0]['extra_medium_url'] = $image->url;
                 $room[$key]['images'][0]['small_url'] = $image->url;
                 $room[$key]['images'][0]['thumbnail_url'] = $image->url;
            }
                            // dd($room);

            $transformedImages = $images->map(function ($image, $index) use ($listing) {
                $url = $image->url;
                $sortOrder = $image->sort_order ?? ($index + 1); // fallback if not in DB
            
                return [
                    "caption" => "",
                    "category" => "ROOM",
                    "extra_medium_url" => $url . "?aki_policy=x_medium",
                    "id" => "{$listing->listing_id}_{$sortOrder}",
                    "listing_id" => (string) $listing->listing_id,
                    "room_id" => (string) ($image->room_id ?? '0'),
                    "small_url" => $url . "?aki_policy=small",
                    "sort_order" => $sortOrder,
                    "thumbnail_url" => $url . "?aki_policy=x_small",
                ];
            });
                            // dd($transformedImages);
                            

                
                
                // return $filteredAmenities;die;

                $apartment['name'] = $dlisting_name; //$listing->be_listing_name; //$response['data']['listing']['listing_nickname'];
                $apartment['apartment_description'] = $dproperty_about; //$listing->property_about; //"Al Olaya is the vibrant heart of Riyadh, renowned for its modern skyline, bustling commercial activity, and cosmopolitan atmosphere. As the city's financial and business hub, it features iconic landmarks such as the Kingdom Tower and Al Faisaliah Tower, which define its striking urban landscape. The district offers a seamless blend of luxury and convenience, with upscale shopping malls, high-end hotels, and a diverse array of international restaurants and cafes. Its central location and dynamic energy make Al Olaya a prime destination for both residents and visitors, epitomizing the contemporary charm and ambition of Riyadh.";
                $apartment['images'] = $transformedImages;
                $apartment['amenities'] = !empty($filteredAmenities) ? $filteredAmenities : [];
                $apartment['rooms'] = $room;
                $apartment['reviewCount'] = $this->convertToArabicNumber(1, $lang);
                $apartment['averageScore'] = $this->convertToArabicNumber(1, $lang);
                $apartment['reviews'] = null;
                $apartment['city_description'] = $dcity_description; //$listing->district." is the vibrant heart of Riyadh, renowned for its modern skyline, bustling commercial activity, and cosmopolitan atmosphere. As the city's financial and business hub, it features iconic landmarks such as the Kingdom Tower and Al Faisaliah Tower, which define its striking urban landscape. The district offers a seamless blend of luxury and convenience, with upscale shopping malls, high-end hotels, and a diverse array of international restaurants and cafes. Its central location and dynamic energy make ".$listing->district." a prime destination for both residents and visitors, epitomizing the contemporary charm and ambition of Riyadh.";
                $apartment['map'] = $listing->google_map;
                $apartment['checkin_time'] = $listing->checkin_time;
                $apartment['checkout_time'] = $listing->checkout_time;
                $apartment['cancellation_policy'] = 'Flexible';

                if(empty($apartment['self_checkin'])){
                    $apartment['self_checkin'] = $listing->is_self_check_in == 1 ? "Yes" : "No"; //$response['data']['listing']['check_in_option']['category'];
                }

                $apartment['district'] = $ddistrict;
                $apartment['lat'] = $listing->latitude;
                $apartment['lng'] = $listing->longitude;

                $apartment['beds'] = (int) $listing->beds;
                $apartment['bedrooms'] = (int) $listing->bedrooms;
                $apartment['bathrooms'] = (int) $listing->bathrooms;

                $apartment['ar_beds'] = $this->convertToArabicNumber((int) $listing->beds, $lang);
                $apartment['ar_bedrooms'] = $this->convertToArabicNumber((int) $listing->bedrooms, $lang);
                $apartment['ar_bathrooms'] = $this->convertToArabicNumber((int) $listing->bathrooms, $lang);

                $apartment['cleaning_fee'] = (int) $listing->cleaning_fee;
                $apartment['discounts'] = (int) $listing->discounts;
                $apartment['tax'] = (int) $listing->tax;

                $apartment['ar_cleaning_fee'] = $this->convertToArabicNumber((int) $listing->cleaning_fee, 'ar');
                $apartment['ar_discounts'] = $this->convertToArabicNumber((int) $listing->discounts, 'ar');
                $apartment['ar_tax'] = $this->convertToArabicNumber((int) $listing->tax, 'ar');

                $apartment['is_long_term'] = $listing->is_long_term;
                $apartment['minimum_days_stay'] = empty($listing->minimum_days_stay) ? 1 : $listing->minimum_days_stay;

                if(empty($apartment['years_hosting'])){
                    $apartment['years_hosting'] = $years_hosting > 1 ? $years_hosting . " Years Hosting" : "1 Year Hosting";
                }

                $apartment['is_favorite'] = !empty($guest_listing_ids) && in_array($listing->listing_id, $guest_listing_ids) ? 1 : 0;

                $apartment['listing_id'] = $listing_id;
                
                // foreach ($apartment['rooms'] as &$room) {
                //     $room['images'] = array_values(array_filter($apartment['images'], function ($image) use ($room) {
                //         return isset($image['room_id']) && $image['room_id'] === $room['id'];
                //     }));
                // }
                return $apartment;
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
        if(!empty($_GET['currency'])){
            $currency = $_GET['currency'];
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

        return [
            'total' => round($sum),
            'adr' => $average,
            'guest_currency' => $currency,
            'total_nights'=>$total_nights,
            'ar_total' => $this->convertToArabicNumber(round($sum), 'ar'),
            'ar_adr' => $this->convertToArabicNumber($average, 'ar'),
            'ar_total_nights'=>$this->convertToArabicNumber($total_nights, 'ar'),
        ];
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
            
            if(!empty($guest->dp)){
                
                if (strpos($guest->dp, "https://lh3.googleusercontent.com") === false) {
                    $guest->dp = url('/storage').'/'.$guest->dp;
                }
            }
            
            return response()->json([
                'success' => 1,
                'message' => 'Update succesfully',
                'user' => $guest
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
                
                if(!empty($guest->dp)){
                
                    if (strpos($guest->dp, "https://lh3.googleusercontent.com") === false) {
                        $guest->dp = url('/storage').'/'.$guest->dp;
                    }
                }
            
                
                return response()->json([
                    'success' => 1,
                    'message' => 'Update succesfully',
                    'user' => $guest
                ]);
            }
        }
        
        return response()->json([
            'success' => 0
        ]);
    }
    
    public function getGuestBookings(Request $request){
        
        $guest = Auth::guard('guest')->user();

        $lang = in_array($request->lang, $this->languages) ? $request->lang : '';
        
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
            
            $checkout_arr = [];
            $formattedBookings = [];
            foreach ($bookings as $month => $bookingData) {
                
                foreach($bookingData as $bk_key => $bkd){
                    if(!empty($bkd['listing_id'])){
                        
                        if(!empty($bookingData[$bk_key]['id']) && !empty($bookingData[$bk_key]['booking_date_start'])){
                            
                            $beReview = BeReview::where('booking_id', $bookingData[$bk_key]['id'])->first();
                            if(is_null($beReview)){
                                $checkout_arr[] = [
                                    'booking_id' => $bookingData[$bk_key]['id'],
                                    'checkin_date' => $bookingData[$bk_key]['booking_date_start'],
                                ];
                            }
                        }
                        
                        $listing = Listing::find($bkd['listing_id']);
                        
                        $images = $listing->airbnbImages()->pluck('url')->toArray();


                        //////////////////
                        $dlisting_name = $listing->be_listing_name;
                        $dproperty_type = $listing->property_type;
                        $ddistrict = $listing->district;

                        if(!empty($lang)){
                            $dlisting_name = $listing->{$lang . '_listing_name'} ?? $dlisting_name;
                            $dproperty_type = $listing->{$lang . '_property_type'} ?? $dproperty_type;
                            $ddistrict = $listing->{$lang . '_district'} ?? $ddistrict;
                        }
                        //////////////////
                
                        if(!is_null($listing)){
                            $bookingData[$bk_key]['listing_id'] = $listing->listing_id;
                            $bookingData[$bk_key]['property_type'] = $dproperty_type;
                            $bookingData[$bk_key]['be_listing_name'] = $dlisting_name;
                            
                            $bookingData[$bk_key]['beds'] = $listing->beds;
                            $bookingData[$bk_key]['bathrooms'] = $listing->bathrooms;

                            $bookingData[$bk_key]['ar_beds'] = $this->convertToArabicNumber($listing->beds, $lang);
                            $bookingData[$bk_key]['ar_bathrooms'] = $this->convertToArabicNumber($listing->bathrooms, $lang);

                            $bookingData[$bk_key]['district'] = $ddistrict;
                            $bookingData[$bk_key]['city_name'] = !empty($lang) ? 'الرياض' : $listing->city_name;
                            $bookingData[$bk_key]['images'] = !empty($images) ? $images[0] : [];

                            $bookingData[$bk_key]['adult'] = $bookingData[$bk_key]['adult'];
                            $bookingData[$bk_key]['children'] = $bookingData[$bk_key]['children'];
                            $bookingData[$bk_key]['rooms'] = $bookingData[$bk_key]['rooms'];
                            $bookingData[$bk_key]['rating'] = $bookingData[$bk_key]['rating'];

                            $bookingData[$bk_key]['ar_adult'] = $this->convertToArabicNumber($bookingData[$bk_key]['adult'], $lang);
                            $bookingData[$bk_key]['ar_children'] = $this->convertToArabicNumber($bookingData[$bk_key]['children'], $lang);
                            $bookingData[$bk_key]['ar_rooms'] = $this->convertToArabicNumber($bookingData[$bk_key]['rooms'], $lang);
                            $bookingData[$bk_key]['ar_rating'] = $this->convertToArabicNumber($bookingData[$bk_key]['rating'], $lang);

                            $bookingData[$bk_key]['service_fee'] = $bookingData[$bk_key]['service_fee'];
                            $bookingData[$bk_key]['cleaning_fee'] = $bookingData[$bk_key]['cleaning_fee'];
                            $bookingData[$bk_key]['per_night_price'] = $bookingData[$bk_key]['per_night_price'];
                            $bookingData[$bk_key]['total_price'] = $bookingData[$bk_key]['total_price'];
                            $bookingData[$bk_key]['modified_charges'] = $bookingData[$bk_key]['modified_charges'];
                            $bookingData[$bk_key]['ota_commission'] = $bookingData[$bk_key]['ota_commission'];

                            $bookingData[$bk_key]['ar_service_fee'] = $this->convertToArabicNumber($bookingData[$bk_key]['service_fee'], $lang);
                            $bookingData[$bk_key]['ar_cleaning_fee'] = $this->convertToArabicNumber($bookingData[$bk_key]['cleaning_fee'], $lang);
                            $bookingData[$bk_key]['ar_per_night_price'] = $this->convertToArabicNumber($bookingData[$bk_key]['per_night_price'], $lang);
                            $bookingData[$bk_key]['ar_total_price'] = $this->convertToArabicNumber($bookingData[$bk_key]['total_price'], $lang);
                            $bookingData[$bk_key]['ar_modified_charges'] = $this->convertToArabicNumber($bookingData[$bk_key]['modified_charges'], $lang);
                            $bookingData[$bk_key]['ar_ota_commission'] = $this->convertToArabicNumber($bookingData[$bk_key]['ota_commission'], $lang);
                        }
                        
                        $beReviewExists = BeReview::where('booking_id', $bookingData[$bk_key]['id'])->exists();
                        
                        $bookingData[$bk_key]['is_review_submitted'] = $beReviewExists;
                    }
                }
                
                $formattedBookings['bookings'][$month] = $bookingData;
            }
            
            if(empty($formattedBookings)){
                return response()->json(['error'=>'Booking not found']);
            }
            
            $formattedBookings['review'] = null;
            if(!empty($checkout_arr)){
                
                $today = date('Y-m-d');
                usort($checkout_arr, function ($a, $b) {
                    return strtotime($b['checkin_date']) - strtotime($a['checkin_date']);
                });
                
                if(!empty($checkout_arr[0])){
                    $formattedBookings['review'] = $checkout_arr[0];
                }
            }
            
            return $formattedBookings;
        }
        
        return response()->json(['error'=>'Guest not logged in']);
    }
    
    public function getGuestBookingDetail($booking_id, Request $request){
        
        $data = [];

        $lang = in_array($request->lang, $this->languages) ? $request->lang : '';
        
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

        $years_hosting_txt = $years_hosting > 1 ? $years_hosting . " Years Hosting" : "1 Year Hosting";

        $dlisting_name = $listing->be_listing_name;
        $dproperty_about = $listing->property_about;

        $dproperty_type = $listing->property_type;

        $ddistrict = $listing->district;
        

        if($lang == 'ar'){
            
            $dproperty_about = $listing->{$lang . '_property_about'} ?? null;

            $dlisting_name = $listing->{$lang . '_listing_name'} ?? $dlisting_name;

            $dproperty_type = $listing->{$lang . '_property_type'} ?? $dproperty_type;

            $ddistrict = $listing->{$lang . '_district'} ?? $ddistrict;

            $years_hosting_txt = $years_hosting > 1 ?  "استضافة لعدة " . $this->convertToArabicNumber($years_hosting, $lang) : "استضافة لمدة سنة واحدة";
        }
        
        $images = $listing->airbnbImages()->pluck('url')->toArray();
        
        $currency = $this->get_logged_in_user_currency();
        
        $beReviewExists = BeReview::where('booking_id', $booking->id)->exists();

        $booking_status_arr = ['cancelled' => 'تم الإلغاء', 'confirmed' => 'مؤكد'];
        
        $data = [
            'booking_id' => $booking->id,
            'adult' => $this->convertToArabicNumber($booking->adult, $lang),
            'children' => $this->convertToArabicNumber($booking->children, $lang),
            'infants' => $this->convertToArabicNumber(0, $lang),
            'pets' => $this->convertToArabicNumber(0, $lang),
            'booking_date_start' => $booking->booking_date_start,
            'booking_date_end' => $booking->booking_date_end,
            'total_nights' => $this->convertToArabicNumber((int) $total_nights, $lang),

            'total_price' => $this->calculate_price_with_exchange_rate($booking->total_price, $currency),
            'per_night_price' => $this->calculate_price_with_exchange_rate($booking->per_night_price, $currency),
            'cleaning_fee' => $this->calculate_price_with_exchange_rate($booking->cleaning_fee, $currency),
            'service_fee' => $this->calculate_price_with_exchange_rate($booking->service_fee, $currency),
            'tax' => $this->calculate_price_with_exchange_rate($listing->tax, $currency),
            'discounts' => $this->calculate_price_with_exchange_rate($listing->discounts, $currency),

            'ar_total_price' => $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($booking->total_price, $currency), 'ar'),
            'ar_per_night_price' => $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($booking->per_night_price, $currency), 'ar'),
            'ar_cleaning_fee' => $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($booking->cleaning_fee, $currency), 'ar'),
            'ar_service_fee' => $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($booking->service_fee, $currency), 'ar'),
            'ar_tax' => $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($listing->tax, $currency), 'ar'),
            'ar_discounts' => $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($listing->discounts, $currency), 'ar'),
            
            'listing_id' => $listing->listing_id,
            'be_listing_name' => $dlisting_name,
            'property_type' => $dproperty_type,
            'district' => $ddistrict,
            'average_score' => $this->convertToArabicNumber($averageScore, $lang),
            'review_count' => $this->convertToArabicNumber($reviewCount, $lang),
            'years_hosting' => $years_hosting_txt,
            'google_map' => $listing->google_map,
            'property_about' => $dproperty_about,
            'bedrooms' => $this->convertToArabicNumber($listing->bedrooms, $lang),
            'beds' => $this->convertToArabicNumber($listing->beds, $lang),
            'bathrooms' => $this->convertToArabicNumber($listing->bathrooms, $lang),
            'booking_status' => !empty($lang) && !empty($booking_status_arr[$booking->booking_status]) ? $booking_status_arr[$booking->booking_status] : $booking->booking_status,
            'reservation_code' => $booking->reservation_code,
            'payment_method' => $booking->payment_method,
            'is_review_submitted' => $beReviewExists,
            'images' => !empty($images) ? $images : [],
            'amenities' => !empty($listing->amenities->amenities_json) ? json_decode($listing->amenities->amenities_json) : [],
        ];
        
        return response()->json($data);
    }
    
    public function getPlaces(Request $request){
        
        $data = $districts = $city_names = [];

        $lang = in_array($request->lang, $this->languages) ? $request->lang : '';

        $slug = '';
        if(!empty($lang)){
            $slug = $lang.'_';

            $city_names[] = 'الرياض';
        }
        
        $districts = Listing::whereNotNull($slug.'district')->groupBy($slug.'district')->pluck($slug.'district')->toArray();
        
        if(empty($city_names)){
            $city_names = Listing::whereNotNull('city_name')->groupBy('city_name')->pluck('city_name')->toArray();
        }
        
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

        $lang = in_array($request->lang, $this->languages) ? $request->lang : '';
        // echo $lang;die;
        
        if($guest && !empty($guest->id)){
            $listings = DB::table('guest_favorites')->where(['user_id' => $guest->id])->get();
            
            $currency = $this->get_logged_in_user_currency();

            foreach($listings as $key => $listing){
            
                $listing = Listing::where('listing_id', $listing->listing_id)->first();

                $listings[$key]->total = !empty($listings[$key]->total) ? $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($listings[$key]->total, $currency), $lang) : 0;
                $listings[$key]->per_night = !empty($listings[$key]->per_night) ? $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($listings[$key]->per_night, $currency), $lang) : 0;
                $listings[$key]->total_nights = !empty($listings[$key]->total_nights) ? $this->convertToArabicNumber($this->calculate_price_with_exchange_rate($listings[$key]->total_nights, $currency), $lang) : 0;

                if(is_null($listing)){
                    $listings[$key]->images = "";
                    continue;
                }

                $dlisting_name = !empty($listing->be_listing_name) ? $listing->be_listing_name : null;
                $dproperty_type = $listing->property_type;
                $dcity_name = $listing->city_name;
                $ddistrict = $listing->district;

                if(is_null($dlisting_name)){
                    $jsn = !empty($listing->listing_json) ? json_decode($listing->listing_json) : null;
                    $dlisting_name = !empty($jsn->title) ? $jsn->title : '';
                }

                if(!empty($lang)){
                    $dlisting_name = $listing->{$lang . '_listing_name'} ?? $dlisting_name;
                    $dproperty_type = $listing->{$lang . '_property_type'} ?? $dproperty_type;
                    $dcity_name = $listing->{$lang . '_city_name'} ?? $dcity_name;
                    $ddistrict = $listing->{$lang . '_district'} ?? $ddistrict;
                }
                
                $images = $listing->airbnbImages()->pluck('url')->toArray();
                // print_r($images);die;
                $listings[$key]->images = !empty($images) ? $images[0] : "";
                
                $listings[$key]->listing_id = (string) $listing->listing_id;

                $listings[$key]->listing_name = $dlisting_name;
                $listings[$key]->district = $ddistrict;
                $listings[$key]->city_name = $dcity_name;
                $listings[$key]->bedrooms = $this->convertToArabicNumber($listing->bedrooms, $lang);
                $listings[$key]->beds = $this->convertToArabicNumber($listing->beds, $lang);
                $listings[$key]->bathrooms = $this->convertToArabicNumber($listing->bathrooms, $lang);
                $listings[$key]->property_type = $dproperty_type;

                

                // echo $listings[$key]->total; die;
                
                
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
            
            if(isset($data['IsSuccess']) && $data['IsSuccess'] == true){
                
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
                
                
                // Booking Modified Trigger
                
                $numberOfGuests = $booking->adult + $booking->children;
            
                $message = $booking->name . " has made changes to their booking: the new dates are ".$booking->booking_date_start." to ".$booking->booking_date_end." with ".$numberOfGuests." guests. Review the updated details!";
                
                $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
                
                if(!empty($user_ids_arr)){
                    foreach($user_ids_arr as $user_id){
                        if(!empty($user_id)){ 
                            sendLiteNotification($user_id, "Booking Modifications", $message, $booking->id, 'livedin_bk_detail');
                        }
                    }
                }
            
            
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
    
    public function addReview(Request $request){
        
        $validated = $request->validate([
            'booking_id' => 'required',
            'user_id' => 'required',
            'house_rules_star' => 'required',
            'house_rules_review' => 'required',
            'communication_star' => 'required',
            'communication_review' => 'required',
            'cleanliness_star' => 'required',
            'cleanliness_review' => 'required',
            'review' => 'required',
            // 'private_review' => 'required',
        ]);
        
        $beReviewExists = BeReview::where('booking_id', $request->booking_id)->exists();
        if($beReviewExists){
            return response()->json([
                'success' => 0,
                'msg' => 'Review already exists'
            ]);
        }
        
        $data = $request->all();
        
        $data['house_rules_review'] = json_encode($data['house_rules_review']);
        $data['communication_review'] = json_encode($data['communication_review']);
        $data['cleanliness_review'] = json_encode($data['cleanliness_review']);
        
        $beReview = BeReview::create($data);
        if($beReview){
            
            $booking = Bookings::find($request->booking_id);
            
            if(!is_null($booking)){
                
                $listing = Listing::find($booking->listing_id);
                
                $listing_json = !is_null($listing) ? json_decode($listing->listing_json) : '';
                $listing_name = !empty($listing_json) ? $listing_json->title : '';
                
                $listing_name_arr = !empty($listing_name) ? explode(" ", $listing_name) : "";
                $list_name_expld = !empty($listing_name_arr[0]) ? $listing_name_arr[0] : "";
                
                $total_review = $request->house_rules_star + $request->communication_star + $request->cleanliness_star;
                $average_rating = round($total_review / 3);
                
                $firebase_service = app(FirebaseService::class);
                
                $title = "New Review Received";
                $body = "New review! ".$booking->name." has left a $average_rating star review for their stay at $list_name_expld. Check it out!";
                
                try{
                    
                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
                    
                    if(!empty($user_ids_arr)){
                        
                        foreach($user_ids_arr as $user_id){
    
                            $user = User::find($user_id);
        
                            if(!is_null($user) && !empty($user->fcmTokens) && $user->host_type_id == 2){ // for pro user only
                                foreach($user->fcmTokens as $token)
                                {
                                    if(!empty($token->fcm_token)){
                                        try{
                                            
                                            $notificationData = [
                                                'id' => 0,
                                                'otaName' => '',
                                                'type' => 'review_recieved',
                                            ];
    
                                            $send = $firebase_service->sendPushNotification($token->fcm_token, $title, $body, "ReviewTrigger", $notificationData);
                                            
                                            logger("Notification Response: " . json_encode($send));
                                        } catch(\Exception $ex){
                                            logger("Notification Error: " . $ex->getMessage());
                                        }
                                    }
                                }
                            }
                        }
                    }
    
                    
                } catch(\Exception $ex){
                    logger("Notification Error: " . $ex->getMessage());
                }
            }
            
            return response()->json([
                'success' => 1,
                'msg' => 'Review added successfully'
            ]);
        }
        
        return response()->json([
            'success' => 0,
            'msg' => 'Review submission failed'
        ]);
    }
    
    public function getReview(Request $request){
        
        $validated = $request->validate([
            'booking_id' => 'required',
        ]);
        
        $beReview = BeReview::where('booking_id', $request->booking_id)->first();
        if(is_null($beReview)){
            return response()->json([
                'success' => 0,
                'msg' => 'Review not found'
            ]);
        }
        
        $total_review = $beReview->house_rules_star + $beReview->communication_star + $beReview->cleanliness_star;
        $beReview->average_rating = round($total_review / 3); //number_format($total_review / 3, 2, '.', '');
        
        return response()->json([
            'success' => 1,
            'review' => $beReview
        ]);
    }
    
    public function getNotificationReview(Request $request){
        
        $validated = $request->validate([
            'user_id' => 'required',
        ]);
        
        $bookings = Bookings::where('guest_id', $request->user_id)
        ->whereNotNull('created_at')
        ->orderBy('id', 'DESC')
        ->get();
        
        $checkout_arr = [];
        foreach($bookings as $booking){
            
            if(is_null($booking->be_review)){
                $checkout_arr[] = [
                    'booking_id' => $booking->id,
                    'checkin_date' => $booking->booking_date_start,
                ];
            }
        }
        
        if(!empty($checkout_arr)){
            
            $today = date('Y-m-d');
            usort($checkout_arr, function ($a, $b) {
                return strtotime($b['checkin_date']) - strtotime($a['checkin_date']);
            });
        }
        
        if(empty($checkout_arr)){
            return response()->json([
                'success' => 0,
                'msg' => 'Review not found'
            ]);
        }
        
        return response()->json([
            'success' => 1,
            'review' => $checkout_arr
        ]);
    }
    
    public function voucher_redeemed(Request $request){
        
        $validated = $request->validate([
            'user_id' => 'required',
            'listing_id' => 'required',
            'voucher_code' => 'required',
            'total_amount' => 'required',
            'total_nights' => 'required',
        ]);
        
        $voucher = Voucher::where(['voucher_code' => $request->voucher_code, 'is_enabled' => 1])->first();
        if(is_null($voucher)){
            return response()->json([
                'success' => 0,
                'error_code' => 1,
                'msg' => 'This voucher is invalid.' // 'Voucher not found.'
            ]);
        }
        
        $today = Carbon::now();
        
        if (
            $today->lt(Carbon::parse($voucher->voucher_start_date)->startOfDay()) || 
            $today->gt(Carbon::parse($voucher->voucher_end_date)->endOfDay())
        ) {
            return response()->json([
                'success' => 0,
                'error_code' => 2,
                'msg' => 'This voucher is invalid.' //'Expire Voucher not found.'
            ]);
        }

        if(!empty($voucher->min_number_nights) && $request->total_nights < $voucher->min_number_nights){
            return response()->json([
                'success' => 0,
                'error_code' => 3,
                'msg' => 'This voucher is invalid.' //'Voucher can only be applied for stays of '.$voucher->min_number_nights.' nights or less.'
            ]);
        }

        if(!empty($voucher->min_order_amount) && $request->total_amount < $voucher->min_order_amount){
            return response()->json([
                'success' => 0,
                'error_code' => 4,
                'msg' => 'This voucher is invalid.' //'Voucher can only be applied for minimum this amount.'
            ]);
        }
        
        $user_voucher_used = VoucherRedeemed::where(['user_id' => $request->user_id, 'voucher_code' => $request->voucher_code, 'is_redeemed' => 1])->count();
        if(!empty($voucher->max_uses_per_guest) && !empty($user_voucher_used) && $user_voucher_used >= $voucher->max_uses_per_guest){
            return response()->json([
                'success' => 0,
                'error_code' => 5,
                'msg' => 'This voucher is invalid.' //"You've already used this voucher ".$user_voucher_used." times. limit exceeded."
            ]);
        }
        
        // Voucher Limit Exceeded
        $voucher_limit_count = VoucherRedeemed::where(['voucher_code' => $request->voucher_code, 'is_redeemed' => 1])->count();
        if(!empty($voucher->voucher_usage_limit) && !empty($voucher_limit_count) && $voucher_limit_count >= $voucher->voucher_usage_limit){
            return response()->json([
                'success' => 0,
                'error_code' => 6,
                'msg' => 'This voucher is invalid.' //'Voucher usage limit exceeded.'
            ]);
        }
        
        $listing_ids = !empty($voucher->listing_ids) ? explode(',', $voucher->listing_ids) : [];
        
        // print_r($listing_ids);die;
        
        if(empty($listing_ids)){
            return response()->json([
                'success' => 0,
                'error_code' => 7,
                'msg' => 'This voucher is invalid.' //'Listing not found against voucher code.'
            ]);
        }
        
        if(!in_array($request->listing_id, $listing_ids)){
            if(!isset($listing_ids[0]) || $listing_ids[0] != 'all'){
                return response()->json([
                    'success' => 0,
                    'error_code' => 8,
                    'msg' => 'This voucher is invalid.' //'Listing not found against voucher code.'
                ]);
            }
        }
        
        if(empty($voucher->discount)){
            return response()->json([
                'success' => 0,
                'error_code' => 9,
                'msg' => 'This voucher is invalid.' //'Discount amount not found.'
            ]);
        }
        
        $discounted_price = 0;
        
        if($voucher->discount_type == "amount"){
            
            $discounted_price = $voucher->discount; // applied on total amount
            
            if($voucher->discount_applied_on == "per_night_value"){
                $discounted_price = $voucher->discount * $request->total_nights;
            }
        }
        
        if($voucher->discount_type == "percentage"){
            $discount = $voucher->discount / 100;
        
            if(empty($discount)){
                return response()->json([
                    'success' => 0,
                    'error_code' => 10,
                    'msg' => 'This voucher is invalid.' //'Discount is empty.'
                ]);
            }
            
            $discounted_price = round($request->total_amount * $discount);
            
            // Max Discount Amount
            if(!empty($voucher->max_discount_amount) && $discounted_price > $voucher->max_discount_amount){
                $discounted_price = $voucher->max_discount_amount;
            }
        }
        
        if(empty($discounted_price)){
            return response()->json([
                'success' => 0,
                'error_code' => 11,
                'msg' => 'This voucher is invalid.' //'Discount price is empty.'
            ]);
        }

        VoucherRedeemed::create([
            'user_id' => $request->user_id,
            'listing_id' => $request->listing_id,
            'voucher_code' => $request->voucher_code
        ]);

        // $voucher_redeemed = VoucherRedeemed::where(['user_id' => $request->user_id, 'voucher_code' => $request->voucher_code])->first();
        // if(is_null($voucher_redeemed)){
        //     VoucherRedeemed::create([
        //         'user_id' => $request->user_id,
        //         'listing_id' => $request->listing_id,
        //         'voucher_code' => $request->voucher_code
        //     ]);
        // } else if(!is_null($voucher_redeemed) && $voucher_redeemed->is_redeemed == 1){
        //     return response()->json([
        //         'success' => 0,
        //         'msg' => 'This voucher is invalid.' //'The voucher has already been availed.'
        //     ]);
        // }

        $total = $request->total_amount - $discounted_price;
        
        if(empty($total) || $total < 1){
            return response()->json([
                'success' => 0,
                'error_code' => 12,
                'msg' => 'This voucher is invalid.' //'Voucher is not valid.'
            ]);
        }
        
        return response()->json([
            'success' => 1,
            'discounted_price' => $discounted_price,
            'ar_discounted_price' => $this->convertToArabicNumber($discounted_price, 'ar'),
            'total' => $total,
            'ar_total' => $this->convertToArabicNumber($total, 'ar'),
        ]);
    }

    function convertToArabicNumber($number, $lang){

        if($lang == "ar"){
            $westernArabic = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            $easternArabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

            return str_replace($westernArabic, $easternArabic, strval($number));
        }
        return $number;
    }

}





<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Channels;
use App\Models\Listing;
use App\Models\BookingOtasDetails;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Services\MixpanelService; 
use Illuminate\Support\Facades\Validator;
use App\Models\ReviewReply;
use App\Utilities\UserUtility;  


class ReviewController extends Controller
{
    
    protected $mixpanelService;

    public function __construct(MixpanelService $mixpanelService)
    {
        $this->mixpanelService = $mixpanelService;
    }
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_id = Auth::id();
        $reviews = array();
        $one = 0;
        $two = 0;
        $three = 0;
        $four = 0;
        $five = 0;
        $overall = 0;
        $userDB = User::find($user_id);
        if ($userDB->parent_user_id !== null) {
            $channel = Channels::where('user_id', $userDB->parent_user_id)->first();
        } else {
            $channel = Channels::where('user_id', $userDB->id)->first();
        }
        // $listings = Listing::where('channel_id', $channel->id)->get();
        $listings = Listing::all();
        $listings = $listings->toArray();
        foreach ($listings as $listing) {
            $users = json_decode($listing['user_id'], true);
            if (is_array($users) && in_array($user_id, $users)) {
                $listing_arr[] = $listing['listing_id'];
            }
        }
        // dd($listing_arr);
        $reviews = [];
        $reviewsDb = Review::where('overall_score','>=','8')->orderBy('id', 'Desc')->get();
        foreach($reviewsDb as $review) {
            $review_json = json_decode($review->review_json);
            if($review_json->content == '' || $review_json->content == null) {
                continue;
            }
            // dd($review_json->content);
            // dd((int)$review_json->meta->listing_id, $listing_arr);
            if(!isset($review_json->meta->listing_id)) {
                continue;
            }
            if(in_array((int)$review_json->meta->listing_id, $listing_arr)) {

                $overall += $review['overall_score'] / 2;

                if ($review['overall_score'] / 2 == 5) {
                    //  dd($item['overall_score'] / 2); array_push($rating, ['five' => $item['overall_score'] / 2]);
                    $five += 1;
                } else if ($review['overall_score'] / 2 >= 4 && $review['overall_score'] / 2 <= 5) {
                    $four += 1;
                } else if ($review['overall_score'] / 2 >= 3 && $review['overall_score'] / 2 < 4) {
                    $three += 1;
                } else if ($review['overall_score'] / 2 >= 2 && $review['overall_score'] / 2 < 3) {
                    $two += 1;
                } else if ($review['overall_score'] / 2 >= 1 && $review['overall_score'] / 2 < 2) {
                    $one += 1;
                }

                $booking = BookingOtasDetails::where('booking_id', $review->booking_id)->first();
                $booking_json = json_decode($booking->booking_otas_json_details);
                $listing = Listing::where('listing_id', $booking_json->attributes->meta->listing_id)->first();
                $listing_json = isset($listing->listing_json) ? json_decode($listing->listing_json) : null;
                // dd();
                // $reviewsDb['']
                $review['arrival_date'] = $booking->arrival_date;
                $review['departure_date'] = $booking->departure_date;
                $review['adult'] = $booking_json->attributes->occupancy->adults;
                $review['listing_name'] = isset($listing_json->title) ? $listing_json->title : '';
                $reviews[] = $review;
            }
        }
        return response()->json([
            'reviews' => $reviews,
            'rating' => [
                'one' => round($one),
                'two' => round($two),
                'three' => round($three),
                'four' => round($four),
                'five' => round($five),
                'overall' => !empty($overall) && !empty($reviews) ? round($overall / count($reviews)) : '',
            ]
        ]);
    }
    
   public function hostReviews(Request $request)
    {
        // dd($request->listing_name);
        $user_id = Auth::id();
        $reviews = array();
        $userDB = User::find($user_id);
        if ($userDB->parent_user_id !== null) {
            $channel = Channels::where('user_id', $userDB->parent_user_id)->first();
        } else {
            $channel = Channels::where('user_id', $userDB->id)->first();
        }
        // $listings = Listing::where('channel_id', $channel->id)->get();
        $listings = Listing::all();
        $listings = $listings->toArray();
        foreach ($listings as $listing) {
            $users = json_decode($listing['user_id'], true);
            if (is_array($users) && in_array($user_id, $users)) {
                $listing_arr[] = $listing['listing_id'];
            }
        }
        // dd($listing_arr);
        $reviews = [];
        // dd($request->has('stars'));
        if($request->has('stars')) {
            $reviewsDb = Review::where('overall_score', $request->stars)->orderBy('id', 'Asc')->where('overall_score','>=','8')->get();
            
            if (!empty($userDB->role_id) && $userDB->role_id === 2) {
           
                try {
    
    
                    $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation();
    
                    $this->mixpanelService->trackEvent('Reviews Filters Used', [
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
                        'db_city' => $userDB->city
                       
                    ]);
    
    
                } catch (\Exception $e) {
                    
                    
                }
            }
            
            
        }else {
            $reviewsDb = Review::where('overall_score','>=','8')->orderBy('id', 'Asc')->get();
        }
        // dd($reviewsDb);
        // if($request->has('starts'))
        foreach($reviewsDb as $review) {
            $review_json = json_decode($review->review_json);
            
            $review_json = json_decode($review->review_json);
            
            
            if($review_json->content == '' || $review_json->content == null) {
                continue;
            }
            
           if (!isset($review_json->meta) || !isset($review_json->meta->listing_id)) {
               
               continue;
           }
            
            
            // dd($review_json->content);
            // dd((int)$review_json->meta->listing_id, $listing_arr);
            if(in_array((int)$review_json->meta->listing_id, $listing_arr)) {
                 $booking = BookingOtasDetails::where('booking_id', $review->booking_id)->first();
                $booking_json = json_decode($booking->booking_otas_json_details);
                $listing = Listing::where('listing_id', $booking_json->attributes->meta->listing_id)->first();
                $listing_json = isset($listing->listing_json) ? json_decode($listing->listing_json) : null;
                // dd();
                // $reviewsDb['']
                $review['arrival_date'] = $booking->arrival_date;
                $review['departure_date'] = $booking->departure_date;
                $review['adult'] = $booking_json->attributes->occupancy->adults;
                $review['listing_name'] = isset($listing_json->title) ? $listing_json->title : '';
                $reviews[] = $review;
            }
        }
        $searchTitle = isset($request->listing_name) ? $request->listing_name : '';
        $filteredReviews = array_filter($reviews, function ($review) use ($searchTitle) {
            // Debugging: Check what you're working with
            // dd($review['listing_name']);
            if (!isset($review['listing_name'])) {
                // If 'listing_name' is missing, log or inspect the data
                error_log('Listing name not set in review: ' . print_r($review, true));
                return false;
            }

            // Debugging: Check what is being searched
            error_log("Searching for '{$searchTitle}' in '{$review['listing_name']}'");

            // Perform the partial match search
            return stripos($review['listing_name'], $searchTitle) !== false;
        });
        $reviews = isset($request->listing_name) ? $filteredReviews : $reviews;
        // Output the filtered results
        // print_r($filteredReviews);
        // dd($filteredReviews);
        if (!empty($userDB->role_id) && $userDB->role_id === 2) {
           
            try {
                
                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();
                
                $this->mixpanelService->trackEvent('Reviews Module Opened', [
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
        
        return response($reviews);
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
   
    public function hostReviewById($id)
    {
        $user_id = Auth::id();
        $userDB = User::find($user_id);
        
        $reviewsDb = Review::where('id', $id)->first();
        $reviewReply = ReviewReply::where('review_id',$reviewsDb->id)->first();
        $booking = BookingOtasDetails::where('booking_id', $reviewsDb->booking_id)->first();
        $booking_json = json_decode($booking->booking_otas_json_details);
        $listing = Listing::where('listing_id', $booking_json->attributes->meta->listing_id)->first();
        $listing_json = isset($listing->listing_json) ? json_decode($listing->listing_json) : null;
        // dd();
        // $reviewsDb['']
        $reviewsDb['arrival_date'] = $booking->arrival_date;
        $reviewsDb['departure_date'] = $booking->departure_date;
        $reviewsDb['adult'] = $booking_json->attributes->occupancy->adults;
        $reviewsDb['listing_name'] = isset($listing_json->title) ? $listing_json->title : '';
        $reviewsDb['reply_review'] = $reviewReply;

        // dd($booking_json->attributes->meta->listing_id);
        
        if (!empty($userDB->role_id) && $userDB->role_id === 2) {
           
            try {


                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Review is Opened', [
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
        
        return response($reviewsDb);
    }
      public function hostReviewReply(Request $request)
    {
        $user_id = Auth::user()->id;

        $validator = Validator::make($request->all(), [
            'review_id' => 'required',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $review = Review::where('id', $request->review_id)->first();
        // dd($review->uId);
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/reviews/$review->uId/reply", [
                    "reply" => [
                        "reply" =>$request->content
                    ]
                ]);
        if ($response->successful()) {
            // dd($response->json());
            $reviewStore = ReviewReply::create([
                'review_id' => $request->review_id,
                'content' => $request->content,
                'reply_by' => $user_id
            ]);
            return response($reviewStore);
        } else {
            $error = $response->body();
            // dd($error);
        }
        
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
    public function show(Review $review)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Review $review)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        //
    }
}

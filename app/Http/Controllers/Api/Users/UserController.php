<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\HostTypeResource;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailJob;
use App\Jobs\SendOtp;
use App\Mail\EmailVerification;
use App\Mail\ForgetPassword;
use App\Mail\HostOnboardEmail;
use App\Models\ChannelLinks;
use App\Models\Channels;
use App\Models\Group;
use App\Models\HostType;
use App\Models\Listing;
use App\Models\Properties;
use App\Models\CustomerIdentifier;
use App\Models\User;
use App\Models\UserEmegencyContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\UserToken;
use App\Services\MixpanelService; 
use App\Utilities\UserUtility;
use App\Models\UserBiometric;

class UserController extends Controller
{
    
    
    protected $mixpanelService;

    public function __construct(MixpanelService $mixpanelService)
    {
        $this->mixpanelService = $mixpanelService;
    }
    
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $users = User::all();
        return UserResource::collection($users);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function authenticate(Request $request): JsonResponse
    {
        \Log::info('Login Data: ' . json_encode($request->all()));
        
        $data = $request->except('fcm_token'); //$request->all();
        
        $validator = Validator::make($data, [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        if (Auth::attempt($data)) {
            $user = Auth::user();
            if ($user->email_verified === null || $user->email_verified === 0) {
                dispatch(new SendEmailJob($user));
                return response()->json([
                    'code' => 301,
                    'message' => 'Please Verify Your Email First',
                ]);
            }
           if ($user->is_block === 1) {
            return response()->json([
                'code' => 401,
                'message' => 'Invalid credentials',
            ], 401);
            }
            $accessToken = $user->createToken($data['email'])->plainTextToken;
            $expiration = now()->addYear();
            $channel = Channels::where('user_id', $user->id)->first();
            if($channel) {
                // $listings = Listing::where('channel_id', $channel->id)->count();
                    $listings = Listing::whereJsonContains('user_id', strval($user->id))
            ->count();
                // dd( $listings);
            }
            // Update firebase token
            if($request->filled('fcm_token')){
                $this->updateFcmTokens($request->fcm_token, $user->id);
            }
            
            if (!empty($user->role_id) && $user->role_id === 2) {
                try {
                  
                   $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation(); 
                    
                    
                    $this->mixpanelService->trackEvent('User Logged In', [
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
            $customer_identifier = CustomerIdentifier::where('customer_identifier', $user->email)->exists();

            // dd($user,$customer_identifier);
            return response()->json([
                'code' => 200,
                'message' => 'Login successful',
                'user' => new UserResource($user),
                'access_token' => $accessToken,
                'customer_identifier' =>$customer_identifier,
                'channel_id' => isset($channel->ch_channel_id) && $channel->ch_channel_id ? $channel->ch_channel_id : null,
                'listing_count' =>isset($listings) ? $listings : 0,
                'expire_at' => $expiration
            ]);
        }
        return response()->json([
            'code' => 401,
            'message' => 'Invalid credentials',
        ], 401);
    }
    public function socialAuthentication(Request $request): JsonResponse
    {

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
        $user = User::where('email', $request->email)->first();
        if($user) {
            Auth::guard('api')->login($user);

            $accessToken = $user->createToken($user->email)->plainTextToken;
            $expiration = now()->addYear();
            // dd($accessToken);
              if($request->filled('fcm_token')){
                $this->updateFcmTokens($request->fcm_token, $user->id);
            }
            $channel = Channels::where('user_id', $user->id)->first();
            if($channel) {
                 $listings = Listing::whereJsonContains('user_id', strval($user->id))
            ->count();
                // dd( $listings);
            }
            $customer_identifier = CustomerIdentifier::where('customer_identifier', $user->email)->exists();
            return response()->json([
                'code' => 200,
                'message' => 'Login successful (Guest)',
                'user' => $user,
                'access_token' => $accessToken,
                'customer_identifier' =>$customer_identifier,
                'channel_id' => isset($channel->ch_channel_id) && $channel->ch_channel_id ? $channel->ch_channel_id : null,
                'listing_count' =>isset($listings) ? $listings : 0,
                'expire_at' => $expiration
            ]);
        }else {
            return response()->json([
                'code' => 301,
                'message' => 'User dosent exists',
                'user' => [],
                'access_token' => null,
                'expire_at' => null
            ]);
        }


    }
    public function registerUserBiometric(Request $request)
    {
        // user_id
        // device_id
        // device_hash
        $validator = Validator::make($request->all(), [
            'user_id' => ['required'],
            'device_id' => ['required'],
            'device_hash' => ['required'],
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // dd($request->all());

        $user = User::where('id', $request->user_id)->first();
        if($user) {
            $biometric = UserBiometric::where('user_id', $request->user_id)->where('device_id',$request->device_id)->where('device_hash',$request->device_hash)->first();
            if(is_null($biometric)) {
                $biometric = UserBiometric::create([
                    'user_id' => $request->user_id,
                    'device_id' => $request->device_id,
                    'device_hash' => $request->device_hash,
                ]);
                if($biometric) {
                     return response()->json([
                        'code' => 200,
                        'message' => 'Biometric Registered Successfully',
                        'user' => $user,
                        'biometric' => $biometric
                    ]);
                }

            }
        }else {
            return response()->json([
                'code' => 301,
                'message' => 'User dosent exists',
                'user' => []
            ]);
        }
    }
    public function loginUserBiometric(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => ['required'],
        'device_id' => ['required'],
        'device_hash' => ['required'],
    ]);
    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()], 400);
    }

    $user = User::where('id', $request->user_id)->first();
    if (!$user) {
        return response()->json([
            'code' => 301,
            'message' => 'User does not exist',
            'user' => []
        ]);
    }

    // Retrieve Biometric Data
    $biometric = UserBiometric::where('user_id', $request->user_id)
        ->where('device_id', $request->device_id)
        ->orderBy('id', 'desc')
        ->first();

    if (!$biometric) {
        return response()->json([
            'code' => 301,
            'message' => 'Biometric is not set up',
        ]);
    }

    // Decode Signature
    $signature = base64_decode($request->device_hash);
    if (!$signature) {
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    // Construct payload (Ensure it matches the mobile app signing format)
    $payload = (string)$request->user_id;

    // Ensure Public Key is in Correct Format
    $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
        chunk_split($biometric->device_hash, 64, "\n") .
        "-----END PUBLIC KEY-----";

    $publicKey = openssl_pkey_get_public($publicKey);
    if ($publicKey === false) {
        return response()->json(['error' => 'Invalid public key format'], 400);
    }
    // Verify Signature
    $ok = openssl_verify($payload, $signature, $publicKey, OPENSSL_ALGO_SHA256);

    if ($ok === 1) {
        
        // Login User
        Auth::guard('api')->login($user);
        $accessToken = $user->createToken($user->email)->plainTextToken;
        $expiration = now()->addYear();
        if($request->filled('fcm_token')){
                $this->updateFcmTokens($request->fcm_token, $user->id);
            }
              $channel = Channels::where('user_id', $user->id)->first();
            if($channel) {
                 $listings = Listing::whereJsonContains('user_id', strval($user->id))
            ->count();
                // dd( $listings);
            }
            $customer_identifier = CustomerIdentifier::where('customer_identifier', $user->email)->exists();
        
        return response()->json([
            'code' => 200,
            'message' => 'Login successful',
            'user' => $user,
            'access_token' => $accessToken,
             'customer_identifier' =>$customer_identifier,
            'channel_id' => isset($channel->ch_channel_id) && $channel->ch_channel_id ? $channel->ch_channel_id : null,
            'listing_count' =>isset($listings) ? $listings : 0,
            'expire_at' => $expiration
        ]);
    } elseif ($ok === 0) {
        return response()->json(['error' => 'Signature does not match'], 400);
    } else {
        return response()->json(['error' => 'Error verifying signature'], 400);
    }
}
    /**
     * @param Request $request
     * @return UserResource|JsonResponse
     */
    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|integer',
            'dob' => 'required',
            'gender' => 'nullable',            
            'country' => 'nullable',
            'city' => 'required|string',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $validator->validated();
        $data['password'] = bcrypt($data['password']);
        $data['role_id'] = 2;

        try {
            $fcmToken = $request->has('fcm_token') ? $request->fcm_token : null;
            $user = User::create($data);
            $emergencyContactCreated = UserEmegencyContact::create([
                'user_id' => $user->id
            ]);

            if (!$emergencyContactCreated) {
                return response()->json(['error' => 'Failed to create emergency contact.'], 500);
            }

            $channel = Channels::where('user_id', $user->id)->first();
            $user['access_token'] = $user->createToken($data['email'])->plainTextToken;
            $user['channel_id'] = $channel->ch_channel_id ?? null;
            
            if ($fcmToken) {
                $this->updateFcmTokens($fcmToken, $user->id);
            }

            try {
                dispatch(new SendEmailJob($user));
            } catch (TransportException $e) {
                \Log::error('Email dispatch failed: ' . $e->getMessage());
                $user['email_sent'] = false;
            }
            
             try {
            
              $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();      
                 
               $this->mixpanelService->trackEvent('User Signup', [
                 'distinct_id' => $user->id,
                'first_name' => $user->name,
                'last_name' => $user->surname,
                'Date_of_birth' => $user->dob,
                'gender'  => $user->gender,
                '$country' => $location['country'],
                '$region' => $location['region'],
                '$city' => $location['city'],
                'phone' => $user->phone,
                'email' => $user->email,
                '$os' => $userUtility->getUserOS(), // Add OS here
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'timezone' => $location['timezone'],
                'ip_address' => $location['ip'],
                'db_country' => $user->country,
                'db_city' => $user->city
                ]);
            }
        catch (\Exception $e) {
            
        }

        return new UserResource($user);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong. Please try again later.'. $e->getMessage()], 500);
        }
    }

    public function emailChecker(User $user)
    {
        dispatch(new SendEmailJob($user));
        return response()->json(['status' => 'Email Sent']);
    }
    public function verfiyEmailAddress(User $user)
    {
        if ($user->email_verified === 1) {
            return response()->json([
                'code' => 200,
                'message' => 'Email Already Verified',
                'user' => new UserResource($user),
            ]);
        }

        $user->update(['email_verified' => 1]);
        return response()->json(['success' => 'Email Verified Successfully']);
    }

    public function emailVerificationStatus(User $user)
    {
        return new UserResource($user);
    }

    public function createChannexAccount(User $user)
    {
        $group = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/groups', [
                    "group" => ["title" => strtolower($user->name) . '_g_' . $user->id]
                ]);
        $grData = array();
        if ($group->successful()) {
            $response = $group->json();
            $grData['group_name'] = strtolower($user->name) . '_g_' . $user->id;
            $grData['user_id'] = $user->id;
            $grData['ch_group_id'] = $response['data']['id'];
            $groupCreated = Group::create($grData);
        } else {
            $error = $group->body();
            //            return response()->json(['error' => $error]);
        }
        //        dd($grData['ch_group_id']);
        $property = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/properties', [
                    "property" => [
                        "title" => strtolower($user->name) . $user->id,
                        "currency" => 'USD',
                        "timezone" => 'Asia/Riyadh',
                        "email" => $user->email,
                        "country" => substr($user->country, -2),
                        "city" => $user->city,
                        "group_id" => $grData['ch_group_id'],
                        "settings" => [
                            "min_stay_type" => 'through'
                        ]
                    ]
                ]);
        if ($property->successful()) {
            $property = $property->json();
            $data['title'] = strtolower($user->name) . $user->id;
            $data['currency'] = 'USD';
            $data['email'] = $user->email;
            $data['country'] = substr($user->country, -2);
            $data['city'] = $user->city;
            $data['user_id'] = $user->id;
            $data['ch_property_id'] = $property['data']['id'];
            $data['ch_group_id'] = $grData['ch_group_id'];
            $data['group_id'] = $groupCreated->id;
            Properties::create($data);

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
                //                dd($webhook);
            } else {
                $error = $property->body();
                //                dd($error);
            }
        } else {
            $error = $property->body();
            //            dd($error);
        }

        $userGroup = Group::where('user_id', $user->id)->first();

        $property = Properties::where('user_id', $user->id)->first();

        $url = env('CHANNEX_URL') . '/api/v1/meta/airbnb/connection_link';
        $properties = ["$property->ch_property_id"];
        $min_stay_type = 'Arrival';
        $group_id = $userGroup->ch_group_id;
        $redirect_uri = route('get-channel-callback-api', $user->id);
        //        dd($redirect_uri);
        $user_api_key = env('CHANNEX_API_KEY');

        $response = Http::withHeaders([
            'user-api-key' => $user_api_key,
        ])->get($url, [
                    'properties' => json_encode($properties),
                    'title' => 'channel_' . $group_id,
                    'min_stay_type' => $min_stay_type,
                    'group_id' => $group_id,
                    'redirect_uri' => $redirect_uri
                ]);

        // Handle response here, e.g., decode JSON response
        $resp = $response->json();
        //        dd($resp['data']['attributes']['url']);
        $data['user_id'] = $user->id;
        $data['url'] = $response['data']['attributes']['url'];
        ChannelLinks::create($data);

        return response()->json([
            'code' => 200,
            'message' => 'Channel Created Successfully',
            'data_url' => $data['url'],
        ]);
    }

    public function getChannelCallback(Request $request, User $user)
    {
        $data = $request->all();
        $channel_id = $data['channel_id'];
        $channel = Channels::create(['user_id' => $user->id, 'ch_channel_id' => $data['channel_id']]);

        //Fetch Listing by channel ID
        $listings = array();

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel_id/action/listings");

        if ($response->successful()) {
            $response = $response->json();
            //            dd($response);
            foreach ($response['data']['listing_id_dictionary']['values'] as $item) {
                array_push($listings, $item);
            }
        } else {
            $error = $response->body();
        }
        foreach ($listings as $item) {
            Listing::create([
                'user_id' => json_encode(["$user->id"]),
                'listing_id' => $item['id'],
                'listing_json' => json_encode($item),
                'channel_id' => $channel->id,
            ]);
        }


        return response()->json([
            'code' => 200,
            'message' => 'Channel Created Successfully',
        ]);
    }

    public function updatePlan(Request $request, User $user)
    {
        if($request->host_type_id === 2) {
            Mail::to('adnan.anwar@livedin.co')->send(new HostOnboardEmail($user));
        }
        $user->update(['host_type_id' => $request->host_type_id]);

        return response()->json(['success' => 'Host Type Updated Successfully']);
    }
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'email_verification_code' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = User::where('email', $request->email)->first();
        if ($request->email_verification_code === $user['email_verification_code']) {
            $user->update(['email_verified' => 1]);
            return response()->json(['success' => 'Email Successfully Verified']);
        } else {
            return response()->json(['error' => 'Invalid Otp']);
        }
    }

    /**
     * @return AnonymousResourceCollection
     */
    public function fetchHostType(): AnonymousResourceCollection
    {
        return HostTypeResource::collection(HostType::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function isAccountPlanActivate($id)
    {
        $listingResponse = array();
        // dd($AuthuserId);
        $user = User::whereId($id)->first();
        $channel = Channels::where('user_id', $user->id)->first();

        $listings = Listing::where('channel_id', $channel->id)->get();
        // dd($listings);
        foreach ($listings as $item) {
            $listing_details = $item->toArray();
            $user_arr = json_decode($listing_details['user_id']);
            if (in_array($id, $user_arr)) {
                $listing = json_decode($item->listing_json);
                $listing->is_sync = $item->is_sync;
                array_push($listingResponse, $listing);
            }
        }

        //        $listing = Listing::where('user_id', $user->id)->where('is_sync', 'sync_all')->get();
//        dd($listing,$user->plan_verified);
        if ($user->plan_verified == 0) {
            return response()->json(['status' => false]);
        } else {
            return response()->json(['status' => true]);
        }
    }

    public function isUserListed(Request $request, $id)
    {

        $user = User::findOrfail($id);
        //        $listing = Listing::where('user_id', $user->id)->where('is_sync', 'sync_all')->get();
        $channel = Channels::where('ch_channel_id', $request->channel_id)->first();
        $listings = Listing::where('channel_id', $channel->id)->where('is_sync', 'sync_all')->get();

        if (count($listings) > 0 && $user->host_type_id == 2) {
            return response()->json(['sync' => true]);
        } else {
            return response()->json([
                'sync' => false
            ]);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (isset($request->type) && $request->type == 'emergency') {
            $emergency_contact = UserEmegencyContact::where('user_id', $user->id)->first();
            $emergency_contact->update($request->all());
        } else {
            $user->update($request->all());
        }
        
        if (!empty($user->role_id) && $user->role_id === 2) {
           
            try {

                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Personal Information Edited', [
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
                    'host_key' => $user->host_key,
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
                    'host_key' => $user->host_key,
                    'host_type' => $user->hostType->module_name
                   
                ]);

            } catch (\Exception $e) {
                
                
            }
        }
        
        return new UserResource($user);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function verifyOtp(Request $request): mixed
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = User::where('email', $request->email)->where('email_verification_code', $request->otp)->first();
        if ($user) {
            return response()->json(['status'=>200, 'msg'=>'Otp verified successfully']);
            }else {
            return response()->json(['status'=>401, 'msg'=>'Otp not matched']);
        }
        // $response = Http::get('https://control.msg91.com/api/v5/otp/verify', [
        //     'template_id' => env('TEMPLATE_ID'),
        //     'otp' => $request->otp,
        //     'mobile' => $user->phone,
        //     'authkey' => env('AUTH_KEY'),
        // ]);

        // if ($response->successful()) {
        //     return $response->json();
        // } else {
        //     return $response->body();
        // }

    }


    public function passwordUpdate(Request $request)
    {
        //        dd($request);
        $user = User::where('email', $request->email)->first();
        $request->validate([
            'email' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);
        $request['password'] = bcrypt($request['password']);
        $user->update([
            'password' => $request['password']
        ]);
        return response()->json(['success', 'Password Changes Successfully']);
    }
    function generateRandomNumber($length = 6) {
            return str_pad(mt_rand(0, 999999), $length, '0', STR_PAD_LEFT);
        }
   public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $user = User::where('email', $request->email)->first();
        $otp = $this->generateRandomNumber(6);
        $user->update(['email_verification_code' => $otp]);

        // dd($user, $otp);
        $mailData = [
            'title' => 'Mail from LivedIn',
            'body' => $otp
        ];
        Mail::to($user['email'])->send(new ForgetPassword($mailData));
        return response()->json(['success', 'Email send successfully']);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function sendOtp(Request $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        $validator = Validator::make($request->all(), [
            'email' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        } else {
            dispatch(new SendOtp($user));
            return response()->json(['message' => 'OTP sent successfully'], 200);
        }
    }

    public function resendEmailVerification(User $user)
    {
        try {
            dispatch(new SendEmailJob($user));
            return response()->json(['message' => 'Email verificaion link sent successfully'], 200);
        } catch (TransportException $e) {
            \Log::error('Email dispatch failed: ' . $e->getMessage());
            $user['email_sent'] = false;
        }
       
    }

    public function countries()
    {
        $countries = DB::table("countries")->get();
        return response()->json($countries, 200, [], JSON_UNESCAPED_UNICODE);
    }

// public function countries(Request $request)
// {
   
//     $lang = $request->get('lang', 'en'); 

//     $countries = DB::table('countries')
//         ->select('id', $lang === 'ar' ? 'arabic_name as name' : 'name')
//         ->get();

//     return response()->json($countries, 200, [], JSON_UNESCAPED_UNICODE);
// }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function cities(Request $request): JsonResponse
    {
        $states = DB::table("states")->where('country_id', $request->country_id)->pluck('id');
        $cities = DB::table("cities")
            ->whereIn('state_id', $states)
            ->get();
        return response()->json($cities);
    }

    public function states(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $states = DB::table("states")->where('country_id', $request->country_id)->get();

        return response()->json($states);
    }
    
     public function saveCustomerIdentifier(Request $request): JsonResponse
    {
        $customer_indentifier = CustomerIdentifier::create($request->all());
        return response()->json([
            'customer_identifier' => $customer_indentifier->customer_identifier,
            'status' => $customer_indentifier->status
        ]);
    }

    /**
     * @param User $user
     * @return UserResource
     */
    public function destroy(User $user): UserResource
    {
        if (!empty($user->role_id) && $user->role_id === 2) {
           
            try {
                
                 $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();
                
                $this->mixpanelService->trackEvent('User deleted Account', [
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
            } catch (\Exception $e) {
                
                
            }
        }
        
         $user->update([
            'is_block' => 1
        ]);
        return new UserResource($user);
    }

    public function unblock($user_id){

        $user = User::find($user_id);
        $user->is_block = 0;
        $user->save();

        echo 'done';
    }
    
    public function fcm_token_update(Request $request): JsonResponse
    {
        try{
            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'fcm_token' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 400);
            }

            $user = User::find($request->user_id);
            if (!is_null($user) && $request->filled('fcm_token')) {
               $this->updateFcmTokens($request->fcm_token, $user->id);
            }

            return response()->json(['status'=>1, 'msg'=>'fcm_token has been updated']);
        } catch (\Exception $e) {
            return response()->json(['status'=>0, 'error' => $e->getMessage()], 500);
        }
    }
    
    public function fetch_tiers($hostTypeId){

        if (empty($hostTypeId)) {
            return response()->json(['error' => "The host type id field is required"], 400);
        }

        // dd();

        // Host Lite Packages
        if($hostTypeId == 1){
            return response()->json([
                'inAppNotifications' => true,
                'signUpAndDashboard' => [
                    'hostOnboarding' => true,
                    'dashboard' => true,
                ],
                'listings' => [
                    'creation' => true,
                    'mapping' => true, // updated to true
                    'update' => true,
                    'linkRepository' => true,
                ],
                'bookings' => [
                    'bookingModule' => true, // remains the same
                ],
                'calendar' => [
                    'listingCalendar' => true,
                    'availabilityManagement' => true,
                    'blockedDates' => true,
                    'directBookings' => true,
                    'pricingManagement' => [
                        'pricingModule' => true, // added this
                        'discountsAndPromotions' => true,
                        'additionalFees' => true,
                    ],
                ],
                'chatAndOTABookings' => [
                    'chatModule' => true, // updated to true
                    'bookingsManagement' => [
                        'bookingDetails' => true,
                        'bookingPreApprovalsAndBookingRequests' => true, // updated
                        'specialOffer' => true,
                        'bookingCancellation' => false
                    ],
                ],
                'ratingsAndReviews' => [
                    'incomingGuestReviews' => true,
                    'guestReviewReplies' => true, // updated to true
                ],
                'analyticsModule' => [
                    'analytics' => true,
                    'menuAnalytics' => true, // updated to true
                    'tabAnalytics' => false, // updated to false
                ],
                'paymentRevenueModule' => [
                    'showPaymentModule' => true,
                ],
                'taskManager'=> true,
                'accountSetting' => [
                    'intercorm' => true
                    ]
            ], 200);

        }

        // Host Pro Packages
        if($hostTypeId == 2){
            return response()->json([
                'inAppNotifications' => true,
                'signUpAndDashboard' => [
                    'hostOnboarding' => true,
                    'dashboard' => true,
                ],
                'listings' => [
                    'creation' => false,
                    'mapping' => true, // updated to true
                    'update' => false,
                    'linkRepository' => true,
                ],
                'bookings' => [
                    'bookingModule' => true, // remains the same
                ],
                'calendar' => [
                    'listingCalendar' => true,
                    'availabilityManagement' => true,
                    'blockedDates' => auth()->user()->able_to_block_calender == 1 ? true: false,
                    'directBookings' => true,
                    'pricingManagement' => [
                        'pricingModule' => false, // added this
                        'discountsAndPromotions' => false,
                        'additionalFees' => false,
                    ],
                ],
                'chatAndOTABookings' => [
                    'chatModule' => false, // updated to true
                    'bookingsManagement' => [
                        'bookingDetails' => false,
                        'bookingPreApprovalsAndBookingRequests' => false, // updated
                        'specialOffer' => false,
                        'bookingCancellation' => true
                    ],
                ],
                'ratingsAndReviews' => [
                    'incomingGuestReviews' => true,
                    'guestReviewReplies' => false, // updated to true
                ],
                'analyticsModule' => [
                    'analytics' => true,
                    'menuAnalytics' => false, // updated to true
                    'tabAnalytics' => true, // updated to false
                ],
                'paymentRevenueModule' => [
                    'showPaymentModule' => true,
                ],
                 'taskManager'=> false,
                'accountSetting' => [
                    'intercorm' => true
                    ]
            ], 200);
        }

        return response()->json(['error' => "Data not found"], 400);
    }
    
    protected function updateFcmTokens($fcmToken, $userId) {
        $existingToken = UserToken::where('user_id', $userId)
                                  ->where('fcm_token', $fcmToken)
                                  ->first();
    
        if (!$existingToken) {
            UserToken::create([
                'user_id' => $userId,
                'fcm_token' => $fcmToken,
            ]);
        }
    }

    public function logout(Request $request){

        if(Auth::check() && $request->filled('user_id')){

            $user = Auth::user();
            $userDB = User::whereId($request->user_id)->first();
            
            
             
            
            
            if($user && $user->id == $request->user_id){

                if($request->filled('fcm_token')){
                    $user->fcmTokens()->where('fcm_token', $request->fcm_token)->delete();
                }
                
                

                // Destroy login session
                $user->currentAccessToken()->delete();
            }
        }
        
        if (!empty($user->role_id) && $user->role_id === 2) {
           
            try {
                
                  $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation();
                
                $this->mixpanelService->trackEvent('User Logged Out', [
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
                
                Log::error('Error tracking Mixpanel event.', [
                    'user_id' => $userDB->id,
                    'error_message' => $e->getMessage()
                ]);
            }
        }
        
        return response()->json('success');
    }
    
     private function getUserOS()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        
        Log::info('From Iphone User-Agent: ' . $userAgent);
     
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
    
}

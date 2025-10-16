<?php

namespace App\Http\Controllers\Admin\CommunicationManagement;

use App\Http\Controllers\Controller;
use App\Models\BookingInquiry;
use App\Models\Listing;
use App\Models\Listings;
use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\BookingRequest;
use App\Models\BookingOta;
use App\Models\BookingOtasDetails;
use Carbon\Carbon;
use App\Models\Channels;
use App\Services\MixpanelService; 
use App\Models\Review;

class CommunicationManagementController extends Controller
{
    
    private $mixpanelService;
    
    public function __construct(MixpanelService $mixpanelService)
    {
        //$this->middleware('permission');
        $this->mixpanelService = $mixpanelService;
    }

    public function index()
    {
        //        $response = Http::withHeaders([
//            'user-api-key' =>  env('CHANNEX_API_KEY'),
//        ])->post(env('CHANNEX_URL')."/api/v1/live_feed/f45c82f7-2078-4095-9b14-102b2d17e6ea/resolve",[
//            "resolution" => [
//                "type" => "preapproval",
//                "accept" => true
//            ]
//        ]);
//        if ($response->successful()) {
//            $response = $response->json();
//            dd($response);
////            dd($response['data']['threads']);
//            return response()->json($response['data']);
//        } else {
//            $error = $response->body();
//            dd($error);
//        }
        // return view('Admin.communication-management.index');
        return view('Admin.communication-management.index');
    }
    public function indexTwo()
    {
        return view('Admin.communication-management.index');

    }
    
     public function fetchThreads(Request $request)
    {

        if (isset($request->system) && $request->system == 'Admin') {
            $offset = $request->offset ?? 0;
            $limit = $request->limit ?? 10;
            $searchKeyword = '%'.($request->search ?? "").'%';

            $threads = DB::select('
                SELECT 
                    threads.id,
                    threads.listing_id,
                    threads.live_feed_event_id,
                    threads.ch_thread_id,
                    threads.name,
                    threads.is_read as is_read,
                    threads.message_date,
                    threads.thread_type,
                    threads.booking_info_json,
                    threads.status,
                    threads.created_at,
                    threads.updated_at,
                    booking_inquiry.total_price, 
                    threads_messages.message_content AS last_message, 
                    threads_messages.sender AS last_message_sender, 
                    threads_messages.created_at as last_message_date,
                    channels.connection_type
                FROM 
                    threads
                JOIN 
                    (SELECT 
                        tm1.*
                    FROM 
                        threads_messages tm1
                    JOIN 
                        (SELECT 
                            thread_id, 
                            MAX(created_at) AS created_at
                        FROM 
                            threads_messages
                        GROUP BY 
                            thread_id
                        ) AS latest ON tm1.thread_id = latest.thread_id AND tm1.created_at = latest.created_at
                    ) AS threads_messages ON threads.id = threads_messages.thread_id
                LEFT JOIN 
                    booking_inquiry ON threads.ch_thread_id = booking_inquiry.message_thread_id
                LEFT JOIN 
                    listings ON threads.listing_id = listings.listing_id 
                LEFT JOIN 
                    channels ON listings.channel_id = channels.id
                WHERE 
                    (threads.name LIKE ? 
                    OR threads_messages.message_content LIKE ?
                    OR threads.message_date LIKE ?)
                ORDER BY 
                    threads_messages.created_at DESC
                LIMIT ? OFFSET ?
            ', [$searchKeyword, $searchKeyword, $searchKeyword, $limit, $offset]);
            // dd($threads->toArray());
            $threads_array = array();
            foreach ($threads as $item) {
                $item = (array)$item;
                
                $listing = Listings::where('listing_id', $item['listing_id'])->first();
                if(is_null($listing)) {
                    continue;
                }
                $listing_json = json_decode($listing->listing_json);
                $item['listing_name'] = $listing_json->title;
                $item['title'] = 'test user';
                $item['message_date'] = Carbon::parse($item['last_message_date'])->format('M d, Y, g:i A');
                // dd($item);
                array_push($threads_array, $item);
            }
            return response()->json($threads_array);
        }

        $listingResponse = array();
        $user = User::where('id', $request->user_id)->first();
        $listings = Listing::all();
        foreach ($listings as $item) {
            $listing_details = $item->toArray();
            $user_arr = json_decode($listing_details['user_id']);
            if (in_array($user->id, $user_arr)) {
                $listing = json_decode($item->listing_json);
                $listing->is_sync = $item->is_sync;
                array_push($listingResponse, $listing);
            }
        }
        
        $threadCollection = array();
        
        $listingIds = [];
        foreach ($listingResponse as $item) {
            array_push($listingIds, $item->id);
        }
        
        if (!empty($listingIds)) {
            $placeholders = implode(',', array_fill(0, count($listingIds), '?'));
            $threads = DB::select("
                SELECT 
                    threads.id,
                    threads.listing_id,
                    threads.live_feed_event_id,
                    threads.ch_thread_id,
                    threads.name,
                    threads.message_date,
                    threads.thread_type,
                    threads.booking_info_json,
                    threads.status,
                    threads.created_at,
                    threads.updated_at,
                    booking_inquiry.total_price, 
                    threads_messages.message_content AS last_message, 
                    threads_messages.created_at
                FROM 
                    threads
                JOIN 
                    (SELECT 
                         tm1.*
                     FROM 
                         threads_messages tm1
                     JOIN 
                         (SELECT 
                              thread_id, 
                              MAX(created_at) AS created_at
                          FROM 
                              threads_messages
                          GROUP BY 
                              thread_id
                         ) AS latest ON tm1.thread_id = latest.thread_id AND tm1.created_at = latest.created_at
                    ) AS threads_messages ON threads.id = threads_messages.thread_id
                LEFT JOIN 
                    booking_inquiry ON threads.ch_thread_id = booking_inquiry.message_thread_id
                WHERE 
                    threads.listing_id IN ($placeholders)
                ORDER BY 
                    threads_messages.created_at DESC;
            ", $listingIds);
        } else {
            $threads = [];
        }

            
          foreach ($threads as $key => $thread) {
                $listing = Listings::where('listing_id', $thread->listing_id)->first();
                if(is_null($listing)) {
                    continue;
                }
                $listing_json = json_decode($listing->listing_json);
              
                $threadCollection[$key]['id'] = $thread->id; 
                $threadCollection[$key]['listing_name'] = $listing_json->title;
                $threadCollection[$key]['name'] = $thread->name;
                $threadCollection[$key]['last_message'] = $thread->last_message;
                $threadCollection[$key]['message_date'] = $thread->message_date;
            }
            
        
        $user = Auth::user();
            
        if (!empty($user->role_id) && $user->role_id === 2) {
       
            try {
                $this->mixpanelService->trackEvent('Chat Module Opened', [
                    'distinct_id' => $user->id,
                    'first_name' => $user->name,
                    'last_name' => $user->surname,
                    'email' => $user->email,
                    '$country' => $user->country,
                    '$city' => $user->city,
                    '$os' => $this->getUserOS(), // Add OS here
                ]);
                
                $this->mixpanelService->setPeopleProperties($user->id, [
                    '$first_name' => $user->name,
                    '$last_name' => $user->surname,
                    '$email' => $user->email,
                    '$country' => $user->country,
                    '$city' => $user->city,
                    '$os' => $this->getUserOS(), // Add OS here
                   
                ]);
                
            } catch (\Exception $e) {
                
                
            }
        }
            
            
        return response()->json($threadCollection);

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
         'PostmanRuntime' => 'Postman', // For Postman testing
     ];

      foreach ($osArray as $key => $os) {
         if (strpos($userAgent, $key) !== false) {
             return $os;
         }
      }

      return 'Unknown';
    }

    public function fetchThreadByID($id)
    {
                $threadMessageCollection = array();
        $threadMessages = ThreadMessage::where('thread_id', $id)->get();
        //        dd($threadMessages);
        foreach ($threadMessages as $key=>$item) {
            $item['message_date'] = Carbon::parse($item['created_at'])->format('M d, Y, g:i A');
            array_push($threadMessageCollection, $item);
        }
//        dd($threadCollection);
        return response()->json($threadMessageCollection);
        // return response()->json($threadMessages);

        //        $thread_id = request('thread_id');
//        $channel_id = request('channel_id');
//        $response = Http::withHeaders([
//            'user-api-key' =>  env('CHANNEX_API_KEY'),
//        ])->post(env('CHANNEX_URL')."/api/v1//channels/$channel_id/action/api_proxy", [
//            "request" =>  [
//                "endpoint" => "/threads/$thread_id",
//                "method" => "get",
//            ]
//        ]);
//        if ($response->successful()) {
//            $response = $response->json();
////            dd($response['data']['thread']);
//            return response()->json($response['data']['thread']);
//        } else {
//            $error = $response->body();
//            dd($error);
//        }
    }

    public function sendMessage(Request $request)
    {
        // dd(Carbon::now()->addDay());
        $thread_id = request('thread_id');
        $message = request('message');
        $thread = Thread::where('id', $request->thread_id)->first();
        // dd($thread);
        $channel_id = 'b3649c41-0c5b-425f-948a-815fc459a2d4';
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/message_threads/$thread->ch_thread_id/messages", [
                    "message" => [
                        "message" => "$message",
                    ]
                ]);
        if ($response->successful()) {
            $response = $response->json();
            $threadMessage = ThreadMessage::create([
                'thread_id' => $thread->id,
                'sender' => "property",
                'message_content' => $message,
                'message_date' => Carbon::now(),
                'created_at' => Carbon::now(),

            ]);
            //            dd($response['data']['id']);
            return response()->json($response['data']['id']);
        } else {
            $error = $response->body();
            dd($error);
        }
    }

    public function checkForBookingInquiryDetails($ch_inquiry_id)
    {
        $inquiry = BookingInquiry::where('ch_inquiry_id', $ch_inquiry_id)->first();
        return response()->json($inquiry);
    }

    public function approveOrRejectInquiry(Request $request)
    {
        $thread = Thread::where('live_feed_event_id', $request->live_feed_event_id)->first();
        //        dd($thread->live_feed_event_id);
        if ($request->status == 'special_offer') {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/live_feed/$thread->live_feed_event_id/resolve", [
                        "resolution" => [
                            "type" => "special_offer",
                            "total_price" => $request->amount
                            //                "accept" => false,
//                "reason" => "dates_not_available",
//                "decline_message_to_guest" => "not available",
//                "decline_message_to_airbnb" => "not available"
                        ]
                    ]);
            if ($response->successful()) {
                //            dd($response['data']['attributes']);

                $response = $response->json();
                \Log::info("Special Offer:...". json_encode($response));
                BookingInquiry::create(
                    [
                        'ch_inquiry_id' => $response['data']['attributes']['id'],
                        'property_id' => $response['data']['attributes']['property_id'],
                        'status' => $response['data']['attributes']['payload']['status'],
                        'comment' => $response['data']['attributes']['payload']['comment'],
                        'message_thread_id' => $response['data']['attributes']['payload']['message_thread_id'],
                        'type' => $response['data']['attributes']['payload']['resolution']['type'],
                        'total_price' => $response['data']['attributes']['payload']['resolution']['total_price'],
                        'booking_details' => json_encode($response['data']['attributes']['payload']['booking_details']),
                    ]
                );

                ThreadMessage::create([
                    'thread_id' => $thread->id,
                    'sender' => "channel",
                    'message_content' => "Special Offer sent",
                    'message_date' => now()->addHour(3),
                    'message_type' => 'special_offer'
                ]);

                $thread->update(['status' => $request->status, 'action_taken_at' => now()]);
                //            dd($response['data']['threads']);
                return response()->json(["message" => "Record updated!"], 200);
            } else {
                $error = $response->body();
                return response()->json(["error" => $error], 500);
                dd($error);
            }
        } else {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/live_feed/$thread->live_feed_event_id/resolve", [
                        "resolution" => [
                            "type" => "preapproval",
                            "block_instant_booking" => true,
                            //                    "accept" => true,
                        ]
                    ]);
            if ($response->successful()) {
                $response = $response->json();
                //                BookingInquiry::create(
//                    [
//                        'ch_inquiry_id' => $response['data']['attributes']['id'],
//                        'property_id' => $response['data']['attributes']['property_id'],
//                        'status' => $response['data']['attributes']['payload']['status'],
//                        'comment' => $response['data']['attributes']['payload']['comment'],
//                        'message_thread_id' => $response['data']['attributes']['payload']['message_thread_id'],
//                        'type' => $response['data']['attributes']['payload']['resolution']['type'],
//                        'total_price' => $response['data']['attributes']['payload']['resolution']['total_price'],
//                        'booking_details' => json_encode($response['data']['attributes']['payload']['booking_details']),
//                    ]
//                );
                $thread->update(['status' => $request->status, 'action_taken_at' => now()]);
                //            dd($response['data']['threads']);
                return response()->json(["message" => "Record updated!"], 200);
                return response()->json(1);
            } else {
                $error = $response->body();
                return response()->json(["error" => $error], 500);
                dd($error);
            }
        }

    }
   
  public function getNewRequests(Request $request) {
       
        $listingIds = Listing::whereJsonContains('user_id', strval($request->user_id))
            ->pluck('listing_id');

        $recentTime = Carbon::now()->subDay();
        
        $bookingRequests = BookingRequest::whereIn('listing_id', $listingIds)
            ->where('status', 'pending')
            ->where('created_at', '>=', $recentTime)
            ->orderBy('created_at', 'desc')
            // ->limit(5)
            ->get();
        
        $finalResults = [];
        $threadCollectionData = [];
        
        $requestThreadIds = [];
        foreach ($bookingRequests as $bRequest) {
            $listing = $bRequest->listing;
            $channel = Channels::where('id', $listing->channel_id)->first();
            if ($listing) {
                $bookingDetails = json_decode($bRequest->booking_json, true);
                $thread = Thread::where('ch_thread_id', $bRequest->message_thread_id)->first();
                $lastMessage = $thread->messages?->last();

                $requestThreadIds[] = $thread->id;
                if ($thread) {
                    $finalResults[] = [
                        "listing_id" => $bRequest->listing_id,
                        "thread_id" => $thread->id,
                        "live_feed_event_id" => $bRequest->live_feed_event_id,
                        "text" => !empty($lastMessage) ? $lastMessage->message_content : ($bRequest->guest_name . " wants to book " . $listing?->listing_name),
                        "type" => "new_booking_request",
                        "amount" => $bRequest->amount,
                        "source" => $bookingDetails['payload']['bms']['channel_name'],
                        "booking_request_id" => $bRequest->id,
                        "created_at" => $bRequest->created_at,
                        'guest_name' => $bRequest->guest_name,
                        'listing_name' => $listing?->listing_name,
                        'message_date' => !empty($lastMessage) ? Carbon::parse($lastMessage->message_date)->addHour(3)->format('M d, Y, g:i A') : Carbon::parse($bRequest->created_at)->subHour()->format('M d, Y, g:i A'),
                        'last_message_sender' => !empty($lastMessage) ? $lastMessage?->sender : 'guest',
                        'is_read' => $thread->is_read,
                        'is_starred' => $thread->is_starred,
                        'is_archived' => $thread->is_archived,
                        'unread_count' => $thread->unread_count,
                        'is_mute' => $thread->is_mute,
                        'ota_type' => $channel->connection_type == null ? 'airbnb' : strtolower($channel->connection_type),
                        'status' => $thread->status,
                        'thread_type' => $thread->thread_type,
                        // 'order_date' => $bRequest->created_at,
                        'order_date' => !empty($lastMessage) ? Carbon::parse($lastMessage->message_date)->addHour(3) : Carbon::parse($bRequest->created_at)->subHour()->format('M d, Y, g:i A'),
                        "status" => null,
                    ];
                }
            }
        }

        
        $threads = Thread::whereIn('listing_id', $listingIds)
            // ->with(['messages' => function ($query) {
            //     $query->latest()->limit(1);
            // }])
            // ->whereHas('messages', function ($query) {
            //     $query->latest()->limit(1);
            // })
            ->whereNotIn('id', $requestThreadIds)
            ->with(['messages' => function ($query) {
                    $query->orderBy('id', 'desc');
                }])
            ->whereHas('messages', function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->where('thread_type', 'inquiry')
            ->where('created_at', '>=', $recentTime)
            ->whereNull('action_taken_at');

            // ->limit(5)

        if ($request->others == 'is_read') {
            $threads = $threads->where('is_read', 0);
        } elseif ($request->others == 'is_starred') {
            $threads = $threads->where('is_starred', 1);
        } elseif ($request->others == 'is_archived') {
            $threads = $threads->where('is_archived', 1);
        } elseif ($request->others == 'is_mute') {
            $threads = $threads->where('is_mute', 1);
        }
        
        $threads = $threads->orderBy('created_at', 'desc')->get();

        $threadIds = $threads->pluck('id');
    
        foreach ($threads as $thread) {
            $listing = $thread->listing;
            $channel = Channels::where('id', $listing->channel_id)->first();
            
            
            if ($listing) {
                $details = json_decode($thread->booking_info_json, true);
                if ($thread) {
                    $adjustedTime = !empty($thread?->messages[0]) 
                        ? tap(Carbon::parse($thread?->messages[0]?->message_date), function ($date) use ($thread) {
                            if ($thread?->messages[0]?->sender !== 'channel') {
                                $date->addHour(3);
                            }
                        })->format('M d, Y, g:i A')
                        : Carbon::parse($thread?->created_at)->format('M d, Y, g:i A');
                    
                    $timeForOrder = !empty($thread?->messages[0]) 
                        ? tap(Carbon::parse($thread?->messages[0]?->message_date), function ($date) use ($thread) {
                            if ($thread?->messages[0]?->sender !== 'channel') {
                                $date->addHour(3);
                            }
                        })
                        : Carbon::parse($thread?->created_at);
                    
                    $finalResults[] = [
                        "listing_id" => $thread->listing_id,
                        "thread_id" => $thread->id,
                        "live_feed_event_id" => $thread->live_feed_event_id,
                        "text" => $thread->name . " is inquiring to book " . $listing?->listing_name,
                        "type" => "new_inquiry",
                        "amount" => $details['expected_payout_amount_accurate'] ?? null,
                        "source" => "AirBNB",
                        "created_at" => $thread->created_at,
                        'guest_name' => $thread->name,
                        'listing_name' => $listing?->listing_name,
                        'message_date' => $adjustedTime,
                        'last_message_sender' => !empty($thread?->messages[0]) ? $thread?->messages[0]?->sender : 'guest',
                        'is_read' => $thread->is_read,
                        'is_starred' => $thread->is_starred,
                        'is_archived' => $thread->is_archived,
                        'is_mute' => $thread->is_mute,
                          'unread_count' => $thread->unread_count,
                        'ota_type' => $channel->connection_type == null ? 'airbnb' : strtolower($channel->connection_type),
                        'status' => $thread->status,
                        'thread_type' => $thread->thread_type,
                        'order_date' => $timeForOrder,
                        "status" => $thread->status,
                    ];
                }
            }
        }
        
        $oldThreads = Thread::whereNotIn('id', array_merge($threadIds->toArray(), $requestThreadIds))
            ->whereIn('listing_id', $listingIds)
                ->with(['messages' => function ($query) {
                    $query->orderBy('id', 'desc');
                }])
            ->whereHas('messages', function ($query) {
                $query->orderBy('id', 'desc');
            });

        if ($request->others == 'is_read') {
            $oldThreads = $oldThreads->where('is_read', 0);
        } elseif ($request->others == 'is_starred') {
            $oldThreads = $oldThreads->where('is_starred', 1);
        } elseif ($request->others == 'is_archived') {
            $oldThreads = $oldThreads->where('is_archived', 1);
        } elseif ($request->others == 'is_mute') {
            $oldThreads = $oldThreads->where('is_mute', 1);
        }
        
        
        $oldThreads = $oldThreads->get();
        
        // $oldThreads = [];
        foreach ($oldThreads as $thread) {
            // if($thread->id == 2902) {
            //     return $thread?->messages[0]['message_date'];
            // }
            // $lastMessage = ThreadMessage::where('thread_id', $thread->id)->orderBy('id', 'desc')->first();
            $listing = $thread->listing;
            if ($listing) {
                $channel = Channels::where('id', $listing->channel_id)->first();
                $details = json_decode($thread->booking_info_json, true);
                
                if ($thread) {
                     $adjustedTime = !empty($thread?->messages[0]) 
                        ? tap(Carbon::parse($thread?->messages[0]['message_date']), function ($date) use ($thread) {
                            if ($thread?->messages[0]?->sender !== 'channel') {
                                $date->addHour(3);
                            }
                        })->format('M d, Y, g:i A')
                        : Carbon::parse($thread?->created_at)->format('M d, Y, g:i A');
                    
                    $timeForOrder = !empty($thread?->messages[0]) 
                        ? tap(Carbon::parse($thread?->messages[0]['message_date']), function ($date) use ($thread) {
                            if ($thread?->messages[0]?->sender !== 'channel') {
                                $date->addHour(3);
                            }
                        })
                        : Carbon::parse($thread?->created_at);

                    // if($thread->id == 2846) {
                    //     return $thread;
                    // }
                    // return $thread?->messages[0]?->sender !== 'channel';
                    $finalResults[] = [
                        "listing_id" => $thread->listing_id,
                        "thread_id" => $thread->id,
                        "live_feed_event_id" => $thread->live_feed_event_id,
                        // "text" => !empty($thread?->messages) && !empty($thread?->messages[0]) ? thread?->messages[0]?->message_content : $thread->last_message,
                        'text' => !empty($thread?->messages) && !empty($thread?->messages[0]) ? $thread?->messages[0]['message_content'] : $thread->last_message,
                        // 'text' => !empty($lastMessage) ? $lastMessage?->message_content : $thread->last_message,
                        "type" => $thread->thread_type,
                        "amount" => $details['expected_payout_amount_accurate'] ?? null,
                        "source" => "AirBNB",
                        "created_at" => $thread->created_at,
                        'guest_name' => $thread->name,
                        'listing_name' => $listing?->listing_name,
                        'message_date' => $adjustedTime,
                        'last_message_sender' => !empty($thread?->messages[0]) ? $thread?->messages[0]?->sender : 'guest',
                        'is_read' => $thread->is_read,
                        'is_starred' => $thread->is_starred,
                        'is_archived' => $thread->is_archived,
                        'is_mute' => $thread->is_mute,
                          'unread_count' => $thread->unread_count,
                        'ota_type' => $channel->connection_type == null ? 'airbnb' : strtolower($channel->connection_type),
                        'status' => $thread->status,
                        'thread_type' => $thread->thread_type,
                        'order_date' => $timeForOrder,
                        "status" => $thread->status,
                    ];
                }
            }
        }
    
        foreach ($finalResults as $key => $thread) {
            $listing = Listings::where('listing_id', $thread['listing_id'])->first();
            if(is_null($listing)) {
                continue;
            }

            if($request->has('trip_stage')) {
                if($thread['type'] != 'new_inquiry' && $thread['type'] != 'new_booking_request') {
                    $trip_stage = explode(',',$request->trip_stage);
                    if(in_array('pre_approval', $trip_stage)) {
                        if($thread['status'] == 'pre_approval' || $thread['status'] == 'preapproval'){
                            array_push($threadCollectionData, $thread);
                        }
                    }
                    if(in_array('upcomming_reseration', $trip_stage)) {
                        $thread_date = Carbon::now()->toDateString();
                        $booking = BookingOtasDetails::where('arrival_date','>', $thread_date)->where('listing_id',$listing->listing_id)->first();
                        if($booking) {
                            array_push($threadCollectionData, $thread);
                        }
                    }
                    if(in_array('currently_hosting', $trip_stage)) {
                        $thread_date = Carbon::parse($thread->message_date)->toDateString();
                        $booking = BookingOtasDetails::where('arrival_date','>', $thread_date)->where('departure_date','<', $thread_date)->where('listing_id',$listing->listing_id)->first();
                        if($booking) {
                            array_push($threadCollectionData, $thread);
                        }
                    }
                    if(in_array('past_reservation', $trip_stage)) {
                        $thread_date = Carbon::now()->toDateString();
                        $booking = BookingOtasDetails::where('arrival_date','<', $thread_date)->where('listing_id',$listing->listing_id)->first();
                        if($booking) {
                            array_push($threadCollectionData, $thread);
                        }

                    }
                }
                else {
                    array_push($threadCollectionData, $thread);
                }

            }
            else {
                    array_push($threadCollectionData, $thread);
                }
        }
        
        // usort($threadCollectionData, function($a, $b) {
        //     return $b['order_date'] <=> $a['order_date'];
        // });
        
        usort($threadCollectionData, function ($a, $b) {
            $dateA = Carbon::parse($a['message_date']);
            $dateB = Carbon::parse($b['message_date']);
        
            return $dateB->greaterThan($dateA) ? 1 : -1;
        });
    
        // return array_slice($finalResults, 0, 5);
        return $threadCollectionData;
    }    

    public function manageRequest(Request $request)
    {
        $validated = $request->validate([
            'thread_id' => 'required|exists:threads,id',
            // 'action_type' => 'required|string|in:special_offer,preapproval,accept_booking_request,decline_booking_request,special_offer_withdraw',
            'action_type' => 'required',
            'amount' => 'required_if:action_type,special_offer|numeric',
            'booking_request_id' => 'required_if:action_type,accept_booking_request,decline_booking_request|exists:booking_requests,id',
            "live_feed_event_id" => "required",
            // 'reason' => 'required_if:action_type,decline_booking_request|string'
        ]);
        
        $thread = Thread::findOrFail($request->thread_id);

        $apiUrl = env('CHANNEX_URL') . "/api/v1/live_feed/{$request->live_feed_event_id}/resolve";
        $headers = ['user-api-key' => env('CHANNEX_API_KEY')];

        
        if($request->action_type === 'special_offer_withdraw' || $request->action_type === 'special_offer_withdrawl')
        {
            $listing = $thread->listing;
            $channel = Channels::where('id', $listing->channel_id)->first();
            $specialOffer = BookingInquiry::where(['message_thread_id' => $thread->ch_thread_id])->first();
            if($specialOffer)
            {
                $data = [
                    'request' => [
                        'endpoint' => "/special_offers/$specialOffer->ch_inquiry_id/",
                        'method' => 'put',
                        'payload' => [
                            'update_type' => 'withdraw',
                        ],
                    ],
                ];


                $response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/action/api_proxy", $data);

                if($response->successful())
                {
                    ThreadMessage::create([
                        'thread_id' => $thread->id,
                        'sender' => "channel",
                        'message_content' => "Special offer has been withdrawn",
                        'message_date' => now()->addHour(3),
                        'message_type' => 'special_offer_withdraw'
                    ]);

                    $specialOffer->update(['has_withdrawn' => 1]);
                }
                else{
                    return response()->json(["error" => json_decode($response->body(), true)], 500);
                }
            }
        }
        
        if($request->action_type === 'decline_inquiry') {
            ThreadMessage::create([
                        'thread_id' => $thread->id,
                        'sender' => "channel",
                        'message_content' => "Inquiry has been declined",
                        'message_date' => now()->addHour(3),
                        'message_type' => 'decline_inquiry'
                    ]);
            $thread->update(['status' => $request->action_type, 'action_taken_at' => now()]);
            return response()->json(["message" => "Record updated!", "success" => true], 200);
        }
        
        switch ($request->action_type) {
            case 'special_offer':
                $payload = [
                    "resolution" => [
                        "type" => "special_offer",
                        "total_price" => $request->amount
                    ]
                ];
                break;

            case 'preapproval':
                $payload = [
                    "resolution" => [
                        "type" => "preapproval",
                        "block_instant_booking" => true
                    ]
                ];
                break;

            case 'accept_booking_request':
                $payload = [
                    "resolution" => [
                        "accept" => true
                    ]
                ];
                break;

            case 'decline_booking_request':
                $payload = [
                    "resolution" => [
                        "accept" => false,
                        "reason" => $request->reason ?? null
                    ]
                ];
                break;

            default:
                return response()->json(["error" => "Invalid action type"], 400);
        }

        $response = Http::withHeaders($headers)->post($apiUrl, $payload);

        if ($response->successful()) {
            $responseData = $response->json();

            if ($request->action_type === 'special_offer') {
                BookingInquiry::create([
                    'ch_inquiry_id' => $responseData['data']['attributes']['id'],
                    'property_id' => $responseData['data']['attributes']['property_id'],
                    'status' => $responseData['data']['attributes']['payload']['status'],
                    'comment' => $responseData['data']['attributes']['payload']['comment'],
                    'message_thread_id' => $responseData['data']['attributes']['payload']['message_thread_id'],
                    'type' => $responseData['data']['attributes']['payload']['resolution']['type'],
                    'total_price' => $responseData['data']['attributes']['payload']['resolution']['total_price'],
                    'booking_details' => json_encode($responseData['data']['attributes']['payload']['booking_details']),
                ]);
                ThreadMessage::create([
                    'thread_id' => $thread->id,
                    'sender' => "channel",
                    'message_content' => "Special Offer sent",
                    'message_date' => now()->addHour(3),
                    'message_type' => 'special_offer'
                ]);
            }

            if (in_array($request->action_type, ['accept_booking_request', 'decline_booking_request'])) {
                $bookingRequest = BookingRequest::findOrFail($request->booking_request_id);
                $bookingRequest->update([
                    'status' => $request->action_type === 'accept_booking_request' ? 'accepted' : 'declined'
                ]);

                if($request->action_type === 'accept_booking_request')
                {
                    ThreadMessage::create([
                        'thread_id' => $thread->id,
                        'sender' => "channel",
                        'message_content' => $thread->name. "'s booking request has been accepted",
                        'message_date' => now()->addHour(3),
                        'message_type' => 'accept_booking_request'
                    ]);
                }
                else{
                    ThreadMessage::create([
                        'thread_id' => $thread->id,
                        'sender' => "channel",
                        'message_content' => $thread->name. "'s booking request has been declined",
                        'message_date' => now()->addHour(3),
                        'message_type' => 'decline_booking_request'
                    ]);
                }
            }

            $thread->update(['status' => $request->action_type, 'action_taken_at' => now()]);
            return response()->json(["message" => "Record updated!", "success" => true], 200);
        } else {
            return $response;
            return response()->json(["error" => json_decode($response->body(), true)], 500);
        }
    }

    public function getBookingDetailsByThreadId($thread_id, $type)
    {
        $thread = Thread::find($thread_id);
        if (!$thread) {
            return response()->json(["error" => "Thread not found!"], 400);
        }

        $listing = $thread->listing;
        if (!$listing) {
            return response()->json(["error" => "Listing not found!"], 400);
        }
        
        // if(empty($type)) {
        //     $type = $thread->thread_type;
        // }
        
        // if ($type == 'new_booking_request') {
            $booking = BookingRequest::where('message_thread_id', $thread->ch_thread_id)->first();
            if (!$booking) {
                 $booking = json_decode($thread->booking_info_json, true);
                $firstMessage = $thread->messages?->first();
                $data = [];
                $checkinDate = Carbon::parse($booking['checkin_date']);
                $checkoutDate = Carbon::parse($booking['checkout_date']);
                
                // Calculate the total number of days
                $totalDays = $checkinDate->diffInDays($checkoutDate);
    
                $data['listing_id'] = $thread->listing_id;
                $data['guest_name'] = $booking['guest_name'] ?? 'Unknown';
                $data['listing_name'] = $booking['listing_name'] ?? 'Unknown';
                $data['number_of_adults'] = $booking['number_of_adults'] ?? 0;
                $data['number_of_children'] = $booking['number_of_children'] ?? 0;
                $data['number_of_infants'] = $booking['number_of_infants'] ?? 0;
                $data['number_of_pets'] = $booking['number_of_pets'] ?? 0;
                $data['total_guests'] = 
                    ($booking['number_of_adults'] ?? 0) + 
                    ($booking['number_of_children'] ?? 0) + 
                    ($booking['number_of_infants'] ?? 0) + 
                    ($booking['number_of_pets'] ?? 0);
                $data['checkin_date'] = $booking['checkin_date'];
                $data['checkout_date'] = $booking['checkout_date'];
                $data['total_nights'] = $totalDays;
                $data['amount'] = $booking['expected_payout_amount_accurate'] ?? 0;
                $data['total_amount'] = $booking['expected_payout_amount_accurate'] ?? 0;
                $data['currency'] = $booking['currency'] ?? 'SAR';
                
                // $data['expires_in'] = Carbon::parse($thread['created_at'])->addDay();
                // $data['expires_in'] = $firstMessage ? (Carbon::parse($firstMessage->message_date)->addHour(3)->addDay()->format('H:i')) : Carbon::parse($thread->created_at)->format('H:i');
                
                $currentUTCPlus3 = Carbon::now('UTC')->addHours(3);
                $expiresInTime = $firstMessage ? Carbon::parse($firstMessage->message_date)->addHours(3)->addDay() : Carbon::parse($thread->created_at);
                $remainingSeconds = $currentUTCPlus3->diffInSeconds($expiresInTime, false);
                if ($remainingSeconds > 0) {
                    $remainingHours = floor($remainingSeconds / 3600); // Total hours
                    $remainingMinutes = floor(($remainingSeconds % 3600) / 60); // Remaining minutes
                    $data['expires_in'] = "$remainingHours:$remainingMinutes";
                } else {
                    $data['expires_in'] = "Expired";
                }
    
                return $data;
                
                return response()->json(["error" => "Booking not found!"], 400);
            }

            $bookingJson = json_decode($booking->booking_json, true);
            if (!$bookingJson || !isset($bookingJson['payload']['bms'])) {
                return response()->json(["error" => "Booking details not found!"], 400);
            }

            $details = $bookingJson['payload']['bms'];
            $guestDetails = $details['raw_message']['reservation']['guest_details'] ?? [];

            $bookingData = [];
            $bookingData['listing_id'] = $listing->listing_id ?? null;
            $bookingData['is_guest_verified'] = $booking->is_guest_verified ?? false;
            $bookingData['guest_name'] = $booking->guest_name ?? 'Unknown';
            $bookingData['listing_name'] = $listing->listing_name ?? 'Unknown';
            
            $bookingData['number_of_adults'] = $guestDetails['number_of_adults'] ?? 0;
            $bookingData['number_of_children'] = $guestDetails['number_of_children'] ?? 0;
            $bookingData['number_of_infants'] = $guestDetails['number_of_infants'] ?? 0;
            $bookingData['number_of_pets'] = $guestDetails['number_of_pets'] ?? 0;

            $bookingData['total_guests'] = 
                ($guestDetails['number_of_adults'] ?? 0) + 
                ($guestDetails['number_of_children'] ?? 0) + 
                ($guestDetails['number_of_infants'] ?? 0) + 
                ($guestDetails['number_of_pets'] ?? 0);
            $bookingData['checkin_date'] = $details['arrival_date'];
            $bookingData['checkout_date'] = $details['departure_date'];
            $bookingData['amount'] = $details['amount'] ?? 0;
            $bookingData['currency'] = $details['currency'] ?? 'SAR';

            $bookingData['guests'] = $details['guests'][0] ?? [];
            $bookingData['guest_details'] = $guestDetails;
            $bookingData['services'] = $details['rooms'][0]['services'] ?? [];

            $bookingData['total_nights'] = $details['raw_message']['reservation']['nights'] ?? 0;
            $bookingData['total_amount'] = $details['amount'] ?? 0;
            
            // $bookingData['expires_in'] = Carbon::parse($booking['created_at']);
            
            $currentUTCPlus3 = Carbon::now('UTC')->addHours(3);
            $expiresInTime = Carbon::parse($booking['created_at'])->addDay();
            $remainingSeconds = $currentUTCPlus3->diffInSeconds($expiresInTime, false);
            
            if ($remainingSeconds > 0) {
                $remainingHours = floor($remainingSeconds / 3600);
                $remainingMinutes = floor(($remainingSeconds % 3600) / 60);
                $bookingData['expires_in'] = sprintf('%02d:%02d', $remainingHours, $remainingMinutes);
            } else {
                $bookingData['expires_in'] = 0;
            }

            return $bookingData;
        // }

        if($type == 'new_inquiry')
        {
            $booking = json_decode($thread->booking_info_json, true);
            $firstMessage = $thread->messages?->first();
            $data = [];
            $checkinDate = Carbon::parse($booking['checkin_date']);
            $checkoutDate = Carbon::parse($booking['checkout_date']);
            
            // Calculate the total number of days
            $totalDays = $checkinDate->diffInDays($checkoutDate);

            $data['listing_id'] = $thread->listing_id;
            $data['guest_name'] = $booking['guest_name'] ?? 'Unknown';
            $data['listing_name'] = $booking['listing_name'] ?? 'Unknown';
            $data['number_of_adults'] = $booking['number_of_adults'] ?? 0;
            $data['number_of_children'] = $booking['number_of_children'] ?? 0;
            $data['number_of_infants'] = $booking['number_of_infants'] ?? 0;
            $data['number_of_pets'] = $booking['number_of_pets'] ?? 0;
            $data['total_guests'] = 
                ($booking['number_of_adults'] ?? 0) + 
                ($booking['number_of_children'] ?? 0) + 
                ($booking['number_of_infants'] ?? 0) + 
                ($booking['number_of_pets'] ?? 0);
            $data['checkin_date'] = $booking['checkin_date'];
            $data['checkout_date'] = $booking['checkout_date'];
            $data['total_nights'] = $totalDays;
            $data['amount'] = $booking['expected_payout_amount_accurate'] ?? 0;
            $data['total_amount'] = $booking['expected_payout_amount_accurate'] ?? 0;
            $data['currency'] = $booking['currency'] ?? 'SAR';
            
            // $data['expires_in'] = Carbon::parse($thread['created_at'])->addDay();
            $data['expires_in'] = $firstMessage ? (Carbon::parse($firstMessage->message_date)->addHour(3)->addDay()->format('H:i')) : $thread->created_at;

            return $data;
        }

        return response()->json(["error" => "Something went wrong!"], 400);
    }

    public function fetchThreadsById($id)
    {
        $thread = Thread::findOrFail($id);
        $thread->unread_count = 0;
        $thread->is_read = 0;
        $thread->save();
        
        $listing = $thread->listing;
        $channel = Channels::where('id', $listing->channel_id)->first();
        $details = json_decode($thread->booking_info_json, true);
        $buttonsData = [];
        $chats = [];
        $review = [];
        $guestDetails = [
            "listing_name" => $listing->listing_name,
            "ota_type" => $channel->connection_type == null ? 'airbnb' : strtolower($channel->connection_type)
            ];
        $bookingJson = !empty($thread->booking_info_json) ? json_decode($thread->booking_info_json) : [];
        if(!empty($bookingJson)) {
        // dd($bookingJson->payload->bms->arrival_date);
            if(!empty($bookingJson?->checkin_date)) {
                $checkInDate = $bookingJson?->checkin_date;
            }
            else {
                $checkInDate = $bookingJson->payload->bms->arrival_date;
            }
            
            if(!empty($bookingJson?->checkout_date)) {
                $checkOutDate = $bookingJson?->checkout_date;
            }
            else {
                $checkOutDate = $bookingJson->payload->bms->departure_date;
            }
            
//             dd($checkInDate
// ,$checkOutDate,$thread->listing_id);
            $confirmedBooking = BookingOtasDetails::query()
            ->whereDate('arrival_date', $checkInDate)
            ->whereDate('departure_date', $checkOutDate)
            ->where([
                ['listing_id', $thread->listing_id]
                // ['status', 'confirmed'],
            ])
            ->first();
                // dd($confirmedBooking);
            if($confirmedBooking) {
                $review = Review::where('booking_id', $confirmedBooking->booking_id)->first();
                // dd($review);
            }
        }

      $isInquiry = false;

        // add inquiry message
        if($thread->thread_type == 'inquiry')
        {
            if (empty($thread->action_taken_at) && $thread->created_at->greaterThanOrEqualTo(Carbon::now()->subDay())) {
                $buttonsData = [
                    'thread_id' => $thread->id,
                    'type' => 'inquiry',
                    'live_feed_event_id' => $thread->live_feed_event_id,
                    'listing_name' => $thread->listing?->listing_name,
                ];
            }
            $firstMessage = $thread->messages?->first();
            
            array_push($chats, [
                'thread_id' => $thread->id,
                'sender' => 'channel',
                'guest_name' => $thread->name,
                'listing_name' => $thread->listing?->listing_name,
                'message_content' => "Inquiry Sent at ".Carbon::parse($thread->created_at)->format('F j, Y'),
                'message_date' => $firstMessage ? (Carbon::parse($firstMessage->message_date)->addHour(3)->subMinute()) : $thread->created_at,
                'message_type' => 'inquiry',
                'created' => Carbon::parse($thread->created_at)->toDateString()
            ]);
            $isInquiry = true;
        }

        //for booking request
        $bookingRequest = BookingRequest::where(['message_thread_id' => $thread->ch_thread_id, 'status' => 'pending'])->first();
        if(!$isInquiry && $bookingRequest && $bookingRequest->created_at->greaterThanOrEqualTo(Carbon::now()->subDay()))
        {
            $buttonsData = [
                'thread_id' => $thread->id,
                'type' => 'booking_request',
                'live_feed_event_id' => $bookingRequest->live_feed_event_id,
                'listing_name' => $thread->listing?->listing_name,
                'booking_request_id' => $bookingRequest->id
            ];
        }
        
        // for special offer withdraw
        $specialOffer = BookingInquiry::where(['message_thread_id' => $thread->ch_thread_id, 'has_withdrawn' => 0])->first();
        if($specialOffer)
        {

            $buttonsData = [
                'thread_id' => $thread->id,
                'type' => 'special_offer_withdraw',
                'live_feed_event_id' => $thread->live_feed_event_id,
                'listing_name' => $thread->listing?->listing_name,
                'special_offer_id' => $specialOffer->id,
            ];
        }

        // add preapproval message
        if(!empty($thread->action_taken_at) && $thread->status == 'preapproval')
        {
            $checkinDate = Carbon::parse($thread->checkin_date);
            $checkoutDate = Carbon::parse($thread->checkout_date);

            if ($checkinDate->isSameDay($checkoutDate)) {
                $formattedDate = $checkinDate->format('j M Y'); 
            } else {
                $formattedDate = $checkinDate->format('j') . '-' . $checkoutDate->format('j M Y');
            }

            array_push($chats, [
                'thread_id' => $thread->id,
                'sender' => 'channel',
                'message_content' => "Pre-approval sent - ".$details['number_of_guests']. " guests, ". $formattedDate,
                'message_date' => Carbon::parse($thread->action_taken_at)->addHour(3),
                'guest_name' => $thread->name,
                'listing_name' => $thread->listing?->listing_name,
                'message_type' => 'preapproval',
                'created' => Carbon::parse($thread->created_at)->toDateString()
            ]);
        }

        // add booking confirmation
        // if(!empty($bookingInquiryDetails['payload']['live_feed_event_id']))
        // {
        //     array_push($chats, [
        //         'thread_id' => $thread->id,
        //         'sender' => 'channel',
        //         'message_content' => $bookingInquiryDetails['payload']['customer_name']. " has confirmed the booking",
        //         'message_date' => $thread->created_at
        //     ]);
        // }

        foreach($thread->messages as $message)
        {
            array_push($chats, [
                'thread_id' => $thread->id,
                'sender' => $message->sender,
                'message_content' => $message->message_content,
                'message_date' => $message->sender != 'channel' ? Carbon::parse($message->message_date)->addHour(3) : Carbon::parse($message->message_date),
                'guest_name' => $thread->name,
                'listing_name' => $thread->listing?->listing_name,
                'message_type' => $message->message_type ?? 'message',
                'created' => Carbon::parse($thread->created_at)->toDateString()
            ]);
        }
        
        // Descending order
        // usort($chats, function($a, $b) {
        //     return $b['message_date'] <=> $a['message_date'];
        // });
        
        // Ascending Order
      


        if($review && $review?->id) {
            array_push($chats, [
                'thread_id' => $thread->id,
                'sender' => 'channel',
                'message_content' => $thread->name." has sent you the review.",
                'message_date' => $review->created_at,
                'guest_name' => $thread->name,
                'listing_name' => $thread->listing?->listing_name,
                'message_type' => 'review',
                'review_id' => $review->id,
                'created' => Carbon::parse($review->created_at)->toDateString()
            ]);
        }
        
        $requestBooking = BookingRequest::where('message_thread_id', $thread->ch_thread_id)->first();
        if(!empty($thread->booking_info_json) && !empty($threadDetails['payload']['bms']['arrival_date'])) {
            $threadDetails = json_decode($thread->booking_info_json, true);
            // dd($threadDetails['payload']['bms']['arrival_date']);
            $guestDetails['checkin_date'] = $threadDetails['payload']['bms']['arrival_date'];
            $guestDetails['checkout_date'] = $threadDetails['payload']['bms']['departure_date'];
        }
        else if($requestBooking) {
            $checkInDate = preg_replace('/\[[^\]]+\]/', '', $requestBooking->check_in_datetime);
            $guestDetails['checkin_date'] = Carbon::parse($checkInDate)->format('Y-m-d');
            
            $checkOutDate = preg_replace('/\[[^\]]+\]/', '', $requestBooking->check_out_datetime);
            $guestDetails['checkout_date'] = Carbon::parse($checkOutDate)->format('Y-m-d');
        }
        // dd($chats)
        
        //  usort($chats, function ($a, $b) {
        //         return strtotime($b['created']) - strtotime($a['created']);
        //     });
        
         usort($chats, function($a, $b) {
            return strtotime($b['message_date']) - strtotime($a['message_date']);
        });

        return [
            "details" => $guestDetails,
            "buttonsData" => $buttonsData,
            "chats" => $chats
        ];
    }
   
}

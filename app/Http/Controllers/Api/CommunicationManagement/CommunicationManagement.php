<?php

namespace App\Http\Controllers\Api\CommunicationManagement;

use App\Http\Controllers\Controller;
use App\Models\BookingOtasDetails;
use App\Models\Listings;
use App\Models\Thread;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class CommunicationManagement extends Controller
{
    public function fetchThreads(Request $request)
    {
        // dd($request->listings);
        $listingResponse = array();
        $user = Auth::user();
        $listings = Listings::all();
        foreach ($listings as $item) {
            $listing_details = $item->toArray();
            $user_arr = json_decode($listing_details['user_id']);
            if (in_array($user->id, $user_arr)) {
                $listing = json_decode($item->listing_json);
                $listing->is_sync = $item->is_sync;
                array_push($listingResponse, $listing);
            }
        }
        //        dd($listingResponse);
        $threadCollection = array();
        $threadCollectionData = array();

        $listingIds = [];
        foreach ($listingResponse as $item) {
            if($request->has('listings')) {
                $listingsReq = explode(',',$request->listings);
                $listingIds = $listingsReq;
            }else {
                array_push($listingIds, $item->id);
            }
        }
        if (!empty($listingIds)) {
            $placeholders = implode(',', array_fill(0, count($listingIds), '?'));
            $conditions = "threads.listing_id IN ($placeholders)";
            $bindings = $listingIds;

            if (isset($request->others)) {
                if ($request->others == 'is_read') {
                    $conditions .= " AND threads.is_read = 1";
                } elseif ($request->others == 'is_starred') {
                    $conditions .= " AND threads.is_starred = 1";
                }
                elseif ($request->others == 'is_archived') {
                    $conditions .= " AND threads.is_archived = 1";
                }
                elseif ($request->others == 'is_mute') {
                    $conditions .= " AND threads.is_mute = 1";
                }
            }

            // Final query with dynamic conditions
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
                    threads.is_read,
                    threads.is_starred,
                    threads.is_archived,
                    threads.is_mute,
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
                    $conditions
                ORDER BY
                    threads_messages.created_at DESC;
            ", $bindings);
        }else {
            $threads = [];
        }
        // dd($threads);
          foreach ($threads as $key => $thread) {
                $listing = Listings::where('listing_id', $thread->listing_id)->first();
                if(is_null($listing)) {
                    continue;
                }
                $listing_json = json_decode($listing->listing_json);

                // if($thread->status == 'pre_approval') {
                //     dd($thread);
                // }
                if($thread->thread_type == 'message') {
                    // dd($thread);
                    if($request->has('trip_stage')) {
                        $trip_stage = explode(',',$request->trip_stage);
                        if(in_array('pre_approval', $trip_stage)) {
                            // dd($thread);
                            if($thread->status == 'pre_approval'){
                                $threadCollection['id'] = $thread->id;
                                $threadCollection['listing_name'] = $listing_json->title;
                                $threadCollection['listing_id'] = $listing->listing_id;
                                $threadCollection['name'] = $thread->name;
                                $threadCollection['last_message'] = $thread->last_message;
                                $threadCollection['message_date'] = $thread->message_date;
                                $threadCollection['thread_type'] = $thread->thread_type == null ? 'booking_request' : $thread->thread_type;
                                $threadCollection['is_read'] = $thread->is_read;
                                $threadCollection['is_starred'] = $thread->is_starred;
                                $threadCollection['is_archived'] = $thread->is_archived;
                                $threadCollection['is_mute'] = $thread->is_mute;
                                $threadCollection['unread_count'] = 2;
                                $threadCollection['ota_type'] = 'airbnb';
                                $threadCollection['status'] = $thread->status;

                                array_push($threadCollectionData, $threadCollection);
                            }
                        }
                        if(in_array('upcomming_reseration', $trip_stage)) {
                            $thread_date = Carbon::now()->toDateString();
                            // $thread_date = Carbon::parse($thread->message_date)->toDateString();
                            $booking = BookingOtasDetails::where('arrival_date','>', $thread_date)->where('listing_id',$listing->listing_id)->first();
                            if($booking) {
                                $threadCollection['id'] = $thread->id;
                                $threadCollection['listing_name'] = $listing_json->title;
                                $threadCollection['listing_id'] = $listing->listing_id;
                                $threadCollection['name'] = $thread->name;
                                $threadCollection['last_message'] = $thread->last_message;
                                $threadCollection['message_date'] = $thread->message_date;
                                $threadCollection['thread_type'] = $thread->thread_type == null ? 'booking_request' : $thread->thread_type;
                                $threadCollection['is_read'] = $thread->is_read;
                                $threadCollection['is_starred'] = $thread->is_starred;
                                $threadCollection['is_archived'] = $thread->is_archived;
                                $threadCollection['is_mute'] = $thread->is_mute;
                                $threadCollection['unread_count'] = 2;
                                $threadCollection['ota_type'] = 'airbnb';
                                $threadCollection['status'] = $thread->status;
                                array_push($threadCollectionData, $threadCollection);
                            }

                            // dd($trip_stage);
                        }
                        if(in_array('currently_hosting', $trip_stage)) {
                            // dd($trip_stage);
                            // $thread_date = Carbon::now()->toDateString();
                            $thread_date = Carbon::parse($thread->message_date)->toDateString();
                            $booking = BookingOtasDetails::where('arrival_date','>', $thread_date)->where('departure_date','<', $thread_date)->where('listing_id',$listing->listing_id)->first();
                            if($booking) {
                                $threadCollection['id'] = $thread->id;
                                $threadCollection['listing_name'] = $listing_json->title;
                                $threadCollection['listing_id'] = $listing->listing_id;
                                $threadCollection['name'] = $thread->name;
                                $threadCollection['last_message'] = $thread->last_message;
                                $threadCollection['message_date'] = $thread->message_date;
                                $threadCollection['thread_type'] = $thread->thread_type == null ? 'booking_request' : $thread->thread_type;
                                $threadCollection['is_read'] = $thread->is_read;
                                $threadCollection['is_starred'] = $thread->is_starred;
                                $threadCollection['is_archived'] = $thread->is_archived;
                                $threadCollection['is_mute'] = $thread->is_mute;
                                $threadCollection['unread_count'] = 2;
                                $threadCollection['ota_type'] = 'airbnb';
                                $threadCollection['status'] = $thread->status;
                                array_push($threadCollectionData, $threadCollection);
                            }
                        }
                        if(in_array('past_reservation', $trip_stage)) {
                            $thread_date = Carbon::now()->toDateString();
                            // $thread_date = Carbon::parse($thread->message_date)->toDateString();
                            $booking = BookingOtasDetails::where('arrival_date','<', $thread_date)->where('listing_id',$listing->listing_id)->first();
                            if($booking) {
                                $threadCollection['id'] = $thread->id;
                                $threadCollection['listing_name'] = $listing_json->title;
                                $threadCollection['listing_id'] = $listing->listing_id;
                                $threadCollection['name'] = $thread->name;
                                $threadCollection['last_message'] = $thread->last_message;
                                $threadCollection['message_date'] = $thread->message_date;
                                $threadCollection['thread_type'] = $thread->thread_type == null ? 'booking_request' : $thread->thread_type;
                                $threadCollection['is_read'] = $thread->is_read;
                                $threadCollection['is_starred'] = $thread->is_starred;
                                $threadCollection['is_archived'] = $thread->is_archived;
                                $threadCollection['is_mute'] = $thread->is_mute;
                                $threadCollection['unread_count'] = 2;
                                $threadCollection['ota_type'] = 'airbnb';
                                $threadCollection['status'] = $thread->status;
                                array_push($threadCollectionData, $threadCollection);
                            }

                        }
                    }else {

                        $threadCollection['id'] = $thread->id;
                        $threadCollection['listing_name'] = $listing_json->title;
                        $threadCollection['listing_id'] = $listing->listing_id;
                        $threadCollection['name'] = $thread->name;
                        $threadCollection['last_message'] = $thread->last_message;
                        $threadCollection['message_date'] = $thread->message_date;
                        $threadCollection['thread_type'] = $thread->thread_type == null ? 'booking_request' : $thread->thread_type;
                        $threadCollection['is_read'] = $thread->is_read;
                        $threadCollection['is_starred'] = $thread->is_starred;
                        $threadCollection['is_archived'] = $thread->is_archived;
                        $threadCollection['is_mute'] = $thread->is_mute;
                        $threadCollection['unread_count'] = 2;
                        $threadCollection['ota_type'] = 'airbnb';
                        $threadCollection['status'] = $thread->status;
                        array_push($threadCollectionData, $threadCollection);
                    }

                }
            }
        return response()->json($threadCollectionData);
    }

    public function is_read(Thread $thread)
    {
        $thread->update(['is_read' => !$thread->is_read]);
        return response()->json($thread);
    }
    public function is_starred(Thread $thread)
    {
        $thread->update(['is_starred' => !$thread->is_starred]);
        return response()->json($thread);
    }
    public function is_archived(Thread $thread)
    {
        $thread->update(['is_archived' => !$thread->is_archived]);
        return response()->json($thread);
    }
    public function is_mute(Thread $thread)
    {
        $thread->update(['is_mute' => !$thread->is_mute]);
        return response()->json($thread);
    }
}

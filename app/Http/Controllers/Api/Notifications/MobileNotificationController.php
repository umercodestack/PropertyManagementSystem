<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Models\MobileNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Listing;
use App\Models\Bookings;

class MobileNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
 public function index()
    {
        $user_id = Auth::user()->id;
        // $bookings = Bookings::whereYear('created_at', 2025)
        //           ->whereMonth('created_at', 3)
        //           ->get();
        //         // dd($bookings);
        //         foreach($bookings as $item) {
        //             $listing = Listing::where('id', $item->listing_id)->first();
        //             $listing_json = json_decode($listing->listing_json);
        //             $listing_name = $listing_json->title;
        //             MobileNotification::create([
        //                 'listing_id' => $listing->listing_id,
        //                 'booking_id' => $item->id,
        //                 'ota_type' => 'livedin',
        //                 'type' => 'booking',
        //                 'price' => $item->total_price,
        //                 'notification_label' => $item->name.' has booked '.$listing_name,
        //                 'status' => 'unread',
        //                 'booking_dates' => $item->booking_date_start.' to '.$item->booking_date_end,
        //                 'listing_name' => $listing_name,
        //                 'created_at' =>$item->created_at
        //             ]);
        //             // dd($item);

        //         }
        //         dd('die');
        $listingIds = Listing::whereJsonContains('user_id', strval($user_id))
            ->pluck('listing_id');
    
        $notifications = MobileNotification::whereIn('listing_id', $listingIds)
            ->orderBy('created_at', 'desc')
            ->get();
    
        $timezone = 'Asia/Riyadh'; // Adjust to your actual timezone
    
        $transformedNotifications = $notifications->map(function ($notification) use ($timezone) {
            // Convert UTC time to local timezone
            $time = Carbon::parse($notification->created_at)->setTimezone($timezone);
            return [
                'notification_id' => $notification->id,
                'booking_id' => $notification->booking_id,
                'review_id' => $notification->review_id,
                'ota_type' => $notification->ota_type,
                'notification_type' => $notification->type,
                'price' => isset($notification->price) && $notification->price == 0 ? null : $notification->price,
                'notification_label' => $notification->notification_label,
                'time' => $time,
                'status' => $notification->status,
                'booking_dates' => $notification->booking_dates,
                'listing_property_name' => $notification->listing_name,
                'original_created_at' => $notification->created_at
            ];
        })->groupBy(function ($notification) use ($timezone) {
            $date = Carbon::parse($notification['time'])->setTimezone($timezone)->startOfDay();
            $today = Carbon::today($timezone);
            
            if ($date->equalTo($today)) {
                return 'Today';
            } elseif ($date->equalTo($today->copy()->subDay())) {
                return 'Yesterday';
            }
            return $date->format('d M Y');
        })->sortKeysUsing(function ($keyA, $keyB) use ($timezone) {
            $priority = ['Today' => 1, 'Yesterday' => 2];
            $priorityA = $priority[$keyA] ?? 3;
            $priorityB = $priority[$keyB] ?? 3;
    
            if ($priorityA === $priorityB && $priorityA === 3) {
                return Carbon::parse($keyB, $timezone)->gt(Carbon::parse($keyA, $timezone)) ? 1 : -1;
            }
            return $priorityA - $priorityB;
        })->map(function ($group, $title) {
            return [
                'title' => $title,
                'data' => $group->sortByDesc('time')->values(),
            ];
        })->values();
    
        return response($transformedNotifications);
    }
    
    public function getUnreadCount()
    {
        $user_id = Auth::user()->id;
         $listingIds = Listing::whereJsonContains('user_id', strval($user_id))
            ->pluck('listing_id');
            // return response($listingIds);
            // dd($listingIds);
        $unreadNotifications = MobileNotification::whereIn('listing_id',$listingIds)->where('status', 'unread')->get();
        return response([
            'unread' => count($unreadNotifications)
            ]);
    }
    public function markAllAsRead()
    {
        MobileNotification::query()->update(['status' => 'read']);

        return response()->json([
            'message' => 'All notifications marked as read',
            'data' => $this->index()
        ], 200);
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
    public function show(MobileNotification $mobileNotification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MobileNotification $mobileNotification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MobileNotification $mobileNotification)
    {
        $mobileNotification->update([
            'status' => $request->status
        ]);
        return response($mobileNotification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MobileNotification $mobileNotification)
    {
        //
    }
}

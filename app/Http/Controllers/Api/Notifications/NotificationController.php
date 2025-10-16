<?php

namespace App\Http\Controllers\Api\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\RoleNotificationResource;
use App\Models\AdminNotification;
use App\Models\Notifications;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use App\Utilities\UserUtility;
use App\Services\MixpanelService; 
use App\Models\NotificationM;
use App\Models\NotificationType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BookingOtasDetails;
use Carbon\Carbon;

class NotificationController extends Controller
{
    
    private $mixpanelService;
    public function __construct(MixpanelService $mixpanelService)
    {
        //  $this->middleware('permission');
        $this->mixpanelService = $mixpanelService;
    }
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $notifications = Notifications::all();
        
        return NotificationResource::collection($notifications);
    }

    public function getNotifications()
    {
        // $notifications = Notifications::all();
           
        $notifications = Notifications::where([
            'is_checked' => 0,
            'event' => 'message'
        ])->paginate(10);
        

        $notificationIds = $notifications->pluck('id');

        Notifications::whereIn('id', $notificationIds)->update(['is_checked' => 1]);

        return NotificationResource::collection($notifications);
    }

    /**
     * @param Request $request
     * @return NotificationResource|JsonResponse
     */
    public function store(Request $request): NotificationResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'notification_detail' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        return new NotificationResource(Notifications::create($request->all()));
    }

    /**
     * @param User $user
     * @return AnonymousResourceCollection
     */
    public function show(User $user): AnonymousResourceCollection
    {
        $notifications = Notifications::where('user_id', $user->id)->get();
        
          $userDB = User::find($user->id);
        if (!empty($userDB->role_id) && $userDB->role_id === 2) {
       
            try {

                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();
             

                $this->mixpanelService->trackEvent('Notifications Module Opened', [
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
        
        return NotificationResource::collection($notifications);
    }

    public function countUnread()
    {
        $unreadCount = Notifications::where([
            'is_checked' => 0, 
            'event' => 'message'
            ])->count();

        $adminCount = AdminNotification::where('is_checked', 0)->count();
        return response()->json(['count' => $adminCount+$unreadCount]);
    }

    public function getAdminNotifications()
    {
        $notifications = AdminNotification::where('is_checked', 0)->get();
        
        AdminNotification::whereIn('id', $notifications->pluck('id'))->update(['is_checked' => 1]);

        return $notifications;
    }

    public function create()
    {
        return view('Admin.notifications.create');
    }

    public function storeAdminNotification(Request $request)
    {
        $request->validate([
            'message' => 'required',
        ]);

        AdminNotification::create($request->all());

        return redirect()->back()->with('success', 'Notification has been sent!');
    }


    public function getNotificationsByRoleCount()
    {
        $user = Auth::user();
    
        if ($user && !empty($user->role_id)) {
            $notifications = NotificationM::whereHas('notificationType', function ($query) use ($user) {
                $query->whereJsonContains('role_ids', $user->role_id);
            })
            ->where('is_seen_by_all', 0)
            ->where('created_at', '>=', Carbon::now()->subDays(5))
            //  ->where('status', '=', null)
            ->orderBy('created_at', 'desc')
            ->get();
    
            return response()->json($notifications);
        }
    
        return response()->json(['message' => 'No notifications found for this role.'], 404);
    }


    public function getNotificationsByRole()
    {
        $user = Auth::user();

        if ($user && !empty($user->role_id)) {
            $notifications = NotificationM::whereHas('notificationType', function ($query) use ($user) {
                    $query->whereJsonContains('role_ids', $user->role_id);
                })
                ->where('created_at', '>=', Carbon::now()->subDays(5))
                // ->where('status', '=', null)
                ->orderBy('is_seen_by_all', 'asc')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
    
            return RoleNotificationResource::collection($notifications)->response();
        }
    
        return response()->json(['message' => 'No notifications found for this role.'], 404);
    }
    
    // public function getNotificationsByRoleData(Request $request)
    // {
    //     $user = Auth::user();
    
    //     // Get the perPage parameter and search term
    //     $perPage = $request->get('perPage', 10);
    //     $searchTerm = $request->get('search', '');
    
    //     if ($user && !empty($user->role_id)) {
    //         // Query with dynamic search for all columns
    //         $notificationsQuery = NotificationM::whereHas('notificationType', function ($query) use ($user) {
    //             $query->whereJsonContains('role_ids', $user->role_id);
    //         })
    //         ->when($searchTerm, function ($query) use ($searchTerm) {
    //             $query->where(function($query) use ($searchTerm) {
    //                 $query->where('message', 'like', '%' . $searchTerm . '%')
    //                       ->orWhere('created_at', 'like', '%' . $searchTerm . '%');
    //             });
    //         })
    //         ->orderBy('is_seen_by_all', 'asc')
    //         ->orderBy('created_at', 'desc');
    
    //         // Get the total record count (before pagination)
    //         $totalRecords = $notificationsQuery->count();
    
    //         // Paginate the results
    //         $notifications = $notificationsQuery->paginate($perPage);
    
    //         // Return view with notifications, search term, and total record count
    //         return view('Admin.notifications.index', compact('notifications', 'searchTerm', 'totalRecords'));
    //     }
    
    //     return redirect()->back()->with('error', 'No notifications found for this role.');
    // }


    public function getNotificationsByRoleData(Request $request)
    {
        $user = Auth::user();
        $page = $request->input('page', 1); 
        $rowsPerPage = $request->input('rowsPerPage', 10);
        $search = $request->input('search', '');
        
        
        $notifications = NotificationM::where('message', 'like', "%$search%")
            ->orderBy('created_at', 'desc')  
            ->paginate($rowsPerPage);
     
        return response()->json([
            'notifications' => $notifications->items(),  
            'totalEntries' => $notifications->total(), 
            'totalPages' => $notifications->lastPage(),  
        ]);
    }

    public function getNotificationsByRoleView(Request $request)
    {
        return view('Admin.notifications.index');
    }

    public function markAsSeen($id)
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
    
        $notification = NotificationM::find($id);
    
        if ($notification) {
            
            $notification->is_seen_by_all = 1;
            $notification->save();
    
            
            DB::table('notification_user_seen')->updateOrInsert(
                ['notification_id' => $notification->id, 'user_id' => $user->id],
                ['seen_at' => Carbon::now()]
            );
    
            return response()->json(['success' => true]);
        }
    
        return response()->json(['success' => false], 404);
    }


    public function updateStatus(Request $request, $id)
    {
        $notification = NotificationM::where('module_id', $id)->first();
       
        $OtaBooking = BookingOtasDetails::where('id', $id)->first();




        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
        }

        if (!$OtaBooking) {
            return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
        }

        $OtaBooking->status = $request->status;
        $OtaBooking->system_status = $request->status;
        $OtaBooking->save();


        $notification->status = $request->status; 
        $notification->save();

        return response()->json(['success' => true]);
    }

}
 
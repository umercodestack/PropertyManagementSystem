<?php

use App\Models\User;
use App\Models\Thread;
use App\Models\BookingRequest;
use App\Models\BookingOtasDetails;
use App\Models\NotificationM;

use App\Services\FirebaseService;
use Illuminate\Support\Facades\{
    DB
};

if (!function_exists('sendLiteNotification')) {
    function sendLiteNotification($userId, $title, $message, $data_id=0, $notify_type='chats_view') {

        $firebase_service = app(FirebaseService::class);

        $user = User::find($userId);
        if(!is_null($user) && !empty($user->fcmTokens) && $user->host_type_id == 1){ // for lite user only
            
            $user_notifications = DB::table('user_send_notifications')->where(
                [
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'send_date' => date('Y-m-d')
                ]
            )->first();

            if(is_null($user_notifications)){
                
                DB::table('user_send_notifications')->insert(
                    [
                        'user_id' => $user->id,
                        'title' => $title,
                        'message' => $message,
                        'send_date' => date('Y-m-d')
                    ]
                );
                
                $data = [
                    'id' => $data_id,
                    'otaName' => $notify_type == 'livedin_bk_detail' ? 'livedin' : 'airbnb',
                    'type' => $notify_type == 'livedin_bk_detail' ? 'booking_detail' : $notify_type //$notify_type //'chats_view',
                ];
            
                foreach($user->fcmTokens as $token)
                {
                    if(!empty($token->fcm_token)){
                        try{
                            $firebase_response = $firebase_service->sendPushNotification($token->fcm_token, $title, $message, '', $data);
                        } catch(\Exception $ex){
                            logger("Notification Error: " . $ex->getMessage());
                        }
                    }
                }
            }
        }
    }
}

if (!function_exists('getThreadIDbyBookingOtaFDt')) {
    function getThreadIDbyBookingOtaFDt($booking_id){
        
        $bookingDb = BookingOtasDetails::where('id', $booking_id)->first();
        
        if(!is_null($bookingDb)){
            
            $thread = Thread::where('listing_id', $bookingDb->listing_id)
            ->whereJsonContains('booking_info_json->checkin_date', $bookingDb->arrival_date)
            ->whereJsonContains('booking_info_json->checkout_date', $bookingDb->departure_date)
            ->orderBy('id', 'DESC')
            ->first();

            if(!is_null($thread)){
                return $thread->id;
            }
            
            $bookingRequest = BookingRequest::where('listing_id', $bookingDb->listing_id)
            ->whereDate('check_in_datetime', $bookingDb->arrival_date)
            ->whereDate('check_out_datetime', $bookingDb->departure_date)
            ->orderBy('id', 'DESC')
            ->first();
            
            if(!is_null($bookingRequest)){
                
                $fthread = Thread::where('ch_thread_id', $bookingRequest->message_thread_id)
                ->orderBy('id', 'DESC')
                ->first();
                
                if(!is_null($fthread)){
                    return $fthread->id;
                }
            }
        }
        
        return 0;
    }
}


if (!function_exists('notifyheader')) {
    /**
     * Create a notification record
     *
     * @param int $notificationTypeId
     * @param string $moduleName (e.g., "hostaboard", "booking")
     * @param \Illuminate\Database\Eloquent\Model|int $moduleItem Model or ID
     * @param string|null $title
     * @param string|null $message
     * @param string|null $url
     * @param bool $isSeenByAll
     * @return bool
     */
    function notifyheader(
        int $notificationTypeId,
        string $moduleName,
        $moduleItem,
        ?string $title = null,
        ?string $message = null,
        ?string $url = null,
        bool $isSeenByAll = false
    ): bool {
        try {
            $moduleId = is_object($moduleItem) ? $moduleItem->id : $moduleItem;
            $generatedTitle = $title ?? 'New ' . ucfirst($moduleName) . ' Notification';
            $generatedMessage = $message ?? (is_object($moduleItem) && isset($moduleItem->title) ? $moduleItem->title : $generatedTitle);
            $generatedUrl = $url ?? url("/{$moduleName}/{$moduleId}/edit");

            NotificationM::create([
                'notification_type_id' => $notificationTypeId,
                'module' => ucfirst($moduleName),
                'module_id' => $moduleId,
                'title' => $generatedTitle,
                'message' => $generatedMessage,
                'url' => $generatedUrl,
                'is_seen_by_all' => $isSeenByAll,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error("Notification Failed for module [$moduleName]: " . $e->getMessage());
            return false;
        }
    }
}



if (!function_exists('apiResponse')) {
    function apiResponse($status = 'success', $message = '', $data = null, $errors = null, $httpCode = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'errors' => $errors,
        ], $httpCode);
    }
}

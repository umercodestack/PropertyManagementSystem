<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FirebaseService
{
    protected $firebase;

    public function __construct()
    {
        $credentialsPath = public_path('/notification_json/firebase_credentials.json');

        // Ensure that credentials path is set and file exists
        if (!$credentialsPath || !file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials file not found at path: {$credentialsPath}");
        }
        
        // print_r($credentialsPath);die;

        // Create messaging instance with the service account
        $this->firebase = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->createMessaging();
    }

    public function sendPushNotification($deviceToken, $title, $body, $type="NotDefined", $data=[])
    {
        $fcm_token = $deviceToken;
        if(!empty($deviceToken->fcm_token)){
            $fcm_token = $deviceToken->fcm_token;
        }

        // Validate device token
        if (empty($fcm_token)) {
            return response()->json(['error' => 'Device token is required']);
        }
        
        if($type == "NotDefined"){
            $type = $title;
        }
        
        $data['sound'] = 'default';

        $notification = Notification::create($title, $body);
        $message = CloudMessage::withTarget('token', $fcm_token)
                                ->withNotification($notification)
                                ->withData($data);
                                
                                // [
                                //     'sound' => 'default',
                                    
                                //     // 'id' => $id,
                                //     // 'otaName' => $ota_name,
                                //     // 'type' => $trigger_type,
                                    
                                //     // 'id' => 10,
                                //     // 'otaName' => 'livedin',
                                //     // 'type' => 'booking_detail',
                                    
                                //     // 'id' => 24,
                                //     // 'otaName' => '',
                                //     // 'type' => 'review_recieved',
                                    
                                //     // 'id' => 40,
                                //     // 'otaName' => '',
                                //     // 'type' => 'payment_received',
                                    
                                //     'id' => 24,
                                //     'otaName' => '',
                                //     'type' => 'payment_received',
                                // ]

        try {
            // Sending the push notification
            $send = $this->firebase->send($message);
            
            // logger($type.": Notification Send Successfully: " . json_encode($send));
            
        } catch (\Kreait\Firebase\Exception\MessagingException $e) {
            
            $error = $e->getMessage();
            if($error == "The registration token is not a valid FCM registration token" || $error == "Requested entity was not found." || $error == "SenderId mismatch"){

                logger($type.": New Fcm Token Deleted: " . json_encode($fcm_token));

                DB::table('user_tokens')
                ->where('fcm_token', $fcm_token)
                // ->update(['is_fcm_token_deleted'=>1]);
                ->delete();
            }
            
            logger($type.": Firebase Messaging Error: " . json_encode($e->getMessage()));
            
            return response()->json(['error' => 'Firebase Messaging Error: ' . $e->getMessage()]);
        } catch (\Exception $e) {
            // Catch other exceptions
            
            logger("Notification Exception Error: " . json_encode($e->getMessage()));
            
            return response()->json(['error' => 'Error: ' . $e->getMessage()]);
        }

        return response()->json(['status' => 'success', 'message' => 'Notification sent successfully']);
    }
}

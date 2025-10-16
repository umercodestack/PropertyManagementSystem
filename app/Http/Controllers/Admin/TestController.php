<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Bookings;

use Illuminate\Support\Facades\Storage;
use App\Services\FirebaseService;

//use Barryvdh\DomPDF\Facade as PDF;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Mail\BookingConfirmation;


use App\Mail\{
    CheckIn,
    CheckOut
};

class TestController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function sendPushNotification(Request $request)
    {
        
        try{
            
            if($request->filled(['fcm_token', 'title', 'text'])){
                
                // print_r($request->all());die;
                
                // $notificationData = [
                //     'id' => 4596,
                //     'otaName' => 'airbnb',
                //     'type' => 'chats_view' // booking_detail 24 | chats_view 4595
                // ];
                
                $notificationData = [
                    'id' => 4596,
                    'otaName' => 'airbnb',
                    'type' => 'chats_view',
                    'badge' => !empty($request->badge) ? $request->badge : 1
                ];
                
                $testsend = $this->firebaseService->sendPushNotification($request->fcm_token, $request->title, $request->text, '', $notificationData);
                print_r($testsend);
                die;
                
            }
            
            echo 'All fields are mandatory';die;
            
            // logger("Send Notification to my device");
            
            // $testtoken = "dk6hmEvfSHS2F6wTJKY0Od:APA91bEq1ovIxv5_Jg72UdDV6OUydvtc51FHW8nYdkhQWSu_onLb_Sp2P_Tu4faMCSOokFC5_8AoJTVEWJXYPimutAlvDFejR-z-y0W9G-JGaZiHSmO0Yhg";
            // $testtoken = "eiPlsdGKcUptm4tb6VMN7l:APA91bFxv2bQzBPnHT_-4Jpyt8ZuKKKNox23RLlGgz3aQrseNbVj9H8y9i95xqERT9awcI6M5gupjKTpKNPgI9e70lcODdAVQtbalp9d6t9oUCJkCEAoSFA";
            // $testtoken = "fMx1hYHB001GoEYoBRxhVv:APA91bE63uaOeSiPdnH8AwAW1u85oBmvnuILFCXqdyI5GJToVFECazrMTtdFTNLZSsbtDjCGqDct7mSr9XzOjR84D00-6RUlh31e-uCOi0vQxyPWr4kzlgk";
            
            // $testtoken = "cRMMtYKhTweWcJbFGk6CCB:APA91bHuuzYPZV8jqTR_-5EWZbZe18a5J41rWFbAdEjoH2Mw-nIrBPAO672IMSb9DSYTsRc3f9-RIHreMaVBbC73B46Wvlqg-faBLpwoQrGb0rs33uQExrI";
            
            
            $testtoken = "ftDS570500ovgaRwb7d1XC:APA91bEj-YePcKlHysLSucf_v792EZ4RHfoL4lmB1TCX4gUGbjfKXqbneBydTs-0diGvgB2OSv7B_1HA4mjOsPUxDvTOczGkcAIVyirEtSICd6j3i3WJv04";
            
            $customer_name = "Ahmed New";
            // $listing_name = "LP1.2 New Appartment";
            $listing_name = "LP64 - Thikra - Apt 7\u0628  \u00b7 Comfortable 1BR in Qurtubah";
            
            $arrival_date = $departure_date = date('Y-m-d');
            
            
            $title = "Booking Confirmed";
            $body = "Booking confirmed! " . $customer_name . " is staying at " . $listing_name . " from " . Carbon::parse($arrival_date)->format('j M Y') . " to " . Carbon::parse($departure_date)->format('j M Y') . ".";

            $testsend = $this->firebaseService->sendPushNotification($testtoken, $title, $body);
            
            logger("Test Notification Response: " . json_encode($testsend));
            
            print_r($testsend);
            
        } catch(\Exception $ex){
            
            logger("Test Notification Error: " . $ex->getMessage());
            
            print_r($ex->getMessage());die;
        }
        
        die;
        
        return false;
        // Send Notification
        try{
            
            // logger("Notification Device Triggerd");
            
            // $testtoken = 'd2ePWJ5TRcK5EeKoLhgunr:APA91bFFMMfWc4PbiB1hsJlFOlQjb2DV0QX3iN0tZnQ9x5CAdl7AKAr53NExJPxEkrRTK3Ld2xvamsHUTO1omxvnyDHgM6mPPzTsh4gzCtblfPiW3xhjkVY';
            
            
            // $testtoken = 'fX3g52teScOfU5Yn5GscGz:APA91bGhgCwOba6kP09ucM-odAyqj08UG7PJWwxsQFPSI-Ns3W4gIWd2hJ_dwyM2aMs13JjKURsa_xlGAGixaiyZWK9uM4tUOKIXA3VubNOTtGh9t-TKVk8';
            
            // Rajesh
            // $testtoken = 'cqCp1te_TnqPr3HOgVStHs:APA91bEbFZCoil7_OeylZdC_Nhxl8PYfeaBKj6sqRrWGelnpUxSVo0FuOe1fyRtAKXVvZO97N5KxpbH5RhgFpKQfpcmx4RYU3c33Ub6LYwcHJSQmGeCavts';
            
            // Riaz
            // $testtoken = 'fZDoilP8S5OQoGWPAJvWXD:APA91bH--8FR5RhxpRAnUXaVUHDEdkABOXYFHmbqwjc5KCeOaVm5LNj_vuOvw8zkx_lTJiDbm8h1slXO7J0lgBc0gk9SB5Hzb9fDD09lm2csSvBStEZacNM';
            
            // Basil
            $testtoken = 'd6BPe24HQXCnYQupFXkyEV:APA91bFL-fNF0P9_28_mGc9jcYKAMwMW8IPDFpGPfCNqzqgCULoiUDqMj-ac3owtskpbMqNtjmpW_gaRfGJ_BoGvvIYOpuh_D-nR9N8JIAd1wTDRu85ad54';

            
//                     $testsend =  $this->firebaseService->sendPushNotification($testtoken, "livedin test", "test is confirmed");
                    
//                     print_r($testsend);die;


// die;

            $testsend = $this->firebaseService->sendPushNotification($testtoken, "livedin 5", "Hi basil");
        } catch(\Exception $ex){
            print_r($ex->getMessage());die;
            // logger("Notification Error: " . $ex->getMessage());
        }
        
         print_r($testsend);die;
         die;               

        // $deviceToken = 'fByOqNOjRfK7QYhhgnQKcl:APA91bGrNdMFczvXDx94WiyoLf8T4bfKYz1mcI-P45Q48Xsp0dMs7zpf0ZcTkvr72fsL7xuX5tIbyNhGIz8YHy2K6immzpYFEpZI95ymt4Z2JPriqFRREoU';
        // $deviceToken = 'coyAMsmgSemKnEIXGp_b60:APA91bHHDbKeDjMiGgEu7aSqRYv71tJXcU0EsWm5lR70KstQ0wieJgvWY5KPXyrkXBGXe2E_7oFnxAA35GvJfxwga48pRwzFJJZ4AcVawiedk0W6yr4tSM4';
        // $deviceToken = 'e4ri5BpcT_WY8UkjZN0TTS:APA91bFLynMKEyVENIJ0Q7G83ngLEN1ujZusCkhyPzcTN7gzYXIWo5MnrkzjL_BgVg1BSKXQdOawl0QKbKGuVbILXS-nyDlRIQaCen49VLtvZ8Et4pSbWCY';
        // $deviceToken = 'f4oLIzJ3QnWxmtaaKpsggf:APA91bEQTs3MKgwH94FE9pVvLAXGF11Wdpihsl98Vub6O6I2tY2AoCW7mG9MbhZtA6XF4Q64BXyHm6J319xcxSoECYeNXNdXvVLyPHNFTjAvG1pNwFVyBoI';
        // $deviceToken = 'eyH9lXDcQG6vS0cfJQr_Ku:APA91bFkbJ9JdZQDMl19-21eNfocxgyR3Q_wB8Pj0lAnqU4O5xLdZnTS-zJBnBOrP2d3c9YrvGyJYpcZSzKyCic2ETCdUA05d2AXZWU3xRPcK1HIJ_d85Fg';
        // $deviceToken = 'eOcuOewyQi6g-Y0bLqBWDL:APA91bEAL2kPZcO10yYVeKQ5JUXgbs_qvBUmIrgSLjT4Qp0MuOX8FV0kSs53dagOU0AzhjAqJbaw_DVHEyKWasgNQJ6gmCUnXps2h6PRJyxjrRJZaovYt1o';
        $deviceToken = 'fcxbuzQr4E9agjyUy_3T3p:APA91bFb9PCirmjPpAKO0YgFzXd67DJ9PBfv4aKYbtzOrTaux9UUPYqPxa1l1_qQV_-irzaCuwY2u6jtBRmydK0csm1JRIl00Y0ZRqKFDsgD4pa3zkTAkQY';
        // $deviceToken = 'd_yDdvDlb0HprC8kHXO1mA:APA91bEwVemDM1d61b353B3RKQcJJmFu3hRl9cKZmtCpmtcDdhO709D0PhpSfF4RV8DNv23qVYCuVpKu2Ik8ycZoDd5nzFA5FBsd773ilLB8dLvN6Yq-B3s';
        $title = "Booking Confirmed 44";
        $body = "Thank you! Your booking has been confirmed";

        return $this->firebaseService->sendPushNotification($deviceToken, $title, $body);


        die;


        // $messaging = app('firebase.messaging');

        // $deviceToken = 'f3cCxhuDRou156tMBp_lDp:APA91bEazJ2_Fld4--QgLUPKc95LtUDEsiO8dAUOgbi9DJcWkqltQHGum88L9y6hFE7eNbRTc4f71hpq9xE4iEtkJN1bq0ijpBqIOUxenU-8wIYnJWfcw-g';

        // $message = CloudMessage::new()
        //     ->withTarget($deviceToken, 'token')  // 'token' for device token
        //     ->withNotification([
        //         'title' => 'Sample Notification',
        //         'body' => 'This is a test notification.'
        //     ]);

        // // Send the message
        // try {
        //     $messaging->send($message);
        //     echo 'Notification sent successfully.';
        // } catch (\Exception $e) {
        //     dd('Error sending notification: ' . $e->getMessage());
        // }

        // die;

        // $deviceToken = "f3cCxhuDRou156tMBp_lDp:APA91bEazJ2_Fld4--QgLUPKc95LtUDEsiO8dAUOgbi9DJcWkqltQHGum88L9y6hFE7eNbRTc4f71hpq9xE4iEtkJN1bq0ijpBqIOUxenU-8wIYnJWfcw-g";
        // $title = 'Test Notification';
        // $body = 'This is a test notification sent from Laravel!';

        
        // $user = User::find(1);
        // $user->notify(new FirebasePushNotification('Test Notification', 'This is a test notification'));
        
        // // $this->firebaseService->sendNotification($deviceToken, $title, $body);

        // return response()->json(['message' => 'Notification sent successfully']);
    }
    
    public function testNotification(){
        try{
            $request['listings'] = json_encode([8, 9]);
            $request['listings'] = json_decode($request['listings']);
            $listing_arr = Listing::whereIn('id',$request['listings'])->get();
            
            // print_r($request['listings']);die;
            
            $request['total'] = 121;
            $request['daterange'] = '2024-12-10';
            
            if(!empty($listing_arr)){
                $amount = $request['total'];
                $daterange = $request['daterange'];
                foreach($listing_arr as $listing){
                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
                    
                    if(!empty($user_ids_arr)){
                        
                        $title = "New Payment Recieved";
                        
                        // Need to change here
                           $body = "Good News! You've earned $amount for $daterange. Review it now!";
                           
                        //   echo $body . '\n';
                        //   print_r($user_ids_arr);die;
                
                        foreach($user_ids_arr as $user_id){
            
                            $user = User::find($user_id);
                            
                            // print_r($user);die;
            
                            if(!is_null($user) && !empty($user->fcmTokens)){
                                
                                $fcmtokens = [
                                    // 'eyH9lXAPA91bFkbJ9JdZQDMl19DcQG6vS0cfJQr_Ku',
                                    'eyH9lXDcQG6vS0cfJQr_Ku:APA91bFkbJ9JdZQDMl19-21eNfocxgyR3Q_wB8Pj0lAnqU4O5xLdZnTS-zJBnBOrP2d3c9YrvGyJYpcZSzKyCic2ETCdUA05d2AXZWU3xRPcK1HIJ_d85Fg'
                                ];
                                
                                foreach($fcmtokens as $token)
                                {
                                    try{
                                        
                                        //  print_r($token);die;
                                        
                                        $send = $this->firebaseService->sendPushNotification($token, $title, $body);
                                        
                                        echo $send . '\n';
                                        
                                    } catch(\Exception $ex){
                                        logger("Notification Error: " . $ex->getMessage());
                                    }
                                    
                                    // echo 'DONE 1';die;
                                }
                                echo 'DONE 3';die;
                            }
                            echo 'DONE 2';die;
                        }
                    }
                }
            }
        } catch(\Exception $ex){
            print_r($ex->getMessage());
        }
    }
    
    
    
    public function testemailcheckin(){
        
        try{
            
            $booking = Bookings::find(1687);

            $listing = Listing::where('id', $booking->listing_id)->first();
            if(is_null($listing)){
                return false;
            }
            
            $emailData = $booking;
            
            $emailData['listing_id'] = $listing->listing_id;
            $emailData['be_listing_name'] = substr($listing->be_listing_name, 0, 8)."...";
            $emailData['is_self_check_in'] = $listing->is_self_check_in;
            $emailData['district'] = $listing->district;
            $emailData['city_name'] = $listing->city_name;
            $emailData['google_map'] = $listing->google_map;
            $emailData['discounts'] = $listing->discounts;
            $emailData['tax'] = $listing->tax;
            
            $emailData['view_property_link'] = "https://booking.livedin.co/property_detail?listing_id=".$listing->listing_id;
            
            $start = Carbon::parse($booking->booking_date_start);
            $end = Carbon::parse($booking->booking_date_end);
            
            $total_nights = $start->diffInDays($end);
            
            $emailData['checkin_date'] = $start->format('jS') . ' ' . $start->format('M Y');
            $emailData['checkout_date'] = $end->format('jS') . ' ' . $end->format('M Y');
            
            $emailData['total_nights'] = $total_nights;
            $emailData['total_nights_txt'] = $total_nights == 1 ? "1 night" : $total_nights." nights";
            
            //$pdf = Facade::loadView('pdf.CheckInPdf', $emailData);
            //$fileName = time() . '_checkin.pdf';
            //Storage::put('public/' . $fileName, $pdf->output());

        
            //$pdfUrl = Storage::url($fileName);
            
            $emailData['pdf_url'] = "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf"; 

      
            Mail::to('pawanmatani01@gmail.com')->send(new CheckIn($emailData));
            
            echo 'Email send';
        
        } catch (TransportException $e) {
            echo $e->getMessage();
        }
        
    }
    
    public function testemailcheckout(){
        
        try{
            
            $booking = Bookings::find(1687);

            $listing = Listing::where('id', $booking->listing_id)->first();
            if(is_null($listing)){
                return false;
            }
            
            $emailData = $booking;
            
            $emailData['listing_id'] = $listing->listing_id;
            $emailData['be_listing_name'] = substr($listing->be_listing_name, 0, 8)."...";
            $emailData['is_self_check_in'] = $listing->is_self_check_in;
            $emailData['district'] = $listing->district;
            $emailData['city_name'] = $listing->city_name;
            $emailData['google_map'] = $listing->google_map;
            $emailData['discounts'] = $listing->discounts;
            $emailData['tax'] = $listing->tax;
            
            $emailData['view_property_link'] = "https://booking.livedin.co/property_detail?listing_id=".$listing->listing_id;
            
            $start = Carbon::parse($booking->booking_date_start);
            $end = Carbon::parse($booking->booking_date_end);
            
            $total_nights = $start->diffInDays($end);
            
            $emailData['checkin_date'] = $start->format('jS') . ' ' . $start->format('M Y');
            $emailData['checkout_date'] = $end->format('jS') . ' ' . $end->format('M Y');
            
            $emailData['total_nights'] = $total_nights;
            $emailData['total_nights_txt'] = $total_nights == 1 ? "1 night" : $total_nights." nights";
            
            //$pdf = Facade::loadView('pdf.CheckOutPdf', $emailData);
            //$fileName = time() . '_checkin.pdf';
            //Storage::put('public/' . $fileName, $pdf->output());

        
            //$pdfUrl = Storage::url($fileName);
            
            $emailData['pdf_url'] = "https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf"; 
            
            Mail::to('pawanmatani01@gmail.com')->send(new CheckOut($emailData));
            
            echo 'Email send';
        
        } catch (TransportException $e) {
            echo $e->getMessage();
        }
        
    }
}

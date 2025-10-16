<?php

namespace App\Console\Commands;

use App\Models\Thread;
use App\Models\ThreadMessage;
use App\Models\Listing;
use App\Models\Bookings;
use App\Models\BookingOtasDetails;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;

use App\Mail\{
    CheckIn,
    CheckOut
};

class SendReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // For Follow-up notifications
        $threadsWithPendingReplies = Thread::with(['messages' => function ($query) {
            $query->latest()->limit(1);
        }])->whereHas('messages', function ($query) {
            $query->latest()->limit(1)
                ->where('sender', 'guest')
                // Get only 15 minutes replies
                ->whereBetween('created_at', [Carbon::now()->subMinutes(15), Carbon::now()]);
        })->where('is_notification_sent', 0)->get();

        if(!empty($threadsWithPendingReplies) && count($threadsWithPendingReplies) > 0){
            foreach ($threadsWithPendingReplies as $thread) {
                $lastMessage = $thread->messages->first();
                $listing = Listing::where("listing_id", $thread->listing_id)->first();

                if($listing && !is_null($lastMessage) && $lastMessage->sender === 'guest') {
                    
                    Thread::where('id', $thread->id)->update(['is_notification_sent'=>1]);
                    
                    $message = "{$thread->name} is waiting for your response! Respond soon to keep your response rate high!";
                    
                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
                    foreach($user_ids_arr as $user_id){
                        if(!empty($user_id)){
                            sendLiteNotification($user_id, "Follow-ups", $message, $thread->id);
                            Log::info("now: ".Carbon::now()."last msg...". $lastMessage);
                        }
                    }
                    
                }
            }
        }

        $this->sendCheckInReminder();
        $this->sendCheckOutReminder();
        $this->sendHousePreparationReminder();
        
        $this->sendCheckInAndCheckOutEmail();

    }

    private function sendCheckInAndCheckOutEmail(){
        
        try{
            $saudiaTime = Carbon::now('Asia/Riyadh');
        
            // Checkin Email
            if ($saudiaTime->hour == 3) { // 3AM
                
                $currentDate = Carbon::now('Asia/Riyadh')->format('Y-m-d');
                
                $bookings = Bookings::where('booking_sources', '=' , 'booking_engine')
                ->where('booking_status', '!=' , 'cancelled')
                ->where('is_email_sent', 0)
                ->where('booking_date_start', '=', $currentDate)
                ->get();
            
                if(!empty($bookings)){
                    foreach($bookings as $booking){
                        
                        $booking->is_email_sent = 1;
                        $booking->save();
                        
                        $send = $this->sendEmail($booking, "CheckIn");
                    }
                }
            }
            
            // Checkout Email
            if ($saudiaTime->hour == 9) { // 9AM
                
                $currentDate = Carbon::now('Asia/Riyadh')->format('Y-m-d');
                
                $bookings = Bookings::where('booking_sources', '=' , 'booking_engine')
                ->where('booking_status', '!=' , 'cancelled')
                ->where('is_email_sent', 0)
                ->where('booking_date_end', '=', $currentDate)
                ->get();
            
                if(!empty($bookings)){
                    foreach($bookings as $booking){
                        
                        $booking->is_email_sent = 1;
                        $booking->save();
                        
                        $send = $this->sendEmail($booking, "CheckOut");
                    }
                }
            }
        } catch(\Exception $ex){
            //
        }
    }
    
    private function sendEmail($booking, $type){
        
        if(is_null($booking) || empty($type)){
            return false;
        }
        
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
        
        if($type == "CheckIn"){
            
            $emailData['pdf_url'] =  "https://admin.livedin.co/checkinpdf?booking_id=$booking->id";
            Mail::to($booking->email)->send(new CheckIn($emailData));
        }
        
        if($type == "CheckOut"){
            
            $emailData['pdf_url'] =  "https://admin.livedin.co/checkoutpdf?booking_id=$booking->id";
            Mail::to($booking->email)->send(new CheckOut($emailData));
        }
    }
    
    private function sendCheckInReminder()
    {
        $bookings = BookingOtasDetails::where('status', '!=', 'cancelled')->where('is_notification_sent', 0)->where('arrival_date', '=', Carbon::now()->format('Y-m-d'))->get();
        foreach ($bookings as $booking) {
            
            // $gtthrdid = getThreadIDbyBookingOtaFDt($booking->id);
            
            $bookingJson = json_decode($booking->booking_otas_json_details, true);

            $rawMessage = json_decode($bookingJson['attributes']['raw_message']);
            
            // Log::info("TestCheckinResponse: ".json_encode($rawMessage));
            
            $checkInDatetime = !empty($rawMessage->reservation->check_in_datetime) ? $rawMessage->reservation->check_in_datetime : '';
            $cleanedDatetime = preg_replace('/\[[^\]]+\]/', '', $checkInDatetime);
            $checkInCarbon = Carbon::parse($cleanedDatetime);

            $nowGmtPlus3 = Carbon::now('GMT+3')->addHour();
            if (!empty($checkInDatetime) && $checkInCarbon->format('Y-m-d H:i') === $nowGmtPlus3->format('Y-m-d H:i')) {
                $listing = Listing::where('listing_id', $booking->listing_id)->first();
                if(!is_null($listing)) {
                    $guestName = $bookingJson['attributes']['customer']['name'] ?? 'Unknown';
                    
                    $listingName = '';
                    if(!is_null($listing)){
                        $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                        $listingName = !empty($listing_json->title) ? $listing_json->title : '';
                    }

                    $checkInDate = Carbon::parse($booking->arrival_date)->format('M d, Y');
                    $numberOfNights = $rawMessage->reservation->nights;

                    $message = "{$guestName} will be checking in at {$listingName} today ({$checkInDate}) for {$numberOfNights} nights. Make their arrival special!";
                    
                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
                    
                    if(!empty($user_ids_arr)){
                        $booking->is_notification_sent = 1;
                        $booking->save();
                    }
                    
                    foreach($user_ids_arr as $user_id){
                        if(!empty($user_id)){ 
                            sendLiteNotification($user_id, "Check-in Reminder", $message, $booking->id, 'booking_detail');
                        }
                    }
                }
            }
        }
    }

    private function sendCheckOutReminder() 
    {
        $bookings = BookingOtasDetails::where('status', '!=', 'cancelled')->where('is_notification_sent', 0)->where('departure_date', '=', Carbon::now()->format('Y-m-d'))->get();

        foreach ($bookings as $booking) {
            
            // $gtthrdid = getThreadIDbyBookingOtaFDt($booking->id);
            
            $bookingJson = json_decode($booking->booking_otas_json_details, true);

            $rawMessage = json_decode($bookingJson['attributes']['raw_message']);
            
            // Log::info("TestCheckoutResponse: ".json_encode($rawMessage));
            
            $checkOutDatetime = !empty($rawMessage->reservation->check_out_datetime) ? $rawMessage->reservation->check_out_datetime : '';
            $cleanedDatetime = preg_replace('/\[[^\]]+\]/', '', $checkOutDatetime);
            $checkOutCarbon = Carbon::parse($cleanedDatetime);

            $nowGmtPlus3 = Carbon::now('GMT+3')->addHour();
            if (!empty($checkOutDatetime) && $checkOutCarbon->format('Y-m-d H:i') === $nowGmtPlus3->format('Y-m-d H:i')) {
                $listing = Listing::where('listing_id', $booking->listing_id)->first();
                if($listing) {
                    $guestName = $bookingJson['attributes']['customer']['name'] ?? 'Unknown';
                    
                    $listingName = '';
                    if(!is_null($listing)){
                        $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                        $listingName = !empty($listing_json->title) ? $listing_json->title : '';
                    }
                    
                    $checkOutDate = Carbon::parse($booking->departure_date)->format('M d, Y');
                    $numberOfNights = $rawMessage->reservation->nights;
    
                    $message = "{$guestName} will be checking out at {$listingName} today ({$checkOutDate}) for {$numberOfNights} nights."; // Make their arrival special!";
                    
                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
                    
                    if(!empty($user_ids_arr)){
                        $booking->is_notification_sent = 1;
                        $booking->save();
                    }
                    
                    foreach($user_ids_arr as $user_id){
                        if(!empty($user_id)){ 
                            sendLiteNotification($user_id, "Check-out Reminder", $message, $booking->id, 'booking_detail');
                        }
                    }
                }
            }
        }
    }

    private function sendHousePreparationReminder() 
    {
        $bookings = BookingOtasDetails::where('status', '!=', 'cancelled')->where('is_notification_sent', 0)->where('arrival_date', '=', Carbon::now()->format('Y-m-d'))->get();

        foreach ($bookings as $booking) {
            
            // $gtthrdid = getThreadIDbyBookingOtaFDt($booking->id);

            $bookingJson = json_decode($booking->booking_otas_json_details, true);
            
            $rawMessage = json_decode($bookingJson['attributes']['raw_message']);
            
            // Log::info("sendHousePreparationReminder Response: ".json_encode($rawMessage));
            
            
            $checkInDatetime = !empty($rawMessage->reservation->check_in_datetime) ? $rawMessage->reservation->check_in_datetime : '';
            $cleanedDatetime = preg_replace('/\[[^\]]+\]/', '', $checkInDatetime);
            $checkInCarbon = Carbon::parse($cleanedDatetime);

            $nowGmtPlus3 = Carbon::now('GMT+3')->addHour();
            if (!empty($checkInDatetime) && $checkInCarbon->format('Y-m-d H:i') === $nowGmtPlus3->format('Y-m-d H:i')) {
                $listing = Listing::where('listing_id', $booking->listing_id)->first();
                if($listing) {
                    $guestName = $bookingJson['attributes']['customer']['name'] ?? 'Unknown';

                    $checkInDate = Carbon::parse($booking->arrival_date)->format('M d, Y');
                    $numberOfNights = $rawMessage->reservation->nights;

                    $message = "{$guestName} will be arriving on ({$checkInDate}). Make sure your space is clean, cozy, and ready to welcome them! ";

                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];

                    if(!empty($user_ids_arr)){
                        $booking->is_notification_sent = 1;
                        $booking->save();
                    }
                    
                    foreach($user_ids_arr as $user_id){
                        if(!empty($user_id)){ 
                            sendLiteNotification($user_id, "House Preparation Reminder", $message, $booking->id, 'booking_detail');
                        }
                    }
                }
            }

        }
    }
    
    
    
    
}

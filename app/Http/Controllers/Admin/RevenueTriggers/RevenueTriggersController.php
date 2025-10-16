<?php

namespace App\Http\Controllers\Admin\RevenueTriggers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Auth;

use Illuminate\Support\Facades\{
    Http,
    Log,
    DB
};

use App\Models\{
    Listing,
    Bookings,
    ChurnedProperty,
    BookingOtasDetails,
    Properties,
    RatePlan,
    RoomType,
    Calender
};

class RevenueTriggersController extends Controller
{
    
    // public function __construct()
    // {
    //     $this->middleware('permission');
    // }
    
    public function index()
    {
        $data = [];

        $startOfWeek = Carbon::now('Asia/Riyadh')->addWeek()->startOfWeek();  // Start of next week (Monday)
        $endOfWeek = Carbon::now('Asia/Riyadh')->addWeek()->endOfWeek();      // End of next week (Sunday)
        
        $startOfMonth = Carbon::now('Asia/Riyadh')->addMonth()->startOfMonth(); // 1st day of the next month
        $endOfMonth = Carbon::now('Asia/Riyadh')->addMonth()->endOfMonth();   // Last day of the next month
        
        $currentDate = Carbon::now('Asia/Riyadh')->format('Y-m-d');
        
        $threePmKsaTime = '15:00:00'; // 3 PM KSA time
        $sixPmKsaTime = '18:00:00'; // 6 PM KSA time
        $eightPmKsaTime = '20:00:00'; // 8 PM KSA time
        
        // echo $currentDate;die;
        
        $ksaTime = Carbon::now('Asia/Riyadh');
        
        $is_week_day = $is_weekend_day = true;
        $startCurrentWeek = Carbon::now('Asia/Riyadh');
        
        // Weekdays only MONDAY-1 | TUESDAY-2 | WEDNESDAY-3 | THURSDAY-4 | SUNDAY-7
        if($startCurrentWeek->dayOfWeek != 5 && $startCurrentWeek->dayOfWeek != 6){
            $is_week_day = true;
        }
        
        // Weekends only FRIDAY-5 | SATURDAY-6
        if($startCurrentWeek->dayOfWeek == 5 || $startCurrentWeek->dayOfWeek == 6){
            $is_weekend_day = true;
        }

        // ********************* DAILY TRIGGERS STARTS *********************

        if(empty($_GET['trigger']) ||  $_GET['trigger'] == "daily_trigger"){
            
            if($is_weekend_day && in_array($ksaTime->hour, [20, 21])){ // 8 PM KSA time
                $data['listings_without_booking_on_weekends_eight_pm'] = $this->get_listings_without_bookings($eightPmKsaTime, $currentDate, 'reduce', 'alldaysweek', 15, 0);
            }
            
            else if($is_weekend_day && in_array($ksaTime->hour, [18, 19])){ // 6 PM KSA time
                $data['listings_without_booking_on_weekends_six_pm'] = $this->get_listings_without_bookings($sixPmKsaTime, $currentDate, 'reduce', 'alldaysweek', 10, 0);
            }
            
            else if($is_week_day && in_array($ksaTime->hour, [15, 16, 17])){ // 3 PM KSA time $ksaTime->hour >= 15
                $data['listings_without_booking_on_weekdays_three_pm'] = $this->get_listings_without_bookings($threePmKsaTime, $currentDate, 'reduce', 'alldaysweek', 5, 0);
            }
            
        }
        // print_r($data);die;

        // ********************* DAILY TRIGGERS ENDS *********************
        
        
        $totalDaysInMonth = Carbon::now('Asia/Riyadh')->daysInMonth;
        $thirty_percent = round(0.3 * $totalDaysInMonth);
        $fifty_percent = round(0.5 * $totalDaysInMonth);
        $seventy_percent = round(0.7 * $totalDaysInMonth);
        
        
        $where = ['is_sync'=>'sync_all', 'is_churned'=>0];
        if(!empty($_GET['listing_id'])){
            $where['listing_id'] = $_GET['listing_id'];
        }

        $listings = Listing::where($where)->select(['id', 'listing_id as listing_id', 'listing_json->title as jsn_listing_name'])->get();
        foreach ($listings as $listing) {
            
            // $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
            // $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
            
            // ********************* NEXT MONTH BOOKINGS STARTS *********************
            
            if(empty($_GET['trigger']) || $_GET['trigger'] == "next_month_trigger"){
            
                $monthly_bookings = BookingOtasDetails::where('listing_id', $listing->listing_id)
                ->whereNotNull('status')
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('arrival_date', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('departure_date', [$startOfMonth, $endOfMonth])
                    ->orWhere(function($query) use ($startOfMonth, $endOfMonth) {
                        $query->where('arrival_date', '<=', $startOfMonth)
                        ->where('departure_date', '>=', $endOfMonth);
                    });
                })
                ->get();

                $totalOccupiedDaysOfMonth = 0;
                foreach ($monthly_bookings as $mbooking) {
                    
                    $startDate = max($mbooking->arrival_date, explode(' ', $startOfMonth)[0]);
                    $endDate = min($mbooking->departure_date, explode(' ', $endOfMonth)[0]);
    
                    $start_date_arr = explode(' ', $startDate);
                    $start_date = !empty($start_date_arr[0]) ? $start_date_arr[0] : $startDate;
            
                    $checkinDate = Carbon::parse($start_date);
                    $checkoutDate = Carbon::parse($endDate);
                    
                    if ($checkinDate->lt($checkoutDate)) {
                        $occupiedDays = $checkinDate->diffInDays($checkoutDate);
                        $totalOccupiedDaysOfMonth += $occupiedDays;
                    }
                }
                
                
                // Direct Bookings Monthly Include
                $monthly_bookings2 = Bookings::where('listing_id', $listing->id)
                ->whereNotNull('booking_status')
                ->where('booking_status', '!=', 'cancelled')
                ->where(function($query) use ($startOfMonth, $endOfMonth) {
                    $query->whereBetween('booking_date_start', [$startOfMonth, $endOfMonth])
                    ->orWhereBetween('booking_date_end', [$startOfMonth, $endOfMonth])
                    ->orWhere(function($query) use ($startOfMonth, $endOfMonth) {
                        $query->where('booking_date_start', '<=', $startOfMonth)
                        ->where('booking_date_end', '>=', $endOfMonth);
                    });
                })
                ->get();
                
                foreach ($monthly_bookings2 as $mbooking2) {
                    
                    $startDate = max($mbooking2->booking_date_start, explode(' ', $startOfMonth)[0]);
                    $endDate = min($mbooking2->booking_date_end, explode(' ', $endOfMonth)[0]);
    
                    $start_date_arr = explode(' ', $startDate);
                    $start_date = !empty($start_date_arr[0]) ? $start_date_arr[0] : $startDate;
            
                    $checkinDate = Carbon::parse($start_date);
                    $checkoutDate = Carbon::parse($endDate);
                    
                    if ($checkinDate->lt($checkoutDate)) {
                        $occupiedDays = $checkinDate->diffInDays($checkoutDate);
                        $totalOccupiedDaysOfMonth += $occupiedDays;
                    }
                }
                
                
                if(!empty($totalOccupiedDaysOfMonth)){
                    
                    // Occupancy-30% | 10% -> (30/100) * total_days_in_next_month
                    if ($totalOccupiedDaysOfMonth >= $thirty_percent && $totalOccupiedDaysOfMonth < $fifty_percent) {
                        $data['monthly']['thirty_percent'][] = (object) ['listing_id'=>$listing->listing_id, 'jsn_listing_name'=>$listing->jsn_listing_name, 'occupied_days'=>$totalOccupiedDaysOfMonth, 'price_type'=>'increase', 'days_type'=>'next_month', 'percent'=>10];
                    }
                    
                    // Occupancy-50% | 30% -> (50/100) * total_days_in_next_month
                    if ($totalOccupiedDaysOfMonth >= $fifty_percent && $totalOccupiedDaysOfMonth < $seventy_percent) {
                        $data['monthly']['fivety_percent'][] = (object) ['listing_id'=>$listing->listing_id, 'jsn_listing_name'=>$listing->jsn_listing_name, 'occupied_days'=>$totalOccupiedDaysOfMonth, 'price_type'=>'increase', 'days_type'=>'next_month', 'percent'=>30];
                    }
                    
                    // Occupancy-70% | 50% -> (70/100) * total_days_in_next_month
                    if ($totalOccupiedDaysOfMonth >= $seventy_percent) {
                        $data['monthly']['seventy_percent'][] = (object) ['listing_id'=>$listing->listing_id, 'jsn_listing_name'=>$listing->jsn_listing_name, 'occupied_days'=>$totalOccupiedDaysOfMonth, 'price_type'=>'increase', 'days_type'=>'next_month', 'percent'=>50];
                    }
                }
            }
            
            // ********************* NEXT MONTH BOOKINGS ENDS *********************


            // ********************* NEXT WEEK BOOKINGS STARTS *********************
            
            if(empty($_GET['trigger']) || $_GET['trigger'] == "next_week_trigger"){

                $KSAStartOfWeek = Carbon::now('Asia/Riyadh')->addWeek()->startOfWeek()->subDay();
                $KSAEndOfWeek = Carbon::now('Asia/Riyadh')->addWeek()->endOfWeek()->subDay();
                
                $weekly_bookings = BookingOtasDetails::where('listing_id', $listing->listing_id)
                ->whereNotNull('status')
                ->where('status', '!=', 'cancelled')
                ->where(function($query) use ($KSAStartOfWeek, $KSAEndOfWeek) {
                    $query->whereBetween('arrival_date', [$KSAStartOfWeek, $KSAEndOfWeek])
                    ->orWhereBetween('departure_date', [$KSAStartOfWeek, $KSAEndOfWeek])
                    ->orWhere(function($query) use ($KSAStartOfWeek, $KSAEndOfWeek) {
                        $query->where('arrival_date', '<=', $KSAStartOfWeek)
                        ->where('departure_date', '>=', $KSAEndOfWeek);
                    });
                })
                ->get();

    
                $totalOccupiedDays = 0;
                foreach ($weekly_bookings as $wbooking) {
                    
                    $startDate = max($wbooking->arrival_date, explode(' ', $KSAStartOfWeek)[0]);
                    $endDate = min($wbooking->departure_date, explode(' ', $KSAEndOfWeek)[0]);
    
                    $start_date_arr = explode(' ', $startDate);
                    $start_date = !empty($start_date_arr[0]) ? $start_date_arr[0] : $startDate;
            
                    $checkinDate = Carbon::parse($start_date);
                    $checkoutDate = Carbon::parse($endDate);
                    
                    if ($checkinDate->lt($checkoutDate)) {
                        $occupiedDays = $checkinDate->diffInDays($checkoutDate);
                        $totalOccupiedDays += $occupiedDays;
                    }
                }
                
                
                // Direct Bookings Weekly Include
                $weekly_bookings2 = Bookings::where('listing_id', $listing->id)
                ->whereNotNull('booking_status')
                ->where('booking_status', '!=', 'cancelled')
                ->where(function($query) use ($KSAStartOfWeek, $KSAEndOfWeek) {
                    $query->whereBetween('booking_date_start', [$KSAStartOfWeek, $KSAEndOfWeek])
                    ->orWhereBetween('booking_date_end', [$KSAStartOfWeek, $KSAEndOfWeek])
                    ->orWhere(function($query) use ($KSAStartOfWeek, $KSAEndOfWeek) {
                        $query->where('booking_date_start', '<=', $KSAStartOfWeek)
                        ->where('booking_date_end', '>=', $KSAEndOfWeek);
                    });
                })
                ->get();
                
                foreach ($weekly_bookings2 as $wbooking2) {
                    
                    $startDate = max($wbooking2->booking_date_start, explode(' ', $KSAStartOfWeek)[0]);
                    $endDate = min($wbooking2->booking_date_end, explode(' ', $KSAEndOfWeek)[0]);
    
                    $start_date_arr = explode(' ', $startDate);
                    $start_date = !empty($start_date_arr[0]) ? $start_date_arr[0] : $startDate;
            
                    $checkinDate = Carbon::parse($start_date);
                    $checkoutDate = Carbon::parse($endDate);
                    
                    if ($checkinDate->lt($checkoutDate)) {
                        $occupiedDays = $checkinDate->diffInDays($checkoutDate);
                        $totalOccupiedDays += $occupiedDays;
                    }
                }
                
    
                // < Occupancy-10% -> 1 night
                if ($totalOccupiedDays == 1) {
                    $data['weekly']['twenty_percent'][] = (object) ['listing_id'=>$listing->listing_id, 'jsn_listing_name'=>$listing->jsn_listing_name, 'occupied_days'=>$totalOccupiedDays, 'price_type'=>'reduce', 'days_type'=>'next_week', 'percent'=>20];
                }
                
                // < Occupancy-30% -> 2 night
                if ($totalOccupiedDays == 2) {
                    $data['weekly']['ten_percent'][] = (object) ['listing_id'=>$listing->listing_id, 'jsn_listing_name'=>$listing->jsn_listing_name, 'occupied_days'=>$totalOccupiedDays, 'price_type'=>'reduce', 'days_type'=>'next_week', 'percent'=>10];
                }
                
                // > Occupancy-50% -> 4 nights
                if ($totalOccupiedDays == 4) {
                    $data['weekly']['fifteen_percent'][] = (object) ['listing_id'=>$listing->listing_id, 'jsn_listing_name'=>$listing->jsn_listing_name, 'occupied_days'=>$totalOccupiedDays, 'price_type'=>'increase', 'days_type'=>'next_week', 'percent'=>15];
                }
                
                // > Occupancy-70% -> 5-7 nights
                if ($totalOccupiedDays == 5 || $totalOccupiedDays == 6 || $totalOccupiedDays == 7) {
                    $data['weekly']['thirty_percent'][] = (object) ['listing_id'=>$listing->listing_id, 'jsn_listing_name'=>$listing->jsn_listing_name, 'occupied_days'=>$totalOccupiedDays, 'price_type'=>'increase', 'days_type'=>'next_week', 'percent'=>30];
                }
            }
            
            // ********************* NEXT WEEK BOOKINGS ENDS *********************


            // ********************* CURRENT WEEK BOOKINGS STARTS *********************
            
            if(empty($_GET['trigger']) || $_GET['trigger'] == "current_week_trigger"){

                $listing_ids_arr = [];
                
                // $ago_flag = true;
                $four_ago_bookings = $this->get_listings_without_bookings_by_days_ago($listing->listing_id, 4, 'reduce', 'update_price_until_booking_received', 20, $listing_ids_arr);
                
                
                
                // print_r($four_ago_bookings);die;
                
                if(!empty($four_ago_bookings)){
                    $data['no_bookings_last_four_days'][] = $four_ago_bookings;
                }
                
                // print_r($data['no_bookings_last_four_days']);die;
                
                
                // if(!empty($four_ago_bookings)){
                //     foreach($four_ago_bookings as $four_bk){
                //         if(!empty($four_bk->listing_id)){
                //             $listing_ids_arr[] = $four_bk->listing_id;
                //         }
                //     }
                // }
                
                if(!empty($four_ago_bookings->listing_id)){
                    $listing_ids_arr[] = $four_ago_bookings->listing_id;
                }
                
                // print_r($listing_ids_arr);die;
                
                $three_ago_bk = $this->get_listings_without_bookings_by_days_ago($listing->listing_id, 3, 'reduce', 'update_price_until_booking_received', 15, $listing_ids_arr);
                
                if(!empty($three_ago_bk)){
                    $data['no_bookings_last_three_days'][] = $three_ago_bk;
                }
                
                if(!empty($three_ago_bk->listing_id)){
                    $listing_ids_arr[] = $three_ago_bk->listing_id;
                }
                
                // print_r($listing_ids_arr);die;
                
                $two_ago_bk = $this->get_listings_without_bookings_by_days_ago($listing->listing_id, 2, 'reduce', 'update_price_until_booking_received', 10, $listing_ids_arr);
                
                if(!empty($two_ago_bk)){
                    $data['no_bookings_last_two_days'][] = $two_ago_bk;
                }
                
                
                // if($ago_flag){
                    
                //     if(!empty($three_ago_bk)){
                //         foreach($three_ago_bk as $threebk){
                //             if(!empty($threebk->listing_id)){
                //                 $listing_ids_arr[] = $threebk->listing_id;
                //             }
                //         }
                //     } else if(!empty($ago_bookings)){
                //         foreach($ago_bookings as $abkn){
                //             if(!empty($abkn->listing_id)){
                //                 $listing_ids_arr[] = $abkn->listing_id;
                //             }
                //         }
                //     }
                    
                //     $data['no_bookings_last_two_days'][] = $this->get_listings_without_bookings_by_days_ago($listing->listing_id, 2, 'reduce', 'update_price_until_booking_received', 10, $listing_ids_arr);
                //     // $ago_flag = false;
                // }
            }
            
            // ********************* CURRENT WEEK BOOKINGS ENDS *********************
        }

        // print_r($data);die;

        // 1 hour data store in cache
        $listings = Cache::remember('get_listings', 60, function () {
            return Listing::where(['is_sync'=>'sync_all', 'is_churned'=>0])->get();
        });

        $triggers = ['daily_trigger'=>'Daily Trigger', 'current_week_trigger'=>'Current Week Trigger', 'next_week_trigger'=>'Next Week Trigger', 'next_month_trigger'=>'Next Month Trigger'];
        
        return view('Admin.revenue-triggers.index', ['data'=>$data, 'listings'=>$listings, 'triggers'=>$triggers]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'listing_id' => 'required',
            'price_type' => 'required',
            'days_type' => 'required',
            'percent' => 'required',
        ]);
        
        $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
        if(is_null($rate_plan)){
            return response()->json(['success'=>0, 'error' => 'Rate Plan not found']);
        }

        $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
        if(is_null($room_type)){
            return response()->json(['success'=>0, 'error' => 'Room Type not found']);
        }

        // $property = Properties::where('user_id', $rate_plan->user_id)->first();
        $property = Properties::where('id', $room_type->property_id)->first();
        if(is_null($property)){
            return response()->json(['success'=>0, 'error' => 'Property not found']);
        }
        
        $update_days = [];
        switch($request->days_type) {

            case "weekdays":

                $startOfWeek = Carbon::now('Asia/Riyadh')->startOfWeek();
                $endOfWeek = Carbon::now('Asia/Riyadh')->endOfWeek();

                while ($startOfWeek <= $endOfWeek) {
    
                    if ($startOfWeek->dayOfWeek != Carbon::FRIDAY && $startOfWeek->dayOfWeek != Carbon::SATURDAY) {
                        $update_days[] = $startOfWeek->toDateString();
                    }
                    $startOfWeek->addDay();
                }
                break;

            case "weekends":
                
                $today_date = Carbon::now('Asia/Riyadh');
                
                if ($today_date->dayOfWeek == Carbon::FRIDAY || $today_date->dayOfWeek == Carbon::SATURDAY) {
                    $update_days[] = $today_date->toDateString();
                }

                // $startOfWeek = Carbon::now('Asia/Riyadh')->startOfWeek();
                // $endOfWeek = Carbon::now('Asia/Riyadh')->endOfWeek();

                // while ($startOfWeek <= $endOfWeek) {

                //     if ($startOfWeek->dayOfWeek == Carbon::FRIDAY || $startOfWeek->dayOfWeek == Carbon::SATURDAY) {
                //         $update_days[] = $startOfWeek->toDateString();
                //     }
                //     $startOfWeek->addDay();
                // }
                break;
            
            case "alldaysweek":

                $update_days[] = Carbon::now('Asia/Riyadh')->toDateString();

                // $startOfWeek = Carbon::now('Asia/Riyadh')->startOfWeek();
                // $endOfWeek = Carbon::now('Asia/Riyadh')->endOfWeek();

                // while ($startOfWeek <= $endOfWeek) {
    
                //     $update_days[] = $startOfWeek->toDateString();
                //     $startOfWeek->addDay();
                // }
                break;

            case "update_price_until_booking_received":
                
                $start_date = Carbon::now('Asia/Riyadh');
                
                $today_availability_exists = Calender::where('listing_id', $request->listing_id)
                    ->where('availability', 1)
                    ->where('calender_date', '=', Carbon::today())
                    ->exists();

                $calendar = Calender::where('listing_id', $request->listing_id)
                    ->where('availability', 0)
                    ->where('calender_date', '>', Carbon::today())
                    ->orderBy('calender_date', 'ASC')
                    ->first();

                $end_date = !empty($calendar->calender_date) ? $calendar->calender_date : '';

                if(empty($today_availability_exists)){
                    $update_days['no_booking_dates'] = "Unable to update the price because next day availability is 0";
                }

                if(empty($end_date)){
                    $end_date = Calender::where('listing_id', $request->listing_id)->max('calender_date');
                }

                if(!empty($end_date)){
                    $end_date = Carbon::parse($end_date);
                    while ($start_date < $end_date) {
    
                        $update_days[] = $start_date->toDateString();
                        $start_date->addDay();
                    }
                }
                break;

            case "next_week":
                
                $startOfWeek = Carbon::now('Asia/Riyadh')->addWeek()->startOfWeek()->subDay();
                $endOfWeek = Carbon::now('Asia/Riyadh')->addWeek()->endOfWeek()->subDay();

                while ($startOfWeek <= $endOfWeek) {

                    $update_days[] = $startOfWeek->toDateString();
                    $startOfWeek->addDay();
                }
                break;
                
            case "next_month":

                $startOfMonth = Carbon::now('Asia/Riyadh')->addMonth()->startOfMonth();
                $endOfMonth = Carbon::now('Asia/Riyadh')->addMonth()->endOfMonth();

                while ($startOfMonth <= $endOfMonth) {

                    $update_days[] = $startOfMonth->toDateString();
                    $startOfMonth->addDay();
                }
                break;

            default:
                // echo "Invalid day.";
                break;
        }

        if(!empty($update_days['no_booking_dates'])){
            return response()->json(['success'=>0, 'error'=>$update_days['no_booking_dates']]);
        }

        if(empty($update_days)){
            return response()->json(['success'=>0, 'error'=>'Invalid days']);
        }
        // return $update_days;

        $result = $this->update_price($request->listing_id, $property->ch_property_id, $rate_plan->ch_rate_plan_id, $request->price_type, $request->days_type, $request->percent, $update_days);
        return $result;
    }

    private function filter_calendar_price_change($listing_id, $days, $days_type){
        
        // $filter_days = [];

        $filter_days = Calender::where('listing_id', $listing_id)
        ->whereIn('calender_date', $days)
        ->where('availability', 1)
        ->where('is_lock', 0)
        ->where('is_blocked', 0)
        ->groupBy('calender_date')
        ->orderBy('calender_date', 'ASC')
        ->pluck('calender_date')
        ->toArray();
        
        // $calendars = Calender::where('listing_id', $listing_id)
        // ->whereIn('calender_date', $days)
        // ->select(['calender_date', 'availability', 'is_lock', 'is_blocked'])
        // ->orderBy('calender_date', 'ASC')
        // ->get();

        // return $calendars;

        // foreach($calendars as $calendar){
            
        //     // if($days_type == "update_price_until_booking_received"){
        //     //     if($calendar->availability == 0 || $calendar->is_lock == 1 || $calendar->is_blocked == 1){
        //     //         break;
        //     //     }
        //     // }

        //     if($calendar->availability == 1 && $calendar->is_lock == 0 && $calendar->is_blocked == 0){
        //         if(!in_array($calendar->calender_date, $filter_days)){
        //             $filter_days[] = $calendar->calender_date;
        //         }
        //     }
        // }
        
        // // Availability Check
        // $availability = Calender::where('listing_id', $listing_id)
        // ->whereIn('calender_date', $days)
        // ->where('availability', 0)
        // ->exists();

        // if($availability){
        //     return ['success'=>0, 'error' => 'There is no availability for this date to update the price'];
        // }
        
        // // Is Lock Check
        // $is_lock = Calender::where('listing_id', $listing_id)
        // ->whereIn('calender_date', $days)
        // ->where('is_lock', 1)
        // ->exists();
        
        // if($is_lock){
        //     return ['success'=>0, 'error' => 'This date is locked, the price cannot be updated'];
        // }
        
        // // Is Blocked Check
        // $is_blocked = Calender::where('listing_id', $listing_id)
        // ->whereIn('calender_date', $days)
        // ->where('is_blocked', 1)
        // ->exists();
        
        // if($is_blocked){
        //     return ['success'=>0, 'error' => 'This date is blocked, the price cannot be updated'];
        // }

        if(empty($filter_days)){
            return ['success'=>0, 'error' => 'Dates are not available to update the price'];
        }
        
        return ['success'=>1, 'error' => '', 'filter_days'=>$filter_days];
    }

    private function prepareUpdateData($listing_id, $property_id, $ch_rate_plan_id, $price_type, $percent, $days, $days_type, $log_flag=0){

        $pricing_arr = $channex_pricing_arr = [];
        $calendars_update_arr = [];
        $actual_percent = $percent / 100;
        $user_id = Auth::check() ? (int) Auth::user()->id : null;
        $data = ['success'=>0, 'pricing_arr'=>[], 'calendars_update_arr'=>[]];

        $filter_days = [];
        
        $calendars = Calender::where('listing_id', $listing_id)
            ->whereIn('calender_date', $days)
            ->select(['id', 'calender_date', 'rate', 'base_price'])
            ->orderBy('calender_date', 'ASC')
            ->get();


        $is_no_booking_trigger = 0;
        if($days_type == "update_price_until_booking_received"){
            $is_no_booking_trigger = 1;
        }
        
        $logs = ['rate_min_price'=>0, 'rate_max_price'=>0, 'base_min_price'=>0, 'base_max_price'=>0];

        foreach($calendars as $calendar){
            
            // if(in_array($calendar->calender_date, $filter_days)){
            //     continue;
            // }
            
            
            
            $price = $calendar->base_price;
            if(empty($price)){
                $price = $calendar->rate;
                $calendar->base_price = $price;
            }
            
            $amount = $price * $actual_percent;
            
            $priceModifiers = [
                'increase' => fn($price, $amount) => $price + $amount,
                'reduce' => fn($price, $amount) => $price - $amount,
            ];
            
            if (isset($priceModifiers[$price_type])) {
                $price = round($priceModifiers[$price_type]($price, $amount));
                
                $cl_rate = $price;
                $cl_base = $calendar->base_price;
                
                if(!empty($calendars_update_arr)){
                    foreach($calendars_update_arr as $cua){
                        if($cua['listing_id'] == $listing_id && $cua['calender_date'] == $calendar->calender_date){
                            $cl_rate = $cua['rate'];
                            $cl_base = $cua['base_price'];
                            break;
                        }
                    }
                }

                if(!in_array($calendar->calender_date, $filter_days)){

                    $calendars_update_arr[] = ['id'=>$calendar->id, 'listing_id'=>$listing_id, 'calender_date'=>$calendar->calender_date, 'rate'=>$cl_rate, 'base_price'=>$cl_base, 'is_no_booking_trigger'=>$is_no_booking_trigger];
                
                    $pricing_arr[] = [
                        "date_from" => $calendar->calender_date,
                        "date_to" => $calendar->calender_date,
                        "property_id" => "$property_id",
                        "rate_plan_id" => "$ch_rate_plan_id",
                        // 'min_stay' => (int) $request->min_stay,
                        'min_stay_through' => (int) 1,
                        'max_stay' => (int) 365,
                        'rate' => intval($price * 100), //269*100
                        'updated_by' => $user_id
                    ];
                    
                    
                    $channex_pricing_arr[] = [
                        "date_from" => $calendar->calender_date,
                        "date_to" => $calendar->calender_date,
                        "property_id" => "$property_id",
                        "rate_plan_id" => "$ch_rate_plan_id",
                        // 'min_stay' => (int) $request->min_stay,
                        'min_stay_through' => (int) 1,
                        'max_stay' => (int) 365,
                        'rate' => intval($calendar->base_price * 100),
                        'updated_by' => $user_id
                    ];
                    
                    // Rate Min & Max Price Set
                    $rate_p = $price;
                    if(empty($logs['rate_min_price'])){
                        $logs['rate_min_price'] = $rate_p;
                    }
                    if($rate_p < $logs['rate_min_price']){
                        $logs['rate_min_price'] = $rate_p;
                    }
                    if($rate_p > $logs['rate_max_price']){
                        $logs['rate_max_price'] = $rate_p;
                    }
    
                    // Base Min & Max Price Set
                    $base_p = round($calendar->base_price);
                    if(empty($logs['base_min_price'])){
                        $logs['base_min_price'] = $base_p;
                    }
                    if($base_p < $logs['base_min_price']){
                        $logs['base_min_price'] = $base_p;
                    }
                    if($base_p > $logs['base_max_price']){
                        $logs['base_max_price'] = $base_p;
                    }
                }
                
                $filter_days[] = $calendar->calender_date;
            }
        }

        if(!empty($pricing_arr) && !empty($calendars_update_arr) && !empty($channex_pricing_arr)){
            
            $logs = 'New: (Min:'.$logs['rate_min_price'].' | Max:'.$logs['rate_max_price'].') Old: (Min:'.$logs['base_min_price']. ' | Max:'.$logs['base_max_price'].')';
            
            if($log_flag){
                $logs = ['listing_id'=>$listing_id, 'msg'=>$logs];
            }

            $data = ['success'=>1, 'channex_price_update'=>$pricing_arr, 'calendars_price_update'=>$calendars_update_arr, 'channex_pricing_arr'=>$channex_pricing_arr, 'logs'=>$logs];
        }

        return $data;
    }

    private function autort_price_sync($channex_price_update, $calendars_price_update, $channex_pricing_arr, $trigger_log){

        // $listing_id, $days_type,

        // print_r($channex_price_update);
        // print_r($calendars_price_update);
        // print_r(strlen(json_encode($calendars_price_update)));
        // print_r($channex_pricing_arr);
        // print_r($trigger_log);
        // die;

        // Timeout 180 seconds 3 minutes
        // Retry 3 times with 1 second delay

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])
        ->timeout(0)
        ->retry(3, 1000)
        ->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
            "values" => 
                $channex_price_update
            ]);

        // Check if a warning exists, then return the warning
        $pricing_response = $response->json();
        if(!empty($pricing_response['meta']['warnings'][0]['warning'])){
            Log::info('AutoRT:: Price Update Warning Error:', ['response' => $response->body()]);
            return response()->json(['success'=>0, 'error' => $response->body()]);
        }

        if ($response->successful()) {
            
            Log::info('AutoRT:: Price Update Response:', ['response' => $response->json()]);

            
            $user_name = Auth::check() ? Auth::user()->name . ' ' . Auth::user()->surname : null;

            foreach($calendars_price_update as $calendar_price){
                // where('id', $calendar_price['id'])
                Calender::where('listing_id', $calendar_price['listing_id'])
                ->where('calender_date', $calendar_price['calender_date'])
                ->update(['rate'=>$calendar_price['rate'], 'base_price'=>$calendar_price['base_price']]);

                // TRIGGER PRICING FOR REVERT THE PRICE

                // DB::table('triggers_prices')->updateOrInsert(
                //     ['listing_id' => $calendar_price['listing_id'], 'trigger_type' => 'alldaysweek'],
                //     [
                //         'price_json' => json_encode($calendars_price_update),
                //         'channex_price_json' => json_encode($channex_pricing_arr),
                //         'trigger_logs' => $trigger_log
                //     ]
                // );
            
                // TRIGGER LOGS

                // $listing_name = '';
                // $listing = Listing::where('listing_id', $calendar_price['listing_id'])->first();
                // if(!is_null($listing) && !empty($trigger_log[$calendar_price['listing_id']])){
                //     $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                //     $listing_name = !empty($listing_json->title) ? $listing_json->title : '';

                //     DB::table('triggers_histories')->insert(
                //         ['listing_id' => $calendar_price['listing_id'], 'listing_name' => $listing_name, 'trigger_type' => $days_type, 'status'=>'Applied', 'trigger_log'=>$trigger_log[$calendar_price['listing_id']], 'user_name'=>$user_name],
                //     );
                // }
            }

            // DB::table('triggers_bulk_prices')->insert(
            //     [
            //         'is_reset' => 0,
            //         'trigger_type' => 'alldaysweek',
            //         'price_json' => json_encode($calendars_price_update),
            //         'channex_price_json' => json_encode($channex_pricing_arr),
            //         // 'trigger_logs' => $trigger_log
            //     ]
            // );

            logger("AutoRT:: Price has been updated");

            return response()->json(['success'=>1, 'error' => '']);
        }

        Log::info('AutoRT:: Price Update Error:', ['response' => $response->body()]);
        return response()->json(['success'=>0, 'error' => $response->body()]);
    }

    private function price_sync($listing_id, $days_type, $channex_price_update, $calendars_price_update, $channex_pricing_arr, $trigger_log){

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
            "values" => 
                $channex_price_update
            ]);

        // Check if a warning exists, then return the warning
        $pricing_response = $response->json();
        if(!empty($pricing_response['meta']['warnings'][0]['warning'])){
            Log::info('RT:: Price Update Warning Error:', ['response' => $response->body()]);
            return response()->json(['success'=>0, 'error' => $response->body()]);
        }

        if ($response->successful()) {
            
            Log::info('RT:: Price Update Response:', ['response' => $response->json()]);

            
            foreach($calendars_price_update as $calendar_price){
                Calender::where('listing_id', $calendar_price['listing_id'])
                ->where('calender_date', $calendar_price['calender_date'])
                ->update(['rate'=>$calendar_price['rate'], 'base_price'=>$calendar_price['base_price']]);
            }
            
            DB::table('triggers_prices')->updateOrInsert(
                ['listing_id' => $listing_id, 'trigger_type' => $days_type],
                [
                    'price_json' => json_encode($calendars_price_update),
                    'channex_price_json' => json_encode($channex_pricing_arr),
                    'trigger_logs' => $trigger_log
                ]
            );
            
            $user_name = Auth::check() ? Auth::user()->name . ' ' . Auth::user()->surname : null;
            
            $listing_name = '';
            $listing = Listing::where('listing_id', $listing_id)->first();
            if(!is_null($listing)){
                $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
            }
            
            DB::table('triggers_histories')->insert(
                ['listing_id' => $listing_id, 'listing_name' => $listing_name, 'trigger_type' => $days_type, 'status'=>'Applied', 'trigger_log'=>$trigger_log, 'user_name'=>$user_name],
            );

            return response()->json(['success'=>1, 'error' => '']);
        }

       
        Log::info('RT:: Price Update Error:', ['response' => $response->body()]);
        return response()->json(['success'=>0, 'error' => $response->body()]);
    }

    public function autort_update_price($listing_id, $property_id, $ch_rate_plan_id, $price_type, $days_type, $percent, $days)
    {
        $response = ['success'=>0, 'error' => 'Failed to update price. Please try again.'];
        
        // return $days;
        
        $filtered_data = $this->filter_calendar_price_change($listing_id, $days, $days_type);
        
        // return $filtered_data;
        
        if(empty($filtered_data['success']) || empty($filtered_data['filter_days'])){
            return response()->json($filtered_data);
        }
        // return $filtered_data;

        $data = $this->prepareUpdateData($listing_id, $property_id, $ch_rate_plan_id, $price_type, $percent, $filtered_data['filter_days'], $days_type, 1);
        if(empty($data['success'])){
            return response()->json(['success'=>0, 'error' => 'Prepare data not found']);
        }
        // return $data;

        if(!empty($data['channex_price_update']) && !empty($data['calendars_price_update']) && !empty($data['channex_pricing_arr'])){
            return $data;
            // $response = $this->price_sync($listing_id, $days_type, $data['channex_price_update'], $data['calendars_price_update'], $data['channex_pricing_arr'], $data['logs']);
        }

        return $response;
    }

    public function update_price($listing_id, $property_id, $ch_rate_plan_id, $price_type, $days_type, $percent, $days)
    {
        $response = ['success'=>0, 'error' => 'Failed to update price. Please try again.'];
        
        // return $days;
        
        $filtered_data = $this->filter_calendar_price_change($listing_id, $days, $days_type);
        
        // return $filtered_data;
        
        if(empty($filtered_data['success']) || empty($filtered_data['filter_days'])){
            return response()->json($filtered_data);
        }
        // return $filtered_data;

        $data = $this->prepareUpdateData($listing_id, $property_id, $ch_rate_plan_id, $price_type, $percent, $filtered_data['filter_days'], $days_type);
        if(empty($data['success'])){
            return response()->json(['success'=>0, 'error' => 'Prepare data not found']);
        }
        // return $data;

        if(!empty($data['channex_price_update']) && !empty($data['calendars_price_update']) && !empty($data['channex_pricing_arr'])){
            $response = $this->price_sync($listing_id, $days_type, $data['channex_price_update'], $data['calendars_price_update'], $data['channex_pricing_arr'], $data['logs']);
        }

        return $response;
    }

    private function get_listings_without_bookings($KSATimeZone, $currentDate, $price_type, $days_type, $percent, $is_auto_trigger=0){
        
        // $listings = Listing::whereDoesntHave('bookingsOTAS', function ($query) use ($KSATimeZone, $days) {
        //     $query->whereRaw('
        //         CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00") <= ? 
        //         AND DAYOFWEEK(CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00")) ' . $days, [
        //         $KSATimeZone->toDateTimeString()
        //     ]);
        // })
        
        $where = ['is_sync'=>'sync_all', 'is_churned'=>0];
        if(!empty($_GET['listing_id'])){
            $where['listing_id'] = $_GET['listing_id'];
        }
        
        if($is_auto_trigger == 1){
            $where['is_auto_trigger'] = 1;
        }
        
        $listings = Listing::where($where)
        ->where(function ($query) use ($KSATimeZone, $currentDate) {
            
            $query->whereDoesntHave('bookingsOTAS', function ($subQuery) use ($KSATimeZone, $currentDate) {
                $subQuery->whereRaw('
                    TIME(CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00")) <= "'.$KSATimeZone.'" 
                    AND DATE(CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00")) = "'.$currentDate.'"');
            })
            ->orWhereDoesntHave('bookings', function ($subQuery) use ($KSATimeZone, $currentDate) {
                $subQuery->whereRaw('
                    TIME(CONVERT_TZ(bookings.created_at, "+00:00", "+03:00")) <= "'.$KSATimeZone.'" 
                    AND DATE(CONVERT_TZ(bookings.created_at, "+00:00", "+03:00")) = "'.$currentDate.'"');
            });
        })
        ->select(['listing_id as listing_id', 'listing_json->title as jsn_listing_name'])
        ->get()
        ->map(function ($listing) use ($price_type, $days_type, $percent) {
            $listing->price_type = $price_type;
            $listing->days_type = $days_type;
            $listing->percent = $percent;
    
            return $listing;
        });
        
        // $listings = Listing::where($where)
        // ->whereDoesntHave('bookingsOTAS', function ($query) use ($KSATimeZone, $currentDate) {
        //     $query->whereRaw('
        //         TIME(CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00")) <= "'.$KSATimeZone.'" 
        //         AND DATE(CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00")) = '.$currentDate);
        // })
        // ->select(['listing_id as listing_id', 'listing_json->title as jsn_listing_name'])
        // ->get()
        // ->map(function ($listing) use ($price_type, $days_type, $percent) {
            
        //     $listing->price_type = $price_type;
        //     $listing->days_type = $days_type;
        //     $listing->percent = $percent;
    
        //     return $listing;
        // });
        
        return !empty($listings) ? $listings : [];
    }

    private function get_listings_without_bookings_by_days_ago($listing_id, $days, $price_type, $days_type, $percent, $listing_ids_arr){
        
        
        $days_ago = Carbon::today('Asia/Riyadh')->subDays($days)->toDateString();
        
        $today_date = Carbon::today('Asia/Riyadh')->toDateString();
        
        // if(!empty($listing_ids_arr)){
        //     $calendars = Calender::whereNotIn('listing_id', $listing_ids_arr)
        //     ->whereDate('calender_date', '>=', $days_ago)
        //     ->whereDate('calender_date', '<=', $today_date)
        //     ->get();
        // } else{
        //     $calendars = Calender::where('listing_id', $listing_id)
        //     ->whereDate('calender_date', '>=', $days_ago)
        //     ->whereDate('calender_date', '<=', $today_date)
        //     ->get();
        // }
        
        if(!empty($listing_ids_arr)){
            $is_found = Calender::whereNotIn('listing_id', $listing_ids_arr)
            ->whereDate('calender_date', '>=', $days_ago)
            ->whereDate('calender_date', '<=', $today_date)
            ->where(function ($query) {
                $query->where('availability', 0)
                      ->orWhere('is_lock', 1)
                      ->orWhere('is_blocked', 1);
            })
            ->exists();
            
            $calendars = Calender::whereNotIn('listing_id', $listing_ids_arr)
            ->whereDate('calender_date', '>=', $days_ago)
            ->whereDate('calender_date', '<=', $today_date)
            ->exists();
            
        } else{
            $is_found = Calender::where('listing_id', $listing_id)
            ->whereDate('calender_date', '>=', $days_ago)
            ->whereDate('calender_date', '<=', $today_date)
            ->where(function ($query) {
                $query->where('availability', 0)
                      ->orWhere('is_lock', 1)
                      ->orWhere('is_blocked', 1);
            })
            ->exists();
            
            $calendars = Calender::where('listing_id', $listing_id)
            ->whereDate('calender_date', '>=', $days_ago)
            ->whereDate('calender_date', '<=', $today_date)
            ->exists();
        }
        
        // $is_found = true;
        // foreach($calendars as $calendar){
        //     if($calendar->availability == 0 || $calendar->is_lock == 1 || $calendar->is_blocked == 1){
        //         $is_found = false;
        //     }
        // }
        
        // No booking found against input days
        // if($is_found && !empty($calendars)){
        if(empty($is_found) && !empty($calendars)){
            $listing = Listing::where('listing_id', $listing_id)->first();
            
            // print_r($listing);die;
            
            if(!is_null($listing)){
                $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
            
                return (object) [
                    'listing_id' => $listing_id,
                    'jsn_listing_name' => $listing_name,
                    'price_type' => $price_type,
                    'days_type' => $days_type,
                    'percent' => $percent,
                ];
            }
        }
        
        return [];
        
        
        // $listings = [];
        
        // if(!empty($listing_ids_arr)){
        //     $listings = Listing::where('listing_id', $listing_id)
        //     ->where(['is_sync'=>'sync_all', 'is_churned'=>0])
        //     ->whereNotIn('listing_id',$listing_ids_arr)
        //     ->whereDoesntHave('bookingsOTAS', function ($query) use ($days_ago) {
        //         $query->whereRaw('
        //             DATE(CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00")) >= ? 
        //         ', [
        //             $days_ago->toDateString()
        //         ]);
        //     })
        //     ->whereDoesntHave('bookings', function ($query) use ($days_ago) {
        //         $query->whereRaw('
        //             bookings.booking_status != "cancelled" AND 
        //             DATE(CONVERT_TZ(bookings.booking_date_end, "+00:00", "+03:00")) >= ? 
        //         ', [
        //             $days_ago->toDateString()
        //         ]);
        //     })
        //     ->select(['listing_id as listing_id', 'listing_json->title as jsn_listing_name'])
        //     ->get()
        //     ->map(function ($listing) use ($price_type, $days_type, $percent) {
                
        //         $listing->price_type = $price_type;
        //         $listing->days_type = $days_type;
        //         $listing->percent = $percent;
        
        //         return $listing;
        //     });
        // } else{
        //     $listings = Listing::where('listing_id', $listing_id)
        //     ->where(['is_sync'=>'sync_all', 'is_churned'=>0])
        //     ->whereDoesntHave('bookingsOTAS', function ($query) use ($days_ago) {
        //         $query->whereRaw('
        //             DATE(CONVERT_TZ(booking_otas_details.created_at, "+00:00", "+03:00")) >= ? 
        //         ', [
        //             $days_ago->toDateString()
        //         ]);
        //     })
        //     ->select(['listing_id as listing_id', 'listing_json->title as jsn_listing_name'])
        //     ->get()
        //     ->map(function ($listing) use ($price_type, $days_type, $percent) {
                
        //         $listing->price_type = $price_type;
        //         $listing->days_type = $days_type;
        //         $listing->percent = $percent;
        
        //         return $listing;
        //     });
        // }

        
        
        // return !empty($listings) ? $listings : [];
    }

    function auto_revert_price(){
        
        echo 'closed'; die;

        // $triggers_price = DB::table('triggers_bulk_prices')
        // ->where(['is_reset' => 0, 'trigger_type' => 'alldaysweek'])
        // ->orderBy('id', 'DESC')
        // ->first();

        if(is_null($triggers_price)){
            return response()->json(['success'=>0, 'error' => 'Date not found']);
        }
        
        $calendars_price_update = json_decode($triggers_price->price_json);
        if(empty($calendars_price_update)){
            return response()->json(['success'=>0, 'error' => 'Calendar Price is required']);
        }
        // return $calendars_price_update;
        
        $channex_price_update = json_decode($triggers_price->channex_price_json);
        if(empty($channex_price_update)){
            return response()->json(['success'=>0, 'error' => 'Channex Price is required']);
        }
        
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
            "values" => 
                $channex_price_update
            ]);

        // Check if a warning exists, then return the warning
        $pricing_response = $response->json();
        if(!empty($pricing_response['meta']['warnings'][0]['warning'])){
            Log::info('AUTO REVERT PRICE:: Price Update Warning Error:', ['response' => $response->body()]);
            return response()->json(['success'=>0, 'error' => $response->body()]);
        }
        
        if ($response->successful()) {
            
            Log::info('AUTO REVERT PRICE:: Price Update Response:', ['response' => $response->json()]);
            
            
            foreach($calendars_price_update as $calendar_price){
                Calender::where('listing_id', $calendar_price->listing_id)
                ->where('calender_date', $calendar_price->calender_date)
                ->update(['rate'=>$calendar_price->base_price, 'base_price'=>0]);
            }
            
            // $user_name = Auth::check() ? Auth::user()->name . ' ' . Auth::user()->surname : null;
            
            // $listing_name = '';
            // $listing = Listing::where('listing_id', $request->listing_id)->first();
            // if(!is_null($listing)){
            //     $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
            //     $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
            // }
            
            
            // $tpl = !empty($triggers_price->trigger_logs) ? $triggers_price->trigger_logs : '';
            // DB::table('triggers_histories')->insert(
            //     ['listing_id' => $request->listing_id, 'listing_name' => $listing_name, 'trigger_type' => $request->days_type, 'status'=>'Remove', 'trigger_log'=>$tpl, 'user_name'=>$user_name],
            // );
            
            $triggers_price->is_reset = 1;
            $triggers_price->save();

            // DB::table('triggers_prices')->where(['listing_id' => $request->listing_id, 'trigger_type' => $request->days_type])->delete();
        }
        
        return response()->json(['success'=>1, 'error' => '']);
        
    }
    

    function temp_revert_price(){

        echo 'Closed';die;

        $calendars_update_arr = [];
                
        $pricing_arr = [];

        $update_days[] = '2025-05-15';
        $update_days[] = '2025-05-16';
        $update_days[] = '2025-05-17';
        $update_days[] = '2025-05-18';

        $calendars = Calender::where('base_price', '!=', 0)
        ->whereIn('calender_date', $update_days)
        ->where('availability', 1)
        ->where('is_lock', 0)
        ->where('is_blocked', 0)
        ->get();

        // print_r($calendars);die;

        // print_r($update_days);die;

        $user_id = Auth::check() ? (int) Auth::user()->id : null;

        foreach($calendars as $record){
            
            $is_churned = ChurnedProperty::where('listing_id', $record->listing_id)->exists();
            if($is_churned){
                continue;
            }
            
            $rate_plan = RatePlan::where('listing_id', $record->listing_id)->first();
            if(is_null($rate_plan)){
                continue;
                // return response()->json(['success'=>0, 'error' => 'Rate Plan not found']);
            }
    
            $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
            if(is_null($room_type)){
                continue;
                // return response()->json(['success'=>0, 'error' => 'Room Type not found']);
            }
    
            // $property = Properties::where('user_id', $rate_plan->user_id)->first();
            $property = Properties::where('id', $room_type->property_id)->first();
            if(is_null($property)){
                continue;
                // return response()->json(['success'=>0, 'error' => 'Property not found']);
            }

            $calendars_update_arr[] = ['id'=>0, 'listing_id'=>$record->listing_id, 'calender_date'=>$record->calender_date, 'rate'=>$record->base_price, 'base_price'=>0, 'is_no_booking_trigger'=>0];

            $pricing_arr[] = [
                "date_from" => $record->calender_date,
                "date_to" => $record->calender_date,
                "property_id" => "$property->ch_property_id",
                "rate_plan_id" => "$rate_plan->ch_rate_plan_id",
                // 'min_stay' => (int) $request->min_stay,
                'min_stay_through' => (int) 1,
                'max_stay' => (int) 365,
                'rate' => intval($record->base_price * 100), //269*100
                'updated_by' => $user_id
            ];
        }

        // print_r($logs_arr);
        print_r($calendars_update_arr);
        print_r($pricing_arr);
        // die;

        if(!empty($pricing_arr) && !empty($calendars_update_arr)){
            $response = $this->autort_price_sync($pricing_arr, $calendars_update_arr, [], []);

            print_r($response);
            die;
        }

        echo 'Not Found';
    }

    function revert_price(Request $request){
        
        if(empty($request->listing_id) || empty($request->days_type)){
            return response()->json(['success'=>0, 'error' => 'Listing id or days type is missing']);
        }
        
        $triggers_price = DB::table('triggers_prices')->where(['listing_id' => $request->listing_id, 'trigger_type' => $request->days_type])->first();
        if(is_null($triggers_price)){
            return response()->json(['success'=>0, 'error' => 'Date not found']);
        }
        
        $calendars_price_update = json_decode($triggers_price->price_json);
        if(empty($calendars_price_update)){
            return response()->json(['success'=>0, 'error' => 'Calendar Price is required']);
        }
        // return $calendars_price_update;
        
        $channex_price_update = json_decode($triggers_price->channex_price_json);
        if(empty($channex_price_update)){
            return response()->json(['success'=>0, 'error' => 'Channex Price is required']);
        }
        
        // foreach($channex_price_update as $channex_p){
        //     foreach($calendars_price_update as $calendar_p){
        //         if($channex_p->date_from == $calendar_p->calendar_date && $channex_p->date_to == $calendar_p->calendar_date){
        //             //
        //         }
        //     }
        // }
        // return $channex_price_update;
        
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
            "values" => 
                $channex_price_update
            ]);

        // Check if a warning exists, then return the warning
        $pricing_response = $response->json();
        if(!empty($pricing_response['meta']['warnings'][0]['warning'])){
            Log::info('REVERT PRICE:: Price Update Warning Error:', ['response' => $response->body()]);
            return response()->json(['success'=>0, 'error' => $response->body()]);
        }
        
        
        if ($response->successful()) {
            
            Log::info('REVERT PRICE:: Price Update Response:', ['response' => $response->json()]);
            
            
            foreach($calendars_price_update as $calendar_price){
                Calender::where('listing_id', $calendar_price->listing_id)
                ->where('calender_date', $calendar_price->calender_date)
                ->update(['rate'=>$calendar_price->base_price, 'base_price'=>0]);
            }
            
            
            $user_name = Auth::check() ? Auth::user()->name . ' ' . Auth::user()->surname : null;
            
            $listing_name = '';
            $listing = Listing::where('listing_id', $request->listing_id)->first();
            if(!is_null($listing)){
                $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                $listing_name = !empty($listing_json->title) ? $listing_json->title : '';
            }
            
            
            $tpl = !empty($triggers_price->trigger_logs) ? $triggers_price->trigger_logs : '';
            DB::table('triggers_histories')->insert(
                ['listing_id' => $request->listing_id, 'listing_name' => $listing_name, 'trigger_type' => $request->days_type, 'status'=>'Remove', 'trigger_log'=>$tpl, 'user_name'=>$user_name],
            );
            
            
            DB::table('triggers_prices')->where(['listing_id' => $request->listing_id, 'trigger_type' => $request->days_type])->delete();
        }
        
        return response()->json(['success'=>1, 'error' => '']);
        
    }
    
    function view_logs($listing_id){
        
        $triggers_histories = DB::table('triggers_histories')->where('listing_id', $listing_id)->orderBy('id', 'DESC')->get();
            
            
        return view('Admin.revenue-triggers.view_logs', ['triggers_histories'=>$triggers_histories]);
    }
    
    public function AutoRevenueTrigger(){

        logger("************** Auto Revenue Trigger STARTS **************");

        $currentDate = Carbon::now('Asia/Riyadh')->format('Y-m-d');
        
        $threePmKsaTime = '17:00:00'; // 5 PM KSA time
        $sixPmKsaTime = '20:00:00'; // 8 PM KSA time
        $eightPmKsaTime = '22:00:00'; // 10 PM KSA time
        // $twelveAmKsaTime = '23:59:59'; // 12 AM KSA time
        
        $ksaTime = Carbon::now('Asia/Riyadh');

        $data = [];
        // if($ksaTime->hour == 20){ // 8 PM KSA time
        if($ksaTime->hour == 22){ // 10 PM KSA time
            $data = $this->get_listings_without_bookings($eightPmKsaTime, $currentDate, 'reduce', 'alldaysweek', 15, 1);
        }

        // else if($ksaTime->hour == 18){ // 6 PM KSA time
        else if($ksaTime->hour == 20){ // 8 PM KSA time
            $data = $this->get_listings_without_bookings($sixPmKsaTime, $currentDate, 'reduce', 'alldaysweek', 10, 1);
        }

        // else if($ksaTime->hour == 15){ // 3 PM KSA time
        else if($ksaTime->hour == 17){ // 5 PM KSA time
            $data = $this->get_listings_without_bookings($threePmKsaTime, $currentDate, 'reduce', 'alldaysweek', 5, 1); // 0
        }

        // else if($ksaTime->hour == 0) { // 12 AM KSA time
        //     // $revert_price = $this->auto_revert_price();

        //     $previousDate = Carbon::now('Asia/Riyadh')->subDay()->format('Y-m-d');

        //     $data = $this->get_listings_without_bookings($twelveAmKsaTime, $previousDate, 'increase', 'alldaysweek', 15, 1); // 0

        //     logger("AutoRT Data: " . json_encode($data));

        //     $response = $this->autoTriggerSubmit($data);

        //     echo 'Price revert';
        //     die;
        // }
        
        // print_r($data);die;
        
        if(!empty($data)){

            logger("AutoRT Data: " . json_encode($data));

            $response = $this->autoTriggerSubmit($data);
            // return $response;
        }

        // ********************* DAILY TRIGGERS ENDS *********************
        
        logger("************** Auto Revenue Trigger ENDS **************");

        echo 'Done';
    }
    
    private function autoTriggerSubmit($data){

        $channex_price_update_arr = [];
        $calendars_price_update_arr = [];
        $channex_pricing_arr = [];
        $logs_arr = [];

        // Daily Trigger apply
        $update_days[] = Carbon::now('Asia/Riyadh')->toDateString();

        // $startOfWeek = Carbon::now('Asia/Riyadh');

        // $endOfWeek = Carbon::now('Asia/Riyadh')->endOfWeek();

        // while ($startOfWeek <= $endOfWeek) {

        //     $update_days[] = $startOfWeek->toDateString();
        //     $startOfWeek->addDay();
        // }
        
        foreach($data as $record){

            $is_churned = ChurnedProperty::where('listing_id', $record->listing_id)->exists();
            if($is_churned){
                continue;
            }
            
            $rate_plan = RatePlan::where('listing_id', $record->listing_id)->first();
            if(is_null($rate_plan)){
                continue;
                // return response()->json(['success'=>0, 'error' => 'Rate Plan not found']);
            }
    
            $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
            if(is_null($room_type)){
                continue;
                // return response()->json(['success'=>0, 'error' => 'Room Type not found']);
            }
    
            // $property = Properties::where('user_id', $rate_plan->user_id)->first();
            $property = Properties::where('id', $room_type->property_id)->first();
            if(is_null($property)){
                continue;
                // return response()->json(['success'=>0, 'error' => 'Property not found']);
            }
            
            // print_r($record);die;
            
            // if(!empty($record->days_type)){
                // if($record->days_type == 'weekdays'){
                
                //     $startOfWeek = Carbon::now('Asia/Riyadh')->startOfWeek();
                //     $endOfWeek = Carbon::now('Asia/Riyadh')->endOfWeek();
        
                //     while ($startOfWeek <= $endOfWeek) {
        
                //         if ($startOfWeek->dayOfWeek != Carbon::FRIDAY && $startOfWeek->dayOfWeek != Carbon::SATURDAY) {
                //             $update_days[] = $startOfWeek->toDateString();
                //         }
                //         $startOfWeek->addDay();
                //     }
                // }
                
                // if($record->days_type == 'weekends'){
                    
                //     $today_date = Carbon::now('Asia/Riyadh');
    
                //     if ($today_date->dayOfWeek == Carbon::FRIDAY || $today_date->dayOfWeek == Carbon::SATURDAY) {
                //         $update_days[] = $today_date->toDateString();
                //     }
                // }
                
                // if($record->days_type == 'alldaysweek'){
                
                //     // $startOfWeek = Carbon::now('Asia/Riyadh')->startOfWeek();

                //     $startOfWeek = Carbon::now('Asia/Riyadh');

                //     $endOfWeek = Carbon::now('Asia/Riyadh')->endOfWeek();
        
                //     while ($startOfWeek <= $endOfWeek) {
        
                //         $update_days[] = $startOfWeek->toDateString();
                //         $startOfWeek->addDay();
                //     }
                // }

                // logger("AutoRT Days Type: " . $record->days_type);
            // }
            
            if(empty($update_days)){
                continue;
                // return response()->json(['success'=>0, 'error'=>'Invalid days']);
            }

            // print_r($update_days);die;

            // return $update_days;

            $result = $this->autort_update_price($record->listing_id, $property->ch_property_id, $rate_plan->ch_rate_plan_id, $record->price_type, $record->days_type, $record->percent, $update_days);

            // print_r($result);die;

            // echo is_array($result);die;

            // if(!empty($result)){
            //     logger("AutoRT Response: " . json_encode($result));
            // }

            if(is_array($result) && !empty($result['channex_price_update']) && !empty($result['calendars_price_update']) && !empty($result['channex_pricing_arr'])){
                // return $data;

                // $channex_price_update_arr[] = $result['channex_price_update'];
                // $calendars_price_update_arr[] = $result['calendars_price_update'];
                // $channex_pricing_arr[] = $result['channex_pricing_arr'];

                $channex_price_update_arr = array_merge($channex_price_update_arr, $result['channex_price_update']);

                $calendars_price_update_arr = array_merge($calendars_price_update_arr, $result['calendars_price_update']);

                $channex_pricing_arr = array_merge($channex_pricing_arr, $result['channex_pricing_arr']);

                if(!empty($result['logs']['listing_id']) && !empty($result['logs']['msg'])){
                    if(!isset($logs_arr[$result['logs']['listing_id']])){
                        $logs_arr[$result['logs']['listing_id']] = $result['logs']['msg'];
                    }
                }

                // $response = $this->price_sync($listing_id, $days_type, $data['channex_price_update'], $data['calendars_price_update'], $data['channex_pricing_arr'], $data['logs']);
            }

            // return $result;
        }

        // print_r($logs_arr);
        // print_r($channex_price_update_arr);
        // print_r($calendars_price_update_arr);
        // die;

        if(!empty($channex_price_update_arr) && !empty($calendars_price_update_arr)){
            $response = $this->autort_price_sync($channex_price_update_arr, $calendars_price_update_arr, $channex_pricing_arr, $logs_arr);
        }
    }
    
    public function auto_revenue()
    {
        $listings = Listing::where(['is_sync' => 'sync_all', 'is_churned' => 0])
        ->select(['id', 'listing_id as listing_id', 'be_listing_name', 'listing_json->title as jsn_listing_name', 'is_auto_trigger'])
        ->orderBy('id', 'desc')
        ->get();
        
        return view('Admin.revenue-triggers.auto_revenue', ['listings' => $listings]);
    }
    
    public function set_auto_trigger(Request $request){
        
        // Select All option | Bulk Update
        if(!empty($request->listing_ids)){
            $data = [];
            foreach($request->listing_ids as $listing_id){
                
                $listing = Listing::where(['listing_id' => $listing_id])->first();
                if(!is_null($listing)){
                    $listing->is_auto_trigger = $request->select_type == 1 ? 1 : 0;
                    $listing->save();
                    
                    $data[] = ['listing_id'=>$listing_id, 'set_auto'=>$listing->is_auto_trigger];
                }
            }
            
            if(!empty($data)){
                return response()->json(['success'=>1, 'data' => $data]);
            }
            
            return response()->json(['success'=>0, 'data' => []]);
        }
        
        // Single Update
        if(!empty($request->listing_id)){
            
            $is_auto_trigger = $request->is_auto_trigger == 1 ? 0 : 1;
            
            Listing::where(['listing_id' => $request->listing_id])
            ->update(['is_auto_trigger' => $is_auto_trigger]);
            
            return response()->json(['success'=>1, 'set_auto' => $is_auto_trigger]);
        }
    }
}














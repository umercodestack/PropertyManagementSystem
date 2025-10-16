<?php

namespace App\Http\Controllers\Api\CaptainApp;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Channels;
use App\Models\Cleaning;
use App\Models\CleaningComment;
use App\Models\DeepCleaning;
use App\Models\Listing;
use App\Models\User;
use App\Models\CleaningTask;
use App\Models\CleaningStatusLog;
use App\Models\Cleaningimages;
use App\Models\RevenueActivationAudit;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CleaningController extends Controller
{
    public function getAllTasks()
    {
        $listings = Listing::all();
        $user = Auth::user();
        $listing_arr_lived = array();
        $listing_arr_ota = array();
        foreach ($listings as $item) {
            $users = json_decode($item['user_id']);
            // if (in_array($user->id, $users)) {
            //     array_push($listing_arr_lived, $item['id']);
            //     array_push($listing_arr_ota, $item['listing_id']);
            // }

            // If it's an array and user ID is inside
            if (is_array($users) && in_array($user->id, $users)) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
            // If it's a single user_id (not an array)
            elseif ((string)$user->id === (string)$item['user_id']) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
        }
        $date = Carbon::today()->toDateString();
        $checkoutData = array();
        $startDate = null;
        $endDate = null;
         $order_by = 'asc';
        isset($endDate) ? $order_by = 'desc' : $order_by = 'asc';
        
        
        //$chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'asc')->whereIn('listing_id', $listing_arr_ota)->where('departure_date', '>=', $date)->limit(1)->get();
        //$chechoutsLived = Bookings::orderBy('booking_date_end', 'asc')->whereIn('listing_id', $listing_arr_lived)->where('booking_date_end', '>=', $date)->limit(1)->get();
          if($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 2)
          {
             $chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'asc')
             ->whereIn('listing_id', $listing_arr_ota)
             ->where('departure_date', '>=', $date)
             ->limit(1)
             ->get();

            $chechoutsLived = Bookings::orderBy('booking_date_end', 'asc')
            ->whereIn('listing_id', $listing_arr_lived)
            ->where('booking_date_end', '>=', $date)
            ->where('bookings.include_cleaning', '!=', 1)
            ->limit(1)
            ->get();
          }
          else if ($user->role_id === 9) {
            $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'cleanings.cleaner_id')
            ->join('cleanings', 'cleanings.booking_id', '=', 'booking_otas_details.id')
            ->where('cleanings.cleaner_id', $user->id)
            ->orderBy('departure_date', 'asc')
            ->whereIn('booking_otas_details.listing_id', $listing_arr_ota)
            ->where('departure_date', '>=', $date)
            ->limit(1)
            ->get();

            $chechoutsLived = Bookings::select('bookings.*', 'cleanings.cleaner_id')
            ->join('cleanings', 'cleanings.booking_id', '=', 'bookings.id')
            ->where('cleanings.cleaner_id', $user->id)
            ->where('bookings.include_cleaning', '!=', 1)
            ->orderBy('booking_date_end', 'asc')
            ->whereIn('bookings.listing_id', $listing_arr_lived)
            ->where('booking_date_end', '>=', $date)
            ->limit(1)
            ->get();
          } 
          else {
          
                $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'listings.exp_managers')
                ->join('listings', 'booking_otas_details.listing_id', '=', 'listings.listing_id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->orderBy('departure_date', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('departure_date', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->limit(1)->get();
                
                $chechoutsLived = Bookings::select('bookings.*', 'listings.exp_managers')
                ->join('listings', 'bookings.listing_id', '=', 'listings.id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->where('bookings.include_cleaning', '!=', 1)
                ->orderBy('booking_date_end', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                    return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->limit(1)->get();
               
              
          }

        
       
       
       
        foreach ($chechoutsOta as $items) {
            $checkout['id'] = $items->id;
            $checkout['type'] = 'ota';
            $checkout['date'] = $items->departure_date;
            $listing = Listing::where('listing_id', $items->listing_id)->first();
            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->departure_date)->first();
            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            array_push($checkoutData, $checkout);
        }
        foreach ($chechoutsLived as $items) {
            $checkout['id'] = $items->id;
            $checkout['type'] = 'livedin';
            $checkout['date'] = $items->booking_date_end;
            $listing = Listing::where('id', $items->listing_id)->first();
            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->booking_date_end)->first();
            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            array_push($checkoutData, $checkout);
        }
        
        $deepCleaning = DeepCleaning::orderBy('cleaning_date', 'desc') ->where('assignToPropertyManager', $user->id) ->select('id', 'listing_title', 'listing_id', 'cleaning_date as date', 'status') ->limit(2) ->get() ->map(function ($item) { if ($item->status === "mark as done") { $item->status = "completed"; } return $item; });
        
        
        $audit = Audit::orderBy('audit_date', 'desc') ->when($user->role_id !== 6, function ($query) use ($user) { return $query->where('assignTo', $user->id); }) ->select('id', 'listing_title', 'listing_id', 'audit_date as date', 'status') ->limit(2) ->get() ->map(function ($item) { if ($item->status === "mark as done") { $item->status = "completed"; } return $item; });
        
        $photoreview = RevenueActivationAudit::with('hostaboard') ->when($user->role_id == 6, function ($query) { return $query->where('task_status', '!=', 'no required'); }) ->orderBy('created_at', 'desc') ->select('id', 'hostaboard_id', 'status', 'task_status', 'remarks', 'task_remarks', 'url', 'updated_by', 'created_at') ->limit(2) ->get() ->map(function ($item) { $status = strtolower($item->status); $taskStatus = strtolower($item->task_status); if ($status === 'approved') { $item->status = 'completed'; } elseif ($status === 'mark as done') { $item->status = 'in review'; } elseif ($taskStatus !== 'completed') { $item->status = 'pending'; } return [ 'id' => $item->id, 'hostaboard_id' => $item->hostaboard_id, 'status' => $item->status, 'task_status' => $item->task_status, 'remarks' => $item->remarks, 'task_remarks' => $item->task_remarks, 'url' => $item->url, 'updated_by' => $item->updated_by, 'date' => $item->created_at ? $item->created_at->format('Y-m-d') : null, 'listing_title' => $item->hostaboard->title ?? null, 'city_name' => $item->hostaboard->city_name ?? null, 'owner_name' => $item->hostaboard->owner_name ?? null, 'unit_type' => $item->hostaboard->unit_type ?? null, 'type' => $item->hostaboard->type ?? null, ]; });


        return response()->json([
            'Cleanings' => $checkoutData,
            'DeepCleanings' => $deepCleaning,
            'Audit' => $audit,
            'PhotoReview' => $photoreview
        ]);
    }
   
    public function getAllTaskCount()
    {
        $listings = Listing::all();
        $user = Auth::user();
        $listing_arr_lived = array();
        $listing_arr_ota = array();
        foreach ($listings as $item) {
            $users = json_decode($item['user_id']);
            //if (in_array($user->id, $users)) {
              //  array_push($listing_arr_lived, $item['id']);
              //  array_push($listing_arr_ota, $item['listing_id']);
            //}
            
            if (is_array($users) && in_array($user->id, $users)) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
        // If it's a single user_id (not an array)
            elseif ((string)$user->id === (string)$item['user_id']) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
            
            
            
        }

        $date = Carbon::today()->toDateString();
        $checkoutData = array();
        $taskCompleted = 0;
        
        $startDate = null;
        $endDate = null;
         $order_by = 'asc';
        isset($endDate) ? $order_by = 'desc' : $order_by = 'asc';
        
        //$chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'asc')->whereIn('listing_id', $listing_arr_ota)->where('departure_date', '>=', $date)->limit(1)->get();
        //$chechoutsLived = Bookings::orderBy('booking_date_end', 'asc')->whereIn('listing_id', $listing_arr_lived)->where('booking_date_end', '>=', $date)->limit(1)->get();
        
          if($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 2)
          {
             $chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'asc')
             ->whereIn('listing_id', $listing_arr_ota)
             ->where('departure_date', '>=', $date)
             ->get();

            $chechoutsLived = Bookings::orderBy('booking_date_end', 'asc')
            ->whereIn('listing_id', $listing_arr_lived)
            ->where('booking_date_end', '>=', $date)
            ->get();
          }
          else if ($user->role_id === 9) {
              $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'cleanings.cleaner_id')
              ->join('cleanings', 'cleanings.booking_id', '=', 'booking_otas_details.id')
              ->where('cleanings.cleaner_id', $user->id)
              ->orderBy('departure_date', 'asc')
              ->whereIn('booking_otas_details.listing_id', $listing_arr_ota)
              ->where('departure_date', '>=', $date)
              
              ->get();

              $chechoutsLived = Bookings::select('bookings.*', 'cleanings.cleaner_id')
              ->join('cleanings', 'cleanings.booking_id', '=', 'bookings.id')
              ->where('cleanings.cleaner_id', $user->id)
              ->where('bookings.include_cleaning', '!=', 1)
              ->orderBy('booking_date_end', 'asc')
              ->whereIn('bookings.listing_id', $listing_arr_lived)
              ->where('booking_date_end', '>=', $date)
              ->get();
          } 
          else {
          
          
               $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'listings.exp_managers')
                ->join('listings', 'booking_otas_details.listing_id', '=', 'listings.listing_id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->orderBy('departure_date', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('departure_date', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->get();
                
                
                
                $chechoutsLived = Bookings::select('bookings.*', 'listings.exp_managers')
                ->join('listings', 'bookings.listing_id', '=', 'listings.id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->where('bookings.include_cleaning', '!=', 1)
                ->orderBy('booking_date_end', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                    return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->get();
               
             
          }

                 foreach ($chechoutsOta as $items) {
             if ($items->status == 'cancelled') {
                    continue;
                }
            $checkout['id'] = $items->id;
            $checkout['type'] = 'ota';
            $checkout['date'] = $items->departure_date;
            $listing = Listing::where('listing_id', $items->listing_id)->first();
            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->departure_date)->first();
            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            $checkout['status'] == 'completed' ? $taskCompleted += 1 : $taskCompleted + 0;

            array_push($checkoutData, $checkout);
        }
        // dd($checkoutData);
        foreach ($chechoutsLived as $items) {
             if ($items->booking_status == 'cancelled') {
                    continue;
                }
            $checkout['id'] = $items->id;

            $checkout['type'] = 'livedin';
            $checkout['date'] = $items->booking_date_end;
            $listing = Listing::where('id', $items->listing_id)->first();
            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->booking_date_end)->first();

            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            $checkout['status'] == 'completed' ?  $taskCompleted += 1 : $taskCompleted + 0;
            array_push($checkoutData, $checkout);
        }
        
        
        
         //dd($checkoutData);
        //stop this for now
        $deepCleaning = DeepCleaning::orderBy('cleaning_date', 'desc')->where('assignToPropertyManager', $user->id)->select('id', 'listing_title', 'listing_id', 'cleaning_date', 'status')->get();
        
        $deepCleaningCompleted = DeepCleaning::orderBy('cleaning_date', 'desc')->where('assignToPropertyManager', $user->id)->select('id', 'listing_title', 'listing_id', 'cleaning_date', 'status')->where('status', 'completed')->get();
       
        
        $audit = Audit::orderBy('audit_date', 'desc')->where('assignTo', $user->id)->select('id', 'listing_title', 'listing_id', 'audit_date', 'status')->get();
        
        $auditCompleted = Audit::orderBy('audit_date', 'desc')->where('assignTo', $user->id)->select('id', 'listing_title', 'listing_id', 'audit_date', 'status')->where('status', 'completed')->get();
       
        $photoreview = RevenueActivationAudit::with('hostaboard') ->when($user->role_id == 6, function ($query) { return $query->where('task_status' ,'!=', 'no required'); }) ->orderBy('created_at', 'desc') ->select('id', 'hostaboard_id', 'status', 'task_status', 'task_remarks', 'updated_by', 'created_at') ->limit(2) ->get() ->map(function ($item) { if ($item->status === 'mark as done') { $item->status = 'completed'; } return [ 'id' => $item->id, 'hostaboard_id' => $item->hostaboard_id, 'status' => $item->status, 'task_status' => $item->task_status, 'task_remarks' => $item->task_remarks, 'updated_by' => $item->updated_by, 'created_at' => $item->created_at, 'listing_title' => $item->hostaboard->title ?? null, 'city_name' => $item->hostaboard->city_name ?? null, 'owner_name' => $item->hostaboard->owner_name ?? null, 'unit_type' => $item->hostaboard->unit_type ?? null, 'type' => $item->hostaboard->type ?? null, ]; });
        $photoreviewcompleted = RevenueActivationAudit::with('hostaboard') ->when($user->role_id == 6, function ($query) { return $query->whereIn('task_status', ['mark as done', 'Completed']); }) ->orderBy('created_at', 'desc') ->select('id', 'hostaboard_id', 'status', 'task_status', 'task_remarks', 'updated_by', 'created_at') ->limit(2) ->get() ->map(function ($item) { if ($item->status === 'mark as done') { $item->status = 'completed'; } return [ 'id' => $item->id, 'hostaboard_id' => $item->hostaboard_id, 'status' => $item->status, 'task_status' => $item->task_status, 'task_remarks' => $item->task_remarks, 'updated_by' => $item->updated_by, 'created_at' => $item->created_at, 'listing_title' => $item->hostaboard->title ?? null, 'city_name' => $item->hostaboard->city_name ?? null, 'owner_name' => $item->hostaboard->owner_name ?? null, 'unit_type' => $item->hostaboard->unit_type ?? null, 'type' => $item->hostaboard->type ?? null, ]; });

        return response()->json([
            'total_tasks' => count($checkoutData) + count($deepCleaning) + count($audit) + count($photoreview),
            'completed_task' => $taskCompleted + count($auditCompleted) + count($deepCleaningCompleted) + count($photoreviewcompleted)
        ]);
    }
    
    
    public function index(Request $request)
    {
        $listings = Listing::all();
        $user = Auth::user();
        $listing_arr_lived = array();
        $listing_arr_ota = array();
        $date = Carbon::today()->toDateString();
        $checkoutData = array();
        $taskCompleted = 0;
        $startDate = isset($request->start_date) && $request->start_date ? $request->start_date : null;
        $endDate = isset($request->end_date) && $request->end_date ? $request->end_date : null;
        foreach ($listings as $item) {
            $users = json_decode($item['user_id']);
           // dd($users);
            // If it's an array and user ID is inside
        if (is_array($users) && in_array($user->id, $users)) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
        // If it's a single user_id (not an array)
            elseif ((string)$user->id === (string)$item['user_id']) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
        }
        
        $order_by = 'asc';
        isset($endDate) ? $order_by = 'desc' : $order_by = 'asc';
        
        
            if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 2) {
                
                
                $chechoutsOta = BookingOtasDetails::orderBy('departure_date', $order_by)
                ->whereIn('listing_id', $listing_arr_ota)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                    return $query->where('departure_date', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->get();
                
                $chechoutsLived = Bookings::orderBy('booking_date_end', $order_by)
                ->whereIn('listing_id', $listing_arr_lived)
                ->where('bookings.include_cleaning', '!=', 1)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->get();
                
            }
            elseif($user->role_id === 9)
            {
                
               $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'cleanings.cleaner_id')
                ->join('cleanings', 'cleanings.booking_id', '=', 'booking_otas_details.id')
                ->where('cleanings.cleaner_id', $user->id)
                ->orderBy('departure_date', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('departure_date', '>=', $date);
                })->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->orderBy('cleanings.cleaner_assign_datetime', 'asc')->get();
                
                
               $chechoutsLived = Bookings::select('bookings.*', 'cleanings.cleaner_id')
                ->join('cleanings', 'cleanings.booking_id', '=', 'bookings.id')
                ->where('cleanings.cleaner_id', $user->id)
                ->where('bookings.include_cleaning', '!=', 1)
                ->orderBy('booking_date_end', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->orderBy('cleanings.cleaner_assign_datetime', 'asc')->get();
                
            }
            else
            {
                $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'listings.exp_managers')
                ->join('listings', 'booking_otas_details.listing_id', '=', 'listings.listing_id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->orderBy('departure_date', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('departure_date', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->get();
                
                $chechoutsLived = Bookings::select('bookings.*', 'listings.exp_managers')
                ->join('listings', 'bookings.listing_id', '=', 'listings.id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->where('bookings.include_cleaning', '!=', 1)
                ->orderBy('booking_date_end', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                    return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->get();
                
            }

        
        
        if (count($chechoutsOta) > 0) {
            foreach ($chechoutsOta as $items) {
                if ($items->status == 'cancelled') {
                    continue;
                }
                $checkout['id'] = $items->id;
                $checkout['type'] = 'ota';
                $checkout['date'] = $items->departure_date;
                $listing = Listing::where('listing_id', $items->listing_id)->first();
                $Channels = Channels::where('id', $listing->channel_id)->first();
                
                if($Channels->connection_type != null)
                {
                    $listing = Listing::where('listing_id', $chechoutsOta->listing_id)->first();
                    $listingRelation = ListingRelation::where('listing_id_other_ota', $listing->id)->first();
                    $listing = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
                }
                
                
                $listing_json = json_decode($listing->listing_json);
                $checkout['listing_id'] = $listing->id;
                $checkout['listing_title'] = $listing_json->title;
                $has_booking = BookingOtasDetails::where('listing_id', $listing->listing_id)->where('status', '!=', 'cancelled')->where('arrival_date', $checkout['date'])->get();
                $has_bookingLived = Bookings::where('listing_id', $listing->id)->where('booking_status', '!=', 'cancelled')->where('booking_date_start', $checkout['date'])->get();
                $checkout['has_checkin'] = isset($has_booking) && count($has_booking) > 0 || isset($has_bookingLived) && count($has_bookingLived) > 0 ? true : false;
                $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->departure_date)->first();
                $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
                $cleaning === null ? $checkout['cleaner_assign_datetime'] = '' : $checkout['cleaner_assign_datetime'] = $cleaning->cleaner_assign_datetime;
                isset($cleaning->status) && $cleaning->status == 'completed' ? $taskCompleted + 1 : $taskCompleted + 0;
                array_push($checkoutData, $checkout);
            }
        }

        if (count($chechoutsLived) > 0) {
            foreach ($chechoutsLived as $items) {
                if ($items->booking_status == 'cancelled') {
                    continue;
                }
                $checkout['id'] = $items->id;
                $checkout['type'] = 'livedin';
                $checkout['date'] = $items->booking_date_end;
                $listing = Listing::where('id', $items->listing_id)->first();
                $listing_json = json_decode($listing->listing_json);
                $checkout['listing_id'] = $listing->id;
                $checkout['listing_title'] = $listing_json->title;
                $has_booking = BookingOtasDetails::where('listing_id', $listing->listing_id)->where('status', '!=', 'cancelled')->where('arrival_date', $checkout['date'])->get();
                $has_bookingLived = Bookings::where('listing_id', $listing->id)->where('booking_status', '!=', 'cancelled')->where('booking_date_start', $checkout['date'])->get();
                $checkout['has_checkin'] = isset($has_booking) && count($has_booking) > 0 || isset($has_bookingLived) && count($has_bookingLived) > 0 ? true : false;
                $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->booking_date_end)->first();
                $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
                $cleaning === null ? $checkout['cleaner_assign_datetime'] = '' : $checkout['cleaner_assign_datetime'] = $cleaning->cleaner_assign_datetime;
                isset($cleaning->status) && $cleaning->status == 'completed' ? $taskCompleted + 1 : $taskCompleted + 0;
                array_push($checkoutData, $checkout);
            }
        }
        
        if($endDate != null) {
            
            usort($checkoutData, function ($a, $b) {
                
                $dateCompare = strtotime($b['date']) - strtotime($a['date']);
        
        
            if ($dateCompare == 0) {
                return strtotime($a['cleaner_assign_datetime']) - strtotime($b['cleaner_assign_datetime']);
             }

                return $dateCompare;
            });
        }
        
        else {
            usort($checkoutData, function ($a, $b) {
                $dateCompare = strtotime($a['date']) - strtotime($b['date']);
        
                // Agar date same hai, to cleaner_assign_datetime ascending order mein sort karein
                if ($dateCompare == 0) {
                    return strtotime($a['cleaner_assign_datetime']) - strtotime($b['cleaner_assign_datetime']);
                }

                return $dateCompare;
            });
        }

        return response()->json($checkoutData);

    }

    public function getCleaningCount(Request $request)
    {
        $listings = Listing::all();
        $user = Auth::user();
        $listing_arr_lived = array();
        $listing_arr_ota = array();
        foreach ($listings as $item) {
            $users = json_decode($item['user_id']);


            if (is_array($users) && in_array($user->id, $users)) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
            // If it's a single user_id (not an array)
            elseif ((string)$user->id === (string)$item['user_id']) {
                array_push($listing_arr_lived, $item['id']);
                array_push($listing_arr_ota, $item['listing_id']);
            }
            
        }

        $date = Carbon::today()->toDateString();
        $checkoutData = array();
        $taskCompleted = 0;
        $startDate = isset($request->start_date) && $request->start_date ? $request->start_date : null;
        $endDate = isset($request->end_date) && $request->end_date ? $request->end_date : null;
        $order_by = 'asc';
        isset($endDate) ? $order_by = 'desc' : $order_by = 'asc';
       
            if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 2) {
                
                
                $chechoutsOta = BookingOtasDetails::orderBy('departure_date', $order_by)
                ->whereIn('listing_id', $listing_arr_ota)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                    return $query->where('departure_date', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->get();
                
                $chechoutsLived = Bookings::orderBy('booking_date_end', $order_by)
                ->whereIn('listing_id', $listing_arr_lived)
                ->where('bookings.include_cleaning', '!=', 1)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->get();
                
            }
            elseif($user->role_id === 9)
            {
                
               $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'cleanings.cleaner_id')
                ->join('cleanings', 'cleanings.booking_id', '=', 'booking_otas_details.id')
                ->where('cleanings.cleaner_id', $user->id)
                ->orderBy('departure_date', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('departure_date', '>=', $date);
                })->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->get();
                
                
               $chechoutsLived = Bookings::select('bookings.*', 'cleanings.cleaner_id')
                ->join('cleanings', 'cleanings.booking_id', '=', 'bookings.id')
                ->where('cleanings.cleaner_id', $user->id)
                ->where('bookings.include_cleaning', '!=', 1)
                ->orderBy('booking_date_end', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                    return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->get();
                
                
                
            }
            else
            {
                $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'listings.exp_managers')
                ->join('listings', 'booking_otas_details.listing_id', '=', 'listings.listing_id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->orderBy('departure_date', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                return $query->where('departure_date', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('departure_date', [$startDate, $endDate]);
                })->get();
                
                $chechoutsLived = Bookings::select('bookings.*', 'listings.exp_managers')
                ->join('listings', 'bookings.listing_id', '=', 'listings.id')
                ->whereJsonContains('listings.exp_managers', (string)$user->id)
                ->where('bookings.include_cleaning', '!=', 1)
                ->orderBy('booking_date_end', $order_by)
                ->when(!$startDate || !$endDate, function ($query) use ($date) {
                    return $query->where('booking_date_end', '>=', $date);
                })
                ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                })->get();
                
                
                
            }
       
       
        foreach ($chechoutsOta as $items) {
             if ($items->status == 'cancelled') {
                    continue;
                }
            $checkout['id'] = $items->id;
            $checkout['type'] = 'ota';
            $checkout['date'] = $items->departure_date;
            $listing = Listing::where('listing_id', $items->listing_id)->first();
            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->departure_date)->first();
            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            $checkout['status'] == 'completed' ? $taskCompleted += 1 : $taskCompleted + 0;

            array_push($checkoutData, $checkout);
        }
        // dd($checkoutData);
        foreach ($chechoutsLived as $items) {
             if ($items->booking_status == 'cancelled') {
                    continue;
                }
            $checkout['id'] = $items->id;

            $checkout['type'] = 'livedin';
            $checkout['date'] = $items->booking_date_end;
            $listing = Listing::where('id', $items->listing_id)->first();
            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $items->booking_date_end)->first();

            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            $checkout['status'] == 'completed' ?  $taskCompleted += 1 : $taskCompleted + 0;
            array_push($checkoutData, $checkout);
        }
    
        return response()->json([
            'total_tasks' => count($checkoutData),
            'completed_task' => $taskCompleted
        ]);
    }

    public function store(Request $request)
    {
        $cleaning = Cleaning::where('booking_id', $request->booking_id)->where('listing_id', $request->listing_id)->where('checkout_date', $request->checkout_date)->first();
        // dd( $cleaning);

        $statusFlow = [
            'pending' => 'on the way',
            'on the way' => 'start',
            'start' => 'resume',
            'resume' => 'resume' // Ensures it remains "Complete"
        ];
        
        $next_status = $statusFlow[$request->status] ?? 'on the way';
        $user_id = Auth::user()->id;


        if (isset($cleaning->status) && $cleaning->status === 'completed') {
            return response()->json([
                'status' => 200,
                'message' => 'Cleaning Task Already Updated',
                'cleaning' => $cleaning
            ]);
        } else {
            try {
                if($cleaning) {

                   
                    $cleaning = $cleaning->update([
                        'cleaning_date' => Carbon::now()->toDateString(),
                        'cleaning_time' => Carbon::now()->toTimeString(),
                        'status' => $request->status
                    ]);
                    
                    

                    $cleaning = Cleaning::where('booking_id', $request->booking_id)->where('listing_id', $request->listing_id)->where('checkout_date', $request->checkout_date)->first();

                    CleaningStatusLog::create([
                        'cleaning_id' => $cleaning->id,
                        'user_id' => $user_id, 
                        'status' => $request->status
                    ]);
                    
                    return response()->json([
                        'status' => 204,
                        'message' => 'Cleaning Task Updated Successfully',
                        'cleaning' => $cleaning,
                        'next_status' => $next_status
                    ]);

                }else {
                   
                   
                    $cleaning = Cleaning::create([
                        'booking_id' => $request->booking_id,
                        'listing_id' => $request->listing_id,
                        'checkout_date' => $request->checkout_date,
                        'cleaning_date' => Carbon::now()->toDateString(),
                        'cleaning_time' => Carbon::now()->toTimeString(),
                        'status' => $request->status
                    ]);

                    

                    $cleaning = Cleaning::where('booking_id', $request->booking_id)->where('listing_id', $request->listing_id)->where('checkout_date', $request->checkout_date)->first();
                    
                    CleaningStatusLog::create([
                        'cleaning_id' => $cleaning->id,
                        'user_id' => $user_id, 
                        'status' => $request->status
                    ]);

                    return response()->json([
                        'status' => 204,
                        'message' => 'Cleaning Task Updated Successfully',
                        'cleaning' => $cleaning,
                        'next_status' => $next_status
                    ]);
                }

            } catch (\Exception $e) {
                return response($e);
            }
        }
    }
    
    public function show(Request $request, $id)
    {
        // dd($request);
        if (isset($request->type) && $request->type == 'ota') {
            $booking = BookingOtasDetails::where('id', $id)->first();
            $booking_json = json_decode($booking->booking_otas_json_details);
            
            $raw_message = json_decode($booking_json->attributes->raw_message ?? '{}');

            $check_in_datetime = $raw_message->reservation->check_in_datetime ?? null;
            $check_out_datetime = $raw_message->reservation->check_out_datetime ?? null;
          
             

            $checkin_clean = preg_replace('/\[.*\]/', '', $check_in_datetime);
            $checkout_clean = preg_replace('/\[.*\]/', '', $check_out_datetime);
            
            // Convert to Carbon instance and format
            $check_in_datetime = Carbon::parse($checkin_clean)->format('d M Y H:i');
            $check_out_datetime = Carbon::parse($checkout_clean)->format('d M Y H:i');
            
            $check_out_date = Carbon::parse($checkout_clean)->format('Y-m-d');
            
            
            $booking_json = $booking_json->attributes;
            $listing = Listing::where('listing_id', $booking->listing_id)->first();
            $Channels = Channels::where('id', $listing->channel_id)->first();
                
            if($Channels->connection_type != null)
            {
                $listing = Listing::where('listing_id', $chechoutsOta->listing_id)->first();
                $listingRelation = ListingRelation::where('listing_id_other_ota', $listing->id)->first();
                $listing = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
            }
            
            
            $google_map = $listing->google_map;
            
            
            $listing_json = json_decode($listing->listing_json);
            $listing_users = json_decode($listing->user_id);
            $listing_user = isset($listing_users) && count($listing_users) > 0 ? $listing_users[count($listing_users) -1] : $listing_users[0];
            $listing_user = (int)$listing_user;
            $host = User::whereId($listing_user)->select('name', 'surname', 'email', 'phone')->first();
            $guest = $booking_json->customer;
            $cleaning = Cleaning::where('booking_id', $id)->where('listing_id', $listing->id)->first();
            
            if ($cleaning === null) {
    
                $cleaning = Cleaning::create([
               'booking_id' => $id, 
               'listing_id' => $listing->id, 
               'status' => 'pending',
               'checkout_date' => $check_out_date,
               ]);
 

                $cleaning = Cleaning::where('booking_id', $id)->where('listing_id', $listing->id)->first();
            }
            
            
         
            $generatechecklist = DB::select("CALL insert_property_checklist_v2($listing->id);");
            
            if ($cleaning !== null) {
               
                $cleaner = User::whereId($cleaning->cleaner_id)->select('name', 'surname')->first();
                $cleaning['cleaner_Name'] = $cleaner !== null 
                    ? $cleaner->name . ' ' . $cleaner->surname 
                    : ''; 
            } else {
                $cleaning['cleaner_Name'] = ''; 
            }
            
            isset($cleaning->id) ? $comments = CleaningComment::where('cleaning_id', $cleaning->id)->orderBy('id', 'desc')->get() : $comments = null;
            isset($comments) ? $cleaning['remarks'] = $comments : $cleaning['remarks'] = '';
            
            isset($cleaning->id) ? $cleaningimages = Cleaningimages::where('cleaning_id', $cleaning->id)->orderBy('id', 'desc')->get() : $cleaningimages = null;
            
            
            if (isset($cleaningimages) && $cleaningimages->isNotEmpty()) {
                $cleaning['cleaningimages'] = $cleaningimages->map(function ($image) {
                    
                $image->file_path = url('public/storage/' . $image->file_path); 

                return $image;
               });
            } 
            else {
                $cleaning['cleaningimages'] = []; 
            }

            $cleaning['status'] = (empty($cleaning) || !isset($cleaning['status'])) 
            ? 'pending' 
            : $cleaning['status'];

            $statusFlow = [
                'pending' => 'on the way',
                'on the way' => 'start',
                'start' => 'resume',
                'resume' => 'resume' 
            ];
            
            $cleaning['next_status'] = $statusFlow[$cleaning['status']] ?? 'on the way';
            
            $cleaning['key_code'] = (empty($cleaning) || !isset($cleaning['key_code'])) ? '' : $cleaning['key_code'];

            $cleaning['checkin_datetime'] = $check_in_datetime;
            $cleaning['checkout_datetime'] = $check_out_datetime; 
            $cleaning['cleaner_assign_datetime'] = Carbon::parse($cleaning->cleaner_assign_datetime)->format('d M Y h:i');
            
             
            if (!empty($listing->exp_managers)) {
                $exp_managers = json_decode($listing->exp_managers);
            
           
                if ($exp_managers && !empty($exp_managers)) {
                   
                   
                    $user = User::where('id', $exp_managers[0])->first();
            
                    if ($user) {
                        $cleaning['poc'] = [
                            'name' => $user->name,
                            'surname' => $user->surname,
                            'email' => $user->email,
                            'phone' => $user->phone,
                        ];
                    } else {
                        $cleaning['poc'] = ""; 
                    }
                } else {
                    $cleaning['poc'] = ""; 
                }
            } else {
                $cleaning['poc'] = ""; 
            }
            
            $cleaning['guest'] = $guest;
            $cleaning['host'] = $host;
            $cleaning['listing_title'] = $listing_json->title;
            $cleaning['property_type'] = 'TD';
            $cleaning['location'] = $listing->google_map;

            return response($cleaning);
        } else {
            $booking = Bookings::where('id', $id)->first();
            
            
            
            $listing = Listing::where('id', $booking->listing_id)->first();
            
            $google_map = $listing->google_map;
            
            
            $listing_json = json_decode($listing->listing_json);
            $listing_users = json_decode($listing->user_id);
            $listing_user = isset($listing_users) && count($listing_users) > 0 ? $listing_users[count($listing_users) -1] : $listing_users[0];
            $listing_user = (int)$listing_user;
            $host = User::whereId($listing_user)->select('name', 'surname', 'email', 'phone')->first();
            $cleaning = Cleaning::where('booking_id', $id)->where('listing_id', $listing->id)->first();
            
            if ($cleaning === null) {
    
                $cleaning = Cleaning::create([
               'booking_id' => $id, 
               'listing_id' => $listing->id, 
               'status' => 'pending',
               'checkout_date' => $booking->booking_date_end
            ]);


                $cleaning = Cleaning::where('booking_id', $id)->where('listing_id', $listing->id)->first();
           }

           $generatechecklist = DB::select("CALL insert_property_checklist_v2($listing->id);");



            if ($cleaning !== null) {
                $cleaner = User::whereId($cleaning->cleaner_id)->select('name', 'surname')->first();
                $cleaning['cleaner_Name'] = $cleaner !== null 
                    ? $cleaner->name . ' ' . $cleaner->surname 
                    : ''; 
            } else {
                $cleaning['cleaner_Name'] = ''; 
            }

            $cleaning['status'] = (empty($cleaning) || !isset($cleaning['status'])) 
            ? 'pending' 
            : $cleaning['status'];

            $statusFlow = [
                'pending' => 'on the way',
                'on the way' => 'start',
                'start' => 'resume',
                'resume' => 'resume' // Ensures it remains "Complete"
            ];
            
            $cleaning['next_status'] = $statusFlow[$cleaning['status']] ?? 'on the way';
            
            $checkin_time = '15:00';
            $checkout_time = '12:00';

            $cleaning['checkin_datetime'] = Carbon::parse($booking->booking_date_start)->format('d M Y') . " " . $checkin_time;
            $cleaning['checkout_datetime'] = Carbon::parse($booking->booking_date_end)->format('d M Y') . " " . $checkout_time;
            $cleaning['cleaner_assign_datetime'] = Carbon::parse($cleaning->cleaner_assign_datetime)->format('d M Y h:i');
            
            $cleaning['key_code'] = (empty($cleaning) || !isset($cleaning['key_code'])) ? '' : $cleaning['key_code'];

            if (!empty($listing->exp_managers)) {
                $exp_managers = json_decode($listing->exp_managers);
            
                if ($exp_managers && !empty($exp_managers)) {
                   
                    $user = User::where('id', $exp_managers[0])->first();
            
                    if ($user) {
                        $cleaning['poc'] = [
                            'name' => $user->name,
                            'surname' => $user->surname,
                            'email' => $user->email,
                            'phone' => $user->phone,
                        ];
                    } else {
                        $cleaning['poc'] = ""; 
                    }
                } else {
                    $cleaning['poc'] = ""; 
                }
            } else {
                $cleaning['poc'] = ""; 
            }


            $cleaning['guest'] = array(
                'name' => $booking->name,
                'surname' => $booking->surname,
                'email' => $booking->email,
                'phone' => $booking->phone,
            );
            $cleaning['host'] = $host;
            

            isset($cleaning->id) ? $comments = CleaningComment::where('cleaning_id', $cleaning->id)->orderBy('id', 'desc')->get() : $comments = null;
            isset($comments) ? $cleaning['remarks'] = $comments : $cleaning['remarks'] = '';


            isset($cleaning->id) ? $cleaningimages = Cleaningimages::where('cleaning_id', $cleaning->id)->orderBy('id', 'desc')->get() : $cleaningimages = null;
            
            if (isset($cleaningimages) && $cleaningimages->isNotEmpty()) {
                $cleaning['cleaningimages'] = $cleaningimages->map(function ($image) {
                    
                $image->file_path = url('public/storage/' . $image->file_path); 

                return $image;
               });
            } 
             
            else {
                $cleaning['cleaningimages'] = []; 
            }

            
            
           

            $cleaning['listing_title'] = $listing_json->title;
            $cleaning['location'] = $listing->google_map;
            $cleaning['property_type'] = 'ST';

            $cleaning['location'] = $listing->google_map;
            
            // dd($cleaning);
            return response($cleaning);

        }

    }
    
    public function createCleaningComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cleaning_id' => 'required',
            'comments' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $data  = $request->all();
        $user_id = Auth::user()->id;
        $data['user_id'] = $user_id;
        // dd($data, $user_id);
        CleaningComment::create($data);
        return response()->json([
            'status' => 'Success',
            'message' => 'Comment Created Successfully'
        ]);
    }
    
    
    
    public function uploadMultipleImages(Request $request)
    {
        // dd($request->file);
        try 
        {
          $request->validate([
            'cleaning_id' => 'required|integer',
            'file' => 'required', // Ensures 'file' input exists
            //'file.*' => 'image|mimes:jpg,jpeg,png,gif|max:2048' // Validates each image
         ]);
    
        $filePaths = []; 
    
        
        if ($request->hasFile('file')) {
        // dd($request->file('file'));
            $images = $request->file('file');
    
           
            if (!is_array($images)) {
                $images = [$images];
            }
    
            try {
                foreach ($images as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                       
                        $fileName = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
                                                // dd($fileName);

                       
                        $filePath = $img->storeAs('cleaning_images', $fileName, 'public');
                       
                        
                        Cleaningimages::create([
                            'cleaning_id' => $request->input('cleaning_id'),
                            'file_path' => $filePath,
                        ]);
    
                         $fullUrl = url('public/storage/' . $filePath);
                        
                         $filePaths[] = $fullUrl;
                       
                        Log::info('Image uploaded and saved', ['file_path' => $filePath]);
                    }
                }
    
                
                return response()->json([
                    'message' => 'Images uploaded successfully!',
                    'paths' => $filePaths
                    
                ], 201);
    
            } catch (\Exception $e) {
               
                Log::error('Error uploading images', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Failed to upload images',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    
        
        return response()->json([
            'message' => 'No images uploaded'
        ], 400);
        }
        catch (\Exception $e) {
            Log::error('Error uploading images', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function deleteImage(Request $request)
    {
        $request->validate([
            'image_id' => 'required|integer', // Image ID to be deleted
        ]);
    
        try {
            // Fetch the image from the database using the provided image ID
            $image = Cleaningimages::find($request->input('image_id'));
    
            if (!$image) {
                return response()->json([
                    'message' => 'Image not found'
                ], 404);
            }
    
            // Get the file path from the database record
            $filePath = $image->file_path;
    
            // Check if the file exists and delete it from storage
            if (Storage::disk('public')->exists($filePath)) {
                // Delete the image from the storage
                Storage::disk('public')->delete($filePath);
            }
    
            // Delete the image record from the database
            $image->delete();
    
            // Log the action
            Log::info('Image deleted successfully', ['file_path' => $filePath]);
    
            return response()->json([
                'message' => 'Image deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            // Log error if anything goes wrong
            Log::error('Error deleting image', ['error' => $e->getMessage()]);
    
            return response()->json([
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function GetCleaningChecklist($cleaning_id)
    {
        try {
            $user_id = Auth::user()->id;
    
           
            $checklist = DB::select("CALL get_cleaning_checklist_v2($cleaning_id);");
            $totalTasksSum = collect($checklist)->sum('total_tasks');
            $completedTasksSum = collect($checklist)->sum('completed_tasks');
            
            return response()->json(['tasks' => $checklist,'sum_of_tasks' => $totalTasksSum,
            'sum_of_completed_tasks' => $completedTasksSum], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }


    public function GetCleaningChecklist_detail($property_checklist_id,$cleaning_id)
    {
        try {
            $user_id = Auth::user()->id;
            $checklist = DB::select("CALL get_cleaning_checklist_detail_v2($property_checklist_id,$cleaning_id);");
            return response()->json(['task_detail' => $checklist], 200);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }
    
    public function updateChecklistTask(Request $request)
    {

        
        try {

            //dd($checklistData);
            $checklistData = $request->all();
            foreach ($checklistData as $item) {
                
                $task = CleaningTask::where('property_checklist_id', $item['checklist_id'])->where('cleaning_id', $item['cleaning_id'])->first();
    
                if ($task) {
                    
                    $task->update([
                        'is_completed' => $item['is_completed']
                    ]);
                } else {
                    
                    CleaningTask::create([
                        'property_checklist_id' => $item['checklist_id'],
                        'listing_id' => $item['listing_id'],
                        'cleaning_id' => $item['cleaning_id'],
                        'is_completed' => $item['is_completed']
                        
                    ]);
                }
            }
    
        return response()->json(['message' => 'Checklist tasks updated successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating checklist tasks', 'error' => $e->getMessage()], 500);
        }
    }
    
}

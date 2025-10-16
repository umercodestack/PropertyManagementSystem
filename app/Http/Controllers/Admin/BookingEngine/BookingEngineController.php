<?php

namespace App\Http\Controllers\Admin\BookingEngine;

use App\Http\Controllers\Controller;
use App\Imports\BookingImport;
use App\Models\Apartments;
use App\Models\RatePlan;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Calender;
use App\Models\Guests;
use App\Models\Listings;
use App\Models\Listing;
use App\Models\Properties;
use App\Models\RoomType;
use App\Models\User;
use App\Models\BookingImages;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Services\StoreProcedureService;
use App\Models\Vendors;
use Twilio\Rest\Client;
use App\Models\ListingRelation;

use Illuminate\Support\Facades\Storage;




class BookingEngineController extends Controller
{
    public function checkinpdf(Request $request)
    {
        
            $booking_id = $request->query('booking_id');
            $booking = Bookings::find($booking_id);

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
            
            $pdfData = json_decode(json_encode($emailData), true); 
            
            return view('mail.checkin_pdf', compact('emailData'));
       
    }
    
    public function checkoutpdf(Request $request)
    {
        
        $booking_id = $request->query('booking_id');

        
        $booking = Bookings::find($booking_id);

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
            
           
            
        return view('mail.checkout_pdf', compact('emailData'));
   
    }
    
}

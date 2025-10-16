<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Http\Controllers\Controller;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Calender;
use App\Models\Channels;
use App\Models\ChurnedProperty;
use App\Models\ListingRelation;
use App\Models\Listings;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;

class RevenueManagementController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->all());

        list($yearReq, $monthReq) = explode('-', $request->month);

        $month = $monthReq;
        $year = $yearReq;
        $start = new DateTime("$year-$month-01");
        $end = clone $start;
        $end->modify('last day of this month');
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
        $listings = \DB::table('listings')->get();
        $occupancies = array();
        $occupancy_header = [
            $occupancy['apartment_title'] = 'Apartment Title',
            $occupancy['live_date'] = 'Live Date',
        ];
        $now = $request->month.'-1';

        foreach ($period as $index => $item) {
            array_push($occupancy_header, $item->format('Y-m-d'));
        }
        array_push($occupancies, $occupancy_header);
        foreach ($listings as $key => $items) {
              $channel = Channels::where('id', $items->channel_id)->first();
            if(isset($channel->connection_type) && $channel->connection_type != null) {
                continue;
            }
             $churnedProperty = ChurnedProperty::where('listing_id', $items->listing_id)->first();

            if($churnedProperty) {
                $churnedDate = $churnedProperty->churned_date;
                $churnedDate = Carbon::parse($churnedDate);
                $now =Carbon::parse($now);
                 if ($now->gte($churnedDate)) {
                    continue;
                }
            }
            $occupancy['apartment_title'] = 'Apartment Title';
            $occupancy['live_date'] = 'Live Date';
            $calendar = \DB::table('calenders')
                ->where('listing_id', $items->listing_id)
                ->whereMonth('calender_date', $month)
                ->whereYear('calender_date', $year)
                ->orderBy('calender_date', 'asc')
                ->pluck('calender_date');
            $listing_json = json_decode($items->listing_json);
            $occupancy['apartment_title'] = $listing_json->title;
            $occupancy['live_date'] = Carbon::parse($items->created_at)->toDateString();
            $count_dates = 0;
            $calendar = $calendar->toArray();
            foreach ($period as $index => $item) {
                if (in_array($item->format('Y-m-d'), $calendar)) {
                    // if (!isset($items->listing_id)) {
                    //     dd($items);
                    // }
                    $listingIdArr = [$items->listing_id];
                    $listingRelation = ListingRelation::where('listing_id_airbnb', $items->id)->get();
                        if($listingRelation) {
                            foreach($listingRelation as $it) {
                                 $listing_Bcom = Listings::where('id',$it->listing_id_other_ota)->first();
                                 $listingIdArr[] = $listing_Bcom->listing_id;
                            }
                            
                            $otaBookingData = BookingOtasDetails::whereIn('listing_id', $listingIdArr)
                    ->whereDate('departure_date', '>', $item->format('Y-m-d'))
                    ->whereDate('arrival_date', '<=', $item->format('Y-m-d'))
                    ->where('status', '!=', 'cancelled')
                    ->select('id', 'arrival_date', 'departure_date', 'status', 'booking_otas_json_details')
                    ->first();
                        }else {
                            $otaBookingData = BookingOtasDetails::where('listing_id', $items->listing_id)
                    ->whereDate('departure_date', '>', $item->format('Y-m-d'))
                    ->whereDate('arrival_date', '<=', $item->format('Y-m-d'))
                    ->where('status', '!=', 'cancelled')
                    ->select('id', 'arrival_date', 'departure_date', 'status', 'booking_otas_json_details')
                    ->first();
                       
                        }
                         
                     $livedInBookingData = Bookings::where('listing_id', $items->id)
                    ->whereDate('booking_date_end', '>', $item->format('Y-m-d'))
                    ->whereDate('booking_date_start', '<=', $item->format('Y-m-d'))
                    ->where('booking_status', '!=', 'cancelled')
                    ->select('id', 'name', 'surname', 'booking_date_start', 'booking_date_end', 'booking_status', 'per_night_price','cleaning_fee', 'booking_status')
                    ->first();
                        // dd($otaBookingData->toArray(),$livedInBookingData->toArray);
                    if ($livedInBookingData) {
                        $start = Carbon::parse($livedInBookingData->booking_date_start);
                        $end = Carbon::parse($livedInBookingData->booking_date_end);

                        // Calculate the difference in days
                        $daysCount = $start->diffInDays($end);
                        if($daysCount == 0) {
                            $daysCount = 1;
                        }
                        // dd($item->format('Y-m-d'), $livedInBookingData->per_night_price, $daysCount);
                        $occupancy[$item->format('Y-m-d')] = round($livedInBookingData->per_night_price+($livedInBookingData->cleaning_fee/$daysCount), 2);
                    } else if ($otaBookingData) {
                        $start = Carbon::parse($otaBookingData->arrival_date);
                        $end = Carbon::parse($otaBookingData->departure_date);

                        // Calculate the difference in days
                        $daysCount = $start->diffInDays($end);
                        if($daysCount == 0) {
                            $daysCount = 1;
                        }
                        $otaBookingDataJson = json_decode($otaBookingData->booking_otas_json_details);
                        $raw_message = json_decode($otaBookingDataJson->attributes->raw_message);
                        $discount = 0;
                        $promotion = 0;
                        if (isset($raw_message->reservation->pricing_rule_details)) {
                            foreach ($raw_message->reservation->pricing_rule_details as $dis) {
                                $dis->amount_native = (int) $dis->amount_native;
                                if($dis->amount_native > 0) {
                                    $raw_message->reservation->listing_base_price_accurate -= $dis->amount_native;
                                }
                                else {
                                    $raw_message->reservation->listing_base_price_accurate += abs($dis->amount_native);
                                    }
                                // $discount += abs($dis->amount_native);
                            }
                        }
                        // dd($raw_message->reservation->promotion_details, $raw_message->reservation->pricing_rule_details);
                        if (isset($raw_message->reservation->promotion_details)) {
                            foreach ($raw_message->reservation->promotion_details as $prop) {
                                $prop->amount_native = (int) $prop->amount_native;
                                if($prop->amount_native > 0) {
                                    $raw_message->reservation->listing_base_price_accurate -= $prop->amount_native;
                                }
                                else {
                                    $raw_message->reservation->listing_base_price_accurate += abs($prop->amount_native);
                                }
                                // $promotion += abs($prop->amount_native);
                            }
                        }
                        if (isset($raw_message->reservation->standard_fees_details)) {
                            foreach ($raw_message->reservation->standard_fees_details as $prop) {
                                $prop->amount_native = (int) $prop->amount_native;
                                $raw_message->reservation->listing_base_price_accurate += $prop->amount_native;
                                // if($prop->amount_native > 0) {
                                //     $raw_message->reservation->listing_base_price_accurate -= $prop->amount_native;
                                // }
                                // else {
                                //     $raw_message->reservation->listing_base_price_accurate += abs($prop->amount_native);
                                // }
                                // $promotion += abs($prop->amount_native);
                            }
                        }
                        // $cleaning_fees = isset($otaBookingDataJson->attributes->rooms[0]->services[0]->total_price) ? (int) $otaBookingDataJson->attributes->rooms[0]->services[0]->total_price : 0;
                        // dd($raw_message);
                        // $raw_message->reservation->listing_base_price_accurate = ($raw_message->reservation->listing_base_price_accurates);
                        // dd($daysCount, $raw_message->reservation->listing_base_price_accurate);
                        // $raw_message->reservation->listing_base_price_accurate = ($raw_message->reservation->listing_base_price_accurate - abs($promotion) - abs($discount));

                        // dd($raw_message, $item->format('Y-m-d'), $otaBookingDataJson, $daysCount);
                                                $cleaning_fee = isset($raw_message->reservation->standard_fees_details[0]->amount_native) ? $raw_message->reservation->standard_fees_details[0]->amount_native :0;
                        $occupancy[$item->format('Y-m-d')] = round(($raw_message->reservation->listing_base_price_accurate) / $daysCount);
                    } else {
                        $occup = Calender::where('listing_id', $items->listing_id)->where('calender_date', $item->format('Y-m-d'))->first();
                        if($occup->is_lock ==1) {
                            $occupancy[$item->format('Y-m-d')] = 'Block';
                        }
                        else {
                            $occupancy[$item->format('Y-m-d')] = 'Available';
                        }
                    }
                    // if ($occup->availability == 1) {
                    // $occupancy[$item->format('Y-m-d')] = $occup->rate;
                    // }
                } else {
                    $occupancy[$item->format('Y-m-d')] = 'Block';
                }
            }
            array_push($occupancies, $occupancy);
        }
        // dd($occupancies);
        return view('Admin.reports.revenue', ['occupancies' => $occupancies]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{
    User,
    Listing,
    Calender,
    Bookings,
    BookingOtasDetails,
    Cleaning,
    DeepCleaning,
    Audit,
    Guests,
    RoomType,
    Properties,
    Review,
    ChurnedProperty
};
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardApiController extends Controller
{
    public function get_data(Request $request)
    {

        try {

            if ($request->offset == null || $request->offset == "") {
                return response()->json(['error' => 'offset is required']);
            }

            if ($request->limit == null || $request->limit == "") {
                return response()->json(['error' => 'limit is required']);
            }

            if ($request->limit > 10) {
                return response()->json(['error' => 'Maximum limit is 10']);
            }

            $listings = Listing::where([
                'is_sync' => 'sync_all'
            ])
                ->offset($request->offset)
                ->limit($request->limit)
                ->get();

            if (empty($listings)) {
                return response()->json(['error' => 'listing data is not found']);
            }

            $data = [];
            foreach ($listings as $listing) {

                // Get Host
                $record['host'] = [];
                $user_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];
                if (!empty($user_arr)) {
                    $record['host'] = User::whereIn('id', $user_arr)->get();
                }

                // Get Listing
                $record['listing'] = $listing;

                $churned_txt = 'no';
                $is_churned = ChurnedProperty::where('listing_id', $listing->listing_id)->first();
                if ($is_churned) {
                    $churned_txt = 'yes';
                    $record['churned_date'] = $is_churned->churned_date;
                }

                $record['churned'] = $churned_txt;


                // Get Calendar
                $calendars = Calender::where('listing_id', $listing->listing_id)->get();
                $record['calendar'] = !empty($calendars) ? $calendars : [];

                $booking_arr = [];
                $booking = Bookings::where('listing_id', $listing->id)
                    ->where('booking_status', '!=', 'cancelled')
                    ->selectRaw('ROUND(SUM(total_price), 2) as total_price, ROUND(SUM(custom_discount), 2) as discount, ROUND(SUM(service_fee), 2) as service_fee, ROUND(SUM(ota_commission), 2) as ota_commission, ROUND(SUM(cleaning_fee), 2) as cleaning_fee, ROUND(SUM(per_night_price), 2) as per_night_price')
                    ->first();

                $booking->host_commission = !empty($listing->commission_value) ? round((float) $listing->commission_value / 100 * $booking->total_price, 2) : 0;

                $booking->livedin_commission = round((float) $booking->total_price - $booking->discount - $booking->host_commission - $booking->ota_commission, 2);

                $booking_arr['livedin'] = $booking;

                $ota_bookings = BookingOtasDetails::where('listing_id', $listing->listing_id)
                    ->where('status', '!=', 'cancelled')
                    ->get();

                $ota_bookings_data = [
                    'total_price' => 0,
                    'discount' => 0,
                    'service_fee' => 0,
                    'ota_commission' => 0,
                    'cleaning_fee' => 0,
                    'per_night_price' => 0,
                    'livedin_commission' => 0,
                    'host_commission' => 0
                ];
                foreach ($ota_bookings as $ota_booking) {
                    $bookingOtaJson = json_decode($ota_booking->booking_otas_json_details);
                    $raw_message = json_decode($bookingOtaJson->attributes->raw_message);
                    $pricing_rules = 0;
                    $promotion = $ota_booking->promotion;
                    $discount = $discount + $promotion;

                    $total = $ota_booking->amount + $discount;
                    $ota_commission = (float) $ota_booking->ota_commission;

                    $host_commission = !empty($listing->commission_value) ? $listing->commission_value / 100 * $total : 0;

                    $start_date = $ota_booking->arrival_date;
                    $end_date = $ota_booking->departure_date;
                    $total_nights = $start_date == $end_date ? 1 : Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));

                    $livedin_commission = $total - $discount - $host_commission - $ota_commission;

                    $per_night_price = !empty($total) && !empty($total_nights) ? $total / $total_nights : 0;

                    $cleaning_fee = $ota_booking->cleaning_fee;

                    $ota_bookings_data['total_price'] += round((float) $total, 2);
                    $ota_bookings_data['discount'] += round((float) $discount, 2);
                    // $ota_bookings_data['service_fee'] += round((float) $total, 2);
                    $ota_bookings_data['ota_commission'] += round((float) $ota_commission, 2);
                    $ota_bookings_data['cleaning_fee'] += round((float) $cleaning_fee, 2);
                    $ota_bookings_data['per_night_price'] += round((float) $per_night_price, 2);
                    $ota_bookings_data['livedin_commission'] += round((float) $livedin_commission, 2);
                    $ota_bookings_data['host_commission'] += round((float) $host_commission, 2);
                }

                $booking_arr['ota'] = $ota_bookings_data;
                $record['bookings'] = $booking_arr;

                $record['cleaning'] = Cleaning::where('listing_id', $listing->id)->get();
                $record['deep_cleaning'] = DeepCleaning::where('listing_id', $listing->id)->get();
                $record['services_audit'] = Audit::where('listing_id', $listing->id)->get();

                $guest_ids = Bookings::where('listing_id', $listing->listing_id)
                    ->where('booking_status', '!=', 'cancelled')
                    ->pluck('guest_id')
                    ->toArray();

                $record['guests'] = [];
                if (!empty($guest_ids)) {
                    $guests = Guests::whereIn('id', $guest_ids)->get();
                    if (!empty($guests)) {
                        $record['guests'] = $guests;
                    }
                }

                $property_id = RoomType::where('listing_id', $listing->listing_id)->pluck('property_id')->toArray();
                $record['properties'] = Properties::where('id', $property_id)->get(['title', 'created_at']);

                $booking_ids = BookingOtasDetails::where('listing_id', $listing->listing_id)
                    ->where('status', '!=', 'cancelled')
                    ->pluck('booking_id')
                    ->toArray();

                $record['reviews'] = Review::whereIn('booking_id', $booking_ids)->get();

                $data[] = $record;
            }

            return response()->json($data);
        } catch (\Exception $ex) {
            return response()->json($ex->getMessage());
        }
    }
}

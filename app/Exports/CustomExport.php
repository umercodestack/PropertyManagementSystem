<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Listing;
use App\Models\ReportFinanceSoa;
use App\Models\SoaDetail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CustomExport implements FromView
{
    protected $filter;

    public function __construct($filter)
    {
        $this->filter = $filter;
    }


    public function view(): View
    {
        // Return the Blade view with data
        $request = $this->filter;
        $host = User::whereId($request['user_id'])->first();
        // dd($request);
        $soa = ReportFinanceSoa::where('user_id', $request['user_id'])->where('booking_dates', $request['daterange'])->where('listings', $request['listings'])->first();
        if ($soa) {
            $soaDetails = SoaDetail::where('soa_id', $soa->id)->get();
            $soaDetails = $soaDetails->toArray();
        } else {
            $soaDetails = null;
        }
        $listing_arr = Listing::whereIn('id', json_decode($request['listings']))->get();
        $bookingsCod = [];
        $bookings = [];
        // $user_id = Auth::id();
        $dateRange = $request['daterange'];
        [$startDate, $endDate] = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        // dd($startDate, $endDate);
        $period = CarbonPeriod::create($startDate, $endDate)->excludeEndDate();
        $periodDatesArray = $period->toArray();
        // dd($periodDatesArray);
        $periodDateStrings = array_map(fn($date) => $date->toDateString(), $periodDatesArray);
        // dd($periodDatesArray,count($periodDateStrings));
        $dates = [];
        $bookingOtaDatas = [];
        $bookingOtaliveds = [];
        // $dates[] = $date->format('Y-m-d');
        foreach ($listing_arr as $key => $item) {

            foreach ($period as $date) {
                $bookingLivedIn = Bookings::where('listing_id', $item->id)
                    ->whereDate('booking_date_end', '>', $date->format('Y-m-d'))
                    ->whereDate('booking_date_start', '<=', $date->format('Y-m-d'))
                    ->get();
                // dd($bookingLivedIn);
                $bookingOta = BookingOtasDetails::where('listing_id', $item->listing_id)
                    ->whereDate('departure_date', '>', $date->format('Y-m-d'))
                    ->whereDate('arrival_date', '<=', $date->format('Y-m-d'))
                    ->get();
                // $bookingOtaDatas = [];
                foreach ($bookingOta as $booking) {
                    if (in_array($booking->id, $bookingOtaDatas)) {
                        continue;
                    }
                    $start_date = $booking->arrival_date;
                    $end_date = $booking->departure_date;
                    $bookingOtaDatas[] = $booking->id;
                    $promotion = $booking->promotion;
                    $discount = $booking->discount + $promotion;
                    $total = ($booking->amount);
                    $discount = $discount + $promotion;
                    $start_date = $booking->arrival_date;
                    $end_date = $booking->departure_date;
                    $ota_commission = $booking->ota_commission;
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $datesArray = $bookingPeriod->toArray();
                    $dateStrings = array_map(fn($date) => $date->toDateString(), $datesArray);
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $bookingPeriodDatesArray = $bookingPeriod->toArray();
                    $bookingPeriodDateStrings = array_map(fn($date) => $date->toDateString(), $bookingPeriodDatesArray);
                    // $periodDateStrings
                    $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);

                    $nights = count($matchingValues);
                    $livedin_commission = $item->commission_value / 100 * $total;
                    $host_commission = $total - $discount - $ota_commission - $livedin_commission;
                    $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));

                    $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                    array_push($bookings, [
                        'start_date' => $start_date < $startDate ? $startDate : $start_date,
                        'end_date' => $end_date > $endDate ? $endDate : $end_date,
                        'nights' => $nights,
                        'listing_id' => $item->listing_id,
                        'status' => $booking->status == 'cancelled' ? $booking->status : $booking_status,
                        'payment_status' => 'OTA',
                        'type' => 'AirBnb',
                        'booking_id' => 'O' . $booking->id,
                        'name' => $booking->guest_name,
                        'night_rate' => round($total / $totalnights),
                        'total' => round($total / $totalnights * $nights),
                        'discount' => round($discount / $totalnights * $nights),
                        'ota_commission' => round($ota_commission / $totalnights * $nights),
                        'livedin_commission' => round($livedin_commission / $totalnights * $nights),
                        'host_commission' => round($host_commission / $totalnights * $nights),
                    ]);
                }

                foreach ($bookingLivedIn as $booking) {
                    if (in_array($booking->id, $bookingOtaliveds)) {
                        continue;
                    }
                    $bookingOtaliveds[] = $booking->id;
                    $total = $booking->total_price;
                    $discount = $booking->custom_discount;
                    $start_date = $booking->booking_date_start;
                    $end_date = $booking->booking_date_end;
                    $ota_commission = $booking->ota_commission;
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $bookingPeriodDatesArray = $bookingPeriod->toArray();
                    $bookingPeriodDateStrings = array_map(fn($date) => $date->toDateString(), $bookingPeriodDatesArray);
                    // $periodDateStrings
                    $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);

                    $nights = count($matchingValues);
                    if ($nights === 0) {
                        $nights = 1;
                    }
                    $livedin_commission = $item->commission_value / 100 * $booking->per_night_price * $nights;
                    $host_commission = $booking->per_night_price * $nights - $discount - $ota_commission - $livedin_commission;
                    $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                    if ($booking->payment_method == 'cod') {

                        array_push($bookingsCod, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->booking_status == 'cancelled' ? $booking->booking_status : $booking_status,
                            'payment_status' => $booking->payment_method,
                            'type' => $booking->booking_sources,
                            'booking_id' => 'L' . $booking->id,
                            'name' => $booking->name . ' ' . $booking->surname,
                            'night_rate' => round($booking->per_night_price),
                            'total' => round($booking->per_night_price * $nights),
                            'discount' => round($discount),
                            'ota_commission' => round($ota_commission) / count($bookingPeriod) * $nights,
                            'livedin_commission' => round($livedin_commission),
                            'host_commission' => round($booking->per_night_price * $nights) - (round($ota_commission) / count($bookingPeriod) * $nights) - round($livedin_commission),

                        ]);
                    } else {
                        array_push($bookings, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->booking_status == 'cancelled' ? $booking->booking_status : $booking_status,
                            'payment_status' => $booking->payment_method,
                            'type' => $booking->booking_sources,
                            'booking_id' => 'L' . $booking->id,
                            'name' => $booking->name . ' ' . $booking->surname,
                            'night_rate' => round($booking->per_night_price),
                            'total' => round($booking->per_night_price * $nights),
                            'discount' => round($discount),
                            'ota_commission' => round($ota_commission) / count($bookingPeriod) * $nights,
                            'livedin_commission' => round($livedin_commission),
                            'host_commission' => round($booking->per_night_price * $nights) - (round($ota_commission) / count($bookingPeriod) * $nights) - round($livedin_commission),

                        ]);
                    }

                }
            }
        }
        return view('Admin.reports.finance.downloadSoaExcel', ['host' => $host, 'bookings' => $bookings, 'bookingsCod' => $bookingsCod, 'soa' => $soa, 'soaDetails' => $soaDetails]);

    }
}

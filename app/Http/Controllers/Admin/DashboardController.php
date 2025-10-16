<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdrExport;
use App\Exports\AdrExportNet;
use App\Exports\CancelAdrExport;
use App\Exports\CancelAndNewAdrExport;
use App\Exports\CombinedSheet;
use App\Exports\OtaCombinedSheet;
use App\Exports\OccupancyExport;
use App\Http\Controllers\Controller;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Calender;
use App\Models\Listings;
use App\Models\Listing;
use App\Models\Channels;
use App\Models\User;
use App\Models\Cleaning;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use DateInterval;
use DatePeriod;
use DateTime;
class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission');
    }

    public function getPerformance($listing_arr, $request)
    {
        $listingIds = collect($listing_arr)->pluck('listing_id')->all();
        $bookingsOta = BookingOtasDetails::whereIn('listing_id', $listingIds)->get()->groupBy('listing_id');
        $bookingsLivedIn = Bookings::whereIn('listing_id', $listingIds)->get()->groupBy('listing_id');

        $bookings = [];

        foreach ($listing_arr as $item) {
            $listingId = $item->listing_id;
            $listing_json = json_decode($item->listing_json);

            if (isset($bookingsOta[$listingId])) {
                foreach ($bookingsOta[$listingId] as $booking) {
                    if ($booking->status === 'cancelled')
                        continue;

                    // $bookingOtaJson = json_decode($booking->booking_otas_json_details);
                    // $raw_message = json_decode($bookingOtaJson->attributes->raw_message);

                    $pricing_rules = $booking->discount;

                    $promotion_rules = $booking->promotion;

                    $cleaning_fees = $booking->cleaning_fee;

                    $total = $booking->amount + $promotion_rules + $pricing_rules + $cleaning_fees;
                    $ota_commission = $booking->ota_commission;
                    $nights = Carbon::parse($booking->arrival_date)->diffInDays(Carbon::parse($booking->departure_date));
                    $host_commission = ($item->commission_value / 100) * $total;

                    $bookings[] = [
                        'booking_id' => $booking->id,
                        'listing_title' => $listing_json->title,
                        'listing_id' => $listingId,
                        'total' => $total,
                        'discount' => $pricing_rules,
                        'start_date' => $booking->arrival_date,
                        'end_date' => $booking->departure_date,
                        'ota_commission' => $ota_commission,
                        'nights' => $nights,
                        'host_commission' => $host_commission,
                        'ht_comm' => $total - $pricing_rules - $ota_commission - $host_commission,
                        'status' => $booking->status
                    ];
                }
            }

            if (isset($bookingsLivedIn[$listingId])) {
                foreach ($bookingsLivedIn[$listingId] as $booking) {
                    if ($booking->booking_status === 'cancelled')
                        continue;

                    $total = $booking->total_price + $booking->cleaning_fee;
                    $discount = $booking->custom_discount;
                    $nights = Carbon::parse($booking->booking_date_start)->diffInDays(Carbon::parse($booking->booking_date_end));
                    $host_commission = ($item->commission_value / 100) * $total;

                    $bookings[] = [
                        'booking_id' => $booking->id,
                        'listing_title' => $listing_json->title,
                        'listing_id' => $listingId,
                        'total' => $total,
                        'discount' => $discount,
                        'start_date' => $booking->booking_date_start,
                        'end_date' => $booking->booking_date_end,
                        'ota_commission' => $booking->ota_commission,
                        'nights' => $nights,
                        'host_commission' => $host_commission,
                        'ht_comm' => $total - $discount - $booking->ota_commission - $host_commission,
                        'status' => $booking->booking_status
                    ];
                }
            }
        }

        return $this->calculatePerformanceMetrics($bookings, $listing_arr, $request);
    }
    private function calculatePerformanceMetrics($bookings, $listing_arr, $request)
    {
        if (empty($bookings))
            return [];

        $total_nights = $total_host_commission = $total_discount = $total_ota_commission = $total_amount = 0;
        $stayCount = ['>1' => 0, '>2' => 0, '>3' => 0, '>7' => 0, '>30' => 0];

        $lowestStartDate = collect($bookings)->min('start_date');
        $highestEndDate = collect($bookings)->max('end_date');

        $startDateRequested = isset($request['day']) && $request['day'] === 'today' ? Carbon::today()->toDateString()
            : (isset($request['day']) && $request['day'] === 'yesterday' ? Carbon::yesterday()->toDateString() : $lowestStartDate);

        $endDateRequested = isset($request['day']) && $request['day'] === 'today' ? Carbon::today()->toDateString()
            : (isset($request['day']) && $request['day'] === 'yesterday' ? Carbon::yesterday()->toDateString() : $highestEndDate);

        $periodRequest = CarbonPeriod::create($startDateRequested, $endDateRequested);
        $dateArrayRequest = collect($periodRequest)->map(fn($date) => $date->toDateString())->all();

        foreach ($bookings as $booking) {
            if ($booking['status'] === 'cancelled')
                continue;

            $startDateDB = Carbon::parse($booking['start_date'])->toDateString();
            $endDateDB = Carbon::parse($booking['end_date'])->toDateString();
            $periodDB = CarbonPeriod::create($startDateDB, $endDateDB);
            $dateArrayDB = collect($periodDB)->map(fn($date) => $date->toDateString())->all();

            $countCommonValues = count(array_intersect($dateArrayRequest, $dateArrayDB));
            if ($countCommonValues === 0)
                continue;

            $nightFactor = $booking['nights'] !== 0 ? $countCommonValues / $booking['nights'] : 0;
            $total_nights += $countCommonValues;
            $total_amount += $booking['total'] * $nightFactor;
            $total_host_commission += $booking['host_commission'] * $nightFactor;
            $total_discount += $booking['discount'] * $nightFactor;
            $total_ota_commission += $booking['ota_commission'] * $nightFactor;

            if ($countCommonValues === 1)
                $stayCount['>1']++;
            elseif ($countCommonValues === 2)
                $stayCount['>2']++;
            elseif ($countCommonValues >= 3 && $countCommonValues <= 6)
                $stayCount['>3']++;
            elseif ($countCommonValues >= 7 && $countCommonValues <= 29)
                $stayCount['>7']++;
            elseif ($countCommonValues > 30)
                $stayCount['>30']++;
        }

        $occupancy = ($total_nights > count($listing_arr))
            ? (count($listing_arr) / $total_nights) * 1000
            : ($total_nights / count($listing_arr)) * 100;

        return [
            'occupancy' => round($occupancy),
            'revenue' => round($total_amount, 2),
            'adr' => $total_nights ? round($total_amount / $total_nights, 2) : 0,
            'my_earning' => round($total_amount - $total_discount - $total_host_commission - $total_ota_commission, 2),
            'discount' => round($total_discount, 2),
            'livedIn' => round($total_host_commission, 2),
            'Airbnb' => round($total_ota_commission, 2),
            'stayGreaterThanOne' => round($stayCount['>1'] / count($bookings) * 100, 2),
            'stayGreaterThanTwo' => round($stayCount['>2'] / count($bookings) * 100, 2),
            'stayGreaterThanThree' => round($stayCount['>3'] / count($bookings) * 100, 2),
            'stayGreaterThanSeven' => round($stayCount['>7'] / count($bookings) * 100, 2),
            'stayGreaterThanThirty' => round($stayCount['>30'] / count($bookings) * 100, 2),
        ];
    }

    public function index(Request $request)
    {
        // dd($request->all());
        if (isset($request->day) && $request->day === 'yesterday') {
            $date = Carbon::yesterday()->toDateString();
            $occup = Calender::where('calender_date', $date)->where('availability', 0)->where('is_lock', 0)->get();

            $OtaBookings = BookingOtasDetails::where('arrival_date', '=', $date)->get();

            $bookings = Bookings::where('booking_date_start', '=', $date)->get();
            $OtaBookingsListings = BookingOtasDetails::where('arrival_date', '=', $date)->pluck('listing_id')->unique();
            $BookingsListings = DB::table('bookings')
                ->where('bookings.booking_date_start', '=', $date)
                ->leftJoin('listings', 'listings.id', '=', 'bookings.listing_id')
                ->pluck('listings.listing_id')
                ->unique();
        } else if (isset($request->day) && $request->day === 'today') {
            $date = Carbon::today()->toDateString();
            $occup = Calender::where('calender_date', $date)->where('availability', 0)->where('is_lock', 0)->get();

            $OtaBookings = BookingOtasDetails::where('arrival_date', '=', $date)->get();
            // dd($OtaBookings);
            $bookings = Bookings::where('booking_date_start', '=', $date)->get();
            $OtaBookingsListings = BookingOtasDetails::where('arrival_date', '=', $date)->pluck('listing_id')->unique();
            $BookingsListings = DB::table('bookings')
                ->where('bookings.booking_date_start', '=', $date)
                ->leftJoin('listings', 'listings.id', '=', 'bookings.listing_id')
                ->pluck('listings.listing_id')
                ->unique();

        } else {
            $date = Carbon::today()->toDateString();
            $occup = Calender::where('calender_date', $date)->where('availability', 0)->where('is_lock', 0)->get();

            $OtaBookings = BookingOtasDetails::all();
            $bookings = Bookings::all();

            $OtaBookingsListings = BookingOtasDetails::where('arrival_date', '>=', $date)->pluck('listing_id')->unique();
            $BookingsListings = DB::table('bookings')
                ->where('bookings.booking_date_start', '>=', $date)
                ->leftJoin('listings', 'listings.id', '=', 'bookings.listing_id')
                ->pluck('listings.listing_id')
                ->unique();
        }


        // Merge the two collections and get unique values
        // dd($OtaBookingsListings, $BookingsListings);

        // Convert to an array if needed
        $occupiedRooms = $occup;
        $listings = Listings::all();
        $occupancies = array();
        $performance = $this->getPerformance($listings, $request->all());
        foreach ($listings as $index => $items) {
            $listing_json = json_decode($items->listing_json);
            $occupancy['title'] = $listing_json->title;

            if ($index === 6) {
                break;
            }
            // dd();
            // dd($items);
            $arr = array($items);
            $perf = $this->getPerformance($arr, []);
            $occupancy['occupancy'] = isset($perf['occupancy']) ? $perf['occupancy'] : 0;
            array_push($occupancies, $occupancy);
        }
        usort($occupancies, function ($a, $b) {
            return $b['occupancy'] <=> $a['occupancy'];
        });


        $chechinsOta = BookingOtasDetails::orderBy('arrival_date', 'asc')->where('arrival_date', '=', $date)->paginate(2);
        $chechinsLived = Bookings::orderBy('booking_date_start', 'asc')->where('booking_date_start', '=', $date)->paginate(2);
        // dd($chechinsLived);

        $chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'asc')->where('departure_date', '=', $date)->paginate(2);
        $chechoutsLived = Bookings::orderBy('booking_date_end', 'asc')->where('booking_date_end', '=', $date)->paginate(2);
        // dd($chechoutsLived);
        // dd($performance['revenue']);

        $calender = Calender::orderBy('calender_date', 'desc')->where('calender_date', '=', $date)
            ->whereNotNull('listing_id')
            ->where('listing_id', '!=', '')
            ->where('availability', 1)
            ->get();

        $todayscleanings = $this->todayscleanings();

        return view('Admin.dashboard.dashboard', ['OtaBookings' => $OtaBookings, 'bookings' => $bookings, 'listings' => $listings, 'occupiedRooms' => $occupiedRooms, 'performance' => $performance, 'chechinsOta' => $chechinsOta, 'chechinsLived' => $chechinsLived, 'chechoutsOta' => $chechoutsOta, 'chechoutsLived' => $chechoutsLived, 'occupancies' => $occupancies, 'calender' => $calender, 'todayscleanings' => $todayscleanings]);
    }

    public function todayscleanings()
    {
        $date = Carbon::today()->toDateString();
        // Carbon::today()->toDateString();
        $checkoutData = array();


        $chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'desc')->where('departure_date', '=', $date)->get();


        $chechoutsLived = Bookings::orderBy('booking_date_end', 'desc')->where('booking_date_end', '=', $date)->get();
        // print_r($chechoutsLived);die;


        foreach ($chechoutsOta as $items) {
            if ($items->status == 'cancelled') {
                continue;
            }
            // dd($items);
            if (!isset($items->booking_otas_json_details)) {
                continue;
            }
            $booking_json = json_decode($items->booking_otas_json_details);
            if (!isset($booking_json->attributes)) {
                continue;
            }
            $booking_json = $booking_json->attributes;
            $guest = $booking_json->customer;
            $checkout['booking_id'] = $items->id;
            $checkout['checkin'] = $items->arrival_date;
            $checkout['type'] = 'ota';
            $checkout['checkout'] = $items->departure_date;


            $listing = Listing::where('listing_id', $items->listing_id)->first();

            // print_r($listing);die;

            if (!($listing)) {
                continue;
            }

            $channel = Channels::where('id', $listing->channel_id)->first();
            if (!isset($channel)) {
                continue;
            }
            // print_r($channel);die;

            $host = User::whereId($channel->user_id)->select('name', 'surname', 'email', 'phone')->first();
            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $checkout['guest'] = $guest;
            $checkout['host'] = $host->toArray();
            $has_booking = BookingOtasDetails::where('listing_id', $listing->listing_id)->where('status', '!=', 'cancelled')->where('arrival_date', $checkout['checkout'])->get();
            $has_bookingLived = Bookings::where('listing_id', $listing->id)->where('booking_status', '!=', 'cancelled')->where('booking_date_start', $checkout['checkout'])->get();
            $checkout['has_checkin'] = isset($has_booking) && count($has_booking) > 0 || isset($has_bookingLived) && count($has_bookingLived) > 0 ? 1 : 0;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('cleaning_date', $checkout['checkout'])->first();
            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            $cleaning === null ? $checkout['key_code'] = '' : $checkout['key_code'] = $cleaning->key_code;
            array_push($checkoutData, $checkout);
        }
        foreach ($chechoutsLived as $items) {

            if ($items->booking_status == 'cancelled') {
                continue;
            }
            $checkoutLiv['booking_id'] = $items->id;

            $checkoutLiv['checkin'] = $items->booking_date_start;
            $checkoutLiv['type'] = 'livedin';
            $checkoutLiv['checkout'] = $items->booking_date_end;
            $listing = Listing::where('id', $items->listing_id)->first();
            $listing_json = json_decode($listing->listing_json);
            $checkoutLiv['listing_id'] = $listing->id;
            $checkoutLiv['listing_title'] = $listing_json->title;
            $has_booking = BookingOtasDetails::where('listing_id', $listing->listing_id)->where('status', '!=', 'cancelled')->where('arrival_date', $checkoutLiv['checkout'])->get();
            $channel = Channels::where('id', $listing->channel_id)->first();
            if (!isset($channel)) {
                continue;
            }
            $host = User::whereId($channel->user_id)->select('name', 'surname', 'email', 'phone')->first();
            $checkoutLiv['guest'] = array(
                'name' => $items->name,
                'surname' => $items->surname,
                'email' => $items->email,
                'phone' => $items->phone,
            );
            $checkoutLiv['guest'] = (Object) $checkoutLiv['guest'];
            $checkoutLiv['host'] = $host->toArray();
            $has_bookingLived = Bookings::where('listing_id', $listing->id)->where('booking_status', '!=', 'cancelled')->where('booking_date_start', $checkoutLiv['checkout'])->get();
            $checkoutLiv['has_checkin'] = isset($has_booking) && count($has_booking) > 0 || isset($has_bookingLived) && count($has_bookingLived) > 0 ? 1 : 0;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('cleaning_date', $checkoutLiv['checkout'])->first();
            $cleaning === null ? $checkoutLiv['status'] = 'pending' : $checkoutLiv['status'] = $cleaning->status;
            $cleaning === null ? $checkoutLiv['key_code'] = '' : $checkoutLiv['key_code'] = $cleaning->key_code;
            array_push($checkoutData, $checkoutLiv);
        }

        return $checkoutData;
    }

    public function todaycleaning()
    {
        $todayscleanings = $this->todayscleanings();
        return view('Admin.dashboard.today_cleaning', ['todayscleanings' => $todayscleanings]);
    }

    public function listingVacant()
    {
        $date = Carbon::today()->toDateString();
        $calender = Calender::orderBy('calender_date', 'asc')->where('calender_date', '=', $date)
            ->whereNotNull('listing_id')
            ->where('listing_id', '!=', '')
            ->where('availability', 1)->get();

        return view('Admin.dashboard.listing_vacant', ['calender' => $calender]);
    }


    public function listingOccupancie()
    {
        $occupancies = array();
        $listings = Listings::all();
        foreach ($listings as $index => $items) {
            $listing_json = json_decode($items->listing_json);
            $occupancy['title'] = $listing_json->title;

            // dd($items);
            $arr = array($items);
            $perf = $this->getPerformance($arr, []);
            // dd($perf['occupancy']);
            $perf['occupancy'] > 100 ? $perf['occupancy'] = 100 : $perf['occupancy'] = $perf['occupancy'];
            $occupancy['occupancy'] = ($perf['occupancy']);
            array_push($occupancies, $occupancy);
        }
        usort($occupancies, function ($a, $b) {
            return $b['occupancy'] <=> $a['occupancy'];
        });
        // dd($occupancies);
        return view('Admin.dashboard.listing_occupancies', ['occupancies' => $occupancies]);
    }

    public function listingCheckins()
    {
        $date = Carbon::today()->toDateString();
        $bookings = array();
        $chechinsOta = BookingOtasDetails::orderBy('arrival_date', 'asc')->where('arrival_date', '>=', $date)->get();
        $chechinsLived = Bookings::orderBy('booking_date_start', 'asc')->where('booking_date_start', '>=', $date)->get();
        $mergedArray = $chechinsOta->merge($chechinsLived);
        $mergedArray = $mergedArray->toArray();
        return view('Admin.dashboard.listing_checkins', ['chechinsOta' => $chechinsOta, 'chechinsLived' => $chechinsLived, 'checkins' => $mergedArray]);
    }
    public function listingCheckouts()
    {
        $date = Carbon::yesterday()->toDateString();
        $chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'asc')->where('departure_date', '>', $date)->get();
        $chechoutsLived = Bookings::orderBy('booking_date_end', 'asc')->where('booking_date_end', '>', $date)->get();
        $mergedArray = $chechoutsOta->merge($chechoutsLived);
        $mergedArray = $mergedArray->toArray();
        return view('Admin.dashboard.listing_checkouts', ['chechoutsOta' => $chechoutsOta, 'chechoutsLived' => $chechoutsLived, 'chechouts' => $mergedArray]);
    }

    public function AdrDataExport($request)
    {
        return Excel::download(new AdrExport($request), 'occupancy2.xlsx');
    }

    // public function occupancyReport(Request $request)
    // {
    //     $request = $request->all();

    //     list($yearReq, $monthReq) = explode('-', $request['month']);

    //     $month = $monthReq;
    //     $year = $yearReq;
    //     $start = new DateTime("$year-$month-01");
    //     $end = clone $start;
    //     $end->modify('last day of this month');
    //     $interval = new DateInterval('P1D');
    //     $period = new DatePeriod($start, $interval, $end->modify('+1 day'));
    //     $listings = DB::table('listings')->get();
    //     $occupancies = array();
    //     $occupancy_header = [
    //         $occupancy['apartment_title'] = 'Apartment Title',
    //         $occupancy['live_date'] = 'Live Date',
    //     ];

    //     foreach ($period as $index => $item) {
    //         array_push($occupancy_header, $item->format('Y-m-d'));
    //     }
    //     array_push($occupancies, $occupancy_header);
    //     foreach ($listings as $key => $items) {
    //         $occupancy['apartment_title'] = 'Apartment Title';
    //         $occupancy['live_date'] = 'Live Date';
    //         $calendar = DB::table('calenders')
    //             ->where('listing_id', $items->listing_id)
    //             ->whereMonth('calender_date', $month)
    //             ->whereYear('calender_date', $year)
    //             ->orderBy('calender_date', 'asc')
    //             ->pluck('calender_date');
    //         $listing_json = json_decode($items->listing_json);
    //         $occupancy['apartment_title'] = $listing_json->title;
    //         $occupancy['live_date'] = Carbon::parse($items->created_at)->toDateString();
    //         $count_dates = 0;
    //         $calendar = $calendar->toArray();
    //         foreach ($period as $index => $item) {
    //             if (in_array($item->format('Y-m-d'), $calendar)) {
    //                 $occup = Calender::where('listing_id', $items->listing_id)->where('calender_date', $item->format('Y-m-d'))->first();
    //                 if ($occup->availability == 1) {
    //                     $occupancy[$item->format('Y-m-d')] = '0';
    //                 } else if ($occup->is_lock == 1) {
    //                     $bookings = Bookings::where('listing_id', $items->id)->where('booking_date_start', $item->format('Y-m-d'))->get();
    //                     $bookingsOta = BookingOtasDetails::where('listing_id', $items->listing_id)->where('arrival_date', $item->format('Y-m-d'))->get();
    //                     if (count($bookings) > 0 || count($bookingsOta) > 0) {
    //                         $occupancy[$item->format('Y-m-d')] = 1;
    //                     } else {
    //                         $occupancy[$item->format('Y-m-d')] = '-';
    //                     }
    //                 } else {
    //                     $occupancy[$item->format('Y-m-d')] = 1;
    //                 }
    //             } else {
    //                 $occupancy[$item->format('Y-m-d')] = '-';
    //             }
    //         }
    //         array_push($occupancies, $occupancy);
    //     }
    //     dd($occupancies);
    //     return view('Admin.dashboard.listing_report');
    //     // dd($request);
    //     // return Excel::download(new CombinedSheet($request), 'combined_data.xlsx');
    // }

    public function occupancyData(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'report_type' => 'required|in:gross_revenue,net_revenue,occupancy,cancelled_bookings,cancelled_bookings_new_bookings',
        ]);

        $data = $request->all();
        $date = Carbon::now()->format('Y-m-d');

        // Map report_type to specific export class
        $exportMap = [
            'gross_revenue' => AdrExport::class,
            'net_revenue' => AdrExportNet::class,
            'occupancy' => OccupancyExport::class,
            // Add other report types and their corresponding export classes
            'cancelled_bookings' => CancelAdrExport::class, // Create this class if needed
            'cancelled_bookings_new_bookings' => CancelAndNewAdrExport::class, // Create this class if needed
        ];

        $exportClass = $exportMap[$data['report_type']] ?? null;

        if (!$exportClass) {
            return back()->withErrors(['report_type' => 'Invalid report type selected.']);
        }

        return Excel::download(new $exportClass($data), "revenue_report_{$data['report_type']}_$date.xlsx");
    }

    public function otaOccupancyData(Request $request)
    {
        $request = $request->all();
        $date = Carbon::now()->format('Y-m-d');
        return Excel::download(new OtaCombinedSheet($request), "otaCombined_report_$date.xlsx");
    }

}

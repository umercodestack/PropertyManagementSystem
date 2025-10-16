<?php

namespace App\Http\Controllers\Admin\Reports;

use App\Exports\StatementOfAccountExport;
use App\Http\Controllers\Controller;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Listing;
use App\Models\ReportFinanceSoa;
use App\Models\ReportFinanceSoaPop;
use App\Models\SoaDetail;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\FirebaseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Elibyy\TCPDF\Facades\TCPDF;
use App\Exports\CustomExport;
use App\Models\ListingRelation;
use App\Models\Channels;

class FinanceMangementController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;

        $this->middleware('permission');
    }


    public function index(Request $request)
    {
        $data = $request->all();
        // dd($data);
        $hosts = User::where('role_id', 2)->get();
        if (isset($data['user_id']) && $data['user_id']) {
            // dd($data['daterange']);
            $soa = ReportFinanceSoa::with('user')
                ->where('user_id', $data['user_id'])
                ->where('booking_dates', $data['daterange'])
                ->orderBy('id', 'desc')->get();
        } else {
            $soa = ReportFinanceSoa::with('user')->orderBy('id', 'desc')->get();
        }
        // $data['user_id'] == null ? $soa = ReportFinanceSoa::with('user')->get() : $soa = ReportFinanceSoa::with('user')->where('user_id', $data['user_id'])->get();
        // dd($soa);
        return view('Admin.reports.finance.soa', ['soa' => $soa, 'hosts' => $hosts]);
    }
    public function create()
    {
        $hosts = User::where('role_id', 2)->get();
        return view('Admin.reports.finance.soa_create', ['hosts' => $hosts]);
    }
    public function hostListings($host_id)
    {
        $listing_arr = [];
        $userDB = User::find($host_id);
        $listings = Listing::all();
        foreach ($listings as $listing) {
            $channel = Channels::where('id', $listing->channel_id)->first();
            if (isset($channel->connection_type) && $channel->connection_type != null) {
                continue;
            }
            $users = json_decode($listing->user_id, true);
            if (is_array($users) && in_array($host_id, $users)) {
                $listing_arr[] = $listing;
            }
        }
        return response($listing_arr);
    }


    public function fetchSoasByHostId(Request $request)
    {
        $data = $request->all();
        $data['user_id'] == null ? $soa = ReportFinanceSoa::with('user')->get() : $soa = ReportFinanceSoa::with('user')->where('user_id', $data['user_id'])->get();
        return response($soa);
    }

    public function printSoaExcel(Request $request)
    {
        return Excel::download(new CustomExport($request->all()), 'custom_sheet.xlsx');
        // return Excel::download(new StatementOfAccountExport($bookings, $bookingsCod), 'Statement_of_Account.xlsx');
        //     dd($bookings);

    }

    public function printSoa(Request $request)
    {
        if (gettype($request['listings']) == 'string') {
            $request['listings'] = json_decode($request['listings']);
        }
        $request = $request->all();
        $host = User::whereId($request['user_id'])->first();
        $soa = ReportFinanceSoa::where('user_id', $request['user_id'])->where('booking_dates', $request['daterange'])->where('listings', json_encode($request['listings']))->first();
        if ($soa) {
            $soaDetails = SoaDetail::where('soa_id', $soa->id)->get();
            $soaDetails = $soaDetails->toArray();
        } else {
            $soaDetails = null;
        }
        $listing_arr = Listing::whereIn('id', $request['listings'])->get();
        $bookingsCod = [];
        $bookings = [];
        $dateRange = $request['daterange'];
        [$startDate, $endDate] = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        $period = CarbonPeriod::create($startDate, $endDate)->excludeEndDate();
        $periodDatesArray = $period->toArray();
        $periodDateStrings = array_map(fn($date) => $date->toDateString(), $periodDatesArray);
        $dates = [];
        $bookingOtaDatas = [];
        $bookingOtaliveds = [];
        foreach ($listing_arr as $key => $item) {
            foreach ($period as $date) {
                $listingIdArr = [$item->listing_id];
                $bookingLivedIn = Bookings::where('listing_id', $item->id)
                    ->whereDate('booking_date_end', '>', $date->format('Y-m-d'))
                    ->whereDate('booking_date_start', '<=', $date->format('Y-m-d'))
                    ->get();
                $listingRelation = ListingRelation::where('listing_id_airbnb', $item->id)->get();
                if ($listingRelation) {
                    foreach ($listingRelation as $it) {
                        $listing_Bcom = Listing::where('id', $it->listing_id_other_ota)->first();
                        if ($it->listing_type == 'Almosafer') {
                            $listingIdArr[] = $listing_Bcom->id;
                        } else {
                            $listingIdArr[] = $listing_Bcom->listing_id;
                        }
                    }
                }
                $bookingOta = BookingOtasDetails::whereIn('listing_id', $listingIdArr)
                    ->whereDate('departure_date', '>', $date->format('Y-m-d'))
                    ->whereDate('arrival_date', '<=', $date->format('Y-m-d'))
                    ->get();
                foreach ($bookingOta as $booking) {
                    if (in_array($booking->id, $bookingOtaDatas)) {
                        continue;
                    }
                    $start_date = $booking->arrival_date;
                    $end_date = $booking->departure_date;
                    $bookingOtaDatas[] = $booking->id;
                    $amount = $booking->amount;
                    $discount = $booking->discount;
                    $promotion = $booking->promotion;
                    $discount = $discount + $promotion;
                    $total_cleaning = $booking->cleaning_fee;
                    $apartment = Listing::where('listing_id', $item->listing_id)->first();
                    if ($apartment->is_cleaning_fee == 0 || $apartment->is_cleaning_fee == null) {
                        $cleaning_fee = $booking->cleaning_fee;
                        $amount = $amount + $cleaning_fee;
                    }
                    $start_date = $booking->arrival_date;
                    $end_date = $booking->departure_date;
                    $ota_commission = $booking->ota_commission;
                    $forex_adjustment = $booking->forex_adjustment ?? 0; // Assuming forex_adjustment exists in BookingOtasDetails
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $bookingPeriodDatesArray = $bookingPeriod->toArray();
                    $bookingPeriodDateStrings = array_map(fn($date) => $date->toDateString(), $bookingPeriodDatesArray);
                    $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);
                    $nights = count($matchingValues);
                    if ($apartment->pre_discount == 1) {
                        $livedin_commission = ($item->commission_value / 100 * ($amount - $discount)) * 1.15;
                    } else {
                        $livedin_commission = ($item->commission_value / 100 * $amount) * 1.15;
                    }
                    $host_commission = $amount - $discount - $ota_commission - $livedin_commission;
                    $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                    $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                    if ($start_date < $startDate) {
                        $total_cleaning = 'LB';
                    } else if ($apartment->is_cleaning_fee == 0) {
                        $total_cleaning = 'NCBL';
                    } else {
                        $total_cleaning = $booking->cleaning_fee;
                    }
                    if ($apartment->is_co_host == 1) {
                        array_push($bookingsCod, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->status == 'cancelled' ? $booking->status : $booking_status,
                            'payment_status' => 'OTA',
                            'type' => 'AirBnb',
                            'booking_id' => 'O' . $booking->id,
                            'name' => $booking->guest_name ?? '',
                            'night_rate' => round($amount / $totalnights) > 0 ? round($amount / $totalnights, 2) : 0,
                            'total' => round($amount / $totalnights * $nights) > 0 ? round($amount / $totalnights * $nights, 2) : 0,
                            'discount' => round($discount / $totalnights * $nights),
                            'post_discount_booking_amount' => round($amount / $totalnights * $nights, 2) - round($discount / $totalnights * $nights, 2),
                            'ota_commission' => round($ota_commission / $totalnights * $nights),
                            'forex_adjustment' => round($forex_adjustment / $totalnights * $nights), // New field
                            'livedin_commission' => round($livedin_commission / $totalnights * $nights, 1) > 0 ? round($livedin_commission / $totalnights * $nights, 2) : 0,
                            'host_commission' => round($host_commission / $totalnights * $nights) > 0 ? round($host_commission / $totalnights * $nights, 2) : 0,
                            'post_forex_host_share' => round($host_commission / $totalnights * $nights, 2) - round($forex_adjustment / $totalnights * $nights, 2), // New calculated field
                            'total_cleaning' => $total_cleaning,
                        ]);
                    } else {
                        array_push($bookings, [
                            'start_date' => $start_date < $startDate ? $startDate : $start_date,
                            'end_date' => $end_date > $endDate ? $endDate : $end_date,
                            'nights' => $nights,
                            'listing_id' => $item->listing_id,
                            'status' => $booking->status == 'cancelled' ? $booking->status : $booking_status,
                            'payment_status' => 'OTA',
                            'type' => 'AirBnb',
                            'booking_id' => 'O' . $booking->id,
                            'name' => $booking->guest_name ?? '',
                            'night_rate' => round($amount / $totalnights) > 0 ? round($amount / $totalnights, 2) : 0,
                            'total' => round($amount / $totalnights * $nights) > 0 ? round($amount / $totalnights * $nights, 2) : 0,
                            'discount' => round($discount / $totalnights * $nights),
                            'post_discount_booking_amount' => round($amount / $totalnights * $nights, 2) - round($discount / $totalnights * $nights, 2),
                            'ota_commission' => round($ota_commission / $totalnights * $nights),
                            'forex_adjustment' => round($forex_adjustment / $totalnights * $nights), // New field
                            'livedin_commission' => round($livedin_commission / $totalnights * $nights, 1) > 0 ? round($livedin_commission / $totalnights * $nights, 2) : 0,
                            'host_commission' => round($host_commission / $totalnights * $nights) > 0 ? round($host_commission / $totalnights * $nights, 2) : 0,
                            'post_forex_host_share' => round($host_commission / $totalnights * $nights, 2) - round($forex_adjustment / $totalnights * $nights, 2), // New calculated field
                            'total_cleaning' => $total_cleaning,
                        ]);
                    }
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
                    $forex_adjustment = $booking->forex_adjustment ?? 0; // Assuming forex_adjustment exists in Bookings
                    $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                    $bookingPeriodDatesArray = $bookingPeriod->toArray();
                    $bookingPeriodDateStrings = array_map(fn($date) => $date->toDateString(), $bookingPeriodDatesArray);
                    $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);
                    $nights = count($matchingValues);
                    if ($nights === 0) {
                        $nights = 1;
                    }
                    $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                    $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                    $apartment = Listing::where('id', $item->id)->first();
                    $discount = round($discount / $totalnights * $nights);
                    if ($apartment->pre_discount == 1) {
                        $livedin_commission = ($nights * ($booking->per_night_price - $discount / $nights) * $item->commission_value / 100) * 1.15;
                    } else {
                        $livedin_commission = ($item->commission_value / 100 * $booking->per_night_price * $nights) * 1.15;
                    }
                    $post_discount_booking_amount = ($booking->per_night_price * $nights) - $discount;
                    $host_commission = $booking->per_night_price * $nights - $discount - ($ota_commission / $totalnights * $nights) - $livedin_commission;
                    $total_cleaning = $booking->cleaning_fee;

                    if ($start_date < $startDate) {
                        $total_cleaning = 'LB';
                    } else if ($apartment->is_cleaning_fee == 0) {
                        $total_cleaning = 'NCBL';
                    } else if ($booking->include_cleaning == 0) {
                        $total_cleaning = 'EX';
                    } else {
                        $total_cleaning = $booking->booking_status == 'cancelled' ? 0 : $booking->cleaning_fee;
                    }
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
                            'night_rate' => $booking->booking_status == 'cancelled' ? 0 : round($booking->per_night_price, 2),
                            'total' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->cleaning_fee_direct_booking == 1
                                    ? round($booking->per_night_price * $nights, 2)
                                    : round(($booking->per_night_price + $booking->cleaning_fee) * $nights, 2)),
                            'discount' => $discount,
                            'post_discount_booking_amount' => round($post_discount_booking_amount, 2),
                            'ota_commission' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->ota_fee_direct_booking == 1
                                    ? round($ota_commission / $totalnights * $nights, 2)
                                    : 0),
                            'forex_adjustment' => round($forex_adjustment / $totalnights * $nights), // New field
                            'livedin_commission' => $booking->booking_status == 'cancelled' ? 0 : round($livedin_commission, 2),
                            'host_commission' => $booking->booking_status == 'cancelled' ? 0 : round($host_commission, 2),
                            'post_forex_host_share' => $booking->booking_status == 'cancelled' ? 0 : round($host_commission, 2) - round($forex_adjustment / $totalnights * $nights, 2), // New calculated field
                            'total_cleaning' => $total_cleaning,
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
                            'night_rate' => $booking->booking_status == 'cancelled' ? 0 : round($booking->per_night_price, 2),
                            'total' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->cleaning_fee_direct_booking == 1
                                    ? round($booking->per_night_price * $nights, 2)
                                    : round(($booking->per_night_price + $booking->cleaning_fee) * $nights, 2)),
                            'discount' => $discount,
                            'post_discount_booking_amount' => round($post_discount_booking_amount, 2),
                            'ota_commission' => ($booking->booking_status == 'cancelled')
                                ? 0
                                : ($apartment->ota_fee_direct_booking == 1
                                    ? round($ota_commission / $totalnights * $nights, 2)
                                    : 0),
                            'forex_adjustment' => round($forex_adjustment / $totalnights * $nights, 2), // New field
                            'livedin_commission' => $booking->booking_status == 'cancelled' ? 0 : round($livedin_commission, 2),
                            'host_commission' => $booking->booking_status == 'cancelled' ? 0 : round($host_commission, 2),
                            'post_forex_host_share' => $booking->booking_status == 'cancelled' ? 0 : round($host_commission, 2) - round($forex_adjustment / $totalnights * $nights, 2), // New calculated field
                            'total_cleaning' => $total_cleaning,
                        ]);
                    }
                }
            }
        }
        return view('Admin.reports.finance.printsoa', ['host' => $host, 'cleaning_per_cycle' => $host->cleaning_per_cycle, 'bookings' => $bookings, 'bookingsCod' => $bookingsCod, 'soa' => $soa, 'soaDetails' => $soaDetails]);
    }


    public function uploadPop(Request $request)
    {
        $data = $request->all();
        // dd($data['soa_id']);
        if (isset($data['soa_id'])) {
            $data['soa_id'] = (int) $data['soa_id'];
            $soa = ReportFinanceSoa::where('id', $data['soa_id'])->first();

        } else {
            $soa = ReportFinanceSoa::where('user_id', $data['user_id'])->where('booking_dates', $data['daterange'])->where('listings', $data['listings'])->first();
        }
        // dd($soa);
        // $soa = ReportFinanceSoa::where('user_id', $data['user_id'])->where('booking_dates', $data['daterange'])->where('listings', $data['listings'])->first();

        $soaPop = ReportFinanceSoaPop::where('soa_id', $soa->id)->first();
        if ($soaPop) {
            if ($request->hasFile('pop')) {
                $image = public_path('storage/' . $soaPop->file_path);

                if (file_exists($image)) {
                    unlink($image);
                }
                $images = $request->all();
                $images = $images['pop'];
                $fileName = time() . '_' . $images->getClientOriginalName();
                $filePath = $images->storeAs('financeSoaPop', $fileName, 'public');
                $soaPop->update(
                    [
                        'file_path' => $filePath,
                    ]
                );
                // }
            }
            return redirect()->back();
        }
        if ($request->hasFile('pop')) {
            $images = $request->all();
            $images = $images['pop'];
            // dd($images['pop']);
            // foreach ($request->file('image') as $images) {
            $fileName = time() . '_' . $images->getClientOriginalName();
            $filePath = $images->storeAs('financeSoaPop', $fileName, 'public');
            ReportFinanceSoaPop::create(
                [
                    'soa_id' => $soa->id,
                    'file_path' => $filePath,
                ]
            );
            // }
        }
        return redirect()->back();
    }

    public function publishSoa(Request $request)
    {
        $data = $request->all();
        $soa = ReportFinanceSoa::where('user_id', $data['user_id'])->where('booking_dates', $data['daterange'])->where('listings', $data['listings'])->first();
        if ($soa) {
            $image = public_path('storage/' . $soa->file_path);

            if (file_exists($image)) {
                unlink($image);
            }
            if ($request->hasFile('soa_file')) {
                $images = $request->all();
                $images = $images['soa_file'];
                // dd($images['pop']);
                // foreach ($request->file('image') as $images) {
                $fileName = time() . '_' . $images->getClientOriginalName();
                $filePath = $images->storeAs('financeSoa', $fileName, 'public');
                $soa->update(
                    [
                        'file_path' => $filePath,
                        'total' => $data['total'],
                    ]
                );
                // }
            }
            // dd($image);
            return redirect()->back();
        }
        // dd($soa);
        // dd($request->all());
        if ($request->hasFile('soa_file')) {
            $images = $request->all();
            $images = $images['soa_file'];
            // dd($images['pop']);
            // foreach ($request->file('image') as $images) {
            $fileName = time() . '_' . $images->getClientOriginalName();
            $filePath = $images->storeAs('financeSoa', $fileName, 'public');
            ReportFinanceSoa::create(
                [
                    'user_id' => $data['user_id'],
                    'booking_dates' => $data['daterange'],
                    'publish_date' => Carbon::now()->toDateString(),
                    'listings' => $data['listings'],
                    'file_path' => $filePath,
                    'total' => $data['total'],
                ]
            );
            // }
        }

        try {
            $request = $request->all();
            $request['listings'] = json_decode($request['listings']);
            $listing_arr = Listing::whereIn('id', $request['listings'])->get();

            if (!empty($listing_arr)) {
                $amount = $request['total'];
                $daterange = $request['daterange'];
                foreach ($listing_arr as $listing) {
                    $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];

                    if (!empty($user_ids_arr)) {

                        // Need to change here
                        $title = "New Payment Recieved";
                        $body = "Good News! You've earned $amount for $daterange. Review it now!";

                        foreach ($user_ids_arr as $user_id) {

                            $user = User::find($user_id);

                            if (!is_null($user) && !empty($user->fcmTokens) && $user->host_type_id == 2) { // for pro user only
                                foreach ($user->fcmTokens as $token) {
                                    try {

                                        $notificationData = [
                                            'id' => 0,
                                            'otaName' => '',
                                            'type' => 'payment_received',
                                        ];

                                        $send = $this->firebaseService->sendPushNotification($token, $title, $body, 'PaymentTrigger', $notificationData);
                                    } catch (\Exception $ex) {
                                        logger("Notification Error: " . $ex->getMessage());
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $ex) {
            //
        }

        return redirect()->back();
    }

    public function saveSoaDetails(Request $request)
    {
        $data = $request->all();
        $soa = ReportFinanceSoa::where('user_id', $data['user_id'])->where('booking_dates', $data['daterange'])->where('listings', $data['listings'])->first();
        $total = $data['details']['final_amount_paid_to_host'];
        // dd($data['details']['final_amount_paid_to_host']);
        if ($soa) {
            $soaDetails = SoaDetail::where('soa_id', $soa->id)->get();
            if ($soaDetails) {
                SoaDetail::where('soa_id', $soa->id)->delete();
            }
            // dd($soaDetails);

            if (isset($data['details']['livedin'])) {
                $livedInData = $data['details']['livedin'];

                // dd($hostData);
                foreach ($livedInData as $index => $data) {

                    if ($index === 'sales_tax') {
                        // dd($index);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index === 'total_rev_sales') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index !== 'sales_tax' && $index !== 'total_rev_sales' && $index !== 'total_livedIn') {
                        // dd($data);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => $index,
                                'head_type' => $data['head_type'],
                                'comment' => $data['comment'],
                                'value' => $data['value'],
                            ]
                        );

                    }
                    if ($index === 'total_livedIn') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                }
            }
            if (isset($data['details']['host'])) {
                $hostData = $data['details']['host'];
                foreach ($hostData as $index => $data) {

                    if ($index === 'sales_tax') {
                        // dd($index);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index === 'total_rev_sales') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index !== 'sales_tax' && $index !== 'total_rev_sales' && $index !== 'total_livedIn') {
                        // dd($data);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => $index,
                                'head_type' => $data['head_type'],
                                'comment' => $data['comment'],
                                'value' => $data['value'],
                            ]
                        );

                    }
                    if ($index === 'total_livedIn') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                }
            }
            // dd($data);
            $soa->update(['total' => $total]);
            return redirect()->back();
        } else {
            $soa = ReportFinanceSoa::create(
                [
                    'user_id' => $data['user_id'],
                    'booking_dates' => $data['daterange'],
                    'publish_date' => Carbon::now()->toDateString(),
                    'listings' => $data['listings'],
                    'file_path' => 'www.google.com',
                    'total' => $total,
                ]
            );

            if (isset($data['details']['livedin'])) {
                $livedInData = $data['details']['livedin'];
                // dd($hostData);
                foreach ($livedInData as $index => $data) {

                    if ($index === 'sales_tax') {
                        // dd($index);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index === 'total_rev_sales') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index !== 'sales_tax' && $index !== 'total_rev_sales' && $index !== 'total_livedIn') {
                        // dd($data);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => $index,
                                'head_type' => $data['head_type'],
                                'comment' => $data['comment'],
                                'value' => $data['value'],
                            ]
                        );

                    }
                    if ($index === 'total_livedIn') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'livedin',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                }
            }
            if (isset($data['details']['host'])) {
                $hostData = $data['details']['host'];
                foreach ($hostData as $index => $data) {

                    if ($index === 'sales_tax') {
                        // dd($index);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index === 'total_rev_sales') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                    if ($index !== 'sales_tax' && $index !== 'total_rev_sales' && $index !== 'total_livedIn') {
                        // dd($data);
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => $index,
                                'head_type' => $data['head_type'],
                                'comment' => $data['comment'],
                                'value' => $data['value'],
                            ]
                        );

                    }
                    if ($index === 'total_livedIn') {
                        SoaDetail::create(
                            [
                                'soa_id' => $soa->id,
                                'type' => 'host',
                                'alpa' => '',
                                'head_type' => '',
                                'comment' => '',
                                'value' => $data,
                            ]
                        );
                    }
                }
            }
            return redirect()->back();
        }
    }

    public function resetSoaDetails(Request $request)
    {
        $data = $request->all();
        $soa = ReportFinanceSoa::where('user_id', $data['user_id'])->where('booking_dates', $data['daterange'])->where('listings', $data['listings'])->first();
        if ($soa) {
            $soaDetails = SoaDetail::where('soa_id', $soa->id)->get();
            if ($soaDetails) {
                SoaDetail::where('soa_id', $soa->id)->delete();
            }
            return redirect()->back();
        }
    }

    public function downloadSoa(Request $request)
    {

        // dd('Dies');
        $request = $request->all();
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
                    // $days = $start->diffInDays($end);

                    // dd($start_date,$end_date);
                    // dd($booking,$period);
                    $bookingOtaDatas[] = $booking->id;
                    $bookingOtaJson = json_decode($booking->booking_otas_json_details);
                    // dd( $bookingOtaJson);
                    $raw_message = json_decode($bookingOtaJson->attributes->raw_message);
                    $promotion = $booking->promotion;
                    $discount = $booking->discount + $promotion;
                    $total = $booking->amount;
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

                    // Count the matching values
                    $nights = count($matchingValues);
                    $livedin_commission = $item->commission_value / 100 * $total;
                    $host_commission = $total - $discount - $ota_commission - $livedin_commission;
                    $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                    // if($booking->id == 1158) {
                    //     dd($booking, $nights);
                    // }
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
                        'name' => $bookingOtaJson->attributes->customer->name . ' ' . $bookingOtaJson->attributes->customer->surname,
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
                    // if($booking->id == 1217) {

                    //     dd(count($bookingPeriod),$matchingValues, $nights, count($bookingPeriod));
                    // }
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
        return view('Admin.reports.finance.downloadSoa', ['host' => $host, 'bookings' => $bookings, 'bookingsCod' => $bookingsCod, 'soa' => $soa, 'soaDetails' => $soaDetails]);
    }

    public function financeReportIndex(Request $request)
    {
        $data = $request->all();
        if (isset($request['daterange'])) {

            // $dateRange = $request['daterange'];
            // [$startDate, $endDate] = explode(' - ', $dateRange);
            // $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
            // $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
            $request = $request->all();
            $soa = ReportFinanceSoa::where('booking_dates', $request['daterange'])->get();
            // dd($soa);
            foreach ($soa as $record) {
                // dd($record);
                // if ($record) {
                //     $soaDetails = SoaDetail::where('soa_id', $record->id)->f();
                //     // dd($soaDetails);
                //     $soaDetails = $soaDetails->toArray();
                // } else {
                //     $soaDetails = null;
                // }
                $listing_arr = Listing::whereIn('id', json_decode($record->listings))->get();
                $bookingsCod = [];
                $bookings = [];
                $dateRange = $request['daterange'];
                [$startDate, $endDate] = explode(' - ', $dateRange);
                $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
                $period = CarbonPeriod::create($startDate, $endDate)->excludeEndDate();
                $periodDatesArray = $period->toArray();
                $periodDateStrings = array_map(fn($date) => $date->toDateString(), $periodDatesArray);
                $dates = [];
                $bookingOtaDatas = [];
                $bookingOtaliveds = [];
                foreach ($listing_arr as $key => $item) {
                    foreach ($period as $date) {
                        $listingIdArr = [$item->listing_id];
                        $bookingLivedIn = Bookings::where('listing_id', $item->id)
                            ->whereDate('booking_date_end', '>', $date->format('Y-m-d'))
                            ->whereDate('booking_date_start', '<=', $date->format('Y-m-d'))
                            ->get();
                        $listingRelation = ListingRelation::where('listing_id_airbnb', $item->id)->get();
                        if ($listingRelation) {
                            foreach ($listingRelation as $it) {
                                $listing_Bcom = Listing::where('id', $it->listing_id_other_ota)->first();
                                $listingIdArr[] = $listing_Bcom->listing_id;
                            }
                        }
                        $bookingOta = BookingOtasDetails::whereIn('listing_id', $listingIdArr)
                            ->whereDate('departure_date', '>', $date->format('Y-m-d'))
                            ->whereDate('arrival_date', '<=', $date->format('Y-m-d'))
                            ->get();
                        foreach ($bookingOta as $booking) {
                            if (in_array($booking->id, $bookingOtaDatas)) {
                                continue;
                            }
                            $start_date = $booking->arrival_date;
                            $end_date = $booking->departure_date;
                            $bookingOtaDatas[] = $booking->id;
                            $amount = $booking->amount;
                            $discount = $booking->discount;
                            $promotion = $booking->promotion;
                            $discount = $discount + $promotion;
                            $total_cleaning = $booking->cleaning_fee;
                            $apartment = Listing::where('listing_id', $item->listing_id)->first();
                            if ($apartment->is_cleaning_fee == 0 || $apartment->is_cleaning_fee == null) {
                                $cleaning_fee = $booking->cleaning_fee;
                                $amount = $amount + $cleaning_fee;
                            }
                            $start_date = $booking->arrival_date;
                            $end_date = $booking->departure_date;
                            $ota_commission = $booking->ota_commission;
                            $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                            $datesArray = $bookingPeriod->toArray();
                            $dateStrings = array_map(fn($date) => $date->toDateString(), $datesArray);
                            $bookingPeriod = CarbonPeriod::create($start_date, $end_date)->excludeEndDate();
                            $bookingPeriodDatesArray = $bookingPeriod->toArray();
                            $bookingPeriodDateStrings = array_map(fn($date) => $date->toDateString(), $bookingPeriodDatesArray);
                            $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);
                            $nights = count($matchingValues);
                            if ($apartment->pre_discount == 1) {
                                $livedin_commission = ($item->commission_value / 100 * ($amount - $discount)) * 1.15;
                            } else {
                                $livedin_commission = ($item->commission_value / 100 * $amount) * 1.15;
                            }
                            $host_commission = $amount - $discount - $ota_commission - $livedin_commission;
                            $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                            $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                            if ($apartment->is_co_host == 1) {
                                array_push($bookingsCod, [
                                    'start_date' => $start_date < $startDate ? $startDate : $start_date,
                                    'end_date' => $end_date > $endDate ? $endDate : $end_date,
                                    'nights' => $nights,
                                    'listing_id' => $item->listing_id,
                                    'status' => $booking->status == 'cancelled' ? $booking->status : $booking_status,
                                    'payment_status' => 'OTA',
                                    'type' => 'AirBnb',
                                    'booking_id' => 'O' . $booking->id,
                                    'name' => $booking->guest_name ?? '',
                                    'night_rate' => round($amount / $totalnights) > 0 ? round($amount / $totalnights) : 0,
                                    'total' => round($amount / $totalnights * $nights) > 0 ? round($amount / $totalnights * $nights) : 0,
                                    'discount' => round($discount / $totalnights * $nights),
                                    'post_discount_booking_amount' => round($amount / $totalnights * $nights) - round($discount / $totalnights * $nights),
                                    'ota_commission' => round($ota_commission / $totalnights * $nights),
                                    'livedin_commission' => round($livedin_commission / $totalnights * $nights, 1) > 0 ? round($livedin_commission / $totalnights * $nights, 1) : 0,
                                    'host_commission' => round($host_commission / $totalnights * $nights) > 0 ? round($host_commission / $totalnights * $nights) : 0,
                                    'total_cleaning' => round($total_cleaning / $totalnights * $nights),
                                ]);
                            } else {
                                array_push($bookings, [
                                    'start_date' => $start_date < $startDate ? $startDate : $start_date,
                                    'end_date' => $end_date > $endDate ? $endDate : $end_date,
                                    'nights' => $nights,
                                    'listing_id' => $item->listing_id,
                                    'status' => $booking->status == 'cancelled' ? $booking->status : $booking_status,
                                    'payment_status' => 'OTA',
                                    'type' => 'AirBnb',
                                    'booking_id' => 'O' . $booking->id,
                                    'name' => $booking->guest_name ?? '',
                                    'night_rate' => round($amount / $totalnights) > 0 ? round($amount / $totalnights) : 0,
                                    'total' => round($amount / $totalnights * $nights) > 0 ? round($amount / $totalnights * $nights) : 0,
                                    'discount' => round($discount / $totalnights * $nights),
                                    'post_discount_booking_amount' => round($amount / $totalnights * $nights) - round($discount / $totalnights * $nights),
                                    'ota_commission' => round($ota_commission / $totalnights * $nights),
                                    'livedin_commission' => round($livedin_commission / $totalnights * $nights, 1) > 0 ? round($livedin_commission / $totalnights * $nights, 1) : 0,
                                    'host_commission' => round($host_commission / $totalnights * $nights) > 0 ? round($host_commission / $totalnights * $nights) : 0,
                                    'total_cleaning' => round($total_cleaning / $totalnights * $nights),
                                ]);
                            }
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
                            $matchingValues = array_intersect($periodDateStrings, $bookingPeriodDateStrings);
                            $nights = count($matchingValues);
                            if ($nights === 0) {
                                $nights = 1;
                            }
                            $totalnights = Carbon::parse($start_date)->diffInDays(Carbon::parse($end_date));
                            $booking_status = $end_date > $endDate ? 'checked-in' : 'checked-out';
                            $apartment = Listing::where('id', $item->id)->first();
                            if ($apartment->pre_discount == 1) {
                                $livedin_commission = ($nights * ($booking->per_night_price - $discount / $nights) * $item->commission_value / 100) * 1.15;
                            } else {
                                $livedin_commission = ($item->commission_value / 100 * $booking->per_night_price * $nights) * 1.15;
                            }
                            $host_commission = $booking->per_night_price * $nights - $discount - $ota_commission - $livedin_commission;
                            $post_discount_booking_amount = ($booking->per_night_price * $nights) - $discount; // Calculate for LivedIn bookings
                            $total_cleaning = $booking->cleaning_fee; // Use cleaning_fee for LivedIn bookings
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
                                    'total' => $apartment->cleaning_fee_direct_booking == 1 ? round($booking->per_night_price * $nights) : round(($booking->per_night_price + $booking->cleaning_fee) * $nights),
                                    'discount' => round($discount),
                                    'post_discount_booking_amount' => round($post_discount_booking_amount),
                                    'ota_commission' => $apartment->ota_fee_direct_booking == 1 ? round($ota_commission / $totalnights * $nights) : 0,
                                    'livedin_commission' => round($livedin_commission, 1),
                                    'host_commission' => round($booking->per_night_price * $nights, 1) - (round($ota_commission, 1) / count($bookingPeriod) * $nights) - round($livedin_commission, 1),
                                    'total_cleaning' => round($total_cleaning / $totalnights * $nights),
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
                                    'night_rate' => $apartment->cleaning_fee_direct_booking == 1 ? round($booking->per_night_price) : round($booking->per_night_price + $booking->cleaning_fee),
                                    'total' => $apartment->cleaning_fee_direct_booking == 1 ? round($booking->per_night_price * $nights) : round(($booking->per_night_price + $booking->cleaning_fee) * $nights),
                                    'discount' => round($discount),
                                    'post_discount_booking_amount' => round($post_discount_booking_amount),
                                    'ota_commission' => $apartment->ota_fee_direct_booking == 1 ? round($ota_commission / $totalnights * $nights) : 0,
                                    'livedin_commission' => round($livedin_commission, 1),
                                    'host_commission' => round($booking->per_night_price * $nights, 1) - (round($ota_commission, 1) / count($bookingPeriod) * $nights) - round($livedin_commission, 1),
                                    'total_cleaning' => round($total_cleaning / $totalnights * $nights),
                                ]);
                            }
                        }
                        dd($bookings);
                    }
                }
            }
            // dd($startDate, $endDate);
        }
        return view('Admin.reports.finance.finance', []);
    }
}

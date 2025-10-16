<?php

namespace App\Http\Controllers\Admin\ListingManagement;

use App\Http\Controllers\Controller;
use App\Jobs\SaveCalenderData;
use App\Models\Calender;
use App\Models\Channels;
use App\Models\Listing;
use App\Models\ListingIcalLink;
use App\Models\Listings;
use App\Models\ListingSetting;
use App\Models\ListingShifting;
use App\Models\Properties;
use App\Models\RatePlan;
use App\Models\ReportFinanceSoa;
use App\Models\User;
use App\Models\Hostaboard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Auth;
use App\Services\ChannexProxyService;
use App\Models\ChurnedProperty;
use App\Models\ChannelToken;

use App\Models\Bookings;
use App\Models\BookingOtasDetails;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ListingManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('permission')->except('fetchListingInfo');
    }

    public function index(Request $request)
    {
        $data = $request->all();
        $channel_id = $data['channel_id'];
        $listings = Listings::where('channel_id', $channel_id)->get();
        $airbnb_listings = Listings::all();
        // dd($airbnb_listings);
        return view('Admin.listings-management.index', ['listings' => $listings, 'airbnb_listings' => $airbnb_listings]);
    }

    public function listingDataShift()
    {
        $listings = Listing::join('channels', 'listings.channel_id', '=', 'channels.id')
            ->whereNull('channels.connection_type')
            ->select('listings.id', 'listings.listing_id', \DB::raw('JSON_UNQUOTE(JSON_EXTRACT(listing_json, "$.title")) as title'), 'listings.created_at')
            ->where('is_sync', 'sync_all')
            ->get();
        $listing_shifting = ListingShifting::with('user')->get();
        return view('Admin.listings-management.data-shift', ['listings' => $listings, 'listing_shifting' => $listing_shifting]);
    }

    public function listingDataShiftUpdate(Request $request)
    {
        // dd(auth()->user()->email);
        $allowed_emails = [
            'syed.ali@livedin.co',
            'muhammad.faizan@livedin.co',
            'rajesh.kumar@livedin.co'
        ];
        if (!in_array(auth()->user()->email, $allowed_emails)) {
            dd("Ruk jaa bhai!! You are not allowed to do this operation please contact Ops Lead");
        }
        $request->validate([
            'listing_id_one' => 'required|different:listing_id_two',
            'listing_id_two' => 'required',
        ]);

        $data = $request->all();
        // dd($data);
        $listing_id_one = Listing::where('id', $data['listing_id_one'])
            ->select(
                'id',
                'listing_id',
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(listing_json, "$.title")) as title')
            )
            ->first();

        $listing_id_two = Listing::where('id', $data['listing_id_two'])
            ->select(
                'id',
                'listing_id',
                DB::raw('JSON_UNQUOTE(JSON_EXTRACT(listing_json, "$.title")) as title')
            )
            ->first();
        // dd($listing_id_one, $listing_id_two);
        DB::beginTransaction();

        try {
            // Update bookings table
            DB::table('bookings')
                ->where('listing_id', $listing_id_one->id)
                ->update(['listing_id' => $listing_id_two->id]);

            // Update booking_otas_details table
            DB::table('booking_otas_details')
                ->where('listing_id', $listing_id_one->listing_id)
                ->update(['listing_id' => $listing_id_two->listing_id]);

            // Update threads table
            DB::table('threads')
                ->where('listing_id', $listing_id_one->listing_id)
                ->update(['listing_id' => $listing_id_two->listing_id]);

            // Update calendars table
            DB::table('calenders')
                ->where('listing_id', $listing_id_one->listing_id)
                ->update(['listing_id' => $listing_id_two->listing_id]);

            // Delete from calendars table
            DB::table('calenders')
                ->where('listing_id', $listing_id_one->listing_id)
                ->delete();

            // Delete from listings table
            DB::table('listings')
                ->where('listing_id', $listing_id_one->listing_id)
                ->delete();

            // Delete from room_types table
            DB::table('room_types')
                ->where('listing_id', $listing_id_one->listing_id)
                ->delete();

            // Delete from rate_plans table
            DB::table('rate_plans')
                ->where('listing_id', $listing_id_one->listing_id)
                ->delete();

            // Update listing_relations table
            DB::table('listing_relations')
                ->where('listing_id_airbnb', $listing_id_two->id)
                ->update(['listing_id_airbnb' => $listing_id_one->id]);

            // Update cleanings table
            DB::table('cleanings')
                ->where('listing_id', $listing_id_two->id)
                ->update(['listing_id' => $listing_id_one->id]);

            // Update listing_ical_links table
            DB::table('listing_ical_links')
                ->where('listing_id', $listing_id_one->listing_id)
                ->update(['listing_id' => $listing_id_one->listing_id]);

            // Update listing_template table
            DB::table('listing_template')
                ->where('listing_id', $listing_id_one->listing_id)
                ->update(['listing_id' => $listing_id_one->listing_id]);

            $report_finances = ReportFinanceSoa::all();
            // dd($report_finances);
            foreach ($report_finances as $report_finance) {
                $listings = json_decode($report_finance->listings);
                // dd($listing_id_one->id);
                $index = array_search($listing_id_one->id, $listings);
                if ($index == true) {
                    $listings[$index] = $listing_id_two->id;
                    //decode this json
                    $report_finance->listings = json_encode($listings);
                    $report_finance->save();
                }
                // dd($index, $listings);
            }

            ListingShifting::create([
                'listing_id_one' => $listing_id_one->id,
                'title_one' => $listing_id_one->title,
                'listing_id_two' => $listing_id_two->id,
                'title_two' => $listing_id_two->title,
                'created_by' => auth()->id(),
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Data Shift Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update listing data: ' . $e->getMessage()], 500);
        }

    }

    public function saveVrboListing($channel_id)
    {
        // dd($data['data']['id'], $datas);
        $channel = Channels::where('ch_channel_id',$channel_id)->first();
        $channel_token = ChannelToken::where('channel_id', $channel->id)->first();
        $channel_token_json = json_decode($channel_token->token_json);
        // dd($channel,$channel_token, $channel_token_json);
        $listings = array();
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/mapping_details", [
                    "channel" => "VRBO",
                    "settings" => [
                        "username" => $channel_token_json->username,
                        "password" => $channel_token_json->password,
                        "token" => $channel_token_json
                    ],
                ]);

        if ($response->successful()) {
            $response = $response->json();
            //    dd($response['data']['property_id_dictionary']['values']);
            foreach ($response['data']['property_id_dictionary']['values'] as $item) {
                $parts = explode('.', $item['id']);
                // dd($parts);
                if (count($parts) > 2) {
                    // Reconstruct the number up to the second decimal point
                    $correctedValue = $parts[0] . $parts[1] . $parts[2];
                    // dd($correctedValue);
                    $result = (float) $correctedValue; // Convert to float
                }
                // dd($result);
                $listingData = [
                    "id" => $item['id'],
                    "type" => "apartment",
                    "title" => $item['title'],
                    "occupancies" => 1,
                    "rooms" => $item
                ];
                array_push($listings, $listingData);
            }
            // dd($channel->id);
            //    dd($listings);
            $listingsOld = Listing::where('channel_id', $channel->id)->pluck('listing_id');
            $listingsOld = $listingsOld->toArray();
            // dd($listings,$listingsOld);

            foreach ($listings as $item) {
                if (!in_array($item['id'], $listingsOld)) {
                    // dd($item['id']);

                    Listing::create([
                        'user_id' => json_encode(["$channel->user_id"]),
                        'listing_id' => $item['id'],
                        'listing_json' => json_encode($item),
                        'channel_id' => $channel->id,
                    ]);
                }
            }
            //    foreach ($listings as $listing) {
            // // dd($item['id']);
            //     if (!in_array($item['id'], $listingsOld)) {
            //     Listing::create([
            //         'user_id' => json_encode(["$channel->user_id"]),
            //         'listing_id' => $item['id'],
            //         'listing_json' => json_encode($item),
            //         'channel_id' => $channel->id,
            //     ]);
            // }
            // }

        } else {
            $error = $response->body();
            // dd( $error);
        }
    }

    public function syncListings(Request $request)
    {
        // dd($request);
        $channel = Channels::whereId($request->channel_id)->first();
        if ($channel->connection_type == 'VRBO') {
            $this->saveVrboListing($channel->ch_channel_id);
            return redirect()->back();
        }
        $listings = Listing::where('channel_id', $request->channel_id)->pluck('listing_id');
        $listings = $listings->toArray();
        // dd($listings);
        // 5f6b3682-16e9-47f9-b49a-6b6ec4c97581
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/action/listings");
        if ($response->successful()) {
            $availability = $response->json();
            foreach ($availability['data']['listing_id_dictionary']['values'] as $item) {
                // dd($item['id']);
                if (!in_array($item['id'], $listings)) {
                    Listing::create([
                        'user_id' => json_encode(["$channel->user_id"]),
                        'listing_id' => $item['id'],
                        'channel_id' => $request->channel_id,
                        'listing_json' => json_encode($item)
                    ]);
                }
            }
        } else {
            $error = $response->body();
            // dd($error);
        }
        return redirect()->back();
    }


    public function fetchListingsByChannelId(Request $request)
    {
        $listingResponse = array();
        $channel = Channels::where('ch_channel_id', $request->channel_id)->first();
        if (isset($request->is_sync) && $request->is_sync) {
            $listings = Listing::where('channel_id', $channel->id)->where('is_sync', $request->is_sync)->get();
        } else {
            $listings = Listing::where('channel_id', $channel->id)->get();
        }

        //        $listings = Listing::where('channel_id', $channel->id)->get();
        foreach ($listings as $item) {
            $listing = json_decode($item->listing_json);

            $listing->is_sync = $item->is_sync;

            array_push($listingResponse, $listing);
        }
        return response()->json($listingResponse);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function syncCalenderData($listing_id)
    {

        $user = Auth::user();
        // dd($user->email);
        if ($user->email !== 'ali.raza@lokal.pk') {
            dd('You are not allowed to do this operation please contact Ali Raza');
        }
        // dd($listing_id);
        $listing = Listing::where('listing_id', $listing_id)->first();
        $startDate = Carbon::parse($listing->created_at)->toDateString();

        // Add 600 days to the date
        $endDate = Carbon::parse($startDate)->addDays(600)->toDateString();


        //     $startDate = Carbon::parse($listing->created_at)->timestamp;

        // // Add 600 days to created_at
//     $endDate = Carbon::parse($listing->created_at)->addDays(600);
        // dd($startDate, $endDate);
        $rate_plan = RatePlan::where('listing_id', $listing_id)->first();
        $property = Properties::where('id', $rate_plan->property_id)->first();
        // dd($property);
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/restrictions?filter[property_id]=$property->ch_property_id&filter[date][gte]=$startDate&filter[date][lte]=$endDate&filter[restrictions]=rate,availability,max_stay,min_stay_through");

        if ($response->successful()) {
            $response = $response->json();
            $ari = $response['data'];
        } else {
            $error = $response->body();
        }
        $rate_plan = RatePlan::where('listing_id', $listing_id)->first();
        if (isset($ari[$rate_plan->ch_rate_plan_id])) {
            // Get all values for the specific key
            $calender = $ari[$rate_plan->ch_rate_plan_id];
            //                return response()->json($values);
        }
        SaveCalenderData::dispatch($calender, $listing_id);
        return redirect()->back();
    }

    public function fetchListingInfo(Request $request)
    {

        $listing = Listing::findOrfail($request->id);
        $dateRange = $request->daterange;
        // dd($listing);
        [$startDate, $endDate] = explode(' - ', $dateRange);

        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        $daysCount_system = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));

        $calender = Calender::whereBetween('calender_date', [$startDate, $endDate])->where('listing_id', $listing->listing_id)->get();
        $rate = 0;
        foreach ($calender as $item) {
            $rate += $item->rate;
        }

        $currentDate = Carbon::now()->format('Y-m-d');

        $currentDatePlusFourYears = Carbon::now()->addYears(4)->format('Y-m-d');

        // $calenderBlockDates = Calender::whereBetween('calender_date', [$currentDate, $currentDatePlusFourYears])
        // ->where('listing_id', $listing->listing_id)
        // ->orderBy('calender_date', 'ASC')
        // // ->pluck('calender_date', 'availability', 'is_lock', 'is_blocked')
        // ->get();

        $calendar_arr = [];
        // foreach($calenderBlockDates as $calenderBlockDate){
        //     if($calenderBlockDate->availability == 0 || $calenderBlockDate->is_lock == 1 || $calenderBlockDate->is_blocked == 1){
        //         $calendar_arr[] = $calenderBlockDate->calender_date;
        //     }
        // }

        $rate = !empty($rate) && !empty($daysCount_system) ? $rate / $daysCount_system : 0;
        // dd($daysCount_system, $rate);
        return response()->json(['rate' => round($rate), 'calenderBlockDates' => $calendar_arr]);
    }

    public function fetchCheckInOutBlocked(Request $request)
    {

        $listing = Listing::find($request->id);
        if (is_null($listing)) {
            return response()->json(['success' => 0, 'error' => 'Listing not found']);
        }

        if (empty($request->startDate) || empty($request->endDate)) {
            return response()->json(['success' => 0, 'error' => 'startDate or endDate not found']);
        }

        $flag = true;
        if (!empty($request->type) && $request->type == "edit") {
            $flag = false;
        }

        if ($flag) {
            $startDate = Carbon::parse($request->startDate)->addDay()->format('Y-m-d');
            $endDate = Carbon::parse($request->endDate)->subDay()->format('Y-m-d');

            $calendars = Calender::whereBetween('calender_date', [$startDate, $endDate])
                ->where('listing_id', $listing->listing_id)
                ->orderBy('calender_date', 'ASC')
                ->get();

            if (!empty($calendars)) {
                foreach ($calendars as $calendar) {
                    if ($calendar->availability == 0 || $calendar->is_lock == 1 || $calendar->is_blocked == 1) {
                        return response()->json(['success' => 0, 'error' => 'Calendar blocked']);
                    }
                }
            }
        }

        // Booking exists checks
        if ($flag) {
            $booking_exists = Bookings::where(['listing_id' => $listing->id, 'booking_date_start' => $request->startDate])->exists();
            if ($booking_exists) {
                return response()->json(['success' => 0, 'error' => 'Booking already exists on start date']);
            }
        }

        $booking_exists = BookingOtasDetails::where(['listing_id' => $listing->listing_id, 'arrival_date' => $request->startDate])->exists();
        if ($booking_exists) {
            return response()->json(['success' => 0, 'error' => 'OtaBooking already exists on start date']);
        }

        // Check booking exists on start date
        $booking_exists = Bookings::where(['listing_id' => $listing->id, 'booking_date_start' => $request->endDate])->exists();
        if ($booking_exists) {
            return response()->json(['success' => 1, 'error' => 'Booking allowed, booking_date_start exists']);
        }

        $booking_exists = BookingOtasDetails::where(['listing_id' => $listing->listing_id, 'arrival_date' => $request->endDate])->exists();
        if ($booking_exists) {
            return response()->json(['success' => 1, 'error' => 'OtaBooking allowed, arrival_date exists']);
        }

        // Check booking exists on end date
        $booking_exists = Bookings::where(['listing_id' => $listing->id, 'booking_date_end' => $request->startDate])->exists();
        if ($booking_exists) {
            return response()->json(['success' => 1, 'error' => 'Booking allowed, booking_date_end exists']);
        }

        $booking_exists = BookingOtasDetails::where(['listing_id' => $listing->listing_id, 'departure_date' => $request->startDate])->exists();
        if ($booking_exists) {
            return response()->json(['success' => 1, 'error' => 'OtaBooking allowed, departure_date exists']);
        }

        if ($flag) {
            $startCalendar = Calender::where(['listing_id' => $listing->listing_id, 'calender_date' => $request->startDate])->first();
            if (!is_null($startCalendar)) {
                if ($startCalendar->availability == 0 || $startCalendar->is_lock == 1 || $startCalendar->is_blocked == 1) {
                    return response()->json(['success' => 0, 'error' => 'startCalendar, Calendar blocked']);
                }
            }
        }

        return response()->json(['success' => 1, 'error' => 'All dates are enabled']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Listings $listings)
    {
        //
    }

    public function churnedListing($listing_id)
    {
        $listing = Listing::where('listing_id', $listing_id)->first();
        $churned_listing = ChurnedProperty::where('listing_id', $listing_id)->first();
        $today_date = Carbon::now()->toDateString();
        if ($churned_listing) {
            $churned_listing->delete();
            return redirect()->back();
        } else {
            ChurnedProperty::Create([
                'listing_id' => $listing_id,
                'churned_date' => $today_date,
            ]);
            return redirect()->back();
        }
    }

    public function edit($listing_id)
    {
        $listing = Listings::whereId($listing_id)->first();
        $channel = Channels::where('id', $listing->channel_id)->first();
        $data = [
            'request' => [
                'endpoint' => "/listings/$listing->listing_id/",
                'method' => 'get',
            ],
        ];
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/action/api_proxy", $data);
        // dd($listing);
        if ($response->successful()) {
            $response = $response->json();
            //  dd($response);
        } else {
            $error = $response->body();
            // dd($error);
        }
        return view('Admin.listings-management.edit-listing-details', ['listing' => $listing]);
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function addDiscounts(Request $request)
    {
        $listing = Listings::where('listing_id', $request->listing_id)->firstOrFail();
        $channel = Channels::whereId($listing->channel_id)->first();
        $channexProxyService = new ChannexProxyService();
        $rules = [];

        if ($request->early_bird_price_change < 0) {
            $rules[] = [
                "rule_type" => "BOOKED_BEYOND_AT_LEAST_X_DAYS",
                "price_change" => $request->early_bird_price_change,
                "price_change_type" => "PERCENT",
                "threshold_one" => $request->early_bird_threshold_one
            ];
        }

        if ($request->last_minute_price_change < 0) {
            $rules[] = [
                "rule_type" => "BOOKED_WITHIN_AT_MOST_X_DAYS",
                "price_change" => $request->last_minute_price_change,
                "price_change_type" => "PERCENT",
                "threshold_one" => $request->last_minute_threshold_one
            ];
        }


        $data = [
            'request' => [
                'endpoint' => "/pricing_and_availability/standard/pricing_settings/" . $request->listing_id,
                'method' => 'put',
                'payload' => [
                    "default_pricing_rules" => $rules
                ],
            ],
        ];

        $response = $channexProxyService->postToProxy($channel->channel_id, $data);

        return $this->editListingPricing($listing->id);
    }

    protected function getDiscounts($channelId, $listingId)
    {
        $channexProxyService = new ChannexProxyService();
        $data = [
            'request' => [
                'endpoint' => "/pricing_and_availability/standard/pricing_settings/" . $listingId,
                'method' => 'get',
            ],
        ];

        $response = $channexProxyService->postToProxy($channelId, $data);

        if (!empty($response['data']['pricing_setting']['default_pricing_rules'])) {
            $responseData = $response['data']['pricing_setting']['default_pricing_rules'];
            $result = [];
            if ($responseData && count($responseData) > 0) {
                foreach ($responseData as $data) {
                    if ($data['rule_type'] == 'BOOKED_WITHIN_AT_MOST_X_DAYS') {
                        $result['last_minute'] = [
                            "threshold_one" => $data['threshold_one' ?? 0],
                            "price_change" => $data['price_change'] ?? 0
                        ];
                    }
                    if ($data['rule_type'] == 'BOOKED_BEYOND_AT_LEAST_X_DAYS') {
                        $result['early_bird'] = [
                            "threshold_one" => $data['threshold_one' ?? 0],
                            "price_change" => $data['price_change'] ?? 0
                        ];
                    }
                }

                return $result;
            }
        }

    }

    public function editListingPricing($id)
    {
        $listing = Listings::findOrfail($id);
        $channel = Channels::whereId($listing->channel_id)->first();
        $listingIcalLink = ListingIcalLink::where('listing_id', $listing->listing_id)->first();
        // dd($listingIcalLink);
        $users = User::all();
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
        $experiencemanagers = User::where('role_id', 6)->get();

        if ($channel->connection_type != null) {
            return view('Admin.listings-management.edit', ['listing' => $listing, 'users' => $users, 'experiencemanagers' => $experiencemanagers, 'rate_plan' => $rate_plan->ch_rate_plan_id]);
        }

        // dd( $channel);


        $listing_settings = ListingSetting::where('listing_id', $listing->listing_id)->first();
        $listing_settings = $listing_settings->toArray();

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/execute/load_listing_price_settings", [
                    "listing_id" => $listing->listing_id
                ]);

        if ($response->successful()) {
            $response = $response->json();
            //   dd($response['data']);
            $listing_settings['ch_pricing_settings'] = $response['data'];
            //                dd($ari);
        } else {
            $error = $response->body();
            //    dd($error);
        }
        //    dd( $listing_settings);

        $hostaboards = DB::table('hostaboard')
            ->select('id', DB::raw("CONCAT(host_id, '-', property_id) AS ActivationCode"))
            ->where('is_old', 0)
            ->get();

        return view('Admin.listings-management.edit', [
            'listing' => $listing,
            'listingIcalLink' => $listingIcalLink,
            'users' => $users,
            'experiencemanagers' => $experiencemanagers,
            'rate_plan' => $rate_plan->ch_rate_plan_id,
            'listing_settings' => $listing_settings,
            "listing_discount" => $this->getDiscounts($channel->ch_channel_id, $listing->listing_id) ?? null,
            'hostaboards' => $hostaboards
        ]);
    }

    public function storeListingIcal(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'url' => ['required', 'url', 'regex:/^https?:\/\/.*\.ics(\?.*)?$/i'],
                'active' => 'required|boolean',
            ]);

            $existing = ListingIcalLink::where('listing_id', $id)->first();

            $icalLink = ListingIcalLink::updateOrCreate(
                ['listing_id' => $id],
                [
                    'url' => $validated['url'],
                    'active' => $validated['active'],
                    'token' => $existing ? $existing->token : Str::random(15),
                ]
            );

            return redirect()->back()->with('success', 'iCal link saved successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors([
                'ical_error' => 'Something went wrong while saving the iCal link. Please try again.',
            ])->withInput();
        }
    }

    public function getactivationdetail(Request $request)
    {
        $id = $request->input('id'); // Get id from request data
        $activation = Hostaboard::find($id);  // Fetch activation details by id

        if ($activation) {
            return response()->json(['success' => true, 'data' => $activation]);
        } else {
            return response()->json(['success' => false, 'message' => 'Property not found']);
        }
    }


    public function updateactivationdetail(Request $request)
    {
        Log::info('Incoming request to updateactivationdetail:', $request->all());


        try {
            // Update Hostaboard table


            $updateData = [
                'property_about' => $request->property_about,
                'be_listing_name' => $request->be_listing_name,
                'bedrooms' => $request->bedrooms,
                'beds' => $request->beds,
                'bathrooms' => $request->bathrooms,
                'city_name' => $request->city_name,
                'district' => $request->district,
                'street' => $request->street,
                'property_type' => $request->property_type,


                'is_allow_pets' => $request->input('is_allow_pets') == 1 ? 1 : 0,
                'is_self_check_in' => $request->input('is_self_check_in') == 1 ? 1 : 0,

                'living_room' => $request->input('living_room') == 1 ? 1 : 0,
                'laundry_area' => $request->input('laundry_area') == 1 ? 1 : 0,
                'corridor' => $request->input('corridor') == 1 ? 1 : 0,
                'outdoor_area' => $request->input('outdoor_area') == 1 ? 1 : 0,
                'kitchen' => $request->input('kitchen') == 1 ? 1 : 0,



                'cleaning_fee' => $request->cleaning_fee,
                'discounts' => $request->discounts,
                'tax' => $request->tax,
                'google_map' => $request->google_map,
                'property_google_map_link' => $request->google_map,
                //'is_old' => 1
            ];
            Log::info('Incoming request to updateactivationdetail:', $updateData);
            //dd($updateData);

            if ($request->activationid) {
                $hostaboard = Hostaboard::findOrFail($request->activationid);
                $hostaboard->update($updateData);

            }




            $listing = Listings::where('listing_id', $request->listingId)->firstOrFail();
            if ($request->activationid) {
                $updateData['activation_id'] = $request->activationid;
            }

            $listing->update($updateData);

            return response()->json(['success' => true, 'message' => 'Records updated successfully!']);


        } catch (\Exception $e) {

            Log::error('Error in updateactivationdetail:', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Error occurred while updating records.']);
        }

    }

    public function updateListingSettings(Request $request, $id)
    {
        // dd($request);

        $data = $request->all();
        //        dd($data['default_daily_price']);

        $listing_setting = ListingSetting::where('id', $id)->first();
        $listing = Listing::where('listing_id', $listing_setting->listing_id)->first();
        $channel = Channels::where('id', $listing->channel_id)->first();
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
        $property = Properties::where('id', $rate_plan->property_id)->first();
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id");
        if ($response->successful()) {
            $response = $response->json();
            $channex_channel_rate_plan = $response['data']['attributes']['rate_plans'];

            foreach ($channex_channel_rate_plan as $item) {
                //                dd($item['rate_plan_id']);
                if ($item['rate_plan_id'] == $data['rate_plan_id']) {
                    $channel_rate_plan = $item;
                    break;
                }

            }
            //            dd($channel_rate_plan['settings']['booking_setting']);

            //                dd($ari);
        } else {
            $error = $response->body();
            dd($error);
        }

        //dd($data['rate_plan_id'],  $channel->ch_channel_id);
//        dd($listing_setting,$id,$request);
        if (isset($data['instant_booking']) && $data['instant_booking']) {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->put(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/execute/update_booking_setting", [
                        "channel_rate_plan_id" => $channel_rate_plan['id'],
                        "data" => [
                            //                    "cancellation_policy_settings" => [
//                        "cancellation_policy_category" => "flexible",
//                        "lts_cancellation_policy_id" => "CANCEL_LONG_TERM_FAIR",
//                        "non_refundable_price_factor" => null
//                    ],
                            "check_in_time_end" => $channel_rate_plan['settings']['booking_setting']['check_in_time_end'],
                            "check_in_time_start" => $channel_rate_plan['settings']['booking_setting']['check_in_time_start'],
                            "check_out_time" => $channel_rate_plan['settings']['booking_setting']['check_out_time'],
                            //                    "guest_controls" => [
//                        "allows_children_as_host" => true,
//                        "allows_events_as_host" => false,
//                        "allows_infants_as_host" => true,
//                        "allows_pets_as_host" => false,
//                        "allows_smoking_as_host" => false,
//                        "children_not_allowed_details" => null,
//                        "pet_capacity" => null
//                    ],
//                    "instant_book_welcome_message" => null,
                            "instant_booking_allowed_category" => $data['instant_booking'],
                            //                    "listing_expectations_for_guests" => []
                        ]
                    ]);
            if ($response->successful()) {
                $response = $response->json();
                $listing_setting->update(['instant_booking' => $data['instant_booking']]);
                //                dd($ari);
            } else {
                $error = $response->body();
                dd($error);
            }
        }

        // dd($channel->ch_channel_id);
        // $response = Http::withHeaders([
        //     'user-api-key' => env('CHANNEX_API_KEY'),
        // ])->post(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/execute/update_pricing_setting", [
        //     "channel_rate_plan_id" => "9ab86e43-e09a-48cb-9d21-ae6dc3f6ef4d",
        //     "data" => [
        //         "cleaning_fee" => null,
        //         "default_daily_price" => 269,
        //         "eligible_for_pass_through_taxes" => null,
        //         "guests_included" => 1,
        //         "listing_currency" => "SAR",
        //         "monthly_price_factor" => 15,
        //         "pass_through_taxes" => [],
        //         "pass_through_taxes_collection_type" => "NO_AIRBNB_COLLECTED_TAX",
        //         "price_per_extra_person" => 6,
        //         "security_deposit" => null,
        //         "standard_fees" => [
        //             [
        //                 "amount" => 0.0,
        //                 "amount_type" => "flat",
        //                 "fee_unit_type" => null,
        //                 "charge_type" => "PER_GROUP",
        //                 "fee_type" => "PASS_THROUGH_CLEANING_FEE",
        //                 "offline" => false
        //             ],
        //         ],
        //         "weekend_price" => null,
        //         "weekly_price_factor" => 10
        //     ]
        // ]);
        // if ($response->successful()) {
        //     $availability = $response->json();
        //     dd($availability);
        // } else {
        //     $error = $response->body();
        //     dd($error);
        // }

        // Define your variables

        $user_api_key = env('CHANNEX_API_KEY');
        $channel_id = $channel->ch_channel_id;
        $standardfees = [];
        if (empty($request->standard_fees)) {
            return redirect()->back()->with('error', 'Standard fees are required.');
        }
        // dd($request->standard_fees);
        foreach ($request->standard_fees as $key => $item) {
            // dd($item,$key);
            $standardfees[] = [
                "amount" => (int) $item,
                "amount_type" => "flat",
                "fee_unit_type" => null,
                "charge_type" => "PER_GROUP",
                "fee_type" => $key,
                "offline" => false
            ];
        }

        // dd($standardfees);


        // Create the data array
        $data = [
            "channel_rate_plan_id" => $channel_rate_plan['id'],
            "data" => [
                "cleaning_fee" => $request->cleaning_fee,
                "default_daily_price" => $request->default_daily_price,
                "eligible_for_pass_through_taxes" => $request->eligible_for_pass_through_taxes,
                "guests_included" => $request->guests_included,
                "listing_currency" => $request->listing_currency,
                "monthly_price_factor" => $request->monthly_price_factor,
                "pass_through_taxes" => [],
                "pass_through_taxes_collection_type" => $request->pass_through_taxes_collection_type,
                "price_per_extra_person" => $request->price_per_extra_person,
                "security_deposit" => $request->security_deposit,
                "standard_fees" => $standardfees,
                "weekend_price" => $request->weekend_price,
                "weekly_price_factor" => $request->weekly_price_factor
            ]
        ];

        // Convert data array to JSON
        $json_data = json_encode($data);
        // dd($json_data);
        // Initialize cURL
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://app.channex.io/api/v1/channels/' . $channel_id . '/execute/update_pricing_setting',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT', // Change this to POST to send data
            CURLOPT_POSTFIELDS => $json_data, // Pass the JSON-encoded data
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'user-api-key: ' . $user_api_key
            ),
        ));

        // Execute cURL request and get response
        $response = curl_exec($curl);

        // Close cURL session
        curl_close($curl);

        // Output response
        // echo $response;

        // dd($response);


        // $curl = curl_init();

        // curl_setopt_array(
        //     $curl,
        //     array(
        //         CURLOPT_URL => "https://app.channex.io/api/v1/channels/" . $channel->ch_channel_id . "/execute/update_pricing_setting",
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 0,
        //         CURLOPT_FOLLOWLOCATION => true,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'PUT',
        //         CURLOPT_POSTFIELDS => '{
        //      "channel_rate_plan_id": "36dfcf2e-64ab-4d42-8320-6d2b04646afb",

        //         "data": {
        //             "cleaning_fee": ' . $data['cleaning_fee'] . ',
        //             "default_daily_price": ' . $data['default_daily_price'] . ',

        //             "eligible_for_pass_through_taxes": null,
        //             "guests_included": ' . $data['guests_included'] . ',
        //             "listing_currency": "' . $data['listing_currency'] . '",
        //            "monthly_price_factor": ' . $data['monthly_price_factor'] . ',
        //             "pass_through_taxes": [],
        //             "pass_through_taxes_collection_type": "NO_AIRBNB_COLLECTED_TAX",
        //             "price_per_extra_person": ' . $data['price_per_extra_person'] . ',
        //             "security_deposit": ' . $data['pass_through_security_deposit'] . ',
        //             "standard_fees": [

        //                 {
        //                     "amount": ' . $data['pass_through_community_fee'] . ',
        //                     "amount_type": "flat",
        //                     "charge_period": "PER_BOOKING",
        //                     "charge_type": "PER_GROUP",
        //                     "fee_type": "PASS_THROUGH_COMMUNITY_FEE",
        //                     "offline": false
        //                 },
        //                 {
        //                     "amount":' . $data['pass_through_linen_fee'] . ',
        //                     "amount_type": "flat",
        //                     "charge_period": "PER_BOOKING",
        //                     "charge_type": "PER_GROUP",
        //                     "fee_type": "PASS_THROUGH_LINEN_FEE",
        //                     "offline": false
        //                 },
        //                 {
        //                     "amount": ' . $data['pass_through_resort_fee'] . ',
        //                     "amount_type": "flat",
        //                     "charge_period": "PER_BOOKING",
        //                     "charge_type": "PER_GROUP",
        //                     "fee_type": "PASS_THROUGH_RESORT_FEE",
        //                     "offline": false
        //                 },
        //                 {
        //                     "amount": ' . $data['pass_through_cleaning_fee'] . ',
        //                     "amount_type": "flat",
        //                     "charge_period": "PER_BOOKING",
        //                     "charge_type": "PER_GROUP",
        //                     "fee_type": "PASS_THROUGH_CLEANING_FEE",
        //                     "offline": false
        //                 },
        //                 {
        //                     "amount": ' . $data['pass_through_short_term_cleaning_fee'] . ',
        //                     "amount_type": "flat",
        //                     "charge_period": "PER_BOOKING",
        //                     "charge_type": "PER_GROUP",
        //                     "fee_type": "PASS_THROUGH_SHORT_TERM_CLEANING_FEE",
        //                     "offline": false
        //                 }
        //             ],
        //             "weekend_price": ' . $data['weekend_price'] . ',
        //             "weekly_price_factor": ' . $data['weekly_price_factor'] . ',
        //             "monthly_price_factor": ' . $data['monthly_price_factor'] . ',
        //             "weekly_price_factor": ' . $data['weekly_price_factor'] . '
        //         }
        //     }',
        //         CURLOPT_HTTPHEADER => array(
        //             'user-api-key: urucl9zRQNDO35/5NIlWnaQSf13AECW7WW8EwoOURoPTqs1oGbbkceq1ArXolgoZ',
        //             'Content-Type: application/json'
        //         ),
        //     )
        // );

        // $response = curl_exec($curl);

        // curl_close($curl);
        //        dd($response);

        $listing_setting->update($data['data'] ?? $data);

        // $startDate = Carbon::today()->toDateString();
        // $endDate = Carbon::today()->addDays(500)->toDateString();
        // $response = Http::withHeaders([
        //     'user-api-key' => env('CHANNEX_API_KEY'),
        // ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
        //             "values" => [
        //                 [
        //                     "date_from" => $startDate,
        //                     "date_to" => $endDate,
        //                     "property_id" => $property->ch_property_id,
        //                     "rate_plan_id" => $data['rate_plan_id'],
        //                     'rate' => $data['default_daily_price']
        //                 ]
        //             ]
        //         ]);

        // if ($response->successful()) {
        //     $restrictions = $response->json();

        //     Calender::where('listing_id', $listing->listing_id)->update(['rate' => $data['default_daily_price']]);
        // } else {
        //     $error = $response->body();
        //     dd($error);
        // }
        return redirect()->back();

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $listing = Listings::findOrfail($id);
        $channel = Channels::where('id', $listing->channel_id)->first();
        $channel = $channel->toArray();
        $channel['user_id'] = (int) $request->host_id;
        $listing_user = json_decode($listing->user_id);
        array_push($listing_user, $request->host_id);
        $listing_user = json_encode($listing_user);
        $listing->update(['user_id' => $listing_user]);
        Channels::create($channel);
        return redirect()->back();
    }
    public function commissionUpdate(Request $request, $id)
    {
        // dd($request->pre_discount);
        $listing = Listings::findOrfail($id);
        $listing_json = json_decode($listing->listing_json);
        $listing_json->title = $request->title;
        $listing_json = json_encode($listing_json);
        $listing->update(
            [
                'commission_type' => $request->commission_type,
                'commission_value' => $request->commission_value,
                'listing_json' => $listing_json,
                'google_map' => $request->google_map,
                'apartment_num' => $request->apartment_num,
                'is_churned' => $request->is_churned,
                'cleaning_fee_direct_booking' => $request->cleaning_fee_direct_booking,
                'ota_fee_direct_booking' => $request->ota_fee_direct_booking,
                'is_cleaning_fee' => $request->is_cleaning_fee,
                'cleaning_fee_per_cycle' => $request->cleaning_fee_per_cycle,
                'pre_discount' => (int) $request->pre_discount,
                'is_co_host' => (int) $request->is_co_host,

            ]
        );
        // dd($listing);
        return redirect()->back();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $listing = Listings::findOrfail($id);
        $listing_user = json_decode($listing->user_id);
        $listing_user_new = array();
        foreach ($listing_user as $key => $item) {
            if ($item != $request->user_id) {
                array_push($listing_user_new, $item);
            }
        }
        $listing_user = json_encode($listing_user_new);
        $listing->update(['user_id' => $listing_user]);
        return redirect()->back();
    }


    public function updatemanager(Request $request, $id)
    {
        $listing = Listings::findOrFail($id);

        // Decode the current managers, or initialize as an empty array if null
        $listing_user = $listing->exp_managers ? json_decode($listing->exp_managers, true) : [];

        // Add the new manager ID
        array_push($listing_user, $request->exp_manager_id);

        // Encode back to JSON and update the listing
        $listing_user = json_encode($listing_user);
        $listing->update(['exp_managers' => $listing_user]);

        return redirect()->back();
    }

    public function destroyexpmanager(Request $request, $id)
    {
        $listing = Listings::findOrfail($id);
        $listing_user = json_decode($listing->exp_managers);
        $listing_user_new = array();
        foreach ($listing_user as $key => $item) {
            if ($item != $request->exp_manager_id) {
                array_push($listing_user_new, $item);
            }
        }
        $listing_user = json_encode($listing_user_new);
        $listing->update(['exp_managers' => $listing_user]);
        return redirect()->back();
    }
}

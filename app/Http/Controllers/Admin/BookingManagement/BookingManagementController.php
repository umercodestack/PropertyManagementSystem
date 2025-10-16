<?php

namespace App\Http\Controllers\Admin\BookingManagement;

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
use App\Models\BookingReference as BookingReferences;
use App\Models\BookingImages;
use App\Models\MobileNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Services\StoreProcedureService;
use App\Models\Vendors;
use Twilio\Rest\Client;
use App\Models\ListingRelation;
use App\Services\FirebaseService;

use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Admin\MagaRental\MagaRentalController;




class BookingManagementController extends Controller
{
    protected $client;
    protected $storeProcedureService = false;
    public function __construct(StoreProcedureService $storeProcedureService)
    {

        $this->storeProcedureService = $storeProcedureService;
        $this->middleware('permission');

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->client = new Client($sid, $token);

    }

   public function index(Request $request)
{
    $query = Bookings::with(['guest', 'listing'])->orderBy('created_at', 'DESC');

    if ($request->filled('booking_status')) {
        $query->where('booking_status', $request->booking_status);
    }

    $bookings = $query->get();

    return view('Admin.bookings-management.index', compact('bookings'));
}

    public function syncBookings()
    {
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->get(env('CHANNEX_URL') . "/api/v1/bookings?pagination[limit]=63");
        if ($response->successful()) {
            $response = $response->json();
            foreach ($response['data'] as $items) {
                $booking_id = $items['id'];
                $response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])->get(env('CHANNEX_URL') . "/api/v1/bookings/$booking_id");

                if ($response->successful()) {
                    $response = $response->json();
                    $raw_mesasge = json_decode($response['data']['attributes']['raw_message']);
                    $listing_id = isset($raw_mesasge->reservation->listing_id) && $raw_mesasge->reservation->listing_id ? $raw_mesasge->reservation->listing_id : 12345;
                    //                        dd($response['data']);
                    $arrival_date = $response['data']['attributes']['arrival_date'];
                    $departure_date = $response['data']['attributes']['departure_date'];
                    // dd($arrival_date, $departure_date, $listing_id);
                    $booking_detail_json = json_encode($response['data']);

                    //                        $listing = $response['data']['listing'];
//                dd($listing['pricing_settings']['default_daily_price']);

                } else {
                    $error = $response->body();
                    // dd($error);
                }


            }

        } else {
            $error = $response->body();
            // dd($error);
        }
    }

    public function index_ota(Request $request)
    {
        $data = $request->all();
        if (isset($data['system_status']) && $data['system_status'] != null) {
            $bookings = BookingOtasDetails::orderBy('created_at', 'DESC')->where('system_status', $data['system_status'])->get();
        } else {
            $bookings = BookingOtasDetails::orderBy('id', 'Desc')->get();
        }
        return view('Admin.bookings-management.ota_index', ['bookings' => $bookings]);
    }

    public function editOtaBooking(Request $request, $id)
    {
        $role = Auth::user()->role_id;
        $bookings = BookingOtasDetails::whereId($id)->first();
        $booking_images = BookingImages::where('booking_id', $bookings->id)->get();
        $booking_payment_images = BookingReferences::where('booking_id', $bookings->id)->where('booking_type', 'ota')->get();
        $listings = Listings::all();

        $listing = Listings::where(
            $bookings->ota_name == 'Almosafer' ? 'id' : 'listing_id',
            $bookings->listing_id
        )->first();

        $almosafer_airbnb_listing_id = 0;
        if ($bookings->ota_name == 'Almosafer') {

            $almosafer_listing_relation = ListingRelation::where('listing_id_other_ota', $bookings->listing_id)
                ->where('listing_type', 'Almosafer')
                ->first();
            $almosafer_airbnb_listing_id = $almosafer_listing_relation->listing_id_airbnb ?? 0;
        }

        $list_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
        $listing_name = !empty($list_json->title) ? $list_json->title : '';
        $bk_json = !empty($bookings->booking_otas_json_details) ? json_decode($bookings->booking_otas_json_details) : '';
        return view('Admin.bookings-management.ota-edit', ['role' => $role, 'booking_images' => $booking_images, 'booking' => $bookings, 'listings' => $listings, 'bk_json' => $bk_json, 'listing_name' => $listing_name, 'listing' => $listing, 'booking_payment_images' => $booking_payment_images, 'almosafer_airbnb_listing_id' => $almosafer_airbnb_listing_id]);
    }

    public function updateOtaBooking(Request $request, $id)
    {
        if (isset($request->amount_received)) {
            $booking = BookingOtasDetails::whereId($id)->first();

            $booking->update([
                'amount_received' => $request->amount_received,
                'forex_adjustement' => $request->forex_adjustement,
                'reference_numbers' => $request->reference_numbers,
                'payment_status' => $request->payment_status,
            ]);
            return redirect()->back()->with('success', 'Booking updated successfully');
        }

        $dateRange = $request['daterange'];

        [$startDate, $endDate] = explode(' - ', $dateRange);
        // dd($startDate, $endDate);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        $endDates = $endDate;

        $booking = BookingOtasDetails::whereId($id)->first();

        $proof_of_payment = "";
        if ($request->hasFile('proof_of_payment')) {

            if (!empty($booking->proof_of_payment)) {
                Storage::delete('public/' . $booking->proof_of_payment);
            }

            // Save new file
            $file = $request->file('proof_of_payment');
            $filePath = $file->store('proof_of_payments', 'public');
            $proof_of_payment = $filePath;
        }

        if ($booking->ota_name == "Almosafer") {

            $MagaRentalController = new MagaRentalController();

            $booking_listing_id = $booking->listing_id;

            $listingRelation = ListingRelation::where('listing_id_other_ota', $booking_listing_id)
                ->where('listing_type', 'Almosafer')
                ->first();

            if (!is_null($listingRelation)) {

                $almsr_listing = Listings::where('id', $listingRelation->listing_id_airbnb)->first();
                $almsr_listing_id = $almsr_listing->listing_id ?? 0;

                $booking_listing_id = $almsr_listing_id;

                $subEndDate = Carbon::parse($endDate)->subDay()->toDateString();

                if ((int) $request->apartment_id != $booking_listing_id) {

                    $this->updateOtaAvailability($startDate, $subEndDate, $request->apartment_id, 0);
                    $this->updateOtaAvailability($startDate, $subEndDate, $booking_listing_id, 1);

                    // Almosafer Old Property set available
                    $almsfr_respn = $MagaRentalController->almosafer_block_calendar($almsr_listing_id, $startDate, $subEndDate, 1);

                    // Almosafer New property set unavailable
                    $new_almsr_airbnb_listing = Listings::where('id', $request->apartment_id)->first();
                    if (!is_null($new_almsr_airbnb_listing)) {
                        $new_almsfr_respn = $MagaRentalController->almosafer_block_calendar($new_almsr_airbnb_listing->listing_id, $startDate, $subEndDate, 0);
                    }
                }
                if ($startDate != $booking->arrival_date || $endDates != $booking->departure_date) {

                    $bookingEndDate = Carbon::parse($booking->departure_date)->subDay()->toDateString();

                    $this->updateOtaAvailability($booking->arrival_date, $bookingEndDate, $booking_listing_id, 1);
                    $this->updateOtaAvailability($startDate, $subEndDate, $request->apartment_id, 0);

                    // Almosafer Old Property set available
                    $almsfr_respn = $MagaRentalController->almosafer_block_calendar($almsr_listing_id, $booking_management->booking_date_start, $endDateDb->toDateString(), 1);

                    // Almosafer second property set unavailable
                    $new_almsr_airbnb_listing = Listings::where('id', $request->apartment_id)->first();
                    if (!is_null($new_almsr_airbnb_listing)) {
                        $new_almsfr_respn = $MagaRentalController->almosafer_block_calendar($new_almsr_airbnb_listing->listing_id, $startDate, $endDate->toDateString(), 0);
                    }
                }

                if (!empty($proof_of_payment)) {
                    $booking->proof_of_payment = $proof_of_payment;
                }

                $booking->listing_id = $request->apartment_id;
                $booking->discount = $request->discount;
                $booking->promotion = $request->promotion;
                $booking->ota_commission = $request->ota_commission;
                $booking->cleaning_fee = $request->cleaning_fee;
                $booking->amount = $request->total;
                $booking->arrival_date = $startDate;
                $booking->departure_date = $endDates;
                $booking->status = $request->status;
                $booking->system_status = $request->system_status;
                $booking->save();

                return redirect()->route('bookings.ota');
            }
            return redirect()->back()->with('error', 'Booking error. Please try again');
        }

        $booking_details = json_decode($booking->booking_otas_json_details);
        $raw_message = json_decode($booking_details->attributes->raw_message);

        $discount = 0;
        $promotion = 0;
        $promo = array();
        $disc = array();

        if (isset($raw_message->reservation->pricing_rule_details)) {
            foreach ($raw_message->reservation->pricing_rule_details as $items) {
                $items->amount_native = $request->discount / count($raw_message->reservation->pricing_rule_details);
                array_push($disc, ($items));
                $discount += $items->amount_native;
            }
        }
        // dd($raw_message->reservation->promotion_details, $raw_message->reservation->pricing_rule_details);
        if (isset($raw_message->reservation->promotion_details)) {
            foreach ($raw_message->reservation->promotion_details as $items) {

                $items->amount_native = $request->promotion / count($raw_message->reservation->promotion_details);
                array_push($promo, ($items));

                $promotion += $items->amount_native;
            }
        }
        if (isset($raw_message->reservation->standard_fees_details[0]->amount_native)) {
            $raw_message->reservation->standard_fees_details[0]->amount_native = $request->cleaning_fee;

        }

        if (!empty($promo)) {
            $raw_message->reservation->promotion_details = $promo;
        }

        if (!empty($disc)) {
            $raw_message->reservation->pricing_rule_details = $disc;
        }

        // dd($promotion, $discount);
        $booking_details->attributes->ota_commission = $request->ota_commission;
        // dd($raw_message->reservation->promotion_details);

        // dd($raw_message->reservation->listing_base_price_accurate + -($raw_message->reservation->promotion_details[0]->amount_native));

        // Comment
        // $raw_message->reservation->listing_base_price_accurate =
        //     $request->total +
        //     $promotion +
        //     $discount;

        // dd($raw_message);
        // dd($raw_message->reservation->listing_base_price_accurate);
        // dd($raw_message->reservation->listing_base_price_accurate);
        $booking_details->attributes->raw_message = json_encode($raw_message);

        $booking_details->attributes->arrival_date = $startDate;
        $booking_details->attributes->departure_date = $endDates;

        $booking->booking_otas_json_details = json_encode($booking_details);

        // Update Caldender
        // dd($request->apartment_id, $booking->listing_id);
        $request->apartment_id = (int) $request->apartment_id;

        if ((int) $request->apartment_id != $booking->listing_id) {
            $endDate = Carbon::parse($endDate);
            $endDate = $endDate->subDay();
            $this->updateOtaAvailability($startDate, $endDate->toDateString(), $request->apartment_id, 0);
            $this->updateOtaAvailability($startDate, $endDate->toDateString(), $booking->listing_id, 1);
        }
        if ($startDate != $booking->arrival_date || $endDate != $booking->departure_date) {
            $endDateDb = $booking->booking_date_end;
            $endDateDb = Carbon::parse($endDateDb);
            $endDateDb = $endDateDb->subDay();
            $endDate = Carbon::parse($endDate);
            $endDate = $endDate->subDay();
            $this->updateOtaAvailability($booking->arrival_date, $endDateDb->toDateString(), $booking->listing_id, 1);
            $this->updateOtaAvailability($startDate, $endDate->toDateString(), $request->apartment_id, 0);
        }
        // Update Caldender

        $endDate = Carbon::parse($endDate);
        $endDate = $endDate->addDay();



        // dd($booking->booking_otas_json_details);

        $otabData = [
            'listing_id' => $request->apartment_id,
            'arrival_date' => $startDate,
            'departure_date' => $endDates,
            'booking_otas_json_details' => $booking->booking_otas_json_details,
            'status' => $request->status,
            'system_status' => $request->system_status,

        ];

        if (!empty($proof_of_payment)) {
            $otabData['proof_of_payment'] = $proof_of_payment;
        }

        // print_r($otabData);die;

        $booking->update($otabData);



        return redirect()->route('bookings.ota');

    }

    public function deleteFile($id)
    {
        $booking = BookingOtasDetails::whereId($id)->first();

        if (!empty($booking->proof_of_payment)) {
            Storage::delete('public/' . $booking->proof_of_payment);
            $booking->proof_of_payment = null;
            $booking->save();

            return redirect()->route('booking.updateOtaBooking', $booking->id)->with('success', 'File deleted successfully');
        }

        return redirect()->route('booking.updateOtaBooking', $booking->id)->with('error', 'Something went wrong');
    }

    public function create()
    {
        $users = User::all();

        $data = [];
        $listings = Listing::where('is_sync', 'sync_all')->get();
        foreach ($listings as $listing) {
            // if (is_null($listing->channel) || !is_null($listing->channel->connection_type)) {
            //     continue;
            // }
            $data[] = $listing;
        }

        $countries = DB::select("Select * from countries");
        return view('Admin.bookings-management.create', ['listings' => $data, 'users' => $users, 'countries' => $countries]);
    }

    public function blockAvailability($listing_id, $startDate, $endDate, $blockAvailability = 0)
    {
        $listing = Listings::where('id', $listing_id)->first();
        $endDate = Carbon::parse($endDate)->format('Y-m-d');

        $room_type = RoomType::where('listing_id', $listing->listing_id)->first();
        $property = Properties::where('id', $room_type->property_id)->first();
        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    "values" => [
                        [
                            //                        'date' => '2024-11-21',
                            "date_from" => $startDate,
                            "date_to" => $endDate,
                            "property_id" => $property->ch_property_id,
                            "room_type_id" => $room_type->ch_room_type_id,
                            "availability" => $blockAvailability,
                        ],
                    ]
                ]);
        if ($response->successful()) {
            $availability = $response->json();
            Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                ->update(
                    ['availability' => $blockAvailability]
                );

        } else {
            $error = $response->body();
        }
    }


    public function sendMessage($to, $message, $type)
    {

        $to = $this->formatPhoneNumber($to);

        $from = $type === 'whatsapp'
            ? env('TWILIO_WHATSAPP_FROM')
            : env('TWILIO_PHONE_NUMBER');


        if ($type === 'whatsapp') {
            $to = "whatsapp:$to";
        }


        try {

            return $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function formatPhoneNumber($to)
    {

        $to = preg_replace('/[\s()-]+/', '', $to);


        if (substr($to, 0, 1) === '0') {
            $to = substr($to, 1);
        }


        $to = '+' . $to;

        return $to;
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'country' => 'required',
            'city' => 'required',
            'cnic_passport' => 'required',
            'adult' => 'required',
            'children' => 'required',
            'rooms' => 'required',
            'payment_method' => 'required',
            'apartment_id' => 'required',
            'booking_sources' => 'required',
            'daterange' => 'required',
        ]);
        $guest = Guests::where('id', $request->guest_id)->first();
        $data = $request->all();
        $data['created_by'] = Auth::user()->id;
        $dateRange = $data['daterange'];

        [$startDate, $endDate] = explode(' - ', $dateRange);

        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        $data['booking_date_start'] = $startDate;
        $data['booking_date_end'] = $endDate;
        $data['listing_id'] = $data['apartment_id'];
        $listing = Listing::where('id', $data['listing_id'])->first();
        $listing_json = json_decode($listing->listing_json);
        $listing_name = $listing_json->title;
        if ($guest) {
            $existingBooking = Bookings::where('listing_id', $data['apartment_id'])
                ->where('booking_date_start', $startDate)
                ->where('booking_date_end', $endDate)
                ->first();

            if ($existingBooking) {
                return redirect()->back()->with('error', 'A booking already exists for this apartment at the selected dates.');
            } else {
                $booking = Bookings::create($data);
            }
            MobileNotification::create([
                'listing_id' => $listing->listing_id,
                'booking_id' => $booking->id,
                'ota_type' => 'livedin',
                'type' => 'booking',
                'price' => $data['total_price'],
                'notification_label' => $data['name'] . ' ' . $data['surname'] . ' has booked ' . $listing_name,
                'status' => 'unread',
                'booking_dates' => $startDate . ' to ' . $endDate,
                'listing_name' => $listing_name,
            ]);
            if ($request->hasFile('image') && count($request->file('image')) > 0) {
                foreach ($request->file('image') as $images) {
                    $fileName = time() . '_' . $images->getClientOriginalName();
                    $filePath = $images->storeAs('booking_images', $fileName, 'public');
                    BookingImages::create(
                        [
                            'booking_id' => $booking->id,
                            'image' => $filePath,
                        ]
                    );
                }
            }
            if ($request->hasFile('payment_references') && count($request->file('payment_references')) > 0) {
                foreach ($request->file('payment_references') as $images) {
                    $fileName = time() . '_' . $images->getClientOriginalName();
                    $filePath = $images->storeAs('reference_images', $fileName, 'public');
                    BookingReferences::create(
                        [
                            'booking_id' => $booking->id,
                            'booking_type' => 'livedin',
                            'image' => $filePath,
                        ]
                    );
                }
            }
            $endDate = Carbon::parse($endDate);

            $endDate = $endDate->subDay();
            $endDate = $endDate->toDateString();
            $this->blockAvailability($data['apartment_id'], $startDate, $endDate);

            // Almosafer Calendar Update
            $MagaRentalController = new MagaRentalController();

            $almsr_listing = Listings::where('id', $data['apartment_id'])->first();
            $almsr_listing_id = $almsr_listing->listing_id ?? 0;

            $almsfr_respn = $MagaRentalController->almosafer_block_calendar($almsr_listing_id, $startDate, $endDate, 0);


            $listing_name_arr = !empty($listing_name) ? explode(" ", $listing_name) : "";
            $list_name_expld = !empty($listing_name_arr[0]) ? $listing_name_arr[0] : "";

            $title = "Booking Confirmed";
            $body = "Booking confirmed! " . $data['name'] . " is staying at " . $list_name_expld . " from " . Carbon::parse(trim($startDate))->format('j M Y') . " to " . Carbon::parse(trim($endDate))->format('j M Y') . ".";

            $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];

            if (!empty($user_ids_arr)) {

                $firebase_service = app(FirebaseService::class);

                $notificationData = [
                    'id' => $booking->id,
                    'otaName' => 'livedin',
                    'type' => 'booking_detail',
                ];

                foreach ($user_ids_arr as $user_id) {

                    $user = User::find($user_id);

                    if (!is_null($user) && !empty($user->fcmTokens)) {

                        if ($user->host_type_id == 2) { // for pro user only
                            foreach ($user->fcmTokens as $token) {
                                if (!empty($token->fcm_token)) {
                                    try {
                                        $send = $firebase_service->sendPushNotification($token->fcm_token, $title, $body, "BookingTrigger", $notificationData);
                                    } catch (\Exception $ex) {
                                        logger("Notification Error: " . $ex->getMessage());
                                    }
                                }
                            }
                        }

                        // if(!empty($bodyForLite) && $user->host_type_id == 1) { // for lite user only

                        //     $numberOfGuests = $data['adult'] + $data['children'];
                        //     $bodyForLite = "Booking Confirmed! ".$data['name']." has booked your space from ".Carbon::parse(trim($startDate))->format('j M Y')." to ".Carbon::parse(trim($endDate))->format('j M Y')." for ".$numberOfGuests." guests.";

                        //     sendLiteNotification($user->id, "Instant Book Confirmations", $bodyForLite, 0);
                        // }
                    }
                }
            }


            try {

                $procedurparameter = [
                    'p_listing_id' => $booking->listing_id,
                    'P_Booking_Id' => $booking->id
                ];

                $result = $this->storeProcedureService
                    ->name('sp_check_triggers_and_bookings_livedin_V4')
                    ->InParameters([
                        'p_listing_id',
                        'P_Booking_Id'

                    ])
                    ->OutParameters(['return_value', 'return_message', 'return_host_id', 'return_vendor_id'])
                    ->data($procedurparameter)
                    ->execute();

                $response = $this->storeProcedureService->response();

                if ($response['response']['return_host_id'] > 0) {

                    $host_id = $response['response']['return_host_id'];
                    $vendor_id = $response['response']['return_vendor_id'];

                    $vendor = Vendors::find($vendor_id);
                    $userDB = User::find($host_id);

                    if ($vendor != null) {

                        if (!empty($vendor->phone) && $vendor->phone != '0') {

                            $vendorPhone = $vendor->country_code . $vendor->phone;
                            $this->sendMessage($vendorPhone, "New Task Created", "sms");

                        } else {

                            logger("Message not sent to vendor: Invalid phone number for vendor ID {$vendor->id}");
                        }
                    }

                    if (!empty($userDB->phone) && $userDB->phone != '0') {


                        $this->sendMessage($userPhone, "New Task Created", "whatsapp");

                    } else {

                        logger("Message not sent to user: Invalid phone number for user ID {$vendor->id}");
                    }

                }

                logger("*************************LivedIn Automated Task Executed *************************");

            } catch (\Exception $e) {

                logger("*************************LivedIn Automated Task Error *************************");
            }


            return redirect()->route('booking-management.store')->with('success', 'Booking Created Successfully');
        } else {
            $guest = Guests::create(
                [
                    'name' => $data['name'],
                    'surname' => $data['surname'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'country' => $data['country'],
                    'city' => $data['city'],
                ]
            );
            $data['guest_id'] = $guest->id;
            $existingBooking = Bookings::where('listing_id', $data['apartment_id'])
                ->where('booking_date_start', $startDate)
                ->where('booking_date_end', $endDate)
                ->first();

            if ($existingBooking) {
                return redirect()->back()->with('error', 'A booking already exists for this apartment at the selected dates.');
            } else {
                $booking = Bookings::create($data);
            }
            MobileNotification::create([
                'listing_id' => $listing->listing_id,
                'booking_id' => $booking->id,
                'ota_type' => 'livedin',
                'type' => 'booking',
                'price' => $data['total_price'],
                'notification_label' => $guest->name . ' ' . $guest->surname . ' has booked ' . $listing_name,
                'status' => 'unread',
                'booking_dates' => $startDate . ' to ' . $endDate,
                'listing_name' => $listing_name,
            ]);
            if ($request->hasFile('image') && count($request->file('image')) > 0) {
                foreach ($request->file('image') as $images) {
                    $fileName = time() . '_' . $images->getClientOriginalName();
                    $filePath = $images->storeAs('booking_images', $fileName, 'public');
                    BookingImages::create(
                        [
                            'booking_id' => $booking->id,
                            'image' => $filePath,
                        ]
                    );
                }
            }
            if ($request->hasFile('payment_references') && count($request->file('payment_references')) > 0) {
                foreach ($request->file('payment_references') as $images) {
                    $fileName = time() . '_' . $images->getClientOriginalName();
                    $filePath = $images->storeAs('reference_images', $fileName, 'public');
                    BookingReferences::create(
                        [
                            'booking_id' => $booking->id,
                            'booking_type' => 'livedin',
                            'image' => $filePath,
                        ]
                    );
                }
            }
            $endDate = Carbon::parse($endDate);

            $endDate = $endDate->subDay();
            $endDate = $endDate->toDateString();
            $this->blockAvailability($data['apartment_id'], $startDate, $endDate);


            try {

                $procedurparameter = [
                    'p_listing_id' => $booking->listing_id,
                    'P_Booking_Id' => $booking->id
                ];

                $result = $this->storeProcedureService
                    ->name('sp_check_triggers_and_bookings_livedin_V4')
                    ->InParameters([
                        'p_listing_id',
                        'P_Booking_Id'

                    ])
                    ->OutParameters(['return_value', 'return_message', 'return_host_id', 'return_vendor_id'])
                    ->data($procedurparameter)
                    ->execute();

                $response = $this->storeProcedureService->response();

                if ($response['response']['return_host_id'] > 0) {

                    $host_id = $response['response']['return_host_id'];
                    $vendor_id = $response['response']['return_vendor_id'];

                    $vendor = Vendors::find($vendor_id);
                    $userDB = User::find($host_id);


                    if ($vendor != null) {

                        if (!empty($vendor->phone) && $vendor->phone != '0') {

                            $vendorPhone = $vendor->country_code . $vendor->phone;
                            $this->sendMessage($vendorPhone, "New Task Created", "sms");

                        } else {

                            logger("Message not sent to vendor: Invalid phone number for vendor ID {$vendor->id}");
                        }

                    }
                    if (!empty($userDB->phone) && $userDB->phone != '0') {


                        $this->sendMessage($userPhone, "New Task Created", "whatsapp");

                    } else {

                        logger("Message not sent to user: Invalid phone number for user ID {$vendor->id}");
                    }

                }

                logger("*************************LivedIn Automated Task Executed *************************");

            } catch (\Exception $e) {

                logger("*************************LivedIn Automated Task Error *************************");
            }


            return redirect()->route('booking-management.store')->with('success', 'Booking Created Successfully');
        }

    }

    public function importBookings(Request $request)
    {
        // Ensure that the 'file' field is present, is a file and is either in xlsx or xls format
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        $file = $request->file('file');
        $arrays = Excel::toArray(new BookingImport, $file);

        foreach ($arrays as $array) {
            foreach ($array as $key => $item) {
                if ($key === 0) {
                    continue;
                }
                $guest = Guests::where('phone', $item[3])->first();
                if ($guest) {
                    $booking = [
                        'name' => $item[0] . ' ' . $item[1],
                        'email' => $item[2],
                        'phone' => $item[3],
                        'country' => $item[4],
                        'city' => $item[5],
                        'cnic_passport' => $item[6],
                        'adult' => $item[7],
                        'children' => $item[8],
                        'rooms' => $item[9],
                        'rating' => $item[10],
                        'purpose_of_call' => $item[11],
                        'reason' => $item[12],
                        'booking_notes' => $item[13],
                        'custom_discount' => $item[14],
                        'payment_method' => $item[15],
                        'ota_name' => $item[16],
                        'listing_id' => $item[17],
                        'booking_sources' => $item[18],
                        'booking_date_start' => Date::excelToDateTimeObject($item[19]),
                        'booking_date_end' => Date::excelToDateTimeObject($item[20]),
                        'service_fee' => $item[21],
                        'cleaning_fee' => $item[22],
                        'per_night_price' => $item[23],
                        'total_price' => $item[24],
                        'ota_commission' => $item[25],
                        'created_at' => Date::excelToDateTimeObject($item[26]),
                        'guest_id' => $guest->id
                    ];
                    Bookings::create($booking);
                } else {
                    $guest = Guests::create(
                        [
                            'name' => $item[0],
                            'surname' => $item[1],
                            'email' => $item[2],
                            'phone' => $item[3],
                            'country' => $item[4],
                            'city' => $item[5],
                        ]
                    );
                    $booking = [
                        'name' => $item[0] . ' ' . $item[1],
                        'email' => $item[2],
                        'phone' => $item[3],
                        'country' => $item[4],
                        'city' => $item[5],
                        'cnic_passport' => $item[6],
                        'adult' => $item[7],
                        'children' => $item[8],
                        'rooms' => $item[9],
                        'rating' => $item[10],
                        'purpose_of_call' => $item[11],
                        'reason' => $item[12],
                        'booking_notes' => $item[13],
                        'custom_discount' => $item[14],
                        'payment_method' => $item[15],
                        'ota_name' => $item[16],
                        'listing_id' => $item[17],
                        'booking_sources' => $item[18],
                        'booking_date_start' => Date::excelToDateTimeObject($item[19]),
                        'booking_date_end' => Date::excelToDateTimeObject($item[20]),
                        'service_fee' => $item[21],
                        'cleaning_fee' => $item[22],
                        'per_night_price' => $item[23],
                        'total_price' => $item[24],
                        'ota_commission' => $item[25],
                        'created_at' => Date::excelToDateTimeObject($item[26]),
                        'guest_id' => $guest->id
                    ];
                    Bookings::create($booking);
                }


            }
        }
        return redirect()->back();
    }



    public function edit(Bookings $booking_management)
    {
        //        dd($booking_management);
        $role = Auth::user()->role_id;

        $guest = Guests::where('id', $booking_management->guest_id)->first();
        //        dd($guest);
        $booking_images = BookingImages::where('booking_id', $booking_management->id)->get();
        $booking_payment_images = BookingReferences::where('booking_id', $booking_management->id)->where('booking_type', 'livedin')->get();

        // dd($booking_payment_images);
        $users = User::all();
        // $listings = Listings::all();
        $countries = DB::select("Select * from countries");
        //        dd($booking_images);

        $data = [];
        $listings = Listing::where('is_sync', 'sync_all')->get();
        foreach ($listings as $listing) {
            // if (is_null($listing->channel) || !is_null($listing->channel->connection_type)) {
            //     continue;
            // }
            $data[] = $listing;
        }

        return view('Admin.bookings-management.edit', ['role' => $role, 'listings' => $data, 'users' => $users, 'countries' => $countries, 'booking' => $booking_management, 'booking_date_start' => $booking_management->booking_date_start, 'guest' => $guest, 'booking_images' => $booking_images, 'booking_payment_images' => $booking_payment_images]);
    }

    public function updateAvailability($startDate, $endDate, $listing_id, $avail)
    {
        // dd($startDate, $endDate, (int) $listing_id, $avail);
        $today = Carbon::now();
        // dd( $today->toDateString());
        if ($startDate < $today->toDateString()) {
            // dd('before');
            $startDate = $today->toDateString();
        }

        $rate_plan = $property = $room_type = null;

        $listing = Listings::where('id', (int) $listing_id)->first();
        if (!is_null($listing)) {
            $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
        }
        if (!is_null($rate_plan)) {
            $property = Properties::where('id', $rate_plan->property_id)->first();
        }
        if (!is_null($property) && !is_null($listing)) {
            $room_type = RoomType::where('listing_id', $listing->listing_id)->first();
        }

        if (!is_null($listing) && !is_null($rate_plan) && !is_null($property) && !is_null($room_type)) {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                        "values" => [
                            [
                                //                        'date' => '2024-11-21',
                                "date_from" => $startDate,
                                "date_to" => $endDate,
                                "property_id" => $property->ch_property_id,
                                "room_type_id" => $room_type->ch_room_type_id,
                                "availability" => (int) $avail,
                            ],
                        ]
                    ]);
            if ($response->successful()) {

                $availability = $response->json();

                Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                    ->update(
                        ['availability' => (int) $avail]
                    );
            } else {
                $error = $response->body();
            }
        }
    }

    public function updateOtaAvailability($startDate, $endDate, $listing_id, $avail)
    {
        // dd($startDate, $endDate, (int) $listing_id, $avail);
        $listing = Listings::where('listing_id', (int) $listing_id)->first();
        $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();
        $property = Properties::where('id', $rate_plan->property_id)->first();
        $room_type = RoomType::where('listing_id', $listing->listing_id)->first();

        // dd($room_type->ch_room_type_id,$property);

        $response = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    "values" => [
                        [
                            //                        'date' => '2024-11-21',
                            "date_from" => $startDate,
                            "date_to" => $endDate,
                            "property_id" => $property->ch_property_id,
                            "room_type_id" => $room_type->ch_room_type_id,
                            "availability" => (int) $avail,
                        ],
                    ]
                ]);
        if ($response->successful()) {

            $availability = $response->json();
            // dd( $availability);
            Calender::where('listing_id', $listing->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
                ->update(
                    ['availability' => (int) $avail]
                );
        } else {
            $error = $response->body();
        }
    }

    public function addBookingPaymentReconciliation($request, $booking_management, $type)
    {
        if (isset($request->reference_number) && isset($request->reference_status)) {
            if ($request->hasFile('image') && count($request->file('image')) > 0) {
                foreach ($request->file('image') as $index => $images) {
                    $fileName = time() . '_' . $images->getClientOriginalName();
                    $filePath = $images->storeAs('booking_images', $fileName, 'public');

                    // Save image
                    $imageModel = BookingImages::create([
                        'booking_id' => $booking_management->id,
                        'image' => $filePath,
                    ]);

                    // Save reference if data is available
                    $refId = $request->reference_number[$index] ?? null;
                    $refStatus = $request->reference_status[$index] ?? null;


                    BookingReferences::create([
                        'booking_id' => $booking_management->id,
                        'image_id' => $imageModel->id,
                        'reference_id' => $refId,
                        'reference_status' => $refStatus,
                        'booking_type' => $type,
                    ]);
                }


            }
        }
        $booking_management->update([
            'amount_received' => $request->amount_received,
        ]);

    }

    public function update(Request $request, Bookings $booking_management)
    {
        if (isset($request->amount_received)) {
            $booking_management->update([
                'amount_received' => $request->amount_received,
                'forex_adjustement' => $request->forex_adjustement,
                'reference_numbers' => $request->reference_numbers,
                'payment_status' => $request->payment_status,
            ]);
            return redirect()->back()->with('success', 'Booking updated successfully');
        }
        $request['listing_id'] = $request->apartment_id;
        $dateRange = $request->daterange;
        [$startDate, $endDate] = explode(' - ', $dateRange);
        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        // }
        //   $endDate = Carbon::parse($endDate);

        //   $endDate = $endDate->subDay();

        // Almosafer Calendar Update
        $MagaRentalController = new MagaRentalController();

        $booking_management_listing_id = $booking_management->listing_id;

        if ($request['booking_sources'] == "almosafer" || $booking_management->booking_sources == "almosafer") {

            $listingRelation = ListingRelation::where('listing_id_other_ota', $booking_management_listing_id)
                ->where('listing_type', 'Almosafer')
                ->first();

            if (!is_null($listingRelation)) {
                $booking_management_listing_id = $listingRelation->listing_id_airbnb;
            } else {
                $booking_management_listing_id = $request->apartment_id;
            }
        }

        $almsr_listing = Listings::where('id', $booking_management_listing_id)->first();
        $almsr_listing_id = $almsr_listing->listing_id ?? 0;


        $actEndDate = '';
        $request['booking_date_start'] = $startDate;
        $request['booking_date_end'] = $endDate;
        if ((int) $request->apartment_id != $booking_management_listing_id) {
            $endDate = Carbon::parse($endDate);
            $endDate = $endDate->subDay();

            $actEndDate = $endDate;

            $this->updateAvailability($startDate, $endDate->toDateString(), $request->apartment_id, 0);
            $this->updateAvailability($startDate, $endDate->toDateString(), $booking_management_listing_id, 1);

            // Almosafer Old Property set available
            $almsfr_respn = $MagaRentalController->almosafer_block_calendar($almsr_listing_id, $startDate, $endDate->toDateString(), 1);

            // Almosafer New property set unavailable
            $new_almsr_airbnb_listing = Listings::where('id', $request->apartment_id)->first();
            if (!is_null($new_almsr_airbnb_listing)) {
                $new_almsfr_respn = $MagaRentalController->almosafer_block_calendar($new_almsr_airbnb_listing->listing_id, $startDate, $endDate->toDateString(), 0);
            }

        }
        if ($startDate != $booking_management->booking_date_start || $endDate != $booking_management->booking_date_end) {
            $endDateDb = $booking_management->booking_date_end;
            $endDateDb = Carbon::parse($endDateDb);
            $endDateDb = $endDateDb->subDay();
            $endDate = Carbon::parse($endDate);
            $endDate = $endDate->subDay();

            $actEndDate = $endDate;

            $this->updateAvailability($booking_management->booking_date_start, $endDateDb->toDateString(), $booking_management_listing_id, 1);
            $this->updateAvailability($startDate, $endDate->toDateString(), $request->apartment_id, 0);

            // Almosafer Old Property set available
            $almsfr_respn = $MagaRentalController->almosafer_block_calendar($almsr_listing_id, $booking_management->booking_date_start, $endDateDb->toDateString(), 1);

            // Almosafer second property set unavailable
            $new_almsr_airbnb_listing = Listings::where('id', $request->apartment_id)->first();
            if (!is_null($new_almsr_airbnb_listing)) {
                $new_almsfr_respn = $MagaRentalController->almosafer_block_calendar($new_almsr_airbnb_listing->listing_id, $startDate, $endDate->toDateString(), 0);
            }

        }

        // $firebase_service = app(FirebaseService::class);

        // $this->sendNotification($listing_id, $response['data']['attributes'], "booking_modification", 0);


        if ($request->booking_status == 'cancelled') {

            if (empty($actEndDate)) {
                $endDate = Carbon::parse($endDate);
                $endDate = $endDate->subDay();
                $actEndDate = $endDate;
            }

            if ((int) $request->apartment_id != $booking_management_listing_id) {
                $this->updateAvailability($startDate, $actEndDate, $booking_management_listing_id, 1);

                // Almosafer Old Property set available
                $almsfr_respn = $MagaRentalController->almosafer_block_calendar($almsr_listing_id, $startDate, $actEndDate, 1);
            }

            $this->updateAvailability($startDate, $actEndDate, $request->apartment_id, 1);

            // Almosafer second property set unavailable
            $new_almsr_airbnb_listing = Listings::where('id', $request->apartment_id)->first();
            if (!is_null($new_almsr_airbnb_listing)) {
                $new_almsfr_respn = $MagaRentalController->almosafer_block_calendar($new_almsr_airbnb_listing->listing_id, $startDate, $actEndDate, 1);
            }

            try {

                $procedurparameter = [
                    'p_listing_id' => $request->apartment_id,
                    'P_Booking_Id' => $booking_management->id
                ];

                $result = $this->storeProcedureService
                    ->name('sp_trigger_task_delete_livedin_V2')
                    ->InParameters([
                        'p_listing_id',
                        'P_Booking_Id'

                    ])
                    ->OutParameters(['return_value', 'return_message', 'return_host_id', 'return_vendor_id'])
                    ->data($procedurparameter)
                    ->execute();

                $response = $this->storeProcedureService->response();

                //dd($response);  

                logger("*************************Delete Task LivedIn Automated Task Executed *************************");

            } catch (\Exception $e) {

                logger("*************************Delete Task LivedIn Automated Task Error *************************");
            }


        }
        $referenceNumbers = $request->input('reference_number');
        $referenceStatuses = $request->input('reference_status');
        $isSelected = $request->input('is_selected', []); // Optional checkboxes
        if ($request->hasFile('image') && count($request->file('image')) > 0) {
            foreach ($request->file('image') as $index => $images) {
                $fileName = time() . '_' . $images->getClientOriginalName();
                $filePath = $images->storeAs('booking_images', $fileName, 'public');

                // Save image
                $imageModel = BookingImages::create([
                    'booking_id' => $booking_management->id,
                    'image' => $filePath,
                ]);

            }
        }

        if ($request->hasFile('payment_references') && count($request->file('payment_references')) > 0) {
            foreach ($request->file('payment_references') as $images) {
                $fileName = time() . '_' . $images->getClientOriginalName();
                $filePath = $images->storeAs('reference_images', $fileName, 'public');
                BookingReferences::create(
                    [
                        'booking_id' => $booking_management->id,
                        'booking_type' => 'livedin',
                        'image' => $filePath,
                    ]
                );
            }
        }

        $bk_listing_id = $booking_management_listing_id;

        $updated_by = Auth::user()->id;
        $request->merge(['updated_by' => $updated_by]);
        $booking_management->update($request->all());

        $guest = Guests::find($request->guest_id);

        if ($guest) {

            $guest->update([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,

            ]);

        }

        // dd($request->guest_id);

        if ($request->booking_status != 'cancelled') {

            $numberOfGuests = $request->adult + $request->children;

            $message = $request->name . " has made changes to their booking: the new dates are " . $request['booking_date_start'] . " to " . $request['booking_date_end'] . " with " . $numberOfGuests . " guests. Review the updated details!";

            $listing = Listing::find($bk_listing_id);
            $user_ids_arr = !empty($listing->user_id) ? json_decode($listing->user_id) : [];

            if (!empty($user_ids_arr)) {
                foreach ($user_ids_arr as $user_id) {
                    if (!empty($user_id)) {
                        sendLiteNotification($user_id, "Booking Modifications", $message, $booking_management->id, 'livedin_bk_detail');
                    }
                }
            }
        }

        return redirect()->route('booking-management.index');
    }

    public function bookingImageDelete($id)
    {
        $id = (int) $id;

        // Fetch image from booking_images table
        $imageDB = DB::table('booking_images')->where('id', $id)->first();

        if ($imageDB) {
            $imagePath = public_path('storage/' . $imageDB->image);

            // Delete physical file if exists
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Delete related references

            // Delete the image record
            DB::table('booking_images')->where('id', $id)->delete();
        }

        return redirect()->back()->with('success', 'Image and reference deleted successfully.');
    }
    public function ReferenceImageDelete($id)
    {
        $id = (int) $id;

        // Fetch image from booking_images table
        $imageDB = DB::table('booking_references')->where('id', $id)->first();

        if ($imageDB) {
            $imagePath = public_path('storage/' . $imageDB->image);

            // Delete physical file if exists
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Delete related references
            DB::table('booking_references')->where('id', $id)->delete();

            // Delete the image record
        }

        return redirect()->back()->with('success', 'Image and reference deleted successfully.');
    }

    public function checkinpdf(Request $request)
    {

        $booking_id = $request->query('booking_id');
        $booking = Bookings::find($booking_id);

        $listing = Listing::where('id', $booking->listing_id)->first();
        if (is_null($listing)) {
            return false;
        }

        $emailData = $booking;

        $emailData['listing_id'] = $listing->listing_id;
        $emailData['be_listing_name'] = substr($listing->be_listing_name, 0, 8) . "...";
        $emailData['is_self_check_in'] = $listing->is_self_check_in;
        $emailData['district'] = $listing->district;
        $emailData['city_name'] = $listing->city_name;
        $emailData['google_map'] = $listing->google_map;
        $emailData['discounts'] = $listing->discounts;
        $emailData['tax'] = $listing->tax;

        $emailData['view_property_link'] = "https://booking.livedin.co/property_detail?listing_id=" . $listing->listing_id;

        $start = Carbon::parse($booking->booking_date_start);
        $end = Carbon::parse($booking->booking_date_end);

        $total_nights = $start->diffInDays($end);

        $emailData['checkin_date'] = $start->format('jS') . ' ' . $start->format('M Y');
        $emailData['checkout_date'] = $end->format('jS') . ' ' . $end->format('M Y');

        $emailData['total_nights'] = $total_nights;
        $emailData['total_nights_txt'] = $total_nights == 1 ? "1 night" : $total_nights . " nights";

        $pdfData = json_decode(json_encode($emailData), true);

        return view('mail.checkin_pdf', compact('emailData'));

    }

    public function checkoutpdf(Request $request)
    {

        $booking_id = $request->query('booking_id');


        $booking = Bookings::find($booking_id);

        $listing = Listing::where('id', $booking->listing_id)->first();
        if (is_null($listing)) {
            return false;
        }

        $emailData = $booking;

        $emailData['listing_id'] = $listing->listing_id;
        $emailData['be_listing_name'] = substr($listing->be_listing_name, 0, 8) . "...";
        $emailData['is_self_check_in'] = $listing->is_self_check_in;
        $emailData['district'] = $listing->district;
        $emailData['city_name'] = $listing->city_name;
        $emailData['google_map'] = $listing->google_map;
        $emailData['discounts'] = $listing->discounts;
        $emailData['tax'] = $listing->tax;

        $emailData['view_property_link'] = "https://booking.livedin.co/property_detail?listing_id=" . $listing->listing_id;

        $start = Carbon::parse($booking->booking_date_start);
        $end = Carbon::parse($booking->booking_date_end);

        $total_nights = $start->diffInDays($end);

        $emailData['checkin_date'] = $start->format('jS') . ' ' . $start->format('M Y');
        $emailData['checkout_date'] = $end->format('jS') . ' ' . $end->format('M Y');

        $emailData['total_nights'] = $total_nights;
        $emailData['total_nights_txt'] = $total_nights == 1 ? "1 night" : $total_nights . " nights";



        return view('mail.checkout_pdf', compact('emailData'));

    }
}

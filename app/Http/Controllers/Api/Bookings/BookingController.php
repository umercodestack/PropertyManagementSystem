<?php

namespace App\Http\Controllers\Api\Bookings;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\PropertyResource;
use App\Models\BookingOtasDetails;
use App\Models\BookingReference;
use App\Models\Bookings;
use App\Models\Channels;
use App\Models\Calender;
use App\Models\HostType;
use App\Models\Properties;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Admin\BookingManagement\BookingManagementController;
use App\Models\Guests;
use App\Services\MixpanelService;
use App\Models\Listing;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Mail\BookingCreationEmail;
use App\Utilities\UserUtility;
use App\Services\StoreProcedureService;
use App\Models\Vendors;
use Twilio\Rest\Client;
use App\Models\ListingRelation;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Http\Controllers\Admin\MagaRental\MagaRentalController;

class BookingController extends Controller
{

    protected $mixpanelService;
    protected $client;
    protected $storeProcedureService = false;

    public function __construct(MixpanelService $mixpanelService, StoreProcedureService $storeProcedureService)
    {
        $this->mixpanelService = $mixpanelService;
        $this->storeProcedureService = $storeProcedureService;


        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->client = new Client($sid, $token);
    }
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $bookings = Bookings::all();
        return BookingResource::collection($bookings);
    }

    public function fetchAllBooking(Request $request)
    {
        $user = Auth::user();

        // Get listing IDs owned by user
        $listing_id = Listing::whereJsonContains('user_id', strval($user->id))->pluck('id');

        $listing_ids = implode(',', $listing_id->toArray());

        // Get pagination params
        $limit = (int) $request->input('limit', 10); // default 10
        $page = (int) $request->input('page', 1);
        $offset = ($page - 1) * $limit;

        // Get total count separately (if needed for accurate pagination)
        $totalCountResult = \DB::select("
        SELECT COUNT(*) AS total_count
        FROM (
            SELECT bod.id
            FROM booking_otas_details AS bod
            JOIN listings AS lis ON lis.listing_id = bod.listing_id
            WHERE lis.id IN ($listing_ids)

            UNION ALL

            SELECT b.id
            FROM bookings AS b
            JOIN listings AS lis ON lis.id = b.listing_id
            WHERE lis.id IN ($listing_ids)
        ) AS total_booking_count
    ");

        $totalItems = $totalCountResult[0]->total_count ?? 0;
        $totalPages = $limit > 0 ? ceil($totalItems / $limit) : 1;

        // Call stored procedure with pagination
        $data = \DB::select("CALL sp_get_all_combined_bookings(?, ?, ?)", [
            $limit,
            $offset,
            $listing_ids,
        ]);




        // Return response in mobile-friendly format
        return response()->json([
            'data' => $data,
            'page' => $page,
            'limit' => $limit,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
        ]);
    }
    /**
     * @param Request $request
     * @return BookingResource|JsonResponse
     */
    // public function store(Request $request): JsonResponse|BookingResource
    // {
    //     $validator = Validator::make($request->all(), [
    //         'guests' => 'required',
    //         'apartment_id' => 'required',
    //         'booking_date_start' => 'required',
    //         'booking_date_end' => 'required',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['error' => $validator->errors()], 400);
    //     }
    //     $booking = Bookings::create($request->all());
    //     return new BookingResource($booking);
    // }


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
        $validator = Validator::make($request->all(), [
            'listing_id' => 'required',
            'name' => 'required',
            'phone' => 'required',
            'booking_date_start' => 'required',
            'booking_date_end' => 'required',
            'per_night_price' => 'required|integer|min:0',
            'booking_type' => 'required|in:host,livedin',
            'payment_references.*' => 'nullable|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $bookingDateStart = Carbon::parse($request->input('booking_date_start'));
        $bookingDateEnd = Carbon::parse($request->input('booking_date_end'));

        $booking_exists = Bookings::where('listing_id', $request->listing_id)
            ->where('booking_date_start', $bookingDateStart)
            ->where('booking_date_end', $bookingDateEnd)
            ->first();

        if (!is_null($booking_exists)) {
            return response()->json(['success' => 0, 'error' => 'Booking already exists, Please select different dates']);
        }

        $updatedBookingDateEnd = $bookingDateEnd->subDay();

        $listing = Listing::where('id', $request->listing_id)->first();

        if (is_null($listing)) {
            return response()->json(['success' => 0, 'error' => 'Listing not found']);
        }

        $calenders = Calender::where('listing_id', $listing->listing_id)
            ->whereBetween('calender_date', [$bookingDateStart, $updatedBookingDateEnd])
            ->get();

        $is_calendar_blocked = false;
        foreach ($calenders as $calender) {
            if ($calender->availability == 0 || $calender->is_blocked == 1 || $calender->is_lock == 1) {
                $is_calendar_blocked = true;
                break;
            }
        }

        if ($is_calendar_blocked) {
            return response()->json(['success' => 0, 'error' => 'Booking already exists, Please select different dates']);
        }


        $numberOfNights = $bookingDateStart->diffInDays($request->input('booking_date_end'));

        $totalAmount = $request->per_night_price * $numberOfNights;

        if ($request->cleaning_fee > 0) {
            $totalAmount += $request->cleaning_fee;
        }

        if ($request->custom_discount > 0) {
            $totalAmount -= $request->custom_discount;
        }

        $request['total_price'] = $totalAmount;
        $request['booking_sources'] = "host_booking";
        $request['host_id'] = $request->host_id ?? auth()->user()->id;

        $guest = Guests::where('name', $request->name)->first();
        if (!$guest) {
            $guest = Guests::create([
                'name' => $request['name'],
                'phone' => $request['phone'],
                'city' => $request['city'] ?? null,
                'country' => $request['country'] ?? null,
            ]);
        }
        $request['guest_id'] = $guest->id;
        $dataB = $request->all();
        $dataB['payment_method'] = 'cod';



        $dataB['booking_type'] = $request->booking_type;


        $booking = Bookings::create($dataB);


        $filePaths = [];

        // Check if booking_type is 'livedin'
        if ($request->booking_type === 'livedin') {

            if ($request->hasFile('payment_references')) {
                $files = $request->file('payment_references');

                // Ensure we always have an array
                if (!is_array($files)) {
                    $files = [$files];
                }

                foreach ($files as $file) {
                    if ($file->isValid()) {
                        $fileName = time() . '' . preg_replace('/\s+/', '', $file->getClientOriginalName());
                        $filePath = $file->storeAs('reference_images', $fileName, 'public');

                        BookingReference::create([
                            'booking_id' => $booking->id,
                            'booking_type' => $request->booking_type,
                            'image' => $filePath,
                        ]);

                        $filePaths[] = url('storage/' . $filePath);
                    }
                }


                $booking->update(['payment_status' => 'payment_unverified']);

            } else {
                $booking->update(['payment_status' => 'payment_unverified']);

            }
        }







        try {

            // dd($booking);
            //Block Availibility

            $bookingController = new BookingManagementController(app(StoreProcedureService::class));
            $bookingController->blockAvailability($request->listing_id, $request->booking_date_start, $updatedBookingDateEnd);

            // Almosafer Calendar Update
            $MagaRentalController = new MagaRentalController();
            $almsfr_respn = $MagaRentalController->almosafer_block_calendar($listing->listing_id, $request->booking_date_start, $updatedBookingDateEnd, 0);

            $emailData = $booking;
            $listing = Listing::where('id', $emailData['listing_id'])->first();
            $listing_json = json_decode($listing->listing_json);
            $emailData['host_name'] = auth()->user()->name . ' ' . auth()->user()->surname;
            $emailData['listing_title'] = $listing_json->title;
            // dd($emailData);
            Mail::to(['adnan.anwar@livedin.co', 'support@livedin.co'])->send(new BookingCreationEmail($emailData));

            $user = auth()->user();
            if (!empty($user->role_id) && $user->role_id === 2) {

                try {

                    $userUtility = new UserUtility();
                    $location = $userUtility->getUserGeolocation();
                    $listingJson = json_decode($listing->listing_json, true);

                    $this->mixpanelService->trackEvent('Direct Booking Created', [
                        'distinct_id' => $user->id,
                        'first_name' => $user->name,
                        'last_name' => $user->surname,
                        'email' => $user->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $user->country,
                        'db_city' => $user->city,
                        'host_type' => $user->hostType->module_name,
                        'LP_NAME' => $listingJson['title'] ?? 'Unnamed Listing'

                    ]);

                    $this->mixpanelService->setPeopleProperties($user->id, [
                        '$first_name' => $user->name,
                        '$last_name' => $user->surname,
                        '$email' => $user->email,
                        '$country' => $location['country'],
                        '$region' => $location['region'],
                        '$city' => $location['city'],
                        '$os' => $userUtility->getUserOS(), // Add OS here
                        'latitude' => $location['latitude'],
                        'longitude' => $location['longitude'],
                        'timezone' => $location['timezone'],
                        'ip_address' => $location['ip'],
                        'db_country' => $user->country,
                        'db_city' => $user->city,
                        'host_type' => $user->hostType->module_name,
                        'LP_NAME' => $listingJson['title'] ?? 'Unnamed Listing'

                    ]);


                } catch (\Exception $e) {


                }
            }


        } catch (TransportException $e) {
            \Log::error('Email dispatch failed: ' . $e->getMessage());
            $user['email_sent'] = false;
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

            logger("************************* LivedIn Automated Task Error *************************");
        }


        return (new BookingResource($booking))->additional([
            'paths' => $filePaths
        ]);
    }

    public function deleteBookingReference($id)
    {
        $reference = BookingReference::find($id);

        if (!$reference) {
            return response()->json([
                'success' => false,
                'message' => 'Reference not found',
            ], 404);
        }

        if ($reference->image && \Storage::disk('public')->exists($reference->image)) {
            \Storage::disk('public')->delete($reference->image);
        }

        $reference->delete();

        return response()->json([
            'success' => true,
            'message' => 'Reference deleted successfully',
        ]);
    }
    public function uploadPaymentReferences(Request $request, $id)
    {
        // echo $id;
        $booking = Bookings::find($id);

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }


        if ($booking->booking_type !== 'livedin') {
            return response()->json(['error' => 'Payment references can only be uploaded for livedin bookings'], 400);
        }

        $validator = Validator::make($request->all(), [
            'payment_references.*' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $files = $request->file('payment_references');

        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if ($file->isValid()) {
                $fileName = time() . '' . preg_replace('/\s+/', '', $file->getClientOriginalName());
                $filePath = $file->storeAs('reference_images', $fileName, 'public');


                BookingReference::create([
                    'booking_id' => $booking->id,
                    'booking_type' => $booking->booking_type,
                    'image' => $filePath,
                ]);


            }
        }

        $allReferences = BookingReference::where('booking_id', $booking->id)
            ->where('booking_type', $booking->booking_type)
            ->pluck('image')
            ->map(fn($img) => url('storage/' . $img));

        return response()->json([
            'success' => true,
            'message' => 'Payment reference(s) uploaded successfully',
            'paths' => $allReferences,
            'payment_status' => $booking->payment_status
        ]);
    }





    /**
     * @param Bookings $booking
     * @return BookingResource
     */
    public function show(Bookings $booking): BookingResource
    {
        return new BookingResource($booking);
    }

    /**
     * @param $date
     * @return AnonymousResourceCollection
     */
    public function getBookingByDate($date): AnonymousResourceCollection
    {
        $bookings = Bookings::where('booking_date_start', $date)->get();
        return BookingResource::collection($bookings);
    }

    public function getBookingByApartmentId(User $user, Request $request)
    {
        $listings = Listing::all();
        $listing_arr = array();
        $booking_arr = array();
        foreach ($listings as $item) {
            $users = json_decode($item['user_id']);
            if (in_array($user->id, $users)) {
                array_push($listing_arr, $item);
            }
        }

        foreach ($listing_arr as $item) {
            $listing_json = json_decode($item->listing_json);
            // dd($listing_json->title);

            $todayDate = Carbon::now()->toDateString();
            if (isset($request->status) && $request->status === 'checked_in') {
                $bookings = Bookings::where('listing_id', $item->id)->where('booking_date_start', $todayDate)->get();
                // dd($todayDate);
            } else if (isset($request->status) && $request->status === 'checked_out') {
                $bookings = Bookings::where('listing_id', $item->id)->where('booking_date_end', '<=', $todayDate)->get();
            } else if (isset($request->status) && $request->status === 'upcoming') {
                $bookings = Bookings::where('listing_id', $item->id)->where('booking_date_start', '>', $todayDate)->get();
            } else if (isset($request->status) && $request->status === 'pending') {
                $bookings = [];
            } else {
                $bookings = Bookings::where('listing_id', $item->id)->get();
            }

            if ($bookings) {
                foreach ($bookings as $booking) {
                    $booking['listing_title'] = $listing_json->title;
                    array_push($booking_arr, $booking);
                }
            }
        }
        // dd($booking_arr);


        return response()->json($booking_arr);
    }
    public function getLivedInBookingByID($id)
    {
        $todayDate = Carbon::now()->toDateString();
        $booking = Bookings::with('references')->where('id', $id)->first();
        // dd($todayDate);
        if ($todayDate >= $booking->booking_date_start) {
            $booking['cancellable'] = false;
            $booking['trip_status'] = 'Trip Completed';
        } else if ($todayDate < $booking->booking_date_start) {
            $booking['cancellable'] = true;
            $booking['trip_status'] = 'Upcoming Trip';
        } else {
            $booking['cancellable'] = true;
            $booking['trip_status'] = 'Currently Hosting';
        }
        if ($booking->booking_status == 'cancelled') {
            $booking['trip_status'] = 'Trip Cancelled';
        }
        $listing = Listing::where('id', $booking->listing_id)->first();
        $listing_json = json_decode($listing->listing_json);
        $booking['host_share'] = round($booking->total_price - ($booking->total_price * $listing->commission_value / 100) - $booking->custom_discount - $booking->ota_commission);
        $booking['booking_status'] = 'confirmed';
        $booking['payment_status'] = $booking->payment_status;
        $booking['listing_title'] = $listing_json->title;
        $booking['livedin_share'] = round($booking->total_price * $listing->commission_value / 100);
        //  Append images
        $booking['images'] = $booking->references->map(function ($ref) {
            return [
                'id' => $ref->id,
                'uri' => url('storage/' . $ref->image),
            ];
        })->values();
        unset($booking['references']);

        // dd($booking->total_price);


        if (!empty($user->role_id) && $user->role_id === 2) {

            try {


                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Booking Details Opened', [
                    'distinct_id' => $user->id,
                    'first_name' => $user->name,
                    'last_name' => $user->surname,
                    'email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city,
                    'host_type' => $user->hostType->module_name,
                ]);


                $this->mixpanelService->setPeopleProperties($user->id, [
                    '$first_name' => $user->name,
                    '$last_name' => $userDB->surname,
                    '$email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city,
                    'host_type' => $user->hostType->module_name,

                ]);

            } catch (\Exception $e) {


            }
        }
        return response()->json($booking);
    }

    /**
     * @param Request $request
     * @param Bookings $booking
     * @return BookingResource|JsonResponse
     */
    public function update(Request $request, Bookings $booking): JsonResponse|BookingResource
    {
        $validator = Validator::make($request->all(), [
            'booking_date_start' => 'required',
            'booking_date_end' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $booking->update($request->all());
        return new BookingResource($booking);
    }

    /**
     * @param Bookings $booking
     * @return BookingResource
     */
    public function destroy(Bookings $booking): BookingResource
    {
        $booking->delete();
        return new BookingResource($booking);
    }

    public function fetchPropertyByHostID(User $user)
    {
        $property = Properties::where('user_id', $user->id)->first();
        return new PropertyResource($property);
    }
    public function getBookingByPropertyID(Request $request, $channel_id)
    {
        $listings = Listing::all();
        $listing_arr = array();
        $booking_arr = array();
        $user_id = Auth::user()->id;
        $userDB = User::whereId($user_id)->first();
        foreach ($listings as $item) {
            $users = json_decode($item['user_id']);
            $listingRelation = ListingRelation::where('listing_id_airbnb', $item->id)->pluck('listing_id_other_ota')->toArray();
            if (in_array($item->id, $listingRelation)) {
                if (in_array($user_id, $users)) {
                    array_push($listing_arr, $item);
                }
                // array_push($listing_arr, $item);
            }

            if (in_array($user_id, $users)) {
                array_push($listing_arr, $item);
            }
        }

        $index = 0;
        foreach ($listing_arr as $item) {
            $todayDate = Carbon::now()->toDateString();
            if (isset($request->status) && $request->status === 'checked_in') {
                $bookings = BookingOtasDetails::where('listing_id', $item->listing_id)->where('arrival_date', $todayDate)->get();

                if ($index === 0) {
                    if (!empty($userDB->role_id) && $userDB->role_id === 2) {

                        try {


                            $userUtility = new UserUtility();
                            $location = $userUtility->getUserGeolocation();

                            $this->mixpanelService->trackEvent('Booking Module Checkedin Clicked', [
                                'distinct_id' => $userDB->id,
                                'first_name' => $userDB->name,
                                'last_name' => $userDB->surname,
                                'email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,
                            ]);


                            $this->mixpanelService->setPeopleProperties($userDB->id, [
                                '$first_name' => $userDB->name,
                                '$last_name' => $userDB->surname,
                                '$email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,

                            ]);

                        } catch (\Exception $e) {


                        }
                    }
                }

                // dd($todayDate);
            } else if (isset($request->status) && $request->status === 'checked_out') {
                $bookings = BookingOtasDetails::where('listing_id', $item->listing_id)->where('departure_date', $todayDate)->get();

                if ($index === 0) {
                    if (!empty($userDB->role_id) && $userDB->role_id === 2) {

                        try {


                            $userUtility = new UserUtility();
                            $location = $userUtility->getUserGeolocation();

                            $this->mixpanelService->trackEvent('Booking Module Checkedout Clicked', [
                                'distinct_id' => $userDB->id,
                                'first_name' => $userDB->name,
                                'last_name' => $userDB->surname,
                                'email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,
                            ]);


                            $this->mixpanelService->setPeopleProperties($userDB->id, [
                                '$first_name' => $userDB->name,
                                '$last_name' => $userDB->surname,
                                '$email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,

                            ]);

                        } catch (\Exception $e) {


                        }
                    }
                }

            } else if (isset($request->status) && $request->status === 'upcoming') {
                $bookings = BookingOtasDetails::where('listing_id', $item->listing_id)->where('arrival_date', '>', $todayDate)->get();

                if ($index === 0) {
                    if (!empty($userDB->role_id) && $userDB->role_id === 2) {

                        try {


                            $userUtility = new UserUtility();
                            $location = $userUtility->getUserGeolocation();

                            $this->mixpanelService->trackEvent('Booking Module Upcoming Clicked', [
                                'distinct_id' => $userDB->id,
                                'first_name' => $userDB->name,
                                'last_name' => $userDB->surname,
                                'email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,
                            ]);


                            $this->mixpanelService->setPeopleProperties($userDB->id, [
                                '$first_name' => $userDB->name,
                                '$last_name' => $userDB->surname,
                                '$email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,

                            ]);

                        } catch (\Exception $e) {


                        }
                    }
                }


            } else if (isset($request->status) && $request->status === 'pending') {
                $bookings = [];

                if ($index === 0) {
                    if (!empty($userDB->role_id) && $userDB->role_id === 2) {

                        try {


                            $userUtility = new UserUtility();
                            $location = $userUtility->getUserGeolocation();

                            $this->mixpanelService->trackEvent('Booking Module Pending Clicked', [
                                'distinct_id' => $userDB->id,
                                'first_name' => $userDB->name,
                                'last_name' => $userDB->surname,
                                'email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,
                            ]);


                            $this->mixpanelService->setPeopleProperties($userDB->id, [
                                '$first_name' => $userDB->name,
                                '$last_name' => $userDB->surname,
                                '$email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,

                            ]);

                        } catch (\Exception $e) {


                        }
                    }
                }

            } else {
                $bookings = BookingOtasDetails::where('listing_id', $item->listing_id)->get();
                // dd($bookings);

                if ($index === 0) {
                    if (!empty($userDB->role_id) && $userDB->role_id === 2) {

                        try {


                            $userUtility = new UserUtility();
                            $location = $userUtility->getUserGeolocation();

                            $this->mixpanelService->trackEvent('Booking Module Confirmed Clicked', [
                                'distinct_id' => $userDB->id,
                                'first_name' => $userDB->name,
                                'last_name' => $userDB->surname,
                                'email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,
                            ]);


                            $this->mixpanelService->setPeopleProperties($userDB->id, [
                                '$first_name' => $userDB->name,
                                '$last_name' => $userDB->surname,
                                '$email' => $userDB->email,
                                '$country' => $location['country'],
                                '$region' => $location['region'],
                                '$city' => $location['city'],
                                '$os' => $userUtility->getUserOS(), // Add OS here
                                'latitude' => $location['latitude'],
                                'longitude' => $location['longitude'],
                                'timezone' => $location['timezone'],
                                'ip_address' => $location['ip'],
                                'db_country' => $userDB->country,
                                'db_city' => $userDB->city,
                                'host_type' => $userDB->hostType->module_name,

                            ]);

                        } catch (\Exception $e) {


                        }
                    }
                }
            }

            if ($bookings) {
                foreach ($bookings as $booking) {
                    $booking_details = json_decode($booking->booking_otas_json_details);

                    $raw_message = json_decode($booking_details->attributes->raw_message);

                    $discount = $booking->discount;
                    $promotion = $booking->promotion;
                    $booking->amount = ($booking->amount + $discount + $promotion);
                    $booking['booking_status'] = 'confirmed';
                    // dd($booking);
                    array_push($booking_arr, $booking);
                }
            }

            $index++;
        }

        $bookingsArr = array();
        foreach ($booking_arr as $item) {
            $listings_details = Listing::where('listing_id', $item['listing_id'])->first();
            $listingRelation = ListingRelation::where('listing_id_other_ota', $listings_details->id)->first();
            if ($listingRelation) {
                $listings_details = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
            }
            $item['listing_details'] = $listings_details['listing_json'];
            array_push($bookingsArr, $item);
        }


        if (!empty($userDB->role_id) && $userDB->role_id === 2) {

            try {

                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Dashboard Opened', [
                    'distinct_id' => $userDB->id,
                    'first_name' => $userDB->name,
                    'last_name' => $userDB->surname,
                    'email' => $userDB->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $userDB->country,
                    'db_city' => $userDB->city

                ]);

                $this->mixpanelService->setPeopleProperties($userDB->id, [
                    '$first_name' => $userDB->name,
                    '$last_name' => $userDB->surname,
                    '$email' => $userDB->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $userDB->country,
                    'db_city' => $userDB->city

                ]);

            } catch (\Exception $e) {


            }
        }


        return response()->json($bookingsArr);

        //        GET https://staging.channex.io/api/v1/booking_revisions/feed?filter[property_id]=PROPERTY_ID

        //        $response = Http::withHeaders([
//            'user-api-key' =>  env('CHANNEX_API_KEY'),
//        ])->get(env('CHANNEX_URL')."/api/v1/booking_revisions?filter[property_id]=$propertyId");
//        // Check if the response is successful
//        if ($response->successful()) {
//            // Parse the JSON response
//            $data = $response->json();  // Returns the response body as an array
//            return $data;
//        } else {
//            // Handle error
//            abort(500, 'Failed to retrieve booking revisions');
//        }

    }

    private function getUserOS()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];


        $osArray = [
            'Windows' => 'Windows',
            'Macintosh' => 'Mac OS',
            'iPhone' => 'iOS',
            'iPad' => 'iOS',
            'Android' => 'Android',
            'Linux' => 'Linux',
            'PostmanRuntime' => 'Postman',
            'okhttp' => 'Android',
        ];


        if (stripos($userAgent, 'LivedInMobileApp') !== false && stripos($userAgent, 'CFNetwork') !== false && stripos($userAgent, 'Darwin') !== false) {

            return 'iOS';
        }


        foreach ($osArray as $key => $os) {
            if (stripos($userAgent, $key) !== false) {
                return $os;
            }
        }


        return 'Unknown';
    }

    public function bookingConfirmation($id)
    {
        $channel_activation = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . "/api/v1/booking_revisions/$id/ack");
        if ($channel_activation->successful()) {
            $channel_activation = $channel_activation->json();
            return response()->json($channel_activation);
        } else {
            return $channel_activation->body();
        }
    }

    public function getBookingByID($id)
    {
        $user = Auth::user();
        $booking_bank = BookingOtasDetails::where('id', $id)->first();

        if (strtolower($booking_bank->ota_name) == 'almosafer') {
            $listing = Listing::where('id', $booking_bank['listing_id'])->first();
        } else {
            $listing = Listing::where('listing_id', $booking_bank['listing_id'])->first();
        }

        $discount = $booking_bank->discount;
        $promotion = $booking_bank->promotion;
        $booking_bank->amount = ($booking_bank->amount + $discount + $promotion);

        if (strtolower($booking_bank->ota_name) == 'almosafer') {
            $listings_details = Listing::where('id', $booking_bank->listing_id)->first();
        } else {
            $listings_details = Listing::where('listing_id', $booking_bank->listing_id)->first();
        }

        $listingRelation = ListingRelation::where('listing_id_other_ota', $listings_details->id)->first();
        if ($listingRelation) {
            $listings_details = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
        }
        $booking_bank['cleaning_fees'] = $booking_bank->cleaning_fee;
        $booking_bank['discounts'] = $promotion + $discount;
        $booking_bank['guest_service_fees'] = 0;
        $booking_bank['airbn_fees'] = (int) $booking_bank->ota_commission;
        $total = $booking_bank->amount;
        $booking_bank['booking_status'] = 'confirmed';
        $booking_bank['livedin_share'] = round($total * ($listing->commission_value / 100));
        $booking_bank['host_share'] = round($total - $booking_bank['discounts'] - $booking_bank['airbn_fees'] - $booking_bank['livedin_share']);


        $arrival_date = Carbon::parse($booking_bank['arrival_date']);
        $departure_date = Carbon::parse($booking_bank['departure_date']);
        $booking_bank['total_night'] = $arrival_date->diffInDays($departure_date);


        $booking_bank['infants'] = 0;
        $booking_bank['cancellation_policy'] = '';
        $booking_bank['is_guest_verified'] = false;
        if (strtolower($booking_bank->ota_name) != 'almosafer' && !empty($booking_bank->booking_otas_json_details)) {
            $booking_details = json_decode($booking_bank->booking_otas_json_details, true);

            if (!empty($booking_details['attributes']['occupancy'])) {
                $occupancy = $booking_details['attributes']['occupancy'];

                if (!empty($occupancy['adults'])) {
                    $booking_bank['adults'] = $occupancy['adults'];
                }
                if (!empty($occupancy['children'])) {
                    $booking_bank['children'] = $occupancy['children'];
                }
                if (!empty($occupancy['infants'])) {
                    $booking_bank['infants'] = $occupancy['infants'];
                }
            }

            if (!empty($booking_details['attributes']['raw_message'])) {
                $raw_message = json_decode($booking_details['attributes']['raw_message'], true);

                if (!empty($raw_message['reservation']['cancellation_policy_category'])) {
                    $booking_bank['cancellation_policy'] = $raw_message['reservation']['cancellation_policy_category'];
                }

                if (!empty($raw_message['reservation']['is_guest_verified'])) {
                    $booking_bank['is_guest_verified'] = $raw_message['reservation']['is_guest_verified'];
                }
            }
        }

        $todayDate = Carbon::now()->toDateString();
        if ($todayDate >= $booking_bank->arrival_date) {
            $booking_bank['cancellable'] = false;
            $booking_bank['trip_status'] = 'Trip Completed';
        } else if ($todayDate < $booking_bank->arrival_date) {
            $booking_bank['cancellable'] = true;
            $booking_bank['trip_status'] = 'Upcoming Trip';

        } else {
            $booking_bank['cancellable'] = true;
            $booking_bank['trip_status'] = 'Currently Hosting';
        }
        $booking_bank['listing_details'] = $listings_details['listing_json'];
        try {
            $userUtility = new UserUtility();
            $location = $userUtility->getUserGeolocation();

            $this->mixpanelService->trackEvent('Booking Details Opened', [
                'distinct_id' => $user->id,
                'first_name' => $user->name,
                'last_name' => $user->surname,
                'email' => $user->email,
                '$country' => $location['country'],
                '$region' => $location['region'],
                '$city' => $location['city'],
                '$os' => $userUtility->getUserOS(), // Add OS here
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'timezone' => $location['timezone'],
                'ip_address' => $location['ip'],
                'db_country' => $user->country,
                'db_city' => $user->city,
                'host_type' => $user->hostType->module_name,
            ]);


            $this->mixpanelService->setPeopleProperties($user->id, [
                '$first_name' => $user->name,
                '$last_name' => $userDB->surname,
                '$email' => $user->email,
                '$country' => $location['country'],
                '$region' => $location['region'],
                '$city' => $location['city'],
                '$os' => $userUtility->getUserOS(), // Add OS here
                'latitude' => $location['latitude'],
                'longitude' => $location['longitude'],
                'timezone' => $location['timezone'],
                'ip_address' => $location['ip'],
                'db_country' => $user->country,
                'db_city' => $user->city,
                'host_type' => $user->hostType->module_name,

            ]);

        } catch (\Exception $e) {

        }

        return response()->json($booking_bank);
    }

    // public function getBookingByListingId($id)
    // {
    //     $bookings = BookingOtasDetails::where('listing_id', $id)->get();
    //     $listing = Listing::where('listing_id', $id)->first();
    //     $booking_livedIn = Bookings::where('listing_id', $listing->id)->get();

    //     $bookingCounts = [
    //         "livedin" => 0,
    //         "airbnb" => 0,
    //         "booking_com" => 0,
    //         "vrbo" => 0
    //     ];

    //     // dd($booking_livedIn);
    //     $bookingMainArray = array();
    //     if (count($booking_livedIn) > 0) {
    //         foreach ($booking_livedIn as $key => $item) {
    //             //            dd($booking_details->attributes);
    //             $booking_array['listing_id'] = $listing->id;
    //             $booking_array['booking_id'] = $item->id;
    //             $booking_array['ota_name'] = 'livedin';
    //             $booking_array['arrival_date'] = $item->booking_date_start;
    //             $departure_date = $item->booking_date_end;
    //             // $oneDayBefore = date('Y-m-d', strtotime($targetDate . ' -1 day'));

    //             $new_departure_date = date('Y-m-d', strtotime($departure_date . ' -1 day'));
    //             $booking_array['departure_date'] = $new_departure_date;
    //             array_push($bookingMainArray, $booking_array);
    //         }
    //     }

    //     $bookingCounts["livedin"] = count($booking_livedIn ?? []);

    //     if (count($bookings) > 0) {
    //         foreach ($bookings as $key => $item) {
    //             $booking_details = json_decode($item['booking_otas_json_details']);
    //             $bookingJson = json_decode($item->booking_otas_json_details, true);
    //             $otaSource = !empty($bookingJson['attributes']['ota_name']) ? $bookingJson['attributes']['ota_name'] : null;
    //             $otaSource = strtolower($otaSource);

    //             //            dd($booking_details->attributes);
    //             $booking_array['listing_id'] = $item['listing_id'];
    //             $booking_array['booking_id'] = $item->id;
    //             $booking_array['ota_name'] = $otaSource;
    //             $booking_array['arrival_date'] = $item->arrival_date;
    //             $new_departure_date = date('Y-m-d', strtotime($item->departure_date . ' -1 day'));
    //             $booking_array['departure_date'] = $new_departure_date;
    //             array_push($bookingMainArray, $booking_array);
    //             if ($otaSource === 'airbnb') {
    //                 $bookingCounts['airbnb']++;
    //             } elseif ($otaSource === 'booking.com') {
    //                 $bookingCounts['booking_com']++;
    //             } elseif ($otaSource === 'vrbo') {
    //                 $bookingCounts['vrbo']++;
    //             }
    //         }
    //     }

    //     // dd($booking_arrayOTA, $new_departure_date);
    //     $data = [];
    //     $data['bookings'] = $bookingMainArray;
    //     $data['booking_counts'] = $bookingCounts;
    //     // return $data;

    //     return response()->json([
    //         'status' => 200,
    //         'bookings' => json_encode($bookingMainArray),
    //         'booking_counts' => json_encode($bookingCounts),
    //     ]);

    // }

    public function getBookingByListingId(Request $request, $id)
    {
        $source = strtolower($request->search_ota);
        $source = $source === 'direct' ? 'host_booking' : $source;

        $listing = Listing::where('listing_id', $id)->first();
        if (!$listing) {
            return response()->json(['status' => 404, 'message' => 'Listing not found']);
        }

        $bookingLivedInQuery = Bookings::where('listing_id', $listing->id);
        $bookingLivedIn = $bookingLivedInQuery->get();
        $bookingsQuery = BookingOtasDetails::where('listing_id', $id);

        $bookingCounts = [
            'livedin' => $bookingLivedIn->count(),
            'airbnb' => $bookingsQuery->clone()->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                ['airbnb']
            )->count(),
            'booking_com' => $bookingsQuery->clone()->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                ['booking.com']
            )->count(),
            'vrbo' => $bookingsQuery->clone()->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                ['vrbo']
            )->count(),
            'gathern' => $bookingLivedInQuery->clone()->where('booking_sources', 'gathern')->count(),
            'direct' => $bookingLivedInQuery->clone()->where('booking_sources', 'host_booking')->count(),
        ];

        if (!empty($source) && $source !== 'all') {
            if ($source == 'livedin') {
                $bookingLivedInQuery = $bookingLivedInQuery->where('ota_name', $source)->get();
            } else {
                $bookingLivedInQuery = $bookingLivedInQuery->where('booking_sources', $source)->get();
            }
        } else {
            $bookingLivedInQuery = $bookingLivedInQuery->get();
        }

        $otaSource = $source === 'booking_com' ? 'booking.com' : $source;
        if (!empty($source) && $source !== 'all') {
            $bookingsQuery->whereRaw(
                'LOWER(JSON_UNQUOTE(JSON_EXTRACT(booking_otas_json_details, "$.attributes.ota_name"))) = ?',
                [$otaSource]
            );
        }

        $bookings = $bookingsQuery->get();


        $bookingMainArray = [];
        foreach ($bookingLivedInQuery as $item) {
            $bookingMainArray[] = [
                'listing_id' => $listing->id,
                'booking_id' => $item->id,
                'ota_name' => $item->booking_sources == 'host_booking' ? 'direct' : $item->booking_sources,
                'arrival_date' => $item->booking_date_start,
                'departure_date' => date('Y-m-d', strtotime($item->booking_date_end . ' -1 day')),
            ];
        }

        foreach ($bookings as $item) {
            $bookingJson = json_decode($item->booking_otas_json_details, true);
            $otaSource = strtolower($bookingJson['attributes']['ota_name'] ?? '');

            $bookingMainArray[] = [
                'listing_id' => $item->listing_id,
                'booking_id' => $item->id,
                'ota_name' => $otaSource,
                'arrival_date' => $item->arrival_date,
                'departure_date' => date('Y-m-d', strtotime($item->departure_date . ' -1 day')),
            ];
        }

        if (!empty($user->role_id) && $user->role_id === 2) {

            try {


                $userUtility = new UserUtility();
                $location = $userUtility->getUserGeolocation();

                $this->mixpanelService->trackEvent('Booking Details Opened', [
                    'distinct_id' => $user->id,
                    'first_name' => $user->name,
                    'last_name' => $user->surname,
                    'email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city,
                    'host_type' => $user->hostType->module_name,
                ]);


                $this->mixpanelService->setPeopleProperties($user->id, [
                    '$first_name' => $user->name,
                    '$last_name' => $userDB->surname,
                    '$email' => $user->email,
                    '$country' => $location['country'],
                    '$region' => $location['region'],
                    '$city' => $location['city'],
                    '$os' => $userUtility->getUserOS(), // Add OS here
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude'],
                    'timezone' => $location['timezone'],
                    'ip_address' => $location['ip'],
                    'db_country' => $user->country,
                    'db_city' => $user->city,
                    'host_type' => $user->hostType->module_name,

                ]);

            } catch (\Exception $e) {


            }
        }

        return response()->json([
            'status' => 200,
            'bookings' => json_encode($bookingMainArray),
            'booking_counts' => json_encode($bookingCounts),
            // 'bookings' => count($bookingMainArray),
            // 'booking_counts' => $bookingCounts,
        ]);
    }
}

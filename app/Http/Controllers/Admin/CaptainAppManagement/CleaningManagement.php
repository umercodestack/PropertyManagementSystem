<?php

namespace App\Http\Controllers\Admin\CaptainAppManagement;

use App\Http\Controllers\Controller;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Channels;
use App\Models\Cleaning;
use App\Models\CleaningComment;
use App\Models\Listing;
use App\Models\User;
use App\Models\Cleaningimages;
use App\Models\CleaningStatusLog;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ListingRelation;
class CleaningManagement extends Controller
{
    public function __construct()
    {
        $this->middleware('permission');
    }

    public function index(Request $request)
    {
        $sTime = microtime(true);
        $user = Auth::user();
        $date = Carbon::today()->toDateString();

        $bookings = Bookings::select(
            'bookings.id AS booking_id',
            DB::raw("'livedin' as type"),
            'bookings.booking_date_start AS checkin',
            'bookings.booking_date_end AS checkout',
            'guests.phone AS guest_phone',
            'guests.name AS guest_name',
            'guests.surname AS guest_surname',
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(listings.listing_json, '$.title')) AS listing_title"),
            'cleanings.status',
            'cleanings.key_code',
            'cleanings.checkout_time as checkouttime',
            'cleanings.checkin_time as checkintime',
            'users.name as cleaner_name',
            'users.surname as cleaner_surname',
            DB::raw("
        CASE 
            WHEN channels.connection_type IS NOT NULL THEN 
                (
                    EXISTS (
                        SELECT 1 
                        FROM bookings AS b
                        JOIN listings AS lst ON lst.id = b.listing_id
                        WHERE lst.id = listings.id
                        AND b.booking_status != 'cancelled'
                        AND b.booking_date_start = bookings.booking_date_end
                    ) 
                    OR EXISTS (
                        SELECT 1 
                        FROM booking_otas_details AS bod
                        WHERE bod.listing_id = l2.listing_id
                        AND bod.status != 'cancelled'
                        AND bod.arrival_date = bookings.booking_date_end
                    )
                )
            ELSE 
                (
                    EXISTS (
                        SELECT 1 
                        FROM bookings AS b
                        JOIN listings AS lst ON lst.id = b.listing_id
                        WHERE lst.id = listings.id
                        AND b.booking_status != 'cancelled'
                        AND b.booking_date_start = bookings.booking_date_end
                    )
                    OR EXISTS (
                        SELECT 1 
                        FROM booking_otas_details AS bod
                        LEFT JOIN listings AS l2 ON l2.listing_id = bod.listing_id
                        LEFT JOIN channels AS c ON c.id = l2.channel_id
                        LEFT JOIN listing_relations AS lr ON lr.listing_id_other_ota = l2.id
                        WHERE 
                            l2.id = bookings.listing_id
                            AND bod.status != 'cancelled'
                            AND bod.arrival_date = bookings.booking_date_end
                    )
                )
        END AS has_checkin
    "),
            DB::raw("'0' as channel_id"),
            'listings.id'
        )
            ->leftJoin('guests', 'guests.id', '=', 'bookings.guest_id')
            ->leftJoin('listings', 'listings.id', '=', 'bookings.listing_id')
            ->leftJoin('cleanings', function ($join) {
                $join->on('cleanings.booking_id', '=', 'bookings.id')
                    ->on('cleanings.listing_id', '=', 'listings.id');
            })
            ->leftJoin('users', 'users.id', '=', 'cleanings.cleaner_id')
            ->leftJoin('channels', 'channels.id', '=', 'listings.channel_id')
            ->leftJoin('listing_relations', 'listing_relations.listing_id_other_ota', '=', 'listings.id')
            ->leftJoin('listings as l2', 'l2.id', '=', 'listing_relations.listing_id_airbnb')
            ->where(function ($query) {
                $query->where('bookings.include_cleaning', 1)
                    ->orWhereNull('bookings.include_cleaning');
            })
            ->distinct();

        // ->orderBy('bookings.booking_date_end', 'DESC');

        $bookingOtas = BookingOtasDetails::select(
            'booking_otas_details.id AS booking_id',
            DB::raw("'ota' as type"),
            'booking_otas_details.arrival_date AS checkin',
            'booking_otas_details.departure_date AS checkout',
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(booking_otas_details.booking_otas_json_details, '$.attributes.customer.phone')) AS guest_phone"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(booking_otas_details.booking_otas_json_details, '$.attributes.customer.name')) AS guest_name"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(booking_otas_details.booking_otas_json_details, '$.attributes.customer.surname')) AS guest_surname"),
            DB::raw("
                CASE 
                    WHEN channels.connection_type IS NOT NULL THEN JSON_UNQUOTE(JSON_EXTRACT(l2.listing_json, '$.title'))
                    ELSE JSON_UNQUOTE(JSON_EXTRACT(listings.listing_json, '$.title'))
                 END AS listing_title
            "),
            'cleanings.status',
            'cleanings.key_code',
            'cleanings.checkout_time as checkouttime',
            'cleanings.checkin_time as checkintime',
            'users.name as cleaner_name',
            'users.surname as cleaner_surname',
            DB::raw("
            CASE 
                WHEN channels.connection_type IS NOT NULL THEN 
                    (EXISTS (
                        SELECT 1 
                        FROM bookings AS b
                        JOIN listings AS lst ON lst.id = b.listing_id
                        WHERE lst.id = l2.id
                        AND b.booking_status != 'cancelled'
                        AND b.booking_date_start = booking_otas_details.departure_date
                    ) 
                    OR EXISTS (
                        SELECT 1 
                        FROM booking_otas_details AS bod
                        WHERE bod.listing_id = l2.listing_id
                        AND bod.status != 'cancelled'
                        AND bod.arrival_date = booking_otas_details.departure_date
                    )) 
                ELSE 
                   (
    EXISTS (
        SELECT 1 
        FROM bookings AS b
        JOIN listings AS lst ON lst.id = b.listing_id
        WHERE lst.listing_id = booking_otas_details.listing_id
          AND b.booking_status != 'cancelled'
          AND b.booking_date_start = booking_otas_details.departure_date
    )
    OR EXISTS (
        SELECT 1 
        FROM booking_otas_details AS bod
        LEFT JOIN listings AS l2 ON l2.listing_id = bod.listing_id
        LEFT JOIN channels AS c ON c.id = l2.channel_id
        LEFT JOIN listing_relations AS lr ON lr.listing_id_other_ota = l2.id
        WHERE 
            l2.listing_id = booking_otas_details.listing_id
            AND bod.status != 'cancelled'
            AND bod.arrival_date = booking_otas_details.departure_date
    )
)
            END AS has_checkin
        ")
            ,
            'channels.id as channel_id',
            'listings.id'
        )

            ->leftJoin('listings', 'listings.listing_id', '=', 'booking_otas_details.listing_id')
            ->leftJoin('cleanings', function ($join) {
                $join->on('cleanings.booking_id', '=', 'booking_otas_details.id')
                    ->on('cleanings.listing_id', '=', 'listings.id');
            })
            ->leftJoin('users', 'users.id', '=', 'cleanings.cleaner_id')
            ->leftJoin('channels', 'channels.id', '=', 'listings.channel_id')
            ->leftJoin('listing_relations', 'listing_relations.listing_id_other_ota', '=', 'listings.id')
            ->leftJoin('listings as l2', 'l2.id', '=', 'listing_relations.listing_id_airbnb')
            ->where('booking_otas_details.ota_name', '!=', 'almosafer')

            ->distinct();
        // ->orderBy('booking_otas_details.departure_date', 'DESC');

        if ($request->type == 'checkin') {
            $dateRange = $request->input('daterange');
            [$startDate, $endDate] = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');

            if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 8) {
                $bookingOtas->whereBetween('arrival_date', [$startDate, $endDate]);
                $bookings->whereBetween('booking_date_start', [$startDate, $endDate]);

            } else {
                $bookingOtas->whereJsonContains('listings.exp_managers', (string) $user->id)
                    ->whereBetween('arrival_date', [$startDate, $endDate]);

                $bookings->whereJsonContains('listings.exp_managers', (string) $user->id)
                    ->whereBetween('booking_date_start', [$startDate, $endDate]);
            }
        } else if ($request->type == "checkout") {
            $dateRange = $request->input('daterange');
            [$startDate, $endDate] = explode(' - ', $dateRange);
            $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
            $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');

            if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 8) {
                $bookingOtas->whereBetween('departure_date', [$startDate, $endDate]);
                $bookings->whereBetween('booking_date_end', [$startDate, $endDate]);
            } else {
                $bookingOtas->whereJsonContains('listings.exp_managers', (string) $user->id)
                    ->whereBetween('departure_date', [$startDate, $endDate]);

                $bookings->whereJsonContains('listings.exp_managers', (string) $user->id)
                    ->whereBetween('booking_date_end', [$startDate, $endDate]);
            }
        } else {
            if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 8) {
                $bookingOtas->where('departure_date', '<=', $date);
                $bookings->where('booking_date_end', '<=', $date);
            } else {
                $bookingOtas
                    ->where('departure_date', '<=', $date)
                    ->whereJsonContains('listings.exp_managers', (string) $user->id);

                $bookings
                    ->where('booking_date_end', '<=', $date)
                    ->whereJsonContains('listings.exp_managers', (string) $user->id);
            }
        }
        $bookings = $bookings
            ->where('booking_status', '!=', 'cancelled');

        $bookingOtas = $bookingOtas
            ->where('booking_otas_details.status', '!=', 'cancelled');

        // $data = $bookings->unionAll($bookingOtas)->get();
        $data = $bookings->unionAll($bookingOtas)
            ->orderByRaw("checkout IS NULL, checkout DESC")
            ->get();

        return view('Admin.captain-app-management.cleaning-management.index', ['cleanings' => $data]);
    }


    public function oldindex(Request $request)
    {
        // dd($request);
        $date = Carbon::today()->toDateString();
        $checkoutData = array();
        $user = Auth::user();

        if ($request->has('type') || $request->has('daterange')) {
            if ($request->type == 'checkout') {



                $dateRange = $request->input('daterange');
                [$startDate, $endDate] = explode(' - ', $dateRange);
                // Parse the dates and format them as YYYY-MM-DD
                $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');

                if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 8) {

                    $chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'desc')->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('departure_date', [$startDate, $endDate]);
                    })->get();


                    $chechoutsLived = Bookings::orderBy('booking_date_end', 'desc')->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                    })->get();
                } else {
                    $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'listings.exp_managers') // Select 
                        ->join('listings', 'booking_otas_details.listing_id', '=', 'listings.listing_id') // Join condition
                        ->whereJsonContains('listings.exp_managers', (string) $user->id)
                        ->orderBy('departure_date', 'desc')
                        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                            return $query->whereBetween('departure_date', [$startDate, $endDate]);
                        })->get();

                    $chechoutsLived = Bookings::select('bookings.*', 'listings.exp_managers')
                        ->join('listings', 'bookings.listing_id', '=', 'listings.id')
                        ->whereJsonContains('listings.exp_managers', (string) $user->id)
                        ->orderBy('booking_date_end', 'desc')
                        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                            return $query->whereBetween('booking_date_end', [$startDate, $endDate]);
                        })->get();
                }

            } else if ($request->type == 'checkin') {


                $dateRange = $request->input('daterange');
                [$startDate, $endDate] = explode(' - ', $dateRange);
                // Parse the dates and format them as YYYY-MM-DD
                $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');

                if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 8) {

                    $chechoutsOta = BookingOtasDetails::orderBy('arrival_date', 'desc')->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('arrival_date', [$startDate, $endDate]);
                    })->get();
                    $chechoutsLived = Bookings::orderBy('booking_date_start', 'desc')->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('booking_date_start', [$startDate, $endDate]);
                    })->get();
                } else {
                    $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'listings.exp_managers')
                        ->join('listings', 'booking_otas_details.listing_id', '=', 'listings.listing_id')
                        ->whereJsonContains('listings.exp_managers', (string) $user->id)
                        ->orderBy('arrival_date', 'desc')
                        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                            return $query->whereBetween('arrival_date', [$startDate, $endDate]);
                        })->get();


                    $chechoutsLived = Bookings::select('bookings.*', 'listings.exp_managers') // Select required columns
                        ->join('listings', 'bookings.listing_id', '=', 'listings.id') // Join condition
                        ->whereJsonContains('listings.exp_managers', (string) $user->id) // Apply exp_managers condition
                        ->orderBy('booking_date_start', 'desc')
                        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                            return $query->whereBetween('booking_date_start', [$startDate, $endDate]);
                        })->get();

                }

            }

        } else {




            if ($user->role_id === 1 || $user->role_id === 4 || $user->role_id === 8) {
                $chechoutsOta = BookingOtasDetails::orderBy('departure_date', 'desc')->where('departure_date', '<=', $date)->get();
                $chechoutsLived = Bookings::orderBy('booking_date_end', 'desc')->where('booking_date_end', '<=', $date)->get();
            } else {
                $chechoutsOta = BookingOtasDetails::select('booking_otas_details.*', 'listings.exp_managers') // Select required columns
                    ->join('listings', 'booking_otas_details.listing_id', '=', 'listings.listing_id') // Join condition
                    ->where('departure_date', '<=', $date)
                    ->whereJsonContains('listings.exp_managers', (string) $user->id)
                    ->orderBy('departure_date', 'desc')
                    ->get();




                $chechoutsLived = Bookings::select('bookings.*', 'listings.exp_managers')
                    ->join('listings', 'bookings.listing_id', '=', 'listings.id')
                    ->where('booking_date_end', '<=', $date)
                    ->whereJsonContains('listings.exp_managers', (string) $user->id)
                    ->orderBy('booking_date_end', 'desc')
                    ->get();
            }



        }

        foreach ($chechoutsOta as $items) {
            if ($items->status == 'cancelled') {
                continue;
            }
            // dd($items);

            $booking_json = json_decode($items->booking_otas_json_details);
            $booking_json = $booking_json->attributes;
            $guest = $booking_json->customer;
            $checkout['booking_id'] = $items->id;
            $checkout['checkin'] = $items->arrival_date;
            $checkout['type'] = 'ota';
            $checkout['checkout'] = $items->departure_date;
            $listing = Listing::where('listing_id', $items->listing_id)->first();
            if (!isset($listing->channel_id)) {
                continue;


            }


            // use App\Models\ListingRelation;

            $listingRelation = ListingRelation::where('listing_id_other_ota', $listing->id)->first();
            if ($listingRelation) {

                $listing = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
                // }

            }

            if (!$listing) {
                continue; // Skip iteration if $listing becomes null
            }
            $channel = Channels::where('id', $listing->channel_id)->first();


            $host = User::whereId($channel->user_id)->select('name', 'surname', 'email', 'phone')->first();


            $listing_json = json_decode($listing->listing_json);
            $checkout['listing_id'] = $listing->id;
            $checkout['listing_title'] = $listing_json->title;
            $checkout['guest'] = $guest;
            $checkout['host'] = $host->toArray();
            $has_booking = BookingOtasDetails::where('listing_id', $listing->listing_id)->where('status', '!=', 'cancelled')->where('arrival_date', $checkout['checkout'])->get();
            $has_bookingLived = Bookings::where('listing_id', $listing->id)->where('booking_status', '!=', 'cancelled')->where('booking_date_start', $checkout['checkout'])->get();
            $checkout['has_checkin'] = isset($has_booking) && count($has_booking) > 0 || isset($has_bookingLived) && count($has_bookingLived) > 0 ? 1 : 0;
            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $checkout['checkout'])->first();
            $cleaning === null ? $checkout['status'] = 'pending' : $checkout['status'] = $cleaning->status;
            $cleaning === null ? $checkout['key_code'] = '' : $checkout['key_code'] = $cleaning->key_code;

            $cleaning === null ? $checkout['checkouttime'] = '' : $checkout['checkouttime'] = $cleaning->checkout_time;
            $cleaning === null ? $checkout['checkintime'] = '' : $checkout['checkintime'] = $cleaning->checkin_time;

            if ($cleaning !== null) {
                $cleaner = User::whereId($cleaning->cleaner_id)->select('name', 'surname')->first();
                $checkout['cleaner_Name'] = $cleaner !== null
                    ? $cleaner->name . ' ' . $cleaner->surname
                    : 'N/A';
            } else {
                $checkout['cleaner_Name'] = 'N/A';
            }

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
            if (is_null($listing)) {
                continue;
            }
            $listing_json = json_decode($listing->listing_json);
            $checkoutLiv['listing_id'] = $listing->id;
            $checkoutLiv['listing_title'] = $listing_json->title;
            $has_booking = BookingOtasDetails::where('listing_id', $listing->listing_id)->where('status', '!=', 'cancelled')->where('arrival_date', $checkoutLiv['checkout'])->get();
            if (!$listing) {
                continue; // Skip iteration if $listing becomes null
            }

            $channel = Channels::where('id', $listing->channel_id)->first();


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

            $cleaning = Cleaning::where('booking_id', $items->id)->where('listing_id', $listing->id)->where('checkout_date', $checkoutLiv['checkout'])->first();
            $cleaning === null ? $checkoutLiv['status'] = 'pending' : $checkoutLiv['status'] = $cleaning->status;
            $cleaning === null ? $checkoutLiv['key_code'] = '' : $checkoutLiv['key_code'] = $cleaning->key_code;

            $cleaning === null ? $checkoutLiv['checkouttime'] = '' : $checkoutLiv['checkouttime'] = $cleaning->checkout_time;
            $cleaning === null ? $checkoutLiv['checkintime'] = '' : $checkoutLiv['checkintime'] = $cleaning->checkin_time;

            if ($cleaning !== null) {
                $cleaner = User::whereId($cleaning->cleaner_id)->select('name', 'surname')->first();
                $checkoutLiv['cleaner_Name'] = $cleaner !== null
                    ? $cleaner->name . ' ' . $cleaner->surname
                    : 'N/A';
            } else {
                $checkoutLiv['cleaner_Name'] = 'N/A';
            }


            array_push($checkoutData, $checkoutLiv);
        }
        // dd($checkoutData);

        return view('Admin.captain-app-management.cleaning-management.index', ['cleanings' => $checkoutData]);
    }

    public function editData(Request $request)
    {
        // dd($request->all());
        $cleaning = Cleaning::where('booking_id', $request->booking_id)->where('checkout_date', $request->checkout)->first();
        isset($cleaning->id) ? $comments = CleaningComment::with('user')->where('cleaning_id', $cleaning->id)->get() : $comments = null;

        $cleaningImages = isset($cleaning->id) ? CleaningImages::where('cleaning_id', $cleaning->id)->get() : null;

        $checklist = isset($cleaning->id) ? DB::select("CALL get_cleaning_checklist_v2($cleaning->id);") : null;

        $statuslogs = isset($cleaning->id) ? CleaningStatusLog::select('users.name as user_name', 'cleaning_status_log.status', 'cleaning_status_log.timestamp')->join('users', 'users.id', '=', 'cleaning_status_log.user_id')->where('cleaning_status_log.cleaning_id', $cleaning->id)->orderBy('cleaning_status_log.timestamp', 'desc')->get() : null;

        if ($request->type == 'ota') {
            $chechoutsOta = BookingOtasDetails::where('id', $request->booking_id)->where('departure_date', '=', $request->checkout)->first();
            $booking_json = json_decode($chechoutsOta->booking_otas_json_details);
            $guest = $booking_json->attributes->customer;
            $booking_json = $booking_json->attributes;
            $listing = Listing::where('listing_id', $chechoutsOta->listing_id)->first();

            $Channels = Channels::where('id', $listing->channel_id)->first();
            if ($Channels->connection_type != null) {
                $listing = Listing::where('listing_id', $chechoutsOta->listing_id)->first();
                $listingRelation = ListingRelation::where('listing_id_other_ota', $listing->id)->first();
                $listing = Listing::where('id', $listingRelation->listing_id_airbnb)->first();
            }


            $listing_users = json_decode($listing->user_id);
            $listing_user = isset($listing_users) && count($listing_users) > 0 ? $listing_users[count($listing_users) - 1] : $listing_users[0];
            $listing_user = (int) $listing_user;
            $listing_json = json_decode($listing->listing_json);
            $host = User::whereId($listing_user)->select('name', 'surname', 'email', 'phone')->first();
            $booking = [
                'listing' => $listing_json->title,
                'listing_id' => $listing->id,
                'booking_id' => $request->booking_id,
                'checkout' => $request->checkout,
                'host' => $host,
                'guest' => $guest,
                'cleaning_date' => $cleaning ? $cleaning->cleaning_date : null,
                'cleaner_id' => $cleaning ? $cleaning->cleaner_id : null,
                'checkout_time' => $cleaning ? $cleaning->checkout_time : null,
                'checkin_time' => $cleaning ? $cleaning->checkin_time : null,
                'cleaner_assign_datetime' => $cleaning ? $cleaning->cleaner_assign_datetime : null,
                'cleaning' => isset($cleaning) ? $cleaning->toArray() : null,
                // 'comments' => $comments->toArray()
            ];
            // dd($booking);
        } else {
            $chechoutsLived = Bookings::where('id', $request->booking_id)->where('booking_date_end', '<=', $request->checkout)->first();
            $listing = Listing::where('id', $chechoutsLived->listing_id)->first();
            $listing_users = json_decode($listing->user_id);
            $listing_user = isset($listing_users) && count($listing_users) > 0 ? $listing_users[count($listing_users) - 1] : $listing_users[0];
            $listing_user = (int) $listing_user;
            $listing_json = json_decode($listing->listing_json);
            $guest = array(
                'name' => $chechoutsLived->name,
                'surname' => $chechoutsLived->surname,
                'email' => $chechoutsLived->email,
                'phone' => $chechoutsLived->phone,
            );
            $guest = (Object) $guest;
            $host = User::whereId($listing_user)->select('name', 'surname', 'email', 'phone')->first();
            $booking = [
                'listing' => $listing_json->title,
                'listing_id' => $listing->id,
                'booking_id' => $request->booking_id,
                'checkout' => $request->checkout,
                'host' => $host,
                'guest' => $guest,
                'cleaning_date' => $cleaning ? $cleaning->cleaning_date : null,
                'cleaner_id' => $cleaning ? $cleaning->cleaner_id : null,
                'checkout_time' => $cleaning ? $cleaning->checkout_time : null,
                'checkin_time' => $cleaning ? $cleaning->checkin_time : null,
                'cleaner_assign_datetime' => $cleaning ? $cleaning->cleaner_assign_datetime : null,
                'cleaning' => isset($cleaning) ? $cleaning->toArray() : null,
                // 'comments' => $comments->toArray()
            ];
        }
        // dd($booking);

        $users = User::where('role_id', '=', 9)->get();
        return view('Admin.captain-app-management.cleaning-management.edit', ['booking' => $booking, 'comments' => $comments, 'cleaningimages' => $cleaningImages, 'users' => $users, 'checklist' => $checklist, 'statuslogs' => $statuslogs]);

    }

    public function updateData(Request $request)
    {
        $data = $request->all();

        if ($data['cleaning_id'] !== null) {
            $cleaning = Cleaning::where('id', (int) $data['cleaning_id'])->first();
            $cleaning->update([
                'key_code' => $data['keycode'],
                'cleaner_id' => $data['cleaner_id'] ?? null,
                'checkin_time' => $request->checkintime ? Carbon::parse($request->checkintime)->toDateTimeString() : null,
                'checkout_time' => $request->checkouttime ? Carbon::parse($request->checkouttime)->toDateTimeString() : null,
                'cleaner_assign_datetime' => $request->cleaner_assign_datetime ? Carbon::parse($request->cleaner_assign_datetime)->toDateTimeString() : null,
                'status' => $data['cleaning_status'],
            ]);
            return redirect()->back();
        }
        if ($data['cleaning_id'] === null) {
            // dd($data);
            $cleaning = Cleaning::create([
                'listing_id' => $data['listing_id'],
                'booking_id' => (int) $data['booking_id'],
                'checkout_date' => $data['checkout_date'],
                'key_code' => $data['keycode'],
                'status' => $data['cleaning_status'],
                'cleaner_id' => $data['cleaner_id'] ?? null,
                'checkin_time' => $request->checkintime ? Carbon::parse($request->checkintime)->toDateTimeString() : null,
                'checkout_time' => $request->checkouttime ? Carbon::parse($request->checkouttime)->toDateTimeString() : null,
                'cleaner_assign_datetime' => $request->cleaner_assign_datetime ? Carbon::parse($request->cleaner_assign_datetime)->toDateTimeString() : null,
            ]);
            return redirect()->back();

        } else {
            return redirect()->back();
        }
    }

    public function storeComment(Request $request)
    {
        $data = $request->all();
        $user = Auth::user();
        CleaningComment::create([
            'user_id' => $user->id,
            'cleaning_id' => $data['cleaning_id'],
            'comments' => $data['comments'],
        ]);
        return redirect()->back();
    }
}

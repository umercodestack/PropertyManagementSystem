<?php

namespace App\Exports;

use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Calender;
use App\Models\Listings;
use App\Models\ListingRelation;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;

class AdrExport implements FromCollection, WithTitle
{
    protected $data;
    protected $month;
    protected $year;
    protected $datePeriod;
    protected $churnedListings;

    public function __construct($data)
    {
        $this->data = $data;
        [$this->year, $this->month] = explode('-', $data['month']);

        // Initialize DatePeriod once
        $start = new DateTime("{$this->year}-{$this->month}-01");
        $end = (clone $start)->modify('last day of this month');
        $this->datePeriod = new DatePeriod($start, new DateInterval('P1D'), $end->modify('+1 day'));

        $reportMonthStart = Carbon::createFromDate($this->year, $this->month, 1)->startOfMonth();

        $this->churnedListings = \App\Models\ChurnedProperty::select('listing_id', 'churned_date')
            ->get()
            ->filter(function ($churned) use ($reportMonthStart) {
                // dd(Carbon::parse($churned->churned_date)->lt($reportMonthStart));
                return Carbon::parse($churned->churned_date)->lt($reportMonthStart);
            })
            ->pluck('listing_id')
            ->toArray();
    }

    public function title(): string
    {
        return 'Gross Revenue';
    }

    public function collection()
    {
        // Fetch listings in a single query
        $listings = Listings::select([
            'listings.id',
            'listings.created_at',
            DB::raw("REPLACE(listings.listing_id, '.', '-') AS listing_id"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(listing_json, '$.title')) AS title"),
            'be_listing_name',
            'property_about',
            'bedrooms',
            'beds',
            'bathrooms',
            'district',
            'street',
            'property_type',
            'is_allow_pets',
            'is_self_check_in',
            'kitchen',
            'living_room',
            'corridor',
            'laundry_area',
            'outdoor_area',
            'discounts',
            'tax',
            'cleaning_fee',
            'google_map',
        ])
            ->join('channels', fn($query) => $query->on('channels.id', '=', 'listings.channel_id')->whereNull('channels.connection_type'))
            ->where('listings.is_sync', 'sync_all')
            ->whereNotIn('listings.listing_id', $this->churnedListings)
            ->orderBy('listings.id')
            ->get()
            ->keyBy('listing_id');
        // dd($listings->pluck('id')->toArray());
        $listingRel = ListingRelation::whereIn('listing_id_airbnb', $listings->pluck('id'))
            ->get();
        $listingRel = Listings::whereIn('id', $listingRel->pluck('listing_id_other_ota'))->get();
        // dd($listingRel->pluck('listing_id')->toArray());
        // Fetch calendar data for all listings in one query
        $calendarData = Calender::whereIn('listing_id', $listings->pluck('listing_id'))
            ->whereMonth('calender_date', $this->month)
            ->whereYear('calender_date', $this->year)
            ->select('listing_id', 'calender_date', 'is_lock', 'rate')
            ->get()
            ->groupBy('listing_id');

        // Fetch booking data
        $bookingData = Bookings::whereIn('listing_id', $listings->pluck('id'))
            ->where('booking_status', '!=', 'cancelled')
            ->whereYear('booking_date_start', $this->year)
            ->whereMonth('booking_date_start', '<=', $this->month)
            ->whereYear('booking_date_end', $this->year)
            ->whereMonth('booking_date_end', '>=', $this->month)
            ->select('id', 'listing_id', 'booking_date_start', 'booking_date_end', 'per_night_price', 'cleaning_fee')
            ->get()
            ->groupBy('listing_id');

        // Fetch OTA booking data
        $otaBookingData = BookingOtasDetails::whereIn('listing_id', array_merge($listings->pluck('listing_id')->toArray(), $listingRel->pluck('listing_id')->toArray()))
            ->where('status', '!=', 'cancelled')
            ->whereYear('arrival_date', $this->year)
            ->whereMonth('arrival_date', '<=', $this->month)
            ->whereYear('departure_date', $this->year)
            ->whereMonth('departure_date', '>=', $this->month)
            ->select('id', 'listing_id', 'arrival_date', 'departure_date', 'booking_otas_json_details', 'amount', 'discount', 'promotion', 'cleaning_fee', 'short_term_cleaning')
            ->get()
            ->groupBy('listing_id');
        // dd($otaBookingData);
        // Fetch listing relations
        $listingRelations = ListingRelation::whereIn('listing_id_airbnb', $listings->pluck('id'))
            ->where('listing_type', 'BookingCom')
            ->get()
            ->groupBy('listing_id_airbnb');

        // Almosafer
        $almosaferlistingRelations = ListingRelation::whereIn('listing_id_airbnb', $listings->pluck('id'))
            ->where('listing_type', 'Almosafer')
            ->get()
            ->groupBy('listing_id_airbnb');

        // Build header
        $header = ['Apartment Title', 'Live Date'];
        $dates = [];
        foreach ($this->datePeriod as $date) {
            $dateStr = $date->format('Y-m-d');
            $header[] = $dateStr;
            $dates[] = $dateStr;
        }
        $occupancies = [$header];

        // Process each listing
        foreach ($listings as $listing) {
            $occupancy = [
                'apartment_title' => $listing->title,
                'live_date' => Carbon::parse($listing->created_at)->toDateString(),
            ];

            // Get related listing IDs
            $relatedListingIds = [$listing->listing_id];
            if (isset($listingRelations[$listing->id])) {
                $relatedListingIds = array_merge(
                    $relatedListingIds,
                    Listings::whereIn('id', $listingRelations[$listing->id]->pluck('listing_id_other_ota'))
                        ->pluck('listing_id')
                        ->toArray()
                );
            }

            // Almosafer
            if(!empty($almosaferlistingRelations[$listing->id])){
                $relatedListingIds = array_merge(
                    $relatedListingIds,
                    Listings::whereIn('id', $almosaferlistingRelations[$listing->id]->pluck('listing_id_other_ota'))
                        ->pluck('id')
                        ->toArray()
                );
            }

            // Get calendar for this listing
            $listingCalendar = $calendarData[$listing->listing_id] ?? collect([]);
            $calendarByDate = $listingCalendar->keyBy('calender_date');

            // Get bookings for this listing
            $listingBookings = $bookingData[$listing->id] ?? collect([]);
            $listingOtaBookings = $otaBookingData[$listing->listing_id] ?? collect([]);
            foreach ($dates as $date) {
                $carbonDate = Carbon::parse($date);

                // Check direct bookings
                $directBooking = $listingBookings->first(function ($booking) use ($carbonDate) {
                    $startDate = Carbon::parse($booking->booking_date_start);
                    // Exclude checkout date by subtracting 1 day from end date
                    $endDate = Carbon::parse($booking->booking_date_end)->subDay();
                    return $carbonDate->between($startDate, $endDate);
                });

                if ($directBooking) {
                    $daysCount = max(1, Carbon::parse($directBooking->booking_date_start)->diffInDays($directBooking->booking_date_end));
                    $occupancy[$date] = round($directBooking->per_night_price + ($directBooking->cleaning_fee / $daysCount), 2);
                    continue;
                }

                // Check OTA bookings
                $otaBooking = $listingOtaBookings->first(function ($booking) use ($carbonDate, $relatedListingIds) {
                    $startDate = Carbon::parse($booking->arrival_date);
                    // Exclude checkout date by subtracting 1 day from departure date
                    $endDate = Carbon::parse($booking->departure_date)->subDay();
                    return in_array($booking->listing_id, $relatedListingIds) &&
                        $carbonDate->between($startDate, $endDate);
                });

                if ($otaBooking) {

                    $daysCount = max(1, Carbon::parse($otaBooking->arrival_date)->diffInDays($otaBooking->departure_date));
                    $basePrice = $otaBooking->amount + $otaBooking->cleaning_fee;
                    // if($otaBooking->id == 3106){
                    //     dd($otaBooking->amount , $otaBooking->cleaning_fee , $otaBooking->short_term_cleaning, $daysCount);
                    // }
                    $occupancy[$date] = round(($basePrice) / $daysCount, 2);
                    continue;
                }

                $listingRel = ListingRelation::where('listing_id_airbnb', $listing->id)->where('listing_type', 'BookingCom')
                    ->first();
                if ($listingRel) {
                    $listingRel = Listings::where('id', $listingRel->listing_id_other_ota)->first();
                    // dd($listingRel);
                    if ($listingRel) {
                        $listingRelOtaBookings = $otaBookingData[$listingRel->listing_id] ?? collect([]);
                        if ($listingRelOtaBookings->isNotEmpty()) {

                            $otaBooking = $listingRelOtaBookings->first(function ($booking) use ($carbonDate, $relatedListingIds) {
                                $startDate = Carbon::parse($booking->arrival_date);
                                // Exclude checkout date by subtracting 1 day from departure date
                                $endDate = Carbon::parse($booking->departure_date)->subDay();
                                return in_array($booking->listing_id, $relatedListingIds) &&
                                    $carbonDate->between($startDate, $endDate);
                            });

                            if ($otaBooking) {

                                $daysCount = max(1, Carbon::parse($otaBooking->arrival_date)->diffInDays($otaBooking->departure_date));
                                $basePrice = $otaBooking->amount + $otaBooking->discount + $otaBooking->promotion + $otaBooking->cleaning_fee;
                                $occupancy[$date] = round(($basePrice) / $daysCount, 2);
                                continue;
                            }
                        }
                    }
                }

                // Almosafer
                $listingRel = ListingRelation::where('listing_id_airbnb', $listing->id)->where('listing_type', 'Almosafer')
                    ->first();
                if ($listingRel) {
                    $listingRel = Listings::where('id', $listingRel->listing_id_other_ota)->first();
                    // dd($listingRel);
                    if ($listingRel) {
                        $listingRelOtaBookings = $otaBookingData[$listingRel->id] ?? collect([]);
                        if ($listingRelOtaBookings->isNotEmpty()) {

                            $otaBooking = $listingRelOtaBookings->first(function ($booking) use ($carbonDate, $relatedListingIds) {
                                $startDate = Carbon::parse($booking->arrival_date);
                                // Exclude checkout date by subtracting 1 day from departure date
                                $endDate = Carbon::parse($booking->departure_date)->subDay();
                                return in_array($booking->listing_id, $relatedListingIds) &&
                                    $carbonDate->between($startDate, $endDate);
                            });

                            if ($otaBooking) {

                                $daysCount = max(1, Carbon::parse($otaBooking->arrival_date)->diffInDays($otaBooking->departure_date));
                                $basePrice = $otaBooking->amount + $otaBooking->discount + $otaBooking->promotion + $otaBooking->cleaning_fee;
                                $occupancy[$date] = round(($basePrice) / $daysCount, 2);
                                continue;
                            }
                        }
                    }
                }

                // Check calendar availability for all dates, including checkout date
                $calendarEntry = $calendarByDate[$date] ?? null;
                $occupancy[$date] = $calendarEntry && $calendarEntry->is_lock == 1 ? 'Block' : 'Available';
            }

            $occupancies[] = $occupancy;
        }

        return new Collection($occupancies);
    }
}
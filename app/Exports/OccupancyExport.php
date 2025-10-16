<?php

namespace App\Exports;

use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Calender;
use App\Models\ListingRelation;
use App\Models\Listings;
use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OccupancyExport implements FromCollection, WithStyles, WithTitle
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
        return 'Occupancy';
    }

    public function collection()
    {
        // Fetch listings in a single query
        $listings = Listings::select([
            'listings.id',
            'listings.created_at',
            DB::raw("REPLACE(listings.listing_id, '.', '-') AS listing_id"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(listing_json, '$.title')) AS title"),
        ])
            ->join('channels', fn($query) => $query->on('channels.id', '=', 'listings.channel_id')->whereNull('channels.connection_type'))
            ->where('listings.is_sync', 'sync_all')
            ->whereNotIn('listings.listing_id', $this->churnedListings)
            ->orderBy('listings.id')
            ->get()
            ->keyBy('listing_id');

        // Fetch calendar data for all listings in one query
        $calendarData = Calender::whereIn('listing_id', $listings->pluck('listing_id'))
            ->whereMonth('calender_date', $this->month)
            ->whereYear('calender_date', $this->year)
            ->select('listing_id', 'calender_date', 'is_lock')
            ->get()
            ->groupBy('listing_id');

        // Fetch booking data
        $bookingData = Bookings::whereIn('listing_id', $listings->pluck('id'))
            ->where('booking_status', '!=', 'cancelled')
            ->whereYear('booking_date_start', $this->year)
            ->whereMonth('booking_date_start', '<=', $this->month)
            ->whereYear('booking_date_end', $this->year)
            ->whereMonth('booking_date_end', '>=', $this->month)
            ->select('listing_id', 'booking_date_start', 'booking_date_end')
            ->get()
            ->groupBy('listing_id');

        $listingRel = ListingRelation::whereIn('listing_id_airbnb', $listings->pluck('id'))
            ->get();
        $listingRel = Listings::whereIn('id', $listingRel->pluck('listing_id_other_ota'))->get();


        // Fetch OTA booking data
        $otaBookingData = BookingOtasDetails::whereIn('listing_id', array_merge($listings->pluck('listing_id')->toArray(), $listingRel->pluck('listing_id')->toArray()))
            ->where('status', '!=', 'cancelled')
            ->whereYear('arrival_date', $this->year)
            ->whereMonth('arrival_date', '<=', $this->month)
            ->whereYear('departure_date', $this->year)
            ->whereMonth('departure_date', '>=', $this->month)
            ->select('listing_id', 'arrival_date', 'departure_date')
            ->get()
            ->groupBy('listing_id');

        // Fetch listing relations
        $listingRelations = ListingRelation::whereIn('listing_id_airbnb', $listings->pluck('id'))
            ->pluck('listing_id_other_ota', 'listing_id_airbnb')
            ->mapWithKeys(function ($otherOtaId, $airbnbId) {
                return [$airbnbId => Listings::where('id', $otherOtaId)->value('listing_id')];
            });

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
                $relatedListingIds[] = $listingRelations[$listing->id];
            }

            // Get calendar for this listing
            $listingCalendar = $calendarData[$listing->listing_id] ?? collect([]);
            $calendarByDate = $listingCalendar->keyBy('calender_date');

            // Get bookings for this listing
            $listingBookings = $bookingData[$listing->id] ?? collect([]);
            $listingOtaBookings = $otaBookingData[$listing->listing_id] ?? collect([]);

            foreach ($dates as $date) {
                $carbonDate = Carbon::parse($date);

                // Check if date is in calendar
                if (!isset($calendarByDate[$date])) {
                    $occupancy[$date] = 'Block';
                    continue;
                }

                // Check direct bookings
                $directBooking = $listingBookings->first(function ($booking) use ($carbonDate) {
                    $start = Carbon::parse($booking->booking_date_start);
                    $end = Carbon::parse($booking->booking_date_end);
                    return $carbonDate->greaterThanOrEqualTo($start) && $carbonDate->lessThan($end);
                });

                if ($directBooking) {
                    $occupancy[$date] = 'Booked';
                    continue;
                }

                // Check OTA bookings
                $otaBooking = $listingOtaBookings->first(function ($booking) use ($carbonDate, $relatedListingIds) {
                    $start = Carbon::parse($booking->arrival_date);
                    $end = Carbon::parse($booking->departure_date);
                    return in_array($booking->listing_id, $relatedListingIds) &&
                        $carbonDate->greaterThanOrEqualTo($start) && $carbonDate->lessThan($end);
                });

                if ($otaBooking) {
                    $occupancy[$date] = 'Booked';
                    continue;
                }

                $listingRel = ListingRelation::where('listing_id_airbnb', $listing->id)->where('listing_type', 'BookingCom')
                    ->first();
                if ($listingRel) {
                    $listingRel = Listings::where('id', $listingRel->listing_id_other_ota)->first();
                    if ($listingRel) {
                        $listingRelOtaBookings = $otaBookingData[$listingRel->listing_id] ?? collect([]);
                        $otaBooking = $listingRelOtaBookings->first(function ($booking) use ($carbonDate, $relatedListingIds) {
                            $startDate = Carbon::parse($booking->arrival_date);
                            // Exclude checkout date by subtracting 1 day from departure date
                            $endDate = Carbon::parse($booking->departure_date)->subDay();
                            return in_array($booking->listing_id, $relatedListingIds) &&
                                $carbonDate->between($startDate, $endDate);
                        });
                        if ($otaBooking) {
                            $occupancy[$date] = 'Booked';
                            continue;
                        }
                    }
                }

                // Check calendar lock status
                $calendarEntry = $calendarByDate[$date];
                $occupancy[$date] = $calendarEntry->is_lock == 1 ? 'Block' : 'Available';
            }

            $occupancies[] = $occupancy;
        }

        return new Collection($occupancies);
    }
    public function numberToAlphabet($number)
    {
        if ($number < 1 || $number > 26) {
            return "Invalid input. Please enter a number between 1 and 26.";
        }
        return chr(64 + $number);
    }
    private function numberToColumnLetter($index)
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = (int) ($index / 26) - 1;
        }
        return $letters;
    }
    public function styles(Worksheet $sheet)
    {
        $data = $this->collection();
        $styleArray = [
            1 => ['font' => ['bold' => true]],

            'B2' => ['font' => ['italic' => true]],
        ];
        foreach ($data as $key => $item) {
            if ($key == 0) {
                continue;
            }
            $sufKey = 0;
            foreach ($item as $index => $innerItem) {
                $sufKey = $sufKey + 1;
                if ($innerItem == "Available") {
                    if ($sufKey > 26) {
                        $newSufKey = $sufKey - 26;
                        $styleArray['A' . $this->numberToAlphabet($newSufKey) . $key + 1] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '6100cb']]];
                    } else {
                        $styleArray[$this->numberToAlphabet($sufKey) . $key + 1] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '6100cb']]];
                    }
                } else if ($innerItem == "Booked") {
                    if ($sufKey > 26) {
                        $newSufKey = $sufKey - 26;
                        $styleArray['A' . $this->numberToAlphabet($newSufKey) . $key + 1] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '009970']]];
                    } else {
                        $styleArray[$this->numberToAlphabet($sufKey) . $key + 1] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => '009970']]];
                    }
                } else if ($innerItem == "Block") {
                    if ($sufKey > 26) {
                        $newSufKey = $sufKey - 26;
                        $styleArray['A' . $this->numberToAlphabet($newSufKey) . $key + 1] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFF0000']]];
                    } else {
                        $styleArray[$this->numberToAlphabet($sufKey) . $key + 1] = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFF0000']]];
                    }
                }
            }
        }
        return $styleArray;
    }
}

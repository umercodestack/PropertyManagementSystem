<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingOtasDetails;
use App\Models\Bookings;
use App\Models\Calender;
use App\Models\ListingIcalLink;
use App\Models\Listings;
use Eluceo\iCal\Component\Calendar;
use Eluceo\iCal\Component\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class IcalManagementController extends Controller
{
    public function generateIcal(Request $request, $token)
    {
        try {
            $token = trim($token, '.ics'); // Remove .ics extension if present
            Log::channel('cron_gathern')->info("Processing iCal request for token: {$token}");

            $icalLink = ListingIcalLink::where('token', $token)->first();

            if (!$icalLink) {
                Log::channel('cron_gathern')->warning("Invalid iCal token: {$token}");
                return response()->json(['error' => 'Invalid iCal link'], 404);
            }

            Log::channel('cron_gathern')->info("Found iCal link for listing ID: {$icalLink->listing_id}");

            $events = Calender::where('listing_id', $icalLink->listing_id)
                ->where('availability', 0)
                ->where('calender_date', '>', '2025-06-26')
                ->get();

            Log::channel('cron_gathern')->info("Found " . $events->count() . " blocked events");

            $listing = Listings::where('listing_id', $icalLink->listing_id)->first();

            if (!$listing) {
                Log::channel('cron_gathern')->warning("Listing not found for ID: {$icalLink->listing_id}");
                $calendar = new Calendar('-//LivedIn Inc//Hosting Calendar 1.0//EN');
                return $calendar->render();
            }

            $calendar = new Calendar('-//LivedIn Inc//Hosting Calendar 1.0//EN');
            // $calendar->addProperty('CALSCALE', 'GREGORIAN');
            $calendar->setPublishedTTL('P1D');
            $calendar->setTimezone('UTC');
            $currentTimestamp = Carbon::now('Asia/Karachi')->setTimezone('UTC');

            foreach ($events as $event) {
                $livedinBookingData = Bookings::where('listing_id', $listing->id)
                    ->where('booking_date_start', '<=', $event->calender_date)
                    ->where('booking_date_end', '>=', $event->calender_date)
                    ->select('id', 'listing_id', 'booking_date_start', 'booking_date_end')
                    ->first();

                $otaBookingData = BookingOtasDetails::where('listing_id', $listing->listing_id)
                    ->where('arrival_date', '<=', $event->calender_date)
                    ->where('departure_date', '>=', $event->calender_date)
                    ->select('id', 'listing_id', 'arrival_date', 'departure_date')
                    ->first();

                $onlyBookingId = null;
                if ($livedinBookingData && !$otaBookingData) {
                    $onlyBookingId = 'L-' . $livedinBookingData->id;
                } elseif (!$livedinBookingData && $otaBookingData) {
                    $onlyBookingId = 'O-' . $otaBookingData->id;
                }

                $startDate = null;
                $endDate = null;

                try {
                    $startDate = $livedinBookingData && Carbon::canParse($livedinBookingData->booking_date_start)
                        ? new \DateTime($livedinBookingData->booking_date_start)
                        : new \DateTime($event->calender_date);

                    $endDate = $livedinBookingData && Carbon::canParse($livedinBookingData->booking_date_end)
                        ? new \DateTime($livedinBookingData->booking_date_end)
                        : new \DateTime($event->calender_date);

                    $endDate->modify('+1 day'); // iCal end date is exclusive
                } catch (\Exception $e) {
                    Log::channel('cron_gathern')->warning("Invalid date for event ID {$event->id}: {$e->getMessage()}");
                    continue;
                }
                $eventComponent = new \Eluceo\iCal\Component\Event();
                $eventComponent->setUniqueId(uniqid() . '@livedin.co');
                $eventComponent->setSummary('LivedIn (Not available)');
                $eventComponent->setDescription('Blocked due to livedin booking ' . ($onlyBookingId ?? 'Unknown'));
                $eventComponent->setDtStamp(new \DateTime('now', new \DateTimeZone('UTC')));

                $startDateObj = new \DateTime($startDate->format('Y-m-d'));
                $endDateObj = (new \DateTime($endDate->format('Y-m-d')))->modify('+1 day');

                $eventComponent->setUseTimezone(false);
                $eventComponent->setDtStart($startDateObj);
                $eventComponent->setDtEnd($endDateObj);

                $calendar->addComponent($eventComponent);
            }

            $icalOutput = $calendar->render();
            $icalOutput = str_replace("BEGIN:VEVENT", "CALSCALE:GREGORIAN\r\nBEGIN:VEVENT", $icalOutput);
            if (empty($icalOutput) || strpos($icalOutput, 'BEGIN:VEVENT') === false) {
                Log::channel('cron_gathern')->warning("Empty or invalid iCal generated");
                return response()->json(['error' => 'No valid calendar data available'], 404);
            }

            return response($icalOutput, 200, [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'inline; filename="calendar.ics"',
            ]);
        } catch (\Exception $e) {
            Log::channel('cron_gathern')->error("Failed to generate iCal: {$e->getMessage()}, Trace: {$e->getTraceAsString()}");
            return response()->json(['error' => 'Failed to generate iCal'], 500);
        }
    }
}
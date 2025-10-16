<?php

namespace App\Console\Commands;

use App\Models\Calender;
use App\Models\ListingIcalLink;
use App\Models\Listings;
use App\Models\RoomType;
use App\Models\Properties;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use om\IcalParser;
use Carbon\Carbon;
use App\Models\ListingRelation;

class IcalCron extends Command
{
    protected $signature = 'ical:cron';
    protected $description = 'Sync iCal data for active listings';

    public function handle()
    {
        Log::channel('cron')->info("iCal Cron Job started at " . now());

        try {
            $listings = ListingIcalLink::where('active', 1)
                // ->where('listing_id', 1173067429189103741)
                ->get();

            if ($listings->isEmpty()) {
                Log::channel('cron')->info("No active iCal links found for listing ID 1173067429189103741.");
                return self::SUCCESS;
            }

            $availabilityUpdates = [];

            foreach ($listings as $listing) {
                $url = $listing->url;

                try {
                    // Fetch iCal data
                    $response = Http::timeout(30)->get($url);

                    if (!$response->successful()) {
                        Log::channel('cron')->error("Failed to fetch iCal data for listing ID {$listing->listing_id}. Status: {$response->status()}, URL: {$url}");
                        continue;
                    }

                    // Parse iCal data
                    $parser = new IcalParser();
                    $parser->parseString($response->body());
                    $eventsList = $parser->getSortedEvents();

                    if (empty($eventsList)) {
                        Log::channel('cron')->info("No events found in iCal feed for listing ID {$listing->listing_id}, URL: {$url}");
                    }

                    // Collect current blocked dates from iCal
                    $currentBlockedDates = [];
                    $formattedEvents = [];
                    foreach ($eventsList as $event) {
                        $start = $event['DTSTART'] ?? null;
                        $end = $event['DTEND'] ?? null;

                        $formatDate = fn($date) => $date instanceof \DateTime
                            ? $date->format('Y-m-d')
                            : (is_string($date) ? date('Y-m-d', strtotime($date)) : null);

                        $startDate = $formatDate($start);
                        $endDate = $formatDate($end);

                        if (!$startDate || !$endDate) {
                            Log::channel('cron')->warning("Invalid date range for event in listing ID {$listing->listing_id}. Event: " . json_encode($event));
                            continue;
                        }

                        // Collect all dates in the range (excluding end date)
                        $date = Carbon::parse($startDate);
                        $end = Carbon::parse($endDate);
                        while ($date->lt($end)) {
                            $currentBlockedDates[] = $date->format('Y-m-d');
                            $date->addDay();
                        }

                        $formattedEvents[] = [
                            'summary' => $event['SUMMARY'] ?? null,
                            'start' => $startDate,
                            'end' => $endDate,
                            'description' => $event['DESCRIPTION'] ?? null,
                            'location' => $event['LOCATION'] ?? null,
                            'listing_id' => $listing->listing_id,
                        ];
                    }

                    // Log current blocked dates
                    Log::channel('cron')->debug("Current blocked dates for listing ID {$listing->listing_id}: " . json_encode($currentBlockedDates));

                    // Get previously blocked dates from Calender table
                    $previouslyBlockedDates = Calender::where('listing_id', $listing->listing_id)
                        ->where('availability', 0)
                        ->whereRaw('LOWER(block_reason) LIKE ?', ['gathern%'])
                        ->where('calender_date', '>=', now()->startOfDay())
                        ->where('calender_date', '<=', now()->addYears(1))
                        ->pluck('calender_date')
                        ->map(fn($date) => $date instanceof \DateTime ? $date->format('Y-m-d') : $date)
                        ->toArray();

                    // Log previously blocked dates with details
                    Log::channel('cron')->debug("Previously blocked dates for listing ID {$listing->listing_id}: " . json_encode($previouslyBlockedDates));
                    if (empty($previouslyBlockedDates)) {
                        Log::channel('cron')->warning("No previously blocked dates found for listing ID {$listing->listing_id} in Calender table.");
                    }

                    // Identify unblocked dates
                    $unblockedDates = array_diff($previouslyBlockedDates, $currentBlockedDates);
                    Log::channel('cron')->debug("Unblocked dates for listing ID {$listing->listing_id}: " . json_encode($unblockedDates));

                    // Log if no unblocked dates found
                    if (empty($unblockedDates)) {
                        Log::channel('cron')->info("No unblocked dates found for listing ID {$listing->listing_id}.");
                    }

                    // Process unblocked dates
                    $listingData = Listings::where('listing_id', $listing->listing_id)->first();
                    $unblockedRanges = $this->groupDateRanges($unblockedDates);
                    foreach ($unblockedRanges as $range) {
                        // Update Calender table
                        $updatedRows = Calender::where('listing_id', $listing->listing_id)
                            ->whereBetween('calender_date', [$range['start'], $range['end']])
                            ->where('availability', 0)
                            ->whereRaw('LOWER(block_reason) LIKE ?', ['gathern%'])
                            ->update([
                                'availability' => 1,
                                'block_reason' => null,
                            ]);

                        if ($updatedRows > 0) {
                            Log::channel('cron')->info("Restored availability for {$updatedRows} calendar entries for listing ID {$listing->listing_id} from {$range['start']} to {$range['end']}.");
                        } else {
                            Log::channel('cron')->debug("No calendar entries updated for unblocking listing ID {$listing->listing_id} from {$range['start']} to {$range['end']}. Check if records exist with availability=0 and block_reason LIKE 'Gathern%'.");
                        }

                        // Add to availability updates for Airbnb and related OTAs
                        if ($listingData) {
                            $room_type = RoomType::where('listing_id', $listingData->listing_id)->first();
                            if ($room_type) {
                                $property = Properties::where('id', $room_type->property_id)->first();
                                if ($property) {
                                    $availabilityUpdates[] = [
                                        'date_from' => $range['start'],
                                        'date_to' => $range['end'],
                                        'property_id' => $property->ch_property_id,
                                        'room_type_id' => $room_type->ch_room_type_id,
                                        'availability' => 1,
                                    ];
                                }
                            }
                            $listingRelations = ListingRelation::where('listing_id_airbnb', $listingData->id)->get();
                            foreach ($listingRelations as $listingRelation) {
                                $listingDataOtherOta = Listings::where('id', $listingRelation->listing_id_other_ota)->first();
                                if ($listingDataOtherOta) {
                                    $room_type_other_ota = RoomType::where('listing_id', $listingDataOtherOta->listing_id)->first();
                                    if ($room_type_other_ota) {
                                        $property_other = Properties::where('id', $room_type_other_ota->property_id)->first();
                                        if ($property_other) {
                                            $updatedOtaRows = Calender::where('listing_id', $listingDataOtherOta->listing_id)
                                                ->whereBetween('calender_date', [$range['start'], $range['end']])
                                                ->where('availability', 0)
                                                ->whereRaw('LOWER(block_reason) LIKE ?', ['gathern%'])
                                                ->update([
                                                    'availability' => 1,
                                                    'block_reason' => null,
                                                ]);

                                            if ($updatedOtaRows > 0) {
                                                Log::channel('cron')->info("Restored availability for {$updatedOtaRows} calendar entries for related OTA listing ID {$listingDataOtherOta->listing_id} from {$range['start']} to {$range['end']}.");
                                            } else {
                                                Log::channel('cron')->debug("No OTA calendar entries updated for unblocking listing ID {$listingDataOtherOta->listing_id} from {$range['start']} to {$range['end']}.");
                                            }

                                            $availabilityUpdates[] = [
                                                'date_from' => $range['start'],
                                                'date_to' => $range['end'],
                                                'property_id' => $property_other->ch_property_id,
                                                'room_type_id' => $room_type_other_ota->ch_room_type_id,
                                                'availability' => 1,
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Process blocked dates from iCal
                    foreach ($formattedEvents as $event) {
                        if (!$event['start'] || !$event['end']) {
                            Log::channel('cron')->warning("Invalid date range for event in listing ID {$listing->listing_id}. Event: " . json_encode($event));
                            continue;
                        }

                        // Define block reason
                        $blockReason = 'Gathern ' . ($event['DESCRIPTION'] ?? 'No description');

                        // Generate expected dates (excluding end date)
                        $expectedDates = [];
                        $date = Carbon::parse($event['start']);
                        $endDate = Carbon::parse($event['end']);
                        while ($date->lt($endDate)) {
                            $expectedDates[] = $date->format('Y-m-d');
                            $date->addDay();
                        }

                        // Create or update Calender records for blocked dates
                        foreach ($expectedDates as $blockDate) {
                            $existingRecord = Calender::where('listing_id', $listing->listing_id)
                                ->where('calender_date', $blockDate)
                                ->first();

                            if ($existingRecord) {
                                // Update existing record
                                $updatedRows = Calender::where('listing_id', $listing->listing_id)
                                    ->where('calender_date', $blockDate)
                                    ->update([
                                        'availability' => 0,
                                        'block_reason' => $blockReason,
                                    ]);

                                if ($updatedRows > 0) {
                                    Log::channel('cron')->info("Blocked calendar entry for listing ID {$listing->listing_id} on {$blockDate}.");
                                }
                            } else {
                                // Create new record
                                Calender::create([
                                    'listing_id' => $listing->listing_id,
                                    'calender_date' => $blockDate,
                                    'availability' => 0,
                                    'block_reason' => $blockReason,
                                ]);
                                Log::channel('cron')->info("Created and blocked calendar entry for listing ID {$listing->listing_id} on {$blockDate}.");
                            }
                        }

                        // Add to availability updates
                        if ($listingData) {
                            $room_type = RoomType::where('listing_id', $listingData->listing_id)->first();
                            if ($room_type) {
                                $property = Properties::where('id', $room_type->property_id)->first();
                                if ($property) {
                                    $availabilityUpdates[] = [
                                        'date_from' => $event['start'],
                                        'date_to' => Carbon::parse($event['end'])->subDay()->format('Y-m-d'),
                                        'property_id' => $property->ch_property_id,
                                        'room_type_id' => $room_type->ch_room_type_id,
                                        'availability' => 0,
                                    ];
                                }
                            }
                            $listingRelations = ListingRelation::where('listing_id_airbnb', $listingData->id)->get();
                            foreach ($listingRelations as $listingRelation) {
                                $listingDataOtherOta = Listings::where('id', $listingRelation->listing_id_other_ota)->first();
                                if ($listingDataOtherOta) {
                                    $room_type_other_ota = RoomType::where('listing_id', $listingDataOtherOta->listing_id)->first();
                                    if ($room_type_other_ota) {
                                        $property_other = Properties::where('id', $room_type_other_ota->property_id)->first();
                                        if ($property_other) {
                                            foreach ($expectedDates as $blockDate) {
                                                $existingOtaRecord = Calender::where('listing_id', $listingDataOtherOta->listing_id)
                                                    ->where('calender_date', $blockDate)
                                                    ->first();

                                                if ($existingOtaRecord) {
                                                    $updatedOtaRows = Calender::where('listing_id', $listingDataOtherOta->listing_id)
                                                        ->where('calender_date', $blockDate)
                                                        ->update([
                                                            'availability' => 0,
                                                            'block_reason' => $blockReason,
                                                        ]);

                                                    if ($updatedOtaRows > 0) {
                                                        Log::channel('cron')->info("Blocked calendar entry for related OTA listing ID {$listingDataOtherOta->listing_id} on {$blockDate}.");
                                                    }
                                                } else {
                                                    Calender::create([
                                                        'listing_id' => $listingDataOtherOta->listing_id,
                                                        'calender_date' => $blockDate,
                                                        'availability' => 0,
                                                        'block_reason' => $blockReason,
                                                    ]);
                                                    Log::channel('cron')->info("Created and blocked calendar entry for related OTA listing ID {$listingDataOtherOta->listing_id} on {$blockDate}.");
                                                }
                                            }

                                            $availabilityUpdates[] = [
                                                'date_from' => $event['start'],
                                                'date_to' => Carbon::parse($event['end'])->subDay()->format('Y-m-d'),
                                                'property_id' => $property_other->ch_property_id,
                                                'room_type_id' => $room_type_other_ota->ch_room_type_id,
                                                'availability' => 0,
                                            ];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    Log::channel('cron')->info("Successfully synced iCal data for listing ID {$listing->listing_id}.");

                } catch (\Exception $e) {
                    Log::channel('cron')->error("Error processing iCal for listing ID {$listing->listing_id}: {$e->getMessage()}, URL: {$url}, Trace: {$e->getTraceAsString()}");
                    continue;
                }
            }

            // Send single availability update request to Airbnb
            if (!empty($availabilityUpdates)) {
                Log::channel('cron')->debug("Availability updates to be sent to Channex: " . json_encode($availabilityUpdates));
                $response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                    'values' => $availabilityUpdates
                ]);

                if ($response->successful()) {
                    Log::channel('cron')->info("Successfully sent batch availability updates to Airbnb.");
                } else {
                    Log::channel('cron')->error("Failed to send batch availability updates to Airbnb: " . $response->body());
                }
            }

            Log::channel('cron')->info("iCal Cron Job completed successfully at " . now());
            return self::SUCCESS;

        } catch (\Exception $e) {
            Log::channel('cron')->error("iCal Cron Job failed: {$e->getMessage()}, Trace: {$e->getTraceAsString()}");
            return self::FAILURE;
        }
    }

    /**
     * Group consecutive dates into ranges.
     *
     * @param array $dates
     * @return array
     */
    private function groupDateRanges(array $dates): array
    {
        if (empty($dates)) {
            return [];
        }

        sort($dates);
        $ranges = [];
        $start = $current = Carbon::parse($dates[0]);
        $end = $start->copy();

        foreach ($dates as $index => $date) {
            $date = Carbon::parse($date);
            if ($date->diffInDays($current) <= 1) {
                $end = $date;
                $current = $date;
            } else {
                $ranges[] = [
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ];
                $start = $current = $date;
                $end = $date;
            }
            if ($index === count($dates) - 1) {
                $ranges[] = [
                    'start' => $start->format('Y-m-d'),
                    'end' => $end->format('Y-m-d'),
                ];
            }
        }

        return $ranges;
    }
}
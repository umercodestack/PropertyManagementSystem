<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{
    Listing,
    AirbnbImage,
    AirbnbListing,
    Channels,
    AirbnbRoom
};
use App\Services\ChannexProxyService;

class SyncAirbnbListingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:airbnb-listings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting the listings sync process...');
        // $listings = Listing::where('listing_id', 1201931695052170595)->get();
        $listings = Listing::
            offset(0)
            ->limit(10)
            ->get();
        $channexProxyService = new ChannexProxyService();

        foreach ($listings as $listing) {
            try {
                $channel = Channels::find($listing->channel_id);
                $listingId = $listing->listing_id;
                $getPayload = [
                    'request' => [
                        'endpoint' => "/listings/{$listingId}",
                        'method' => 'get',
                    ],
                ];

                try {
                    $listingResponse = $channexProxyService->postToProxy($channel->ch_channel_id, $getPayload);
                } catch (\Exception $e) {
                    \Log::error("Error fetching listing details for ID {$listingId}: {$e->getMessage()}");
                    continue;
                }

                if (!empty($listingResponse['data']['listing'])) {
                    $listingData = $listingResponse['data']['listing'] ?? null;

                    try {
                        $airbnbListing = AirbnbListing::where('listing_id', $listingId)->first();
                        if (!$airbnbListing) {
                            $airbnbListing = AirbnbListing::create([
                                'channel_id' => $channel->ch_channel_id,
                                'listing_id' => $listingId,
                                'name' => $listingResponse['data']['listing']['name'] ?? '-',
                                'user_id' => 1,
                            ]);
                        }

                        // Sync Description
                        $descriptionPayload = [
                            'request' => [
                                'endpoint' => "/listing_descriptions?listing_id={$listingId}",
                                'method' => 'get',
                            ],
                        ];
                        try {
                            $descriptionResponse = $channexProxyService->postToProxy($channel->ch_channel_id, $descriptionPayload);
                            if (!empty($descriptionResponse['data']['listing_descriptions'][0])) {
                                $airbnbListing->description = json_encode($descriptionResponse['data']['listing_descriptions'][0]);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error fetching descriptions for listing ID {$listingId}: {$e->getMessage()}");
                        }

                        // Sync Prices
                        $pricesPayload = [
                            'request' => [
                                'endpoint' => "/pricing_and_availability/standard/pricing_settings/{$listingId}",
                                'method' => 'get',
                            ],
                        ];
                        try {
                            $pricesResponse = $channexProxyService->postToProxy($channel->ch_channel_id, $pricesPayload);
                            if (!empty($pricesResponse['data']['pricing_setting'])) {
                                $airbnbListing->prices = json_encode($pricesResponse['data']['pricing_setting']);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error fetching prices for listing ID {$listingId}: {$e->getMessage()}");
                        }

                        
                        // Sync Booking Settings
                        $bookingPayload = [
                            'request' => [
                                'endpoint' => "/booking_settings/{$listingId}",
                                'method' => 'get',
                            ],
                        ];
                        try {
                            $bookingResponse = $channexProxyService->postToProxy($channel->ch_channel_id, $bookingPayload);
                            if (!empty($bookingResponse['data']['booking_setting'])) {
                                $airbnbListing->booking_setting = json_encode($bookingResponse['data']['booking_setting']);
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error fetching prices for listing ID {$listingId}: {$e->getMessage()}");
                        }

                        // Sync Images
                        $imagesPayload = [
                            'request' => [
                                'endpoint' => "/listing_photos?listing_id={$listingId}",
                                'method' => 'get',
                            ],
                        ];
                        try {
                            $imagesResponse = $channexProxyService->postToProxy($channel->ch_channel_id, $imagesPayload);
                            if (!empty($imagesResponse['data']['listing_photos'])) {
                                AirbnbImage::where('listing_id', $listingId)->delete();
                                $images = $imagesResponse['data']['listing_photos'];
                                foreach ($images as $image) {
                                    AirbnbImage::create([
                                        'listing_id' => $listingId,
                                        'airbnb_image_id' => $image['id'],
                                        'url' => $image['extra_medium_url'],
                                        'description' => null,
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error fetching images for listing ID {$listingId}: {$e->getMessage()}");
                        }

                        // Sync Rooms
                        $roomsPayload = [
                            'request' => [
                                'endpoint' => "/listing_rooms?listing_id={$listingId}",
                                'method' => 'get',
                            ],
                        ];
                        try {
                            $roomsResponse = $channexProxyService->postToProxy($channel->ch_channel_id, $roomsPayload);
                            if (!empty($roomsResponse['data']['listing_rooms'])) {
                                AirbnbRoom::where('listing_id', $listingId)->delete();
                                $rooms = $roomsResponse['data']['listing_rooms'];
                                foreach ($rooms as $room) {
                                    AirbnbRoom::create([
                                        'listing_id' => $listingId,
                                        'airbnb_room_id' => $room['id'],
                                        'room_number' => $room['room_number'],
                                        'beds' => json_encode($room['beds']),
                                        'room_amenities' => json_encode($room['room_amenities']),
                                        'room_type' => $room['room_type']
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            \Log::error("Error fetching rooms for listing ID {$listingId}: {$e->getMessage()}");
                        }

                        $airbnbListing->details = json_encode($listingData);
                        $airbnbListing->save();
                    } catch (\Exception $e) {
                        \Log::error("Error processing listing ID {$listingId}: {$e->getMessage()}");
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Error processing channel or listing for ID {$listing->id}: {$e->getMessage()}");
            }
        }

        $this->info('Listings sync process has completed...');
    }

}

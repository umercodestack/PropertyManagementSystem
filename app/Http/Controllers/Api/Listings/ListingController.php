<?php

namespace App\Http\Controllers\Api\Listings;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirbnbListingResource;
use App\Models\AirbnbImage;
use App\Models\AirbnbListing;
use App\Models\Channels;
use App\Models\RatePlan;
use App\Models\Properties;
use App\Services\ChannexProxyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Listing;

class ListingController extends Controller
{
    protected $channexProxyService;
    protected $channelId = "009c5c69-c40b-4ae2-9026-f5351d468895";

    public function __construct(ChannexProxyService $channexProxyService)
    {
        $this->channexProxyService = $channexProxyService;
    }

    public function index()
    {
        $listings = AirbnbListing::all();
        return AirbnbListingResource::collection($listings);
    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $channel = Channels::findOrFail($request->channel_id);
        // Prepare the data for the API request
        $data = [
            'request' => [
                'endpoint' => '/listings',
                'method' => 'post',
                'payload' => [
                    'name' => $request->name,
                ],
            ],
        ];

        // // Call the service to send the proxy request
        $response = $this->channexProxyService->postToProxy($channel->ch_channel_id, $data);

        // Check for errors and handle the response
        if ($response->failed()) {
            return response()->json([
                'error' => 'Request failed',
                'details' => $response->json(),
            ], 400);
        }
        
        // $data = $response->json();
        if (empty($response['data']['id'])) {
            return response()->json([
                "error" => !empty($response['errors']['error_message']) ? $response['errors']['error_message'] : "Something Went Wrong!",
            ], 500);
        }

        $listingId = $response['data']['id'];

        $listing = AirbnbListing::create([
            'channel_id' => $channel->ch_channel_id,
            'listing_id' => $listingId,
            'name' => $request->name,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json([
            "message" => "Listing Created!",
            "id" => $listingId,
        ]);

        $payload = $request->all();

        $fieldsToCheck = ['lat', 'lng', 'directions', 'amenities', 'permit_or_tax_id', 'property_external_id', 'house_manual', 'listing_nickname', 'wifi_network', 'wifi_password', 'deactivation_reason', 'deactivation_details'];

        foreach ($fieldsToCheck as $field) {
            if (is_null($payload[$field] ?? null)) {
                unset($payload[$field]);
            }
        }

        $putData = [
            'request' => [
                'endpoint' => "/listings/{$listingId}",
                'method' => 'put',
                'payload' => $payload,
            ],
        ];
        // return $putData;
        $response = $this->channexProxyService->postToProxy($channel->ch_channel_id, $putData);

        if (empty($response['errors'])) {

            $getPayload = [
                'request' => [
                    'endpoint' => "/listings/{$listingId}",
                    'method' => 'get',
                ],
            ];

            $listingResponse = $this->channexProxyService->postToProxy($listing->channel_id, $getPayload);
            $listingData = $listingResponse['data']['listing'] ?? null;

            if ($listingData) {
                $listing->details = $listingData ? json_encode($listingData) : null;
                $listing->save();
            }

            return response()->json([
                'message' => 'Listing Created!',
                'data' => $response->json(),
            ], 200);
        }

        return response()->json([
            "error" => !empty($response['errors']['error_message']) ? $response['errors']['error_message'] : "Something Went Wrong!",
        ], 500);
    }

    public function updateDescription($listingId, Request $request)
    {
        $channel = Channels::findOrFail($request->channel_id);

        $validated = $request->validate([
            'summary' => 'nullable|string',
            'space' => 'nullable|string',
            'access' => 'nullable|string',
            'interaction' => 'nullable|string',
            'neighborhood_overview' => 'nullable|string',
            'transit' => 'nullable|string',
            'notes' => 'nullable|string',
            'house_rule' => 'nullable|string',
        ]);

        if (empty(array_filter($validated))) {
            return response()->json([
                'error' => 'At least one of the following fields is required: summary, space, access, interaction, neighborhood_overview, transit, notes, house_rule.',
            ], 400);
        }

        try {
            $listing = AirbnbListing::where('listing_id', $listingId)->first();
            if(!$listing)
            {
                $listing = AirbnbListing::create([
                    'channel_id' => $channel->ch_channel_id,
                    'listing_id' => $listingId,
                    'name' => $request->name,
                    'user_id' => auth()->user()->id,
                ]);
            }

            $request->merge(['listing_id' => $listingId]);
            $listingDescription[0] = $request->all();
            $payload = [
                'listing_descriptions' => $listingDescription,
            ];

            $deleteData = [
                'request' => [
                    'endpoint' => "/listing_descriptions/{$listingId}/EN",
                    'method' => 'delete',
                ],
            ];

            $this->channexProxyService->postToProxy($listing->channel_id, $deleteData);

            $data = [
                'request' => [
                    'endpoint' => "/listing_descriptions/{$listingId}",
                    'method' => 'put',
                    'payload' => $payload,
                ],
            ];

            $response = $this->channexProxyService->postToProxy($listing->channel_id, $data);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Request failed',
                    'details' => $response->json(),
                ], 400);
            }

            $listing->description = json_encode($request->all());
            $listing->save();

            return response()->json([
                "success" => true,
                "message" => "Description Updated!"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePrices($listingId, Request $request)
    {
        $channel = Channels::findOrFail($request->channel_id);

        $validatedData = $request->validate([
            'listing_currency' => 'required',
            'default_daily_price' => 'required',
        ]);

        try {
            $listing = AirbnbListing::where('listing_id', $listingId)->first();
            if(!$listing)
            {
                $listing = AirbnbListing::create([
                    'channel_id' => $channel->ch_channel_id,
                    'listing_id' => $listingId,
                    'name' => $request->name,
                    'user_id' => auth()->user()->id,
                ]);
            }

            // Set pricing model
            $this->channexProxyService->postToProxy($listing->channel_id, [
                'request' => [
                    'endpoint' => "/pricing_and_availability/{$listingId}",
                    'method' => 'put',
                    'payload' => [
                        "pricing_availability_model_type" => "STANDARD",
                        // "in_model_transition" => true,
                        // "clear_incompatible_settings" => true,
                    ],
                ],
            ]);

            // Update listing currency
            $this->channexProxyService->postToProxy($listing->channel_id, [
                'request' => [
                    'endpoint' => "/pricing_and_availability/standard/pricing_settings/{$listingId}",
                    'method' => 'put',
                    'payload' => [
                        "listing_currency" => $validatedData['listing_currency'],
                    ],
                ],
            ]);

            // Prepare payload based on pass-through tax amount
            $payload = $request->input('pass_through_taxes.0.amount', 0) > 0
            ? $request->except('listing_currency')
            : ["default_daily_price" => $validatedData['default_daily_price']];

            // Update pricing settings
            $response = $this->channexProxyService->postToProxy($listing->channel_id, [
                'request' => [
                    'endpoint' => "/pricing_and_availability/standard/pricing_settings/{$listingId}",
                    'method' => 'put',
                    'payload' => $payload,
                ],
            ]);

            if ($response->failed()) {
                return response()->json([
                    "error" => $response->json('errors.error_message') ?? "Something Went Wrong!",
                ], 500);
            }

            $listing->prices = json_encode($request->all());
            $listing->save();

            return response()->json(['listing_id' => $listingId], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getImages($listingId)
    {
        return AirbnbImage::where('listing_id', $listingId)->get();
    }

    public function getBookingSettings($listingId) {

    }

    // public function addImage(Request $request)
    // {

    //     $request->validate([
    //         'image' => 'required|file|mimes:jpg,jpeg,png|max:2048',
    //     ]);

    //     try {

    //         $listing = AirbnbListing::where('listing_id', $request->listing_id)->firstOrFail();
    //         $file = $request->file('image');
    //         $base64Image = base64_encode(file_get_contents($file));

    //         $data = [
    //             'request' => [
    //                 'endpoint' => "/listing_photos",
    //                 'method' => 'post',
    //                 'payload' => [
    //                     'listing_id' => $request->listing_id,
    //                     'image' => $base64Image,
    //                     'caption' => $request->description ?? null,
    //                 ],
    //             ],
    //         ];

    //         $response = $this->channexProxyService->postToProxy($listing->channel_id, $data);

    //         if ($response->failed()) {
    //             return response()->json([
    //                 'error' => 'Request failed',
    //                 'details' => $response->json(),
    //             ], 400);
    //         }

    //         $photoId = $response['data']['id'] ?? null;
    //         if ($photoId) {
    //             $imagePayload = [
    //                 'request' => [
    //                     'endpoint' => "/listing_photos/{$photoId}",
    //                     'method' => 'get',
    //                 ],
    //             ];

    //             $imageResponse = $this->channexProxyService->postToProxy($listing->channel_id, $imagePayload);
    //             $imageUrl = $imageResponse['data']['listing_photo']['extra_medium_url'] ?? null;

    //             if ($imageUrl) {
    //                 $image = AirbnbImage::create([
    //                     'listing_id' => $request->listing_id,
    //                     'airbnb_image_id' => $photoId,
    //                     'url' => $imageUrl,
    //                     'description' => $request->description ?? null,
    //                 ]);

    //                 return response()->json([
    //                     "data" => $image,
    //                     "message" => "Image Uploaded!",
    //                 ], 200);
    //             }
    //         }

    //         return response()->json([
    //             'error' => 'Image ID or URL missing',
    //         ], 400);

    //     } catch (\Exception $e) {
    //         Log::error('Error uploading image: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Failed to upload image.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    
    public function addImage(Request $request)
    {
        logger("ADD IMAGE REQUEST..". json_encode($request->all()));
        $request->validate([
            'images' => 'required|array',
            'images.*.url' => 'required|string',
            'images.*.airbnb_image_id' => 'nullable|string',
            'images.*.description' => 'nullable|string',
        ]);

        try {
            $listing = AirbnbListing::where('listing_id', $request->listing_id)->firstOrFail();

            $uploadedImages = [];
            $deleteImageIds = [];

            foreach ($request->images as $imageData) {
                $description = $imageData['description'] ?? null;

                if (!empty($imageData['airbnb_image_id'])) {
                    $finalImageUrl = $imageData['url'];
                    $deleteImageIds[] = $imageData['airbnb_image_id'];

                    $dbImage = AirbnbImage::where('airbnb_image_id', $imageData['airbnb_image_id'])->first();

                    if (!$dbImage) {
                        $uploadedImages[] = AirbnbImage::create([
                            'listing_id' => $request->listing_id,
                            'airbnb_image_id' => $imageData['airbnb_image_id'],
                            'url' => $finalImageUrl,
                            'description' => $description,
                        ]);
                    }
                } else {
                    $base64Image = $imageData['url'];
                    $data = [
                        'request' => [
                            'endpoint' => "/listing_photos",
                            'method' => 'post',
                            'payload' => [
                                'listing_id' => $request->listing_id,
                                'image' => $base64Image,
                                'caption' => $description,
                            ],
                        ],
                    ];

                    $response = $this->channexProxyService->postToProxy($listing->channel_id, $data);

                    if ($response->failed()) {
                        return response()->json([
                            'error' => 'Request failed for one or more images',
                            'details' => $response->json(),
                        ], 400);
                    }

                    $photoId = $response['data']['id'] ?? null;

                    if ($photoId) {
                        $imagePayload = [
                            'request' => [
                                'endpoint' => "/listing_photos/{$photoId}",
                                'method' => 'get',
                            ],
                        ];

                        $imageResponse = $this->channexProxyService->postToProxy($listing->channel_id, $imagePayload);
                        $finalImageUrl = $imageResponse['data']['listing_photo']['extra_medium_url'] ?? null;

                        if ($finalImageUrl) {
                            $uploadedImages[] = AirbnbImage::create([
                                'listing_id' => $request->listing_id,
                                'airbnb_image_id' => $photoId,
                                'url' => $finalImageUrl,
                                'description' => $description,
                            ]);
                        }
                    }
                }
            }

            $deleteImgs = AirbnbImage::where('listing_id', $request->listing_id)
                ->whereNotIn('airbnb_image_id', $deleteImageIds);
                
              $deleteImages = $deleteImgs->get();

            foreach ($deleteImages as $img) {
                $deleteResponse = $this->channexProxyService->postToProxy($listing->channel_id, [
                    'request' => [
                        'endpoint' => "/listing_photos/{$img->airbnb_image_id}",
                        'method' => 'delete',
                    ],
                ]);

            }
            
            $deleteImgs->delete();

            return response()->json([
                'data' => $uploadedImages,
                'message' => 'Images Uploaded!',
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error uploading images: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $listing = AirbnbListing::with(['images', 'rooms'])->where('listing_id', $id)->first();
        return new AirbnbListingResource($listing);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */

    public function update(Request $request, $id)
    {
        $listingId = $id;
        $listing = AirbnbListing::where('listing_id', $listingId)->first();
        if(!$listing) {
            return response([
                "error" => "Listing not found!"
            ], 500);
        }

        $channel = Channels::where('ch_channel_id', $listing->channel_id)->first();
        if(!$channel) {
            return response([
                "error" => "Channel id is incorrect!"
            ], 500);
        }
        
        $request->validate([
            'name' => 'required',
            // 'property_type_group' => 'required',
            'property_type_category' => 'required',
            'room_type_category' => 'required',
            "bedrooms" => 'required',
            "bathrooms" => 'required',
            "beds" => 'required',
            "check_in_option" => 'nullable',
            "apt" => 'required',
            "street" => 'required',
            "city" => 'required',
            "state" => 'required',
            "zipcode" => 'required',
            "country_code" => 'required',
            "person_capacity" => 'required',
        ]);

        if(!$listing)
        {
            $listing = AirbnbListing::create([
                'channel_id' => $channel->ch_channel_id,
                'listing_id' => $listingId,
                'name' => $request->name,
                'user_id' => auth()->user()->id,
            ]);
        }

        $payload = $request->except(['channel_id', 'booking_setting']);
        $details = $listing->details ? json_decode($listing->details, true) : []; 
        $amenities = [];
        $updatedAmenities = [];

        // return $payload;
        
        if (!empty($details)) {
            // $details = array_merge($details, $payload);
            // $amenities = array_map('strtolower', array_keys($details['amenities']));
            $amenities = $details['amenities'];

            $updatedAmenities = [];
            foreach ($amenities as $key => $value) {
                $updatedAmenities[strtolower($key)] = $value;
            }

            foreach ($request['amenities'] as $reqAmenity) {
                $key = strtolower($reqAmenity);
                $updatedAmenities[$key] = [
                    "instruction" => "",
                    "is_present" => true
                ];
            }

            foreach ($updatedAmenities as $key => &$value) {
                if (!in_array($key, array_map('strtolower', $request['amenities']))) {
                    $value['is_present'] = false;
                }
            }

            $payload['amenities'] = $updatedAmenities;
        }

        
        // $fieldsToCheck = ['lat', 'lng', 'directions', 'permit_or_tax_id', 'property_external_id', 'house_manual', 'listing_nickname', 'wifi_network', 'wifi_password', 'deactivation_reason'];

        // foreach ($fieldsToCheck as $field) {
        //     if (is_null($payload[$field] ?? null)) {
        //         unset($payload[$field]);
        //     }
        // }

        $putData = [
            'request' => [
                'endpoint' => "/listings/{$listingId}",
                'method' => 'put',
                'payload' => $payload,
            ],
        ];
        
        // return $putData;
        $updateResponse = $this->channexProxyService->postToProxy($listing->channel_id, $putData);
        
        if (empty($updateResponse['errors'])) {
            if(!empty($request->booking_setting)) {
                $bookingSetting = $request->booking_setting;
                $listingExpectations = [
                    "listing_expectation" => [
                        "type" => $bookingSetting['listing_expectation_type'] ?? null
                    ]
                ];

                $listingExpectations = [
                    [
                        "listing_expectation" => [
                            "added_details" => "",
                            "type" => "potential_noise",
                        ],
                    ],
                ];
                
              
                // return $bookingData;
                // $bookingPayload = [
                //     'request' => [
                //         'endpoint' => "/booking_settings/{$listingId}",
                //         'method' => 'put',
                //         'payload' => $bookingData
                //     ],
                // ];
        
                // return $bookingResponse = $this->channexProxyService->postToProxy($listing->channel_id, $bookingPayload);

                // if(empty($bookingResponse['errors'])) {
                //     $listing->booking_setting = json_encode($bookingData);
                // }

                $rate_plan = RatePlan::where('listing_id', $listing->listing_id)->first();

                if(!empty($rate_plan)) {
                    $property = Properties::where('id', $rate_plan->property_id)->first();
                    if(!empty($property)) {
                        $response = Http::withHeaders([
                            'user-api-key' => env('CHANNEX_API_KEY'),
                        ])->get(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id");
                        if ($response->successful()) {
                            $response = $response->json();
                            $channel_rate_plan = '';
                            $channex_channel_rate_plan = $response['data']['attributes']['rate_plans'];

                            foreach ($channex_channel_rate_plan as $item) {
                                if ($item['rate_plan_id'] == $rate_plan['ch_rate_plan_id']) {
                                    $channel_rate_plan = $item;
                                    break;
                                }
                            }
                            
                            if(!empty($channel_rate_plan['id'])) {
                                // $response = Http::withHeaders([
                                //     'user-api-key' => env('CHANNEX_API_KEY'),
                                // ])->get(env('CHANNEX_URL') . "/api/v1/channels/".$channel->ch_channel_id."/action/listing_details?listing_id=$listingId");

                                // $response = Http::withHeaders([
                                //     'user-api-key' => env('CHANNEX_API_KEY'),
                                // ])->get(env('CHANNEX_URL') . "/api/v1/channels/009c5c69-c40b-4ae2-9026-f5351d468895/action/listing_details?listing_id=1304992673922000022");
                        
                                $bookingData = [
                                    "check_in_time_end" => $channel_rate_plan['settings']['booking_setting']['check_in_time_end'],
                                    "check_in_time_start" => $channel_rate_plan['settings']['booking_setting']['check_in_time_start'],
                                    "check_out_time" => $channel_rate_plan['settings']['booking_setting']['check_out_time'],
                                    "cancellation_policy_settings" => [
                                        "cancellation_policy_category" => $bookingSetting['cancellation_policy_category'] ?? null,
                                    ],
                                    "instant_booking_allowed_category" => $bookingSetting['instant_booking_allowed_category'] ?? null,
                                    // "listing_expectations_for_guests" => $listingExpectations
                                ];
                
                                
                                $response = Http::withHeaders([
                                    'user-api-key' => env('CHANNEX_API_KEY'),
                                ])->put(env('CHANNEX_URL') . "/api/v1/channels/$channel->ch_channel_id/execute/update_booking_setting", [
                                            "channel_rate_plan_id" => $channel_rate_plan['id'],
                                            "data" => $bookingData
                                        ]);
                                return $response;
                                if ($response->successful()) {
                                    $response = $response->json();

                                    if(!empty($response['data'])) {
                                        $listing->booking_setting = json_encode($response['data']);
                                    }
                                }
                            }
                        }
                    }
                }

            }
            
            $getPayload = [
                'request' => [
                    'endpoint' => "/listings/{$listingId}",
                    'method' => 'get',
                ],
            ];

            $listingResponse = $this->channexProxyService->postToProxy($listing->channel_id, $getPayload);
            $listingData = $listingResponse['data']['listing'] ?? null;
            
            if ($listingData) {
                $listing->name = $request->name;
                $listing->details = json_encode($listingData);
                $listing->save();
            }
            
            // $dbListing = Listing::where('listing_id', $listingId)->first();
            // if($dbListing) {
            //     $dbListing->listing_json;
            //     $listingJson = json_decode($dbListing->listing_json);
            //     $listingJson->title = $listingData['name'];
            //     $dbListing->update([
            //         "listing_json" => json_encode($listingJson)
            //     ]);
            // }

            return response()->json([
                'message' => 'Listing Updated!',
                'data' => $updateResponse->json(),
            ], 200);
        }

        return response()->json([
            "message" => !empty($updateResponse['errors']['error_message']) ? $updateResponse['errors']['error_message'] : "Something Went Wrong!",
        ], 500);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $airbnbListing = AirbnbListing::where('listing_id', $id)->firstOrFail();
            $listing = $airbnbListing->listing;

            if ($listing) {
                $listing->channexSetting()->delete();
                $listing->calendars()->delete();
                $listing->bookings()->delete();
                $listing->bookingsOTAS()->delete();
            }

            $airbnbListing->images()->delete();
            $airbnbListing->rooms()->delete();
            $airbnbListing->delete();

            if ($listing) {
                $listing->delete();

                $this->channexProxyService->postToProxy($listing->channel_id, [
                    'request' => [
                        'endpoint' => "/listings/{$id}",
                        'method' => 'delete',
                    ],
                ]);

                return response()->json("Listing Deleted!", 200);
            }

            return response()->json(["error" => "Listing deletion incomplete."], 500);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete listing: ' . $e->getMessage()], 500);
        }
    }

    public function deleteImage($id)
    {
        try {
            $image = AirbnbImage::findOrFail($id);
            $listing = AirbnbListing::where('listing_id', $image->listing_id)->firstOrFail();

            $data = [
                'request' => [
                    'endpoint' => "/listing_photos/{$image->airbnb_image_id}",
                    'method' => 'delete',
                ],
            ];

            $response = $this->channexProxyService->postToProxy($listing->channel_id, $data);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Request failed',
                    'details' => $response->json(),
                ], 400);
            }

            $image->delete();

            return response()->json(['message' => 'Image deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function testTriggers(Request $request) {
        return $request;
    }
}

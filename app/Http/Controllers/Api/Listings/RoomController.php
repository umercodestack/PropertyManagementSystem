<?php

namespace App\Http\Controllers\Api\Listings;

use App\Http\Controllers\Controller;
use App\Models\AirbnbListing;
use App\Models\AirbnbRoom;
use App\Models\Channels;
use App\Services\ChannexProxyService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $channexProxyService;
    protected $channelId = "009c5c69-c40b-4ae2-9026-f5351d468895";

    public function __construct(ChannexProxyService $channexProxyService)
    {
        $this->channexProxyService = $channexProxyService;
    }

    /**
     * Show the form for creating a new resource.
     */

    public function index(Request $request)
    {
        return AirbnbRoom::where('listing_id', $request->listing_id)->get();
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
            'channel_id' => 'required',
            'listing_id' => 'required',
            'room_number' => 'required',
            'room_type' => 'required',
        ]);

        $channel = Channels::findOrFail($request->channel_id);
        try {

            $listing = AirbnbListing::where('listing_id', $request->listing_id)->first();
            if(!$listing)
            {
                $listing = AirbnbListing::create([
                    'channel_id' => $channel->ch_channel_id,
                    'listing_id' => $listingId,
                    'name' => $request->name,
                    'user_id' => auth()->user()->id,
                ]);
            }

            $data = [
                'request' => [
                    'endpoint' => "/listing_rooms",
                    'method' => 'post',
                    'payload' => [
                        'listing_id' => $request->listing_id,
                        'room_number' => $request->room_number,
                        'beds' => $request->beds,
                        'room_amenities' => $request->room_amenities,
                        'room_type' => $request->room_type,
                        'is_private' => $request->is_private,
                        'metadata' => $request->metadata ?? null,
                    ],
                ],
            ];
            // return $data;
            $response = $this->channexProxyService->postToProxy($listing->channel_id, $data);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Request failed',
                    'details' => $response->json(),
                ], 400);
            }

            if (!empty($response['data']['id'])) {
                $room = AirbnbRoom::updateOrCreate(
                    [
                        'listing_id' => $request->listing_id,
                        'room_number' => $request->room_number,
                    ],
                    [
                        'airbnb_room_id' => $response['data']['id'],
                        'beds' => json_encode($request->beds ?? null),
                        'room_amenities' => json_encode($request->room_amenities ?? null),
                        'room_type' => $request->room_type ?? null,
                        'is_private' => $request->is_private ?? false,
                        'metadata' => $request->metadata ?? null,
                    ]
                );

                return $room;
            }

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        $request->validate([
            'room_number' => 'required',
            'room_type' => 'required',
        ]);

        try {
            $listing = AirbnbListing::where('listing_id', $request->listing_id)->first();
            if (!$listing) {
                return response()->json('Listing Not Found!', 404);
            }

            $room = AirbnbRoom::find($id);
            if (!$room) {
                return response()->json('Room Not Found!', 404);
            }

            $data = [
                'request' => [
                    'endpoint' => "/listing_rooms/{$request->listing_id}/{$room->airbnb_room_id}",
                    'method' => 'put',
                    'payload' => [
                        'listing_id' => $request->listing_id,
                        'room_number' => $request->room_number,
                        'beds' => $request->beds,
                        'room_amenities' => $request->room_amenities,
                        'room_type' => $request->room_type,
                        'is_private' => $request->is_private,
                        'metadata' => $request->metadata ?? null,
                    ],
                ],
            ];

            $response = $this->channexProxyService->postToProxy($listing->channel_id, $data);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Request failed',
                    'details' => $response->json(),
                ], 400);
            }

            $room->update([
                'beds' => json_encode($request->beds ?? null),
                'room_amenities' => json_encode($request->room_amenities ?? null),
                'room_type' => $request->room_type ?? null,
                'is_private' => $request->is_private ?? false,
                'metadata' => $request->metadata ?? null,
            ]);

            return $room;
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $room = AirbnbRoom::findOrFail($id);
            $listing = AirbnbListing::where('listing_id', $room->listing_id)->firstOrFail();

            $data = [
                'request' => [
                    'endpoint' => "/listing_rooms/{$listing->listing_id}/{$room->airbnb_room_id}",
                    'method' => 'delete',
                ],
            ];

            $response = $this->channexProxyService->postToProxy($this->channelId, $data);

            if ($response->failed()) {
                return response()->json([
                    'error' => 'Request failed',
                    'details' => $response->json(),
                ], 400);
            }

            $room->delete();

            return response()->json(['message' => 'Room deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An error occurred',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}

<?php

namespace App\Http\Controllers\Api\Apartments;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApartmentResource;
use App\Http\Resources\ListingUrlsResource;
use App\Models\ApartmentAddress;
use App\Models\ApartmentImages;
use App\Models\Apartments;
use App\Models\ListingUrl;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

class ApartmentController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $apartments = Apartments::all();
        return ApartmentResource::collection($apartments);
    }

    /**
     * @param Request $request
     * @return JsonResponse|ApartmentResource
     */
    public function store(Request $request): JsonResponse|ApartmentResource
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'apartment_type' => 'required|string',
            'rental_type' => 'required|string',
            'description' => 'required|string',
            'title' => 'required|string',
            'max_guests' => 'required|integer',
            'bedrooms' => 'required|integer',
            'beds' => 'required|integer',
            'bathrooms' => 'required|integer',
            'latitude' => 'required|integer',
            'longitude' => 'required|integer',
            'country' => 'required|string',
            'address_line' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal' => 'required|integer',
            'apartment_image' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $data = $request->all();
        $data['channex_json'] = '{1}';
        $apartment = Apartments::create($data);

        ApartmentAddress::create([
            'apartment_id' => $apartment->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'country' => $request->country,
            'address_line' => $request->address_line,
            'city' => $request->city,
            'province' => $request->province,
            'postal' => $request->postal,
        ]);

        ApartmentImages::create([
            'apartment_id' => $apartment->id,
            'apartment_image' => 'sadsad/asdsa/png'
        ]);

        return new ApartmentResource($apartment);
    }

    public function getApartmentByUser(User $user)
    {
        $apartments = Apartments::where('user_id', $user->id)->get();
        return ApartmentResource::collection($apartments);

    }

    /**
     * @param Apartments $apartment
     * @return ApartmentResource
     */
    public function show(Apartments $apartment): ApartmentResource
    {
        return new ApartmentResource($apartment);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Apartments $apartments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Apartments $apartments)
    {
        $validator = Validator::make($request->all(), [
            'apartment_type' => 'required|string',
            'rental_type' => 'required|string',
            'description' => 'required|string',
            'title' => 'required|string',
            'max_guests' => 'required|integer',
            'bedrooms' => 'required|integer',
            'beds' => 'required|integer',
            'bathrooms' => 'required|integer',
            'latitude' => 'required|integer',
            'longitude' => 'required|integer',
            'country' => 'required|string',
            'address_line' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $apartments->update($request);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Apartments $apartments)
    {
        //
    }

    /**
     * @param Request $request
     * @return JsonResponse|ListingUrlsResource
     */
    public function createListingUrl(Request $request): JsonResponse|ListingUrlsResource
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'platform' => 'required|string',
            'url' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $data['user_id'] = $request->user_id;
        $data['platform'] = $request->platform;
        $data['url'] = json_encode($request->url);
        $listingUrl = ListingUrl::create($data);
        return new ListingUrlsResource($listingUrl);
    }
}
 
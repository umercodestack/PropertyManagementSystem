<?php

namespace App\Http\Controllers\Api\Apartments;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApartmentPriceResource;
use App\Models\ApartmentPrices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ApartmentPriceController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $userApiKey = env('CHANNEX_API_KEY');
        $response = Http::withHeaders([
            'user-api-key' => $userApiKey,
        ])->get(env('CHANNEX_URL')."/api/v1/channels/$items->ch_channel_id");

        if ($response->successful()) {
//            $response = $response->json();
            return redirect()->route('channel-management.index');
        }
        else {
            $error = $response->body();
            return redirect()->route('channel-management.index')->with('error', $error);
        }
        return ApartmentPriceResource::collection(ApartmentPrices::all());
    }

    /**
     * @param Request $request
     * @return ApartmentPriceResource|JsonResponse
     */

    public function store(Request $request): JsonResponse|ApartmentPriceResource
    {
        $validator = Validator::make($request->all(), [
            'apartment_id' => 'required',
            'discount_id' => 'required',
            'price' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        return new ApartmentPriceResource(ApartmentPrices::create($request->all()));
    }

    /**
     * @param ApartmentPrices $apartment_price
     * @return ApartmentPriceResource
     */
    public function show(ApartmentPrices $apartment_price): ApartmentPriceResource
    {
        return new ApartmentPriceResource($apartment_price);
    }

    /**
     * @param $id
     * @return AnonymousResourceCollection
     */
    public function getApartmentPriceByApartmentId($id): AnonymousResourceCollection
    {
        $apartmentPrice = ApartmentPrices::where('apartment_id', $id)->get();
        return ApartmentPriceResource::collection($apartmentPrice);
    }

    /**
     * @param Request $request
     * @param ApartmentPrices $apartment_price
     * @return JsonResponse|ApartmentPriceResource
     */
    public function update(Request $request, ApartmentPrices $apartment_price): JsonResponse|ApartmentPriceResource
    {
        $validator = Validator::make($request->all(), [
            'discount_id' => 'required',
            'price' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $apartment_price->update($request->all());
        return new ApartmentPriceResource($apartment_price);
    }


    /**
     * @param ApartmentPrices $apartment_price
     * @return ApartmentPriceResource
     */
    public function destroy(ApartmentPrices $apartment_price): ApartmentPriceResource
    {
        $apartment_price->delete();
        return new ApartmentPriceResource($apartment_price);
    }
}
 
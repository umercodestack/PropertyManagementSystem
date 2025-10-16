<?php

namespace App\Http\Controllers\Api\Discount;

use App\Http\Controllers\Controller;
use App\Http\Resources\DiscountResource;
use App\Models\Discounts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return DiscountResource::collection(Discounts::all());
    }

    /**
     * @param Request $request
     * @return DiscountResource|JsonResponse
     */
    public function store(Request $request): DiscountResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'discount_title' => 'required',
            'discount_type' => 'required',
            'discount_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        return new DiscountResource(Discounts::create($request->all()));
    }

    /**
     * @param Discounts $discount
     * @return DiscountResource
     */
    public function show(Discounts $discount): DiscountResource
    {
        return new DiscountResource($discount);
    }

    /**
     * @param Request $request
     * @param Discounts $discount
     * @return DiscountResource|JsonResponse
     */
    public function update(Request $request, Discounts $discount): DiscountResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'discount_title' => 'required',
            'discount_type' => 'required',
            'discount_amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $discount->update($request->all());
        return new DiscountResource($discount);
    }

    /**
     * @param Discounts $discount
     * @return DiscountResource
     */
    public function destroy(Discounts $discount): DiscountResource
    {
        $discount->delete();
        return new DiscountResource($discount);
    }
}
 
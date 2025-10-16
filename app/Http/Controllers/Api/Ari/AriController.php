<?php

namespace App\Http\Controllers\Api\Ari;

use App\Http\Controllers\Controller;
use App\Models\Calender;
use App\Models\Listing;
use App\Models\ListingRelation;
use App\Models\Properties;
use App\Models\RatePlan;
use App\Models\RoomType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class AriController extends Controller
{
    public function index(Request $request)
    {
        $rate_plan = RatePlan::where('listing_id', $request->get('listing_id'))->get();
        $calenderData = Calender::where('listing_id', $request->get('listing_id'))->get();
        $calender = array();
        foreach ($calenderData as $item) {
            $calArr = $item->toArray();
//            $calender[$calArr['calender_date']]['availability']= $calArr['availability'];
//            $calender[$calArr['calender_date']]['max_stay']= $calArr['max_stay'];
//            $calender[$calArr['calender_date']]['min_stay_through']= $calArr['min_stay_through'];
            $calender[$calArr['calender_date']]['rate']= $calArr['rate'];
            $calender[$calArr['calender_date']]['availability']= $calArr['availability'];
        }
//        dd($calender);
//        $response = Http::withHeaders([
//            'user-api-key' => env('CHANNEX_API_KEY'),
//        ])->get(env('CHANNEX_URL')."/api/v1/restrictions?filter[property_id]=$request->property_id&filter[date][gte]=$request->start_date&filter[date][lte]=$request->end_date&filter[restrictions]=rate");
//
//        if ($response->successful()) {
//            $response = $response->json();
//            $ari = $response['data'];
//            dd($ari);
//        } else {
//            $error = $response->body();
//            dd($error);
//        }
//
//        $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
//
//        if (isset($ari[$rate_plan->ch_rate_plan_id])) {
//            // Get all values for the specific key
//            $values = $ari[$rate_plan->ch_rate_plan_id];
//            dd($values);
//            return response()->json($values);
//        }
//        if (in_array($rate_plan->ch_rate_plan_id, $ari))
        return response()->json($calender);
    }

    // public function update(Request $request)
    // {
    //     $startDate = Carbon::createFromFormat('m/d/Y', trim($request->start_date))->format('Y-m-d');
    //     $endDate = Carbon::createFromFormat('m/d/Y', trim($request->end_date))->format('Y-m-d');
    //     $last_date_calender = Calender::where('listing_id', $request->listing_id)->orderBy('calender_date', 'desc')->limit(1)->first();
    //     $last_date = Carbon::parse($last_date_calender->calender_date);
    //     $user_id = Auth::user()->id;
    //     // Add one day
    //     $last_date->addDay();
    //     $period = CarbonPeriod::create($last_date, $endDate);
    //     if ($endDate > $last_date->toDateString()) {

    //         foreach ($period as $date) {
    //             Calender::create([
    //                 'listing_id' => $request->listing_id,
    //                 'availability' => 1,
    //                 'max_stay' => $last_date_calender->max_stay,
    //                 'min_stay_through' => $last_date_calender->min_stay_through,
    //                 'rate' => $request->price,
    //                 'calender_date' => $date->toDateString()
    //             ]);
    //         }
    //     }
    //     $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
    //     $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
    //     $ch_room_type_id = $room_type->ch_room_type_id;
    //     $rate_plan_ch_id = $rate_plan->ch_rate_plan_id;
    //     $property = Properties::where('user_id', $rate_plan->user_id)->first();
    //     $property_id = $property->ch_property_id;
    //     if ($request->has('availability')) {

    //         $response = Http::withHeaders([
    //             'user-api-key' => env('CHANNEX_API_KEY'),
    //         ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
    //                     "values" => [
    //                         [
    //                             //                        'date' => '2024-11-21',
    //                             "date_from" => $startDate,
    //                             "date_to" => $endDate,
    //                             "property_id" => "$property_id",
    //                             "room_type_id" => "$ch_room_type_id",
    //                             "availability" => (int) $request->availability,
    //                         ],
    //                     ]
    //                 ]);
    //         if ($response->successful()) {
    //             $availability = $response->json();
    //             $block = null;
    //             if ($request->has('block_reason')) {
    //                 $block = $request->block_reason;
    //             }

    //             Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
    //                 ->update(
    //                     ['availability' => (int) $request->availability, 'is_lock' => isset($request->availability) && $request->availability == 0 ? 1 : 0, 'updated_by' => $user_id, 'block_reason' =>  $block]
    //                 );
    //             \Log::info('availability resp', ['response' => $availability]);
    //         } else {
    //             $error = $response->body();
    //         }
    //     }

    //     $restrictionData = [
    //         "date_from" => $startDate,
    //         "date_to" => $endDate,
    //         "property_id" => "$property_id",
    //         "rate_plan_id" => "$rate_plan_ch_id", /*twin best rate*/
    //         'updated_by' => $user_id
    //     ];
    //     $restrictionDataDB = [
    //     ];
    //     if ($request->has('max_stay')) {
    //         $restrictionData["max_stay"] = (int) $request->max_stay;
    //         $restrictionDataDB["max_stay"] = (int) $request->max_stay;
    //     }
    //     if ($request->has('min_stay')) {
    //         $restrictionData["min_stay"] = (int) $request->min_stay;
    //         $restrictionDataDB["min_stay_through"] = (int) $request->min_stay;
    //     }
    //     if ($request->has('price')) {
    //         $restrictionData["rate"] = (int) $request->price * 100;
    //         $restrictionDataDB["rate"] = (int) $request->price;
    //     }

    //     if (isset($request->price) && $request->price || isset($request->min_stay) && $request->min_stay || isset($request->max_stay) && $request->max_stay) {
    //         $response = Http::withHeaders([
    //             'user-api-key' => env('CHANNEX_API_KEY'),
    //         ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
    //                     "values" => [
    //                         $restrictionData
    //                     ]
    //                 ]);

    //         if ($response->successful()) {
    //             $restrictions = $response->json();
    //             Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$startDate, $endDate])
    //                 ->update($restrictionDataDB);
    //             \Log::info('rest resp', ['response' => $restrictions]);
    //         } else {
    //             $error = $response->body();
    //             dd($error);
    //         }
    //     }
    //     return redirect()->back();
    // }
    
    public function update(Request $request)
    {
        // $startDate = Carbon::createFromFormat('m/d/Y', trim($request->start_date))->format('Y-m-d');
        // $endDate = Carbon::createFromFormat('m/d/Y', trim($request->end_date))->format('Y-m-d');
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        $last_date_calender = Calender::where('listing_id', $request->listing_id)->orderBy('calender_date', 'desc')->limit(1)->first();
        $last_date = Carbon::parse($last_date_calender->calender_date);
        $user_id = Auth::user()->id;
        $last_date->addDay();
        $period = CarbonPeriod::create($last_date, $endDate);

        if ($endDate > $last_date->toDateString()) {
            foreach ($period as $date) {
                Calender::create([
                    'listing_id' => $request->listing_id,
                    'availability' => 1,
                    'max_stay' => $last_date_calender->max_stay,
                    'min_stay_through' => $last_date_calender->min_stay_through,
                    'rate' => $request->data['price'],
                    'calender_date' => $date->toDateString()
                ]);
            }
        }

        $rate_plan = RatePlan::where('listing_id', $request->listing_id)->first();
        $room_type = RoomType::where('id', $rate_plan->room_type_id)->first();
        $ch_room_type_id = $room_type->ch_room_type_id;
        $rate_plan_ch_id = $rate_plan->ch_rate_plan_id;
        $property = Properties::where('user_id', $rate_plan->user_id)->first();
        $property_id = $property->ch_property_id;

        if ($request->has('data.availability')) {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/availability", [
                "values" => [
                    [
                        "date_from" => $startDate,
                        "date_to" => $endDate,
                        "property_id" => "$property_id",
                        "room_type_id" => "$ch_room_type_id",
                        "availability" => (int) $request->data['availability'],
                    ],
                ]
            ]);

            if ($response->successful()) {
                $availability = $response->json();
                $block = $request->has('block_reason') ? $request->block_reason : null;

                Calender::where('listing_id', $request->listing_id)
                    ->whereBetween('calender_date', [$startDate, $endDate])
                    ->update([
                        'availability' => (int) $request->data['availability'],
                        'is_lock' => isset($request->data['availability']) && $request->data['availability'] == 0 ? 1 : 0,
                        'updated_by' => $user_id,
                        'block_reason' => $block
                    ]);

                \Log::info('availability resp', ['response' => $availability]);
            } else {
                $error = $response->body();
                \Log::error('Availability update failed', ['error' => $error]);
            }
        }

        $restrictionData = [
            "date_from" => $startDate,
            "date_to" => $endDate,
            "property_id" => "$property_id",
            "rate_plan_id" => "$rate_plan_ch_id",
            'updated_by' => $user_id
        ];
        $restrictionDataDB = [];

        if ($request->has('max_stay')) {
            $restrictionData["max_stay"] = (int) $request->max_stay;
            $restrictionDataDB["max_stay"] = (int) $request->max_stay;
        }
        if ($request->has('min_stay')) {
            $restrictionData["min_stay"] = (int) $request->min_stay;
            $restrictionDataDB["min_stay_through"] = (int) $request->min_stay;
        }
        if ($request->has('data.price')) {
            $restrictionData["rate"] = (int) $request->data['price'] * 100;
            $restrictionDataDB["rate"] = (int) $request->data['price'];
        }

        if (isset($request->data['price']) || isset($request->min_stay) || isset($request->max_stay)) {
            $response = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                "values" => [
                    $restrictionData
                ]
            ]);

            if ($response->successful()) {
                $restrictions = $response->json();
                Calender::where('listing_id', $request->listing_id)
                    ->whereBetween('calender_date', [$startDate, $endDate])
                    ->update($restrictionDataDB);
                \Log::info('restrictions resp', ['response' => $restrictions]);
            } else {
                $error = $response->body();
                \Log::error('Restrictions update failed', ['error' => $error]);
            }
        }

        return response()->json(["message" => "Record updated!"], 200);
    }
    
    public function rate_multiplier_options(Request $request) {
        $listing = Listing::where('listing_id', $request->listing_id)->first();
        $airBnb = true;
        $bookingcom = false;
        $vrbo = false;
       
        $listing_relation = ListingRelation::where('listing_id_airbnb',$listing->id)->get();
        foreach($listing_relation as $item) {
            if($item->listing_type == 'BookingCom') {
                $bookingcom = true;
            }else if($item->listing_type == 'VRBO') {
                 $vrbo = true;
            }
            // dd($item);
        }
        //  dd($listing_relation);
        return response([
                'airBnb' => $airBnb,
                'bookingcom' => $bookingcom,
                'vrbo' => $vrbo,
                'livedin' => false,
                'gathern' => false,
            ]);
    }
    
    public function rate_multiplier(Request $request)
    {
        $listing = Listing::where('listing_id', $request->listing_id)->first();
        // dd($listing);
        $listing_relation = ListingRelation::where('listing_id_airbnb',$listing->id)
        ->where('listing_type', $request->channel)->first();
        $validator = Validator::make($request->all(), [
            'channel' => 'required',
            'listing_id' => 'required',
            'multiplier' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $listing = Listing::where('listing_id', $request->listing_id)->first();
        $listing_relation = ListingRelation::where('listing_id_airbnb',$listing->id)
        ->where('listing_type', $request->channel)->first();
        $other_ota_listing = Listing::where('id', $listing_relation->listing_id_other_ota)->first();
        // dd($other_ota_listing);
        // dd($listing, $listing_relation);
        if($listing_relation) {
            $airbnb_calenders = Calender::where('listing_id', $request->listing_id)->whereBetween('calender_date', [$request->start_date, $request->end_date])->get();
            foreach($airbnb_calenders as $airbnb_calender) {
                // dd($other_ota_listing->listing_id);
                $other_calender = Calender::where('listing_id', $other_ota_listing->listing_id)->where('calender_date' , $airbnb_calender->calender_date)->first();
                $rate_plan = RatePlan::where('listing_id', $other_calender->listing_id)->first();
                $property = Properties::where('id', $rate_plan->property_id)->first();
                // $other_calender->update(['rate'=> 249]);
                $multiplied = (int)($request->multiplier / 100 * $airbnb_calender->rate);
                // dd($multiplied);
                $response = Http::withHeaders([
                    'user-api-key' => env('CHANNEX_API_KEY'),
                ])->post(env('CHANNEX_URL') . "/api/v1/restrictions", [
                            "values" => [
                                [
                                    "property_id" => $property->ch_property_id,
                                    "rate_plan_id" => $rate_plan->ch_rate_plan_id,
                                    "date" => $other_calender->calender_date,
                                    "rate" => ($request->multiplier / 100 * $airbnb_calender->rate)  * 100
                                ]
                            ]
                        ]);

                if ($response->successful()) {
                    $restrictions = $response->json();
                    $other_calender->update(['rate'=> $multiplied]);
                    return response($other_calender);
                } else {
                    $error = $response->body();
                    dd($error);
                }
            }
                }

    }
}

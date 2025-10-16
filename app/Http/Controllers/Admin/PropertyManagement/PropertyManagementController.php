<?php

namespace App\Http\Controllers\Admin\PropertyManagement;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Properties;
use App\Models\{
    User,
    Listing
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PropertyManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function __construct()
    {
        $this->middleware('permission');
    } 
     
    public function index()
    {
$properties = Properties::with('user')->orderByDesc('id')->get();
        return view('Admin.properties-management.index', ['properties' => $properties]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        $groups = Group::all();
        return view('Admin.properties-management.create', ['users' => $users, 'groups' => $groups]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'currency' => 'required|string',
            'email' => 'required|email',
            // 'phone' => 'required',
            // 'zip_code' => 'required',
            // 'country' => 'required',
            // 'state' => 'required',
            // 'city' => 'required',
            // 'address' => 'required',
            // 'longitude' => 'required',
            // 'latitude' => 'required',
            // 'timezone' => 'required',
            'property_type' => 'required',
            'group_id' => 'required',
        ]);
        $data = $request->all();
        $group = Group::findOrFail($data['group_id']);
        $data['ch_group_id'] = $group->ch_group_id;
        $response = Http::withHeaders([
            'user-api-key' =>  env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL').'/api/v1/properties', [
            "property" =>  [
                "title" => $request->title,
                "currency" => 'SAR',
                "email" => $request->email,
                "property_type" => $request->property_type,
                "group_id" => $data['ch_group_id'],
            ]
        ]);
        if ($response->successful()) {
            $response = $response->json();
            $data['ch_property_id'] = $response['data']['id'];
            Properties::create($data);
            return redirect()->route('property-management.index')->with('success', 'Group Created Successfully');
        } else {
            $error = $response->body();
            return redirect()->route('property-management.index')->with('error', $error);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Properties $property_management)
    {
        $users = User::all();
        $groups = Group::all();
        return view('Admin.properties-management.edit', ['users' => $users, 'groups' => $groups, 'property' => $property_management]);
    }
    public function propertyIframe(Properties $property)
    {
//        dd($property);

        $response = Http::withHeaders([
            'user-api-key' =>  env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL').'/api/v1/auth/one_time_token', [
            "one_time_token" =>  [
                "property_id" => $property->ch_property_id,
                "group_id" => $property->ch_group_id,
                "username" => $property->email,
            ]
        ]);
        if ($response->successful()) {
            $response = $response->json();
            $token = $response['data']['token'];
            return view('Admin.properties-management.channex-room-iframe', ['token' => $token, 'property_id' => $property->ch_property_id]);
        } else {
            $error = $response->body();
            $properties = Properties::with('user')->get();
            return view('Admin.properties-management.index', ['properties' => $properties]);
        }
    }

    public function channelIframe(Properties $property)
    {

        $response = Http::withHeaders([
            'user-api-key' =>  env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL').'/api/v1/auth/one_time_token', [
            "one_time_token" =>  [
                "property_id" => $property->ch_property_id,
                "group_id" => $property->ch_group_id,
                "username" => $property->email,
            ]
        ]);
        if ($response->successful()) {
            $response = $response->json();
            $token = $response['data']['token'];
            return view('Admin.properties-management.channex-channel-iframe', ['token' => $token, 'property_id' => $property->ch_property_id]);
        } else {
            $error = $response->body();
            $properties = Properties::with('user')->get();
            return view('Admin.properties-management.index', ['properties' => $properties]);
        }
    }
    public function edit(Properties $property_management)
    {
        $users = User::all();
        $groups = Group::all();
        return view('Admin.properties-management.edit', ['users' => $users, 'groups' => $groups, 'property' => $property_management]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Properties $property_management)
    {
        $request->validate([
            'title' => 'required|string',
            'currency' => 'required|string',
            'email' => 'required|email',
//            'phone' => 'required',
//            'zip_code' => 'required',
//            'country' => 'required',
//            'state' => 'required',
//            'city' => 'required',
//            'address' => 'required',
//            'longitude' => 'required',
//            'latitude' => 'required',
//            'timezone' => 'required',
//            'property_type' => 'required',
//            'group_id' => 'required',
        ]);

        $data = $request->all();
        $data['user_id'] = json_encode($data['user_id']);


            $property_management->update($data);

            return redirect()->route('property-management.index');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Properties $properties)
    {
        //
    }


    // For Multi Calendar
    public function get_properties(Request $request)
    {

        $listings = Listing::query();
        $listings->where('is_sync', 'sync_all');

        if ($request->filled('name')) {
            $listings->where('listing_json', 'LIKE', '%' . $request->name . '%');
        }

        $current_property_count = $listings->has('calendars')->count();

        if ($request->filled('limit')) {
            $offset = $request->filled('offset') ? $request->offset : 0;
            $listings->skip($offset)->limit($request->limit);
        }

        $result = [];
        foreach ($listings->get() as $listing) {

            if ($listing->calendars->count() > 0) {

                $listing_json = !empty($listing->listing_json) ? json_decode($listing->listing_json) : '';
                $title = !empty($listing_json->title) ? $listing_json->title : '';
                $listing_id = !empty($listing_json->id) ? $listing_json->id : '';

                $propertyData = ['id' => $listing->id, 'property_uid' => $listing_id, 'property_name' => $title, 'bookings' => []];
                if ($listing->bookings->count() > 0) {
                    foreach ($listing->bookings as $pr) {
                        if (!empty($pr->booking_status) && in_array(strtolower($pr->booking_status), ['confirmed', 'booked', 'new', 'modified', 'cancelled'])) {

                            $booking_date_end = $pr->booking_date_end;
                            if($pr->booking_date_start != $booking_date_end){
                                $booking_date_end = date('Y-m-d', strtotime($booking_date_end . ' -1 day'));
                            }

                            $bk = [
                                'name' => $pr->name . ' ' . $pr->surname,
                                'start_date' => $pr->booking_date_start,
                                'end_date' => $booking_date_end,
                                'price' => (float)$pr->total_price,
                                'booking_status' => strtolower($pr->booking_status),
                                'is_otas' => 0
                            ];
                            $propertyData['bookings'][] = $bk;
                        }
                    }
                }

                if ($listing->bookingsOTAS()->exists()) {
                    foreach ($listing->bookingsOTAS()->get() as $ota_booking) {
                        if (!empty($ota_booking->status) && in_array(strtolower($ota_booking->status), ['confirmed', 'booked', 'new', 'modified', 'cancelled'])) {
                            $amount = 0;
                            $name = '';
                            if (!empty($ota_booking->booking_otas_json_details)) {
                                $attribute = json_decode($ota_booking->booking_otas_json_details);

                                $amount = !empty($attribute->attributes->amount) ? $attribute->attributes->amount : 0;
                                $fname = !empty($attribute->attributes->customer->name) ? $attribute->attributes->customer->name : '';
                                $surname = !empty($attribute->attributes->customer->surname) ? $attribute->attributes->customer->surname : '';
                                $name = $fname . ' ' . $surname;
                            }

                            $booking_date_end = $ota_booking->departure_date;
                            if($ota_booking->arrival_date != $booking_date_end){
                                $booking_date_end = date('Y-m-d', strtotime($booking_date_end . ' -1 day'));
                            }

                            $propertyData['bookings'][] = [
                                'name' => $name,
                                'start_date' => $ota_booking->arrival_date,
                                'end_date' => $booking_date_end,
                                'price' => (float)$amount,
                                'booking_status' => strtolower($ota_booking->status),
                                'is_otas' => 1
                            ];
                        }
                    }
                }
                $result[0][] = $propertyData;
            }
        }
        $result[1][] = ['filter_properties' => $current_property_count];
        return $result;
    }
}

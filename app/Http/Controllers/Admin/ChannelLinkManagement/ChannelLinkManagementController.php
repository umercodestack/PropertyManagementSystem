<?php

namespace App\Http\Controllers\Admin\ChannelLinkManagement;

use App\Http\Controllers\Controller;
use App\Models\ChannelLinks;
use App\Models\Channels;
use App\Models\Group;
use App\Models\Properties;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChannelLinkManagementController extends Controller
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
        $connection_links = ChannelLinks::with('user')->get();
        // dd($connection_links);
        return view('Admin.connection-link-management.index', ['connection_links' => $connection_links]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
//        $properties = Properties::where('group_id',3)->get();
//        dd($properties);
         $host_user_id = $request->input('host_user_id');
        $users = User::all();
        return view('Admin.connection-link-management.create', ['users' => $users, 'host_user_id' => $host_user_id,]);
    }

    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request)
    {
        // dd($request);
        $user = User::where('id', $request->user_id)->first();
        $group = Group::where('user_id', $user->id)->first();
        $grData = array();
        if(is_null($group)) {
            $group = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . '/api/v1/groups', [
                        "group" => ["title" => strtolower($user->name) . '_g_' . $user->id]
                    ]);
            if ($group->successful()) {
                $response = $group->json();
                $grData['group_name'] = strtolower($user->name) . '_g_' . $user->id;
                $grData['user_id'] = $user->id;
                $grData['ch_group_id'] = $response['data']['id'];
                $groupCreated = Group::create($grData);
            } else {
                $error = $group->body();
                //            return response()->json(['error' => $error]);
            }
        } else {
            $grData = $group->toArray();
        }
        $groupCreated = $group;

        //        dd($grData['ch_group_id']);
        $property = Http::withHeaders([
            'user-api-key' => env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL') . '/api/v1/properties', [
                    "property" => [
                        "title" => strtolower($user->name) . $user->id,
                        "currency" => 'USD',
                        "timezone" => 'Asia/Riyadh',
                        "email" => $user->email,
                        "country" => substr($user->country, -2),
                        "city" => $user->city,
                        "group_id" => $grData['ch_group_id'],
                        "settings" => [
                            "min_stay_type" => 'through'
                        ]
                    ]
                ]);
        if ($property->successful()) {
            $property = $property->json();
            $data['title'] = strtolower($user->name) . $user->id;
            $data['currency'] = 'USD';
            $data['email'] = $user->email;
            $data['country'] = substr($user->country, -2);
            $data['city'] = $user->city;
            $data['user_id'] = $user->id;
            $data['ch_property_id'] = $property['data']['id'];
            $data['ch_group_id'] = $grData['ch_group_id'];
            $data['group_id'] = $groupCreated->id;
            Properties::create($data);

            $webhook = Http::withHeaders([
                'user-api-key' => env('CHANNEX_API_KEY'),
            ])->post(env('CHANNEX_URL') . '/api/v1/webhooks', [
                        "webhook" => [
                            "property_id" => $data['ch_property_id'],
                            "callback_url" => 'https://admin.livedin.co/api/webhook',
                            "event_mask" => "*",
                            "is_active" => true,
                            "send_data" => true,
                        ]
                    ]);
            if ($webhook->successful()) {
                $webhook = $webhook->json();
                //                dd($webhook);
            } else {
                $error = $property->body();
                //                dd($error);
            }
        } else {
            $error = $property->body();
            //            dd($error);
        }

        $userGroup = Group::where('user_id', $user->id)->first();

        $property = Properties::where('user_id', $user->id)->first();

        $url = env('CHANNEX_URL') . '/api/v1/meta/airbnb/connection_link';
        $properties = ["$property->ch_property_id"];
        $min_stay_type = 'Arrival';
        $group_id = $userGroup->ch_group_id;
        $redirect_uri = route('get-channel-callback-api', $user->id);
        //        dd($redirect_uri);
        $user_api_key = env('CHANNEX_API_KEY');

        $response = Http::withHeaders([
            'user-api-key' => $user_api_key,
        ])->get($url, [
                    'properties' => json_encode($properties),
                    'title' => 'channel_' . $group_id,
                    'min_stay_type' => $min_stay_type,
                    'group_id' => $group_id,
                    'redirect_uri' => $redirect_uri
                ]);

        // Handle response here, e.g., decode JSON response
        $resp = $response->json();
        //        dd($resp['data']['attributes']['url']);
        $data['user_id'] = $user->id;
        $data['url'] = $response['data']['attributes']['url'];
        ChannelLinks::create($data);

        return redirect()->route('connect-link-management.index')->with('success', 'Connection Link Created Successfully');

    }

    public function getChannelCallback(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = 192;
        Channels::create(['ch_channel_id' => $data['channel_id'], 'user_id'=>$data['user_id']]);
        return redirect()->route('channel-management.index')->with('success', 'Connection Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChannelLinks $channelLinks)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChannelLinks $channelLinks)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChannelLinks $channelLinks)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChannelLinks $connect_link_management)
    {
        dd($connect_link_management);
    }
}
 
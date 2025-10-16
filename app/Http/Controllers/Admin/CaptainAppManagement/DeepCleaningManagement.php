<?php

namespace App\Http\Controllers\Admin\CaptainAppManagement;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\DeepCleaning;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Hostaboard;
use App\Models\DeepcleaningStatusLog; 
use App\Models\Deepcleaningimages;
use App\Models\DeepcleaningComment;
use Auth;


class DeepCleaningManagement extends Controller
{
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    public function index()
    {
        $deepCleaning = DeepCleaning::with('host', 'assignToPropMan', 'assignToVen', 'pocUser', 'listing', 'hostactivation')->orderBy('id', 'desc')->get();
        return view('Admin.captain-app-management.deep-cleaning-management.index', ['deepCleaning' => $deepCleaning]);
    }

    public function create(Request $request)
    {
         
         // Retrieve the necessary parameters from the request
        $host_activation_id = $request->input('hostaboard_id');
        $accountManager_id = $request->input('accountManager_id');
        $owner_name = $request->input('owner_name');
        $host_number = $request->input('host_number');
        $title = $request->input('title');
        $unit_number = $request->input('unit_number');
        $floor = $request->input('floor');
        $type = $request->input('type');
        $unit_type = $request->input('unit_type');

        $property_address = $request->input('property_address');
        $property_google_map_link = $request->input('property_google_map_link');


        $users = User::where('role_id', '!=', 2)->get();
        $listings = Listing::all();
        $audits = Audit::all();
        return view('Admin.captain-app-management.deep-cleaning-management.create', ['listings' => $listings, 'users' => $users, 'audits' => $audits,
        'host_activation_id' => $host_activation_id,
        'accountManager_id' => $accountManager_id,
        'owner_name' => $owner_name,
        'host_number' => $host_number,
        'title' => $title,
        'unit_number' => $unit_number,
        'floor' => $floor,
        'type' => $type,
        'unit_type' => $unit_type,
        'property_address' => $property_address,
        'property_google_map_link' => $property_google_map_link
    ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'assignToPropertyManager' => 'required',
            'assignToVendor' => 'required',
            'location' => 'required',
        ]);
       $deepcleaning = DeepCleaning::create($request->all());

        $user = Auth::user();

       if (!empty($request->comments)) { 
           DeepcleaningComment::create([
               'user_id' => $user->id,
               'deepcleaning_id' => $deepcleaning->id,
               'comments' => $request->comments,
           ]);
       }

        return redirect()->route('deep-cleaning-management.index')->with('success', 'Deep Cleaing Task Created Successfully');
    }

    public function edit(DeepCleaning $deep_cleaning_management)
    {
        $users = User::where('role_id', '!=', 2)->get();
        $listings = Listing::all();
        $audits = Audit::all();
        $hostaboard = Hostaboard::select('unit_number', 'type', 'floor','unit_type') 
        ->where('id', $deep_cleaning_management->host_activation_id)
        ->first();

        $statuslogs = isset($deep_cleaning_management->id) ? DeepcleaningStatusLog::select( 'users.name as user_name', 'deepcleaning_status_log.status', 'deepcleaning_status_log.timestamp' ) ->join('users', 'users.id', '=', 'deepcleaning_status_log.user_id') ->where('deepcleaning_status_log.deepcleaning_id', $deep_cleaning_management->id) ->orderBy('deepcleaning_status_log.timestamp', 'desc') ->get() : null;
        
        $deepcleaningImages = isset($deep_cleaning_management->id) ? Deepcleaningimages::where('deepcleaning_id', $deep_cleaning_management->id)->get() : null;

        isset($deep_cleaning_management->id) ? $comments = DeepcleaningComment::with('user')->where('deepcleaning_id', $deep_cleaning_management->id)->get() : $comments = null;
        
        return view('Admin.captain-app-management.deep-cleaning-management.edit', ['listings' => $listings, 'users' => $users, 'deep_cleaning' => $deep_cleaning_management, 'audits' => $audits, 'hostaboard' => $hostaboard,'statuslogs' => $statuslogs,'deepcleaningImages' => $deepcleaningImages,'comments' => $comments]);
    }

    public function update(Request $request, DeepCleaning $deep_cleaning_management)
    {
        $request->validate([
            'assignToPropertyManager' => 'required',
            'assignToVendor' => 'required',
            'location' => 'required',
        ]);
        $deep_cleaning_management = $deep_cleaning_management->update($request->all());

        return redirect()->route('deep-cleaning-management.index')->with('success', 'Deep Cleaing Task Created Successfully');
    }
    
    public function storeComment(Request $request)
    {
        $data = $request->all();
        //dd($data);
        $user = Auth::user();
        DeepcleaningComment::create([
            'user_id' => $user->id,
            'deepcleaning_id' => $data['deepcleaning_id'],
            'comments' => $data['comments'],
        ]);
        return redirect()->back();
    }

}

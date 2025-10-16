<?php

namespace App\Http\Controllers\Admin\CaptainAppManagement;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Hostaboard; 
use App\Models\AuditStatusLog; 
use App\Models\Auditimages;
use App\Models\AuditComment;
use App\Models\AuditTaskImages;
use Illuminate\Support\Facades\DB;
use Auth;


class AuditManagement extends Controller
{
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    public function index()
    {
        
        $audit = Audit::with('host', 'assignToUser', 'pocUser', 'listing', 'hostactivation')->orderBy('id', 'desc')->get()->map(function ($audit) {
        $statusMap = [
            'mark as done' => 'Completed',
            'pending'      => 'Pending',
            'resume'       => 'In Progress',
            'start'        => 'In Progress',
            'on the way'   => 'In Progress',
             null           => 'Pending',
        ];

        $statusKey = strtolower(trim($audit->status)); // normalize input
        $audit->status = $statusMap[$statusKey] ?? 'Pending';

        return $audit;
        });

        return view('Admin.captain-app-management.audit-management.index', ['audit' => $audit]);
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

    // Get users who are not role 2 (assuming role 2 is for some other category)
    $users = User::where('role_id', '!=', 2)->get();

    // Get listings (assuming this is necessary for the view)
    $listings = Listing::all();

    // Pass data to the view
    return view('Admin.captain-app-management.audit-management.create', [
        'listings' => $listings,
        'users' => $users,
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
            'assignTo' => 'required',
            'audit_date' => 'required',
            'location' => 'required',
        ]);
       $audit = Audit::create($request->all());


       $user = Auth::user();

       if (!empty($request->comments)) { 
           AuditComment::create([
               'user_id' => $user->id,
               'audit_id' => $audit->id,
               'comments' => $request->comments,
           ]);
       }


        return redirect()->route('audit-management.index')->with('success', 'Audit Task Created Successfully');
    }

    public function edit(Audit $audit_management)
    {
        $users = User::where('role_id', '!=', 2)->get();
        $listings = Listing::all();
        
         $hostaboard = Hostaboard::select('host_id','title','property_id','unit_number', 'type', 'floor','unit_type') 
        ->where('id', $audit_management->host_activation_id)
         ->first();
    

        $statuslogs = isset($audit_management->id) ? AuditStatusLog::select( 'users.name as user_name', 'audit_status_log.status', 'audit_status_log.timestamp' ) ->join('users', 'users.id', '=', 'audit_status_log.user_id') ->where('audit_status_log.audit_id', $audit_management->id) ->orderBy('audit_status_log.timestamp', 'desc') ->get() : null;
        
        $auditImages = isset($audit_management->id) ? Auditimages::where('audit_id', $audit_management->id)->get() : null;

        isset($audit_management->id) ? $comments = AuditComment::with('user')->where('audit_id', $audit_management->id)->get() : $comments = null;
        
        
$mainauditchecklist = isset($audit_management->id) 
    ? DB::select("CALL get_audit_list($audit_management->id);") 
    : [];

$finalChecklist = [];

if (!empty($mainauditchecklist)) {
    foreach ($mainauditchecklist as $main) {
        $subauditchecklistRaw = DB::select("CALL get_audit_task_list($audit_management->id, $main->id);");

        $subauditchecklist = collect($subauditchecklistRaw)->map(function ($item) {
            $images = AuditTaskImages::where('audit_id', $item->audit_id)
                ->where('host_activation_id', $item->host_activation_id)
                ->where('audit_checklist_detailed_id', $item->id)
                ->orderBy('id', 'desc')
                ->pluck('file_path') // only get the file paths directly
                ->toArray();

            $item->images = $images;
            return $item;
        });

        $main->sub_checklist = $subauditchecklist;
        $finalChecklist[] = $main;
    }
}

        
        return view('Admin.captain-app-management.audit-management.edit', ['listings' => $listings, 'users' => $users, 'audit_management' => $audit_management, 'hostaboard' => $hostaboard,'comments' => $comments ,'statuslogs' => $statuslogs,'auditImages' => $auditImages ,'finalChecklist' => $finalChecklist]);
    }

    public function update(Request $request, Audit $audit_management)
    {
        $request->validate([
            'assignTo' => 'required',
            'audit_date' => 'required',
            'location' => 'required',
        ]);
        $audit_management->update($request->all());

        return redirect()->route('audit-management.index')->with('success', 'Audit Task Updated Successfully');
    }
    
    
    public function storeComment(Request $request)
    {
        $data = $request->all();
        //dd($data);
        $user = Auth::user();
        AuditComment::create([
            'user_id' => $user->id,
            'audit_id' => $data['audit_id'],
            'comments' => $data['comments'],
        ]);
        return redirect()->back();
    }

    public function getListingDetails($id)
    {
        $listing = Listing::find($id);
    
        if ($listing) {
            $listing_json = json_decode($listing->listing_json, true);
            return response()->json([
                'success' => true,
                'data' => [
                            'title' => $listing_json['title'], 
                            'apartment_num' => $listing->apartment_num, 
                            'property_type' => $listing->property_type,
                            'google_map' => $listing->google_map  
                        ] 
            ]);
        } else {
            return response()->json(['success' => false]);
        }
    }


    
public function showDashboard()
{
    $data = [];

$data['total_activations_without_mapping'] = DB::table('hostaboard')
    ->whereNotIn('id', function ($query) {
        $query->select('hostaboard_id')->from('auditlistingmapping');
    })
    ->whereDate('created_at', '>=', '2025-08-08')
    ->count();

    $data['photography_pending'] = DB::table('revenue_activation_audit')
    ->whereNotIn('status', ['approved', 'mark as done'])
    ->count();

    $data['photography_in_review'] = DB::table('revenue_activation_audit')
        ->where('status', 'mark as done')
        ->count();

    $data['activation_audit_pending'] = DB::table('audits')
    ->where(function ($query) {
        $query->whereNotIn('status', ['mark as done', 'completed'])
              ->orWhereNull('status');
    })
    ->whereDate('created_at', '>=', '2025-08-08')
    ->count();

    $data['inventory_maintenance_pending'] = DB::table('sales_activation_audit')
        ->whereIn('task_type', ['Maintenance', 'Inventory'])
        ->where('status', '!=', 'approved')
        ->where('minor_major', 1)
        ->distinct('hostaboard_id')
        ->count('hostaboard_id');

$data['deep_cleaning_pending'] = DB::table('deep_cleanings')
    ->where(function ($query) {
        $query->whereNotIn('status', ['mark as done', 'completed'])
              ->orWhereNull('status');
    })
    ->whereDate('created_at', '>=', '2025-08-08')
    ->count();


    $data['cohosting_pending'] = DB::table('audit_backend_ops')
        ->where(function ($query) {
            $query->where('status', '!=', 'approved')
                  ->orWhereNull('status');
        })->count();

    $data['listing_creation_pending'] = DB::table('auditlisting')
        ->where(function ($query) {
            $query->whereNotIn('status', ['approve', 'Approve'])
                  ->orWhereNull('status');
        })->count();

    $data['listing_mapping_pending'] = DB::table('auditlistingmapping')
        ->where(function ($query) {
            $query->whereNotIn('status', ['approve', 'Approve'])
                  ->orWhereNull('status');
        })->count();

    $data['listing_mapping_completed'] = DB::table('auditlistingmapping')
        ->whereIn('status', ['approved', 'Approved'])
        ->count();

    return view('Admin.captain-app-management.audit-management.dashboard', compact('data'));
}


}

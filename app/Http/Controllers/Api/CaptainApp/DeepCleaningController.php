<?php

namespace App\Http\Controllers\Api\CaptainApp;

use App\Http\Controllers\Controller;
use App\Models\DeepCleaning;
use App\Models\Listing;
use App\Models\DeepcleaningStatusLog;
use App\Models\User;
use App\Models\Deepcleaningimages;
use App\Models\DeepcleaningComment;
use App\Models\Hostaboard;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DeepCleaningController extends Controller
{
    public function index(Request $request)
    {
        // $deepCleaning = DeepCleaning::orderBy('cleaning_date', 'desc')->where('assignToPropertyManager', $user->id)->select('id', 'listing_title', 'listing_id', 'cleaning_date', 'status')->limit(2)->get();
        $user = Auth::user();
        $startDate = isset($request->start_date) && $request->start_date ? $request->start_date : null;
        $endDate = isset($request->end_date) && $request->end_date ? $request->end_date : null;
        
        // $deepCleaning = DeepCleaning::orderBy('cleaning_date', 'desc')->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
        $deepCleaning = DeepCleaning::orderBy('cleaning_date', 'desc')
            ->when($user->role_id != 6, function ($query) use ($user) {
        
            return $query->where('assignToPropertyManager', $user->id);
        })
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('cleaning_date', [$startDate, $endDate]);
        })
        ->select('id', 'listing_title', 'listing_id', 'cleaning_date as date', 'status')
        ->get()
        ->map(function ($item) {
            $item->status = $item->status === 'mark as done' ? 'completed' : 'pending';
            return $item;
        });
        
        return response()->json(
            $deepCleaning
        );
    }

    public function getDeepCleaningCount(Request $request)
    {
        $user = Auth::user();

        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;

        // Completed Deep Cleaning
        $completedDeepCleaning = DeepCleaning::orderBy('cleaning_date', 'desc')
            ->when($user->role_id != 6, function ($query) use ($user) {
            return $query->where('assignToPropertyManager', $user->id);
        })
        ->whereIn('status', ['completed', 'mark as done'])
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('cleaning_date', [$startDate, $endDate]);
        })
        ->select('id', 'listing_title', 'listing_id', 'cleaning_date as date', 'status')
        ->get();

        // Total Deep Cleaning
        $deepCleaning = DeepCleaning::orderBy('cleaning_date', 'desc')
        ->when($user->role_id != 6, function ($query) use ($user) {
            return $query->where('assignToPropertyManager', $user->id);
        })
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            return $query->whereBetween('cleaning_date', [$startDate, $endDate]);
        })
        ->select('id', 'listing_title', 'listing_id', 'cleaning_date as date', 'status')
        ->get();

    return response([
        'total_tasks' => $deepCleaning->count(),
        'completed_task' => $completedDeepCleaning->count(),
    ]);
}


    public function oldshow($id)
    {
        // $deepCleaning = DeepCleaning::whereId($id)->with('host', 'pocUser')->first();
        $deepCleaning = DeepCleaning::whereId($id)->first();
        $host = User::where('id', $deepCleaning->host_id)->first();
        $poc = User::where('id', $deepCleaning->poc)->first();
        // dd($host);
        if($host) {
            $deepCleaning['host'] = $host;
        }else {
            $deepCleaning['host'] = [
                'name' => $deepCleaning->host_name,
                'surname' => '',
                'phone' => $deepCleaning->host_phone
            ];
        }
        if($poc) {
            $deepCleaning['poc'] = $poc;
        }else {
            $deepCleaning['poc'] = [
                'name' => $deepCleaning->poc_name,
                'surname' => '',
                'phone' => ''
            ];
        }
        // $deepCleaning['host']
        // dd($deepCleaning);
        $deepCleaning['next_status'] = "completed"; 
        $deepCleaning['checkin_datetime'] = "";
        $deepCleaning['checkout_datetime'] = "";
        return response($deepCleaning);
    }

    public function show($id)
    {
    // Fetch DeepCleaning record
    $deepCleaning = DeepCleaning::find($id);
    
    $activation = hostaboard::find($deepCleaning->host_activation_id);

    if (!$deepCleaning) {
        return response()->json(['error' => 'Deep Cleaning record not found'], 404);
    }

    // Assigned vendor (cleaner)
    //$assignTouser = User::whereId($deepCleaning->assignToVendor)
      //  ->select('name', 'surname', 'email', 'phone')
        //->first();

    // Fetch listing
    //$listing = Listing::find($deepCleaning->listing_id);

    //$host = null;
    //$poc = null;
    
     //$assignTouser = User::whereId($deepCleaning->assignToVendor)
       // ->select('name', 'surname', 'email', 'phone')
        //->first();

    // Fetch listing
    $listing = Listing::find($deepCleaning->listing_id);

    $poc = $activation->accountManager;
    $host = $activation->owner_name ?? null;

    if ($listing) {
        // Decode user_ids
        $listing_users = json_decode($listing->user_id, true) ?? [];
        $listing_user = !empty($listing_users) ? (int) end($listing_users) : null;

        $host = $listing_user
            ? User::whereId($listing_user)->select('name', 'surname', 'email', 'phone')->first()
            : null;

        // Decode poc (exp managers)
        $listing_poc = json_decode($listing->exp_managers, true) ?? [];
        $poc_user = !empty($listing_poc) ? (int) end($listing_poc) : null;

        $poc = $poc_user
            ? User::whereId($poc_user)->select('name', 'surname', 'email', 'phone')->first()
            : null;
    }

    // Get comments
    $comments = DeepcleaningComment::where('deepcleaning_id', $deepCleaning->id)
        ->orderBy('id', 'desc')
        ->get();

    $deepCleaning['remarks'] = $comments ?? [];

    // Get images
    $deepcleaningimages = Deepcleaningimages::where('deepcleaning_id', $deepCleaning->id)
        ->orderBy('id', 'desc')
        ->get();

    $deepCleaning['cleaningimages'] = $deepcleaningimages->isNotEmpty()
        ? $deepcleaningimages->map(function ($image) {
            $image->file_path = url('public/storage/' . $image->file_path);
            return $image;
        })
        : [];

    // Host details
    $deepCleaning['host'] = [
            'name' => $activation->owner_name ?? '',
            'surname' => $activation->last_name ?? '',
            'phone' => $activation->host_number ?? ''
        ];

    // POC details
     $deepCleaning['poc'] = [
            'name' => $poc->name ?? '',
            'surname' => $poc->surname ?? '',
            'phone' => $poc->phone ?? ''
        ];

    // Cleaner info
    $deepCleaning['cleaner_Name'] = $assignTouser->name ?? '';

    // Extra fields
    $deepCleaning['checkin_datetime'] = "";
    $deepCleaning['checkout_datetime'] = "";
    $deepCleaning['cleaner_assign_datetime'] = "";

    // Status flow
    $statusFlow = [
        'pending' => 'on the way',
        'on the way' => 'start',
        'start' => 'mark as done',
    ];

    $deepCleaning['next_status'] = $statusFlow[$deepCleaning->status] ?? 'on the way';

    // Fix status wording
    $deepCleaning['status'] = ($deepCleaning->status ?? '') === "mark as done"
        ? "completed"
        : ($deepCleaning->status ?? '');

    return response()->json($deepCleaning);
}

    
    public function update(Request $request, DeepCleaning $deepCleaning)
    {
        if ($deepCleaning->status === 'mark as done') {
            return response()->json([
                'status' => 200,
                'message' => 'Deep Cleaning Task Already Updated',
                'cleaning' => $deepCleaning
            ]);
        } else {
            try {
                 $deepCleaning->update([
                    'status' => $request->status,
                ]);

                $user_id = Auth::user()->id;

                DeepcleaningStatusLog::create([
                    'deepcleaning_id' => $deepCleaning->id,
                    'user_id' => $user_id, 
                    'status' => $request->status
                ]);

                $statusFlow = [
            'pending' => 'on the way',
            'on the way' => 'start',
            'start' => 'mark as done',
            //'resume' => 'resume'
            
        ];     
        
                $deepCleaning['next_status'] = $statusFlow[$deepCleaning['status']] ?? 'on the way';
                
                return response()->json([
                    'status' => 204,
                    'message' => 'Deep Cleaning Task Updated Successfully',
                    'cleaning' => $deepCleaning
                ]);
            } catch (\Exception $e) {
                return response($e);
            }
        }
    }
    
    public function createDeepCleaningComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'deepcleaning_id' => 'required',
            'comments' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data  = $request->all();
        $user_id = Auth::user()->id;
        $data['user_id'] = $user_id;
        
        DeepcleaningComment::create($data);

        return response()->json([
            'status' => 'Success',
            'message' => 'Comment Created Successfully'
        ]);
    }
    
    public function uploadMultipleImages(Request $request)
    {
        
        try 
        {
          $request->validate([
            'deepcleaning_id' => 'required|integer',
            'file' => 'required',
         ]);
    
        $filePaths = []; 
    
        
        if ($request->hasFile('file')) {
     
            $images = $request->file('file');
    
           
            if (!is_array($images)) {
                $images = [$images];
            }
    
            try {
                foreach ($images as $img) {
                    if ($img instanceof \Illuminate\Http\UploadedFile) {
                       
                        $fileName = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
                        

                       
                        $filePath = $img->storeAs('deepcleaning_images', $fileName, 'public');
                       
                        
                        Deepcleaningimages::create([
                            'deepcleaning_id' => $request->input('deepcleaning_id'),
                            'file_path' => $filePath,
                        ]);
    
                         $fullUrl = url('public/storage/' . $filePath);
                        
                         $filePaths[] = $fullUrl;
                       
                         
                    }
                }
    
                
                return response()->json([
                    'message' => 'Images uploaded successfully!',
                    'paths' => $filePaths
                    
                ], 201);
    
            } catch (\Exception $e) {
               
                Log::error('Error uploading images deepcleaning Api', ['error' => $e->getMessage()]);
                return response()->json([
                    'message' => 'Failed to upload images',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
    
        
        return response()->json([
            'message' => 'No images uploaded'
        ], 400);
        }
        catch (\Exception $e) {
            Log::error('Error uploading images deepcleaning Api', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

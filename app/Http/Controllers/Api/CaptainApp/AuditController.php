<?php

namespace App\Http\Controllers\Api\CaptainApp;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Listing;
use App\Models\AuditStatusLog;
use App\Models\User;
use App\Models\Auditimages;
use App\Models\AuditComment;
use App\Models\AuditTask;
use App\Models\RevenueActivationAudit;
use App\Models\Hostaboard;
use App\Models\DeepCleaning;
use App\Models\AuditListing;
use App\Models\SalesActivationAudit;
use App\Models\AuditBackendOp;
use App\Models\PhotographyStatusLog;
use App\Models\AuditTaskImages;
use Carbon\Carbon;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Validator;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $startDate = isset($request->start_date) && $request->start_date ? $request->start_date : null;
        $endDate = isset($request->end_date) && $request->end_date ? $request->end_date : null;

        // $audit = Audit::orderBy('audit_date', 'desc')->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {

        $audit = Audit::orderBy('audit_date', 'desc')
            ->when($user->role_id !== 6, function ($query) use ($user) {
                return $query->where('assignTo', $user->id);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('audit_date', [$startDate, $endDate]);
            })
            ->select('id', 'listing_title', 'listing_id', 'audit_date as date', 'status')
            ->get() ->map(function ($item) { $item->status = in_array($item->status, ['mark as done', 'completed']) ? 'completed' : 'pending'; return $item; });


        return response()->json(
            $audit
        );
    }

    public function getAuditCount(Request $request)
    {
        $user = Auth::user();
        $startDate = isset($request->start_date) && $request->start_date ? $request->start_date : null;
        $endDate = isset($request->end_date) && $request->end_date ? $request->end_date : null;

        $completedAudit = Audit::orderBy('audit_date', 'desc')
            ->when($user->role_id !== 6, function ($query) use ($user) {
                return $query->where('assignTo', $user->id);
            })
            ->whereIn('status', ['completed', 'mark as done'])
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('audit_date', [$startDate, $endDate]);
            })
            ->select('id', 'listing_title', 'listing_id', 'audit_date as date', 'status')
            ->get();

        $audit = Audit::orderBy('audit_date', 'desc')
            ->when($user->role_id !== 6, function ($query) use ($user) {
                return $query->where('assignTo', $user->id);
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('audit_date', [$startDate, $endDate]);
            })
            ->select('id', 'listing_title', 'listing_id', 'audit_date as date', 'status')
            ->get();



        return response([
            'total_tasks' => count($audit),
            'completed_task' => count($completedAudit)
        ]);
    }

    public function show($id)
    {
        $deepCleaning = Audit::whereId($id)->first();

        $activation = hostaboard::find($deepCleaning->host_activation_id);



        $assignTouser = null;
        if (!empty($deepCleaning->assignTo)) {

            $assignTouser = User::whereId($deepCleaning->assignTo)->select('name', 'surname', 'email', 'phone')->first(); //EXP MANAGER
        }


        $listing = Listing::where('id', $deepCleaning->listing_id)->first();

        // if ($listing) {
        //     $listing_users = json_decode($listing->user_id, true);
        //     $listing_user = is_array($listing_users) && count($listing_users) > 0 
        //         ? (int)$listing_users[count($listing_users) - 1] 
        //         : (isset($listing_users[0]) ? (int)$listing_users[0] : null);

        //     $host = $listing_user ? User::whereId($listing_user)->select('name', 'surname', 'email', 'phone')->first() : null;

        //     $listing_poc = json_decode($listing->exp_managers, true);
        //     $poc_user = is_array($listing_poc) && count($listing_poc) > 0 
        //         ? (int)$listing_poc[count($listing_poc) - 1] 
        //         : (isset($listing_poc[0]) ? (int)$listing_poc[0] : null);

        //     $poc = $poc_user ? User::whereId($poc_user)->select('name', 'surname', 'email', 'phone')->first() : null;
        // } else {
        //     $host = null;
        //     $poc = null;
        // }

        $poc = $activation->accountManager;
        $host = $activation->owner_name ?? null;

        isset($deepCleaning->id) ? $comments = AuditComment::where('audit_id', $deepCleaning->id)->orderBy('id', 'desc')->get() : $comments = null;
        isset($comments) ? $deepCleaning['remarks'] = $comments : $deepCleaning['remarks'] = '';

        isset($deepCleaning->id) ? $auditimages = Auditimages::where('audit_id', $deepCleaning->id)->orderBy('id', 'desc')->get() : $auditimages = null;

        if (isset($auditimages) && $auditimages->isNotEmpty()) {
            $deepCleaning['cleaningimages'] = $auditimages->map(function ($image) {

                $image->file_path = url('public/storage/' . $image->file_path);

                return $image;
            });
        } else {
            $deepCleaning['cleaningimages'] = [];
        }




        $deepCleaning['host'] = [
            'name' => $activation->owner_name ?? '',
            'surname' => $activation->last_name ?? '',
            'phone' => $activation->host_number ?? ''
        ];

        $deepCleaning['poc'] = [
            'name' => $poc->name ?? '',
            'surname' => $poc->surname ?? '',
            'phone' => $poc->phone ?? ''
        ];

        $deepCleaning['cleaner_Name'] = $assignTouser?->name ?? null;

        $deepCleaning['next_status'] = "completed";
        $deepCleaning['checkin_datetime'] = "";
        $deepCleaning['checkout_datetime'] = "";
        $deepCleaning['cleaner_assign_datetime'] = "";

        $statusFlow = [
            'pending' => 'on the way',
            'on the way' => 'start',
            'start' => 'resume',
            'resume' => 'resume'
        ];

        $deepCleaning['next_status'] = $statusFlow[$deepCleaning['status']] ?? 'on the way';

        $deepCleaning['status'] = ($deepCleaning['status'] ?? '') === "mark as done" ? "completed" : ($deepCleaning['status'] ?? '');

        return response($deepCleaning);
    }

    public function oldshow($id)
    {
        $deepCleaning = Audit::whereId($id)->first();
        $host = User::where('id', $deepCleaning->host_id)->first();
        $poc = User::where('id', $deepCleaning->poc)->first();
        if ($host) {
            $deepCleaning['host'] = $host;
        } else {
            $deepCleaning['host'] = [
                'name' => $deepCleaning->host_name,
                'surname' => '',
                'phone' => $deepCleaning->host_phone
            ];
        }

        if ($poc) {
            $deepCleaning['poc'] = $poc;
        } else {
            $deepCleaning['poc'] = [
                'name' => $deepCleaning->poc_name,
                'surname' => '',
                'phone' => ''
            ];
        }
        $deepCleaning['next_status'] = "completed";
        $deepCleaning['checkin_datetime'] = "";
        $deepCleaning['checkout_datetime'] = "";

        return response($deepCleaning);
    }


    public function update(Request $request, Audit $audit)
    {
        if ($audit->status === 'mark as done') {
            return response()->json([
                'status' => 200,
                'message' => 'Audit Task Already Updated',
                'cleaning' => $audit
            ]);
        } else {
            try {
                $audit->update([
                    'status' => $request->status,
                ]);

                $user_id = Auth::user()->id;

                AuditStatusLog::create([
                    'audit_id' => $audit->id,
                    'user_id' => $user_id,
                    'status' => $request->status
                ]);

                $statusFlow = [
                    'pending' => 'on the way',
                    'on the way' => 'start',
                    'start' => 'resume',
                    'resume' => 'resume'
                ];

                $audit['next_status'] = $statusFlow[$audit['status']] ?? 'on the way';

                return response()->json([
                    'status' => 204,
                    'message' => 'Audit Task Updated Successfully',
                    'cleaning' => $audit
                ]);
            } catch (\Exception $e) {
                return response($e);
            }
        }
    }

    public function createAuditComment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'audit_id' => 'required',
            'comments' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $data = $request->all();
        $user_id = Auth::user()->id;
        $data['user_id'] = $user_id;

        AuditComment::create($data);
        return response()->json([
            'status' => 'Success',
            'message' => 'Comment Created Successfully'
        ]);
    }

    public function uploadMultipleImages(Request $request)
    {

        try {
            $request->validate([
                'audit_id' => 'required|integer',
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



                            $filePath = $img->storeAs('audit_images', $fileName, 'public');


                            Auditimages::create([
                                'audit_id' => $request->input('audit_id'),
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

                    Log::error('Error uploading images Audit Api', ['error' => $e->getMessage()]);
                    return response()->json([
                        'message' => 'Failed to upload images',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }


            return response()->json([
                'message' => 'No images uploaded'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error uploading images Audit Api', ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function GetAuditChecklist($audit_id)
    {
        try {

            $auditlist = DB::select("CALL get_audit_list($audit_id);");
            $totalTasksSum = collect($auditlist)->sum('total_tasks');
            $completedTasksSum = collect($auditlist)->sum('completed_tasks');
            $is_button_show = false;


            $taskCount = DB::table('audit_tasks as aut')
                ->join('audit_checklist_detailed as acd', 'acd.id', '=', 'aut.audit_checklist_detailed_id')
                ->join('audit_checklist as ac', 'ac.id', '=', 'acd.audit_checklist_id')
                ->where('aut.audit_id', $audit_id)
                ->whereIn('ac.type', ['Inventory', 'Maintenance', 'Deep Cleaning'])
                ->whereIn('ac.task', ['Minor / Major', 'Deep Cleaning Required ?'])
                ->count();


            if ($taskCount >= 3) {
                $is_button_show = true;
            }




            return response()->json([
                'tasks' => $auditlist,
                'sum_of_tasks' => $totalTasksSum,
                'sum_of_completed_tasks' => $completedTasksSum,
                'is_button_show' => $is_button_show
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }

    public function GetAuditSectionChecklist($audit_id, $audit_detail_id)
    {
        try {

            $auditlist = DB::select("CALL get_audit_section_list($audit_id,$audit_detail_id);");
            $totalTasksSum = collect($auditlist)->sum('total_tasks');
            $completedTasksSum = collect($auditlist)->sum('completed_tasks');

            return response()->json([
                'tasks' => $auditlist,
                'sum_of_tasks' => $totalTasksSum,
                'sum_of_completed_tasks' => $completedTasksSum
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }
    }



    private function decodeIfJson($value)
    {
        // If already an array, return as-is
        if (is_array($value)) {
            return $value;
        }

        // Only try to decode if it's a string
        if (!is_string($value)) {
            return $value;
        }

        // Attempt JSON decode
        $decoded = json_decode($value, true);

        // Only return decoded if it's an array
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        // Otherwise, return original value
        return $value;
    }


    public function GetAuditTaskChecklist($audit_id, $audit_detail_id)
    {
        try {
            $user_id = Auth::user()->id;

            // Call your stored procedure
            $checklist = DB::select("CALL get_audit_task_list(?, ?)", [$audit_id, $audit_detail_id]);


            // Initialize array to group by section
            $grouped = [];

            foreach ($checklist as $item) {

                $sectionName = $item->name;

                // Create a key based on section ID to group tasks
                if (!isset($grouped[$sectionName])) {
                    $grouped[$sectionName] = [

                        'name' => $sectionName,
                        'fields' => []
                    ];
                }

                $audittaskimages = AuditTaskImages::where('audit_id', $item->audit_id)->where('host_activation_id', $item->host_activation_id)->where('audit_checklist_detailed_id', $item->id)->orderBy('id', 'desc')->get()->map(function ($image) {
                    $image->file_path = url('public/storage/' . $image->file_path);
                    return $image;
                });


                $value = $item->field === 'fileupload' ? $audittaskimages : $this->decodeIfJson($item->value);
                // Push task details into 'fields' of corresponding section
                $grouped[$sectionName]['fields'][] = [
                    'id' => $item->id,
                    'audit_id' => $item->audit_id,
                    'host_activation_id' => $item->host_activation_id,
                    'name' => $item->task,
                    'field' => $item->field,
                    'options' => $item->options ? json_decode($item->options) : [],
                    'value' => $value


                ];
            }

            // Return re-indexed grouped result
            return response()->json(['sections' => array_values($grouped)], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }



    // public function updateAuditTask(Request $request)
// {
//     try {
//         $taskData = $request->all();

    //         // Normalize to array if single object is sent
//         if (isset($taskData['audit_checklist_detailed_id'])) {
//             $taskData = [$taskData];
//         }

    //         if (empty($taskData)) {
//             return response()->json(['message' => 'No data provided'], 400);
//         }

    //         // === Handle Checkbox Field ===
//         if ($request->field === 'checkbox') {
//             if (isset($taskData['id'])) {
//                 $taskData = [$taskData];
//             }

    //             $grouped = [];

    //             foreach ($taskData as $item) {
//                 $key = $item['id'];

    //                 if (is_array($item['value'])) {
//                     $grouped[$key]['values'] = array_merge($grouped[$key]['values'] ?? [], $item['value']);
//                 } else {
//                     $grouped[$key]['values'][] = $item['value'] ?? null;
//                 }

    //                 $grouped[$key]['audit_id'] = $item['audit_id'];
//                 $grouped[$key]['host_activation_id'] = $item['host_activation_id'];
//             }

    //             foreach ($grouped as $key => $group) {
//                 $finalValue = json_encode(array_unique($group['values']));

    //                 AuditTask::updateOrCreate(
//                     [
//                         'audit_id' => $group['audit_id'],
//                         'host_activation_id' => $group['host_activation_id'],
//                         'audit_checklist_detailed_id' => $key,
//                     ],
//                     ['value' => $finalValue]
//                 );
//             }

    //             return response()->json(['message' => 'Checkbox audit tasks saved successfully'], 200);
//         }

    //         // === Handle File Upload Field (multi-upload supported) ===
//         else if ($request->field === 'fileupload') {
//             $auditId = $request->input('audit_id');
//             $hostActivationId = $request->input('host_activation_id');
//             $checklistDetailId = $request->input('id');

    //             $fileUrls = [];

    //             if ($request->hasFile('file')) {

    //                 $images = $request->file('file');

    //                 if (!is_array($images)) {
//                     $images = [$images];
//                 }

    //                 foreach ($images as $img) {
//                     if ($img instanceof \Illuminate\Http\UploadedFile) {

    //                         $fileName = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
//                                                 // dd($fileName);


    //                         $filePath = $img->storeAs('audit', $fileName, 'public');


    //                         AuditTaskImages::create([
//                             'audit_id' => $auditId,
//                             'host_activation_id' => $hostActivationId,
//                             'audit_checklist_detailed_id' => $checklistDetailId,
//                             'file_path' => $filePath,
//                         ]);




    //                          $fullUrl = url('public/storage/' . $filePath);

    //                          $filePaths[] = $fullUrl;

    //                         Log::info('Image uploaded and saved', ['file_path' => $filePath]);
//                     }
//                 }


    //                 // $uploadedFiles = $request->file('file');
//                 // if (!is_array($uploadedFiles)) {
//                 //     $uploadedFiles = [$uploadedFiles];
//                 // }

    //                 // foreach ($uploadedFiles as $uploadedFile) {
//                 //     $fileName = time() . '_' . str_replace(' ', '_', $uploadedFile->getClientOriginalName());
//                 //     $filePath = $uploadedFile->storeAs('audit', $fileName, 'public');
//                 //     $relativeUrl = Storage::url($filePath);
//                 //     $fileUrls[] = asset($relativeUrl);
//                 // }

    //                 // $existingTask = AuditTask::where([
//                 //     ['audit_id', '=', $auditId],
//                 //     ['host_activation_id', '=', $hostActivationId],
//                 //     ['audit_checklist_detailed_id', '=', $checklistDetailId]
//                 // ])->first();

    //                 // $existingFiles = [];

    //                 // if ($existingTask && !empty($existingTask->value)) {
//                 //     $decoded = json_decode($existingTask->value, true);
//                 //     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
//                 //         $existingFiles = $decoded;
//                 //     }
//                 // }

    //                 // $allFiles = array_unique(array_merge($existingFiles, $fileUrls));
//                 // $finalValue = json_encode($allFiles, JSON_UNESCAPED_SLASHES);

    //                 AuditTask::updateOrCreate(
//                     [
//                         'audit_id' => $auditId,
//                         'host_activation_id' => $hostActivationId,
//                         'audit_checklist_detailed_id' => $checklistDetailId,
//                     ],
//                     ['value' => "saved"]
//                 );

    //                 return response()->json(['message' => 'File(s) uploaded successfully'], 200);
//             }

    //             return response()->json(['error' => 'No file uploaded'], 400);
//         }

    //         // === Handle Text / Radio / Matrix Fields ===
//         else {
//             foreach ($taskData as $item) {
//                 $auditId = $item['audit_id'];
//                 $hostActivationId = $item['host_activation_id'];
//                 $checklistDetailId = $item['id'];
//                 $finalValue = $item['value'] ?? null;

    //                 if ($finalValue === null) continue;

    //                 AuditTask::updateOrCreate(
//                     [
//                         'audit_id' => $auditId,
//                         'host_activation_id' => $hostActivationId,
//                         'audit_checklist_detailed_id' => $checklistDetailId,
//                     ],
//                     ['value' => $finalValue]
//                 );

    //                 // Fetch task info
//                 $taskdata = DB::table('audit_checklist as acl')
//                     ->join('audit_checklist_detailed as acd', 'acd.audit_checklist_id', '=', 'acl.id')
//                     ->where('acd.id', $checklistDetailId)
//                     ->select('acl.task', 'acd.area')
//                     ->first();

    //                 $TaskName = $taskdata->task ?? null;
//                 $TaskArea = $taskdata->area ?? null;

    //                 $Hostaboard = Hostaboard::find($hostActivationId);

    //                 if ($Hostaboard) {
//                     if ($TaskName == 'Door Lock Passcode') {
//                         $Hostaboard->door_lock_code = $finalValue;
//                     } elseif ($TaskName == 'Wifi Password') {
//                         $Hostaboard->wi_fi_password = $finalValue;
//                     }
//                     $Hostaboard->save();
//                 }


    //                 if($TaskName == 'Deep Cleaning Required?' & $finalValue == 'yes')
//                 {


    //                     $deepCleaning = DeepCleaning::updateOrCreate(
//                     ['host_activation_id' => $hostActivationId], // Search criteria
//                         [
//                          'listing_title' => $Hostaboard->title,
//                          'listing_id' => null,
//                          'host_id' => null,
//                          'host_name' => $Hostaboard->owner_name,
//                          'host_phone' => $Hostaboard->host_number,
//                          'poc' => null,
//                          'poc_name' => null,
//                          'audit_id' => $auditId,

    //                         'assignToVendor' => 0,
//                         'assignToPropertyManager' => 0,
//                         'start_date' => null,
//                         'end_date' => null,
//                         'cleaning_date' => now()->toDateString(),
//                         'location' => $Hostaboard->property_google_map_link,
//                         'key_code' => $Hostaboard->door_lock_code,

    //                         'status' => 'pending', 
//                         'remarks' => null,
//                         'host_activation_id' => $Hostaboard->id,
//                         'type' => $Hostaboard->type,
//                         'floor' => $Hostaboard->floor,
//                         'unit_type' => $Hostaboard->unit_type,
//                         'unit_number' => $Hostaboard->unit_number, 
//                     ]);

    //                 }

    //                 if (in_array($TaskArea, ['Maintenance', 'Inventory'])) {
//                     $salesAudit = SalesActivationAudit::firstOrNew([
//                         'hostaboard_id' => $hostActivationId,
//                         'audit_id' => $auditId,
//                         'task_type' => $TaskArea,
//                     ]);

    //                     $shouldNotify = false;

    //                     if ($TaskName == "Add Any Comments") {
//                         $salesAudit->remarks = $finalValue;
//                     }

    //                     if ($TaskName == "Is Major Inventory Needed?") {
//                         if (strtolower($finalValue) === 'yes') {
//                             $shouldNotify = true;
//                             $salesAudit->is_required = 1;
//                             $salesAudit->minor_major = 1;
//                         } elseif (strtolower($finalValue) === 'no') {
//                             $salesAudit->minor_major = 0;
//                             $salesAudit->is_required = 0;
//                         }
//                     }

    //                     if ($TaskName == "Is Major Maintenance Needed?") {
//                         if (strtolower($finalValue) === 'yes') {
//                             $shouldNotify = true;
//                             $salesAudit->is_required = 1;
//                             $salesAudit->minor_major = 1;
//                         } elseif (strtolower($finalValue) === 'no') {
//                             $salesAudit->minor_major = 0;
//                             $salesAudit->is_required = 0;
//                         }
//                     }

    //                     $salesAudit->updated_by = Auth::id();
//                     $salesAudit->save();

    //                     if ($shouldNotify) {
//                         notifyheader(
//                             $TaskArea == 'Maintenance' ? 6 : 7,
//                             $TaskArea == 'Maintenance' ? 'Maintenance Review' : 'Inventory Check Required',
//                             $salesAudit->id,
//                             "{$TaskArea} for {$Hostaboard->title} Property",
//                             "Inspect and validate the details submitted for the property.",
//                             url("/sales-activation-audit/{$salesAudit->id}/edit"),
//                             false
//                         );
//                     }
//                 }

    //                 // === Final Approval Checks ===
//                 $inventoryClear = SalesActivationAudit::where([
//                     ['hostaboard_id', '=', $hostActivationId],
//                     ['audit_id', '=', $auditId],
//                     ['task_type', '=', 'Inventory'],
//                     ['is_required', '=', 0]
//                 ])->first();

    //                 $maintenanceClear = SalesActivationAudit::where([
//                     ['hostaboard_id', '=', $hostActivationId],
//                     ['audit_id', '=', $auditId],
//                     ['task_type', '=', 'Maintenance'],
//                     ['is_required', '=', 0]
//                 ])->first();

    //                 $revenueApproved = RevenueActivationAudit::where([
//                     ['hostaboard_id', '=', $hostActivationId],
//                     ['status', '=', 'approved']
//                 ])->first();

    //                 if ($inventoryClear && $maintenanceClear && $revenueApproved) {
//                     if ($Hostaboard->co_hosting_account == 0) {
//                         AuditListing::updateOrCreate(
//                             [
//                                 'hostaboard_id' => $hostActivationId,
//                                 'audit_id' => $auditId,
//                             ],
//                             [
//                                 'updated_by' => Auth::id()
//                             ]
//                         );
//                     }

    //                     if ($Hostaboard->co_hosting_account == 1) {
//                         AuditBackendOp::updateOrCreate(
//                             [
//                                 'hostaboard_id' => $hostActivationId,
//                                 'audit_id' => $auditId,
//                             ],
//                             [
//                                 'updated_by' => Auth::id()
//                             ]
//                         );
//                     }
//                 }
//             }

    //             return response()->json(['message' => 'Text/Radio/Matrix audit tasks saved successfully'], 200);
//         }
//     } catch (\Exception $e) {
//         return response()->json([
//             'message' => 'Error saving audit task',
//             'error' => $e->getMessage()
//         ], 500);
//     }
// }

    public function updateAuditTask(Request $request)
    {
        try {
            $taskData = $request->all();

            // Normalize to array if single object is sent
            if (isset($taskData['audit_checklist_detailed_id'])) {
                $taskData = [$taskData];
            }

            if (empty($taskData)) {
                return response()->json(['message' => 'No data provided'], 400);
            }

            // === Handle Checkbox Field ===

            // === Handle Checkbox Field ===
            if ($request->field === 'checkbox') {
                if (isset($taskData['id'])) {
                    $taskData = [$taskData];
                }

                $grouped = [];

                foreach ($taskData as $item) {
                    $key = $item['id'];

                    if (is_array($item['value'])) {
                        $grouped[$key]['values'] = array_merge($grouped[$key]['values'] ?? [], $item['value']);
                    } else {
                        $grouped[$key]['values'][] = $item['value'] ?? null;
                    }

                    $grouped[$key]['audit_id'] = $item['audit_id'];
                    $grouped[$key]['host_activation_id'] = $item['host_activation_id'];
                }

                foreach ($grouped as $key => $group) {
                    $finalValue = json_encode(array_unique($group['values']));

                    AuditTask::updateOrCreate(
                        [
                            'audit_id' => $group['audit_id'],
                            'host_activation_id' => $group['host_activation_id'],
                            'audit_checklist_detailed_id' => $key,
                        ],
                        ['value' => $finalValue]
                    );
                }

                return response()->json(['message' => 'Checkbox audit tasks saved successfully'], 200);
            }


            // === Handle File Upload Field (with multi upload support) ===
            // === Handle File Upload Field (multi-upload supported) ===
            else if ($request->field === 'fileupload') {
                $auditId = $request->input('audit_id');
                $hostActivationId = $request->input('host_activation_id');
                $checklistDetailId = $request->input('id');

                $fileUrls = [];

                if ($request->hasFile('file')) {

                    $images = $request->file('file');

                    if (!is_array($images)) {
                        $images = [$images];
                    }

                    foreach ($images as $img) {
                        if ($img instanceof \Illuminate\Http\UploadedFile) {

                            $fileName = time() . '_' . uniqid() . '.' . $img->getClientOriginalExtension();
                            // dd($fileName);


                            $filePath = $img->storeAs('audit', $fileName, 'public');


                            AuditTaskImages::create([
                                'audit_id' => $auditId,
                                'host_activation_id' => $hostActivationId,
                                'audit_checklist_detailed_id' => $checklistDetailId,
                                'file_path' => $filePath,
                            ]);




                            $fullUrl = url('public/storage/' . $filePath);

                            $filePaths[] = $fullUrl;

                            Log::info('Image uploaded and saved', ['file_path' => $filePath]);
                        }
                    }


                    // $uploadedFiles = $request->file('file');
                    // if (!is_array($uploadedFiles)) {
                    //     $uploadedFiles = [$uploadedFiles];
                    // }

                    // foreach ($uploadedFiles as $uploadedFile) {
                    //     $fileName = time() . '_' . str_replace(' ', '_', $uploadedFile->getClientOriginalName());
                    //     $filePath = $uploadedFile->storeAs('audit', $fileName, 'public');
                    //     $relativeUrl = Storage::url($filePath);
                    //     $fileUrls[] = asset($relativeUrl);
                    // }

                    // $existingTask = AuditTask::where([
                    //     ['audit_id', '=', $auditId],
                    //     ['host_activation_id', '=', $hostActivationId],
                    //     ['audit_checklist_detailed_id', '=', $checklistDetailId]
                    // ])->first();

                    // $existingFiles = [];

                    // if ($existingTask && !empty($existingTask->value)) {
                    //     $decoded = json_decode($existingTask->value, true);
                    //     if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    //         $existingFiles = $decoded;
                    //     }
                    // }

                    // $allFiles = array_unique(array_merge($existingFiles, $fileUrls));
                    // $finalValue = json_encode($allFiles, JSON_UNESCAPED_SLASHES);

                    AuditTask::updateOrCreate(
                        [
                            'audit_id' => $auditId,
                            'host_activation_id' => $hostActivationId,
                            'audit_checklist_detailed_id' => $checklistDetailId,
                        ],
                        ['value' => 'save']
                    );

                    return response()->json(['message' => 'File(s) uploaded successfully'], 200);
                }

                return response()->json(['error' => 'No file uploaded'], 400);
            }

            // === Handle Text / Radio / Matrix Fields ===
            else {
                foreach ($taskData as $item) {
                    $auditId = $item['audit_id'];
                    $hostActivationId = $item['host_activation_id'];
                    $checklistDetailId = $item['id'];
                    $finalValue = $item['value'] ?? null;

                    if ($finalValue === null)
                        continue;

                    AuditTask::updateOrCreate(
                        [
                            'audit_id' => $auditId,
                            'host_activation_id' => $hostActivationId,
                            'audit_checklist_detailed_id' => $checklistDetailId,
                        ],
                        ['value' => $finalValue]
                    );

                    $taskdata = DB::table('audit_checklist as acl')
                        ->join('audit_checklist_detailed as acd', 'acd.audit_checklist_id', '=', 'acl.id')
                        ->where('acd.id', $checklistDetailId)
                        ->select('acl.task', 'acd.area')
                        ->first();

                    $TaskName = $taskdata->task ?? null;
                    $TaskArea = $taskdata->area ?? null;

                    $Hostaboard = Hostaboard::find($hostActivationId);

                    if ($Hostaboard) {
                        if ($TaskName == 'Door Lock Passcode') {
                            $Hostaboard->door_lock_code = $finalValue;
                        } elseif ($TaskName == 'Wifi Password') {
                            $Hostaboard->wi_fi_password = $finalValue;
                        }
                        $Hostaboard->save();
                    }


                    if ($TaskName == 'Deep Cleaning Required?' & $finalValue == 'yes') {


                        $deepCleaning = DeepCleaning::updateOrCreate(
                            ['host_activation_id' => $hostActivationId], // Search criteria
                            [
                                'listing_title' => $Hostaboard->title,
                                'listing_id' => null,
                                'host_id' => null,
                                'host_name' => $Hostaboard->owner_name,
                                'host_phone' => $Hostaboard->host_number,
                                'poc' => null,
                                'poc_name' => null,
                                'audit_id' => $auditId,

                                'assignToVendor' => 0,
                                'assignToPropertyManager' => 0,
                                'start_date' => null,
                                'end_date' => null,
                                'cleaning_date' => now()->toDateString(),
                                'location' => $Hostaboard->property_google_map_link,
                                'key_code' => $Hostaboard->door_lock_code,

                                'status' => 'pending',
                                'remarks' => null,
                                'host_activation_id' => $Hostaboard->id,
                                'type' => $Hostaboard->type,
                                'floor' => $Hostaboard->floor,
                                'unit_type' => $Hostaboard->unit_type,
                                'unit_number' => $Hostaboard->unit_number,
                            ]
                        );

                    }

                    if (in_array($TaskArea, ['Maintenance', 'Inventory'])) {
                        $salesAudit = SalesActivationAudit::firstOrNew([
                            'hostaboard_id' => $hostActivationId,
                            'audit_id' => $auditId,
                            'task_type' => $TaskArea,
                        ]);

                        $shouldNotify = false;

                        if ($TaskName == "Add Any Comments") {
                            $salesAudit->remarks = $finalValue;
                        }

                        if ($TaskName == "Is Major Inventory Needed?") {
                            if (strtolower($finalValue) === 'yes') {
                                $shouldNotify = true;
                                $salesAudit->is_required = 1;
                                $salesAudit->minor_major = 1;
                            } elseif (strtolower($finalValue) === 'no') {
                                $salesAudit->minor_major = 0;
                                $salesAudit->is_required = 0;
                            }
                        }

                        if ($TaskName == "Is Major Maintenance Needed?") {
                            if (strtolower($finalValue) === 'yes') {
                                $shouldNotify = true;
                                $salesAudit->is_required = 1;
                                $salesAudit->minor_major = 1;
                            } elseif (strtolower($finalValue) === 'no') {
                                $salesAudit->minor_major = 0;
                                $salesAudit->is_required = 0;
                            }
                        }

                        $salesAudit->updated_by = Auth::id();
                        $salesAudit->save();

                        if ($shouldNotify) {
                            notifyheader(
                                $TaskArea == 'Maintenance' ? 6 : 7,
                                $TaskArea == 'Maintenance' ? 'Maintenance Review' : 'Inventory Check Required',
                                $salesAudit->id,
                                "{$TaskArea} for {$Hostaboard->title} Property",
                                "Inspect and validate the details submitted for the property.",
                                url("/sales-activation-audit/{$salesAudit->id}/edit"),
                                false
                            );
                        }
                    }

                    // === Final Approval Checks ===
                    $inventoryClear = SalesActivationAudit::where([
                        ['hostaboard_id', '=', $hostActivationId],
                        ['audit_id', '=', $auditId],
                        ['task_type', '=', 'Inventory'],
                        ['is_required', '=', 0]
                    ])->first();

                    $maintenanceClear = SalesActivationAudit::where([
                        ['hostaboard_id', '=', $hostActivationId],
                        ['audit_id', '=', $auditId],
                        ['task_type', '=', 'Maintenance'],
                        ['is_required', '=', 0]
                    ])->first();

                    $revenueApproved = RevenueActivationAudit::where([
                        ['hostaboard_id', '=', $hostActivationId],
                        ['status', '=', 'approved']
                    ])->first();

                    if ($inventoryClear && $maintenanceClear && $revenueApproved) {
                        if ($Hostaboard->co_hosting_account == 0) {
                            AuditListing::updateOrCreate(
                                [
                                    'hostaboard_id' => $hostActivationId,
                                    'audit_id' => $auditId,
                                ],
                                [
                                    'updated_by' => Auth::id()
                                ]
                            );
                        }

                        if ($Hostaboard->co_hosting_account == 1) {
                            AuditBackendOp::updateOrCreate(
                                [
                                    'hostaboard_id' => $hostActivationId,
                                    'audit_id' => $auditId,
                                ],
                                [
                                    'updated_by' => Auth::id()
                                ]
                            );
                        }
                    }


                }

                return response()->json(['message' => 'Text/Radio/Matrix audit tasks saved successfully'], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error saving audit task',
                'error' => $e->getMessage()
            ], 500);
        }
    }




    public function deleteAuditTaskImage($id)
    {
        try {
            $image = AuditTaskImages::find($id);

            if (!$image) {
                return response()->json(['message' => 'Image not found.'], 404);
            }

            $checklistId = $image->audit_checklist_detailed_id;

            // Optional: Delete file from storage if stored locally
            if ($image->file_path && Storage::exists('public/' . $image->file_path)) {
                Storage::delete('public/' . $image->file_path);
            }

            $image->delete();


            $remainingCount = AuditTaskImages::where('audit_checklist_detailed_id', $checklistId)->count();

            // If no more images, delete the task as well
            if ($remainingCount === 0) {
                DB::table('audit_tasks')->where('audit_checklist_detailed_id', $checklistId)->delete();
            }

            return response()->json(['message' => 'Image deleted successfully.'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    //photo review



    public function GetPhotoTasks(Request $request)
    {
        $user = Auth::user();

        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;

        $photoreview = RevenueActivationAudit::with('Hostaboard')
            ->when($user->role_id == 6, function ($query) {
                return $query->where('task_status', '!=', 'no required');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->orderBy('created_at', 'desc')
            // Removed select() to allow full model + relations
            ->get()
            ->map(function ($item) {
                $status = 'pending'; // Default
    
                if (strtolower($item->task_status) === 'mark as done' && strtolower($item->status) === 'approved') {
                    $status = 'completed';
                }
                if (strtolower($item->status) === 'mark as done') {
                    $status = 'in review';
                }

                return [
                    'id' => $item->id,
                    'listing_title' => $item->Hostaboard->title ?? null,
                    'Hostaboard_id' => $item->hostaboard_id,
                    'status' => $status,
                    'task_status' => $item->task_status,
                    'remarks' => $item->remarks,
                    'task_remarks' => $item->task_remarks,
                    'location' => $item->Hostaboard->property_google_map_link,
                    'photos' => $item->url,
                    'updated_by' => $item->updated_by,
                    'date' => $item->created_at ? $item->created_at->format('Y-m-d') : null,
                    'city_name' => $item->Hostaboard->city_name ?? null,
                    'unit_type' => $item->Hostaboard->unit_type ?? null,
                    'type' => $item->Hostaboard->type ?? null,
                ];
            });

        return response()->json($photoreview);
    }


    public function GetPhotoTasksshow($id)
    {
        $item = RevenueActivationAudit::with('Hostaboard')->find($id);

        if (!$item) {
            return response()->json(['message' => 'Record not found.'], 404);
        }

        // Transform status

        $status = $item->status;
        if ($item->status == "mark as done") {
            $status = "in review";
        } else if ($item->status == "approved") {
            $status = "completed";
        }


        $statusFlow = [
            'pending' => 'on the way',
            'on the way' => 'start',
            'start' => 'resume',
            'resume' => 'resume'
        ];

        $item->task_status = $item->task_status === 'mark as done' ? 'completed' : $item->task_status;
        $next_status = $statusFlow[$item->task_status] ?? 'on the way';

        $poc = $item->Hostaboard->accountManager;
        $host = $item->Hostaboard->owner_name ?? null;

        $response = [
            'id' => $item->id,
            'Hostaboard_id' => $item->Hostaboard_id,
            'listing_title' => $item->Hostaboard->title ?? null,
            'status' => $status,
            'task_status' => $item->task_status,
            'remarks' => $item->remarks,
            'task_remarks' => $item->task_remarks,
            'key_code' => $item->Hostaboard->door_lock_code,

            'location' => $item->Hostaboard->property_google_map_link,
            'photos' => $item->url,
            'poc' => [
                'name' => $poc->name ?? '',
                'surname' => $poc->surname ?? '',
                'email' => $poc->email ?? '',
                'phone' => $poc->phone ?? '',
            ],

            'host' => [
                'name' => $item->Hostaboard->owner_name ?? '',
                'surname' => $item->Hostaboard->last_name ?? '',
                'email' => $item->Hostaboard->host_email ?? '',
                'phone' => $item->Hostaboard->host_number ?? '',
            ],


            'updated_by' => $item->updated_by,
            'cleaning_date' => $item->created_at ? $item->created_at->format('Y-m-d') : null,


            'city_name' => $item->Hostaboard->city_name ?? null,

            'unit_type' => $item->Hostaboard->unit_type ?? null,
            'type' => $item->Hostaboard->type ?? null,
            'next_status' => $next_status,
        ];

        return response()->json($response);
    }


    public function GetPhotoTasksCount(Request $request)
    {
        $user = Auth::user();

        $startDate = $request->start_date ?? null;
        $endDate = $request->end_date ?? null;

        // Count total tasks (Generated + Completed)
        $photoreview = RevenueActivationAudit::with('Hostaboard')
            ->when($user->role_id == 6, function ($query) {
                return $query->where('task_status', '!=', 'no required');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        // Count completed tasks only
        $photoreviewcompleted = RevenueActivationAudit::with('Hostaboard')
            ->when($user->role_id == 6, function ($query) {
                 return $query->where('task_status', 'mark as done')
                     ->where('status', 'approved');
            })
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                return $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        // $data = [
        //     'total_tasks' => $photoreview->count(),
        //     'completed_task' => $photoreviewcompleted->count()
        // ];

        return response([
            'total_tasks' => $photoreview->count(),
            'completed_task' => $photoreviewcompleted->count()
        ]);
        //return apiResponse('success', 'Photo task counts retrieved successfully', $data);
    }


    public function updatephotoreview(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string',
            'url' => [
                'nullable',
                'string',
                'regex:/^https?:\/\/(drive\.google\.com)\/.+$/'
            ]
        ], [
            'url.regex' => 'The URL must be a valid Google Drive link.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $audit = RevenueActivationAudit::with('Hostaboard')->find($id);

            if (!$audit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'RevenueActivationAudit not found'
                ], 404);
            }

            // Update audit fields
            $audit->task_remarks = $request->task_remarks;
            $audit->updated_by = $user->id;
            $audit->url = $request->url;
            $audit->status = $request->status;
            $audit->task_status = $request->status;
            $audit->save();

            // Update Hostaboard if exists
            if ($audit->Hostaboard) {
                $audit->Hostaboard->property_images_link = $request->url;
                $audit->Hostaboard->save();
            }

            // Log status update
            PhotographyStatusLog::create([
                'revenue_activation_audit_id' => $audit->id,
                'hostaboard_id' => $audit->hostaboard_id,
                'user_id' => $user->id,
                'status' => $request->status,
            ]);

            // Fallback status handling
            if (
                strtolower($audit->status) !== 'approved' &&
                strtolower($audit->status) !== 'declined' &&
                strtolower($audit->task_status) !== 'completed'
            ) {
                $audit->status = 'pending';
            }

            // Next status flow
            $statusFlow = [
                'pending' => 'on the way',
                'on the way' => 'start',
                'start' => 'resume',
                'resume' => 'resume'
            ];

            $taskStatus = strtolower($audit->task_status ?? '');
            $nextStatus = $statusFlow[$taskStatus] ?? 'on the way';

            // Response structure
            $response = [
                'id' => $audit->id,
                'Hostaboard_id' => $audit->hostaboard_id,
                'status' => $audit->status,
                'task_status' => $audit->task_status,
                'remarks' => $audit->remarks,
                'task_remarks' => $audit->task_remarks,
                'location' => $audit->Hostaboard->property_google_map_link,
                'photos' => $audit->Hostaboard->property_images_link,
                'updated_by' => $audit->updated_by,
                'date' => $audit->created_at ? $audit->created_at->format('Y-m-d') : null,
                'host_title' => $audit->Hostaboard->title ?? null,
                'city_name' => $audit->Hostaboard->city_name ?? null,
                'owner_name' => $audit->Hostaboard->owner_name ?? null,
                'unit_type' => $audit->Hostaboard->unit_type ?? null,
                'type' => $audit->Hostaboard->type ?? null,
                'next_status' => $nextStatus,
            ];

            // Trigger notification if task is done
            if (strtolower($audit->task_status) === 'mark as done') {
                notifyheader(
                    5,
                    'Revenue Photo Review',
                    $response['id'],
                    "Review Photos for {$response['host_title']} Property Activation",
                    "Review and approve the property images taken by the On-Ground Team",
                    url("/revenue-activation-audit/{$response['id']}/edit"),
                    false
                );
            }

            return response()->json([
                'status' => 204,
                'message' => 'Updated Successfully',
                'photoreview' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatephotostatus(Request $request, $id)
    {


        try {

            $user = Auth::user();
            $audit = RevenueActivationAudit::with('Hostaboard')->find($id);

            if (!$audit) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'RevenueActivationAudit not found'
                ], 404);
            }


            $audit->updated_by = $user->id;
            $audit->status = $request->status;
            $audit->task_status = $request->status;
            $audit->save();


            PhotographyStatusLog::create([
                'revenue_activation_audit_id' => $id,
                'hostaboard_id' => $audit->hostaboard_id,
                'user_id' => $user->id,
                'status' => $request->status
            ]);

            //dd($audit);
            $Hostaboard = $audit->Hostaboard;
            if ($Hostaboard) {
                $Hostaboard->property_images_link = $request->url;
                $Hostaboard->save();
            }
            // if (
            //     strtolower($audit->status) !== 'approved' &&
            //     strtolower($audit->status) !== 'declined' &&
            //     strtolower($audit->task_status) !== 'completed'
            // ) {
            //     $audit->status = 'pending';
            // }

            $statusFlow = [
                'pending' => 'on the way',
                'on the way' => 'start',
                'start' => 'resume',
                'resume' => 'resume'
            ];

            $taskStatus = strtolower($audit->task_status ?? '');
            $nextStatus = $statusFlow[$taskStatus] ?? 'on the way';

            $response = [
                'id' => $audit->id,
                'Hostaboard_id' => $audit->hostaboard_id,
                'status' => $audit->status,
                'task_status' => $audit->task_status,
                'remarks' => $audit->remarks,
                'task_remarks' => $audit->task_remarks,
                'location' => $audit->Hostaboard->property_google_map_link,
                'photos' => $audit->Hostaboard->property_images_link,
                'updated_by' => $audit->updated_by,
                'date' => $audit->created_at ? $audit->created_at->format('Y-m-d') : null,
                'host_title' => $audit->Hostaboard->title ?? null,
                'city_name' => $audit->Hostaboard->city_name ?? null,
                'owner_name' => $audit->Hostaboard->owner_name ?? null,
                'unit_type' => $audit->Hostaboard->unit_type ?? null,
                'type' => $audit->Hostaboard->type ?? null,
                'next_status' => $nextStatus,
            ];

            if (strtolower($audit->task_status) === 'mark as done') {
                notifyheader(
                    5,
                    'Revenue Photo Review',
                    $response['id'],
                    "Review Photos for {$response['host_title']} Property Activation",
                    "Review and approve the property images taken by the On-Ground Team",
                    url("/revenue-activation-audit/{$response['id']}/edit"),
                    false
                );
            }

            return response()->json([
                'status' => 204,
                'message' => 'Updated Successfully',
                'photoreview' => $response
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    public function GetChecklist($id, $category)
    {
        try {
            $user_id = Auth::id();

            if ($category === 'audit') {
                $auditlist = DB::select("CALL get_audit_list($id);");
                $totalTasksSum = collect($auditlist)->sum('total_tasks');
                $completedTasksSum = collect($auditlist)->sum('completed_tasks');


                $is_button_show = false;


                $totalTaskCount = DB::table('audit_checklist_detailed as acd')
                    ->join('audit_checklist as ac', 'ac.id', '=', 'acd.audit_checklist_id')
                    ->where('acd.audit_id', $id)
                    ->count();

                $completedTaskCount = DB::table('audit_tasks as aut')
                    ->join('audit_checklist_detailed as acd', 'acd.id', '=', 'aut.audit_checklist_detailed_id')
                    ->join('audit_checklist as ac', 'ac.id', '=', 'acd.audit_checklist_id')
                    ->where('aut.audit_id', $id)
                    ->count();


                $is_button_show = false;

                if ($totalTaskCount === $completedTaskCount && $totalTaskCount > 0) {
                    $is_button_show = true;
                }

                $auditStatus = DB::table('audits')->whereIn('status', ['mark as done', 'completed'])->where('id', $id)->first();

                if ($auditStatus !== null) {
                    $is_button_show = false;
                }

                return response()->json([
                    'tasks' => $auditlist,
                    'sum_of_tasks' => $totalTasksSum,
                    'sum_of_completed_tasks' => $completedTasksSum,
                    'is_button_show' => $is_button_show
                ], 200);

            } elseif ($category === 'cleaning' || $category === 'cleanings') {
                $checklist = DB::select("CALL get_cleaning_checklist_v2($id);");
                $totalTasksSum = collect($checklist)->sum('total_tasks');
                $completedTasksSum = collect($checklist)->sum('completed_tasks');

                return response()->json([
                    'tasks' => $checklist,
                    'sum_of_tasks' => $totalTasksSum,
                    'sum_of_completed_tasks' => $completedTasksSum
                ], 200);

            } else {
                return response()->json([
                    'error' => 'Invalid category. Must be either "audit" or "cleaning".'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
                'details' => $e->getMessage()
            ], 500);
        }
    }


}

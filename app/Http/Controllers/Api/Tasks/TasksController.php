<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Resources\TasksResource;
use App\Models\Tasks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Services\StoreProcedureService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client;
use App\Models\Vendors;
use App\Models\User;

class TasksController extends Controller
{


    protected $client;

    protected $storeProcedureService = false;
    public function __construct(StoreProcedureService $storeProcedureService){
        $this->storeProcedureService = $storeProcedureService;
        
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $this->client = new Client($sid, $token);
    }
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $tasks = Tasks::all();
        return TasksResource::collection($tasks);
    }

    /**
     * @param Request $request
     * @return TasksResource|JsonResponse
     */
    public function store(Request $request): TasksResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'task_title' => 'required|string',
            'category_id' => 'required|integer',
            'vendor_id' => 'required|integer',
            'apartment_id' => 'required|integer',
            'stage' => 'required',
            'frequency' => 'required',
            'picture' => 'required',
            'time_duration' => 'required',
            'date_duration' => 'required',
            'completion_time' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $tasks = Tasks::create($request->all());
        return new TasksResource($tasks);
    }

    /**
     * @param Tasks $task
     * @return TasksResource
     */
    public function show(Tasks $task): TasksResource
    {
        return new TasksResource($task);
    }

    /**
     * @param $date
     * @return AnonymousResourceCollection
     */
    public function getTaskByDate($date): AnonymousResourceCollection
    {
        $tasks = Tasks::where('date_duration', $date)->get();
        return TasksResource::collection($tasks);
    }

    /**
     * @param $status
     * @return AnonymousResourceCollection
     */
    public function getTaskByStatus($status): AnonymousResourceCollection
    {
        $tasks = Tasks::where('status', $status)->get();
        return TasksResource::collection($tasks);
    }


    /**
     * @param Request $request
     * @param Tasks $task
     * @return TasksResource|JsonResponse
     */
    public function update(Request $request, Tasks $task): TasksResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'task_title' => 'sometimes|required',
            'category_id' => 'sometimes|required',
            'vendor_id' => 'sometimes|required',
            'apartment_id' => 'sometimes|required',
            'stage' => 'sometimes|required',
            'frequency' => 'sometimes|required',
            'picture' => 'sometimes|required',
            'time_duration' => 'sometimes|required',
            'date_duration' => 'sometimes|required',
            'completion_time' => 'sometimes|required',
            'status' => 'sometimes|required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $task->update($request->all());
        return new TasksResource($task);
    }

    /**
     * @param Tasks $task
     * @return TasksResource
     */
    public function destroy(Tasks $task): TasksResource
    {
        $task->delete();
        return new TasksResource($task);
    }


    //get task list
    function get_task_list()
    {
        $user_id = Auth::user()->id;
        $data = DB::Select("CALL sp_get_task_list_v3($user_id);");
        return response()->json($data);
    }

    public function get_task_list_with_date(Request $request)
    {
        $user_id = Auth::user()->id;
        $status = $request->input('status', null); 
    
      
        $tasks = DB::select("CALL sp_get_task_list_v3($user_id);");
    
        if ($status !== null) {
            $tasks = array_filter($tasks, function ($task) use ($status) {
                return strcasecmp($task->status, $status) === 0; 
            });
        }
    
        
        $startDate = \Carbon\Carbon::now()->startOfYear(); 
        
        $endOfYear = \Carbon\Carbon::now()->endOfYear();

        $db_taskdetail = DB::table('tasks')->orderBy('date', 'desc')->limit(1)->first();


        $lastTaskDate = $db_taskdetail ? \Carbon\Carbon::parse($db_taskdetail->date) : null;


        $endDate = $lastTaskDate && $lastTaskDate->gt($endOfYear) 
        ? $lastTaskDate 
        : $endOfYear; 
        
    

        $allDates = [];
        while ($startDate->lte($endDate)) {
            $allDates[] = $startDate->copy()->format('d M Y'); 
            $startDate->addDay(); 
        }
    
       
        $groupedTasks = collect($tasks)->groupBy(function ($task) {
            return \Carbon\Carbon::parse($task->date)->format('d M Y');
        });
    
        
        $formattedData = collect($allDates)->map(function ($date) use ($groupedTasks) {
            return [
                'date' => $date,
                'data' => $groupedTasks->get($date, []), 
            ];
        });
    
        return response()->json($formattedData, 200, [], JSON_PRETTY_PRINT);
    }

    public function get_task_list_date_filter($date)
    {
        try {

        $user_id = Auth::user()->id;

        
        $tasks = DB::select("CALL sp_get_task_list_w_date_v2($user_id,$date);");

        
        $formattedData = collect($tasks)->groupBy(function ($item) {
        return \Carbon\Carbon::parse($item->date)->format('d M Y');
           })->map(function ($tasks, $date) {
             return [
            'date' => $date,
            'data' => $tasks,
           ];
           })->values();

       
           return response()->json($formattedData, 200, [], JSON_PRETTY_PRINT);
        }
        catch (\Exception $e) {
        
            return response()->json([]);
        }
    }



    function get_task_trigger_list()
    {
        $user_id = Auth::user()->id;
        $data = DB::Select("CALL sp_get_task_trigger_list_v1($user_id);");
        return response()->json($data);
    }


    public function get_task_trigger_list_with_date()
    {
        $user_id = Auth::user()->id;

        
        $tasks = DB::select("CALL sp_get_task_trigger_list_v1($user_id);");

        // Format the tasks
        $formattedData = collect($tasks)->groupBy(function ($item) {
        return \Carbon\Carbon::parse($item->created_at)->format('d M');
        })->map(function ($tasks, $date) {
        return [
            'date' => $date,
            'data' => $tasks,
        ];
         })->values();

        
        return response()->json($formattedData, 200, [], JSON_PRETTY_PRINT);
    }


    public function get_task_trigger_list_date_filter($date)
    {
        try {

        $user_id = Auth::user()->id;

        
        $tasks = DB::select("CALL sp_get_task_trigger_list_w_date_v1($user_id,$date);");

        // Format the tasks
        $formattedData = collect($tasks)->groupBy(function ($item) {
        return \Carbon\Carbon::parse($item->created_at)->format('d M');
        })->map(function ($tasks, $date) {
        return [
            'date' => $date,
            'data' => $tasks,
        ];
         })->values();

        
          return response()->json($formattedData, 200, [], JSON_PRETTY_PRINT);
        
        }
        catch (\Exception $e) {
        
            return response()->json([]);
        }
    }

    

    function get_trigger_detail($triggerid)
    {
        //dd($taskid);
        $data = DB::Select("CALL sp_get_trigger_detail($triggerid);");
        $taskdetail = $data[0] ?? null; 
        return response()->json($taskdetail);
    }


    function get_task_detail($taskid)
    {
        //dd($taskid);
        $data = DB::Select("CALL sp_get_task_detail_v2($taskid);");
        $taskdetail = $data[0] ?? null; 
        return response()->json($taskdetail);
    }

    //getservices
    function getservices()
    {
        
        $user_id = Auth::user()->id;
        // printf($user_id);
        $data = DB::Select("CALL get_all_services();");
        return response()->json($data);

    }

    // get vendor by selected service category
    function getvendorbyServiceId($serviceid)
    {
        
        try {
        
        if (empty($serviceid) || !is_numeric($serviceid)) {
            
            return response()->json([]);
        }

      
        $data = DB::select("CALL sp_get_vendors(?)", [$serviceid]);
        return response()->json($data);
        
    } catch (\Exception $e) {
        
        return response()->json([]);
    }
        
        
    }
    
    
    public function deleteinvoice(Request $request)
    {
        $taskid = $request->input('taskid');

        
        $task = Tasks::find($taskid);

        
        if (!$task) {
        return response()->json(['message' => 'Task not found.'], 404);
        }

      
        if ($task->invoice_url && Storage::exists(str_replace('/storage', '', $task->invoice_url))) {
        Storage::delete(str_replace('/storage', '', $task->invoice_url));
        }
    
        
        $task->invoice_url = null;
       
        $isSaved = $task->save();

        
        $taskdata = DB::select("CALL sp_get_task_detail_v2(?)", [$taskid]);

        return response()->json([
        'message' => 'Invoice deleted successfully.',
        'data' => $taskdata,
        ], 200);
    }
        
    
    // get listing
    function getpropertybyUserId()
    {
        
        $user_id = Auth::user()->id;
        // printf($user_id);
        $data = DB::Select("CALL sp_get_property_by_user_id($user_id);");
        return response()->json($data);

    }


    public function sendMessage($to, $message, $type)
    {

        $to = $this->formatPhoneNumber($to);
        
        $from = $type === 'whatsapp'
            ? env('TWILIO_WHATSAPP_FROM') 
            : env('TWILIO_PHONE_NUMBER'); 
       
        
        if ($type === 'whatsapp') {
            $to = "whatsapp:$to";
        }

       
        try {
            
            return $this->client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => $message,
                ]
            );
        } catch (\Exception $e) {
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    private function formatPhoneNumber($to)
    {
        
        $to = preg_replace('/[\s()-]+/', '', $to);
    
        
        if (substr($to, 0, 1) === '0') {
            $to = substr($to, 1);
        }
    
        
        $to = '+' . $to;
    
        return $to;
    }
   
    public function insert_update_task(Request $request)
    {
        logger("taskpayload: " . json_encode($request->all())); 
        $user_id = Auth::user()->id;
        
          
        $validatedData = $request->validate([
            'listing_id' => 'required|integer', 
            'service_id' => 'required|integer', 
          
        ]);
        
        
        
        try {
        $tasktype = $request->input('tasktype');
        
        $vendorid = $request->input('vendor_id', 0);
        
        $listingid = $validatedData['listing_id'];
        $userDB = User::find($user_id); 
        
        
        if($tasktype == "Manual")
        {
            
            $dateInput = $request->input('date'); 
            $timeInput = $request->input('time'); 
            
            $date = Carbon::parse($dateInput)->setTimezone('UTC')->format('Y-m-d'); 
            
            
            
            if(isset($request->id) && $request->id)
            {
                $time = Carbon::parse($timeInput)->addHour(5)->format('H:i:s');
                $dbtask = Tasks::find($request->id);
            }
            else
            {
                $time = Carbon::parse($timeInput)->setTimezone('UTC')->format('H:i:s'); 
            }
            
            
            
            

            $data = [
                'p_id' => $request->input('id'), 
                'p_title' => $request->input('title'),
                'p_description' => $request->input('description'),
                'p_service_id' => $validatedData['service_id'],
                'p_date' => $date,
                'p_time' => $time,
                'p_vendor_id' => $request->input('vendor_id'),
                'p_listing_id' => $validatedData['listing_id'],
                'p_user_id' => $user_id,
               
               
            ];
            
            
        
            try {
                $result = $this->storeProcedureService
                    ->name('sp_insert_update_task') 
                    ->InParameters([
                        'p_id', 'p_title', 'p_description', 'p_service_id', 'p_date', 'p_time',
                        'p_vendor_id', 'p_listing_id', 'p_user_id'
                        
                    ])
                    ->OutParameters(['return_value', 'return_message'])
                    ->data($data) 
                    ->execute();
        
                $response = $this->storeProcedureService->response();
        
                
    
                if ($response['response']['return_value'] == 1) {
                    
                   
                    if($response['response']['return_message'] == "Task has been successfully updated.")
                    {
                        $task_id = $data['p_id'];
                        $taskdata = DB::Select("CALL sp_get_task_detail_v2($task_id);");
                        $taskdetail = $taskdata[0] ?? null; 
                        
                        if($dbtask->vendor_id != $vendorid)
                        {
                            $vendor = Vendors::find($vendorid);

                            if($vendor != null)
                            {
                                if (!empty($vendor->phone) && $vendor->phone != '0') {
                            
                                $vendorPhone = $vendor->country_code . $vendor->phone;
                                $this->sendMessage($vendorPhone, "New Task Created", "sms");
                                  
                                } else {
                           
                                logger("Message not sent to vendor: Invalid phone number for vendor ID {$vendor->id}"); 
                                 }
                            }
                        }
                        
                        
                        return response()->json([
                            'data' => $taskdetail
                        ], 200);
                    }
                    else
                    {
                        
                       $vendor = Vendors::find($vendorid);

                        
                    if($vendor != null)
                    {
                        if (!empty($vendor->phone) && $vendor->phone != '0') {
                        
                        $vendorPhone = $vendor->country_code . $vendor->phone;
                        $this->sendMessage($vendorPhone, "New Task Created", "sms");

                        } else {
                              logger("Message not sent to vendor: Invalid phone number for vendor ID {$vendor->id}"); 
                        }
                    }
                    
                    
                    
                    if (!empty($userDB->phone) && $userDB->phone != '0') {
                            
                            
                            $this->sendMessage($userDB->phone, "New Task Created", "whatsapp");

                    } 
                    else {
                      
                        logger("Message not sent to user: Invalid phone number for user ID {$vendor->id}"); 
                    }
      
                        return response()->json([
                            'message' => $response['response']['return_message'],
                       ], 200);
    
                    }
                    
                    
    
    
    
    
                } 
                else {
                    return response()->json([
                        'message' => $response['response']['return_message'],
                    ], 400);
                }
    
    
    
    
            } catch (\Exception $e) {
                return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

        }

        else if($tasktype == "Automated")
        {
            
            $data = [
                'p_id' => $request->input('id', 0),
                'p_title' => $request->input('title'),
                'p_description' => $request->input('description'),
                'p_service_id' => $validatedData['service_id'],
             
                'p_vendor_id' => $request->input('vendor_id'),
                'p_listing_id' => $validatedData['listing_id'],
                'p_trigger_type' => $request->input('trigger_type'),
                'p_status' => $request->input('status'),
                'p_user_id' => $user_id,
            ];
        
            try {
                $result = $this->storeProcedureService
                    ->name('sp_insert_update_tasks_trigger')
                    ->InParameters([
                        'p_id', 'p_title', 'p_description', 'p_service_id',
                         'p_vendor_id', 'p_listing_id',
                        'p_trigger_type', 'p_status', 'p_user_id'
                    ])
                    ->OutParameters(['return_value', 'return_message'])
                    ->data($data)
                    ->execute();
        
                $response = $this->storeProcedureService->response();
        
                if ($response['response']['return_value'] == 1) {
                    
                   
                    if($response['response']['return_message'] == "Record successfully updated.")
                    {
                        $trigger_id = $data['p_id'];
                        $taskdata = DB::Select("CALL sp_get_trigger_detail($trigger_id);");
                        $taskdetail = $taskdata[0] ?? null; 
                        
                        return response()->json([
                            'data' => $taskdetail
                        ], 200);
                    }
                    else
                    {
                       return response()->json([
                            'message' => "Trigger has been successfully inserted.",
                       ], 200);
    
                    }
    
                } else {
                    return response()->json([
                        'message' => $response['response']['return_message'],
                    ], 400);
                }
        
            } catch (\Exception $e) {
                return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
            }

        }
       
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
            'message' => 'Validation failed.',
            'errors' => $e->errors(),
            ], 422);
        }
       
        
    }

    public function insertUpdateTasksTrigger(Request $request)
    {
        $user_id = Auth::user()->id;
    
        $data = [
            'p_id' => $request->input('id', 0),
            'p_title' => $request->input('title'),
            'p_description' => $request->input('description'),
            'p_service_id' => $request->input('service_id'),
         
            'p_vendor_id' => $request->input('vendor_id'),
            'p_listing_id' => $request->input('listing_id'),
            'p_trigger_type' => $request->input('trigger_type'),
            'p_status' => $request->input('status'),
            'p_user_id' => $user_id,
        ];
    
        try {
            $result = $this->storeProcedureService
                ->name('sp_insert_update_tasks_trigger')
                ->InParameters([
                    'p_id', 'p_title', 'p_description', 'p_service_id',
                     'p_vendor_id', 'p_listing_id',
                    'p_trigger_type', 'p_status', 'p_user_id'
                ])
                ->OutParameters(['return_value', 'return_message'])
                ->data($data)
                ->execute();
    
            $response = $this->storeProcedureService->response();
    
            if ($response['response']['return_value'] == 1) {
                
               
                if($response['response']['return_message'] == "Record successfully updated.")
                {
                    $trigger_id = $data['p_id'];
                    $taskdata = DB::Select("CALL sp_get_trigger_detail($trigger_id);");
                    $taskdetail = $taskdata[0] ?? null; 
                    
                    return response()->json([
                        'data' => $taskdetail
                    ], 200);
                }
                else
                {
                   return response()->json([
                        'message' => $response['response']['return_message'],
                   ], 200);

                }

            } else {
                return response()->json([
                    'message' => $response['response']['return_message'],
                ], 400);
            }
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function updateTaskStatus(Request $request)
    {
        $user_id = Auth::id();
    
       
        $validatedData = $request->validate([
            'id' => 'required|integer', 
            'status' => 'required|string', 
            
        ]);
    
     
        $task_id = $validatedData['id'];
        $status = $validatedData['status'];
        $invoiceFile = $request->file('invoice');
    
       
        $invoiceUrl = null;
    
       
        if ($invoiceFile) {
            $fileName = time() . '_' . $invoiceFile->getClientOriginalName();
            $filePath = $invoiceFile->storeAs('invoices', $fileName, 'public'); 
            $invoiceUrl = asset('storage/' . $filePath);
        }
    
        
        $data = [
            'p_id' => $task_id,
            'p_status' => $status,
            'p_invoice_url' => $invoiceUrl,
        ];
    
        try {
           
            $result = $this->storeProcedureService
                ->name('sp_update_task_status_v3') 
                ->InParameters(['p_id', 'p_status', 'p_invoice_url'])
                ->OutParameters(['return_value', 'return_message'])
                ->data($data)
                ->execute();
    
            $response = $this->storeProcedureService->response();
            
        
            
            if ($response['response']['return_value'] == 1) {
                
                $taskdata = DB::Select("CALL sp_get_task_detail_v2($task_id);");
                $taskdetail = $taskdata[0] ?? null; 
                
                return response()->json([
                    // 'message' => $response['response']['return_message'],
                    // 'invoice_url' => $invoiceUrl,
                    'data' => $taskdetail
                ], 200);


            } else {
                return response()->json([
                    'message' => $response['response']['return_message'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function task_Delete(Request $request)
    {
        $user_id = Auth::id();
        
        
        $validatedData = $request->validate([
            'id' => 'required|integer', 
        ]);
        
        
        $task_id = $validatedData['id'];
    
        $data = [
            'p_id' => $task_id,
        ];
    
        try {
          
            $result = $this->storeProcedureService
                ->name('sp_task_delete') 
                ->InParameters(['p_id'])
                ->OutParameters(['return_value', 'return_message'])
                ->data($data)
                ->execute();
    
           
            $response = $this->storeProcedureService->response();
            
            if ($response['response']['return_value'] == 1) {
                return response()->json([
                    'message' => $response['response']['return_message'],
                ], 200);
            } else {
                return response()->json([
                    'message' => $response['response']['return_message'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function trigger_Delete(Request $request)
    {
        $user_id = Auth::id();
        
        
        $validatedData = $request->validate([
            'id' => 'required|integer', 
        ]);
        
        
        $task_id = $validatedData['id'];
    
        $data = [
            'p_id' => $task_id,
        ];
    
        try {
          
            $result = $this->storeProcedureService
                ->name('sp_trigger_delete') 
                ->InParameters(['p_id'])
                ->OutParameters(['return_value', 'return_message'])
                ->data($data)
                ->execute();
    
           
            $response = $this->storeProcedureService->response();
            
            if ($response['response']['return_value'] == 1) {
                return response()->json([
                    'message' => $response['response']['return_message'],
                ], 200);
            } else {
                return response()->json([
                    'message' => $response['response']['return_message'],
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
    

}
 
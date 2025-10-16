<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Resources\TasksResource;
use App\Models\Tasks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

class TasksController extends Controller
{
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
}
 
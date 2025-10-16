<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskReviewResource;
use App\Models\TaskReviews;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

class TaskReviewController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return TaskReviewResource::collection(TaskReviews::all());
    }


    /**
     * @param Request $request
     * @return TaskReviewResource|JsonResponse
     */
    public function store(Request $request): JsonResponse|TaskReviewResource
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required',
            'rating' => 'required',
            'review' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        return new TaskReviewResource(TaskReviews::create($request->all()));
    }

    /**
     * @param TaskReviews $task_review
     * @return TaskReviewResource
     */
    public function show(TaskReviews $task_review): TaskReviewResource
    {
        return new TaskReviewResource($task_review);
    }

    public function getTaskReviewByTaskStatus($status)
    {

    }


    /**
     * @param Request $request
     * @param TaskReviews $task_review
     * @return JsonResponse|TaskReviewResource
     */
    public function update(Request $request, TaskReviews $task_review): JsonResponse|TaskReviewResource
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required',
            'review' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $task_review->update($request->all());
        return new TaskReviewResource($task_review);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskReviews $taskReviews)
    {
        //
    }
}
 
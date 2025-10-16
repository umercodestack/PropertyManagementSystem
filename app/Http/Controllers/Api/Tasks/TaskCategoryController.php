<?php

namespace App\Http\Controllers\Api\Tasks;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskCategoriesResource;
use App\Models\TaskCategories;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;

class TaskCategoryController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return TaskCategoriesResource::collection(TaskCategories::all());
    }

    /**
     * @param Request $request
     * @return JsonResponseAlias|TaskCategoriesResource
     */
    public function store(Request $request): JsonResponseAlias|TaskCategoriesResource
    {
        $validator = Validator::make($request->all(), [
            'category_title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        return new TaskCategoriesResource(TaskCategories::create($request->all()));
    }

    /**
     * @param TaskCategories $task_category
     * @return TaskCategoriesResource
     */
    public function show(TaskCategories $task_category): TaskCategoriesResource
    {
        return new TaskCategoriesResource($task_category);
    }

    /**
     * @param Request $request
     * @param TaskCategories $task_category
     * @return JsonResponseAlias|TaskCategoriesResource
     */
    public function update(Request $request, TaskCategories $task_category): JsonResponseAlias|TaskCategoriesResource
    {
        $validator = Validator::make($request->all(), [
            'category_title' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $task_category->update($request->all());
        return new TaskCategoriesResource($task_category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskCategories $task_category)
    {
        //
    }
}
 
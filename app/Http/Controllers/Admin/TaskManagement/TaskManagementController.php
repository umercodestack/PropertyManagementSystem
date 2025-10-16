<?php

namespace App\Http\Controllers\Admin\TaskManagement;

use App\Http\Controllers\Controller;
use App\Models\Apartments;
use App\Models\TaskCategories;
use App\Models\Tasks;
use App\Models\Vendors;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TaskManagementController extends Controller
{
    /**
     * @return View
     */
    
    public function __construct()
    {
        $this->middleware('permission');
    } 
     
    public function index(): View
    {
        $tasks = Tasks::with('category', 'vendor', 'apartment')->get();
        return view('Admin.task-management.index', ['tasks' => $tasks]);
    }
    /**
     * @return View
     */
    public function create(): View
    {
        $category = TaskCategories::all();
        $vendor = Vendors::all();
        $apartment = Apartments::all();
        return view('Admin.task-management.create', ['category' => $category, 'vendor' => $vendor, 'apartment' => $apartment,]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'task_title' => 'required',
            'category_id' => 'required',
            'vendor_id' => 'required',
            'apartment_id' => 'required',
            'frequency' => 'required',
            'time_duration' => 'required',
            'date_duration' => 'required',
            'completion_time' => 'required',
        ]);
        $data = $request->all();
        $data['stage'] = 'initiate';
        $data['status'] = 'Inprocess';
        $data['picture'] = 'task.png';
        Tasks::create($data);
        return redirect()->route('task-management.index')->with('success', 'Task Created Successfully');
    }

    /**
     * @param Tasks $task_management
     * @return View
     */
    public function edit(Tasks $task_management): View
    {
        $category = TaskCategories::all();
        $vendor = Vendors::all();
        $apartment = Apartments::all();
        return view('Admin.task-management.edit', ['category' => $category, 'vendor' => $vendor, 'apartment' => $apartment,'task' => $task_management]);
    }

    public function update(Request $request, Tasks $task_management): RedirectResponse
    {
        $request->validate([
            'task_title' => 'required',
            'category_id' => 'required',
            'vendor_id' => 'required',
            'apartment_id' => 'required',
            'frequency' => 'required',
            'time_duration' => 'required',
            'date_duration' => 'required',
            'completion_time' => 'required',
        ]);
        $data = $request->all();
        $task_management->update($data);
        return redirect()->route('task-management.index')->with('success', 'Task Updated Successfully');
    }

}
 
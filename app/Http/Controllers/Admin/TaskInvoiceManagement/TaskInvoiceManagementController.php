<?php

namespace App\Http\Controllers\Admin\TaskInvoiceManagement;

use App\Http\Controllers\Controller;
use App\Models\TaskInvoices;
use App\Models\Tasks;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class TaskInvoiceManagementController extends Controller
{
    /**
     * @return View
     */
    
    public function __construct()
    {
        $this->middleware('permission');
    } 
     
    public function index():view
    {
        $taskInvoices = TaskInvoices::with('task', 'user')->get();
        return view('Admin.task-invoice-management.index', ['taskInvoices' => $taskInvoices]);
    }

    /**
     * @return View
     */
    public function create():view
    {
        $task = Tasks::all();
        $users = User::all();
        return view('Admin.task-invoice-management.create', ['task' => $task, 'users' => $users]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'task_id' => 'required',
            'user_id' => 'required',
            'amount' => 'required',
            'currency' => 'required',
            'description' => 'required',
        ]);
        $data = $request->all();
        TaskInvoices::create($data);
        return redirect()->route('task-invoice-management.index')->with('success', 'Task Invoice Successfully');
    }

    /**
     * @param TaskInvoices $task_invoice_management
     * @return View
     */
    public function edit(TaskInvoices $task_invoice_management): View
    {
        $task = Tasks::all();
        $users = User::all();
        return view('Admin.task-invoice-management.edit', ['task' => $task, 'users' => $users, 'task_invoices' => $task_invoice_management]);
    }

    /**
     * @param Request $request
     * @param TaskInvoices $task_invoice_management
     * @return RedirectResponse
     */
    public function update(Request $request, TaskInvoices $task_invoice_management): RedirectResponse
    {
        $request->validate([
            'task_id' => 'required',
            'user_id' => 'required',
            'amount' => 'required',
            'currency' => 'required',
            'description' => 'required',
        ]);
        $data = $request->all();
        $task_invoice_management->update($data);
        return redirect()->route('task-invoice-management.index')->with('success', 'Task Invoice Updated Successfully');
    }

    /**
     * @param TaskInvoices $task_invoice_management
     * @return View
     */
    public function printInvoice(TaskInvoices $task_invoice_management): View
    {
        $user = User::findOrFail($task_invoice_management->user_id);
        return view('Admin.task-invoice-management.invoice', ['taskInvoice' => $task_invoice_management, 'user'=>$user]);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskInvoices $taskInvoices)
    {
        //
    }
}
 
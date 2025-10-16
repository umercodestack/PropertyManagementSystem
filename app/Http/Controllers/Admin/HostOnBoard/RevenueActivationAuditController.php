<?php

namespace App\Http\Controllers\Admin\HostOnBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RevenueActivationAudit;
use App\Models\Hostaboard;
use App\Models\Audit;
use App\Models\AuditListing;
use App\Models\SalesActivationAudit;
use App\Models\AuditBackendOp;
use Illuminate\Support\Facades\Auth;

class RevenueActivationAuditController extends Controller
{
    public function index()
    {
        $audits = RevenueActivationAudit::with(['hostaboard', 'updatedBy'])->latest()->get();
        return view('Admin.revenue-activation-audit.index', compact('audits'));
    }

    public function create()
    {
        $hostaboards = Hostaboard::all();
        return view('Admin.revenue-activation-audit.create', compact('hostaboards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hostaboard_id' => 'required|integer',
            'status' => 'required|in:Approved,Declined,Pending',
            'remarks' => 'nullable|string',
            'task_status' => 'nullable|in:Generated,In progress,No Required',
            'task_remarks' => 'nullable|string',
        ]);

        RevenueActivationAudit::create([
            'hostaboard_id' => $request->hostaboard_id,
            'status' => $request->status,
            'remarks' => $request->remarks,
            'task_status' => $request->task_status,
            'task_remarks' => $request->task_remarks,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('revenue-activation-audit.index')->with('success', 'Audit created successfully.');
    }

    public function edit($id)
    {
        $audit = RevenueActivationAudit::findOrFail($id);
        $hostaboards = Hostaboard::all();
        return view('Admin.revenue-activation-audit.edit', compact('audit', 'hostaboards'));
    }



public function update(Request $request, $id)
{
    $request->validate([
        'status' => 'required|string',
        'remarks' => 'nullable|string',
        'task_status' => 'nullable|string',
        'task_remarks' => 'nullable|string',
    ]);

    try {
        $audit = RevenueActivationAudit::findOrFail($id);
        $warnings = [];

        if (strtolower($request->status) === 'approved') {
            $audittask = Audit::where('host_activation_id', $audit->hostaboard_id)->first();
            $hostaboard = Hostaboard::find($audit->hostaboard_id);
           

            if (!$audittask) {
                $warnings[] = 'Audit Task is missing for this Revenue Audit.';
            } else {
                $maintenance = SalesActivationAudit::where('hostaboard_id', $audittask->host_activation_id)
                    ->where('audit_id', $audittask->id)
                    ->where('task_type', 'Maintenance')
                    ->first();

                $inventory = SalesActivationAudit::where('hostaboard_id', $audittask->host_activation_id)
                    ->where('audit_id', $audittask->id)
                    ->where('task_type', 'Inventory')
                    ->first();

                $allClear = true;    

                if (!$maintenance) {
                    $warnings[] = 'Maintenance Required task is missing.';
                    $allClear = false;
                } elseif ($maintenance->is_required == 1 && strtolower($maintenance->status) !== 'approved') {
                    $warnings[] = 'Maintenance Required exists but is not approved.';
                    $allClear = false;
                }

                if (!$inventory) {
                    $warnings[] = 'Inventory Required task is missing.';
                    $allClear = false;
                } elseif ($inventory->is_required == 1 && strtolower($inventory->status) !== 'approved') {
                    $warnings[] = 'Inventory Required exists but is not approved.';
                    $allClear = false;
                }

                

                // ✅ Listing create karega approve main chahe warning ho
                if ($allClear) {
                    
                    if($hostaboard->co_hosting_account==0)
                    {
                        AuditListing::firstOrCreate([
                            'hostaboard_id' => $audittask->host_activation_id,
                            'audit_id'      => $audittask->id,
                        ]);
                    }
                    elseif($hostaboard->co_hosting_account==1)
                    {
                        AuditBackendOp::updateOrCreate(
                          [
                            'hostaboard_id' => $audittask->host_activation_id,
                            'audit_id' => $audittask->id,
                          ],
                          [
                              'hostaboard_id' => $audittask->host_activation_id,
                              'audit_id' => $audittask->id,
                              'updated_by' => Auth::id()
                          ]);
                    }
                    

                    
                }
            }
        }
        


        // $taskStatus = strtolower($request->status) === 'declined' ? 'generated' : $request->task_status;
        // $taskStatus = strtolower($request->status) === 'pending' ? 'required' : $request->task_status;
        //$taskStatus = strtolower($request->status) === 'mark as done' ? 'mark as done' : 'completed';

$updateData = [
    'status'     => $request->status,
    'remarks'    => $request->remarks,
    'updated_by' => Auth::id(),
];

// Only update task_status if status is declined or pending
if (in_array($request->status, ['declined', 'pending'])) {
    $updateData['task_status'] = 'required';
}

$audit->update($updateData);


        return redirect()
            ->route('revenue-activation-audit.index')
            ->with('success', 'Revenue Activation Task updated successfully.')
            ->with('warnings', $warnings);

    } catch (ModelNotFoundException $e) {
        return redirect()
            ->back()
            ->with('error', 'Record not found: ' . class_basename($e->getModel()));
    } catch (\Exception $e) {
        return redirect()
            ->back()
            ->with('error', 'Something went wrong: ' . $e->getMessage());
    }
}







    public function destroy($id)
    {
        RevenueActivationAudit::findOrFail($id)->delete();
        return redirect()->route('revenue-activation-audit.index')->with('success', 'Audit deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin\HostOnBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SalesActivationAudit;
use App\Models\Hostaboard;
use App\Models\RevenueActivationAudit;
use App\Models\Audit;
use App\Models\AuditListing;
use App\Models\AuditBackendOp;
use Illuminate\Support\Facades\Auth;

class SalesActivationAuditController extends Controller
{
    public function index()
    {
        $audits = SalesActivationAudit::with(['hostaboard', 'updatedBy'])->latest()->get();
        return view('Admin.sales-activation-audit.index', compact('audits'));
    }

    public function create()
    {
        $hostaboards = Hostaboard::all();
        return view('Admin.sales-activation-audit.create', compact('hostaboards'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hostaboard_id' => 'required|integer',
            'audit_id' => 'required|integer',
            'status' => 'required|in:Approved,Declined,Pending',
            'remarks' => 'nullable|string',
            'is_maintenance_required' => 'nullable|boolean',
            'minor_major' => 'nullable|boolean',
            'amount' => 'nullable|numeric',
            'task_type' => 'nullable|string',
            'task_remarks' => 'nullable|string',
        ]);

        SalesActivationAudit::create([
            'hostaboard_id' => $request->hostaboard_id,
            'audit_id' => $request->audit_id,
            'status' => $request->status,
            'remarks' => $request->remarks,
            'is_maintenance_required' => $request->is_maintenance_required,
            'minor_major' => $request->minor_major,
            'amount' => $request->amount,
            'task_type' => $request->task_type,
            'task_remarks' => $request->task_remarks,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('sales-activation-audit.index')->with('success', 'Audit created successfully.');
    }

    public function edit($id)
    {
        $audit = SalesActivationAudit::findOrFail($id);
        $hostaboards = Hostaboard::all();
        return view('Admin.sales-activation-audit.edit', compact('audit', 'hostaboards'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
            'remarks' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'minor_major' => 'nullable|boolean',
            'amount' => 'nullable|numeric',
            'task_type' => 'nullable|string',
            'task_remarks' => 'nullable|string',
        ]);

        $audit = SalesActivationAudit::findOrFail($id);

        $audit->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
            'updated_by' => Auth::id(),
        ]);

        if (strtolower($request->status) === 'approved')
        {
            $warnings = [];
            $audittask = Audit::where('host_activation_id', $audit->hostaboard_id)->first();
            $hostaboard = Hostaboard::find($audit->hostaboard_id);

            if (!$audittask) {
                $warnings[] = 'Audit Task is missing for this Revenue Audit.';
            } else {

                $photoRecord = RevenueActivationAudit::where('hostaboard_id', $audittask->host_activation_id)->first();
                    

                $maintenance = SalesActivationAudit::where('hostaboard_id', $audittask->host_activation_id)
                    ->where('audit_id', $audittask->id)
                    ->where('task_type', 'Maintenance')
                    ->first();

                $inventory = SalesActivationAudit::where('hostaboard_id', $audittask->host_activation_id)
                    ->where('audit_id', $audittask->id)
                    ->where('task_type', 'Inventory')
                    ->first();

                $allClear = true;
                
                if (!$photoRecord) {
                    $warnings[] = 'Revenue Photos are missing.';
                    $allClear = false;
                } elseif (strtolower($photoRecord->status) !== 'approved') {
                    $warnings[] = 'Revenue Photos exist but are not approved.';
                    $allClear = false;
                }

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

         return redirect()
            ->route('sales-activation-audit.index')
            ->with('success', 'updated successfully.')
            ->with('warnings', $warnings);
       
    }

    public function destroy($id)
    {
        SalesActivationAudit::findOrFail($id)->delete();
        return redirect()->route('sales-activation-audit.index')->with('success', 'Audit deleted successfully.');
    }
}

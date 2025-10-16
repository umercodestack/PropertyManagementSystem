<?php

namespace App\Http\Controllers\Admin\HostOnBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditBackendOp;
use App\Models\AuditListing;

class AuditBackendOpController extends Controller
{
    // Show all records
    public function index()
    {
        $ops = AuditBackendOp::orderBy('created_at', 'desc')->get();
        return view('Admin.backend-ops.index', compact('ops'));
    }

    // Show create form
    public function create()
    {
        return view('Admin.backend-ops.create');
    }

    // Store new record
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hostaboard_id' => 'required|integer',
            'audit_id' => 'required|integer',
            'status' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
            'updated_by' => 'nullable|integer',
        ]);

        AuditBackendOp::create($validated);

        return redirect()->route('audit-backend-ops.index')->with('success', 'Record created successfully.');
    }

    // Show edit form
    public function edit($id)
    {
        $op = AuditBackendOp::with(['hostaboard'])->findOrFail($id);
        //dd($op);
        return view('Admin.backend-ops.edit', compact('op'));
    }

    // Update record
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
           
            'status' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
            'updated_by' => 'nullable|integer',
        ]);

        $auditBackendOp = AuditBackendOp::findOrFail($id);
        $auditBackendOp->update($validated);


        if (strtolower($request->status) === 'approved') {

            AuditListing::firstOrCreate([
                    'hostaboard_id' => $auditBackendOp->hostaboard_id,
                    'audit_id'      => $auditBackendOp->audit_id,
                ]);
        }

        return redirect()->route('backend-ops.index')->with('success', 'Record updated successfully.');
    }

    // Delete record
    public function destroy($id)
    {
        $auditBackendOp = AuditBackendOp::findOrFail($id);
        $auditBackendOp->delete();

        return redirect()->route('audit-backend-ops.index')->with('success', 'Record deleted successfully.');
    }
}

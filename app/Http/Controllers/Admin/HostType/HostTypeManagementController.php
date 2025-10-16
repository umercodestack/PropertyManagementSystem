<?php

namespace App\Http\Controllers\Admin\HostType;

use App\Http\Controllers\Controller;
use App\Models\HostChargeType;
use App\Models\HostType;
use Illuminate\Http\Request;

class HostTypeManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     
     
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    
    public function index()
    {
        $hostTypes = HostType::all();
        return view('Admin.host-type-management.index', ['hostTypes' => $hostTypes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
//        'Saas Module', 'Saas & Services', 'LOKAL Module'
        return view('Admin.host-type-management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'module_name' => 'required',
            'amount_type' => 'required',
            'amount' => 'required',
        ]);
        $hostType = HostType::create($request->all());
        foreach ($request->charge_type as $item) {
            $chargeType = array(
                'host_type_id' => $hostType->id,
                'charge_type' => $item
            );
            HostChargeType::create($chargeType);
        }
        return redirect()->route('host-type-management.index')->with('success', 'Host Type Created Successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HostType $host_type_management)
    {
        $chargeTypes = HostChargeType::where('host_type_id', $host_type_management->id)->get();
        return view('Admin.host-type-management.edit', ['hostType' => $host_type_management, 'chargeTypes' =>$chargeTypes]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HostType $host_type_management)
    {
        $request->validate([
            'module_name' => 'required',
            'amount_type' => 'required',
            'amount' => 'required',
        ]);
        $host_type_management->update($request->all());
        $chargeTypes = HostChargeType::where('host_type_id', $host_type_management->id);
        $chargeTypes->delete();
        foreach ($request->charge_type as $item) {
            $data = array(
                'host_type_id' => $host_type_management->id,
                'charge_type' => $item
            );
            HostChargeType::create($data);
        }
        return redirect()->route('host-type-management.index')->with('success', 'Host Type Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HostType $hostType)
    {
        //
    }
}
 
<?php

namespace App\Http\Controllers\Admin\VendorManagement;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\Vendors;
use App\Models\VendorServices;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class VendorManagementController extends Controller
{
    
    
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    public function index(): View
    {
        $vendors = Vendors::all();
        return view('Admin.vendor-management.index', ['vendors' => $vendors]);
    }
    public function Create()
    {
        $services = Services::all();
        return view('Admin.vendor-management.create', ['services'=>$services]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'occupation' => 'required',
            'availability' => 'required',
            'last_hired' => 'required',
            'time_duration' => 'required',
            'service_id' => 'required',
            'amount' => 'required',
            'currency' => 'required',
        ]);
        $vendor_data['name'] = $request->name;
        $vendor_data['location'] = $request->location;
        $vendor_data['occupation'] = $request->occupation;
        $vendor_data['availability'] = $request->availability;
        $vendor_data['last_hired'] = $request->last_hired;
        $vendor_data['time_duration'] = $request->time_duration;
        $vendor_data['picture'] = 'vendor.png';
        $vendor_data['is_active'] = 1;
        $vendor = Vendors::create($vendor_data);
        $vendor_services_data['vendor_id'] = $vendor->id;
        $vendor_services_data['service_id'] = $request->service_id;
        $vendor_services_data['amount'] = $request->amount;
        $vendor_services_data['currency'] = $request->currency;
        VendorServices::create($vendor_services_data);
        return redirect()->route('vendor-management.index')->with('success', 'Vendor Created Successfully');
    }
    public function edit(Vendors $vendor_management) {
        $vendor = Vendors::where('id', $vendor_management->id)->with('vendorServices')->first();
        $services = Services::all();
        return view('Admin.vendor-management.edit', ['services'=>$services, 'vendor' => $vendor]);
    }
    public function update(Request $request, Vendors $vendor_management)
    {
//        dd($request);
        $request->validate([
            'name' => 'required',
            'location' => 'required',
            'occupation' => 'required',
            'availability' => 'required',
            'last_hired' => 'required',
            'time_duration' => 'required',
            'service_id' => 'required',
            'amount' => 'required',
            'currency' => 'required',
        ]);
        $vendor_data['name'] = $request->name;
        $vendor_data['location'] = $request->location;
        $vendor_data['occupation'] = $request->occupation;
        $vendor_data['availability'] = $request->availability;
        $vendor_data['last_hired'] = $request->last_hired;
        $vendor_data['time_duration'] = $request->time_duration;
        $vendor_data['picture'] = 'vendor.png';
        $vendor_data['is_active'] = 1;
        $vendor_management->update($vendor_data);

        $vendor_service_id = VendorServices::findOrfail($request->vendor_service_id)->first();
        $vendor_services_data['service_id'] = $request->service_id;
        $vendor_services_data['amount'] = $request->amount;
        $vendor_services_data['currency'] = $request->currency;
        $vendor_service_id->update($vendor_services_data);
        return redirect()->route('vendor-management.index')->with('success', 'Vendor Created Successfully');
    }

    /**
     * @param Vendors $vendor
     * @return VendorResource
     */
    public function destroy(Vendors $vendor): VendorResource
    {
        $vendor->delete();
        return new VendorResource($vendor);
    }
}
 
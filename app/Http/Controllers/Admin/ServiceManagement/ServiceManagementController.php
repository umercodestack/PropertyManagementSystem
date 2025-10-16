<?php

namespace App\Http\Controllers\Admin\ServiceManagement;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategories;
use App\Models\Services;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceManagementController extends Controller
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
        $services = Services::with('ServiceCategories')->get();
        return view('Admin.service-management.index', ['services' => $services]);
    }

    /**
     * @return View
     */
    public function create(): View
    {
        $serviceCategory = ServiceCategories::all();
        return view('Admin.service-management.create', ['serviceCategory' => $serviceCategory]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'service_category_id' => 'required',
            'service_name' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);
        Services::create($request->all());
        return redirect()->route('service-management.index')->with('success', 'Service Created Successfully');
    }

    /**
     * @param Services $service_management
     * @return View
     */
    public function edit(Services $service_management): View
    {
        $serviceCategory = ServiceCategories::all();
        return view('Admin.service-management.edit', ['serviceCategory' => $serviceCategory, 'service' => $service_management]);
    }

    /**
     * @param Request $request
     * @param Services $service_management
     * @return RedirectResponse
     */
    public function update(Request $request, Services $service_management): RedirectResponse
    {
        $request->validate([
            'service_category_id' => 'required',
            'service_name' => 'required',
            'title' => 'required',
            'description' => 'required',
        ]);
        $service_management->update($request->all());
        return redirect()->route('service-management.index')->with('success', 'Service Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Services $services)
    {
        //
    }
}
 
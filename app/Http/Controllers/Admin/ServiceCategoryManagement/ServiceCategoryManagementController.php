<?php

namespace App\Http\Controllers\Admin\ServiceCategoryManagement;

use App\Http\Controllers\Controller;
use App\Models\ServiceCategories;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceCategoryManagementController extends Controller
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
        $serviceCategory = ServiceCategories::all();
        return view('Admin.service-category-management.index', ['serviceCategory' => $serviceCategory]);
    }

    /**
     * @return View
     */
    public function create(): View
    {
        return view('Admin.service-category-management.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category_name' => 'required',
        ]);
        $data = $request->all();
        $data['tags'] = '["cleaning"]';
        ServiceCategories::create($data);
        return redirect()->route('service-category-management.index')->with('success', 'Service Category Created Successfully');
    }

    /**
     * @param ServiceCategories $service_category_management
     * @return View
     */
    public function edit(ServiceCategories $service_category_management): View
    {
        return view('Admin.service-category-management.edit', ['service_category' => $service_category_management]);
    }

    /**
     * @param Request $request
     * @param ServiceCategories $service_category_management
     * @return RedirectResponse
     */
    public function update(Request $request, ServiceCategories $service_category_management): RedirectResponse
    {
        $request->validate([
            'category_name' => 'required',
//            'tags' => 'required'
        ]);
        $service_category_management->update($request->all());
        return redirect()->route('service-category-management.index')->with('success', 'Service Category Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceCategories $serviceCategories)
    {
        //
    }
}
 
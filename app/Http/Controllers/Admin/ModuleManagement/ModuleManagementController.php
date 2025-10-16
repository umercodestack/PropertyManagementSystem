<?php

namespace App\Http\Controllers\Admin\ModuleManagement;

use App\Http\Controllers\Controller;
use App\Models\PermissionModule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModuleManagementController extends Controller
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
        $modules = PermissionModule::all();
        return view('Admin.permission-module-management.index', ['modules' => $modules]);
    }

    /**
     * @return View
     */
    public function create(): View
    {
        $parentModules = PermissionModule::where('is_parent', 1)->get();
        $maxPosition = PermissionModule::max('position');
        return view('Admin.permission-module-management.create', [
            'maxPosition' => $maxPosition+1,
            'parentModules' => $parentModules
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'permission' => 'required',
            'module_name' => 'required',
            'module_icon' => 'required',
            // 'module_route' => 'required',
        ]);
        $data = $request->all();
        if (isset($data['parent_module_id']) && !empty($data['parent_module_id'])) {
            $data['is_parent'] = 0;
        } 
        PermissionModule::create($data);
        return redirect()->route('permission-module-management.index')->with('success', 'Permission Module Created Successfully');
    }

    /**
     * @return View
     */
    public function edit(PermissionModule $permission_module_management)
    {
        return view('Admin.permission-module-management.edit', ['module' => $permission_module_management]);
    }

    public function update(Request $request, PermissionModule $permission_module_management)
    {
        $request->validate([
            'permission' => 'required',
            'module_name' => 'required',
            'module_icon' => 'required',
            // 'module_route' => 'required',
        ]);
        
        $permission_module_management->update($request->all());
        return redirect()->route('permission-module-management.index')->with('success', 'Module Updated Successfully');
    }

    public function destroy(PermissionModule $permission_module_management)
    {
        dd(200);
        $permission_module_management->delete();
        return redirect()->route('permission-module-management.index')->with('success', 'Module Deleted Successfully');
    }
}
 
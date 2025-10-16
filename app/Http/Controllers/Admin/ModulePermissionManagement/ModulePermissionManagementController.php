<?php

namespace App\Http\Controllers\Admin\ModulePermissionManagement;

use App\Http\Controllers\Controller;
use App\Models\PermissionRoleModule;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ModulePermissionManagementController extends Controller
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
        $permission_role_modules = PermissionRoleModule::all();
        return view('Admin.permission-role-module-management.index', ['permission_role_modules' => $permission_role_modules]);
    }

    /**
     * @return View
     */
    public function create(): View
    {
        $user = User::all();
        $role = Roles::all();
        $modules = Roles::all();
        return view('Admin.permission-role-module-management.create');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'module_name' => 'required',
            'module_icon' => 'required',
            'module_route' => 'required',
        ]);
        PermissionRoleModule::create($request->all());
        return redirect()->route('Admin.permission-role-module-management.index')->with('success', 'Permission Module Created Successfully');
    }

    public function edit()
    {
        $modules = PermissionRoleModule::all();
        return view('Admin.permission-role-module-management.index', ['modules' => $modules]);
    }

    public function update()
    {

    }
}
 
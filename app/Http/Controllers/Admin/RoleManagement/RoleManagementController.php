<?php

namespace App\Http\Controllers\Admin\RoleManagement;

use App\Http\Controllers\Controller;
use App\Models\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleManagementController extends Controller
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
        $roles = Roles::all();
        return view('Admin.role-management.index', ['roles' => $roles]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Admin.role-management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required',
        ]);
        Roles::create($request->all());
        return redirect()->route('role-management.index')->with('success', 'Role Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Roles $roles)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Roles $role_management)
    {
        return view('Admin.role-management.edit', ['role' => $role_management]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Roles $role_management)
    {
        $request->validate([
            'role_name' => 'required',
        ]);
        $role_management->update($request->all());
        return redirect()->route('role-management.index')->with('success', 'Role Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Roles $roles)
    {
        //
    }
}
 
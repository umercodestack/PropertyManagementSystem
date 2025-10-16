<?php

namespace App\Http\Controllers\Admin\Rolepermission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Roles;
use Illuminate\Support\Facades\DB; 

class RolePermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $roles = Roles::all();
        $rights = "";
        return view('Admin.role-permissions.index', compact('roles','rights'));
    }

    public function fetchPermissions(Request $request)
    {
            // Debugging: Remove `dd($permissions)` after testing
        //    dd($rights); 
        $roleId = $request->input('role_id');
        $selectedRoleId = request()->input('role_id'); 
        $roles = Roles::all();

   // dd('Role not found for ID:', $roles);  // Debugging output


    // Fetch permissions
    $rights = DB::table('permission_module as pm')
        ->leftJoin('permission_role_module as prm', function($join) use ($roleId) {
            $join->on('prm.module_id', '=', 'pm.id')
                 ->where('prm.role_id', '=', $roleId);
        })
        ->select('pm.id', 'pm.permission', 'pm.module_name', 'prm.id as PermissionAssignedId')
        ->orderBy('pm.module_name')
        ->get();
    
    // Optional: Check if permissions are empty
    if ($rights->isEmpty()) {
        return back()->with('error', 'No permissions found for this role.');
    }

        
        return view('Admin.role-permissions.index', compact('rights','roles','selectedRoleId'));
    }

    public function savePermissions(Request $request)
    {
        $roleId = $request->input('role_id');
        $selectedPermissions = $request->input('permissions', []);

        // Remove all current permissions for the role
        DB::table('permission_role_module')->where('role_id', $roleId)->delete();

        // Insert new permissions
        foreach ($selectedPermissions as $moduleId) {
            DB::table('permission_role_module')->insert([
                'role_id' => $roleId,
                'module_id' => $moduleId,
                'access_level' => 'Full Access', // Customize this field if needed
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Permissions updated successfully!');
    }


    


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

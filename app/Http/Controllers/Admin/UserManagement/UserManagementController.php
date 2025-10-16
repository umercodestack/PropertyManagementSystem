<?php

namespace App\Http\Controllers\Admin\UserManagement;

use App\Http\Controllers\Controller;
use App\Models\Channels;
use App\Models\Group;
use App\Models\HostType;
use App\Models\PermissionModule;
use App\Models\Properties;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * @return View
     */

    public function __construct()
    {
        // $this->middleware('permission');
    }

    public function index(): View
    {
        $users = User::orderBy('id', 'desc')->get();
        return view('Admin.user-management.index', ['users' => $users]);
    }

    /**
     * @return View
     */
    public function create(Request $request): View
    {
        $roles = Roles::all();
        $users = User::orderBy('id', 'desc')->get();
        $host_type = HostType::orderBy('id', 'desc')->get();

        // Capture query parameters
        $hostactivationId = $request->input('hostactivation_id');
        $ownerName = $request->input('owner_name');
        $last_name = $request->input('last_name');
        $hostnumber = $request->input('host_number');
        $host_email = $request->input('host_email');
        // Set a default role if query string data is present
        $defaultRole = $hostactivationId ? 'Host' : null;

        return view('Admin.user-management.create', [
            'roles' => $roles,
            'users' => $users,
            'host_type' => $host_type,
            'hostactivationId' => $hostactivationId,
            'ownerName' => $ownerName,
            'last_name' => $last_name,
            'hostnumber' => $hostnumber,
            'host_email' => $host_email,
            'defaultRole' => $defaultRole
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'host_key' => 'required',
            'role_id' => 'required',
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string',
            'dob' => 'required|string',
            'gender' => 'required|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'password' => 'required|min:6',
        ]);
        $randomEmailStr = Str::random(4);
        $data = $request->all();
        $data['password'] = bcrypt($data['password']);
        $data['email_verification_code'] = $randomEmailStr;
        $data['plan_verified'] = 1;
        $data['email_verified'] = 1;
        $data['parent_user_id'] = $request->parent_user;
        $data['host_activation_id'] = $request->hostactivation_id;
        $user = User::create($data);

    if (!empty($request->parent_user)) {
        // Group
        $group = Group::where('user_id', $request->parent_user)->first();
        if ($group) {
            $groupArr = $group->toArray();
            $groupArr['user_id'] = $user->id;
            Group::create($groupArr);
        }

        // Property
        $property = Properties::where('user_id', $request->parent_user)->first();
        if ($property) {
            $propertyArr = $property->toArray();
            $propertyArr['email'] = $user->email;
            $propertyArr['user_id'] = $user->id;
            Properties::create($propertyArr);
        }

        // Channel
        $channel = Channels::where('user_id', $request->parent_user)->first();
        if ($channel) {
            $channelArr = $channel->toArray();
            $channelArr['user_id'] = $user->id;
            Channels::create($channelArr);
        }
    }
        return redirect()->route('user-management.index')->with('success', 'User Created Successfully');
    }

    /**
     * @param User $user_management
     * @return View
     */
    public function edit(User $user_management): View
    {
        $roles = Roles::all();
        $users = User::all();
        $host_type = HostType::orderBy('id', 'desc')->get();
        $countries = DB::table("countries")->get();
        return view('Admin.user-management.edit', ['roles' => $roles, 'users' => $users, "host_type" => $host_type, 'user' => $user_management, 'countries' => $countries]);
    }
    /**
     * @param Request $request
     * @param User $user_management
     * @return RedirectResponse
     */
    public function update(Request $request, User $user_management): RedirectResponse
    {
        //$data = $request->all();
        $data = array_filter($request->all());
        $user_management->update($data);
        return redirect()->back();
    }

    /**
     * @param User $user
     * @return View
     */
    public function resetPassword(User $user): View
    {
        return view('Admin.authentication.forget-password-host', ['user' => $user]);
    }

    /**
     * @return View
     */
    public function resetPasswordSuccess(): View
    {
        return view('Admin.authentication.forget-password-success');
    }

    public function managePermissions($userId)
    {
        $user = User::findOrFail($userId);
        $permissionModules = PermissionModule::all();
        $userPermissions = $user->permissions;

        return view('Admin.user-management.manage-permissions', [
            'userPermissions' => $userPermissions,
            'permissionModules' => $permissionModules,
            'user' => $user
        ]);

    }

    public function updatePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->modules()->sync($request->permissions);

        return redirect()->action([UserManagementController::class, 'index']);
    }
}

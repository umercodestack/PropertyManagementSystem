<?php

namespace App\Http\Controllers\Admin\GroupManagement;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;

class GroupManagementController extends Controller
{
//    public function checkAndUpdateLatestData()
//    {
//        $userApiKey = env('CHANNEX_API_KEY');
//        $response = Http::withHeaders([
//            'user-api-key' => $userApiKey,
//        ])->get(env('CHANNEX_URL').'/api/v1/groups');
//
//        if ($response->successful()) {
//            $response = $response->json();
//            $data = $response['data'];
//            foreach ($data as $item) {
//                $group = Group::where('ch_group_id', $item['id'])->first();
//                dd($group);
//                if ($group)
//            }
//            dd($data);
//        }
//    }
    /**
     * @return View
     */
     
    public function __construct()
    {
        $this->middleware('permission');
    }
     
    public function index(): View
    {
//        dd($this->checkAndUpdateLatestData());
        $groups = Group::with('user')->get();
        return view('Admin.group-management.index', ['groups' => $groups]);
    }
    /**
     * @return View
     */
    public function create(): View
    {
        $users = User::all();
        return view('Admin.group-management.create', ['users' => $users]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'group_name' => 'required',
            'user_id' => 'required',
        ]);
        $response = Http::withHeaders([
            'user-api-key' =>  env('CHANNEX_API_KEY'),
        ])->post(env('CHANNEX_URL').'/api/v1/groups', [
            "group" =>  ["title" => $request->group_name.'_'.$request->user_id]
        ]);
        if ($response->successful()) {
            $response = $response->json();
            $data = $request->all();
            $data['ch_group_id'] = $response['data']['id'];
            Group::create($data);
            return redirect()->route('group-management.index')->with('success', 'Group Created Successfully');
        } else {
            $error = $response->body();
            return redirect()->route('group-management.index')->with('error', $error);
        }
    }

    /**
     * @param Group $group_management
     * @return View
     */
    public function edit(Group $group_management): View
    {
        $users = User::all();
        return view('Admin.group-management.edit', ['users' => $users, 'group' => $group_management]);
    }

    /**
     * @param Request $request
     * @param Group $group_management
     * @return RedirectResponse
     */
    public function update(Request $request, Group $group_management): RedirectResponse
    {
        $request->validate([
            'group_name' => 'required',
        ]);
        $response = Http::withHeaders([
            'user-api-key' =>  env('CHANNEX_API_KEY'),
        ])->put(env('CHANNEX_URL')."/api/v1/groups/$group_management->ch_group_id", [
            "group" =>  ["title" => $request->group_name]
        ]);
        if ($response->successful()) {
            $response = $response->json();
            $group_management->update([
                'group_name' => $response['data']['attributes']['title']
            ]);
            return redirect()->route('group-management.index')->with('success', 'Group Updated Successfully');
        } else {
            $error = $response->body();
            return redirect()->route('group-management.index')->with('error', $error);
        }
    }
    public function destroy(Group $group_management)
    {
        $group_management->delete();
    }
}
 
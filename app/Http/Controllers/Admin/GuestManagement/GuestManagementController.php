<?php

namespace App\Http\Controllers\Admin\GuestManagement;

use App\Http\Controllers\Controller;
use App\Models\Guests;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GuestManagementController extends Controller
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
        $guests= Guests::all();
        return view('Admin.guest-management.index', ['guests' => $guests]);
    }

    public function create()
    {
        return view('Admin.guest-management.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'dob' => 'required',
            'gender' => 'required',
            'country' => 'required',
        ]);
        Guests::create($request->all());
        return redirect()->route('guest-management.index')->with('success', 'Guest Created Successfully');
    }

    public function edit(Guests $guest_management)
    {
        return view('Admin.guest-management.edit', ['guest' => $guest_management]);
    }
    public function fetchGuestByGuestId(Request $request)
    {
//        dd($request);
        $guest = Guests::where('phone', $request->phone)->orderBy('id', 'DESC')->first();
        if ($guest) {
            return response()->json($guest);
        }
        else {
            return response()->json("no data");
        }
//        return view('Admin.guest-management.edit', ['guest' => $guest_management]);
    }
}
 
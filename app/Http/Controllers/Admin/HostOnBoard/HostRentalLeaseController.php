<?php

namespace App\Http\Controllers\Admin\HostOnBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HostRentalLease;
use Illuminate\Support\Facades\Auth;

class HostRentalLeaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lease = HostRentalLease::where('visible', 1) ->orderBy('created_at', 'desc') ->get();
        return view('Admin.host-rental-lease.index', compact('lease'));
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
        $op = HostRentalLease::with(['hostaboard'])->findOrFail($id);
        return view('Admin.host-rental-lease.edit', compact('op'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
           
            'status' => 'nullable|string|max:50',
            'remarks' => 'nullable|string',
            'updated_by' => 'nullable|integer',
        ]);

        $validated['updated_by'] = Auth::id();

        $lease = HostRentalLease::findOrFail($id);
        $lease->update($validated);

        return redirect()->route('host-rental-lease.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

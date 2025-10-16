<?php

namespace App\Http\Controllers\Admin\HostOnBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditListing;
use App\Models\Hostaboard;
use App\Models\auditlistingmapping;
use Illuminate\Support\Facades\Auth;

class AuditListingMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auditListingsmapping = auditlistingmapping::with('hostaboard','auditlisting')->orderByDesc('id')->get();
        return view('Admin.auditlistingmapping.index', compact('auditListingsmapping'));
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
    public function edit($id)
    {
        
        $auditMapping = auditlistingmapping::with('hostaboard')->findOrFail($id);

        return view('Admin.auditlistingmapping.edit', compact('auditMapping'));
    }

    /**
     * Update the specified resource in storage.
     */
public function update(Request $request, string $id)
{
    $auditMapping = auditlistingmapping::findOrFail($id);
    $auditListing = AuditListing::findOrFail($auditMapping->auditlisting_id);

    $validated = $request->validate([
        'airbnb' => 'nullable|string',
        'booking_com' => 'nullable|string',
        'vrbo' => 'nullable|string',
        'al_mosafer' => 'nullable|string',
        'agoda' => 'nullable|string',
        'golden_host' => 'nullable|string',
        'aqar' => 'nullable|string',
        'bayut' => 'nullable|string',
        'google_hotels' => 'nullable|string',
        'gathen' => 'nullable|string',
        'darent' => 'nullable|string',
        'status' => 'nullable|string',
        'remarks' => 'nullable|string',
    ]);

    // OTA platforms
    $otaPlatforms = [
        'airbnb',
        'booking_com',
        'vrbo',
        'al_mosafer',
        'agoda',
        'golden_host',
        'aqar',
        'bayut',
        'google_hotels',
        'gathen',
        'darent',
    ];

    foreach ($otaPlatforms as $platform) {
        $statusKey = $platform . '_status';
        $validated[$statusKey] = $request->has($statusKey) ? $request->input($statusKey) : null;

        // Also update in AuditListing model
        $auditListing->$statusKey = $validated[$statusKey];
    }

    $validated['updated_by'] = Auth::id();

    $auditMapping->update($validated);
    $auditListing->save(); // Save updated statuses

    return redirect()->route('listing-audit-mapping.index')->with('success', 'Mapping Listing updated successfully.');
}


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

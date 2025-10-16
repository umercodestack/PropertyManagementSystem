<?php

namespace App\Http\Controllers\Admin\HostOnBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditListing;
use App\Models\Hostaboard;
use App\Models\auditlistingmapping;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AuditListingController extends Controller
{
    public function index()
    {
        $auditListings = AuditListing::with('hostaboard')->orderByDesc('id')->get();


        $propertyCounts = Hostaboard::select('host_id', DB::raw('count(*) as total'))
            ->groupBy('host_id')
            ->pluck('total', 'host_id');

    
        $auditCounts = AuditListing::with('hostaboard')
            ->get()
            ->groupBy(fn($listing) => $listing->hostaboard->host_id ?? null)
            ->map(fn($group) => $group->count());

        return view('Admin.auditlisting.index', compact('auditListings', 'propertyCounts', 'auditCounts'));
    }

    public function create()
    {
        return view('Admin.auditlisting.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hostaboard_id' => 'required|integer',
            'AuditId' => 'required|integer',
            'airbnb' => 'required|string',
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

        $validated['updated_by'] = Auth::id();

        AuditListing::create($validated);

        return redirect()->route('listing-audit.index')->with('success', 'Audit Listing created successfully.');
    }

    public function show(AuditListing $auditListing)
    {
        return view('Admin.auditlisting.show', compact('auditListing'));
    }

    public function edit($id)
    {
        $auditListing = AuditListing::findOrFail($id);
        return view('Admin.auditlisting.edit', compact('auditListing'));
    }

    public function update(Request $request, $id)
    {
    $auditListing = AuditListing::findOrFail($id);

    $validated = $request->validate([
        'airbnb' => 'required|string',
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
    }

    $validated['updated_by'] = Auth::id();

    $auditListing->update($validated);

    unset($validated['status'], $validated['remarks']);

    $validated['updated_by'] = Auth::id();
    $validated['hostaboard_id'] = $auditListing->hostaboard_id;
    $validated['audit_id'] = $auditListing->audit_id ?? null;
    $validated['auditlisting_id'] = $auditListing->id;

    $auditMapping = auditlistingmapping::where('auditlisting_id', $auditListing->id)->first();

    if ($auditMapping) {
        $auditMapping->update($validated);
    } else {
        auditlistingmapping::create($validated);
    }



     notifyheader(
                8,
                'OTA Links Upload',
                $id,
                "review",
                "review OTA links",
                url("/listing-audit/{$id}/edit"),
                false
            );

    return redirect()->route('listing-audit.index')->with('success', 'Audit Listing updated successfully.');
    }


    public function destroy(AuditListing $auditListing)
    {
        $auditListing->delete();

        return redirect()->route('auditlisting.index')->with('success', 'Audit Listing deleted successfully.');
    }
}

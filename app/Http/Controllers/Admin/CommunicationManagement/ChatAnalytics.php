<?php

namespace App\Http\Controllers\Admin\CommunicationManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\StoreProcedureService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ChatAnalytics extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $users = User::where('role_id', '=', 8)->get();

    $listings = DB::table('listings')
        ->select('listing_id', DB::raw("JSON_UNQUOTE(JSON_EXTRACT(listing_json, '$.title')) as title"))
        ->whereNotIn('listing_id', function ($query) {
            $query->select('listing_id')->from('churned_properties');
        })
        ->get();

    


    $fromDate = $request->input('from_date') ?? now()->format('Y-m-d');
    $toDate = $request->input('to_date') ?? now()->format('Y-m-d');

    //dd($fromDate . ' - ' . $toDate);

    // Get optional filters or set null
    $agentId = $request->filled('agent') && $request->input('agent') == '' ? (int) $request->input('agent') : null;
    
    $listingId = $request->filled('listing') && $request->input('listing') == '' ? (int) $request->input('listing') : null;

    // Call stored procedure with optional parameters
    // Assuming your SP signature is updated to accept 4 params (start_date, end_date, agent_id, listing_id)
    $results = DB::select('CALL sp_get_threads_handling_report(?, ?, ?, ?)', [$fromDate,$toDate,$agentId,$listingId,]);

    $totalThreads = count($results);

    $totalInquiries = collect($results)
        ->whereIn('status', ['inquiry', 'requested for booking'])
        ->count();

    $totalConfirmedBookings = collect($results)
        ->filter(function ($item) {
            $status = strtolower(trim($item->status));
            return $status === 'booking confirmed' || $status === 'booking confirm';
        })
        ->count();

    $avgFirstResponseSeconds = collect($results)
        ->filter(fn ($item) => isset($item->response_seconds))
        ->avg('response_seconds');

    $avgTotalHandlingSeconds = collect($results)
        ->filter(fn ($item) => isset($item->total_handling_seconds))
        ->avg('total_handling_seconds');

    $secondsToTime = function ($seconds) {
        return gmdate('H:i:s', max(0, intval($seconds)));
    };

    $avgFirstResponseTime = $secondsToTime($avgFirstResponseSeconds);
    $avgTotalHandlingTime = $secondsToTime($avgTotalHandlingSeconds);

    $totalNightsSum = collect($results)
    ->filter(fn($item) => isset($item->total_nights))
    ->sum('total_nights');

    return view('Admin.chat-analytics.index', compact(
        'results', 'fromDate', 'toDate',
        'totalThreads', 'totalInquiries', 'totalConfirmedBookings',
        'avgFirstResponseTime', 'avgTotalHandlingTime','totalNightsSum', 'users', 'listings'
    ));
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

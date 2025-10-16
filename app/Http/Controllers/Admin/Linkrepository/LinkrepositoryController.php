<?php

namespace App\Http\Controllers\Admin\Linkrepository;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Linkrepository; 
use App\Models\User;
use App\Models\Listing;
use Illuminate\Support\Facades\Auth;



class LinkrepositoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        $linkrepository = Linkrepository::with(['listing','userdetail','hostdetail'])->get();
        
        return view('Admin.link-respository.index', [
            'linkrepository' => $linkrepository,
            
        ]);
    }

    public function getListingsByHost($host_id)
    {
    
        $listings = Listing::whereJsonContains('user_id', $host_id)->get(['id', 'listing_json']);

   
        $listings = $listings->map(function ($item) {
        $listing_json = json_decode($item->listing_json);
        return [
            'id' => $item->id,
            'title' => $listing_json->title,
         ];
        });

        return $listings;

    //    return response()->json($listings);

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::where('role_id', '!=', 2)->get();
        $listings = Listing::all();
        return view('Admin.link-respository.create', ['listings' => $listings, 'users' => $users]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|integer',
            'host_id' => 'nullable|integer',
            'listing_id' => 'required|integer',
            'airbnb' => 'nullable|string',
            'gathern' => 'nullable|string',
            'booking' => 'nullable|string',
            'vrbo' => 'nullable|string',
            'status' => 'nullable|string'
        ]);
        
        if (Linkrepository::where('listing_id', $request->input('listing_id'))->exists()) {
            return redirect()->back()
                ->withErrors(['listing_id' => 'The listing ID already exists.'])
                ->withInput(); // Keep old input
        }

        $linkrepository = new Linkrepository();
        $linkrepository->user_id = Auth::user()->id;
        $linkrepository->host_id = $request->input('host_id');
        
        $linkrepository->listing_id = $request->input('listing_id');

        $linkrepository->airbnb = $request->input('airbnb');
        $linkrepository->gathern = $request->input('gathern');
        $linkrepository->booking = $request->input('booking');
        $linkrepository->vrbo = $request->input('vrbo');
        $linkrepository->status = $request->input('status');
       

        $linkrepository->save();

        return redirect()->route('linkrepository.index')->with('success', 'Created Successfully');

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
    public function edit(linkrepository $linkrepository)
    {
        $users = User::where('role_id', '!=', 2)->get();
        $listings = Listing::all();

        return view('Admin.link-respository.edit', ['listings' => $listings, 'users' => $users,'linkrepository' => $linkrepository]);


    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $Linkrepository = Linkrepository::findOrFail($id);
        $Linkrepository = $Linkrepository->update($request->all());
        return redirect()->route('linkrepository.index')->with('success', 'updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}

<?php

namespace App\Http\Controllers\Admin\VendorServiceManagement;

use App\Http\Controllers\Controller;
use App\Models\VendorServices;
use Illuminate\Http\Request;

class VendorServiceManagementController extends Controller
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
        //
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
    public function show(VendorServices $vendorServices)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VendorServices $vendorServices)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, VendorServices $vendorServices)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(VendorServices $vendorServices)
    {
        //
    }
}
 
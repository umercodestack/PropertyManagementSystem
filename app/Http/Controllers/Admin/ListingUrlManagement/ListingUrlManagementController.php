<?php

namespace App\Http\Controllers\Admin\ListingUrlManagement;

use App\Http\Controllers\Controller;
use App\Models\ListingUrl;
use Illuminate\Contracts\View\View;

class ListingUrlManagementController extends Controller
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
        $listingUrl = ListingUrl::with('user')->get();
        return view('Admin.listing-url-management.index', ['listingUrl' => $listingUrl]);
    }
}
 
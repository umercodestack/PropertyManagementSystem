<?php

namespace App\Http\Controllers\Admin\VoucherManagement;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Models\{
    Listing,
    Voucher
};

class VoucherManagementController extends Controller
{

    public function __construct()
    {
        // $this->middleware('permission');
    }

    public function index(): View
    {
        Voucher::where('voucher_end_date', '<', date('Y-m-d'))->update([
            'is_enabled' => 0
        ]);

        $vouchers = Voucher::orderBy('id', 'desc')->get();
        return view('Admin.voucher-management.index', ['vouchers' => $vouchers]);
    }

    public function create(Request $request): View
    {
        // $listings = Listing::where('is_sync', 'sync_all')->get();
        
        $listings = Listing::where('is_sync', 'sync_all')
                ->get()
                ->map(function($item) {
                    
                    $jsn = !empty($item->listing_json) ? json_decode($item->listing_json) : null;
                    $listing_name = !empty($jsn->title) ? $jsn->title : '';
                
                    return [
                        'label' => $listing_name,
                        'value' => $item->listing_id,
                    ];
                })
                ->values();
        
        $listings->prepend([
            'label' => 'Select All',
            'value' => 'all',
        ]);
        
        // print_r($listings);die;

        return view('Admin.voucher-management.create', ['listings' => json_encode($listings)]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'voucher_code' => 'required',
            'voucher_start_date' => 'required',
            'voucher_end_date' => 'required',
            'discount_type' => 'required',
            'discount' => 'required',
            // 'max_discount_amount' => 'required',
            'min_order_amount' => 'required',
            'min_number_nights' => 'required',
            'discount_applied_on' => 'required',
            'voucher_usage_limit' => 'required',
            'max_uses_per_guest' => 'required'
        ]);
        
        // print_r($request->all());die;
        
        $data = $request->except('_token');
        
        $data['is_enabled'] = !empty($data['is_enabled']) ? 1 : 0;
        
        $data['discount'] = str_replace('%', '', trim($data['discount']));
        
        $data['created_by'] = auth()->user()->name;
        
        // if(!empty($data['listing_ids'])){
        //     $data['listing_ids'] = implode(',', $data['listing_ids']);
        // }
        
        if(empty($data['listing_ids'])){
            $data['listing_ids'] = 'all';
        }
        
        // print_r($data);die;
        
        $voucher = Voucher::create($data);
        
        return redirect()->route('voucher-management.index')->with('success', 'Voucher Created Successfully');
    }

    public function edit($id): View
    {
        // $listings = Listing::where('is_sync', 'sync_all')->get();
        
        $listings = Listing::where('is_sync', 'sync_all')
        ->get()
        ->map(function($item) {
            
            $jsn = !empty($item->listing_json) ? json_decode($item->listing_json) : null;
            $listing_name = !empty($jsn->title) ? $jsn->title : '';
        
            return [
                'label' => $listing_name,
                'value' => $item->listing_id,
            ];
        })
        ->values();
        
        $listings->prepend([
            'label' => 'Select All',
            'value' => 'all',
        ]);

        $voucher = Voucher::findOrFail($id);
        
        $selected_listing_ids = [];
        if(!empty($voucher->listing_ids) && $voucher->listing_ids != 'all'){
            $voucher->listing_ids = explode(',', $voucher->listing_ids);
            
            foreach($voucher->listing_ids as $vli){
                $vlisting = Listing::where('listing_id', $vli)->first();
                
                if(!is_null($vlisting)){
                    $selected_listing_ids[] = $vlisting->listing_id;
                }
            }
        }
        
        if(empty($selected_listing_ids) && $voucher->listing_ids == 'all'){
            $selected_listing_ids[] = 'all';
        }
        
        // print_r($listings);die;
        
        // print_r($selected_listing_ids);die;
        
        return view('Admin.voucher-management.edit', ['listings' => json_encode($listings), 'voucher' => $voucher, 'selected_listing_ids'=>json_encode($selected_listing_ids)]);
    }

    public function update($id, Request $request): RedirectResponse
    {
        $request->validate([
            'voucher_code' => 'required',
            'voucher_start_date' => 'required',
            'voucher_end_date' => 'required',
            'discount_type' => 'required',
            'discount' => 'required',
            // 'max_discount_amount' => 'required',
            'min_order_amount' => 'required',
            'min_number_nights' => 'required',
            'discount_applied_on' => 'required',
            'voucher_usage_limit' => 'required',
            'max_uses_per_guest' => 'required'
        ]);
        
        $voucher = Voucher::findOrFail($id);
        
        $data = [
            'listing_ids' => empty($request->listing_ids) ? 'all' : $request->listing_ids,
            'voucher_code' => $request->voucher_code,
            'voucher_start_date' => $request->voucher_start_date,
            'voucher_end_date' => $request->voucher_end_date,
            'min_order_amount' => $request->min_order_amount,
            'min_number_nights' => $request->min_number_nights,
            'discount_applied_on' => $request->discount_applied_on,
            'max_uses_per_guest' => $request->max_uses_per_guest,
            'discount' => $request->discount,
            'discount_type' => $request->discount_type,
            'max_discount_amount' => $request->max_discount_amount,
            'voucher_usage_limit' => $request->voucher_usage_limit,
            'is_enabled' => !empty($request->is_enabled) ? 1 : 0,
            'created_by' => auth()->user()->name
        ];
        
        $data['discount'] = str_replace('%', '', trim($data['discount']));
        
        // if(!empty($request->listing_ids) && $request->listing_ids != 'all'){
        //     $data['listing_ids'] = implode(',', $data['listing_ids']);
        // }
        
        $voucher->update($data);

        return redirect()->route('voucher-management.index')->with('success', 'Voucher Created Successfully');
    }
}

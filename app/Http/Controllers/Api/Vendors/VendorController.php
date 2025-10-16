<?php

namespace App\Http\Controllers\Api\Vendors;

use App\Http\Controllers\Controller;
use App\Http\Resources\VendorResource;
use App\Models\Vendors;
use App\Models\Services;
use Illuminate\Http\JsonResponse as JsonResponseAlias;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class VendorController extends Controller
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $vendors = Vendors::all();
        return VendorResource::collection($vendors);
    }

    /**
     * @param Request $request
     * @return JsonResponseAlias|VendorResource
     */
    public function store(Request $request): JsonResponseAlias|VendorResource
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'service_id' => 'required',
            'country_code' => 'required',
           
            // 'location' => 'required',
            // 'occupation' => 'required',
            // 'availability' => 'required',
            // 'last_hired' => 'required',
            // 'time_duration' => 'required',
            // 'picture' => 'required',
            // 'is_active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        return new VendorResource(Vendors::create($request->all()));
    }

    /**
     * @param Vendors $vendor
     * @return VendorResource
     */
    public function show(Vendors $vendor): VendorResource
    {
        return new VendorResource($vendor);
    }

    /**
     * @param Request $request
     * @param Vendors $vendor
     * @return JsonResponseAlias|VendorResource
     */
    public function update(Request $request, Vendors $vendor): JsonResponseAlias|VendorResource
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'service_id' => 'required',
            'country_code' => 'required',
           
            // 'location' => 'required',
            // 'occupation' => 'required',
            // 'availability' => 'required',
            // 'last_hired' => 'required',
            // 'time_duration' => 'required',
            // 'picture' => 'required',
            // 'is_active' => 'required',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $vendor->update($request->all());
    
       
        return new VendorResource($vendor->load('service'));
    }
    
    
    function get_vendor_list()
    {
       
        $data = DB::Select("CALL sp_get_vendor_list_v1();");
        return response()->json($data);
    }
    
    
    function get_vendor_by_id($id)
    {
       
          $data = DB::select("CALL sp_get_vendor_by_Id_v1(?)", [$id]);
          $vendor = $data[0] ?? null; // Get the first object if available
          return response()->json($vendor);
    }

    /**
     * @param Vendors $vendor
     * @return VendorResource
     */
    public function destroy(Vendors $vendor): VendorResource
    {
        $vendor->delete();
        return new VendorResource($vendor);
    }
}
 
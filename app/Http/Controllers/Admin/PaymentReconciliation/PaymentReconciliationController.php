<?php

namespace App\Http\Controllers\Admin\PaymentReconciliation;

use App\Http\Controllers\Controller;

use App\Models\Bookings;
use App\Models\BookingOtasDetails;
use App\Models\PaymentReconciliation;
use Illuminate\Http\Request;

class PaymentReconciliationController extends Controller
{

    public function index()
    {
        $data['bookings'] = Bookings::orderBy('created_at', 'DESC')->get();
        $data['ota_bookings'] = BookingOtasDetails::orderBy('created_at', 'DESC')->get();

        return view('Admin.payment-reconciliation.index', ['data' => $data]);
    }
    
    public function store(Request $request)
    {
        try{
            // return BookingOtasDetails::where('id', 3)->get();
            // return $request->all();
            
            $data = [];
            
            if($request->hasFile('ibft_screenshot')){
                
                $request->validate([
                    'ibft_screenshot' => 'required|image|mimes:jpeg,png,jpg|max:4096', // Maximum size 4 MB (4096 KB)
                ]);
                
                $file = $request->file('ibft_screenshot');

                $fileName = date('Y-m-d').time().'.'.$file->getClientOriginalExtension();
                
                $destinationPath = public_path('ibft_screenshot');
                
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0775, true);
                }
                
                $file->move($destinationPath, $fileName);

                $path = 'ibft_screenshot/' . $fileName;
                
                $data['ibft_screenshot'] = $path;

            }
        
            $data['payment_recevied_ota'] = $request->filled('payment_recevied_ota') ? $request->payment_recevied_ota : '';
            $data['payment_received_date'] = $request->filled('payment_received_date') ? $request->payment_received_date : '';
            $data['bank_charges'] = $request->filled('bank_charges') ? $request->bank_charges : '';
            $data['bank_statement'] = $request->filled('bank_statement') ? $request->bank_statement : '';
            $data['remarks'] = $request->filled('remarks') ? $request->remarks : '';
            
            if($request->filled('booking_type')){
                
                if($request->booking_type == "bookings"){
                    $data['booking_id'] = $request->booking_id;
                    $data['ota_booking_id'] = null;
                    
                    $record = PaymentReconciliation::updateOrCreate(
                        ['booking_id' => $data['booking_id']],
                        $data
                    );
                    
                }
                
                if($request->booking_type == "ota_bookings"){
                    $data['booking_id'] = null;
                    $data['ota_booking_id'] = $request->booking_id;
                    
                    $record = PaymentReconciliation::updateOrCreate(
                        ['ota_booking_id' => $data['ota_booking_id']],
                        $data
                    );
                }
            }
        
            return response()->json([
                'success' => true,
                'message' => 'Form submitted successfully',
                'data' => $data,
            ]);
        } catch(\Exception $ex){
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
                'data' => [],
            ]);
        }
    }

    
}

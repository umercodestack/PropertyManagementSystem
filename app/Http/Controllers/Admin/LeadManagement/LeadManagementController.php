<?php

namespace App\Http\Controllers\Admin\LeadManagement;

use App\Http\Controllers\Controller;
use App\Models\BookingLead;
use App\Models\Guests;
use App\Models\LeadImages;
use App\Models\Listings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    public function index()
    {
        $bookings = BookingLead::all();
        return view('Admin.lead-management.index', ['bookings' => $bookings]);
    }
    public function create()
    {
        $users = User::all();
        $listings = Listings::all();
        $countries = DB::select("Select * from countries");
        return view('Admin.lead-management.create', ['listings' => $listings, 'users' => $users, 'countries' => $countries]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'country' => 'required',
            'city' => 'required',
            'cnic_passport' => 'required',
            'adult' => 'required',
            'children' => 'required',
            'rooms' => 'required',
            'payment_method' => 'required',
            'apartment_id' => 'required',
            'booking_sources' => 'required',
            'daterange' => 'required',
        ]);
        $guest = Guests::where('id', $request->guest_id)->first();
        $data = $request->all();

        $dateRange = $data['daterange'];

        [$startDate, $endDate] = explode(' - ', $dateRange);

        $startDate = Carbon::createFromFormat('m/d/Y', trim($startDate))->format('Y-m-d');
        $endDate = Carbon::createFromFormat('m/d/Y', trim($endDate))->format('Y-m-d');
        $data['booking_date_start'] = $startDate;
        $data['booking_date_end'] = $endDate;
        $data['listing_id'] = $data['apartment_id'];
        if ($guest) {
            $booking = BookingLead::create($data);
            if (count($request->file('image')) > 0) {
                foreach ($request->file('image') as $images){
                    $fileName = time() . '_' . $images->getClientOriginalName();
                    $filePath = $images->storeAs('booking_images', $fileName, 'public');
                    LeadImages::create(
                        [
                            'lead_id' => $booking->id,
                            'image' => $filePath,
                        ]
                    );
                }
            }
            return redirect()->route('lead-management.store')->with('success', 'Booking Created Successfully');
        } else {
            $guest =  Guests::create(
                [
                    'name' => $data['name'],
                    'surname' => $data['surname'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'country' => $data['country'],
                    'city' => $data['city'],
                ]
            );
            $data['guest_id'] = $guest->id;
            $booking = BookingLead::create($data);
            if (count($request->file('image')) > 0) {
                foreach ($request->file('image') as $images){
                    $fileName = time() . '_' . $images->getClientOriginalName();
                    $filePath = $images->storeAs('booking_images', $fileName, 'public');
                    LeadImages::create(
                        [
                            'lead_id' => $booking->id,
                            'image' => $filePath,
                        ]
                    );
                }
            }
            return redirect()->route('lead-management.store')->with('success', 'Booking Created Successfully');
        }
    }
}
 
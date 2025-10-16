<?php

namespace App\Http\Controllers\Admin\ApartmentManagement;

use App\Http\Controllers\Controller;
use App\Models\ApartmentAddress;
use App\Models\ApartmentImages;
use App\Models\ApartmentInitialPrice;
use App\Models\Apartments;
use App\Models\Discounts;
use App\Models\HostType;
use App\Models\User;
use App\Models\Listings;
use App\Models\Calender;
use App\Models\ListingSetting;
use App\Models\ListingAmenity;
use App\Models\AirbnbImage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class ApartmentManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    public function index()
    {
        $apartments = Apartments::orderBy('id', 'desc')->get();
        return view('Admin.apartment-management.index', ['apartments' => $apartments]);
    }

    public function create()
    {
        $users = User::where('role_id', 2)->get();
        $experiencemanagers = User::where('role_id', 6)->get();
        $journeySpecialist = User::where('role_id', 5)->get();
        $hostTypes = HostType::all();
        $discounts = Discounts::all();
        return view('Admin.apartment-management.create', ['users' => $users,'experiencemanagers' => $experiencemanagers, 'journeySpecialist' => $journeySpecialist, 'hostTypes' => $hostTypes, 'discounts' => $discounts]);
    }


    // public function store(Request $request)
    // {
    //     $request->validate([
           
    //         'apartment_type' => 'required',
    //         'rental_type' => 'required',
    //         'description' => 'required',
    //         'title' => 'required',
    //         'max_guests' => 'required',
    //         'bedrooms' => 'required',
    //         'beds' => 'required',
    //         'bathrooms' => 'required',
    //         'amenities' => 'required',
    //         'any_of_these' => 'required',
    //         'unique_attr' => 'required',
    //         'discount_id' => 'nullable',
    //         'price' => 'required',
    //         'latitude' => 'required',
    //         'longitude' => 'required',
    //         'country' => 'nullable',
    //         'address_line' => 'required',
    //         'city' => 'nullable',
    //         'province' => 'nullable',
    //         'postal' => 'nullable',
    //         'apartment_image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
    //     ]);
    //     $data = $request->all();
    //     $data = (object)$data;
    //     $authUser = Auth::user()->id;
    //     $apartmentDetails = [
    //         'user_id' => $data->user_id,
    //         'apartment_type' => $data->apartment_type,
    //         'rental_type' => $data->rental_type,
    //         'description' => $data->description,
    //         'title' => $data->title,
    //         'max_guests' => $data->max_guests,
    //         'bedrooms' => $data->bedrooms,
    //         'beds' => $data->beds,
    //         'bathrooms' => $data->bathrooms,
    //         'amenities' => json_encode($data->amenities),
    //         'any_of_these' => json_encode($data->any_of_these),
    //         'unique_attr' => json_encode($data->unique_attr),
    //         'js_id' => $data->js_id,
    //         'host_type_id' => $data->host_type_id,
    //         'created_by' => $authUser,
    //         'door_lock' => $data->door_lock,
    //     ];
    //     $apartmentDetails['door_lock'] == 'yes' ? $apartmentDetails['door_lock'] = 1 : $apartmentDetails['door_lock'] = 0;
    //     $apartment = Apartments::create($apartmentDetails);

    //     ApartmentAddress::create([
    //         'apartment_id' => $apartment->id,
    //         'latitude' => $request->latitude,
    //         'longitude' => $request->longitude,
    //         'country' => $request->country,
    //         'address_line' => $request->address_line,
    //         'city' => $request->city,
    //         'province' => $request->province,
    //         'postal' => $request->postal,
    //     ]);
    //     // ApartmentInitialPrice::create([
    //     //     'apartment_id' => $apartment->id,
    //     //     'discount_id' => $request->discount_id,
    //     //     'price' => $request->price,
    //     // ]);

    //     if ($request->hasFile('apartment_image')) {
    //         foreach ($request->file('apartment_image') as $image) {
    //             $imageName = time() . '_' . $image->getClientOriginalName();
    //             $image->storeAs('apartment_images', $imageName, 'public');
    //             $imageLocation = 'apartment_images/' . $imageName;
    //             ApartmentImages::create([
    //                 'apartment_id' => $apartment->id,
    //                 'apartment_image' => $imageLocation
    //             ]);
    //         }
    //     }

    //     return redirect()->route('apartment-management.index')->with('success', 'Apartment Created Successfully');
    // }


    public function store(Request $request)
    {
        try {

            // Validation of input fields
            $request->validate([
                'user_id' => 'nullable|array',
                'exp_managers' => 'nullable|array',
                'commission_type' => 'nullable|string|max:255',
                'commission_value' => 'nullable|numeric',
                'apartment_type' => 'required|string|max:255',
                
                
                'title' => 'required|string|max:255',
                'apartment_num' => 'nullable|string|max:255',
                'is_churned' => 'nullable|boolean',
                'google_map' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'street' => 'nullable|string|max:255',
                'city_name' => 'nullable|string|max:255',
                'address_line' => 'required|string|max:1025',
                'longitude' => 'required|numeric',
                'latitude' => 'required|numeric',
                'postal' => 'nullable|string|max:255',
                'be_listing_name' => 'nullable|string|max:255',
                'property_about' => 'nullable|string',
                'max_guests' => 'required|integer',
                'bedrooms' => 'required|integer',
                'beds' => 'required|integer',
                'bathrooms' => 'required|integer',
                'amenities' => 'required|array',
                'is_allow_pets' => 'nullable|boolean',
                'is_self_check_in' => 'nullable|boolean',
                'living_room' => 'nullable|string',
                'laundry_area' => 'nullable|string',
                'corridor' => 'nullable|string',
                'outdoor_area' => 'nullable|string',
                'kitchen' => 'nullable|string',
                'cleaning_fee' => 'nullable|numeric',
                'discounts' => 'nullable|numeric',
                'tax' => 'nullable|numeric',
                'price' => 'required|numeric',
                'created_by' => 'nullable|exists:users,id',
                //'apartment_image.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
        
            // Get all the request data and cast it as an object
            $data = $request->all();
            $data = (object) $data;
        
            // Get the authenticated user's ID
            $authUser = Auth::user()->id;
        
            // Prepare the apartment details to be saved in the database
            $apartmentDetails = [
                'user_id' => json_encode($data->hosts),
                'exp_managers' => json_encode($data->exp_managers),
                'commission_type' => $data->commission_type,
                'commission_value' => $data->commission_value,
                'apartment_type' => $data->apartment_type,
                'title' => $data->title,
                'apartment_num' => $data->apartment_num,
                'is_churned' => $data->is_churned,
                'google_map' => $data->google_map,
                'district' => $data->district,
                'street' => $data->street,
                'city_name' => $data->city_name,
                'address_line' => $data->address_line,
                'longitude' => $data->longitude,
                'latitude' => $data->latitude,
                'postal' => $data->postal,
                'be_listing_name' => $data->be_listing_name,
                'property_about' => $data->property_about,
                'max_guests' => $data->max_guests,
                'bedrooms' => $data->bedrooms,
                
                'beds' => $data->beds,
                'bathrooms' => $data->bathrooms,
                'amenities' => json_encode($data->amenities),
                'is_allow_pets' => $request->input('is_allow_pets') == 1 ? 1 : 0,
                'is_self_check_in' => $request->input('is_self_check_in') == 1 ? 1 : 0,
                'living_room' => $request->input('living_room') == 1 ? 1 : 0,
                'laundry_area' => $request->input('laundry_area') == 1 ? 1 : 0,
                'corridor' => $request->input('corridor') == 1 ? 1 : 0,
                'outdoor_area' => $request->input('outdoor_area') == 1 ? 1 : 0,
                'kitchen' => $request->input('kitchen') == 1 ? 1 : 0,
                'cleaning_fee' => $data->cleaning_fee,
                'discounts' => $data->discounts,
                'tax' => $data->tax,
                'price' => $data->price,
                'created_by' => $authUser,
                'checkin_time' =>$data->checkin_time,
                'checkout_time' =>$data->checkout_time,
                'cancellation_policy' => 'flexible',
                'minimum_days_stay' => $data->minimum_days_stay,
                'is_long_term' => $data->is_long_term,

            ];
        
            // Create the apartment record in the database
            $apartment = Apartments::create($apartmentDetails);

            //
            $listingData = [
                'user_id' => json_encode($data->hosts),
                'exp_managers' => json_encode($data->exp_managers),

                'commission_type' => $data->commission_type,
                'commission_value' => $data->commission_value,
                'listing_id' => $apartment->id,
                'channel_id' => 0,
                'property_about' => $data->property_about,
                'be_listing_name' => $data->be_listing_name,
                'listing_json' => json_encode(['title' => $data->title,'id' => $apartment->id]),
                'apartment_num' => $data->apartment_num,
                'bedrooms' => $data->bedrooms,
                'beds' => $data->beds,
                'bathrooms' => $data->bathrooms,
                'city_name' => $data->city_name,
                'district' => $data->district,
                'street' => $data->street,
                'property_type' => $data->apartment_type,
                'is_allow_pets' => $request->input('is_allow_pets') == 1 ? 1 : 0,
                'is_self_check_in' => $request->input('is_self_check_in') == 1 ? 1 : 0,
                'living_room' => $request->input('living_room') == 1 ? 1 : 0,
                'laundry_area' => $request->input('laundry_area') == 1 ? 1 : 0,
                'corridor' => $request->input('corridor') == 1 ? 1 : 0,
                'outdoor_area' => $request->input('outdoor_area') == 1 ? 1 : 0,
                'kitchen' => $request->input('kitchen') == 1 ? 1 : 0,

                

                'cleaning_fee' => $data->cleaning_fee,
                'discounts' => $data->discounts,
                'tax' => $data->tax,
                'google_map' => $data->google_map,
                'is_old' => 0,
                'is_churned' => $data->is_churned,
                'is_manual' => 1,
               
                'checkin_time' =>$data->checkin_time,
                'checkout_time' =>$data->checkout_time,
                'cancellation_policy' => 'flexible',
                'is_sync' => 'sync_all',
                'minimum_days_stay' => $data->minimum_days_stay,
                'is_long_term' => $data->is_long_term,
               
            ];

            //create listing
            $listings =  Listings::create($listingData);

            

            $listingId = $listings->listing_id; 
            //dd($listingId);

            $today = Carbon::today();

           // dd($data->price);
            
            $dates = collect(range(0, 499))->map(function ($i) use ($today, $listingId,$data) {
                return [
                    'listing_id' => $listingId,
                    'availability' => 1,
                    'max_stay' => 365,
                    'min_stay_through' => 1,
                    'rate' => $data->price,
                    'calender_date' => $today->copy()->addDays($i)->toDateString(),
                ];
            })->toArray();
            
            Calender::insert($dates);

            ListingSetting::create([
                'listing_id' => $listingId,
                'rate_plan_id' => null,
                'listing_currency' => 'SAR',
                'instant_booking' => 'yes',
                'default_daily_price' => $data->price ?? 0,
                'guests_included' => 2,
                'weekend_price' => null,
                'price_per_extra_person' => null,
                'weekly_price_factor' => null,
                'monthly_price_factor' => null,
                'pass_through_linen_fee' => null,
                'pass_through_security_deposit' => null,
                'pass_through_resort_fee' => null,
                'pass_through_community_fee' => null,
                'pass_through_pet_fee' => null,
                'pass_through_cleaning_fee' => null,
                'pass_through_short_term_cleaning_fee' => null,
                'cleaning_fee' => $data->cleaning_fee ?? null
            ]);


            $selectedAmenities = $data->amenities; 

            $formattedAmenities = [];
            
            foreach ($selectedAmenities as $amenity) {
                $formattedAmenities[$amenity] = [
                    'instruction' => '',
                    'is_present' => true,
                ];
            }
            
            ListingAmenity::create([
                'listing_id' => $listingId,
                'amenities_json' => json_encode($formattedAmenities)
            ]);
            
            // Image upload handling (if images are provided)
            // if ($request->hasFile('apartment_image')) {
            //     foreach ($request->file('apartment_image') as $image) {
            //         $imageName = time() . '_' . $image->getClientOriginalName();
            //         $image->storeAs('apartment_images', $imageName, 'public');
            //         $imageLocation = 'apartment_images/' . $imageName;
            //         ApartmentImages::create([
            //             'apartment_id' => $apartment->id,
            //             'apartment_image' => $imageLocation
            //         ]);
            //     }
            // }

            if ($request->hasFile('cover_image')) {
                $image = $request->file('cover_image');
                $imageName = time() . '_cover_' . $image->getClientOriginalName();
                $image->storeAs('apartment_images', $imageName, 'public');
                $imageLocation = 'apartment_images/' . $imageName;
        
                AirbnbImage::create([
                    'listing_id' => $apartment->id,
                    'url' => url('/storage') . '/' . $imageLocation,
                    'category' => 'cover image',
                ]);
            }


            if ($request->has('room_images')) {
                foreach ($request->room_images as $room => $images) {
                    foreach ($images as $image) {
                        if ($image) {
                            $imageName = time() . '_' . str_replace(' ', '_', strtolower($room)) . '_' . $image->getClientOriginalName();
                            $image->storeAs('apartment_images', $imageName, 'public');
                            $imageLocation = 'apartment_images/' . $imageName;
        
                            AirbnbImage::create([
                                'listing_id' => $apartment->id,
                                'url' => url('/storage') . '/' . $imageLocation,
                                'category' => $room, 
                            ]);
                        }
                    }
                }
            }

            if ($request->hasFile('apartment_image')) {
                foreach ($request->file('apartment_image') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->storeAs('apartment_images', $imageName, 'public');
                    $imageLocation = 'apartment_images/' . $imageName;
                    AirbnbImage::create([
                        'listing_id' => $apartment->id,
                        'url' => url('/storage').'/'.$imageLocation,
                        'category' => 'other images'
                    ]);
                }
            }
    
            // Success response
            return redirect()->route('apartment-management.edit', $apartment->id)->with('success', 'Apartment Created Successfully')->withFragment('images_section');
            //return redirect()->route('apartment-management.index')->with('success', 'Apartment Created Successfully');
        
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error creating apartment: ' . $e->getMessage());
        
            // Return back with an error message
            return back()->withErrors(['error' => 'Something went wrong! Please try again later.']);
        }
    }
    
    
         

    public function edit(Apartments $apartment_management)
    {
        


        try {
           $apartment =  $apartment_management;
    
            // Decode JSON fields
            $apartment->user_id = json_decode($apartment->user_id, true);
            $apartment->exp_managers = json_decode($apartment->exp_managers, true);
           // $apartment->amenities = is_array($apartment->amenities) ? $apartment->amenities : json_decode($apartment->amenities, true);
            //$apartment->any_of_these = json_decode($apartment->any_of_these, true);
           // $apartment->unique_attr = json_decode($apartment->unique_attr, true);
            // $selectedAmenities = $apartment->amenities ?? [];
    
            // Fetch apartment images
            $apartmentImages = AirbnbImage::where('listing_id', $apartment->id)->get();
    
            // Get dropdown/select data
           $users = User::where('role_id', 2)->get(); // hosts
            $experiencemanagers = User::where('role_id', 6)->get();
            $journeySpecialist = User::where('role_id', 5)->get();
           $hostTypes = HostType::all();
            $discounts = Discounts::all();
    
            return view('Admin.apartment-management.edit', compact(
                'apartment',
                'users',
                'experiencemanagers',
                'journeySpecialist',
                'hostTypes',
                'discounts',
                'apartmentImages'
            ));
    
        } catch (\Exception $e) {
            \Log::error('Error loading apartment for edit: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Unable to load apartment. Please try again.']);
        }
    }

    

    public function update(Request $request, $id)
    {
        try {
            // Validation
            $request->validate([
                'user_id' => 'nullable|array',
                'exp_managers' => 'nullable|array',
                'commission_type' => 'nullable|string|max:255',
                'commission_value' => 'nullable|numeric',
                'apartment_type' => 'required|string|max:255',
                'title' => 'required|string|max:255',
                'apartment_num' => 'nullable|string|max:255',
                'is_churned' => 'nullable|boolean',
                'google_map' => 'nullable|string|max:255',
                'district' => 'nullable|string|max:255',
                'street' => 'nullable|string|max:255',
                'city_name' => 'nullable|string|max:255',
                'address_line' => 'required|string|max:1025',
                'longitude' => 'required|numeric',
                'latitude' => 'required|numeric',
                'postal' => 'nullable|string|max:255',
                'be_listing_name' => 'nullable|string|max:255',
                'property_about' => 'nullable|string',
                'max_guests' => 'required|integer',
                'bedrooms' => 'required|integer',
                'beds' => 'required|integer',
                'bathrooms' => 'required|integer',
                'amenities' => 'required|array',
                'is_allow_pets' => 'nullable|boolean',
                'is_self_check_in' => 'nullable|boolean',
                'living_room' => 'nullable|string',
                'laundry_area' => 'nullable|string',
                'corridor' => 'nullable|string',
                'outdoor_area' => 'nullable|string',
                'kitchen' => 'nullable|string',
                'cleaning_fee' => 'nullable|numeric',
                'discounts' => 'nullable|numeric',
                'tax' => 'nullable|numeric',
                'price' => 'required|numeric',
                'created_by' => 'nullable|exists:users,id',
               
            ]);
    
            $data = (object) $request->all();
            $authUser = Auth::user()->id;
    
            $apartment = Apartments::findOrFail($id);
            $apartment->update([
                'user_id' => json_encode($data->hosts),
                'exp_managers' => json_encode($data->exp_managers),
                'commission_type' => $data->commission_type,
                'commission_value' => $data->commission_value,
                'apartment_type' => $data->apartment_type,
                'title' => $data->title,
                'apartment_num' => $data->apartment_num,
                'is_churned' => $data->is_churned,
                'google_map' => $data->google_map,
                'district' => $data->district,
                'street' => $data->street,
                'city_name' => $data->city_name,
                'address_line' => $data->address_line,
                'longitude' => $data->longitude,
                'latitude' => $data->latitude,
                'postal' => $data->postal,
                'be_listing_name' => $data->be_listing_name,
                'property_about' => $data->property_about,
                'max_guests' => $data->max_guests,
                'bedrooms' => $data->bedrooms,
                'beds' => $data->beds,
                'bathrooms' => $data->bathrooms,
                'amenities' => json_encode($data->amenities),
                'is_allow_pets' => $request->input('is_allow_pets') == 1 ? 1 : 0,
                'is_self_check_in' => $request->input('is_self_check_in') == 1 ? 1 : 0,
                'living_room' => $request->input('living_room') == 1 ? 1 : 0,
                'laundry_area' => $request->input('laundry_area') == 1 ? 1 : 0,
                'corridor' => $request->input('corridor') == 1 ? 1 : 0,
                'outdoor_area' => $request->input('outdoor_area') == 1 ? 1 : 0,
                'kitchen' => $request->input('kitchen') == 1 ? 1 : 0,
                'cleaning_fee' => $data->cleaning_fee,
                'discounts' => $data->discounts,
                'tax' => $data->tax,
                'price' => $data->price,
                'created_by' => $authUser,
                'checkin_time' => $data->checkin_time,
                'checkout_time' => $data->checkout_time,
                'cancellation_policy' => 'flexible',
                'minimum_days_stay' => $data->minimum_days_stay,
                'is_long_term' => $data->is_long_term,
            ]);
    
            // Update listing
            $listing = Listings::where('listing_id', $id)->firstOrFail();
            $listing->update([
                'user_id' => json_encode($data->hosts),
                'exp_managers' => json_encode($data->exp_managers),
                'commission_type' => $data->commission_type,
                'commission_value' => $data->commission_value,
                'property_about' => $data->property_about,
                'be_listing_name' => $data->be_listing_name,
                'listing_json' => json_encode(['title' => $data->title,'id' => $apartment->id]),
                'apartment_num' => $data->apartment_num,
                'bedrooms' => $data->bedrooms,
                'beds' => $data->beds,
                'bathrooms' => $data->bathrooms,
                'city_name' => $data->city_name,
                'district' => $data->district,
                'street' => $data->street,
                'property_type' => $data->apartment_type,
                'is_allow_pets' => $request->input('is_allow_pets') == 1 ? 1 : 0,
                'is_self_check_in' => $request->input('is_self_check_in') == 1 ? 1 : 0,
                'living_room' => $request->input('living_room') == 1 ? 1 : 0,
                'laundry_area' => $request->input('laundry_area') == 1 ? 1 : 0,
                'corridor' => $request->input('corridor') == 1 ? 1 : 0,
                'outdoor_area' => $request->input('outdoor_area') == 1 ? 1 : 0,
                'kitchen' => $request->input('kitchen') == 1 ? 1 : 0,
                'cleaning_fee' => $data->cleaning_fee,
                'discounts' => $data->discounts,
                'tax' => $data->tax,
                'google_map' => $data->google_map,
                'is_churned' => $data->is_churned,
                'checkin_time' => $data->checkin_time,
                'checkout_time' => $data->checkout_time,
                'minimum_days_stay' => $data->minimum_days_stay,
                'is_long_term' => $data->is_long_term,
            ]);
    
            // Update calendar
            Calender::where('listing_id', $id)
            ->update(['rate' => $data->price]);
    
            // Update listing settings
            ListingSetting::updateOrCreate(
                ['listing_id' => $id],
                [
                    'rate_plan_id' => null,
                    'listing_currency' => 'SAR',
                    'instant_booking' => 'yes',
                    'default_daily_price' => $data->price ?? 0,
                    'guests_included' => 2,
                    'cleaning_fee' => $data->cleaning_fee ?? null,
                ]
            );
    
            // Update listing amenities
            $formattedAmenities = [];
            foreach ($data->amenities as $amenity) {
                $formattedAmenities[$amenity] = [
                    'instruction' => '',
                    'is_present' => true,
                ];
            }
            ListingAmenity::updateOrCreate(
                ['listing_id' => $id],
                ['amenities_json' => json_encode($formattedAmenities)]
            );
    
            

    
            return redirect()->route('apartment-management.index')->with('success', 'Apartment Updated Successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
    

    public function apartmentImageDelete($id)
    {
        $apartmentImg = AirbnbImage::findorfail($id);
        $fullurl = $apartmentImg->url;
        $filename = Str::after($fullurl, 'storage/');

        //dd($filename);
        
        $db = Storage::delete('public/'.$filename);
        $apartmentImg->delete();
        return redirect()->back();
    }
    
    
    // public function uploadImage(Request $request)
    // {
    //     $request->validate([
    //         'apartment_image' => 'required|image|max:10240', // max 10MB
    //         'listing_id' => 'required|integer',
    //     ]);
    
    //     $image = $request->file('apartment_image');
    //     $listingId = $request->listing_id;
    //     $category = $request->category;
    //     $imageName = time() . '_' . $image->getClientOriginalName();
    //     $imagePath = storage_path('app/public/apartment_images/' . $imageName);
    
    //     // Compress with Intervention
    //     // $compressed = Image::make($image->getRealPath())
    //     //     ->resize(1280, null, function ($constraint) {
    //     //         $constraint->aspectRatio();
    //     //         $constraint->upsize();
    //     //     })
    //     //     ->encode('jpg', 20);

    //     $compressed = Image::make($image->getRealPath())->encode('jpg', 20);
    
    //     // Store
    //     Storage::put('public/apartment_images/' . $imageName, (string) $compressed);
    
    //     // Optimize with Spatie
    //     // $optimizerChain = OptimizerChainFactory::create();
    //     // $optimizerChain->optimize($imagePath);
    
    //     // Save to DB
    //     $airbnbImage = AirbnbImage::create([
    //         'listing_id' => $listingId,
    //         'url' => url('/storage/apartment_images/' . $imageName),
    //         'category' => $category,
    //     ]);
    
    //     return response()->json(['image_url' => url('/storage/apartment_images/' . $imageName),'id' => $airbnbImage->id, 'category' => $airbnbImage->category]);
    // }
    
      
public function uploadImage(Request $request)
{
    $request->validate([
        'apartment_image' => 'required|image|max:10240', 
        'listing_id' => 'required|integer',
    ]);

    try {
        $image = $request->file('apartment_image');
        $listingId = $request->listing_id;
        $category = $request->category ?? 'Other images'; 
        $imageName = time() . '_' . preg_replace('/\s+/', '_', $image->getClientOriginalName());
        $image->storeAs('apartment_images', $imageName, 'public');
        $imageLocation = 'apartment_images/' . $imageName;
       
      
       $airbnbImage = AirbnbImage::create([
            'listing_id' => $listingId,
            'url' => url('/storage') . '/' . $imageLocation,
            'category' => $category,
        ]);

        
        
        return response()->json(['image_url' => url('/storage/apartment_images/' . $imageName),'id' => $airbnbImage->id, 'category' => $airbnbImage->category]);

    } catch (\Exception $e) {
       
        
        \Log::error('Image upload error: ' . $e->getMessage());
    \Log::error($e->getTraceAsString()); 

    return response()->json([
        'error' => 'Upload failed: ' . $e->getMessage(),
    ], 500);
    }
}    
    
       


}
 
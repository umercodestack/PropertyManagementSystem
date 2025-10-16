<?php

namespace App\Http\Controllers\Admin\HostOnBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hostaboard;
use App\Models\HostaboardOwnershipDocument;
use App\Models\HostaboardOwnerDocument;
use App\Models\User;
use App\Models\DeepCleaning;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationM;
use App\Models\RevenueActivationAudit;
use App\Models\Audit;
use App\Models\HostRentalLease;
use Illuminate\Support\Facades\DB;

class HostaboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission');
    }
    
    public function index()
    {
 
        //->whereDate('created_at','>=','2025-06-01')->where('is_old','0')
        $hostsonboard = Hostaboard::with(['ownershipDocuments', 'ownerDocuments','accountManager','userdetail','host'])->orderBy('id', 'desc')->get();
         
        return view('Admin.Host-OnBoard.index', [
            'hostsonboard' => $hostsonboard,
            
        ]);
    }

    public function create()
    {
        $propertyManagers = User::where('role_id', '!=', 2)->get();
        return view('Admin.Host-OnBoard.create',['propertyManagers' => $propertyManagers]);
    }

    public function store(Request $request)
    {
   
        $request->validate([
        'host_id' => 'required|string',
        'property_id' => 'required|string|unique:hostaboard,property_id',
        'property_manager' => 'required|integer',
        'owner_name' => 'required|string|max:250',
        'city_name' => 'required|string|max:250',
        'type' => 'required|string|max:150',
        'unit_type' => 'required|string|max:150',
        'unit_number' => 'required|string|max:150',
        'floor' => 'required|string|max:150',
        'contract_file' => 'required|file|mimes:pdf,jpeg,jpg,png|max:10240', 
        'host_bank_detail' => 'required|string|max:500',
        'existing_ota_links' => 'nullable|string',
        'property_address' => 'required|string',
        'property_google_map_link' => 'required|string',
        'property_images_link' => 'nullable|string',
        'door_locks_mechanism' => 'required|string|max:500',
        'door_lock_code' => 'nullable|string|max:150',
        'wi_fi_password' => 'nullable|string|max:150',
        'amenities' => 'required|array',  // Ensuring it's an array
        'amenities.*' => 'required|string',  // Each amenity is a string 
        'ownership_documents' => 'required|array|min:1', // Ensure at least one file is present
        'ownership_documents.*' => 'file|mimes:jpeg,jpg,png,pdf|max:10240',
        'owner_document' => 'required|array|min:1', // Ensure at least one file is present
        'owner_document.*' => 'file|mimes:jpeg,jpg,png,pdf|max:10240',
        'host_number' => 'required|string',
        'building_Caretaker_name' => 'nullable|string',
        'building_Caretaker_Number' => 'nullable|string',
        'user_id' => 'nullable|integer',
        'title' => 'nullable|string',
        'last_name' => 'required|string',
        'host_email' => 'required|string',
        'bank_name' => 'required|string',
        'iban_no' => 'required|string',
        'swift_code' => 'required|string',
        'postal_code' => 'required|string',
        'be_listing_name' => 'nullable|string',
        'property_about' => 'nullable|string',
        'bedrooms' => 'nullable|integer',
        'beds' => 'nullable|integer',
        'bathrooms' => 'nullable|integer',
        'district' => 'nullable|string',
        'street' => 'nullable|string',
        'is_allow_pets' => 'nullable|boolean',
        'is_self_check_in' => 'nullable|boolean',
        'living_room' => 'nullable|boolean',
        'laundry_area' => 'nullable|boolean',
        'corridor' => 'nullable|boolean',
        'outdoor_area' => 'nullable|boolean',
        'kitchen' => 'nullable|boolean',
        'host_rental_lease' => 'required',


        ]);

   
    

    

  
        $amenities = $request->input('amenities') ? implode(',', $request->input('amenities')) : null;
   
   

        $contractFilePath = null;
        if ($request->hasFile('contract_file')) {
        $contractFilePath = $request->file('contract_file')->store('contract_files', 'public');
        }

        $utiltiy_billsPath = null;
        if ($request->hasFile('utiltiy_bills')) {
            $utiltiy_billsPath = $request->file('utiltiy_bills')->store('utiltiy_bills', 'public');
        }

        $licence_docPath = null;
        if ($request->hasFile('licence_doc')) {
            $licence_docPath = $request->file('licence_doc')->store('licence_doc', 'public');
        }

        $lenght_type_documentPath = null;
        if ($request->hasFile('lenght_type_document')) {
            $lenght_type_documentPath = $request->file('lenght_type_document')->store('lenght_type_document', 'public');
        }

        $spl_national_addressPath = null;
        if ($request->hasFile('spl_national_address')) {
            $spl_national_addressPath = $request->file('spl_national_address')->store('spl_national_address', 'public');
        }

        $existing_property_obligationsPath = null;
        if ($request->hasFile('existing_property_obligations')) {
            $existing_property_obligationsPath = $request->file('existing_property_obligations')->store('existing_property_obligations', 'public');
        }

        $hostaboard = new Hostaboard();
        $hostaboard->host_id = $request->input('host_id');
        $hostaboard->property_id = $request->input('property_id');
    
        $hostaboard->account_manager_id = $request->input('property_manager');

        $hostaboard->owner_name = $request->input('owner_name');
        $hostaboard->city_name = $request->input('city_name');
        $hostaboard->type = $request->input('type');
        $hostaboard->unit_type = $request->input('unit_type');
        $hostaboard->unit_number = $request->input('unit_number');
        $hostaboard->floor = $request->input('floor');
        $hostaboard->location = $request->input('location');
        $hostaboard->contract_file = $contractFilePath;
        $hostaboard->host_bank_detail = $request->input('host_bank_detail');
        $hostaboard->existing_ota_links = $request->input('existing_ota_links');
        $hostaboard->property_address = $request->input('property_address');
        $hostaboard->property_google_map_link = $request->input('property_google_map_link');
        $hostaboard->is_photo_exists = $request->has('is_photo_exists') ? 1 : 0;
        $hostaboard->property_images_link = $request->input('property_images_link');
        $hostaboard->door_locks_mechanism = $request->input('door_locks_mechanism');
        $hostaboard->door_lock_code = $request->input('door_lock_code');
        $hostaboard->wi_fi_password = $request->input('wi_fi_password');
        $hostaboard->amenities = $amenities; 
    
        $hostaboard->host_number = $request->input('host_number');
        $hostaboard->building_Caretaker_name = $request->input('building_Caretaker_name');
        $hostaboard->building_Caretaker_Number = $request->input('building_Caretaker_Number');
  
        $hostaboard->title = $request->input('title');
   
        $hostaboard->user_id = Auth::user()->id;
        $hostaboard->last_name = $request->input('last_name');
        $hostaboard->host_email = $request->input('host_email'); 

        $hostaboard->bank_name = $request->input('bank_name');
        $hostaboard->iban_no = $request->input('iban_no'); 
        $hostaboard->swift_code = $request->input('swift_code');
        $hostaboard->postal_code = $request->input('postal_code');  
    
        $hostaboard->be_listing_name = $request->input('be_listing_name');
        $hostaboard->property_about = $request->input('property_about'); 
        $hostaboard->bedrooms = $request->input('bedrooms');
        $hostaboard->beds = $request->input('beds');  

        $hostaboard->bathrooms = $request->input('bathrooms');
        $hostaboard->district = $request->input('district'); 
        $hostaboard->street = $request->input('street');
        $hostaboard->is_allow_pets = $request->has('is_allow_pets') ? 1 : 0;
        $hostaboard->is_self_check_in = $request->has('is_self_check_in') ? 1 : 0;
    
        $hostaboard->living_room = $request->has('living_room') ? 1 : 0;
        $hostaboard->laundry_area = $request->has('laundry_area') ? 1 : 0;

        $hostaboard->corridor = $request->has('corridor') ? 1 : 0;
        $hostaboard->outdoor_area = $request->has('outdoor_area') ? 1 : 0;
        $hostaboard->kitchen = $request->has('kitchen') ? 1 : 0; 

        $hostaboard->discounts = $request->input('discounts');
        $hostaboard->tax = $request->input('tax'); 
        $hostaboard->cleaning_fee = $request->input('cleaning_fee');

        $hostaboard->block_dates_via_host_app = $request->input('block_dates_via_host_app');
        $hostaboard->ota_charges_on_direct_booking = $request->input('ota_charges_on_direct_booking');
        $hostaboard->cleaning_fee_on_direct_booking = $request->input('cleaning_fee_on_direct_booking');
        $hostaboard->fixed_cleaning_fee = $request->input('fixed_cleaning_fee');
        $hostaboard->fixed_cleaning_fee_amount = $request->input('fixed_cleaning_fee_amount');
        $hostaboard->co_hosting_account = $request->input('co_hosting_account');
        $hostaboard->length_type = $request->input('length_type');
        $hostaboard->min_days_for_ltr = $request->input('min_days_for_ltr');
        $hostaboard->services = $request->input('services');
        $hostaboard->livedin_share_after_discount = $request->input('livedin_share_after_discount');
        $hostaboard->share_percentage = $request->input('share_percentage');


        $hostaboard->utiltiy_bills = $utiltiy_billsPath;
        $hostaboard->licence_doc = $licence_docPath;
        $hostaboard->deep_cleaning_required = $request->has('deep_cleaning_required') ? 1 : 0;
        $hostaboard->airbnb_email = $request->input('airbnb_email');
        $hostaboard->airbnb_password = $request->input('airbnb_password');

        $hostaboard->lenght_type_document = $lenght_type_documentPath;
        $hostaboard->national_address_document = $spl_national_addressPath;

        $hostaboard->host_rental_lease = $request->input('host_rental_lease');
        
        $hostaboard->building_type = $request->input('building_type');
        $hostaboard->floor_number = $request->input('floor_number');

        $hostaboard->host_exclusivity = $request->input('host_exclusivity');
        $hostaboard->cleaning_done_by_livedin = $request->has('cleaning_done_by_livedin') ? 1 : 0;


        $hostaboard->type_of_ownership_document = $request->input('type_of_ownership_document');


        $hostaboard->ownership_document_number_and_type = $request->input('ownership_document_number_and_type');
        $hostaboard->date_of_birth = $request->input('date_of_birth');
        $hostaboard->building_number = $request->input('building_number');
        $hostaboard->additional_number = $request->input('additional_number');
        $hostaboard->property_area = $request->input('property_area');
        $hostaboard->property_services = $request->input('property_services');
        $hostaboard->property_age = $request->input('property_age');
        $hostaboard->room_number = $request->input('room_number');
        $hostaboard->existing_property_obligations = $existing_property_obligationsPath;


        $hostaboard->save();


        // Process ownership documents if present
        if ($request->hasFile('ownership_documents')) {
        foreach ($request->file('ownership_documents') as $document) {
            $documentPath = $document->store('ownership_documents', 'public');

            // Save each document in the `hostaboard_ownership_documents` table
            HostaboardOwnershipDocument::create([
                'hostaboard_id' => $hostaboard->id,
                'document_type' => 'Ownership/Rental Document', // Optional: Customize this as needed
                'document_path' => $documentPath,
            ]);
        }
        }

        if ($request->hasFile('owner_document')) {
        foreach ($request->file('owner_document') as $document) {
            $documentPath = $document->store('owner_document', 'public');

                // Save each document in the `hostaboard_ownership_documents` table
                HostaboardOwnerDocument::create([
                    'hostaboard_id' => $hostaboard->id,
                    'document_type' => 'National-Id', // Optional: Customize this as needed
                    'document_path' => $documentPath,
                ]);
            }
        }


        $lease = HostRentalLease::create([
            'hostaboard_id' => $hostaboard->id,
            'status' => 'pending'
        ]);

          $audit =  Audit::create([
            'audit_date' => now(),
            'host_activation_id' => $hostaboard->id,
            'user_id' => Auth::id(), 
            'owner_name' => $hostaboard->owner_name,
            'host_number' => $hostaboard->host_number,
            'listing_title' => $hostaboard->title,
            'unit_number' => $hostaboard->unit_number,
            'floor' => $hostaboard->floor,
            'type' => $hostaboard->type,
            'unit_type' => $hostaboard->unit_type,
            'property_address' => $hostaboard->property_address,
            'location' => $hostaboard->property_google_map_link,
            'key_code' => $hostaboard->door_lock_code,
            'status' => 'pending'
        ]);

        $checklist = DB::select("CALL insert_audit_checklist($audit->id);");

        
        if($request->filled('deep_cleaning_required') && $request->deep_cleaning_required == 1)
        {
            $deepCleaning = DeepCleaning::updateOrCreate(
                    ['host_activation_id' => $hostaboard->id], // Search criteria
                        [
                         'listing_title' => $hostaboard->title,
                         'listing_id' => null,
                         'host_id' => null,
                         'host_name' => $hostaboard->owner_name,
                         'host_phone' => $hostaboard->host_number,
                         'poc' => null,
                         'poc_name' => null,
                         'audit_id' => $audit->id,
       
                        'assignToVendor' => 0,
                        'assignToPropertyManager' => 0,
                        'start_date' => null,
                        'end_date' => null,
                        'cleaning_date' => now()->toDateString(),
                        'location' => $hostaboard->property_google_map_link,
                        'key_code' => $hostaboard->door_lock_code,
        
                        'status' => 'pending', 
                        'remarks' => null,
                        'host_activation_id' => $hostaboard->id,
                        'type' => $hostaboard->type,
                        'floor' => $hostaboard->floor,
                        'unit_type' => $hostaboard->unit_type,
                        'unit_number' => $hostaboard->unit_number, 
                    ]);
        } 


        $taskStatus = $request->input('is_photo_exists') == 1 ? 'No Required' : 'Generated';

        $revenueactivationaudit = RevenueActivationAudit::create([
            'hostaboard_id' => $hostaboard->id,
            'status' => 'Pending',
            'task_status' => $taskStatus
        ]);


         


        if ($request->input('is_photo_exists') == 1) {
            notifyheader(5,'Revenue Photo Review',$revenueactivationaudit->id,"Review Photos for {$hostaboard->title} Property Activation", "Review and approve the property images to continue the activation process", url("/revenue-activation-audit/{$revenueactivationaudit->id}/edit"), false );
        }

        return redirect()->route('hostaboard.index')->with('success', 'Hostaboard created successfully');
    }


    public function edit(hostaboard $hostaboard)
    {
        $hostaboard->load('owner_documents');
        $hostaboard->load('ownership_documents');


        $propertyManagers = User::where('role_id', '!=', 2)->get();
        $hostaboard->type_of_ownership_document = $hostaboard->type_of_ownership_document ?? [];

        //dd($hostaboard);
        
        return view('Admin.Host-OnBoard.edit', ['hostaboard' => $hostaboard, 'propertyManagers' => $propertyManagers]);
    }

    public function update(Request $request, $id)
    {

       //dd($request->all());
        $request->validate([
            'host_id' => 'required|string',
            'property_id' => 'required|string',
            'property_manager' => 'nullable|integer',
            'owner_name' => 'required|string|max:250',
            'city_name' => 'required|string|max:250',
            'type' => 'required|string|max:150',
            'unit_type' => 'required|string|max:150',
            'unit_number' => 'required|string|max:150',
            'floor' => 'required|string|max:150',
            'location' => 'nullable|string|max:150',
            'contract_file' => 'nullable|file|mimes:pdf,jpeg,jpg,png|max:10240', 
            'host_bank_detail' => 'required|string|max:500',
            'existing_ota_links' => 'nullable|string',
            'property_address' => 'required|string',
            'property_google_map_link' => 'required|string',
            'property_images_link' => 'nullable|string',
            'door_locks_mechanism' => 'required|string|max:500',
            'door_lock_code' => 'nullable|string|max:150',
            'wi_fi_password' => 'nullable|string|max:150',
            'amenities' => 'required|array',  // Ensuring it's an array
            'amenities.*' => 'required|string',  // Each amenity is a string 
           
    
            'ownership_documents' => 'nullable|array|min:1', // Ensure at least one file is present
            'ownership_documents.*' => 'file|mimes:jpeg,jpg,png,pdf|max:10240',
            'owner_document' => 'nullable|array|min:1', // Ensure at least one file is present
            'owner_document.*' => 'file|mimes:jpeg,jpg,png,pdf|max:10240',
    
            'host_number' => 'required|string',
            'building_Caretaker_name' => 'nullable|string',
            'building_Caretaker_Number' => 'nullable|string',
           
            'user_id' => 'nullable|integer',
            'title' => 'nullable|string',
            'last_name' => 'required|string',
            'host_email' => 'required|string',

            'bank_name' => 'required|string',
            'iban_no' => 'required|string',
            'swift_code' => 'required|string',
            'postal_code' => 'required|string',
            
            
            'be_listing_name' => 'nullable|string',
            'property_about' => 'nullable|string',
            'bedrooms' => 'nullable|integer',
            'beds' => 'nullable|integer',
            'bathrooms' => 'nullable|integer',
            'district' => 'nullable|string',
            'street' => 'nullable|string',
            'is_allow_pets' => 'nullable|boolean',
            'is_self_check_in' => 'nullable|boolean',
            
            'living_room' => 'nullable|boolean',
            'laundry_area' => 'nullable|boolean',
            'corridor' => 'nullable|boolean',
            'outdoor_area' => 'nullable|boolean',
            'kitchen' => 'nullable|boolean',
            'host_rental_lease' => 'required',
            
        ]);

    
        $amenities = $request->input('amenities') ? implode(',', $request->input('amenities')) : null;

        $hostaboard = Hostaboard::findOrFail($id);

  
        $data = $request->except(['contract_file', 'amenities']);
        $data['amenities'] = $amenities;  

        $data['type_of_ownership_document'] = $request->input('type_of_ownership_document');
    
        $data['is_allow_pets'] = $request->has('is_allow_pets') ? 1 : 0;
        $data['is_self_check_in'] = $request->has('is_self_check_in') ? 1 : 0;

        $data['living_room'] = $request->has('living_room') ? 1 : 0;
        $data['laundry_area'] = $request->has('laundry_area') ? 1 : 0;
        $data['corridor'] = $request->has('corridor') ? 1 : 0;
        $data['outdoor_area'] = $request->has('outdoor_area') ? 1 : 0;
        $data['kitchen'] = $request->has('kitchen') ? 1 : 0;
        
        $data['deep_cleaning_required'] = $request->has('deep_cleaning_required') ? 1 : 0;
        $data['is_photo_exists'] = $request->input('is_photo_exists') == '1' ? 1 : 0;

        $data['cleaning_done_by_livedin'] = $request->input('cleaning_done_by_livedin') == '1' ? 1 : 0;

        $data['host_rental_lease'] = $request->has('host_rental_lease') ? $request->host_rental_lease : null;

        

        $hostaboard->update($data);

    
        if ($request->hasFile('contract_file')) {
            $filePath = $request->file('contract_file')->store('contract_files', 'public');
            $hostaboard->contract_file = $filePath;
            $hostaboard->save();
        }

        if ($request->hasFile('utiltiy_bills')) {
            
            $filePath = $request->file('utiltiy_bills')->store('utiltiy_bills', 'public');
            $hostaboard->utiltiy_bills = $filePath;
            $hostaboard->save();
        }

        if ($request->hasFile('licence_doc')) {
            
            $filePath = $request->file('licence_doc')->store('licence_doc', 'public');
            $hostaboard->licence_doc = $filePath;
            $hostaboard->save();
        }

        if ($request->hasFile('lenght_type_document')) {
            
            $filePath = $request->file('lenght_type_document')->store('lenght_type_document', 'public');
            $hostaboard->lenght_type_document = $filePath;
            $hostaboard->save();
        }

        if ($request->hasFile('spl_national_address')) {
            
            $filePath = $request->file('spl_national_address')->store('spl_national_address', 'public');
            $hostaboard->national_address_document = $filePath;
            $hostaboard->save();
        }

        if ($request->hasFile('existing_property_obligations')) {
            
            $filePath = $request->file('existing_property_obligations')->store('existing_property_obligations', 'public');
            $hostaboard->existing_property_obligations = $filePath;
            $hostaboard->save();
        }

        
        if ($request->hasFile('ownership_documents')) {
            foreach ($request->file('ownership_documents') as $document) {
            $documentPath = $document->store('ownership_documents', 'public');

            // Save each document in the `hostaboard_ownership_documents` table
            HostaboardOwnershipDocument::create([
                'hostaboard_id' => $hostaboard->id,
                'document_type' => 'Ownership/Rental Document',
                'document_path' => $documentPath,
                ]);
            }
        }

        if ($request->hasFile('owner_document')) {
        foreach ($request->file('owner_document') as $document) {
            $documentPath = $document->store('owner_document', 'public');

            // Save each document in the `hostaboard_ownership_documents` table
            HostaboardOwnerDocument::create([
                'hostaboard_id' => $hostaboard->id,
                'document_type' => 'National-Id', // Optional: Customize this as needed
                'document_path' => $documentPath,
            ]);
        }
        }

       

        return redirect()->route('hostaboard.index')->with('success', 'Hostaboard updated successfully!');
    }

    public function destroy($id)
    {
        $document = HostaboardOwnershipDocument::findOrFail($id);
    
        // Optionally delete the document file from storage
        // if (Storage::exists($document->document_path)) {
        //     Storage::delete($document->document_path);
        // }
    
       $document->delete();

   
        return true;
    }

    public function destroyownerdocument($id)
    {
        $document = HostaboardOwnerDocument::findOrFail($id);
      
        // Optionally delete the document file from storage
        // if (Storage::exists($document->document_path)) {
        //     Storage::delete($document->document_path);
        // }
    
       $document->delete();

       return true;
    }

}

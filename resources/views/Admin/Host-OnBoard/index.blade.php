@extends('Admin.layouts.app')
@section('content')



<div class="nk-block-head nk-block-head-sm">
        <div class="nk-block-between">
            <div class="nk-block-head-content">
                <h3 class="nk-block-title page-title">Activation Form</h3>
                <div class="nk-block-des text-soft">
                    <p>You have total {{count($hostsonboard)}} Activation Form.</p>
                </div>
            </div><!-- .nk-block-head-content -->
            <div class="nk-block-head-content">
                <div class="toggle-wrap nk-block-tools-toggle">
                    <a href="#" class="btn btn-icon btn-trigger toggle-expand me-n1" data-target="pageMenu"><em class="icon ni ni-menu-alt-r"></em></a>
                    <div class="toggle-expand-content" data-content="pageMenu">
                        <ul class="nk-block-tools g-3">
                            <li class="nk-block-tools-opt">
                                <div class="drodown">
                                    <a href="{{route('hostaboard.create')}}" class="btn btn-icon btn-primary"><em class="icon ni ni-plus"></em></a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card card-bordered card-preview">
    <div class="card-inner">
    <table class="datatable-init-export nowrap table" data-export-title="Export">
    <thead>
        <tr>
       
            <th>S.No</th>
            <th>Host Id</th>
            <th>Property Id</th>
            <th>Title</th>
            <th>Account Manager</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Number</th>
            <th>Email</th>
            
            <th>Be Name</th>
            <th>About Property</th>
            <th>Bedrooms</th>
            <th>Beds</th>
            <th>Bathrooms</th>
            <th>district</th>
            <th>Street</th>
            
            
            <th>City Name</th>
            <th>Type</th>
            <th>Unit Type</th>
            <th>Unit Number</th>
            <th>Floor</th>
            
            <th>Allow Pets</th>
            <th>Self CheckIn</th>
            
            <th>Living Room</th>
            <th>Laundry Area</th>

            <th>Corridor</th>
            <th>Outdoor Area</th>

            <th>Kitchen</th>
            
            <th>Discount</th>
            <th>Tax</th>
            <th>Cleaning Fee</th>
            
            <th>Location</th>
            <th>Contract File</th>
            
            <th>Account Title</th>
            <th>Bank Name</th>
            <th>Iban No</th>
            <th>Swift Code</th>
            

            <th>Existing OTA Links</th>
            <th>Postal Address</th>
            <th>Postal Code</th>
            <th>Property Google Map Link</th>
            <th>Building Caretaker Name</th>
            <th>Building Caretaker Number</th>

            <th>Property Images Link</th>
            <th>Door Locks Mechanism</th>
            <th>Door Lock Code</th>
            <th>Wi-Fi Password</th>
            <th>Amenities</th>
            <th>Ownership Documents</th>
            <th>Owner Documents</th>
            <th>User Name</th>
            <th>Actions</th> <!-- Add action buttons for View/Edit/Delete -->
           
        </tr>
    </thead>
    <tbody>
        @foreach($hostsonboard as $key => $item)
            <tr>
            
                <td>{{ ++$key }}</td>
                <td>{{ $item->host_id }}</td>
                <td>{{ $item->property_id }}</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->accountManager ? $item->accountManager->name : 'N/A' }}</td>
                <td>{{ $item->owner_name }}</td>
                <td>{{ $item->last_name }}</td>
                <td>{{ $item->host_number }}</td>
                <td>{{ $item->host_email}}</td>
                
                <td>{{ $item->be_listing_name }} </td>
                <td>{{ $item->property_about }}</td>
                <td>{{ $item->bedrooms }} </td>
                <td>{{ $item->beds }}</td>
                <td>{{ $item->bathrooms }}</td>
                <td>{{ $item->district }}</td>
                <td>{{ $item->street }}</td>
                
                <td>{{ $item->city_name }}</td>
                <td>{{ $item->type }}</td>
                <td>{{ $item->unit_type }}</td>
                <td>{{ $item->unit_number }}</td>
                <td>{{ $item->floor }}</td>
                
                <td>{{ $item->is_allow_pets ? 'Yes' : 'No' }}</td>
                <td>{{ $item->is_self_check_in ? 'Yes' : 'No' }}</td>
                <td>{{ $item->living_room ? 'Yes' : 'No' }}</td>
                <td>{{ $item->laundry_area ? 'Yes' : 'No' }}</td>
                <td>{{ $item->corridor ? 'Yes' : 'No' }}</td>
                <td>{{ $item->outdoor_area ? 'Yes' : 'No' }}</td>
                <td>{{ $item->kitchen ? 'Yes' : 'No' }}</td>
                
                <td>{{ $item->discounts }}</td>
                <td>{{ $item->tax }}</td>
                <td>{{ $item->cleaning_fee }}</td>
                
                <td>{{ $item->location }}</td>
                <td>
                    @if($item->contract_file)
                        <a href="{{ asset('storage/' . $item->contract_file) }}" target="_blank">View Contract</a>
                    @else
                        N/A
                    @endif
                </td>
                <td>{{ $item->host_bank_detail }}</td>

                <td>{{ $item->bank_name }}</td>
                <td>{{ $item->iban_no }}</td>
                <td>{{ $item->swift_code }}</td>
               

                <td>{{ $item->existing_ota_links }}</td>
                <td>{{ $item->property_address }}</td>
                <td>{{ $item->postal_code }}</td>
                <td>{{ $item->property_google_map_link }}</td>
                <td>{{ $item->building_Caretaker_name }}</td>
                <td>{{ $item->building_Caretaker_number }}</td>
                <td>{{ $item->property_images_link }}</td>
                <td>{{ $item->door_locks_mechanism }}</td>
                <td>{{ $item->door_lock_code }}</td>
                <td>{{ $item->wi_fi_password }}</td>
                <td>
                    @if($item->amenities)
                        @foreach(explode(',', $item->amenities) as $amenity)
                            <span class="badge bg-info"  style="margin:0 3px !important;">{{ $amenity }}</span>
                        @endforeach
                    @else
                        N/A
                    @endif
                </td>
                <td>
    @if($item->ownershipDocuments->isNotEmpty())
        @foreach($item->ownershipDocuments as $document)
            <span class="badge bg-info"  style="margin:0 3px !important;">
                <a href="{{ Storage::url($document->document_path) }}" target="_blank" class="text-info" style="color:white !important; ">
                    <!-- {{ $document->document_type ?? 'Document' }} -->

                    View Document {{ $loop->iteration }}
                </a>
            </span>

            <!-- <form id="deleteForm-{{ $document->id }}" action="{{ route('document.destroy', $document->id) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    
   
    <i class="fa fa-trash" onclick="if (confirm('Are you sure you want to delete this document?')) { document.getElementById('deleteForm-{{ $document->id }}').submit(); }" style="cursor: pointer;" ></i>
</form> -->


        @endforeach
    @else
        N/A
    @endif
</td>

<td>
    @if($item->ownerDocuments->isNotEmpty())
        @foreach($item->ownerDocuments as $document)
            <span class="badge bg-info" style="margin:0 3px !important;">
                <a href="{{ Storage::url($document->document_path) }}" target="_blank" class="text-info" style="color:white !important; margin:0 5px">
                    <!-- {{ $document->document_type ?? 'Document' }} -->
                    View Document {{ $loop->iteration }}
                </a>
            </span>

            <!-- <form id="deleteForm2-{{ $document->id }}" action="{{ route('document.destroyownerdocument', $document->id) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')
    

    <i class="fa fa-trash" onclick="if (confirm('Are you sure you want to delete this document?')) { document.getElementById('deleteForm2-{{ $document->id }}').submit(); }" style="cursor: pointer;" ></i>
</form> -->


        @endforeach
    @else
        N/A
    @endif
</td>

<td>{{ $item->userdetail ? $item->userdetail->name : 'N/A' }}</td>

                <td >
                    <!-- Add action buttons for View, Edit, and Delete -->
                     <div class="" style="display:flex;">

                    <a href="{{ route('hostaboard.edit', $item->id) }}" class="btn btn-warning btn-sm" style="margin:0px 5px;">Edit</a>
                    <!-- <form action="{{ route('hostaboard.destroy', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form> -->

                    <!-- Create Host button -->
    <form action="{{ route('user-management.create') }}" method="GET" style="display:inline; margin:0px 5px;">
      
        <input type="hidden" name="hostactivation_id" value="{{ $item->id }}">
        <input type="hidden" name="owner_name" value="{{ $item->owner_name }}">
        <input type="hidden" name="last_name" value="{{ $item->last_name }}">
        
        <input type="hidden" name="host_number" value="{{ $item->host_number }}">
        <input type="hidden" name="host_email" value="{{ $item->host_email }}">

    @php
        $userWithHostActivation = \App\Models\User::where('host_activation_id', $item->id)->first();
    @endphp

    <button type="submit" class="btn btn-success btn-sm text-white" 
        @if($userWithHostActivation) disabled @endif>
        Create Host
    </button>


    </form>


    <form action="{{ route('channel-management.create') }}" method="GET" style="display:inline; margin:0px 5px;">
      
    <input type="hidden" name="host_user_id" value="{{ $item->host ? $item->host->id : 'N/A' }}">

  @php
      $userWithHostActivation = \App\Models\User::where('host_activation_id', $item->id)->first();
  @endphp

  <button type="submit" class="btn btn-success btn-sm text-white" 
      @if(!$userWithHostActivation) disabled @endif>
      Map Listing
  </button>


  </form>

    <form action="{{ route('audit-management.create') }}" method="GET" style="margin:0px 5px;">
                <input type="hidden" name="hostaboard_id" value="{{ $item->id }}" >
                
                
                <input type="hidden" name="accountManager_id" value="{{ $item->accountManager->id }}">
                <input type="hidden" name="owner_name" value="{{ $item->owner_name }}">
                <input type="hidden" name="host_number" value="{{ $item->host_number }}">
                <input type="hidden" name="title" value="{{ $item->title }}">
                
                <input type="hidden" name="unit_number" value="{{ $item->unit_number }}">
                <input type="hidden" name="floor" value="{{ $item->floor }}">

                <input type="hidden" name="type" value="{{ $item->type }}">
                <input type="hidden" name="unit_type" value="{{ $item->unit_type }}">
                
                <input type="hidden" name="property_address" value="{{ $item->property_address }}">
                <input type="hidden" name="property_google_map_link" value="{{ $item->property_google_map_link }}">


                @php
    $auditWithHostActivation = \App\Models\Audit::where('host_activation_id', $item->id)->first();
@endphp

<!--<button type="submit" class="btn btn-primary btn-sm text-white" -->
<!--    @if($auditWithHostActivation) disabled @endif>-->
<!--    Start Audit Task-->
<!--</button>-->
                
            
            
            </form>


            <form action="{{ route('deep-cleaning-management.create') }}" method="GET" style="margin:0px 5px;">
                <input type="hidden" name="hostaboard_id" value="{{ $item->id }}">
                
                
                <input type="hidden" name="accountManager_id" value="{{ $item->accountManager->id }}">
                <input type="hidden" name="owner_name" value="{{ $item->owner_name }}">
                <input type="hidden" name="host_number" value="{{ $item->host_number }}">
                <input type="hidden" name="title" value="{{ $item->title }}">
                <input type="hidden" name="unit_number" value="{{ $item->unit_number }}">
                <input type="hidden" name="floor" value="{{ $item->floor }}">
                <input type="hidden" name="type" value="{{ $item->type }}">
                <input type="hidden" name="unit_type" value="{{ $item->unit_type }}">


                <input type="hidden" name="property_address" value="{{ $item->property_address }}">
                <input type="hidden" name="property_google_map_link" value="{{ $item->property_google_map_link }}">
               
                @php
    $auditWithHostActivation = \App\Models\Audit::where('host_activation_id', $item->id)->first();
     $deepCleaningWithHostActivation = \App\Models\DeepCleaning::where('host_activation_id', $item->id)->first();
@endphp

<button type="submit" class="btn btn-primary btn-sm text-white" 
    @if(!$auditWithHostActivation || $deepCleaningWithHostActivation) disabled @endif>
    Deep Cleaning
</button>

            </form>        
            </div>




                </td>

                
            </tr>
        @endforeach
    </tbody>
</table>

    </div>
</div>
@endsection

@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Create Deep Cleaning Task</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('deep-cleaning-management.store') }}" method="POST">
                        @csrf

                        @if (isset($_GET['audit_id']))
                            @php
                                $audit = \App\Models\Audit::where('id', $_GET['audit_id'])->first();
                                // dd($audits);
                            @endphp
                        @endif
                        <div class="row gy-4">
                            
                        <div class="form-group" style="display:none;">
        <label for="host_activation_id">Host Activation ID</label>
        <input type="text" class="form-control" id="host_activation_id" name="host_activation_id" 
               value="{{ old('host_activation_id', $host_activation_id) }}">
    </div>


    <div class="col-md-6" >
                                <div class="form-group">
                                    <label class="form-label" for="listing_id">Apartment</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="listing_id" id = "listing_id"
                                            data-placeholder="Select Apartment">
                                            <option value="" selected disabled>Select Apartment</option>
                                            @foreach ($listings as $items)
                                                @php
                                                    $listing_json = json_decode($items->listing_json);
                                                @endphp
                                                <option value="{{ $items->id }}"
                                                    {{ isset($audit) && $audit->listing_id === $items->id ? 'selected' : '' }}>
                                                    {{ $listing_json->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('listing_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="listing_title">Apartment Title</label>
                                    <input type="text" class="form-control" id="listing_title" name="listing_title"
                                          value="{{ old('title', $title) }}"
                                        placeholder="Apartment Title">
                                    @error('listing_title')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            
                             <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="type">Type</label>
                                    <input  type="text" class="form-control" id="type" name="type"
                                        placeholder="Type" value="{{ old('type', $type) }}" >
                                    @error('type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="type">Unit Type</label>
                                    <input  type="text" class="form-control" id="unit_type" name="unit_type"
                                        placeholder="Type" value="{{ old('unit_type', $unit_type) }}" >
                                    @error('unit_type')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="floor">Floor</label>
                                    <input  type="text" class="form-control" id="floor" name="floor"
                                        placeholder="floor" value="{{ old('floor', $floor) }}" >
                                    @error('floor')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="unit_number">Unit Number</label>
                                    <input  type="text" class="form-control" id="unit_number" name="unit_number"
                                        placeholder="unit_number" value="{{ old('unit_number', $unit_number) }}" >
                                    @error('unit_number')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            

                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="host_name">Host Name</label>
                                    <input type="text" class="form-control" id="host_name" name="host_name"
                                         value="{{ old('owner_name', $owner_name) }}"
                                        placeholder="Host Name">
                                    @error('host_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="host_phone">Host Phone</label>
                                    <input type="text" class="form-control" id="host_phone" name="host_phone"
                                        {{-- value="{{ isset($audit) && $audit ? $audit->listing_title : '' }}" --}}
                                        placeholder="Host Phone">
                                    @error('host_phone')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="host_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" id ="host_id" name="host_id"
                                            data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ isset($audit) && $audit->host_id === $items->id ? 'selected' : '' }}>
                                                    {{ $items->name }}
                                                    {{ $items->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error('host_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="poc_name">Poc Name</label>
                                    <input type="text" class="form-control" id="poc_name" name="poc_name"
                                        {{-- value="{{ isset($audit) && $audit ? $audit->listing_title : '' }}" --}}
                                        placeholder="Poc Name">
                                    @error('poc_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label">POC</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="poc" data-placeholder="Select POC">
                                            <option value="" selected disabled>Select POC</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ isset($audit) && $audit->poc === $items->id ? 'selected' : '' }}>
                                                    {{ $items->name }}
                                                    {{ $items->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error('poc')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Assign To Property Manager</label>
                                    <div class="form-control-wrap">
                                          <select class="form-select select2" id="assignToPropertyManager"
                                            name="assignToPropertyManager" data-placeholder="Select POC">
                                            <option value="" selected disabled>Select Assign To Property Manager
                                            </option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                
                                                {{ old('assignTo', $accountManager_id) == $items->id ? 'selected' : '' }}
                                                
                                                    {{ isset($audit) && $audit->assignToPropertyManager === $items->id ? 'selected' : '' }}>
                                                    {{ $items->name }}
                                                    {{ $items->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error('assignToPropertyManager')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Assign To Vendor</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" id="assignToVendor" name="assignToVendor"
                                            data-placeholder="Select POC">
                                            <option value="" selected disabled>Select Assign To Vendor</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ isset($audit) && $audit->assignToVendor === $items->id ? 'selected' : '' }}>
                                                    {{ $items->name }}
                                                    {{ $items->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error('assignToVendor')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="start_date">Activation Start</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="{{ isset($audit) && $audit ? $audit->start_date : '' }}"
                                        placeholder="Group Title">
                                    @error('start_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="end_date">Activation End</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="{{ isset($audit) && $audit ? $audit->end_date : '' }}"
                                        placeholder="Group Title">
                                    @error('end_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="cleaning_date">Cleaning Date</label>
                                    <input type="date" class="form-control" id="cleaning_date" name="cleaning_date"
                                        value="{{ isset($audit) && $audit ? $audit->cleaning_date : '' }}"
                                        placeholder="Group Title">
                                    @error('cleaning_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="key_code">Key Code</label>
                                    <input type="text" class="form-control" id="key_code" name="key_code"
                                        value="{{ isset($audit) && $audit ? $audit->key_code : '' }}"
                                        placeholder="Key Code">
                                    @error('key_code')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="status">Status</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="status" id = "status"
                                            data-placeholder="Select Status">
                                            <option value="" selected disabled>Select Status</option>
                                            <option value="assigned">Assigned</option>
                                            <option value="pending">Pending</option>
                                            <option value="on the way">On the way</option>
                                            <option value="start audit">Start audit</option>
                                            <option value="mark as done">Mark as done</option>
                                            <option value="completed"> Completed</option>

                                        </select>
                                        @error('status')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3" style="display:none;">
                                <div class="form-group">
                                    <label class="form-label" for="audit_id">Audit</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="audit_id" id = "audit_id" readonly
                                            data-placeholder="Select Apartment">
                                            <option value="" selected disabled>Select Apartment</option>
                                            @foreach ($audits as $items)
                                                {{-- @php
                                                    $listing_json = json_decode($items->listing_json);
                                                @endphp --}}
                                                {{-- <option value="{{ $items->id }}">{{ $listing_json->title }}</option> --}}
                                                <option value="{{ $items->id }}"
                                                    {{ isset($_GET['audit_id']) && $_GET['audit_id'] == $items->id ? 'selected' : '' }}>
                                                    {{ $items->listing_title }} --
                                                    {{ $items->id }}</option>
                                            @endforeach
                                        </select>
                                        @error('audit_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="form-group">
                                    <label class="form-label" for="location">Google Map Link</label>
                                    <input type="text" class="form-control" id="location" name="location"
                                         value="{{ old('property_google_map_link', $property_google_map_link) }}"
                                        placeholder="Google Map">
                                    @error('location')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="comments">Notes</label>
                                    <textarea type="text" class="form-control" id="comments" name="comments" rows="3"
                                        placeholder="Comments "></textarea>
                                    @error('comments')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group text-end">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<script>

$(document).ready(function () {
    $('#listing_id').on('change', function () {
        var listingId = $(this).val(); 
        
        if (listingId) {
            $.ajax({
                url: '/get-listing-details/' + listingId, 
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        console.log(response.data);
                        $('#some_field').val(response.data.some_field); 

                        $('#listing_title').val(response.data.title).prop('readonly', true);
                        $('#unit_number').val(response.data.apartment_num);
                        $('#unit_type').val(response.data.property_type);
                        $('#location').val(response.data.google_map);

                    } else {
                        alert('Details not found!');
                    }
                },
                error: function () {
                    alert('Error fetching details.');
                }
            });
        }
    });
});

</script>        
@endsection

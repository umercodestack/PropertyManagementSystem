@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Deep Cleaning Task</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('deep-cleaning-management.update', $deep_cleaning->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">


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
                                                    {{ $deep_cleaning->listing_id === $items->id ? 'selected' : '' }}>
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
                                        value="{{ $deep_cleaning->listing_title }}" placeholder="Apartment Title">
                                    @error('listing_title')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="type">Type</label>
                                    <input  type="text" class="form-control" id="type" name="type"
    placeholder="Type" value="{{ old('type', $deep_cleaning->type) }}" >
                                 
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="unit_type">Unit Type</label>
                                    <input  type="text" class="form-control" id="unit_type" name="unit_type"
    placeholder="unit_type" value="{{ old('unit_type', $deep_cleaning->unit_type) }}" >
                                   
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="floor">Floor</label>
                                    <input  type="text" class="form-control" id="floor" name="floor"
    placeholder="floor" value="{{ old('floor', $deep_cleaning->floor) }}" >
                                   
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="unit_number">Unit Number</label>
                                    <input  type="text" class="form-control" id="unit_number" name="unit_number"
    placeholder="unit_number" value="{{ old('unit_number', $deep_cleaning->unit_number) }}" >
                                    
                                </div>
                            </div>
                            
                            <div class="col-md-6" style="display:none">
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
                                                    {{ $deep_cleaning->listing_id === $items->id ? 'selected' : '' }}>
                                                    {{ $listing_json->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('listing_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="host_name">Host Name</label>
                                    <input type="text" class="form-control" id="host_name" name="host_name"
                                        value="{{ $deep_cleaning->host_name }}"
                                        placeholder="Host Name">
                                    @error('host_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="host_phone">Host Phone</label>
                                    <input type="text" class="form-control" id="host_phone" name="host_phone"
                                    value="{{ $deep_cleaning->host_phone }}"
                                        placeholder="Host Phone">
                                    @error('host_phone')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="host_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" id ="host_id" name="host_id"
                                            data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ $deep_cleaning->host_id === $items->id ? 'selected' : '' }}>
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

                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="poc_name">Poc Name</label>
                                    <input type="text" class="form-control" id="poc_name" name="poc_name"
                                          value="{{ $deep_cleaning->poc_name }}"
                                        placeholder="Poc Name">
                                    @error('poc_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label">POC</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="poc" data-placeholder="Select POC">
                                            <option value="" selected disabled>Select POC</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ $deep_cleaning->poc === $items->id ? 'selected' : '' }}>
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
                                                    {{ $deep_cleaning->assignToPropertyManager === $items->id ? 'selected' : '' }}>
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
                                                    {{ $deep_cleaning->assignToVendor === $items->id ? 'selected' : '' }}>
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

                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="start_date">Activation Start</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="{{ $deep_cleaning->start_date }}" placeholder="Group Title">
                                    @error('start_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="end_date">Activation End</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="{{ $deep_cleaning->end_date }}" placeholder="Group Title">
                                    @error('end_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="cleaning_date">Cleaning Date</label>
                                    <input type="date" class="form-control" id="cleaning_date" name="cleaning_date"
                                        value="{{ $deep_cleaning->cleaning_date }}" placeholder="Group Title">
                                    @error('cleaning_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="key_code">Key Code</label>
                                    <input type="text" class="form-control" id="key_code" name="key_code"
                                        value="{{ $deep_cleaning->key_code }}" placeholder="Group Title">
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
                                            <option value="assigned"
                                                {{ $deep_cleaning->status === 'assigned' ? 'selected' : '' }}>
                                                Assigned</option>
                                            <option value="pending"
                                                {{ $deep_cleaning->status === 'pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="on the way"
                                                {{ $deep_cleaning->status === 'on the way' ? 'selected' : '' }}>
                                                On the way</option>
                                                <option value="start audit"
                                                {{ $deep_cleaning->status === 'start audit' ? 'selected' : '' }}>
                                                Start audit</option>
                                                <option value="mark as done"
                                                {{ $deep_cleaning->status === 'mark as done' ? 'selected' : '' }}>
                                                Mark as done</option>
                                            <option value="completed"
                                                {{ $deep_cleaning->status === 'completed' ? 'selected' : '' }}>
                                                Completed</option>

                                        </select>
                                        @error('status')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="audit_id">Audit</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" name="audit_id" id = "audit_id"
                                            data-placeholder="Select Apartment">
                                            <option value="" selected disabled>Select Apartment</option>
                                            @foreach ($audits as $items)
                                                {{-- @php
                                                    $listing_json = json_decode($items->listing_json);
                                                @endphp --}}
                                                {{-- <option value="{{ $items->id }}">{{ $listing_json->title }}</option> --}}
                                                <option value="{{ $items->id }}"
                                                    {{ $deep_cleaning->audit_id === $items->id ? 'selected' : '' }}>
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

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="location">Google Map Link</label>
                                    <input type="text" class="form-control" id="location" name="location"
                                        value="{{ $deep_cleaning->location }}" placeholder="Google Map">
                                    @error('location')
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


                    <h6>Notes</h6>

                    <div class="col-md-12" style="background: #efefef; padding: 10px; border-radius: 5px;">
                        <div class="row">
                            @if (!$comments)
                                <div class="col-md-12">
                                    <p class="ml-3">No Comments Available</p>
                                </div>
                            @endif
                            {{-- {{ dd($comments) }} --}}
                            @isset($comments)
                                @foreach ($comments as $comment)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <span>{{ $comment->comments }}</span>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            <span
                                                style="font-size: 12px">{{ $comment->user->name . ' ' . $comment->user->surname }}
                                                - {{ $comment->created_at }}</span>
                                        </div>
                                        <hr>
                                    </div>
                                @endforeach
                            @endisset
                            <form action="{{ route('deepcleaning-management.storeComment') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <textarea class="form-control" name="comments" id="comments" rows="5"></textarea>
                                        <input type="hidden" name="audit_id"
                                            value="{{ isset($deep_cleaning['id']) ? $deep_cleaning['id'] : '' }}">
                                    </div>
                                    <div class="col-md-12 text-end mt-2">
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>


                    <br>
                    <br>

                    <h6>Status Logs</h6>
<div class="col-md-12 mt-2">

                    <div class="space-y-4">
        @foreach ($statuslogs ?? [] as $log)
            <div class="bg-white p-1 shadow-md rounded-lg border">
                <div class="flex items-center justify-between">
                    <div>
                   <p class="text-sm text-gray-500">{{ Str::ucfirst($log->status) }}</p>
                        <p class="text-sm font-semibold text-gray-700">{{ $log->user_name }}</p>
                        
                    </div>
                    <p class="text-xs text-gray-400"> {{ \Carbon\Carbon::parse($log->timestamp)->addHours(5)->format('d M Y h:i A') }}</p>
                </div>
            </div>
        @endforeach
</div>

<br/>

<h6>Images</h6>
@if($deepcleaningImages && $deepcleaningImages->isNotEmpty())
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            @foreach($deepcleaningImages as $image)
                <div class="swiper-slide image-box">
                    <a href="{{ asset('storage/' . $image->file_path) }}" data-fancybox="gallery">
                        <img class="fixed-image" src="{{ asset('storage/' . $image->file_path) }}" alt="audit Image">
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Swiper Navigation -->
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
@else
    <p class="mt-5">No cleaning images available.</p>
@endif


                </div>

            </div>
        </div>
    </div>
@endsection

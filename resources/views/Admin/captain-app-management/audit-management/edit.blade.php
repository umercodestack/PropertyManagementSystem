@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Audit Task</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('audit-management.update', $audit_management->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row gy-4">
                            {{-- <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Group Title</label>
                                    <input type="text" class="form-control" id="group_name" name="group_name"
                                        placeholder="Group Title">
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div> --}}


                <div class="col-lg-3">
                    <div class="form-group">
                        <label class="form-label">Host Id</label>
                        <input type="text" value="{{ $hostaboard->host_id ?? '-' }}" class="form-control" disabled>
                    </div>
                </div>            

                <div class="col-lg-3">
                    <div class="form-group">
                        <label class="form-label">Property Id</label>
                        <input type="text" value="{{ $hostaboard->property_id ?? '-' }}" class="form-control" disabled>
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
                                                    {{ $audit_management->listing_id === $items->id ? 'selected' : '' }}>
                                                    {{ $listing_json->title ?? 'No Title' }}</option>
                                            @endforeach
                                        </select>
                                        @error('listing_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="listing_title">Apartment Title</label>
                                    <input type="text" class="form-control" id="listing_title" name="listing_title"
                                        value="{{ $hostaboard->title ?? '-' }}" placeholder="Apartment Title" disabled>
                                    @error('listing_title')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="type">Type</label>
                                    <input  type="text" class="form-control" id="type" name="type"
                                                    placeholder="Type" value="{{ old('type', $audit_management->type) }}" >
                                 
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="unit_type">Unit Type</label>
                                    <input  type="text" class="form-control" id="unit_type" name="unit_type"
                                    placeholder="unit_type" value="{{ old('unit_type', $audit_management->unit_type) }}" >
                                   
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="floor">Floor</label>
                                    <input  type="text" class="form-control" id="floor" name="floor" placeholder="floor" value="{{ old('floor', $audit_management->floor) }}" >
                                   
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="unit_number">Unit Number</label>
                                    <input  type="text" class="form-control" id="unit_number" name="unit_number" placeholder="unit_number" value="{{ old('unit_number', $audit_management->unit_number) }}" >
                                    
                                </div>
                            </div>
                            
                            
                            

                            <div class="col-md-6" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="host_id">Host</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" id ="host_id" name="host_id"
                                            data-placeholder="Select Host">
                                            <option value="" selected disabled>Select Host</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ $audit_management->host_id === $items->id ? 'selected' : '' }}>
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
                                    <label class="form-label" for="host_name">Host Name</label>
                                    <input type="text" class="form-control" id="host_name" name="host_name"
                                        value="{{ $audit_management->host_name }}"
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
                                    value="{{ $audit_management->host_phone }}"
                                        placeholder="Host Phone">
                                    @error('host_phone')
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
                                                    {{ $audit_management->poc === $items->id ? 'selected' : '' }}>
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
                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="poc_name">Poc Name</label>
                                    <input type="text" class="form-control" id="poc_name" name="poc_name"
                                          value="{{ $audit_management->poc_name }}"
                                        placeholder="Poc Name">
                                    @error('poc_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">Assign To</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" id="assignTo" name="assignTo"
                                            data-placeholder="Select POC">
                                            <option value="" selected disabled>Select Assign To</option>
                                            @foreach ($users as $items)
                                                <option value="{{ $items->id }}"
                                                    {{ $audit_management->assignTo === $items->id ? 'selected' : '' }}>
                                                    {{ $items->name }}
                                                    {{ $items->surname }}</option>
                                            @endforeach
                                        </select>
                                        @error('assignTo')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="start_date">Activation Start</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="{{ $audit_management->start_date }}" placeholder="Group Title">
                                    @error('start_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3" style="display:none">
                                <div class="form-group">
                                    <label class="form-label" for="end_date">Activation End</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="{{ $audit_management->end_date }}" placeholder="Group Title">
                                    @error('end_date')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label" for="audit_date">Audit Date</label>
                                    <input type="date" class="form-control" id="audit_date" name="audit_date"
                                        value="{{ $audit_management->audit_date }}" placeholder="Audit Date">
                                    @error('audit_date')
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
                                                {{ $audit_management->status === 'assigned' ? 'selected' : '' }}>
                                                Assigned</option>
                                            <option value="pending"
                                                {{ $audit_management->status === 'pending' ? 'selected' : '' }}>
                                                Pending</option>
                                            <option value="on the way"
                                                {{ $audit_management->status === 'on the way' ? 'selected' : '' }}>
                                                On the way</option>
                                                <option value="start audit"
                                                {{ $audit_management->status === 'start audit' ? 'selected' : '' }}>
                                                Start audit</option>
                                                <option value="mark as done"
                                                {{ $audit_management->status === 'mark as done' ? 'selected' : '' }}>
                                                Mark as done</option>
                                            <option value="completed"
                                                {{ $audit_management->status === 'completed' ? 'selected' : '' }}>
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
                                    <label class="form-label" for="key_code">Key Code</label>
                                    <input type="text" class="form-control" id="key_code" name="key_code"
                                        value="{{ $audit_management->key_code }}" placeholder="Key Code">
                                    @error('key_code')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label" for="location">Google Map Link</label>
                                    <input type="text" class="form-control" id="location" name="location"
                                        value="{{ $audit_management->location }}" placeholder="google map">
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
                            <form action="{{ route('audit-management.storeComment') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <textarea class="form-control" name="comments" id="comments" rows="5"></textarea>
                                        <input type="hidden" name="audit_id"
                                            value="{{ isset($audit_management['id']) ? $audit_management['id'] : '' }}">
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

<!-- <h6>Images</h6>
@if($auditImages && $auditImages->isNotEmpty())
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            @foreach($auditImages as $image)
                <div class="swiper-slide image-box">
                    <a href="{{ asset('storage/' . $image->file_path) }}" data-fancybox="gallery">
                        <img class="fixed-image" src="{{ asset('storage/' . $image->file_path) }}" alt="audit Image">
                    </a>
                </div>
            @endforeach
        </div>

        
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
@else
    <p class="mt-5">No Audit images available.</p>
@endif -->


@php
    use Illuminate\Support\Facades\Storage;
@endphp

<br />
<h6>Audit Checklist</h6>

<div class="col-md-12" style="display: flex; flex-wrap: wrap; justify-content: center;">
    @if($finalChecklist && count($finalChecklist) > 0)

        @foreach($finalChecklist as $task)
            <div class="card card-bordered m-2 shadow-sm" style="width: 320px;">
                <div class="card-header bg-light d-flex align-items-center justify-content-between"
                     onclick="toggleDetails({{ $task->id }})" style="cursor: pointer;">
                    <span class="fw-bold">{{ $task->name }} ({{ $task->completed_tasks }}/{{ $task->total_tasks }})</span>
                </div>

                <div class="card-body d-none" id="details-{{ $task->id }}">
                    @if(!empty($task->sub_checklist))
                        @foreach($task->sub_checklist as $detail)
                            <div class="list-group-item mb-3 border rounded p-2"
                                 style="background-color: {{ $detail->value ? '#eaffea' : '#fff3f3' }};">
                                <div><strong>Task:</strong> {{ $detail->task }}</div>
                                <div><strong>Value:</strong> {{ $detail->value ?? '—' }}</div>
                                <div><strong class="text-muted">Options:</strong> {{ $detail->options ?? '—' }}</div>


                               @if(!empty($detail->images) && is_array($detail->images))
    <div class="mt-2">
        <strong class="text-info">Images:</strong>
        <div class="d-flex flex-wrap mt-1">
            @foreach($detail->images as $img)
                <a href="{{ asset('storage/' . $img) }}" target="_blank" class="me-1 mb-1">
                    <img src="{{ asset('storage/' . $img) }}" alt="Image" width="60" height="60"
                         style="object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                </a>
            @endforeach
        </div>
    </div>
@endif

                            </div>
                        @endforeach
                    @else
                        <p>No sub-tasks available.</p>
                    @endif
                </div>
            </div>
        @endforeach

    @else
        <p class="mt-5">No checklist available.</p>
    @endif
</div>





                            
                            
                       
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

<script>
function toggleDetails(taskId) {
    let detailsDiv = document.getElementById("details-" + taskId);
    detailsDiv.classList.toggle("d-none");
}
</script>
@endsection

@extends('Admin.layouts.app')
@section('content')
    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">Edit Cleaning</h3>
                </div><!-- .nk-block-head-content -->
            </div><!-- .nk-block-between -->
        </div><!-- .nk-block-head -->
        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">
                    <form action="{{ route('cleaning-management.updateData') }}" method="POST">
                        @csrf
                        {{-- {{ dd($booking['cleaning']) }} --}}
                        <div class="row gy-4">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Booking ID</label>
                                    <input type="text" class="form-control" id="booking_id" name="booking_id"
                                        value="{{ $booking['booking_id'] }}" placeholder="listing" readonly>
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Apartment</label>
                                    <input type="text" class="form-control" id="listing" name="listing"
                                        value="{{ $booking['listing'] }}" placeholder="listing" readonly>
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Check Out</label>
                                    <input type="text" class="form-control" id="checkout_date" name="checkout_date"
                                        value="{{ $booking['checkout'] }}" placeholder="listing" readonly>
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <input type="hidden" name="listing_id" value="{{ $booking['listing_id'] }}">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="key_code">Key code</label>
                                    <input type="text" class="form-control" id="key_code" name="keycode"
                                        value="{{ isset($booking['cleaning']['key_code']) && $booking['cleaning']['key_code'] ? $booking['cleaning']['key_code'] : '' }}" 
                                        placeholder="listing">
                                    @error('key_code')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror

                                </div>
                            </div>
                            <input type="hidden" name="cleaning_id" value="{{ isset($booking['cleaning']['id']) ? $booking['cleaning']['id'] : null }}">
                            @error('cleaning_id')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Guest Name</label>
                                    <input type="text" class="form-control" id="listing" name="listing" readonly
                                        value="{{ $booking['guest']->name . ' ' . $booking['guest']->surname }}"
                                        placeholder="listing">
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Guest Phone</label>
                                    <input type="text" class="form-control" id="listing" name="listing" readonly
                                        value="{{ $booking['guest']->phone }}" placeholder="listing">
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Host Name</label>
                                    <input type="text" class="form-control" id="listing" name="listing" readonly
                                        value="{{ (isset($booking['host']->name) && $booking['host']->name ? $booking['host']->name : '') . ' ' . (isset($booking['host']->surname) && $booking['host']->surname ? $booking['host']->surname : '') }}"
                                        placeholder="listing">
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Host Phone</label>
                                    <input type="text" class="form-control" id="listing" name="listing" readonly
                                        value="{{ isset($booking['host']->phone) ? $booking['host']->phone : ''}}" placeholder="listing">
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="group_name">Cleaning Date</label>
                                    <input type="text" class="form-control" id="cleaning_date" name="cleaning_date" 
                                        value="{{ $booking['cleaning_date'] }}" placeholder="cleaning date" readonly>
                                    @error('group_name')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
<div class="col-md-2">
    <div class="form-group">
        <label class="form-label" for="cleaning_status">Cleaning Status</label>
        <select class="form-control select2" id="cleaning_status" name="cleaning_status" readonly>
            <option value="pending" 
                {{ isset($booking['cleaning']) && $booking['cleaning']['status'] === 'pending' ? 'selected' : '' }}>
                Pending
            </option>
            <option value="on the way" 
                {{ isset($booking['cleaning']) && $booking['cleaning']['status'] === 'on the way' ? 'selected' : '' }}>
                On the Way
            </option>
            <option value="start cleaning" 
                {{ isset($booking['cleaning']) && $booking['cleaning']['status'] === 'start cleaning' ? 'selected' : '' }}>
                Start Cleaning
            </option>
            <option value="resume cleaning" 
                {{ isset($booking['cleaning']) && $booking['cleaning']['status'] === 'resume cleaning' ? 'selected' : '' }}>
                Resume Cleaning
            </option>
            <option value="completed" 
                {{ isset($booking['cleaning']) && $booking['cleaning']['status'] === 'completed' ? 'selected' : '' }}>
                completed
            </option>
        </select>
        @error('cleaning_status')
            <span id="fva-cleaning-status-error" class="invalid">{{ $message }}</span>
        @enderror
    </div>
</div>


                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="checkouttime">Checkout Time</label>
                                    <input type="text" placeholder="Select Time Picker" class="form-control datetimepic" id="checkouttime" name="checkouttime" value="{{ isset($booking['checkout_time']) ? $booking['checkout_time'] : '' }}">
                                    @error('checkouttime')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="checkintime">Next Guest CheckIn Time</label>
                                    <input type="text" placeholder="Select Time Picker" class="form-control datetimepic" id="checkintime" name="checkintime"  value="{{ isset($booking['checkin_time']) ? $booking['checkin_time'] : '' }}">
                                    @error('checkintime')
                                        <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2" >
                                <div class="form-group">
                                    <label class="form-label" for="cleaner_id">Cleaner</label>
                                    <div class="form-control-wrap">
                                        <select class="form-select select2" id ="cleaner_id" name="cleaner_id"
                                            data-placeholder="Select Cleaner">
                                            <option value="" selected disabled>Select Cleaner</option>
                                            @foreach ($users as $items)
                                            <option value="{{ $items->id }}"
                                                  {{ (isset($booking['cleaner_id']) && $booking['cleaner_id'] === $items->id) ? 'selected' : '' }}>
                                             {{ $items->name }} {{ $items->surname }}
                                            
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('cleaner_id')
                                            <span id="fva-full-name-error" class="invalid">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="form-label" for="cleaner_assign_datetime">Assigned Time</label>
                                    <input type="text" placeholder="Select Time Picker" class="form-control datetimepic" id="cleaner_assign_datetime" name="cleaner_assign_datetime"  value="{{ isset($booking['cleaner_assign_datetime']) ? $booking['cleaner_assign_datetime'] : '' }}">
                                    @error('cleaner_assign_datetime')
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
                            <form action="{{ route('cleaning-management.storeComment') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-12">
                                        <textarea class="form-control" name="comments" id="comments" rows="5"
                                            @if ($booking['cleaning'] == null) ? readonly : '' @endif required></textarea>
                                        <input type="hidden" name="cleaning_id"
                                            value=" {{ isset($booking['cleaning']['id']) ? $booking['cleaning']['id'] : '' }}">
                                    </div>
                                    <div class="col-md-12 text-end mt-2">
                                        <button type="submit" class="btn btn-primary"
                                            @if ($booking['cleaning'] == null) ? readonly : '' @endif>Submit</button>
                                    </div>
                                </div>
                            </form>

                        </div>
                    </div>
                    
                    
                     <br>
                   
<h6>Cleaning Logs</h6>
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
                    
                    
                </div>

<br/>
<h6>Check List</h6>
<div class="col-md-12" style="display: flex; flex-wrap: wrap; justify-content: center;">
    @if($checklist && count($checklist) > 0)
        @foreach($checklist as $task)
            <div class="card card-bordered m-2" style="width: 250px;">
                <div class="card-header d-flex align-items-center justify-content-between" 
                     onclick="toggleDetails({{ $task->id }})" style="cursor: pointer;">
                    <span>{{ $task->name }} ({{ $task->completed_tasks }}/{{ $task->total_tasks }})</span>
                    <input type="checkbox" disabled  class="task-checkbox" data-id="{{ $task->id }}" 
                           {{ $task->completed_tasks == $task->total_tasks ? 'checked' : '' }}>
                </div>
                <div class="card-body d-none" id="details-{{ $task->id }}">
                    @php
                        $task_details = DB::select("CALL get_cleaning_checklist_detail_v2($task->id, $task->cleaning_id);");
                    @endphp
                    <ul class="list-group">
                        @foreach($task_details as $detail)
                            <li class="list-group-item">
                                <input type="checkbox" disabled  class="sub-task-checkbox" data-id="{{ $detail->id }}" 
                                       {{ $detail->is_completed ? 'checked' : '' }}>
                                {{ $detail->task }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endforeach
    @else
        <p class="mt-5">No cleaning checklist available.</p>
    @endif
</div>  

<h6>Images</h6>
@if($cleaningimages && $cleaningimages->isNotEmpty())
    <div class="swiper mySwiper">
        <div class="swiper-wrapper">
            @foreach($cleaningimages as $image)
                <div class="swiper-slide image-box">
                    <a href="{{ asset('storage/' . $image->file_path) }}" data-fancybox="gallery">
                        <img class="fixed-image" src="{{ asset('storage/' . $image->file_path) }}" alt="Cleaning Image">
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
@endsection

<script>
function toggleDetails(taskId) {
    let detailsDiv = document.getElementById("details-" + taskId);
    detailsDiv.classList.toggle("d-none");
}
</script>
